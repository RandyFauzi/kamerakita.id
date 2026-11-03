"""1618 recording CSV connector. Credentials come from environment / Windows user settings."""
import argparse
import csv
import gzip
import hashlib
import http.cookiejar
import json
import logging
from logging.handlers import RotatingFileHandler
import math
import os
from pathlib import Path
import re
import shutil
import sqlite3
import time
import urllib.parse
import urllib.request
from urllib.error import HTTPError, URLError
from datetime import datetime, timezone, timedelta
from decimal import Decimal, InvalidOperation, ROUND_HALF_UP
import uuid

import atlas_sync as shared

BASE = 'https://partners.1618robotics.com'
STATE_DIR = Path(shared.setting('ROBOTICS_1618_DATA_DIR') or Path.home() / '.kamerakita' / '1618')
REQUIRED = {'recording_id', 'email', 'task', 'created_utc', 'duration_s', 'status',
            'billable_h', 'rejected_h', 'pending_h', 'deferred_h', 'submitted_utc',
            'reviewed_utc', 'reconciliation', 'rejection_reasons', 'pay_period_start'}


def utc(value):
    parsed = datetime.fromisoformat(value.replace('Z', '+00:00'))
    if parsed.tzinfo is None:
        raise ValueError('Source timestamp is missing its timezone')
    return parsed.astimezone(timezone.utc)


def minutes(value, factor):
    try:
        number = Decimal(value)
        if not number.is_finite() or number < 0 or number * factor > 10000000:
            raise ValueError('Invalid source duration')
        return str((number * factor).quantize(Decimal('0.000001'), rounding=ROUND_HALF_UP))
    except InvalidOperation:
        raise ValueError('Malformed source duration') from None


def parse_csv(path, metadata):
    expected = metadata['recordings_csv']
    start = datetime.fromisoformat(expected['from']).date()
    end = datetime.fromisoformat(expected['to']).date()
    if end < start:
        raise ValueError('Invalid export window')
    rows = []
    seen = set()
    with Path(path).open(encoding='utf-8-sig', newline='') as handle:
        reader = csv.DictReader(handle, strict=True)
        if not REQUIRED.issubset(reader.fieldnames or []) or len(reader.fieldnames) != len(set(reader.fieldnames)):
            raise ValueError('1618 CSV columns changed or are incomplete')
        for index, row in enumerate(reader, 2):
            if None in row or any(row[key] is None for key in REQUIRED):
                raise ValueError(f'Malformed CSV row {index}')
            identity = row['recording_id'].strip().lower()
            email = row['email'].strip().lower()
            if not re.fullmatch('[a-f0-9]{24}', identity) or identity in seen:
                raise ValueError(f'Invalid or duplicate recording ID at row {index}')
            if not re.fullmatch(r'[^\s@]+@[^\s@]+\.[^\s@]+', email) or len(email) > 255:
                raise ValueError(f'Invalid email at row {index}')
            if not row['task'].strip() or len(row['task']) > 255 or len(row['status']) > 255:
                raise ValueError(f'Invalid task/status at row {index}')
            created = utc(row['created_utc'])
            if not start <= created.date() <= end:
                raise ValueError(f'Recording outside export window at row {index}')
            record = dict(record_id=identity, participant=email, task=row['task'], recorded_utc=created.isoformat(),
                          duration_min=minutes(row['duration_s'], Decimal(1) / 60), status=row['status'])
            for field, column in [('billable_min', 'billable_h'), ('rejected_min', 'rejected_h'),
                                  ('pending_min', 'pending_h'), ('deferred_min', 'deferred_h')]:
                record[field] = minutes(row[column], Decimal(60))
            for key in ['submitted_utc', 'reviewed_utc']:
                record[key] = utc(row[key]).isoformat() if row[key] else None
            for key in ['reconciliation', 'rejection_reasons']:
                if len(row[key]) > 20000:
                    raise ValueError(f'Source notes too long at row {index}')
                record[key] = row[key] or None
            if row['pay_period_start']:
                datetime.fromisoformat(row['pay_period_start'].replace('Z', '+00:00'))
            record['pay_period_start'] = row['pay_period_start'] or None
            seen.add(identity)
            rows.append(record)
    if len(rows) != expected['rows']:
        raise ValueError('CSV row count differs from source export metadata')
    return sorted(rows, key=lambda row: row['record_id'])


class SameOriginRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        target = urllib.parse.urlparse(newurl)
        if target.scheme != 'https' or target.netloc != urllib.parse.urlparse(BASE).netloc:
            raise ValueError('Refusing cross-origin redirect with authenticated session')
        return super().redirect_request(req, fp, code, msg, headers, newurl)


