<?php

namespace App\Services;

use App\Models\VideoWorkReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    /**
     * Get the date range for the current active leaderboard week.
     * Rule: Wednesday to Tuesday.
     */
    public function getCurrentWeekRange(): array
    {
        return [
            Carbon::now()->startOfWeek(Carbon::WEDNESDAY),
            Carbon::now()->endOfWeek(Carbon::TUESDAY)
        ];
    }

    /**
     * Get the weekly leaderboard scores.
     * Caches the result for 15 minutes to reduce database load.
     */
    public function getWeeklyLeaderboard(int $limit = 10)
    {
        return Cache::remember("weekly_leaderboard_v2_{$limit}", 900, function () use ($limit) {
            $range = $this->getCurrentWeekRange();
            
            return VideoWorkReport::selectRaw('partner_id, sum(submitted_duration_minutes) as total_score')
                ->whereBetween('submission_date', $range)
                ->groupBy('partner_id')
                ->having('total_score', '>', 0)
                ->orderByDesc('total_score')
                ->limit($limit)
                ->with('partner')
                ->get();
        });
    }

    /**
     * Get all-time leaderboard scores.
     * Caches the result for 30 minutes.
     */
    public function getAllTimeLeaderboard(int $limit = 10)
    {
        return Cache::remember("alltime_leaderboard_v2_{$limit}", 1800, function () use ($limit) {
            return VideoWorkReport::selectRaw('partner_id, sum(approved_duration_minutes) as total_score')
                ->groupBy('partner_id')
                ->having('total_score', '>', 0)
                ->orderByDesc('total_score')
                ->limit($limit)
                ->with('partner')
                ->get();
        });
    }

    /**
     * Check if a specific partner is in the top ranks for the current week.
     * Returns their rank (1, 2, 3...) or null if they are not in the top.
     */
    public function getPartnerWeeklyRank(string $partnerId, int $topLimit = 3): ?int
    {
        $topPartners = $this->getWeeklyLeaderboard($topLimit);
        
        foreach ($topPartners as $index => $topPartner) {
            if ((string)$topPartner->partner_id === $partnerId) {
                return $index + 1; // 1-indexed rank
            }
        }
        
        return null;
    }
}
