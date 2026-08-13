# Plan: Mailbox Code Review Audit

## Architecture & Scope
Target Application: Laravel project with Mailbox functionality (`kamerakita.id-main`).
Audit Goal: Comprehensive read-only code review of Mailbox feature (coding standards, performance/DB/services/crons, UI/UX/AlpineJS/TailwindCSS, variable handling).

## Work Breakdown Structure (Milestones)

### M1: Backend & DB Performance Exploration
- Identify all Mailbox controllers, routes, models, services (`ProcessCatchAllEmailService`), jobs, commands/cron tasks, events, listeners.
- Audit Eloquent queries (N+1 queries, indexing, eager loading, query efficiency).
- Audit background jobs / IMAP processing / memory management in `ProcessCatchAllEmailService` and cron jobs.
- Audit coding standards, type safety, error handling, variable handling, best practices.

### M2: Frontend UI/UX & AlpineJS Exploration
- Identify all Mailbox Blade views, components, layouts.
- Audit AlpineJS state management, reactive variables, event listeners, memory leaks/unbound state.
- Audit TailwindCSS styling, layout readability, UI responsiveness, UX workflow efficiency.
- Audit variable handling and data binding between backend and frontend.

### M3: Report Generation & Verification
- Compile findings into `mailbox_audit_report.md` at root directory (`c:\laragon\www\kamerakita.id-main\mailbox_audit_report.md`).
- Categorize every issue by severity: Low, Medium, High, Critical.
- Provide specific, clean code snippets for recommended fixes.
- Verify report completeness via Reviewer subagent.
- Notify Project Sentinel upon completion.

## Constraints
- Strictly READ-ONLY for source code.
- Write output to `mailbox_audit_report.md`.
