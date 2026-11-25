<?php

namespace App\Mcp\Tools;

use App\Models\PaymentCycle;

class PaymentCyclesSummaryTool extends BaseTool
{
    public function getName(): string
    {
        return 'payment_cycles_summary';
    }

    public function getDescription(): string
    {
        return 'Ringkasan siklus pembayaran (PaymentCycle): periode, jam billable klien, revenue IDR, total pembayaran worker IDR, dan status freeze.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'source_label' => ['type' => 'string', 'description' => 'Filter label sumber siklus pembayaran'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah siklus terbaru (default 15, maks 50)'],
            ],
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $limit = min(max((int) ($args['limit'] ?? 15), 1), 50);

        $query = PaymentCycle::query();

        if (! empty($args['source_label'])) {
            $query->where('source_label', 'LIKE', '%' . $args['source_label'] . '%');
        }

        $agg = (clone $query)->selectRaw('
            COUNT(*) as total_cycles,
            COALESCE(SUM(client_billable_hours), 0) as total_client_billable_hours,
            COALESCE(SUM(revenue_idr), 0) as total_revenue_idr,
            COALESCE(SUM(total_pay_idr), 0) as total_pay_idr
        ')->first();

        return [
            'summary' => [
                'total_cycles' => (int) ($agg->total_cycles ?? 0),
                'total_client_billable_hours' => round((float) ($agg->total_client_billable_hours ?? 0), 2),
                'total_revenue_idr' => (int) ($agg->total_revenue_idr ?? 0),
                'total_pay_idr' => (int) ($agg->total_pay_idr ?? 0),
            ],
            'cycles' => $query->latest('period_end')->latest('id')->limit($limit)->get([
                'id', 'name', 'source_label', 'period_start', 'period_end', 'frozen_on',
                'client_billable_hours', 'client_total_usd', 'revenue_idr', 'total_pay_idr',
            ]),
        ];
    }
}
