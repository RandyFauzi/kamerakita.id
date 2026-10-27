"""Offline tests: no Playwright launch, real database, or HTTP delivery."""
import importlib.util
import json
import sqlite3
import tempfile
import unittest
from pathlib import Path
from unittest.mock import Mock, patch

spec = importlib.util.spec_from_file_location('atlas_sync', Path(__file__).parents[1] / 'scripts' / 'atlas_sync.py')
bot = importlib.util.module_from_spec(spec)
spec.loader.exec_module(bot)


class BotTest(unittest.TestCase):
    def export_csv(self, rows):
        import csv, io
        output = io.StringIO(newline='')
        writer = csv.writer(output)
        writer.writerow(['Email', 'Recordings', 'Duration (h)', 'Billable (h)', 'Rejected (h)', 'Pending (h)'])
        writer.writerows(rows)
        return ('\ufeff' + output.getvalue()).encode('utf-8')

    def test_export_matches_email_counts_and_rounded_hours_without_replacing_details(self):
        accounts = bot.parse_participant_export(self.export_csv([
            ['WORKER@example.com', '1', '1.00', '0.50', '0.00', '0.00'],
            ['inactive@example.com', '', '', '', '', '']]))
        original = dict(self.record)
        bot.validate_account_export([self.record], accounts)
        self.assertEqual(original, self.record)
        self.assertEqual(0.5, self.record['deferred_hours'])
        with self.assertRaises(ValueError):
            bot.validate_account_export([dict(self.record, duration_sec=3660)], accounts)
        with self.assertRaises(ValueError):
            bot.validate_account_export([dict(self.record, participant='outside@example.com')], accounts)
        with self.assertRaises(ValueError):
            bot.validate_account_export([self.record, self.record], accounts)

    def test_invalid_or_partial_export_never_passes_validation(self):
        for payload in [b'<html>Login</html>', self.export_csv([]),
                        self.export_csv([['worker@example.com', '1', 'nan', '0', '0', '0']]),
                        self.export_csv([['worker@example.com', '-1', '1', '0', '0', '0']]),
                        self.export_csv([['worker@example.com', '1', '1', '0', '0', '0']] * 2)]:
            with self.subTest(payload=payload[:60]), self.assertRaises(ValueError):
                bot.parse_participant_export(payload)

    def test_http_capture_rejects_partial_page_before_delivery(self):
        response = Mock()
        response.url = bot.BASE_URL + '/recordings'
        response.read.return_value = b'placeholder'
        metadata = dict(total=3, page=1, page_size=2, summary={})
        def parse(html, visible_ids=None, metadata=None):
            metadata.update(total=3, page=1, page_size=2, summary={})
            return [self.record]  # Two records required on this page.
        with patch.object(bot.urllib.request, 'urlopen') as request, patch.object(bot, 'process_source_html', side_effect=parse):
            request.return_value.__enter__.return_value = response
            with self.assertRaisesRegex(ValueError, 'Incomplete Atlas data page'):
                bot.capture_recordings_fast('offline-test-cookie')

    def test_parallel_http_capture_reads_all_rows_and_checks_export_total(self):
        from urllib.parse import urlparse, parse_qs
        summary = dict(recordingCount=3, durationHours='3', billableHours='1.5', rejectedHours='0', pendingHours='0', deferredHours='1.5')
        records = [dict(self.record, record_id=str(i) * 24) for i in range(1, 4)]
        def urlopen(request, timeout):
            number = int(parse_qs(urlparse(request.full_url).query)['page'][0])
            response = Mock(url=request.full_url)
            response.read.return_value = str(number).encode()
            response.__enter__ = Mock(return_value=response)
            response.__exit__ = Mock(return_value=False)
            return response
        def parse(html, visible_ids=None, metadata=None):
            number = int(html)
            metadata.update(total=3, page=number, page_size=2, summary=summary)
            return records[(number-1)*2:number*2]
        with patch.object(bot.urllib.request, 'urlopen', side_effect=urlopen), patch.object(bot, 'process_source_html', side_effect=parse):
            self.assertEqual(records, bot.capture_recordings_fast('offline', expected_count=3))
            with self.assertRaisesRegex(ValueError, 'export total'):
                bot.capture_recordings_fast('offline', expected_count=4)

    def test_explicit_process_setting_has_priority(self):
        with patch.dict(bot.os.environ, {'ATLAS_TEST_SETTING': 'process-value'}):
            self.assertEqual('process-value', bot.setting('ATLAS_TEST_SETTING'))

    @unittest.skipUnless(bot.os.name == 'nt', 'Windows Task Scheduler configuration')
    def test_windows_saved_setting_is_available_to_stale_scheduler_environment(self):
        import winreg
        with patch.dict(bot.os.environ, {'ATLAS_TEST_SETTING': ''}):
            with patch.object(winreg, 'QueryValueEx', return_value=('saved-value', winreg.REG_SZ)):
                self.assertEqual('saved-value', bot.setting('ATLAS_TEST_SETTING'))
            with patch.object(winreg, 'QueryValueEx', side_effect=FileNotFoundError):
                self.assertIsNone(bot.setting('ATLAS_TEST_SETTING'))

    def test_simultaneous_sync_is_blocked_and_lock_releases_after_failure(self):
        with tempfile.TemporaryDirectory() as directory, patch.object(bot, 'DB_PATH', str(Path(directory) / 'sync.db')):
            with self.assertRaisesRegex(RuntimeError, 'test failure'):
                with bot.single_run_lock():
                    with self.assertRaisesRegex(RuntimeError, 'masih berjalan'):
                        with bot.single_run_lock():
                            self.fail('Second sync entered the lock')
                    raise RuntimeError('test failure')
            with bot.single_run_lock():
                pass

    def test_cache_is_separate_from_excel_and_reader_does_not_block_save(self):
        self.assertEqual('atlas_sync_cache.db', Path(bot.DB_PATH).name)
        with tempfile.TemporaryDirectory() as directory, patch.object(bot, 'DB_PATH', str(Path(directory) / 'sync.db')):
            with bot.closing(bot.init_db()) as writer, bot.closing(sqlite3.connect(bot.DB_PATH)) as reader:
                self.assertEqual('wal', writer.execute('PRAGMA journal_mode').fetchone()[0])
                self.assertEqual(60000, writer.execute('PRAGMA busy_timeout').fetchone()[0])
                bot.save_or_update_record(writer, self.record, 1)
                reader.execute('BEGIN')
                reader.execute('SELECT * FROM recordings').fetchall()
                changed = dict(self.record, duration_sec=4000)
                self.assertEqual((False, True), bot.save_or_update_record(writer, changed, 1))
                reader.rollback()

    def test_sync_failure_closes_database_and_releases_lock(self):
        with tempfile.TemporaryDirectory() as directory, patch.object(bot, 'DB_PATH', str(Path(directory) / 'sync.db')):
            connection = bot.init_db()
            with patch.object(bot, 'init_db', return_value=connection), patch.object(bot, '_sync_atlas', side_effect=RuntimeError('scan failed')):
                with self.assertRaisesRegex(RuntimeError, 'scan failed'):
                    bot.sync_atlas(no_push=True)
            with self.assertRaises(sqlite3.ProgrammingError):
                connection.execute('SELECT 1')
            with bot.single_run_lock():
                pass

    def test_scan_must_match_atlas_count_and_summary_before_delivery(self):
        record = dict(duration_sec=3600, billable_hours=0.5, rejected_hours=0,
                      pending_hours=0.2, deferred_hours=0.3)
        metadata = dict(total=1, summary=dict(recordingCount=1, durationHours='1',
                        billableHours='0.5', rejectedHours='0', pendingHours='0.2', deferredHours='0.3'))
        bot.validate_source_totals([record], metadata)
        with self.assertRaises(ValueError):
            bot.validate_source_totals([], metadata)
        record['rejected_hours'] = 0.3
        with self.assertRaises(ValueError):
            bot.validate_source_totals([record], metadata)

    def test_raw_source_preserves_precision_and_rejects_stale_table(self):
        source = dict(userTaskId='a' * 24, taskTitle='Task', participantEmail='WORKER@example.com',
                      recordedAt='2026-10-15T16:57:17.150Z', durationSeconds='1215.43',
                      processingStatus='processing', reconciliationStatus='ok',
                      tileBillableHours='0.123456', tileRejectedHours='0',
                      tilePendingHours='0', tileDeferredHours='0.214163')
        flight = '0:' + json.dumps(['$', {'recordings': [source]}]) + '\n'
        html = '<script>self.__next_f.push(' + json.dumps([1, flight]) + ')</script>'
        rec = bot.process_source_html(html, ['a' * 24])[0]
        self.assertEqual(1215.43, rec['duration_sec'])
        self.assertEqual(0.123456, rec['billable_hours'])
        self.assertEqual(0.214163, rec['deferred_hours'])
        self.assertEqual(0, rec['rejected_hours'])
        self.assertEqual('2026-10-15T16:57:17.150000+00:00', rec['recorded_utc'])
        with self.assertRaises(ValueError):
            bot.process_source_html(html, ['b' * 24])
        with self.assertRaises(ValueError):
            bot.process_source_html('<html>Login</html>', ['a' * 24])

    def setUp(self):
        self.conn = sqlite3.connect(':memory:')
        self.conn.execute('''CREATE TABLE recordings (
            record_id TEXT PRIMARY KEY, task TEXT, participant TEXT, recorded_utc TEXT,
            duration_sec REAL, status TEXT, billable_hours REAL, rejected_hours REAL,
            pending_hours REAL, deferred_hours REAL, reconciliation TEXT,
            first_scraped_at TEXT, last_updated_at TEXT)''')
        self.record = dict(record_id='a' * 24, task='Original', participant='worker@example.com',
                           recorded_utc='2026-10-15T00:00:00+00:00', duration_sec=3600, status='Review',
                           billable_hours=0.5, rejected_hours=0, pending_hours=0,
                           deferred_hours=0.5, reconciliation='')

    def tearDown(self):
        self.conn.close()

    def test_metadata_and_small_changes_update(self):
        self.assertEqual((True, False), bot.save_or_update_record(self.conn, self.record, 1))
        self.record.update(task='Corrected', participant='other@example.com', duration_sec=3660,
                           recorded_utc='2026-10-16T00:00:00+00:00', billable_hours=0.504)
        self.assertEqual((False, True), bot.save_or_update_record(self.conn, self.record, 1))
        actual = bot.build_payload_records(self.conn, {self.record['record_id']})[0]
        self.assertEqual('Corrected', actual['task'])
        self.assertEqual(61, actual['duration_min'])
        self.assertEqual(30.24, actual['billable_min'])
        self.assertEqual('other@example.com', actual['participant'])
        self.assertEqual((False, False), bot.save_or_update_record(self.conn, self.record, 1))

    def test_deferred_and_unallocated_duration_are_never_rejected(self):
        bot.save_or_update_record(self.conn, self.record, 1)
        actual = bot.build_payload_records(self.conn, {self.record['record_id']})[0]
        self.assertEqual(0, actual['rejected_min'])
        self.assertEqual(30, actual['deferred_min'])
        self.record['deferred_hours'] = 0
        bot.save_or_update_record(self.conn, self.record, 1)
        self.assertEqual(0, bot.build_payload_records(self.conn, {self.record['record_id']})[0]['rejected_min'])

    def test_only_current_scan_records_are_sent(self):
        bot.save_or_update_record(self.conn, self.record, 1)
        self.assertEqual([], bot.build_payload_records(self.conn, set()))

    def test_hours_parse_preserves_precision_and_rejects_bad_values(self):
        self.assertEqual(0.504, bot.parse_hours('Billable 0.504 h Deferred 0,5 h', 'Billable'))
        self.assertEqual(0.5, bot.parse_hours('Billable 0.504 h Deferred 0,5 h', 'Deferred'))
        with self.assertRaises(ValueError):
            bot.parse_hours('Billable -- h', 'Billable')

    def test_bad_acknowledgement_fails_instead_of_reporting_success(self):
        bot.save_or_update_record(self.conn, self.record, 1)
        with patch.dict(bot.os.environ, {'ATLAS_BOT_TOKEN': 'offline-test'}), patch.object(bot.urllib.request, 'urlopen') as request:
            request.return_value.__enter__.return_value.read.return_value = b'{"status":"success","count":0}'
            with self.assertRaises(RuntimeError):
                bot.push_to_laravel_api(self.conn, {self.record['record_id']})

    def test_main_returns_nonzero_on_failure(self):
        with patch.object(bot.sys, 'argv', ['atlas_sync.py']), patch.object(bot, 'sync_atlas', side_effect=RuntimeError('offline test')):
            self.assertEqual(1, bot.main())

    def test_timestamp_formats_match_cached_atlas_display_and_explicit_offsets(self):
        self.assertEqual('2026-10-02T14:50:00+00:00', bot.normalize_timestamp('Oct 2, 2026, 2:50 PM UTC'))
        self.assertEqual('2026-10-14T18:00:00+00:00', bot.normalize_timestamp('2026-10-15T01:00:00+07:00'))
        with self.assertRaises(ValueError):
            bot.normalize_timestamp('invalid time')

    def test_scraper_extracts_actual_display_format_without_converting_deferred_to_rejected(self):
        values = ['Task', 'WORKER@example.com', 'Oct 2, 2026, 2:50 PM UTC', '3600',
                  'Review', '-', 'Billable 0.504 h Deferred 0.496 h', '-', 'Awaiting review']
        cells = []
        for value in values:
            cell = Mock()
            cell.inner_text.return_value = value
            cells.append(cell)
        row = Mock()
        row.get_attribute.return_value = 'a' * 24
        row.locator.return_value.all.return_value = cells
        page = Mock()
        page.locator.return_value.all.return_value = [row]
        actual = bot.process_table_rows(page)[0]
        self.assertEqual('worker@example.com', actual['participant'])
        self.assertEqual(0.496, actual['deferred_hours'])
        self.assertEqual(0, actual['rejected_hours'])
        self.assertEqual('2026-10-02T14:50:00+00:00', actual['recorded_utc'])
        cells[6].inner_text.return_value = 'Loading...'
        with self.assertRaises(ValueError):
            bot.process_table_rows(page)

    def test_empty_table_aborts_instead_of_sending_cached_records(self):
        page = Mock()
        page.locator.return_value.all.return_value = []
        with self.assertRaises(RuntimeError):
            bot.process_table_rows(page)


if __name__ == '__main__':
    unittest.main()
