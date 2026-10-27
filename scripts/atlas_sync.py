import os
import sys
import re
import sqlite3
import json
import time
import math
import argparse
import csv
import io
from concurrent.futures import ThreadPoolExecutor, as_completed
from collections import defaultdict
from contextlib import closing, contextmanager
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError, URLError
from datetime import datetime
import urllib.request

BASE_URL = "https://atlasdashboard.vtlabs.dev"
SCRIPT_DIR = os.environ.get("ATLAS_SYNC_DATA_DIR", str(Path.home() / ".gemini" / "antigravity" / "scratch"))
STATE_FILE = os.path.join(SCRIPT_DIR, "session_state.json")
# The Excel exporter writes payment overrides to atlas_master.db and can hold
# its write lock for minutes. Recording sync must never share that database.
DB_PATH = os.path.join(SCRIPT_DIR, "atlas_sync_cache.db")


def setting(name):
    value = os.environ.get(name)
    if value or os.name != 'nt':
        return value
    # Task Scheduler may retain the environment from before credentials were set.
    import winreg
    try:
        with winreg.OpenKey(winreg.HKEY_CURRENT_USER, 'Environment') as key:
            return winreg.QueryValueEx(key, name)[0]
    except FileNotFoundError:
        return None


@contextmanager
def single_run_lock():
    """OS lock releases automatically even if Python is stopped or crashes."""
    path = Path(DB_PATH + '.sync.lock')
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open('a+b') as handle:
        if path.stat().st_size == 0:
            handle.write(b'0')
            handle.flush()
        handle.seek(0)
        try:
            if os.name == 'nt':
                import msvcrt
                msvcrt.locking(handle.fileno(), msvcrt.LK_NBLCK, 1)
            else:
                import fcntl
                fcntl.flock(handle.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
        except OSError as error:
            raise RuntimeError('Bot Atlas masih berjalan. Tunggu sync sebelumnya selesai sebelum menjalankan lagi.') from error
        try:
            yield
        finally:
            handle.seek(0)
            if os.name == 'nt':
                msvcrt.locking(handle.fileno(), msvcrt.LK_UNLCK, 1)
            else:
                fcntl.flock(handle.fileno(), fcntl.LOCK_UN)

def init_db():
    Path(DB_PATH).parent.mkdir(parents=True, exist_ok=True)
    conn = sqlite3.connect(DB_PATH, timeout=60)
    conn.execute('PRAGMA journal_mode=WAL')
    cur = conn.cursor()
    cur.execute("""
        CREATE TABLE IF NOT EXISTS recordings (
            record_id TEXT PRIMARY KEY,
            task TEXT,
            participant TEXT,
            recorded_utc TEXT,
            duration_sec REAL,
            status TEXT,
            billable_hours REAL,
            rejected_hours REAL,
            pending_hours REAL,
            deferred_hours REAL,
            reconciliation TEXT,
            first_scraped_at TEXT,
            last_updated_at TEXT
        )
    """)
    conn.commit()
    return conn

def build_payload_records(conn, record_ids):
    # Only recordings observed in this successful scan, never stale cache rows.
    output = []
    for record_id in sorted(record_ids):
        cur = conn.execute('SELECT * FROM recordings WHERE record_id = ?', (record_id,))
        row = cur.fetchone()
        record = dict(zip([c[0] for c in cur.description], row))
        for name, source, factor in [
            ('duration_min', 'duration_sec', 1 / 60),
            ('billable_min', 'billable_hours', 60),
            ('pending_min', 'pending_hours', 60),
            ('rejected_min', 'rejected_hours', 60),
            ('deferred_min', 'deferred_hours', 60),
        ]:
            record[name] = round(record[source] * factor, 6)
        output.append(record)
    return output


def push_to_laravel_api(conn, record_ids):
    records = build_payload_records(conn, record_ids)
    url = os.environ.get('ATLAS_SYNC_URL', 'https://kamerakitaid.site/api/bot/atlas/sync')
    token = setting('ATLAS_BOT_TOKEN')
    if not token:
        raise RuntimeError('Set ATLAS_BOT_TOKEN before enabling API delivery')
    for i in range(0, len(records), 500):
        batch = records[i:i + 500]
        data = json.dumps({'bot_token': token, 'records': batch}, allow_nan=False).encode('utf-8')
        for attempt in range(3):
            req = urllib.request.Request(url, data=data, headers={'Content-Type': 'application/json', 'Accept': 'application/json'})
            try:
                with urllib.request.urlopen(req, timeout=60) as resp:
                    result = json.loads(resp.read().decode('utf-8'))
                    if result.get('status') != 'success' or result.get('count') != len(batch):
                        raise RuntimeError('API did not acknowledge the complete batch; stop sync.')
                print(f'    - Chunk {i}-{i + len(batch)} acknowledged')
                break
            except HTTPError as error:
                if error.code < 500 or attempt == 2:
                    raise RuntimeError(f'API rejected batch with HTTP {error.code}') from error
            except (URLError, TimeoutError):
                if attempt == 2:
                    raise
            time.sleep(2 ** attempt)


def save_or_update_record(conn, rec, page_number):
    columns = ['task', 'participant', 'recorded_utc', 'duration_sec', 'status',
               'billable_hours', 'rejected_hours', 'pending_hours', 'deferred_hours', 'reconciliation']
    values = [rec[name] for name in columns]
    existing = conn.execute('SELECT ' + ', '.join(columns) + ' FROM recordings WHERE record_id = ?',
                            (rec['record_id'],)).fetchone()
    now = datetime.now().isoformat(timespec='seconds')
    if existing is None:
        names = ['record_id'] + columns + ['first_scraped_at', 'last_updated_at']
        conn.execute('INSERT INTO recordings (' + ', '.join(names) + ') VALUES (' +
                     ', '.join('?' for _ in names) + ')', [rec['record_id']] + values + [now, now])
        conn.commit()
        return True, False
    if list(existing) != values:
        conn.execute('UPDATE recordings SET ' + ', '.join(name + ' = ?' for name in columns) +
                     ', last_updated_at = ? WHERE record_id = ?', values + [now, rec['record_id']])
        conn.commit()
        return False, True
    return False, False


def parse_hours(text, label):
    # Do not silently interpret malformed values as zero. Missing categories mean zero.
    if not re.search(r'\b' + label + r'\b', text, re.I):
        return 0.0
    match = re.search(r'\b' + label + r'\s+([0-9]+(?:[.,][0-9]+)?)(?:\s*h(?:ours?)?)?(?=\s|$)', text, re.I)
    if not match:
        raise ValueError(f'Cannot parse {label} hours')
    return float(match.group(1).replace(',', '.'))


def normalize_timestamp(text):
    from datetime import timezone
    text = text.strip()
    if text.upper().endswith(' UTC'):
        text = text[:-4].strip()
    try:
        value = datetime.fromisoformat(text.replace('Z', '+00:00'))
    except ValueError:
        # Atlas displays e.g. "Oct 2, 2026, 2:50 PM UTC".
        value = None
        for fmt in ('%b %d, %Y, %I:%M %p', '%b %d, %Y, %I:%M:%S %p'):
            try:
                value = datetime.strptime(text, fmt)
                break
            except ValueError:
                continue
        if value is None:
            raise ValueError('Unrecognized Atlas UTC timestamp')
    if value.tzinfo is None:
        value = value.replace(tzinfo=timezone.utc)
    return value.astimezone(timezone.utc).isoformat()


def process_table_rows(page):
    rows = page.locator('table tbody tr.ant-table-row').all()
    if not rows:
        raise RuntimeError('No recording rows found; refusing an incomplete sync.')
    results = []
    for row in rows:
        cells = row.locator('td').all()
        if len(cells) < 9:
            raise ValueError('Atlas table layout changed: expected at least 9 cells')
        record_id = (row.get_attribute('data-row-key') or '').strip().lower()
        if not re.fullmatch(r'[a-f0-9]{24}', record_id):
            raise ValueError('Invalid recording ID; refusing to skip a row')
        duration = float(cells[3].inner_text().strip().replace(',', '.'))
        if not math.isfinite(duration) or duration < 0:
            raise ValueError('Invalid recording duration')
        breakdown = cells[6].inner_text().strip()
        if not re.search(r'\b(Billable|Rejected|Pending|Deferred)\b', breakdown, re.I):
            raise ValueError('Missing duration breakdown; refusing to replace durations with zeros')
        participant = cells[1].inner_text().strip().lower()
        if not re.fullmatch(r'[^\s@]+@[^\s@]+\.[^\s@]+', participant):
            raise ValueError('Invalid participant email')
        rec = {
            'record_id': record_id, 'task': cells[0].inner_text().strip(),
            'participant': participant, 'recorded_utc': normalize_timestamp(cells[2].inner_text()),
            'duration_sec': duration, 'status': cells[4].inner_text().strip(),
            'reconciliation': cells[8].inner_text().strip(),
        }
        for label in ['Billable', 'Rejected', 'Pending', 'Deferred']:
            rec[label.lower() + '_hours'] = parse_hours(breakdown, label)
        if not rec['task']:
            raise ValueError('Empty task name')
        results.append(rec)
    return results


def process_source_html(html, visible_ids=None, metadata=None):
    """Read precise Atlas props; table text rounds hours and hides timestamp seconds."""
    class Scripts(HTMLParser):
        def __init__(self):
            super().__init__()
            self.inside = False
            self.parts = []
        def handle_starttag(self, tag, attrs):
            if tag == 'script': self.inside = True
        def handle_endtag(self, tag):
            if tag == 'script': self.inside = False
        def handle_data(self, data):
            if self.inside and data.startswith('self.__next_f.push('):
                self.parts.extend(x for x in json.loads(data[len('self.__next_f.push('):-1]) if isinstance(x, str))
    parser = Scripts()
    parser.feed(html)
    def find(value):
        if isinstance(value, dict):
            if isinstance(value.get('recordings'), list): return value
            children = value.values()
        elif isinstance(value, list): children = value
        else: return None
        for child in children:
            result = find(child)
            if result is not None: return result
        return None
    props = None
    for line in ''.join(parser.parts).splitlines():
        try: value = json.loads(line.split(':', 1)[1])
        except (ValueError, IndexError): continue
        props = find(value)
        if props is not None: break
    if props is None:
        raise ValueError('Missing precise Atlas source props; no rounded fallback allowed')
    if metadata is not None:
        metadata.update(total=props['total'], summary=props['summary'], page=props.get('page'), page_size=props.get('pageSize'))
    records = []
    for source in props['recordings']:
        rec = {'record_id': source['userTaskId'].lower(), 'task': source['taskTitle'],
               'participant': source['participantEmail'].strip().lower(),
               'recorded_utc': normalize_timestamp(source['recordedAt']),
               'duration_sec': float(source['durationSeconds']),
               'status': source['processingStatus'], 'reconciliation': source['reconciliationStatus']}
        for label in ['Billable', 'Rejected', 'Pending', 'Deferred']:
            rec[label.lower() + '_hours'] = float(source['tile' + label + 'Hours'])
        if any(not math.isfinite(rec[key]) or rec[key] < 0 for key in ['duration_sec', 'billable_hours', 'rejected_hours', 'pending_hours', 'deferred_hours']):
            raise ValueError('Invalid precise source duration')
        if not re.fullmatch(r'[a-f0-9]{24}', rec['record_id']) or not rec['task'] or not re.fullmatch(r'[^\s@]+@[^\s@]+\.[^\s@]+', rec['participant']):
            raise ValueError('Invalid precise source metadata')
        records.append(rec)
    ids = [r['record_id'] for r in records]
    if len(ids) != len(set(ids)) or (visible_ids is not None and set(ids) != set(visible_ids)):
        raise ValueError('Raw Atlas rows differ from visible table; source changed during scan')
    return records


def parse_participant_export(payload):
    """Atlas Participants CSV contains rounded account totals, not recording rows."""
    reader = csv.DictReader(io.StringIO(payload.decode('utf-8-sig'), newline=''))
    required = {'Email', 'Recordings', 'Duration (h)', 'Billable (h)', 'Rejected (h)', 'Pending (h)'}
    if not required.issubset(reader.fieldnames or []):
        raise ValueError('Atlas export columns changed; cannot verify account totals')
    accounts = {}
    for row in reader:
        email = row['Email'].strip().lower()
        if not re.fullmatch(r'[^\s@]+@[^\s@]+\.[^\s@]+', email) or email in accounts:
            raise ValueError('Invalid or duplicate email in Atlas export')
        # Atlas exports completely blank metrics for registered accounts that
        # have never recorded. Partial/malformed metrics still fail validation.
        metric_columns = ['Recordings', 'Duration (h)', 'Billable (h)', 'Rejected (h)', 'Pending (h)']
        if all(row[column] == '' for column in metric_columns):
            accounts[email] = dict(count=0, duration_sec=0.0, billable_hours=0.0, rejected_hours=0.0, pending_hours=0.0)
            continue
        count = int(row['Recordings'])
        totals = {key: float(row[column]) for key, column in [
            ('duration_sec', 'Duration (h)'), ('billable_hours', 'Billable (h)'),
            ('rejected_hours', 'Rejected (h)'), ('pending_hours', 'Pending (h)')]}
        if count < 0 or any(not math.isfinite(value) or value < 0 for value in totals.values()):
            raise ValueError('Invalid numeric value in Atlas export')
        accounts[email] = dict(count=count, **totals)
    if not accounts:
        raise ValueError('Empty Atlas export; no data sent')
    return accounts


def validate_account_export(records, accounts):
    grouped = defaultdict(list)
    for record in records:
        grouped[record['participant']].append(record)
    if set(grouped) != {email for email, account in accounts.items() if account['count'] > 0}:
        raise ValueError('Atlas export account list differs from recording details; retry sync')
    for email, rows in grouped.items():
        account = accounts[email]
        if len(rows) != account['count']:
            raise ValueError('Atlas export recording count differs from details; retry sync')
        for key in ['duration_sec', 'billable_hours', 'rejected_hours', 'pending_hours']:
            hours = math.fsum(row[key] for row in rows) / (3600 if key == 'duration_sec' else 1)
            # CSV rounds each account to hundredths of an hour; precise source
            # props remain authoritative for database values.
            if abs(hours - account[key]) > 0.00501:
                raise ValueError(f'Atlas export {key} differs from recording details; retry sync')


def capture_recordings_fast(cookies, max_pages=None, expected_count=None):
    """Read authenticated SSR data with three HTTP workers; no UI pagination."""
    def fetch(number):
        url = BASE_URL + f'/recordings?page={number}&sort=recordedAt&direction=desc'
        for attempt in range(3):
            try:
                request = urllib.request.Request(url, headers={'Cookie': cookies, 'Accept': 'text/html', 'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(request, timeout=45) as response:
                    if '/login' in response.url:
                        raise RuntimeError('Atlas session expired during capture; no data sent')
                    html = response.read().decode('utf-8')
                metadata = {}
                rows = process_source_html(html, metadata=metadata)
                if metadata['page'] != number or not isinstance(metadata['page_size'], int) or metadata['page_size'] <= 0:
                    raise ValueError('Atlas returned an unexpected data page')
                expected = min(metadata['page_size'], max(0, metadata['total'] - (number - 1) * metadata['page_size']))
                if len(rows) != expected:
                    raise ValueError('Incomplete Atlas data page')
                return rows, metadata
            except (URLError, TimeoutError, OSError):
                if attempt == 2: raise
                time.sleep(2 ** attempt)

    first_rows, first = fetch(1)
    if expected_count is not None and first['total'] != expected_count:
        raise ValueError('Atlas export total differs from recording count; retry sync')
    total_pages = max(1, math.ceil(first['total'] / first['page_size']))
    if max_pages and total_pages > max_pages:
        raise RuntimeError('Page limit would produce an incomplete capture; no data sent')
    pages = {1: first_rows}
    print(f'[*] Mengambil detail {first["total"]} recording via HTTP paralel ({total_pages} data pages; tanpa klik browser).', flush=True)
    with ThreadPoolExecutor(max_workers=3) as executor:
        futures = {executor.submit(fetch, number): number for number in range(2, total_pages + 1)}
        for future in as_completed(futures):
            rows, metadata = future.result()
            if metadata['total'] != first['total'] or metadata['page_size'] != first['page_size']:
                raise RuntimeError('Atlas dataset changed during capture; retry sync')
            pages[futures[future]] = rows
            if len(pages) % 25 == 0 or len(pages) == total_pages:
                print(f' - Data dibaca: {len(pages)}/{total_pages}', flush=True)
    check_rows, check = fetch(1)
    if [r['record_id'] for r in check_rows] != [r['record_id'] for r in first_rows] or check['summary'] != first['summary']:
        raise RuntimeError('Atlas data changed during capture; retry sync')
    records = [row for number in range(1, total_pages + 1) for row in pages[number]]
    if len({r['record_id'] for r in records}) != len(records):
        raise ValueError('Duplicate recording IDs in Atlas capture; no data sent')
    validate_source_totals(records, first)
    return records


def validate_source_totals(records, metadata):
    summary = metadata['summary']
    if len(records) != int(metadata['total']) or len(records) != int(summary['recordingCount']):
        raise ValueError('Atlas recording count changed or scan is incomplete; no data sent')
    for key, summary_key, factor in [
        ('duration_sec', 'durationHours', 1 / 3600),
        ('billable_hours', 'billableHours', 1),
        ('rejected_hours', 'rejectedHours', 1),
        ('pending_hours', 'pendingHours', 1),
        ('deferred_hours', 'deferredHours', 1),
    ]:
        expected = float(summary[summary_key])
        if not math.isfinite(expected) or abs(math.fsum(r[key] for r in records) * factor - expected) > 0.0001:
            raise ValueError(f'Atlas {summary_key} differs from scanned rows; retry full sync')


def sync_atlas(email=None, password=None, max_pages=None, disable_smart_stop=True, no_push=False):
    with single_run_lock():
        with closing(init_db()) as conn:
            return _sync_atlas(conn, email, password, max_pages, disable_smart_stop, no_push)


def _sync_atlas(conn, email, password, max_pages, disable_smart_stop, no_push):
    print("[*] Bot Atlas: export akun + detail recording via HTTP paralel.")
    if not no_push and not setting('ATLAS_BOT_TOKEN'):
        raise RuntimeError('Set ATLAS_BOT_TOKEN before enabling API delivery')
    from playwright.sync_api import sync_playwright, Error as PlaywrightError
    email = email or setting('ATLAS_EMAIL')
    password = password or setting('ATLAS_PASSWORD')
    with sync_playwright() as p:
        try:
            browser = p.chromium.launch(headless=True)
        except PlaywrightError as error:
            if "Executable doesn't exist" not in str(error): raise
            browser = p.chromium.launch(headless=True, channel='chrome')
        try:
            context = browser.new_context(storage_state=STATE_FILE if os.path.exists(STATE_FILE) else None)
            page = context.new_page()
            page.goto(BASE_URL + '/recordings')
            if '/login' in page.url:
                if not email or not password:
                    raise RuntimeError('Set ATLAS_EMAIL and ATLAS_PASSWORD for Atlas login')
                print('[*] Memperbarui login Atlas...')
                page.fill("input[name='email']", email)
                page.fill("input[name='password']", password)
                page.click("button[type='submit']")
                page.wait_for_selector('text=Overview', timeout=30000)
                page.goto(BASE_URL + '/recordings')
            page.wait_for_selector('table tbody tr.ant-table-row', timeout=30000)
            context.storage_state(path=STATE_FILE)

            page.goto(BASE_URL + '/participants')
            export_link = page.get_by_role('link', name='Export CSV', exact=True)
            export_link.wait_for(timeout=30000)
            href = export_link.get_attribute('href')
            if href != '/participants/export':
                raise RuntimeError('Atlas export URL changed; inspect before downloading')
            response = context.request.get(BASE_URL + href, timeout=120000)
            if not response.ok or 'text/csv' not in response.headers.get('content-type', ''):
                raise RuntimeError('Atlas CSV export failed; no data sent')
            accounts = parse_participant_export(response.body())
            print(f'[*] CSV berhasil diunduh: {len(accounts)} akun. Validasi detail recording...', flush=True)
            cookies = '; '.join(cookie['name'] + '=' + cookie['value'] for cookie in context.cookies(BASE_URL))
        finally:
            browser.close()

    records = capture_recordings_fast(cookies, max_pages=max_pages, expected_count=sum(a['count'] for a in accounts.values()))
    validate_account_export(records, accounts)
    seen_ids = {record['record_id'] for record in records}
    for record in records:
        save_or_update_record(conn, record, 0)
    print(f'[V] {len(records)} recording tervalidasi dengan ringkasan Atlas dan CSV per email.', flush=True)
    if no_push:
        print(f'[DRY RUN] {len(records)} recordings validated; no API requests sent')
    else:
        push_to_laravel_api(conn, seen_ids)
        print(f'[V] SYNC SELESAI: {len(records)} recording dikonfirmasi server.', flush=True)


def main():
    parser = argparse.ArgumentParser(description='Read Atlas recordings and sync explicitly reported durations')
    parser.add_argument('--all', '-a', action='store_true', help='Compatibility flag; all scans now read every page')
    parser.add_argument('--no-push', action='store_true', help='Read/cache Atlas without sending to KameraKita')
    args = parser.parse_args()
    try:
        sync_atlas(no_push=args.no_push)
        return 0
    except Exception as error:
        print(f'[ERROR] Sync failed: {error}', file=sys.stderr)
        return 1


if __name__ == '__main__':
    sys.exit(main())
