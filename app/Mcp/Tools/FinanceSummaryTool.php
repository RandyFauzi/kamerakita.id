<?php

namespace App\Mcp\Tools;

use App\Models\FinanceEntry;

class FinanceSummaryTool extends BaseTool
{
    public function getName(): string
    {
        return 'finance_summary';
    }

    public function getDescription(): string
    {
        return 'Ringkasan jurnal keuangan internal (FinanceEntry): total pemasukan, pengeluaran, saldo bersih, rincian per kategori, dan transaksi terbaru.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date_from' => ['type' => 'string', 'description' => 'Tanggal mulai entry_date (YYYY-MM-DD)'],
                'date_to' => ['type' => 'string', 'description' => 'Tanggal akhir entry_date (YYYY-MM-DD)'],
                'type' => ['type' => 'string', 'description' => 'Tipe transaksi (misal: income, expense)'],
                'category' => ['type' => 'string', 'description' => 'Filter kategori transaksi'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah transaksi terbaru (default 20, maks 50)'],
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

        $query = FinanceEntry::query();

        if (! empty($args['date_from'])) {
            $query->whereDate('entry_date', '>=', $args['date_from']);
        }
        if (! empty($args['date_to'])) {
            $query->whereDate('entry_date', '<=', $args['date_to']);
        }
        if (! empty($args['type'])) {
            $query->where('type', $args['type']);
        }
        if (! empty($args['category'])) {
            $query->where('category', $args['category']);
        }

        $byType = (clone $query)
            ->selectRaw('type, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $income = (int) ($byType['income']->total_amount ?? 0);
        $expense = (int) ($byType['expense']->total_amount ?? 0);

        $byCategory = (clone $query)
            ->selectRaw('type, category, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('type', 'category')
            ->orderByDesc('total_amount')
            ->limit(15)
            ->get();

        return [
            'summary' => [
                'total_income' => $income,
                'total_expense' => $expense,
                'net_balance' => $income - $expense,
                'by_type' => $byType->values(),
            ],
            'by_category' => $byCategory,
            'latest_entries' => $query->latest('entry_date')->latest('id')->limit($limit)->get([
                'id', 'entry_date', 'type', 'category', 'description', 'amount', 'finance_period_id',
            ]),
        ];
    }
}
