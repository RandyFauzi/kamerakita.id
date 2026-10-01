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
        // 1. All Time Leaderboard
        $allTimeScores = $this->leaderboardService->getAllTimeLeaderboard(10);

        // 2. Weekly Leaderboard
        $weeklyScores = $this->leaderboardService->getWeeklyLeaderboard(10);

        // Helper to format data for the UI
        $formatData = function ($scores) {
            return collect($scores)->map(function ($score) {
                $name = $score->partner->full_name ?? 'Mitra KameraKita';
                // Fallback avatar using ui-avatars since there's no avatar column
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=3b82f6&color=fff&bold=true';
                
                return [
                    'name' => $name,
                    'score' => number_format($score->total_score) . ' ' . __('dashboard.leaderboard.minutes_unit'),
                    'avatar' => $avatar,
                ];
            });
        };

        return view('leaderboard.index', [
            'allTimeData' => $formatData($allTimeScores)->toJson(),
            'weeklyData' => $formatData($weeklyScores)->toJson(),
        ]);
    }
}
