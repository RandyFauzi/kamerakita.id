<?php

namespace App\Mcp\Tools;

use App\Models\VideoWorkReport;
use Carbon\Carbon;

class AnomalyDetectorTool extends BaseTool
{
    public function getName(): string
    {
        return 'anomaly_detector';
    }

    public function getDescription(): string
    {
        return 'Temukan anomali data seperti video tertahan atau durasi tidak wajar.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'anomaly_type' => ['type' => 'string', 'description' => 'all, high_duration, stuck_pending']
            ]
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $type = $args['anomaly_type'] ?? 'all';
        $anomalies = [];

        if (in_array($type, ['all', 'high_duration'])) {
            $highDuration = VideoWorkReport::where('submitted_duration_minutes', '>', 500)
                ->where('qc_status', 'pending')
                ->with('partner.user')
                ->get();
            $anomalies['high_duration'] = $highDuration;
        }

        if (in_array($type, ['all', 'stuck_pending'])) {
            $stuckPending = VideoWorkReport::where('qc_status', 'pending')
                ->where('created_at', '<', Carbon::now()->subDays(7))
                ->with('partner.user')
                ->get();
            $anomalies['stuck_pending'] = $stuckPending;
        }

        return [
            'message' => 'Pemindaian anomali selesai.',
            'anomalies_found' => count($anomalies['high_duration'] ?? []) + count($anomalies['stuck_pending'] ?? []),
            'data' => $anomalies
        ];
    }
}
