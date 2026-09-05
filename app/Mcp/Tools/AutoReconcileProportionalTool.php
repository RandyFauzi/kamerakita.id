<?php

namespace App\Mcp\Tools;

use App\Models\VideoWorkReport;

class AutoReconcileProportionalTool extends BaseTool
{
    public function getName(): string
    {
        return 'auto_reconcile_proportional';
    }

    public function getDescription(): string
    {
        return 'Otomatis bagikan kuota menit yang disetujui secara proporsional ke semua video pending partner.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'partner_id' => ['type' => 'string'],
                'total_quota_minutes' => ['type' => 'integer']
            ],
            'required' => ['partner_id', 'total_quota_minutes']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.write';
    }

    public function execute(array $args, array $client)
    {
        $partnerId = $args['partner_id'] ?? null;
        $totalQuota = $args['total_quota_minutes'] ?? 0;

        if (!$partnerId || $totalQuota <= 0) {
            throw new \Exception("partner_id dan total_quota_minutes (positif) wajib diisi.");
        }

        $pendingReports = VideoWorkReport::where('partner_id', $partnerId)
            ->where('qc_status', 'pending')
            ->get();

        if ($pendingReports->isEmpty()) {
            return ['message' => 'Tidak ada video pending untuk direkonsiliasi.'];
        }

        $totalSubmitted = $pendingReports->sum('submitted_duration_minutes');
        if ($totalSubmitted <= 0) {
            throw new \Exception("Total durasi submitted 0, tidak bisa membagi proporsi.");
        }

        $results = [];
        $remainingQuota = $totalQuota;

        foreach ($pendingReports as $index => $report) {
            $submitted = $report->submitted_duration_minutes;
            $proportion = $submitted / $totalSubmitted;
            
            if ($index === $pendingReports->count() - 1) {
                $allocated = $remainingQuota;
            } else {
                $allocated = (int) round($totalQuota * $proportion);
                $remainingQuota -= $allocated;
            }

            $allocated = min($allocated, $submitted);

            $report->update([
                'qc_status' => 'approved',
                'approved_duration_minutes' => $allocated,
                'verifier_notes' => '(MCP Bot) Proporsional Auto-Reconcile',
                'verified_at' => now(),
            ]);

            $results[] = [
                'report_id' => $report->id,
                'submitted' => $submitted,
                'allocated' => $allocated
            ];
        }

        return [
            'message' => 'Rekonsiliasi proporsional berhasil didistribusikan.',
            'total_allocated' => collect($results)->sum('allocated'),
            'details' => $results
        ];
    }
}
