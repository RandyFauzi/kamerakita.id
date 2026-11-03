import csv
import importlib.util
import json
from pathlib import Path
import sqlite3
import sys
import tempfile
import unittest
from unittest.mock import Mock, patch

sys.path.insert(0, str(Path(__file__).parents[1] / 'scripts'))
spec = importlib.util.spec_from_file_location('robotics1618_sync', Path(__file__).parents[1] / 'scripts/robotics1618_sync.py')
bot = importlib.util.module_from_spec(spec)
spec.loader.exec_module(bot)


class Connector1618Test(unittest.TestCase):
    def setUp(self):
        self.directory = tempfile.TemporaryDirectory()
        self.path = Path(self.directory.name) / 'recordings.csv'
        self.row = dict(recording_id='a'*24, email='WORKER@example.com', task='Pack, suitcase\ncarefully',
            created_utc='2026-11-02T08:00:00Z', submitted_utc='2026-11-02T09:00:00Z', duration_s='1802.31',
            status='ready_for_review', billable_h='0.25', rejected_h='0', pending_h='0.000642', deferred_h='0.25',
            reviewed_utc='', reconciliation='ok', rejection_reasons='', pay_period_start='2026-10-16')
        self.metadata = dict(generated_utc='2026-11-03T01:00:00Z', recordings_csv=dict(rows=1,
            fetched_utc='2026-11-03T00:00:00Z', **{'from': '2026-10-16', 'to': '2026-11-15'}))

    def tearDown(self):
        self.directory.cleanup()

    def write(self, rows, columns=None):
        with self.path.open('w', encoding='utf-8-sig', newline='') as handle:
            writer = csv.DictWriter(handle, fieldnames=columns or list(self.row), extrasaction='ignore')
            writer.writeheader()
            writer.writerows(rows)

    def test_csv_preserves_decimal_duration_and_distinct_qa_categories(self):
        self.write([self.row])
        record = bot.parse_csv(self.path, self.metadata)[0]
        self.assertEqual('worker@example.com', record['participant'])
        self.assertEqual('30.038500', record['duration_min'])
        self.assertEqual('15.000000', record['deferred_min'])
        self.assertEqual('0.000000', record['rejected_min'])
        self.assertEqual(self.row['task'], record['task'])
        self.assertEqual('2026-11-02T08:00:00+00:00', record['recorded_utc'])

    def test_invalid_csv_is_rejected_before_delivery(self):
        for changes in [dict(duration_s='NaN'), dict(deferred_h=''), dict(rejected_h='-1'),
                        dict(recording_id='bad'), dict(email='bad'), dict(created_utc='2026-11-02T08:00:00'),
                        dict(created_utc='2026-09-01T00:00:00Z')]:
            self.write([dict(self.row, **changes)])
            with self.subTest(changes=changes), self.assertRaises(ValueError):
                bot.parse_csv(self.path, self.metadata)
        self.write([self.row], [c for c in self.row if c != 'deferred_h'])
        with self.assertRaises(ValueError):
            bot.parse_csv(self.path, self.metadata)

    def test_duplicate_and_truncated_exports_are_rejected(self):
        self.write([self.row, self.row])
        with self.assertRaisesRegex(ValueError, 'duplicate'):
            bot.parse_csv(self.path, self.metadata)
        self.write([])
        with self.assertRaisesRegex(ValueError, 'row count'):
            bot.parse_csv(self.path, self.metadata)

    def test_receipts_resume_same_import_and_completed_snapshot_skips_http(self):
        self.write([self.row])
        records = bot.parse_csv(self.path, self.metadata)
        with sqlite3.connect(':memory:') as conn, patch.object(bot.shared, 'setting', return_value='offline-token'), patch.object(bot.urllib.request, 'urlopen') as request:
            conn.execute('CREATE TABLE imports (snapshot_key TEXT PRIMARY KEY, id TEXT, completed INTEGER)')
            request.return_value.__enter__.return_value.read.return_value = b'{"status":"success","count":0}'
            with self.assertRaises(ValueError):
                bot.deliver(conn, records, self.metadata, 'b'*64, 'https://example.test/sync')
            original_id = conn.execute('SELECT id FROM imports').fetchone()[0]
            self.assertEqual(0, conn.execute('SELECT completed FROM imports').fetchone()[0])
            request.return_value.__enter__.return_value.read.return_value = b'{"status":"success","count":1,"written":1,"skipped":0}'
            bot.deliver(conn, records, self.metadata, 'b'*64, 'https://example.test/sync')
            payload = json.loads(request.call_args.args[0].data)
            self.assertEqual(original_id, payload['import']['id'])
            self.assertEqual('2026-11-03T00:00:00+00:00', payload['observed_at'])
            bot.deliver(conn, records, self.metadata, 'b'*64, 'https://example.test/sync')
            self.assertEqual(2, request.call_count)

    def test_authenticated_redirect_cannot_forward_session_outside_1618(self):
        with self.assertRaises(ValueError):
            bot.SameOriginRedirect().redirect_request(Mock(), None, 302, '', {}, 'https://other.example/login')

    def test_source_rate_limit_honors_retry_after_and_recovers(self):
        client = bot.SourceClient(Path(self.directory.name), 'kamerakita-ai')
        response = Mock(url=client.root + 'recordings.csv')
        error = bot.HTTPError(response.url, 429, 'Rate limited', {'Retry-After': '2'}, None)
        with patch.object(client.opener, 'open', side_effect=[error, response]), patch.object(bot.time, 'sleep') as sleep:
            self.assertIs(response, client.get('recordings.csv'))
            sleep.assert_called_once_with(2)


if __name__ == '__main__':
    unittest.main()
