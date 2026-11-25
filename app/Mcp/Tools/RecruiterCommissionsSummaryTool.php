<?php

namespace App\Mcp\Tools;

use App\Models\RecruiterCommission;

class RecruiterCommissionsSummaryTool extends BaseTool
{
    public function getName(): string
    {
        return 'recruiter_commissions_summary';
    }

    public function getDescription(): string
    {
        return 'Ringkasan dan daftar komisi rekruter (milestone 20 jam approved), status paid/pending, dan nominal komisi.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'description' => 'Filter status komisi: pending atau paid'],
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

        $query = RecruiterCommission::query()
            ->with(['recruiter:id,full_name,email', 'worker:id,full_name,email']);

        if (! empty($args['status'])) {
            $query->where('status', strtolower((string) $args['status']));
        }

        $agg = (clone $query)->selectRaw('
            COUNT(*) as total_count,
            COALESCE(SUM(commission_amount), 0) as total_amount,
            SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status != "paid" THEN 1 ELSE 0 END) as unpaid_count
        ')->first();

        return [
            'summary' => [
                'total_commissions' => (int) ($agg->total_count ?? 0),
                'paid_count' => (int) ($agg->paid_count ?? 0),
                'unpaid_count' => (int) ($agg->unpaid_count ?? 0),
                'total_amount' => (int) ($agg->total_amount ?? 0),
            ],
            'commissions' => $query->latest()->limit($limit)->get()->map(fn ($c) => [
                'id' => $c->id,
                'recruiter_name' => $c->recruiter?->full_name,
                'worker_name' => $c->worker?->full_name,
                'approved_hours_at_milestone' => (float) $c->approved_hours_at_milestone,
                'commission_amount' => $c->commission_amount,
                'status' => $c->status,
                'paid_at' => $c->paid_at?->toIso8601String(),
            ]),
        ];
    }
}