class SourceClient:
    def __init__(self, directory, tenant):
        if not re.fullmatch('[a-z0-9-]+', tenant):
            raise ValueError('Invalid 1618 tenant')
        self.root = f'{BASE}/{tenant}/'
        self.cookie_path = directory / 'session.cookies'
        self.cookies = http.cookiejar.LWPCookieJar(str(self.cookie_path))
        if self.cookie_path.exists():
            try:
                self.cookies.load(ignore_discard=True)
            except (OSError, http.cookiejar.LoadError):
                pass
        self.opener = urllib.request.build_opener(SameOriginRedirect(), urllib.request.HTTPCookieProcessor(self.cookies))

    def login(self):
        username = shared.setting('ROBOTICS_1618_USERNAME')
        password = shared.setting('ROBOTICS_1618_PASSWORD')
        if not username or not password:
            raise ValueError('Set ROBOTICS_1618_USERNAME and ROBOTICS_1618_PASSWORD')
        body = urllib.parse.urlencode(dict(username=username, password=password, next=urllib.parse.urlparse(self.root).path)).encode()
        request = urllib.request.Request(BASE + '/login', data=body)
        with self.opener.open(request, timeout=30) as response:
            if urllib.parse.urlparse(response.url).path == '/login':
                raise ValueError('1618 login rejected')
            response.read(65536)
        self.cookies.save(ignore_discard=True)

    def get(self, filename):
        refreshed = False
        for attempt in range(3):
            try:
                response = self.opener.open(self.root + filename, timeout=90)
            except HTTPError as error:
                if error.code == 401 and not refreshed:
                    self.login()
                    refreshed = True
                    continue
                if (error.code < 500 and error.code != 429) or attempt == 2:
                    raise
                delay = error.headers.get('Retry-After', '') if error.headers else ''
                time.sleep(min(60, int(delay)) if delay.isdigit() else 2 ** attempt)
                continue
            except (URLError, TimeoutError):
                if attempt == 2:
                    raise
                time.sleep(2 ** attempt)
                continue
            if urllib.parse.urlparse(response.url).path != '/login':
                return response
            response.close()
            if refreshed:
                raise ValueError('1618 session could not be established')
            self.login()
            refreshed = True
        raise ValueError('1618 request could not be completed')

    def metadata(self):
        with self.get('data.json') as response:
            payload = response.read(8 * 1024 * 1024 + 1)
        if len(payload) > 8 * 1024 * 1024:
            raise ValueError('Source metadata exceeds size limit')
        data = json.loads(payload)
        export = data.get('recordings_csv')
        if not isinstance(export, dict) or not isinstance(export.get('rows'), int) or not 0 <= export['rows'] <= 1000000:
            raise ValueError('Missing/invalid CSV export metadata')
        observed = utc(export['fetched_utc'])
        if observed > datetime.now(timezone.utc) + timedelta(minutes=5):
            raise ValueError('Source clock is in the future')
        utc(data['generated_utc'])
        return data

    def download(self, path):
        digest = hashlib.sha256()
        size = 0
        with self.get('recordings.csv') as response, path.open('wb') as handle:
            for chunk in iter(lambda: response.read(65536), b''):
                size += len(chunk)
                if size > 64 * 1024 * 1024:
                    raise ValueError('CSV export exceeds 64 MiB size limit')
                handle.write(chunk)
                digest.update(chunk)
        if not size:
            raise ValueError('Empty CSV response')
        return digest.hexdigest()


def deliver(conn, records, metadata, digest, endpoint, force=False):
    observed = utc(metadata['recordings_csv']['fetched_utc']).isoformat()
    scope = hashlib.sha256(json.dumps([endpoint, digest, observed, metadata['recordings_csv']['from'],
        metadata['recordings_csv']['to'], metadata['generated_utc']], sort_keys=True).encode()).hexdigest()
    row = conn.execute('SELECT id, completed FROM imports WHERE snapshot_key=?', (scope,)).fetchone()
    if row and row[1] and not force:
        print('[V] Snapshot identik sudah dikonfirmasi server; tidak dikirim ulang.')
        return
    if row and not force:
        import_id = row[0]
    else:
        import_id = str(uuid.uuid4())
        conn.execute('INSERT OR REPLACE INTO imports VALUES (?, ?, 0)', (scope, import_id))
        conn.commit()
    token = shared.setting('ROBOTICS_1618_BOT_TOKEN')
    if not token:
        raise ValueError('Set ROBOTICS_1618_BOT_TOKEN before delivery')
    batches = math.ceil(len(records) / 500)
    for index in range(batches):
        batch = records[index*500:(index+1)*500]
        payload = dict(observed_at=observed, records=batch, **{'import': dict(id=import_id, snapshot_hash=digest,
            total_records=len(records), total_batches=batches, batch_index=index,
            metadata=dict(**{'from': metadata['recordings_csv']['from'], 'to': metadata['recordings_csv']['to']},
                          generated_utc=metadata['generated_utc']))})
        body = json.dumps(payload, allow_nan=False).encode()
        for attempt in range(3):
            delay = 2 ** attempt
            request = urllib.request.Request(endpoint, data=body, headers={'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Bot-Token': token})
            try:
                with urllib.request.urlopen(request, timeout=90) as response:
                    ack = json.load(response)
                if ack.get('status') != 'success' or ack.get('count') != len(batch):
                    raise ValueError('Server did not acknowledge complete batch')
                print(f' - Batch {index+1}/{batches}: diterima {len(batch)}, ditulis {ack.get("written")}, dilewati {ack.get("skipped")}', flush=True)
                break
            except HTTPError as error:
                if (error.code < 500 and error.code != 429) or attempt == 2:
                    raise ValueError(f'KameraKita API rejected import: HTTP {error.code}') from None
                retry_after = error.headers.get('Retry-After', '') if error.headers else ''
                if retry_after.isdigit():
                    delay = min(60, int(retry_after))
            except (URLError, TimeoutError):
                if attempt == 2:
                    raise
            time.sleep(delay)
    conn.execute('UPDATE imports SET completed=1 WHERE snapshot_key=?', (scope,))
    conn.commit()


