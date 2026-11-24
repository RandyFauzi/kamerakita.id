<?php

namespace App\Mcp\Tools;

use App\Models\Payroll;

class PayrollSummaryTool extends BaseTool
{
    public function getName(): string
    {
        return 'payroll_summary';
    }

    public function getDescription(): string
    {
        return 'Ringkasan dan daftar Payroll Atlas (status PAID/UNPAID, total jam approved, nominal rupiah, periode, dan filter email Atlas worker).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'description' => 'Status payroll: UNPAID atau PAID'],
                'period_start' => ['type' => 'string', 'description' => 'Filter period_start >= YYYY-MM-DD'],
                'period_end' => ['type' => 'string', 'description' => 'Filter period_end <= YYYY-MM-DD'],
                'atlas_email' => ['type' => 'string', 'description' => 'Filter berdasarkan email Atlas worker'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah data terbaru (default 20, maks 50)'],
            ],
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $limit = min(max((int) ($args['limit'] ?? 20), 1), 50);

        $query = Payroll::query()->with('atlasWorker');

        if (! empty($args['status'])) {
            $query->where('status', strtoupper($args['status']));
        }
        if (! empty($args['period_start'])) {
            $query->whereDate('period_start', '>=', $args['period_start']);
        }
        if (! empty($args['period_end'])) {
            $query->whereDate('period_end', '<=', $args['period_end']);
        }
        if (! empty($args['atlas_email'])) {
            $query->whereHas('atlasWorker', fn ($q) => $q->where('atlas_email', 'LIKE', '%' . $args['atlas_email'] . '%'));
        }

        $agg = (clone $query)->selectRaw('
            COUNT(*) as total_payrolls,
            COALESCE(SUM(total_approved_minutes), 0) as total_approved_minutes,
            COALESCE(SUM(amount_rupiah), 0) as total_amount_rupiah,
            SUM(CASE WHEN status = "UNPAID" THEN 1 ELSE 0 END) as unpaid_count,
            SUM(CASE WHEN status = "PAID" THEN 1 ELSE 0 END) as paid_count
        ')->first();

        return [
            'summary' => [
                'total_payrolls' => (int) ($agg->total_payrolls ?? 0),
                'unpaid_count' => (int) ($agg->unpaid_count ?? 0),
                'paid_count' => (int) ($agg->paid_count ?? 0),
                'total_approved_hours' => round(((float) ($agg->total_approved_minutes ?? 0)) / 60, 2),
                'total_amount_rupiah' => (float) ($agg->total_amount_rupiah ?? 0),
            ],
            'payrolls' => $query->latest('id')->limit($limit)->get()->map(fn ($p) => [
                'id' => $p->id,
                'atlas_email' => $p->atlasWorker?->atlas_email,
                'period_start' => $p->period_start?->toDateString(),
                'period_end' => $p->period_end?->toDateString(),
                'total_approved_hours' => round(((float) $p->total_approved_minutes) / 60, 2),
                'amount_rupiah' => (float) $p->amount_rupiah,
                'status' => $p->status,
                'paid_at' => $p->paid_at?->toIso8601String(),
            ]),
        ];
    }
}
