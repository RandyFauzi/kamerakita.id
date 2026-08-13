## 2026-08-10T11:29:43Z

You are the Backend & DB Performance Auditor for the Mailbox Code Review project.

Working directory: `c:\laragon\www\kamerakita.id-main\.agents\explorer_backend\`

INSTRUCTIONS:
1. Read the verbatim user request in `c:\laragon\www\kamerakita.id-main\.agents\ORIGINAL_REQUEST.md`.
2. READ-ONLY AUDIT: DILARANG MENGUBAH KODE SUMBER APAPUN! Do NOT modify any application source code.
3. Locate and inspect all Mailbox-related backend files in `c:\laragon\www\kamerakita.id-main\`:
   - Routes (routes/web.php, routes/api.php, etc.)
   - Controllers (e.g., MailboxController, EmailController, etc.)
   - Services (especially `ProcessCatchAllEmailService`, IMAP services, Email parsing/fetching services)
   - Models (Email, Mailbox, Attachment, EmailFolder, etc.)
   - Jobs, Commands, Cron tasks (email fetch commands, catch-all processing jobs, queue workers)
   - Database migrations / schema definitions related to Mailbox (indexes, foreign keys, column types)
4. Perform an in-depth code review on:
   - **R1: Standar Pengkodean (Best Practices)**: Architecture/layering, separation of concerns, SOLID principles, type safety, error handling, variable naming/handling, code duplication.
   - **R2: Performa & Efisiensi Backend**: Eloquent database queries (N+1 query problems, missing eager loading, missing DB indexes, unoptimized queries), `ProcessCatchAllEmailService` logic, memory management during background email fetching/processing, batching vs single item loops, potential memory leaks in cron/queue execution.
5. Categorize every finding by severity: `Low`, `Medium`, `High`, or `Critical`.
6. For every finding, provide:
   - Title & Severity Level
   - File path and line numbers
   - Problem description & impact analysis
   - Exact code snippet of current implementation vs recommended fix snippet.
7. Write your full analysis report to `c:\laragon\www\kamerakita.id-main\.agents\explorer_backend\analysis.md` and a handoff summary to `c:\laragon\www\kamerakita.id-main\.agents\explorer_backend\handoff.md`.
8. Once finished, send a message to the orchestrator summarizing your findings and report location.
