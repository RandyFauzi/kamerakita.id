# Graph Report - kamerakita.id-main  (2026-10-07)

## Corpus Check
- Large corpus: 533 files · ~869,462 words. Semantic extraction will be expensive (many Claude tokens). Consider running on a subfolder.

## Summary
- 1728 nodes · 3139 edges · 287 communities (47 shown, 240 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 71 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Module 0
- Module 1
- Module 2
- Module 3
- Module 4
- Module 5
- Module 6
- Module 7
- Module 8
- Module 9
- Module 11
- Module 12
- Module 13
- Module 14
- Module 15
- Module 16
- Module 17
- Module 18
- Module 19
- Module 20
- Module 21
- Module 22
- Module 23
- Module 24
- Module 25
- Module 26
- Module 27
- Module 28
- Module 29
- Module 30
- Module 31
- Module 32
- Module 33
- Module 34
- Module 35
- Module 36
- Module 37
- Module 38
- Module 39
- Module 40
- Module 41
- Module 42
- Module 45
- Module 46
- Module 47
- Module 48
- Module 49
- Module 50
- Module 51
- Module 52
- Module 53
- Module 54
- Module 55
- Module 56
- Module 57
- Module 58
- Module 59
- Module 60
- Module 61
- Module 62
- Module 63
- Module 64
- Module 65
- Module 66
- Module 67
- Module 68
- Module 69
- Module 70
- Module 71
- Module 72
- Module 73
- Module 74
- Module 75
- Module 76
- Module 77
- Module 78
- Module 79
- Module 80
- Module 81
- Module 83
- Module 84
- Module 85
- Module 86
- Module 87
- Module 88
- Module 89
- Module 90
- Module 93
- Module 94
- Module 95
- Module 96
- Module 97
- Module 98
- Module 99
- Module 100
- Module 101
- Module 102
- Module 103
- Module 104
- Module 105
- Module 106
- Module 107
- Module 108
- Module 109
- Module 110
- Module 112
- Module 113
- Module 114
- Module 115
- Module 116
- Module 117
- Module 118
- Module 119
- Module 120
- Module 122
- Module 123
- Module 124
- Module 125
- Module 126
- Module 127
- Module 128
- Module 129
- Module 130
- Module 131
- Module 132
- Module 133
- Module 134
- Module 135
- Module 136
- Module 137
- Module 138
- Module 140
- Module 142
- Module 154
- Module 155

## God Nodes (most connected - your core abstractions)
1. `User` - 143 edges
2. `VideoWorkReport` - 132 edges
3. `Partner` - 131 edges
4. `Controller` - 63 edges
5. `TestCase` - 44 edges
6. `CapturedEmail` - 27 edges
7. `Invoice` - 25 edges
8. `BaseTool` - 21 edges
9. `PeriodApproval` - 16 edges
10. `VerifyVideoWorkReportController` - 15 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --references--> `VideoWorkReport`  [EXTRACTED]
  app/Console/Commands/BackupEvidenceFilesToDatabase.php → app/Models/VideoWorkReport.php
- `{closure#1}()` --references--> `VideoWorkReport`  [EXTRACTED]
  app/Console/Commands/CheckEvidenceFiles.php → app/Models/VideoWorkReport.php
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  app/Console/Commands/CleanProductionDummyData.php → app/Models/User.php
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  app/Http/Controllers/Auth/RegisteredUserController.php → app/Models/User.php
- `{closure#2}()` --calls--> `VideoWorkReport`  [EXTRACTED]
  app/Http/Controllers/FilePondReportUploadController.php → app/Models/VideoWorkReport.php

## Import Cycles
- None detected.

## Communities (287 total, 240 thin omitted)

### Community 0 - "Module 0"
Cohesion: 0.07
Nodes (6): User, CapturedEmailPolicy, ActivityLogTest, PeriodApprovalTest, ProfileTest, RouteRoleProtectionTest

### Community 1 - "Module 1"
Cohesion: 0.07
Nodes (24): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}() (+16 more)

