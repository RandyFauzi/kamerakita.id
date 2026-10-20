import os
import sys
import re
import sqlite3
import json
import time
import math
import argparse
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


def process_source_html(html, visible_ids, metadata=None):
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
        metadata.update(total=props['total'], summary=props['summary'])
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
    if len(ids) != len(set(ids)) or set(ids) != set(visible_ids):
        raise ValueError('Raw Atlas rows differ from visible table; source changed during scan')
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
    print("[*] Menginisialisasi Bot Atlas (Murni Sinkronisasi ke Web KameraKita)...")
    if not no_push and not setting("ATLAS_BOT_TOKEN"):
        raise RuntimeError("Set ATLAS_BOT_TOKEN before enabling API delivery")
    from playwright.sync_api import sync_playwright
    email = email or setting("ATLAS_EMAIL")
    password = password or setting("ATLAS_PASSWORD")
    seen_ids = set()
    visited_pages = set()
    all_records = []
    source_metadata = {}

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context()
        page = context.new_page()

        if os.path.exists(STATE_FILE):
            print("[*] Menggunakan session login tersimpan...")
            context = browser.new_context(storage_state=STATE_FILE)
            page = context.new_page()
            page.goto(BASE_URL + "/recordings")

            if "login" in page.url:
                print("[!] Session kedaluwarsa. Login ulang...")
                if not email or not password:
                    raise RuntimeError("Session expired: set ATLAS_EMAIL and ATLAS_PASSWORD")
                page.fill("input[name='email']", email)
                page.fill("input[name='password']", password)
                page.click("button[type='submit']")
                # Wait for dashboard page to load after login
                page.wait_for_selector("text=Overview", timeout=20000)
                page.goto(BASE_URL + "/recordings")
                context.storage_state(path=STATE_FILE)
        else:
            print("[*] Login baru...")
            page.goto(BASE_URL + "/login")
            if not email or not password:
                raise RuntimeError("Set ATLAS_EMAIL and ATLAS_PASSWORD for initial login")
            page.fill("input[name='email']", email)
            page.fill("input[name='password']", password)
            page.click("button[type='submit']")
            # Wait for dashboard page to load after login
            page.wait_for_selector("text=Overview", timeout=20000)
            page.goto(BASE_URL + "/recordings")
            context.storage_state(path=STATE_FILE)

        print("[*] Membaca halaman recordings...")
        # Wait until table actually has data rows
        page.wait_for_selector("table tbody tr.ant-table-row", timeout=15000)

        limit = max_pages if max_pages else 1000
        total_pages = "?"
        try:
            pages_items = page.locator("li.ant-pagination-item").all()
            if pages_items:
                total_pages = pages_items[-1].inner_text().strip()
        except:
            pass

        print(f"[*] Total halaman terdeteksi di Atlas: {total_pages} halaman.")

        for i in range(1, limit + 1):
            time.sleep(1.5)

            # Update total_pages if discovered later
            if total_pages == "?":
                try:
                    pages_items = page.locator("li.ant-pagination-item").all()
                    if pages_items:
                        total_pages = pages_items[-1].inner_text().strip()
                except:
                    pass

            rows_data = process_table_rows(page)
            response = context.request.get(BASE_URL + f'/recordings?page={i}&sort=recordedAt&direction=desc')
            if not response.ok:
                raise RuntimeError('Cannot read precise Atlas source')
            rows_data = process_source_html(response.text(), [r['record_id'] for r in rows_data], source_metadata)
            all_records.extend(rows_data)
            print(f'Length of rows_data: {len(rows_data)}')
            if not rows_data:
                break

            page_new = 0
            page_updated = 0

            signature = tuple(rec['record_id'] for rec in rows_data)
            if signature in visited_pages:
                raise RuntimeError('Repeated page detected; refusing incomplete sync')
            visited_pages.add(signature)
            for rec in rows_data:
                if rec['record_id'] in seen_ids:
                    raise RuntimeError('Duplicate recording across pages; Atlas data changed during scan')
                seen_ids.add(rec['record_id'])
                is_new, is_updated = save_or_update_record(conn, rec, i)
                if is_new: page_new += 1
                if is_updated: page_updated += 1

            print(f" - Halaman {i}/{total_pages}: {page_new} Baru, {page_updated} Diupdate")

            # Stop only when Atlas explicitly marks the next page disabled.
            next_item = page.locator('li.ant-pagination-next')
            if next_item.count() != 1:
                raise RuntimeError('Cannot verify final page: pagination control missing')
            next_btn = next_item.locator('button')
            if next_btn.count() != 1:
                raise RuntimeError('Cannot identify next page button')
            if ('ant-pagination-disabled' in (next_item.get_attribute('class') or '')
                    or next_item.get_attribute('aria-disabled') == 'true'
                    or next_btn.get_attribute('disabled') is not None):
                print('[*] Final page confirmed.')
                break

            try:
                first_row = page.locator("table tbody tr.ant-table-row").first
                first_id = first_row.get_attribute("data-row-key") if first_row.count() > 0 else ""

                next_btn2 = page.locator("li.ant-pagination-next button")
                if next_btn2.count() > 0:
                    next_btn2.first.click(timeout=5000, force=True)
                else:
                    next_btn.first.click(timeout=5000, force=True)

                changed = False
                for w in range(15):
                    time.sleep(1)
                    curr_first = page.locator("table tbody tr.ant-table-row").first
                    new_id = curr_first.get_attribute("data-row-key") if curr_first.count() > 0 else ""
                    if new_id != first_id:
                        changed = True
                        break

                if not changed:
                    raise RuntimeError("Page did not change after next; refusing incomplete sync")

            except Exception as e:
                raise RuntimeError("Pagination failed; no data sent") from e

        else:
            raise RuntimeError("Page limit reached before final page; no data sent")

        validate_source_totals(all_records, source_metadata)
        browser.close()

    print("\n[V] SINKRONISASI DARI ATLAS SELESAI!")
    if no_push:
        print(f"[DRY RUN] {len(seen_ids)} recordings read; no API requests sent")
    else:
        push_to_laravel_api(conn, seen_ids)

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
