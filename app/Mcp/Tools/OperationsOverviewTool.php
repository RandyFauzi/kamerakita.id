<?php

namespace App\Mcp\Tools;

use App\Models\ActivityLog;
use App\Models\FastworkOnboarding;
use App\Models\MainAccountRequest;
use App\Models\PasswordRecoveryRequest;

class OperationsOverviewTool extends BaseTool
{
    public function getName(): string
    {
        return 'operations_overview';
    }

    public function getDescription(): string
    {
        return 'Ringkasan antrean operasional admin: permintaan Main Account pending, permintaan Password Recovery pending, onboarding Fastwork terbaru, dan log aktivitas sistem terbaru.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => ['type' => 'integer', 'description' => 'Jumlah item terbaru per kategori (default 10, maks 30)'],
            ],
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $limit = min(max((int) ($args['limit'] ?? 10), 1), 30);

        return [
            'counts' => [
                'pending_main_account_requests' => MainAccountRequest::where('status', 'pending')->count(),
                'pending_password_recoveries' => PasswordRecoveryRequest::where('status', 'pending')->count(),
                'total_fastwork_onboardings' => FastworkOnboarding::count(),
            ],
            'latest_main_account_requests' => MainAccountRequest::query()
                ->with('user:id,name,email')
                ->where('status', 'pending')
                ->latest()
                ->limit($limit)
                ->get(['id', 'user_id', 'email', 'estimated_accounts', 'status', 'created_at']),
            'latest_password_recoveries' => PasswordRecoveryRequest::query()
                ->where('status', 'pending')
                ->latest('requested_at')
                ->limit($limit)
                ->get(['id', 'identifier', 'status', 'requested_at']),
            'latest_fastwork_onboardings' => FastworkOnboarding::query()
                ->latest()
                ->limit($limit)
                ->get(['id', 'full_name', 'device_type', 'fastwork_username', 'has_headstrap', 'created_at']),
            'latest_activity_logs' => ActivityLog::query()
                ->latest()
                ->limit($limit)
                ->get(['id', 'user_id', 'action', 'description', 'created_at']),
        ];
    }
}
