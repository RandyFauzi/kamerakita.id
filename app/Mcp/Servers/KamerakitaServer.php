<?php

namespace App\Mcp\Servers;

use App\Mcp\ServerTools\AggregateRecordsMcpTool;
use App\Mcp\ServerTools\AnomalyDetectorMcpTool;
use App\Mcp\ServerTools\AtlasRecordingSummaryMcpTool;
use App\Mcp\ServerTools\AutoReconcileProportionalMcpTool;
use App\Mcp\ServerTools\BatchApproveReportsMcpTool;
use App\Mcp\ServerTools\BatchReconcileByEmailMcpTool;
use App\Mcp\ServerTools\CreateCustomUserMcpTool;
use App\Mcp\ServerTools\CreateWorkerMcpTool;
use App\Mcp\ServerTools\FetchRecordsMcpTool;
use App\Mcp\ServerTools\FinanceSummaryMcpTool;
use App\Mcp\ServerTools\GetMcpContextMcpTool;
use App\Mcp\ServerTools\InvoiceSummaryMcpTool;
use App\Mcp\ServerTools\ListFastworkOnboardingsMcpTool;
use App\Mcp\ServerTools\ListMainAccountRequestsMcpTool;
use App\Mcp\ServerTools\ListPasswordRecoveriesMcpTool;
use App\Mcp\ServerTools\MailboxSummaryMcpTool;
use App\Mcp\ServerTools\MatchPayrollExcelMcpTool;
use App\Mcp\ServerTools\OperationsOverviewMcpTool;
use App\Mcp\ServerTools\PaymentCyclesSummaryMcpTool;
use App\Mcp\ServerTools\PayrollAssistantMcpTool;
use App\Mcp\ServerTools\PayrollSummaryMcpTool;
use App\Mcp\ServerTools\QcStatsMcpTool;
use App\Mcp\ServerTools\QuickReconcileByEmailMcpTool;
use App\Mcp\ServerTools\RecruiterCommissionsSummaryMcpTool;
use App\Mcp\ServerTools\RevertReconcileByEmailMcpTool;
use App\Mcp\ServerTools\SearchPartnerMcpTool;
use App\Mcp\ServerTools\SendWaMcpTool;
use App\Mcp\ServerTools\SoftDeleteEmailMcpTool;
use App\Mcp\ServerTools\TopPartnersMcpTool;
use Laravel\Mcp\Server;

class KamerakitaServer extends Server
{
    protected string $name = 'Kamerakita MCP';

    protected string $version = '3.2.0';

    public int $defaultPaginationLength = 50;

    protected string $instructions = <<<'MARKDOWN'
        Anda adalah asisten operasional dan analitik untuk Kamerakita.id.
        - Gunakan bahasa Indonesia yang sopan, ringkas, dan berorientasi bisnis.
        - Jika suatu data atau aksi tidak tersedia untuk peran pengguna saat ini, jelaskan dengan sopan dalam bahasa bisnis tanpa membocorkan istilah teknis internal (MCP, token, config, atau schema).

        Panduan memilih tool yang tepat:
        - Orientasi peran/sistem: `get_mcp_context`
        - Permintaan Main Account: `list_main_account_requests`
        - Permintaan Password Recovery: `list_password_recoveries`
        - Pendaftaran Fastwork Onboarding: `list_fastwork_onboardings`
        - Ringkasan gabungan antrean operasional & log aktivitas: `operations_overview`
        - Rekaman Atlas (worked/approved/rejected/billable/sync bot): `atlas_recording_summary`
        - Payroll Atlas (PAID/UNPAID, periode, nominal): `payroll_summary` (atau `match_payroll_excel`)
        - Siklus pembayaran (`PaymentCycle`): `payment_cycles_summary`
        - Komisi rekruter (`RecruiterCommission`): `recruiter_commissions_summary`
        - Laporan manual & QC video (`VideoWorkReport`): `qc_stats`, `payroll_assistant`, `top_partners`, `anomaly_detector`
        - Keuangan / kas internal (`FinanceEntry`): `finance_summary`
        - Invoice klien (`Invoice`): `invoice_summary`
        - Mailbox IMAP & sinkronisasi email: `mailbox_summary`
        - Pencarian partner/worker: `search_partner`; query fleksibel lintas tabel: `fetch_records` / `aggregate_records`
        - Aksi perubahan data (rekonsiliasi, approve massal, buat akun, WhatsApp): pastikan parameter tervalidasi dan tawarkan `is_simulation: true` bila perlu pratinjau.

        Penting: bedakan jalur Laporan Manual (`VideoWorkReport`) dan jalur Sinkronisasi Atlas (`AtlasTask` -> `Payroll`) saat menyajikan angka jam kerja dan tagihan.
    MARKDOWN;

    /**
     * @var array<int, class-string<Server\Tool>>
     */
    protected array $tools = [
        GetMcpContextMcpTool::class,
        ListMainAccountRequestsMcpTool::class,
        ListPasswordRecoveriesMcpTool::class,
        ListFastworkOnboardingsMcpTool::class,
        OperationsOverviewMcpTool::class,
        AtlasRecordingSummaryMcpTool::class,
        PayrollSummaryMcpTool::class,
        PaymentCyclesSummaryMcpTool::class,
        RecruiterCommissionsSummaryMcpTool::class,
        FinanceSummaryMcpTool::class,
        InvoiceSummaryMcpTool::class,
        MailboxSummaryMcpTool::class,
        SearchPartnerMcpTool::class,
        QcStatsMcpTool::class,
        FetchRecordsMcpTool::class,
        AggregateRecordsMcpTool::class,
        AnomalyDetectorMcpTool::class,
        TopPartnersMcpTool::class,
        PayrollAssistantMcpTool::class,
        AutoReconcileProportionalMcpTool::class,
        BatchReconcileByEmailMcpTool::class,
        RevertReconcileByEmailMcpTool::class,
        BatchApproveReportsMcpTool::class,
        CreateWorkerMcpTool::class,
        CreateCustomUserMcpTool::class,
        SoftDeleteEmailMcpTool::class,
        SendWaMcpTool::class,
        QuickReconcileByEmailMcpTool::class,
        MatchPayrollExcelMcpTool::class,
    ];
}
