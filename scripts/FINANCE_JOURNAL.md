# Finance journal

`/admin/finance` is a company-wide cash journal for admin/superadmin only. It starts empty and does not import invoices, payroll or recording revenue automatically. Amounts are whole Indonesian Rupiah.

- Income increases cash and operating profit; expenses reduce both.
- Capital/opening balance increases cash without increasing profit.
- Profit distribution reduces cash without being counted again as an operating expense.
- Analysis covers the selected calendar month, while the cash balance covers the whole journal. Table search/type filters do not change the monthly summary.
- Recipient shares must total exactly 100%; new distributions use the full positive profit with no reserve. Integer largest-remainder allocation conserves every Rupiah.
- Distribution confirmation only records the allocation, not a bank transfer. It saves an immutable snapshot and closes the month. Entries in closed months cannot be created, changed, moved or deleted; repeated distribution requests are rejected.
- A locked period row serializes changes and distribution. Distribution also locks the settings row, serializing distributions across months. Settings changes affect future allocations, not closed snapshots.
- Journal deletion is soft deletion. Creator/updater and period closing actor are stored.

Deployment: run `php artisan migrate --force` before publishing the new navigation/page. Build frontend assets with `npm run build`.

Validation: `php vendor/phpunit/phpunit/phpunit tests/Feature/FinanceJournalTest.php`.

The journal uses inline AJAX autosave after a complete row is edited. UUID draft keys prevent duplicate creates on retries, revisions reject stale edits, and failed/incomplete rows stay visible. Closed historical distribution snapshots are retained.
