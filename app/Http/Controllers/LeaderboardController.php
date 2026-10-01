<?php

namespace App\Http\Controllers;

use App\Models\VideoWorkReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    protected $leaderboardService;

    public function __construct(\App\Services\LeaderboardService $leaderboardService)
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
     * Format leaderboard scores for the interactive UI component.
     *
     * @param array $scores
     * @return string JSON representation of the scores
     */
    private function formatScoresForUI(array $scores): string
    {
        return collect($scores)->map(function ($score) {
            $name = data_get($score, 'partner.full_name') ?: 'Mitra KameraKita';
            
            return [
                'name'   => $name,
                'score'  => number_format((float) data_get($score, 'total_score', 0)) . ' ' . __('dashboard.leaderboard.minutes_unit'),
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=3b82f6&color=fff&bold=true',
            ];
        })->toJson();
    }
}
