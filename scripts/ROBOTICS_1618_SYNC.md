# 1618 recording connector

Run `sync_1618.bat` manually, or `sync_1618.bat --scheduled` from Windows Task Scheduler. For validation without API writes: `python scripts/robotics1618_sync.py --no-push`. `--force` resends a validated snapshot with a new import ID; it never bypasses the server freshness checks.

## Configuration

On the bot host set `ROBOTICS_1618_USERNAME`, `ROBOTICS_1618_PASSWORD`, and `ROBOTICS_1618_BOT_TOKEN` in the environment or Windows current-user environment settings. The token is independent from `ATLAS_BOT_TOKEN`. Optional settings: `ROBOTICS_1618_TENANT` (default `kamerakita-ai`), `ROBOTICS_1618_DATA_DIR` (default `%USERPROFILE%/.kamerakita/1618`), and `ROBOTICS_1618_SYNC_URL` (default `https://kamerakitaid.site/api/bot/1618/sync`). HTTPS is required. Credentials are never committed or logged.

On the server deploy the backend and run `php artisan migrate --force`; add the same `ROBOTICS_1618_BOT_TOKEN` to the server environment, then rebuild/clear Laravel configuration and view caches through the normal deployment process. Do not enable automatic delivery until this is done. The migration refuses to proceed if duplicate recording IDs already exist; it never deletes or arbitrarily merges historical data.

## Data flow and safeguards

- Authenticate with a saved cookie session. Download the officially exposed `recordings.csv` and `data.json` from the partner tenant. HTTP redirects are restricted to the same HTTPS origin.
- Compare metadata before/after download. Verify all required CSV columns, exact declared row count, unique 24-hex recording IDs, normalized emails, timezone-aware dates and export window. Decimal conversion retains six decimal places of minutes. Missing/invalid categories fail the entire import; no remainder is inferred as rejected. The `billable` flag is not treated as payment status.
- Use `recordings_csv.fetched_utc` as source observation time, not the download clock. `generated_utc` and the export window are retained in the import audit. The dashboard's export can cover only part of history; missing CSV rows never delete existing recordings. For historical backfill an actual historical export is required.
- Keep the last ten validated gzip CSV archives outside the repository and a local SQLite manifest/checkpoint. The snapshot hash, metadata and destination determine an import. Failed batches resume with the same import ID; exact completed snapshots skip delivery. A concurrent-run OS lock protects the local connector.
- POST batches of up to 500 to `/api/bot/1618/sync`, with a dedicated token header. Each batch is validated and committed atomically. Immutable import manifests and batch hashes provide replay protection; repeated IDs across batches are rejected. Count acknowledgements include stale rows explicitly skipped by the server. Completion is recorded only after every declared row/batch is accounted for.

## Shared recording identity and freshness

1618 currently publishes Atlas recording IDs. Both connectors therefore use the existing `atlas_tasks.atlas_task_id` as a **global unique identity**, regardless of participant email or incoming source. An identical ID updates one row; corrected email does not create another recording. A database unique index also prevents concurrent inserts from making duplicates.

The shared ingestion controller records `ingest_source`, `source_observed_at`, and extra source details (submitted/reviewed time, rejection reasons, pay period). A newer source observation may update an existing recording; an older observation is acknowledged as skipped. Atlas wins a timestamp tie. Existing legacy rows use `updated_at` conservatively until an explicit source observation is available. The Atlas bot now sends its observation timestamp as well. Existing payroll links and payment records are retained.

No new recording page: all existing admin, personal and vendor views/services continue reading the same models and team access rules. The shared recording view shows 1618 provenance where relevant. Atlas and 1618 must not be treated as additive totals for the same recording ID. Different IDs are not heuristically merged using email/task/date; similar recordings can be legitimate separate clips.

## Operations

Check `recording_import_runs` for expected counts, source snapshot time and completion; `recording_import_batches` records accepted/written/skipped counts. The bot host keeps `connector.log` in its private data directory (1 MiB, three rotated backups) with validation counts, snapshot hash, elapsed time and errors, without passwords or tokens. A partial import is visible as incomplete and can be safely retried. Source age and bot success are distinct: successfully downloading an old source snapshot does not make it fresh.

After deployment run setup_1618_schedule.ps1 from PowerShell. It checks server authentication and migration readiness first, then installs KameraKita_1618_Sync for 10:15 and 19:15 local Windows time. If the server returns HTTP 401/404/503 or an unexpected response, no task is changed. The separate connector runs under the current interactive user in a hidden window; Atlas's task is unchanged. Windows must be running, the user logged in, and internet available. Credentials never appear in task arguments.
