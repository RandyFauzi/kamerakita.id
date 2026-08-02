<?php

namespace App\Http\Controllers;

use App\Models\VideoWorkReport;
use App\Services\PeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        // Konfigurasi Event Aktif (Bisa diganti atau ditambah nanti)
        $activeEvent = [
            'name' => 'Closing Project 1 Minutes',
            'target_hours' => 200,
            'deadline' => Carbon::parse('2026-08-03 11:00:00'),
            'group_name' => 'Grup B', // Dikembalikan filter Grup B
            'period_number' => 3, // Target Periode Ke-3
            'is_active' => true,
        ];

        if (!$activeEvent['is_active']) {
            abort(404, 'Tidak ada event yang sedang aktif saat ini.');
        }

        // 1. Ambil semua rentang periode yang tersedia (urutan descending/terbaru di atas)
        $periods = collect(PeriodService::getAvailablePeriods());
        
        // 2. Cari periode yang labelnya mengandung "Periode X" dengan akurat
        $periodTarget = $periods->first(function ($p) use ($activeEvent) {
            return str_contains($p['label'], 'Periode ' . $activeEvent['period_number']);
        });
        $totalSubmittedMinutes = 0;

        if ($periodTarget) {
            $startDate = $periodTarget['start']->format('Y-m-d');
            $endDate = $periodTarget['end']->format('Y-m-d');

            // 2. Query total menit dilaporkan (tanpa memandang status approve) khusus target grup
            $totalSubmittedMinutes = VideoWorkReport::whereBetween('submission_date', [$startDate, $endDate])
                ->when($activeEvent['group_name'], function ($query, $groupName) {
                    $query->whereHas('partner', function ($q) use ($groupName) {
                        $q->where('group_name', $groupName);
                    });
                })
                ->sum('submitted_duration_minutes');
        }

        $totalHours = round($totalSubmittedMinutes / 60, 2);
        
        // Batasi maksimal persentase 100% agar bar tidak meluap
        $rawPercentage = ($totalHours / $activeEvent['target_hours']) * 100;
        $progressPercentage = min(100, $rawPercentage);

        return view('dashboard.event', [
            'eventName' => $activeEvent['name'],
            'targetHours' => $activeEvent['target_hours'],
            'targetDeadline' => $activeEvent['deadline'],
            'totalHours' => $totalHours,
            'progressPercentage' => $progressPercentage,
            'rawPercentage' => $rawPercentage,
            'periodTarget' => $periodTarget
        ]);
    }
}