def run(no_push=False, force=False):
    STATE_DIR.mkdir(parents=True, exist_ok=True)
    shared.DB_PATH = str(STATE_DIR / 'connector.db')
    tenant = shared.setting('ROBOTICS_1618_TENANT') or 'kamerakita-ai'
    endpoint = shared.setting('ROBOTICS_1618_SYNC_URL') or 'https://kamerakitaid.site/api/bot/1618/sync'
    if not endpoint.startswith('https://'):
        raise ValueError('Delivery endpoint must use HTTPS')
    with shared.single_run_lock(), shared.closing(sqlite3.connect(STATE_DIR / 'connector.db', timeout=60)) as conn:
        conn.execute('CREATE TABLE IF NOT EXISTS imports (snapshot_key TEXT PRIMARY KEY, id TEXT NOT NULL, completed INTEGER NOT NULL)')
        client = SourceClient(STATE_DIR, tenant)
        part = STATE_DIR / 'download.part'
        try:
            before = client.metadata()
            digest = client.download(part)
            after = client.metadata()
            if before['recordings_csv'] != after['recordings_csv'] or before['generated_utc'] != after['generated_utc']:
                raise ValueError('1618 published a different snapshot during download; retry sync')
            records = parse_csv(part, after)
            archive = STATE_DIR / f'snapshot-{digest}.csv.gz'
            with part.open('rb') as source, gzip.open(archive, 'wb') as target:
                shutil.copyfileobj(source, target, 65536)
            for old in sorted(STATE_DIR.glob('snapshot-*.csv.gz'), key=lambda p: p.stat().st_mtime, reverse=True)[10:]:
                old.unlink()
            print(f'[V] 1618: {len(records)} recording unik, {len({r["participant"] for r in records})} email; sumber {after["recordings_csv"]["fetched_utc"]}.', flush=True)
            logging.getLogger('1618').info('validated records=%s emails=%s source_fetched=%s snapshot=%s', len(records), len({r['participant'] for r in records}), after['recordings_csv']['fetched_utc'], digest)
            if no_push or not records:
                print('[DRY RUN / EMPTY] Tidak ada data dikirim ke web.')
            else:
                deliver(conn, records, after, digest, endpoint, force)
        finally:
            part.unlink(missing_ok=True)


def main():
    parser = argparse.ArgumentParser(description='Download 1618 recording CSV and import into existing KameraKita recordings')
    parser.add_argument('--no-push', action='store_true')
    parser.add_argument('--force', action='store_true')
    args = parser.parse_args()
    STATE_DIR.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger('1618')
    logger.setLevel(logging.INFO)
    handler = RotatingFileHandler(STATE_DIR / 'connector.log', maxBytes=1024*1024, backupCount=3, encoding='utf-8')
    handler.setFormatter(logging.Formatter('%(asctime)s %(levelname)s %(message)s'))
    logger.addHandler(handler)
    started = time.perf_counter()
    try:
        run(args.no_push, args.force)
        logger.info('success no_push=%s elapsed_seconds=%.2f', args.no_push, time.perf_counter()-started)
        return 0
    except Exception as error:
        print(f'[ERROR] 1618 sync failed: {error}')
        logger.error('failure elapsed_seconds=%.2f error=%s', time.perf_counter()-started, error)
        return 1
    finally:
        handler.close()
        logger.removeHandler(handler)


if __name__ == '__main__':
    raise SystemExit(main())
