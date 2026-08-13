# Context: Mailbox Code Review Project

## Overview
Project: Mailbox Code Review (`kamerakita.id-main`)
Root Path: `c:\laragon\www\kamerakita.id-main\`
Report Output: `c:\laragon\www\kamerakita.id-main\mailbox_audit_report.md`

## Requirements Mapping
- **R1: Coding Standards Analysis (Best Practices)**
  - File structure, route design, controller design, SOLID principles, type declarations, error handling, variable handling.
- **R2: Performance & Backend Efficiency**
  - Eloquent DB queries, N+1 detection, missing indexes, `ProcessCatchAllEmailService`, IMAP processing, background process memory management, cron jobs.
- **R3: UI/UX & Frontend Logic**
  - AlpineJS state management, reactive variables, TailwindCSS styling & readability, UX responsiveness, data binding hygiene.
- **R4: Laporan Audit Markdown**
  - Structured documentation in `mailbox_audit_report.md` with Low, Medium, High, Critical severities and exact before/after code snippets for fixes.

## Strict Rule
- READ-ONLY audit. Do NOT modify any application source code files.
