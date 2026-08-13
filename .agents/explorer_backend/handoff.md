# Explorer Backend & DB Performance Handoff Report

## 1. Observation
Direct observations gathered from read-only audit of Mailbox backend components:

- **MailboxController.php** (`app/Http/Controllers/MailboxController.php:12`):
  `$emails = Auth::user()->capturedEmails()->orderBy('received_at', 'desc')->get();`
  Full dataset without pagination or attribute selection loaded into memory and embedded as monolithic JSON payload in Blade (`resources/views/mailbox/index.blade.php:310`).
- **ProcessCatchAllEmailService.php** (`app/Services/ProcessCatchAllEmailService.php:64`):
  `$user = User::where('email', $emailAddress)->first();` inside nested message iteration loop. Executes 1 SELECT query per message/recipient (N+1 query problem).
- **ProcessCatchAllEmailService.php** (`app/Services/ProcessCatchAllEmailService.php:24`):
  `$messages = $folder->query()->all()->get();` fetches entire IMAP INBOX into memory in a single collection call.
- **ProcessCatchAllEmailService.php** (Lines 14-128):
  Multiple `echo` statements mixed directly in service methods (`echo "🔍 Menghubungkan ke server IMAP Hostinger...\n";`).
- **Database Migrations** (`database/migrations/2026_08_09_101751_create_captured_emails_table.php` & `2026_08_09_152322_add_read_and_starred_to_captured_emails_table.php`):
  No indexes on `received_at`, `(user_id, received_at)`, `(user_id, is_read)`, or `(user_id, is_starred)`.
- **Console Routes** (`routes/console.php:15`):
  `Schedule::command('app:pull-mailbox-emails')->everyMinute();` lacking `withoutOverlapping()` protection.

## 2. Logic Chain
1. Calling `->get()` on `capturedEmails()` without pagination pulls all historical email bodies (including HTML markup) into PHP memory and client-side Blade DOM, which guarantees memory exhaustion (OOM) and browser lag as email count grows.
2. Executing `User::where(...)` inside a loop over IMAP messages performs N+1 database queries. Replacing this with an in-memory dictionary key-value lookup (`$userMap`) reduces query count from N to 1.
3. Querying all IMAP emails at once via `$folder->query()->all()->get()` without batching or `unseen()` filtering risks hitting PHP memory limits on large mailboxes.
4. Performing sorting (`orderBy('received_at', 'desc')`), filtering (`where('received_at', '<', ...)`), and deduplication (`firstOrCreate`) without database indexes forces MySQL to perform full table scans and filesort operations.
5. Scheduling an IMAP sync command every minute without `withoutOverlapping()` enables concurrent process stacking if an IMAP operation stalls or takes >60 seconds.

## 3. Caveats
- No live IMAP connection was initiated during this read-only audit to avoid modifying remote mailbox state.
- Production database row counts were not measured directly; analysis is based on code structure and standard database execution mechanics.
- Source code was strictly preserved without modification per audit mandate.

## 4. Conclusion
The Mailbox backend implementation has 9 identified findings, with **1 Critical** (unpaginated dataset load), **3 High** (N+1 query in IMAP processing, unchunked IMAP memory fetch, missing DB indexes), **3 Medium** (missing cron overlap protection, missing request validation, echo in service layer), and **2 Low** severity items.

Full details, current vs. recommended code snippets, and remediation steps are documented in `.agents/explorer_backend/analysis.md`.

## 5. Verification Method
To independently verify these findings:
1. **Paginasi & Memory Audit**: Inspect `app/Http/Controllers/MailboxController.php` line 12 and `resources/views/mailbox/index.blade.php` line 310. Check if `capturedEmails()` uses `paginate()` or `get()`.
2. **N+1 Query Inspection**: Inspect `app/Services/ProcessCatchAllEmailService.php` line 64 inside the `$messages` loop. Run `php artisan app:pull-mailbox-emails` with Laravel Query Log enabled (`DB::listen(...)`) to verify N SELECT queries executed.
3. **Database Index Check**: Inspect `database/migrations/` for `captured_emails` table to verify absence of composite indexes on `(user_id, received_at)` and `received_at`.
4. **Cron Overlapping Check**: View `routes/console.php` line 15 to confirm `withoutOverlapping()` is absent on `app:pull-mailbox-emails`.
