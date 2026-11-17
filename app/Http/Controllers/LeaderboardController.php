<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    public function index(Request $request)
    {
        $weeklyWorkerScores = $this->leaderboardService->getWeeklyLeaderboard(10, LeaderboardService::CATEGORY_WORKER);
        $allTimeWorkerScores = $this->leaderboardService->getAllTimeLeaderboard(10, LeaderboardService::CATEGORY_WORKER);

        $weeklyMitraScores = $this->leaderboardService->getWeeklyLeaderboard(10, LeaderboardService::CATEGORY_MITRA);
        $allTimeMitraScores = $this->leaderboardService->getAllTimeLeaderboard(10, LeaderboardService::CATEGORY_MITRA);

        $requestedCategory = $request->query('category');
        if (in_array($requestedCategory, [LeaderboardService::CATEGORY_WORKER, LeaderboardService::CATEGORY_MITRA], true)) {
            $defaultCategory = $requestedCategory;
        } else {
            $partner = Auth::user()?->partner;
            $defaultCategory = $partner
                ? $this->leaderboardService->resolvePartnerCategory($partner)
                : LeaderboardService::CATEGORY_WORKER;
        }

        return view('leaderboard.index', [
            'defaultCategory'   => $defaultCategory,
            'weeklyWorkerData'  => $this->formatScoresForUI($weeklyWorkerScores),
            'allTimeWorkerData' => $this->formatScoresForUI($allTimeWorkerScores),
            'weeklyMitraData'   => $this->formatScoresForUI($weeklyMitraScores),
            'allTimeMitraData'  => $this->formatScoresForUI($allTimeMitraScores),
        ]);
    }

    /**
     * Invalidate leaderboard cache (for admin/manual refresh).
     */
    public function reset()
    {
        foreach (['all', LeaderboardService::CATEGORY_WORKER, LeaderboardService::CATEGORY_MITRA] as $category) {
            foreach ([3, 10] as $limit) {
                Cache::forget("weekly_leaderboard_v4_{$category}_{$limit}");
                Cache::forget("alltime_leaderboard_v4_{$category}_{$limit}");
            }
        }

        // Legacy cache keys cleanup
        Cache::forget('weekly_leaderboard_aggregated_v3_10');
        Cache::forget('alltime_leaderboard_aggregated_v3_10');
        Cache::forget('weekly_leaderboard_aggregated_v3_3');
        Cache::forget('alltime_leaderboard_aggregated_v3_3');

        return back()->with('success', 'Leaderboard cache berhasil diperbarui!');
    }

    /**
     * Format leaderboard scores for the interactive UI component.
     */
    private function formatScoresForUI(array $scores): string
    {
        return collect($scores)->map(function ($score) {
            $name = data_get($score, 'name')
                ?: (data_get($score, 'partner.full_name') ?: 'Mitra KameraKita');
            $worked = (float) data_get($score, 'worked_minutes', data_get($score, 'total_score', 0));
            $approved = (float) data_get($score, 'approved_minutes', 0);
            $entityType = data_get($score, 'entity_type', 'solo');
            $membersCount = (int) data_get($score, 'members_count', 1);

            return [
                'name'             => $name,
                'score'            => number_format($worked) . ' ' . __('dashboard.leaderboard.minutes_unit'),
                'worked_minutes'   => $worked,
                'approved_minutes' => $approved,
                'entity_type'      => $entityType,
                'members_count'    => $membersCount,
                'avatar'           => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=3b82f6&color=fff&bold=true',
            ];
        })->toJson();
    }
}
