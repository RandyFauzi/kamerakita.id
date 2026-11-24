<?php

namespace App\Mcp\Tools;

use App\Models\Invoice;

class InvoiceSummaryTool extends BaseTool
{
    public function getName(): string
    {
        return 'invoice_summary';
    }

    public function getDescription(): string
    {
        return 'Ringkasan dan daftar Invoice klien (status DRAFT, ISSUED, SENT, PAID, VOID), total jam billable, serta nominal tagihan.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'description' => 'Status invoice: DRAFT, ISSUED, SENT, PAID, atau VOID'],
                'client_name' => ['type' => 'string', 'description' => 'Filter nama klien (partial match)'],
                'date_from' => ['type' => 'string', 'description' => 'Tanggal invoice_date >= YYYY-MM-DD'],
                'date_to' => ['type' => 'string', 'description' => 'Tanggal invoice_date <= YYYY-MM-DD'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah invoice terbaru (default 20, maks 50)'],
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

        $query = Invoice::query();

        if (! empty($args['status'])) {
            $query->where('status', strtoupper($args['status']));
        }
        if (! empty($args['client_name'])) {
            $query->where('client_name', 'LIKE', '%' . $args['client_name'] . '%');
        }
        if (! empty($args['date_from'])) {
            $query->whereDate('invoice_date', '>=', $args['date_from']);
        }
        if (! empty($args['date_to'])) {
            $query->whereDate('invoice_date', '<=', $args['date_to']);
        }

        $byStatus = (clone $query)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total_amount, COALESCE(SUM(billable_hours), 0) as total_billable_hours')
            ->groupBy('status')
            ->get();

        return [
            'summary_by_status' => $byStatus,
            'invoices' => $query->latest('invoice_date')->latest('id')->limit($limit)->get([
                'id', 'invoice_no', 'invoice_date', 'client_name', 'status',
                'total_workers', 'billable_hours', 'unit_rate', 'currency', 'total_amount',
                'period_start', 'period_end',
            ]),
        ];
    }
}
