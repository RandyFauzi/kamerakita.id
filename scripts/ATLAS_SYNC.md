# Atlas recording sync

Canonical bot: `scripts/atlas_sync.py`. `auto_sync.bat` calls this versioned script. The previous script in the Antigravity scratch directory is a compatibility launcher pointing here. Session state and the dedicated `atlas_sync_cache.db` stay in that scratch directory; `ATLAS_SYNC_DATA_DIR` overrides it. The Excel exporter's `atlas_master.db` is not used by the bot. An OS file lock prevents overlapping scheduled/manual syncs.

## Fast capture and CSV export

The Export CSV link currently exposed to this Atlas account is `/participants/export`. It contains **rounded totals per participant**, not individual recordings. It lacks recording IDs, task names, timestamps, and a Deferred column. Importing those rows as recordings would invent data and lose the existing recording history.

The bot therefore logs in once, downloads the unfiltered Participants CSV, then closes the browser. Three concurrent authenticated HTTP workers read the precise server-rendered recording props; there are no browser Next clicks or per-page browser waits. It checks every page's row count, unique IDs, first-page stability, and exact overall source totals. It then checks account counts and totals against the CSV by normalized email, allowing only the CSV's 0.01-hour rounding. Blank CSV metrics are accepted only when all account metrics are blank (an account with no recordings).

The CSV verifies the result; **precise per-record source values** remain the database input. No rounded account totals are distributed or guessed across recordings. Any validation failure stops API delivery. The existing API and web pages remain unchanged; no new server migration is needed for this capture change.

## Changes

- Read every data page over HTTP and verify the complete source count before declaring a complete scan. Unexpected page numbers, incomplete rows, duplicate IDs, changed totals, or mismatched CSV account totals abort delivery.
- Update all recording metadata and duration fields, including corrections to email, task, timestamp, total seconds, and small changes in hours.
- Send only IDs observed in the current complete scan, not every historical cache entry. Source deletions are not propagated automatically, because visibility/filter changes do not prove deletion.
- Preserve explicit Billable, Rejected, Pending, and Deferred values from precise source props. Never classify the remainder of total duration as rejected. Six decimal places are retained in minute conversion; rounded visible table values and account CSV values never replace precise recording durations.
- Accept Atlas's displayed UTC dates and ISO timestamps; normalize to UTC explicitly.
- API validates each batch before writing and wraps it in one transaction. Missing duration categories are rejected rather than guessed. Source status is stored separately from the internal summary status. Recording ID identifies the same recording even if participant email is corrected; existing duplicate IDs fail for manual reconciliation rather than silently picking one.
- Acknowledgement count must match the batch. Transient transport/server failures retry up to three attempts. Any unacknowledged batch causes a nonzero exit; earlier acknowledged batches remain saved and can safely be retried.
- Recording pages no longer mutate rejected durations or delete rows on GET. Deferred has its own column/label. Payment behavior is outside this change.
- Legacy `atlas:sync` HTTP scraper is disabled and removed from Laravel scheduling because it writes the old table schema. Windows scheduling should invoke `auto_sync.bat --scheduled`.

## Preparation before any real delivery

For a new installation, deploy a backend that supports the recording API and deferred fields before enabling delivery:

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
