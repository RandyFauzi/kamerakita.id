<?php

namespace App\Mcp\Tools;

use App\Models\VideoWorkReport;

class QcStatsTool extends BaseTool
{
    public function getName(): string
    {
        return 'qc_stats';
    }

    public function getDescription(): string
    {
        return 'Dapatkan statistik ringkasan dan daftar laporan video terbaru berdasarkan status atau tanggal.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'description' => 'Status QC (pending, approved, rejected)'],
                'date' => ['type' => 'string', 'description' => 'Tanggal laporan YYYY-MM-DD']
            ]
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $status = $args['status'] ?? null;
        $date = $args['date'] ?? null;

        $query = VideoWorkReport::query();
        if ($status) $query->where('qc_status', $status);
        if ($date) $query->whereDate('submission_date', $date);

        $total = $query->count();
        $approved = (clone $query)->where('qc_status', 'approved')->count();
        $pending = (clone $query)->where('qc_status', 'pending')->count();
        $rejected = (clone $query)->where('qc_status', 'rejected')->count();

        return [
            'summary' => compact('total', 'approved', 'pending', 'rejected'),
            'latest' => $query->latest()->limit(10)->get()
        ];
    }
}
