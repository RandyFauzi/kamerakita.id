<?php

namespace App\Http\Controllers;

use App\Models\VideoWorkReport;
use App\Services\PeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetGrupBController extends Controller
{
    public function index(Request $request): View
    {
        // Parameter Konfigurasi Target
        $targetHours = 200;
        $targetDeadline = Carbon::parse('2026-08-03 11:00:00');
        $groupNameTarget = 'Grup B';

        // 1. Ambil semua rentang periode yang tersedia
        $periods = PeriodService::getAvailablePeriods();
        
        // Periode 3 adalah index ke-2 (karena array index mulai dari 0)
        // Pastikan periode 3 tersedia, jika tidak fallback ke null
        $period3 = isset($periods[2]) ? $periods[2] : null;

        $totalApprovedMinutes = 0;

        if ($period3) {
            $startDate = $period3['start']->format('Y-m-d');
            $endDate = $period3['end']->format('Y-m-d');

            // 2. Query total menit approved khusus Grup B di rentang Periode 3
            $totalApprovedMinutes = VideoWorkReport::where('qc_status', 'approved')
                ->whereBetween('submission_date', [$startDate, $endDate])
                ->whereHas('partner', function ($query) use ($groupNameTarget) {
                    $query->where('group_name', $groupNameTarget);
                })
                ->sum('approved_duration_minutes');
        }

        $totalHours = round($totalApprovedMinutes / 60, 2);
        
        // Batasi maksimal persentase 100% agar bar tidak meluap
        $rawPercentage = ($totalHours / $targetHours) * 100;
        $progressPercentage = min(100, $rawPercentage);

        return view('dashboard.target-sementara', compact(
            'targetHours',
            'targetDeadline',
            'totalHours',
            'progressPercentage',
            'rawPercentage',
            'period3'
        ));
    }
}
