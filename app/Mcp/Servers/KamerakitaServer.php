<?php

namespace App\Mcp\Servers;

use App\Mcp\ServerTools\AggregateRecordsMcpTool;
use App\Mcp\ServerTools\AnomalyDetectorMcpTool;
use App\Mcp\ServerTools\AutoReconcileProportionalMcpTool;
use App\Mcp\ServerTools\BatchApproveReportsMcpTool;
use App\Mcp\ServerTools\BatchReconcileByEmailMcpTool;
use App\Mcp\ServerTools\CreateCustomUserMcpTool;
use App\Mcp\ServerTools\CreateWorkerMcpTool;
use App\Mcp\ServerTools\FetchRecordsMcpTool;
use App\Mcp\ServerTools\MatchPayrollExcelMcpTool;
use App\Mcp\ServerTools\PayrollAssistantMcpTool;
use App\Mcp\ServerTools\QcStatsMcpTool;
use App\Mcp\ServerTools\QuickReconcileByEmailMcpTool;
use App\Mcp\ServerTools\RevertReconcileByEmailMcpTool;
use App\Mcp\ServerTools\SearchPartnerMcpTool;
use App\Mcp\ServerTools\SendWaMcpTool;
use App\Mcp\ServerTools\SoftDeleteEmailMcpTool;
use App\Mcp\ServerTools\TopPartnersMcpTool;
use Laravel\Mcp\Server;

class KamerakitaServer extends Server
{
    protected string $name = 'Kamerakita MCP';

    protected string $version = '3.0.0';

    public int $defaultPaginationLength = 50;

    protected string $instructions = <<<'MARKDOWN'
        Anda adalah asisten operasional dan analitik untuk Kamerakita.id.
        - Gunakan bahasa Indonesia yang sopan, ringkas, dan berorientasi bisnis.
        - Jika suatu data atau aksi tidak tersedia untuk peran pengguna saat ini, jelaskan dengan sopan dalam bahasa bisnis tanpa membocorkan istilah teknis internal (MCP, token, config, atau schema).
        - Untuk aksi perubahan data (seperti rekonsiliasi, persetujuan massal, pembuatan akun, atau pengiriman WhatsApp), pastikan parameter sudah tervalidasi dan gunakan opsi simulasi (`is_simulation`) bila pengguna ingin pratinjau terlebih dahulu.
    MARKDOWN;

    /**
     * @var array<int, class-string<Server\Tool>>
     */
    protected array $tools = [
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
