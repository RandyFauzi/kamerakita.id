<?php

namespace App\Services;

use App\Models\AtlasTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    /**
     * Get the date range for the current active leaderboard week (Monday to Sunday).
     */
    public function getCurrentWeekRange(): array
    {
        return [
            Carbon::now()->startOfWeek()->toDateString(),
            Carbon::now()->endOfWeek()->toDateString()
        ];
    }

    /**
     * Get the weekly leaderboard scores based on Atlas recording tasks.
     * Caches the result for 5 minutes.
     */
    public function getWeeklyLeaderboard(int $limit = 10)
    {
        return Cache::remember("weekly_leaderboard_atlas_v2_{$limit}", 300, function () use ($limit) {
            $range = $this->getCurrentWeekRange();
            
            $results = AtlasTask::selectRaw('atlas_worker_id, SUM(worked_minutes) as total_worked, SUM(approved_minutes) as total_approved')
                ->whereBetween('task_date', $range)
                ->groupBy('atlas_worker_id')
                ->having('total_worked', '>', 0)
                ->orderByDesc('total_worked')
                ->limit($limit)
                ->with(['atlasWorker.user.partner'])
                ->get();

            return $results->map(function ($item) {
                $worker = $item->atlasWorker;
                $name = $worker?->user?->partner?->full_name 
                    ?: ($worker?->user?->name 
                    ?: ($worker?->atlas_email ? explode('@', $worker->atlas_email)[0] : 'Worker'));

                return [
                    'worker_id'        => $item->atlas_worker_id,
                    'name'             => $name,
                    'worked_minutes'   => (float) $item->total_worked,
                    'approved_minutes' => (float) $item->total_approved,
                    'total_score'      => (float) $item->total_worked,
                    'partner'          => ['full_name' => $name],
                ];
            })->all();
        });
    }

    /**
     * Get all-time leaderboard scores based on Atlas recording tasks.
     * Caches the result for 10 minutes.
     */
    public function getAllTimeLeaderboard(int $limit = 10)
    {
        return Cache::remember("alltime_leaderboard_atlas_v2_{$limit}", 600, function () use ($limit) {
            $results = AtlasTask::selectRaw('atlas_worker_id, SUM(worked_minutes) as total_worked, SUM(approved_minutes) as total_approved')
                ->groupBy('atlas_worker_id')
                ->having('total_worked', '>', 0)
                ->orderByDesc('total_worked')
                ->limit($limit)
                ->with(['atlasWorker.user.partner'])
                ->get();

            return $results->map(function ($item) {
                $worker = $item->atlasWorker;
                $name = $worker?->user?->partner?->full_name 
                    ?: ($worker?->user?->name 
                    ?: ($worker?->atlas_email ? explode('@', $worker->atlas_email)[0] : 'Worker'));

                return [
                    'worker_id'        => $item->atlas_worker_id,
                    'name'             => $name,
                    'worked_minutes'   => (float) $item->total_worked,
                    'approved_minutes' => (float) $item->total_approved,
                    'total_score'      => (float) $item->total_worked,
                    'partner'          => ['full_name' => $name],
                ];
            })->all();
        });
    }

    /**
     * Check if a specific worker is in the top ranks for the current week.
     * Returns their rank (1, 2, 3...) or null if they are not in the top.
     */
    public function getPartnerWeeklyRank(string $workerId, int $topLimit = 3): ?int
    {
        $topPartners = $this->getWeeklyLeaderboard($topLimit);
        
        if (!is_iterable($topPartners)) {
            return null;
        }
        
        foreach ($topPartners as $index => $topPartner) {
            $wId = data_get($topPartner, 'worker_id');
            
            if ($wId && (string)$wId === (string)$workerId) {
                return $index + 1; // 1-indexed rank
            }
        }
        
        return null;
    }
}
