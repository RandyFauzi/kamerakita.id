<?php

namespace App\Mcp\Tools;

use App\Models\VideoWorkReport;

class BatchApproveReportsTool extends BaseTool
{
    public function getName(): string
    {
        return 'batch_approve_reports';
    }

    public function getDescription(): string
    {
        return 'Lakukan persetujuan batch untuk laporan berdasarkan ID.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'report_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
                'approved_minutes' => ['type' => 'integer']
            ],
            'required' => ['report_ids', 'approved_minutes']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.write';
    }

    public function execute(array $args, array $client)
    {
        $ids = $args['report_ids'] ?? [];
        $minutes = $args['approved_minutes'] ?? 0;
        
        $reports = VideoWorkReport::whereIn('id', $ids)->whereIn('qc_status', ['pending', 'on_review'])->get();
        $updatedCount = 0;
        
        foreach ($reports as $report) {
            $safeMinutes = min($minutes, $report->submitted_duration_minutes ?? 0);
            $report->update([
                'qc_status' => 'approved',
                'approved_duration_minutes' => $safeMinutes,
                'verifier_notes' => '(MCP Bot) Approved via API',
                'verified_at' => now(),
            ]);
            $updatedCount++;
        }
        return ['message' => "Berhasil menyetujui {$updatedCount} laporan secara batch."];
    }
}
