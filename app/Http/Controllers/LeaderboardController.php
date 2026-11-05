<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    protected $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    public function index(Request $request)
    {
        $allTimeScores = $this->leaderboardService->getAllTimeLeaderboard(10);
        $weeklyScores = $this->leaderboardService->getWeeklyLeaderboard(10);

        return view('leaderboard.index', [
            'allTimeData' => $this->formatScoresForUI($allTimeScores),
            'weeklyData'  => $this->formatScoresForUI($weeklyScores),
        ]);
    }

    /**
     * Invalidate leaderboard cache (for admin/manual refresh).
     */
    public function reset()
    {
        Cache::forget('weekly_leaderboard_aggregated_v3_10');
        Cache::forget('alltime_leaderboard_aggregated_v3_10');
        Cache::forget('weekly_leaderboard_aggregated_v3_3');
        Cache::forget('alltime_leaderboard_aggregated_v3_3');

        return back()->with('success', 'Leaderboard cache berhasil diperbarui!');
    }

    /**
     * Format leaderboard scores for the interactive UI component.
     *
     * @param array $scores
     * @return string JSON representation of the scores
     */
    private function formatScoresForUI(array $scores): string
    {
        return collect($scores)->map(function ($score) {
            $name = data_get($score, 'name') 
                ?: (data_get($score, 'partner.full_name') ?: 'Mitra KameraKita');
            $worked = (float) data_get($score, 'worked_minutes', data_get($score, 'total_score', 0));
            $approved = (float) data_get($score, 'approved_minutes', 0);
            $isTeam = (bool) data_get($score, 'is_team', false);
            $membersCount = (int) data_get($score, 'members_count', 1);

            $badgeLabel = $isTeam
                ? ($membersCount > 1 ? "Tim Mitra ({$membersCount} Worker)" : "Tim Mitra")
                : "Worker Mandiri";

            $avatarBg = $isTeam ? '4f46e5' : '0284c7'; // Indigo for Team, Sky for Solo
            
            return [
                'name'             => $name,
                'score'            => number_format($worked) . ' ' . __('dashboard.leaderboard.minutes_unit'),
                'worked_minutes'   => $worked,
                'approved_minutes' => $approved,
                'is_team'          => $isTeam,
                'members_count'    => $membersCount,
                'badge_label'      => $badgeLabel,
                'avatar'           => 'https://ui-avatars.com/api/?name=' . urlencode($name) . "&background={$avatarBg}&color=fff&bold=true",
            ];
        })->toJson();
    }
}
