# Atlas recording sync

Canonical bot: `scripts/atlas_sync.py`. `auto_sync.bat` now calls this versioned script. The previous script in the Antigravity scratch directory is a compatibility launcher pointing here, with a backup of the original beside it. Default SQLite/session paths stay in that scratch directory; `ATLAS_SYNC_DATA_DIR` overrides the state directory.

## Changes

- Re-read all pages; confirm the next-page control is disabled before declaring a complete scan. Repeated pages, malformed rows, pagination failures, or the 1,000-page safety limit abort delivery.
- Update all recording metadata and duration fields, including corrections to email, task, timestamp, total seconds, and small changes in hours.
- Send only IDs observed in the current complete scan, not every historical cache entry. Source deletions are not propagated automatically, because visibility/filter changes do not prove deletion.
- Preserve explicit Billable, Rejected, Pending, and Deferred values. Never classify the remainder of total duration as rejected. Six decimal places are retained in minute conversion; precision already rounded by the Atlas UI cannot be recovered.
- Accept Atlas's displayed UTC dates and ISO timestamps; normalize to UTC explicitly.
- API validates each batch before writing and wraps it in one transaction. Missing duration categories are rejected rather than guessed. Source status is stored separately from the internal summary status. Recording ID identifies the same recording even if participant email is corrected; existing duplicate IDs fail for manual reconciliation rather than silently picking one.
- Acknowledgement count must match the batch. Transient transport/server failures retry up to three attempts. Any unacknowledged batch causes a nonzero exit; earlier acknowledged batches remain saved and can safely be retried.
- Recording pages no longer mutate rejected durations or delete rows on GET. Deferred has its own column/label. Payment behavior is outside this change.
- Legacy `atlas:sync` HTTP scraper is disabled and removed from Laravel scheduling because it writes the old table schema. Windows scheduling should invoke `auto_sync.bat --scheduled`.

## Preparation before any real delivery

No recording delivery or operational migration was run as part of this fix. A separate no-push scan confirmed login and reading 575 recordings before being stopped because the server still used the old API. Deploy the backend and migration together before enabling the new bot:

1. Apply the new migration `2026_10_15_020000_add_deferred_minutes_to_atlas_tasks.php` through the normal deployment workflow.
2. Set the same nonempty `ATLAS_BOT_TOKEN` in the backend environment and bot process environment; refresh the backend config cache if used. No hardcoded token fallback remains.
3. Set `ATLAS_EMAIL` / `ATLAS_PASSWORD` on the bot host if the existing saved session needs a new login.
4. `ATLAS_SYNC_URL` overrides the existing default destination `https://kamerakitaid.site/api/bot/atlas/sync`. Confirm destination and deployment readiness before running.

Historical misclassified rejected values are not rewritten automatically; they are corrected when those recordings are successfully read and delivered again. A normal scan does not delete data missing from Atlas's visible list.

`--no-push` prevents API delivery but still opens Atlas and updates the local cache. It is not an offline test.

After deploying this commit, run the following on the web server through its normal terminal/deployment workflow:

```sh
php artisan migrate --force
php artisan config:clear
php artisan config:cache
```

Set `ATLAS_BOT_TOKEN` in the server `.env` before rebuilding the config cache. On the Windows bot host, set the same value as an environment variable available to the process running `auto_sync.bat`. Server `.env` values are not automatically available to Python on Windows. Saved sessions are reused; `ATLAS_EMAIL` and `ATLAS_PASSWORD` are needed if login must be refreshed. Do not commit tokens, passwords, SQLite caches, or session files.

## Offline verification

Use the Python interpreter path recorded in `graphify-out/.graphify_python`:

```powershell
$atlasPython = (Get-Content graphify-out/.graphify_python -Raw).Trim()
& $atlasPython -m unittest discover -s tests -p test_atlas_sync_bot.py
php vendor/phpunit/phpunit/phpunit tests/Feature/AtlasRecordingSyncTest.php --no-progress
```

Tests use SQLite memory databases and mocked HTTP only. They do not launch Playwright, log in, or deliver real records.