### Community 2 - "Module 2"
Cohesion: 0.09
Nodes (10): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordResetLinkController, {closure#1}(), RegisteredUserController (+2 more)

### Community 3 - "Module 3"
Cohesion: 0.08
Nodes (10): AuthenticationTest, PasswordUpdateTest, RegistrationTest, ExampleTest, FastworkOnboardingTest, NotifyInactiveWorkersTest, PartnerBulkUpdateTest, PayrollCalculationTest (+2 more)

### Community 4 - "Module 4"
Cohesion: 0.06
Nodes (7): patch_file(), patch_file(), patch_file(), patch_file(), patch_file(), patch_file(), patch_file()

### Community 5 - "Module 5"
Cohesion: 0.08
Nodes (7): AtlasBotController, MobileAuthController, FilePondReportUploadController, LocaleController, PushSubscriptionController, VendorController, GET /user()

### Community 6 - "Module 6"
Cohesion: 0.10
Nodes (9): VerifyVideoWorkReportAction, {closure#1}(), MigrateEvidenceFilesToPrivateStorage, {closure#10}(), {closure#11}(), {closure#9}(), VerifyVideoWorkReportController, PeriodApproval (+1 more)

### Community 7 - "Module 7"
Cohesion: 0.14
Nodes (6): {closure#1}(), ProcessSubmittedReport, SendWhatsAppMessageJob, SyncMailboxJob, AdminAnnouncementWebPush, CustomWebPushNotification

### Community 8 - "Module 8"
Cohesion: 0.07
Nodes (26): devDependencies, alpinejs, autoprefixer, concurrently, laravel-vite-plugin, postcss, tailwindcss, @tailwindcss/forms (+18 more)

### Community 9 - "Module 9"
Cohesion: 0.11
Nodes (5): MigrateInvoicesData, InvoiceController, Client, Invoice, InvoiceLifecycleTest

### Community 11 - "Module 11"
Cohesion: 0.14
Nodes (7): InvoiceItem, MailboxSyncState, MailboxUnmatchedEmail, McpAuditLog, NotificationCampaign, PasswordRecoveryToken, ReportPeriod

### Community 12 - "Module 12"
Cohesion: 0.08
Nodes (7): {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#2}()

### Community 13 - "Module 13"
Cohesion: 0.08
Nodes (6): {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#2}()

### Community 14 - "Module 14"
Cohesion: 0.09
Nodes (4): McpServerController, CreateCustomUserTool, FetchRecordsTool, QcStatsTool

### Community 15 - "Module 15"
Cohesion: 0.13
Nodes (5): BackupEvidenceFilesToDatabase, {closure#1}(), RestoreEvidenceFilesFromDatabase, EvidenceFileBackup, EvidenceFileBackupService

### Community 16 - "Module 16"
Cohesion: 0.12
Nodes (8): PushNotificationController, FastworkOnboardingController, ListPartnerPaymentHistoryController, ListPartnerReportHistoryController, GET /(), GET /jadi-vendor(), GET /onboarding/success(), GET /panduan()

### Community 17 - "Module 17"
Cohesion: 0.13
Nodes (5): {closure#15}(), {closure#1}(), Partner, CheckRecruiterMilestone, DatabaseSeeder

### Community 18 - "Module 18"
Cohesion: 0.17
Nodes (6): EnsureOnboardingCompleted, McpAuditLogger, McpAuthenticate, RoleMiddleware, {closure#1}(), SetLocale

### Community 19 - "Module 19"
Cohesion: 0.11
Nodes (4): ActivityLog, AtlasTask, AtlasWorker, RecruiterCommission

### Community 20 - "Module 20"
Cohesion: 0.16
Nodes (4): EventController, AppLayout, GuestLayout, MailboxLayout

### Community 21 - "Module 21"
Cohesion: 0.16
Nodes (4): MobileRecordingController, Category, ClientInvoice, Recording

### Community 22 - "Module 22"
Cohesion: 0.16
Nodes (3): PhoneHelper, PasswordRecoveryController, PasswordRecoveryRequest

### Community 23 - "Module 23"
Cohesion: 0.12
Nodes (3): AstroRecordingController, RekruterController, ActivityLogger

### Community 24 - "Module 24"
Cohesion: 0.12
Nodes (3): getNewRange(), mergeStatus(), up()

### Community 25 - "Module 25"
Cohesion: 0.12
Nodes (15): server, transport, author, dependencies, @modelcontextprotocol/sdk, description, keywords, license (+7 more)

### Community 28 - "Module 28"
Cohesion: 0.12
Nodes (3): BaseTool, CreateWorkerTool, TopPartnersTool

### Community 29 - "Module 29"
Cohesion: 0.17
Nodes (5): BackfillReferrals, CreateAdminUser, NotifyInactiveWorkers, SyncAtlasData, SyncPartnerActivityStatus

### Community 30 - "Module 30"
Cohesion: 0.17
Nodes (3): CleanExpiredEmailsCommand, MailboxController, CapturedEmail

### Community 31 - "Module 31"
Cohesion: 0.17
Nodes (3): RateCard, CategorySeeder, RateCardSeeder

### Community 32 - "Module 32"
Cohesion: 0.20
Nodes (4): TestEvidenceStorageWrite, {closure#2}(), {closure#1}(), StoreEvidenceImageService

### Community 34 - "Module 34"
Cohesion: 0.20
Nodes (4): {closure#1}(), {closure#2}(), RenderDashboardOverviewController, CalculatePartnerMetricsService

### Community 37 - "Module 37"
Cohesion: 0.21
Nodes (3): PartnerFactory, UserFactory, VideoWorkReportFactory

### Community 40 - "Module 40"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 41 - "Module 41"
Cohesion: 0.17
Nodes (12): require, doctrine/dbal, laravel/framework, laravel-notification-channels/webpush, laravel/sanctum, laravel/telescope, laravel/tinker, league/flysystem-aws-s3-v3 (+4 more)

### Community 42 - "Module 42"
Cohesion: 0.22
Nodes (3): PruneMailboxQuarantine, PullMailboxEmailsCommand, ProcessCatchAllEmailService

### Community 45 - "Module 45"
Cohesion: 0.24
Nodes (3): {closure#1}(), {closure#2}(), TelescopeServiceProvider

### Community 56 - "Module 56"
Cohesion: 0.25
Nodes (3): AppServiceProvider, {closure#1}(), {closure#2}()

### Community 57 - "Module 57"
Cohesion: 0.22
Nodes (9): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, platform, preferred-install, sort-packages (+1 more)

### Community 58 - "Module 58"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+1 more)

### Community 59 - "Module 59"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 60 - "Module 60"
Cohesion: 0.22
Nodes (8): background_color, description, display, icons, name, short_name, start_url, theme_color

### Community 66 - "Module 66"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 79 - "Module 79"
Cohesion: 0.38
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), down(), getConnection(), up()

### Community 84 - "Module 84"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 90 - "Module 90"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 138 - "Module 138"
Cohesion: 0.83
Nodes (3): compressFile(), handleFileAppend(), logDiag()

### Community 140 - "Module 140"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **89 isolated node(s):** `server`, `transport`, `name`, `version`, `description` (+84 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 698 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **240 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Module 0` to `Module 2`, `Module 3`, `Module 5`, `Module 7`, `Module 9`, `Module 10`, `Module 11`, `Module 14`, `Module 16`, `Module 17`, `Module 19`, `Module 28`, `Module 29`, `Module 31`, `Module 36`, `Module 37`, `Module 38`, `Module 42`, `Module 44`, `Module 45`, `Module 49`, `Module 50`, `Module 52`, `Module 61`, `Module 62`, `Module 63`, `Module 74`, `Module 76`, `Module 77`, `Module 80`, `Module 85`, `Module 122`?**
  _High betweenness centrality (0.136) - this node is a cross-community bridge._
- **What connects `server`, `transport`, `name` to the rest of the system?**
  _89 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Module 0` be split into smaller, more focused modules?**
  _Cohesion score 0.0707070707070707 - nodes in this community are weakly interconnected._
- **Why does `VideoWorkReport` connect `Module 6` to `Module 0`, `Module 3`, `Module 5`, `Module 7`, `Module 11`, `Module 14`, `Module 15`, `Module 16`, `Module 17`, `Module 19`, `Module 20`, `Module 21`, `Module 23`, `Module 32`, `Module 34`, `Module 35`, `Module 37`, `Module 38`, `Module 39`, `Module 43`, `Module 46`, `Module 47`, `Module 48`, `Module 51`, `Module 53`, `Module 54`, `Module 64`, `Module 71`, `Module 72`, `Module 73`, `Module 74`, `Module 75`, `Module 76`, `Module 81`, `Module 83`, `Module 85`, `Module 86`, `Module 89`?**
  _High betweenness centrality (0.098) - this node is a cross-community bridge._
- **Should `Module 1` be split into smaller, more focused modules?**
  _Cohesion score 0.07308970099667775 - nodes in this community are weakly interconnected._
- **Why does `Partner` connect `Module 17` to `Module 0`, `Module 2`, `Module 3`, `Module 5`, `Module 6`, `Module 10`, `Module 11`, `Module 14`, `Module 16`, `Module 19`, `Module 21`, `Module 23`, `Module 28`, `Module 29`, `Module 32`, `Module 34`, `Module 35`, `Module 37`, `Module 38`, `Module 39`, `Module 43`, `Module 46`, `Module 48`, `Module 50`, `Module 51`, `Module 52`, `Module 53`, `Module 54`, `Module 67`, `Module 68`, `Module 80`, `Module 82`, `Module 85`, `Module 86`, `Module 89`, `Module 123`?**
  _High betweenness centrality (0.069) - this node is a cross-community bridge._
- **Should `Module 2` be split into smaller, more focused modules?**
  _Cohesion score 0.08943089430894309 - nodes in this community are weakly interconnected._