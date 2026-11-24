<?php

namespace App\Mcp\Tools;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\RoboticsSyncRun;

class AtlasRecordingSummaryTool extends BaseTool
{
    public function getName(): string
    {
        return 'atlas_recording_summary';
    }

    public function getDescription(): string
    {
        return 'Ringkasan data rekaman Atlas (AtlasTask & AtlasWorker): total jam worked, approved, rejected, review, billable, deferred, status sinkronisasi terakhir, dan daftar task terbaru.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date_from' => ['type' => 'string', 'description' => 'Tanggal mulai (YYYY-MM-DD)'],
                'date_to' => ['type' => 'string', 'description' => 'Tanggal akhir (YYYY-MM-DD)'],
                'atlas_email' => ['type' => 'string', 'description' => 'Filter berdasarkan email Atlas worker (partial match)'],
                'group' => ['type' => 'string', 'description' => 'Filter grup AtlasWorker (misal: ASTRO)'],
                'status' => ['type' => 'string', 'description' => 'Status AtlasTask (misal: APPROVED, REJECTED, UNDER_REVIEW)'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah task terbaru yang ditampilkan (default 20, maks 50)'],
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

        $query = AtlasTask::query()->with('atlasWorker');

        if (! empty($args['date_from'])) {
            $query->whereDate('task_date', '>=', $args['date_from']);
        }
        if (! empty($args['date_to'])) {
            $query->whereDate('task_date', '<=', $args['date_to']);
        }
        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }
        if (! empty($args['atlas_email']) || ! empty($args['group'])) {
            $query->whereHas('atlasWorker', function ($q) use ($args) {
                if (! empty($args['atlas_email'])) {
                    $q->where('atlas_email', 'LIKE', '%' . $args['atlas_email'] . '%');
                }
                if (! empty($args['group'])) {
                    $q->where('group', $args['group']);
                }
            });
        }

        $agg = (clone $query)->selectRaw('
            COUNT(*) as total_tasks,
            COALESCE(SUM(worked_minutes), 0) as worked_minutes,
            COALESCE(SUM(approved_minutes), 0) as approved_minutes,
            COALESCE(SUM(rejected_minutes), 0) as rejected_minutes,
            COALESCE(SUM(review_minutes), 0) as review_minutes,
            COALESCE(SUM(billable_minutes), 0) as billable_minutes,
            COALESCE(SUM(deferred_minutes), 0) as deferred_minutes
        ')->first();

        $latestSync = RoboticsSyncRun::query()->latest('id')->first();

        return [
            'summary' => [
                'total_tasks' => (int) ($agg->total_tasks ?? 0),
                'active_atlas_workers' => AtlasWorker::where('active', true)->count(),
                'worked_hours' => round(((float) ($agg->worked_minutes ?? 0)) / 60, 2),
                'approved_hours' => round(((float) ($agg->approved_minutes ?? 0)) / 60, 2),
                'rejected_hours' => round(((float) ($agg->rejected_minutes ?? 0)) / 60, 2),
                'review_hours' => round(((float) ($agg->review_minutes ?? 0)) / 60, 2),
                'billable_hours' => round(((float) ($agg->billable_minutes ?? 0)) / 60, 2),
                'deferred_hours' => round(((float) ($agg->deferred_minutes ?? 0)) / 60, 2),
            ],
            'latest_sync_run' => $latestSync ? [
                'id' => $latestSync->id,
                'status' => $latestSync->status ?? null,
                'started_at' => $latestSync->started_at?->toIso8601String(),
                'finished_at' => $latestSync->finished_at?->toIso8601String(),
            ] : null,
            'latest_tasks' => $query->latest('task_date')->latest('id')->limit($limit)->get()->map(fn ($t) => [
                'id' => $t->id,
                'atlas_task_id' => $t->atlas_task_id,
                'task_date' => $t->task_date,
                'task_name' => $t->task_name,
                'atlas_email' => $t->atlasWorker?->atlas_email,
                'group' => $t->atlasWorker?->group,
                'status' => $t->status,
                'worked_minutes' => (float) $t->worked_minutes,
                'approved_minutes' => (float) $t->approved_minutes,
                'rejected_minutes' => (float) $t->rejected_minutes,
                'billable_minutes' => (float) $t->billable_minutes,
                'payroll_id' => $t->payroll_id,
            ]),
        ];
    }
}
