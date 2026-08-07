<?php

namespace App\Http\Controllers;

use App\Models\VideoWorkReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. All Time Leaderboard
        $allTimeScores = VideoWorkReport::selectRaw('partner_id, sum(approved_duration_minutes) as total_score')
            ->groupBy('partner_id')
            ->having('total_score', '>', 0)
            ->orderByDesc('total_score')
            ->limit(10)
            ->with('partner')
            ->get();

        // 2. Weekly Leaderboard
        $weeklyScores = VideoWorkReport::selectRaw('partner_id, sum(approved_duration_minutes) as total_score')
            ->whereBetween('submission_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->groupBy('partner_id')
            ->having('total_score', '>', 0)
            ->orderByDesc('total_score')
            ->limit(10)
            ->with('partner')
            ->get();

        // Helper to format data for the UI
        $formatData = function ($scores) {
            return $scores->map(function ($score) {
                $name = $score->partner->full_name ?? 'Mitra KameraKita';
                // Fallback avatar using ui-avatars since there's no avatar column
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0f766e&color=fff&bold=true';
                
                return [
                    'name' => $name,
                    'score' => number_format($score->total_score) . ' Menit',
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
