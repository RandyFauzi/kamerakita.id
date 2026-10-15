# Public portfolio — /portofolio

## Scope

Public Laravel page in Indonesian and English, using the existing locale-switch endpoint. Typography, blue/yellow palette, logo, and POV illustrations follow the home and vendor pages. The contact form validates a brief and opens the existing business WhatsApp contact for the visitor to review/send; it does not send messages or store lead data automatically.

## Evidence and metric definitions

Snapshot date: 15 October 2026. Public values are curated in `config/portfolio.php`; no per-visitor database query or private API credential is exposed.

- Atlas screenshot supplied by the user: 2,529h 11m recorded; 1,873h 35m approved. Ratio: 74.1% of recorded duration. These are not 74.1% of reports or hours delivered to a client.
- Read-only production MCP aggregate queries: 529 registered worker-role contributors; 1,174 manual reports; 209,678 reported minutes; 43,716 approved minutes. These reports can overlap Atlas recordings and must not be added to Atlas totals.
- Reported hours by submission month: July 445.42, August 385.52, September 1,162.33, October 1,501.37 through 15 October. The chart is explicitly labeled reported hours; October is partial.
- Minute Data project reports: 38,563 submitted minutes (642h 43m), 32,447 approved minutes (540h 47m).
- 2,000+ hours/month is an explicitly labeled commercial scaling target, subject to scope/onboarding/pilot, not an achieved historical monthly capacity.
- Existing public POV illustrations are used as scenario illustrations, not confidential client footage. Application names denote experience, not endorsement or official partnership.

Update the snapshot values, translations containing exact numbers, and source date together when refreshing metrics. Both translations must remain structurally identical.

## Files and deployment

New: controller, portfolio config, Blade view, CSS, and `lang/id/portfolio.php` / `lang/en/portfolio.php`. Routes are public GET `/portofolio` and throttled POST `/portofolio/contact`. The shared landing footer links to the new page.

No database migration, Node build, or paid integration is needed. Keep existing files and assets. Merge only the two new routes into the server's current route file; preserve other server changes. If Laravel config/routes/views are cached, refresh them through the normal deployment process after upload.

The upload patch archive uses the server's existing route/footer files as its baseline, with only the portfolio additions. Original server files are backed up locally under `.codex/portfolio-remote-backup.json`.

## Checks

`php vendor/phpunit/phpunit/phpunit tests/Feature/PortfolioTest.php --no-progress`

Guest access in both languages, matching translation keys, verified metric formatting, contact validation, encoded WhatsApp handoff, and rendering without a database connection. Browser checks at desktop and 320px/390px mobile cover overflow, images, language switching, and content layout. No actual contact message was sent.
