<?php

namespace App\Mcp\Tools;

use App\Models\Partner;

class TopPartnersTool extends BaseTool
{
    public function getName(): string
    {
        return 'top_partners';
    }

    public function getDescription(): string
    {
        return 'Dapatkan klasemen 20 partner teratas berdasarkan total unggahan video.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => ['type' => 'integer', 'description' => 'Jumlah maksimal partner (default 20)']
            ]
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $limit = $args['limit'] ?? 20;

        $partners = Partner::with('user')
            ->withCount('videoWorkReports as total_reports')
            ->withSum('videoWorkReports as total_submitted', 'submitted_duration_minutes')
            ->withSum('videoWorkReports as total_approved', 'approved_duration_minutes')
            ->orderByDesc('total_reports')
            ->limit($limit)
            ->get()
            ->map(function ($partner) {
                return [
                    'name' => $partner->user->name ?? 'Unknown',
                    'email' => $partner->user->email ?? 'Unknown',
                    'wa_number' => $partner->whatsapp_number ?? '-',
                    'total_reports' => $partner->total_reports,
                    'total_submitted_minutes' => $partner->total_submitted ?? 0,
                    'total_approved_minutes' => $partner->total_approved ?? 0,
                ];
            });

        return [
            'message' => "Top {$limit} Partners by Reports",
            'data' => $partners
        ];
    }
}
