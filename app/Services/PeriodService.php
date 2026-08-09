<?php

namespace App\Services;

use Carbon\Carbon;

class PeriodService
{
    /**
     * Get the Wednesday (start) and Tuesday (end) for the 7-day period containing the given date.
     * Periods run from Wednesday to Tuesday (inclusive).
     */
    public static function getPeriodRange(Carbon $date): array
    {
        $date = $date->copy()->startOfDay();
        $dayOfWeek = $date->dayOfWeek; // 0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat

        // Days elapsed since the most recent Wednesday
        // Wed=0, Thu=1, Fri=2, Sat=3, Sun=4, Mon=5, Tue=6
        $daysSinceWednesday = ($dayOfWeek - Carbon::WEDNESDAY + 7) % 7;

        $start = $date->copy()->subDays($daysSinceWednesday); // Wednesday
        $end   = $start->copy()->addDays(6);                  // Tuesday

        return [
            'start' => $start,
            'end'   => $end,
        ];
    }

    /**
     * Get a list of periods for reports, e.g. for dropdown.
     * It scans the dates of VideoWorkReport to find all periods that have reports.
     */
    public static function getAvailablePeriods(): array
    {
        // 1. Fetch all submission dates in ASCENDING order (oldest first) to number them chronologically
        $dates = \App\Models\VideoWorkReport::select('submission_date')
            ->groupBy('submission_date')
            ->orderBy('submission_date', 'asc')
            ->pluck('submission_date');

        $periods = [];
        foreach ($dates as $dateStr) {
            $date = Carbon::parse($dateStr);
            $range = self::getPeriodRange($date);
            $key = $range['start']->format('Y-m-d') . '|' . $range['end']->format('Y-m-d');
            
            if (!isset($periods[$key])) {
                $periods[$key] = [
                    'start' => $range['start'],
                    'end' => $range['end'],
                ];
            }
        }

        // Add current period if empty or not in database yet
        $currentRange = self::getPeriodRange(now());
        $currentKey = $currentRange['start']->format('Y-m-d') . '|' . $currentRange['end']->format('Y-m-d');
        if (!isset($periods[$currentKey])) {
            $periods[$currentKey] = [
                'start' => $currentRange['start'],
                'end' => $currentRange['end'],
            ];
        }

        // Selasa (hari terakhir periode): tambahkan periode berikutnya (Rabu depan) ke dropdown
        // agar pengguna bisa melihat periode mendatang sehari sebelum dimulai
        if (now()->dayOfWeek === Carbon::TUESDAY) {
            $nextStart = now()->addDay()->startOfDay(); // Rabu berikutnya
            $nextEnd   = $nextStart->copy()->addDays(6)->endOfDay(); // Selasa depan
            $nextKey   = $nextStart->format('Y-m-d') . '|' . $nextEnd->format('Y-m-d');
            if (!isset($periods[$nextKey])) {
                $periods[$nextKey] = [
                    'start' => $nextStart,
                    'end'   => $nextEnd,
                ];
            }
        }

        // Sort chronologically ascending to assign Periode 1, Periode 2, etc.
        uasort($periods, function($a, $b) {
            return $a['start']->timestamp <=> $b['start']->timestamp;
        });

        // Assign labels with Periode numbers (without emojis)
        $numberedPeriods = [];
        $counter = 1;
        foreach ($periods as $key => $p) {
            $numberedPeriods[] = [
                'start' => $p['start'],
                'end' => $p['end'],
                'label' => "Periode {$counter} (" . $p['start']->translatedFormat('d M Y') . ' - ' . $p['end']->translatedFormat('d M Y') . ")",
            ];
            $counter++;
        }

        // Sort descending (newest first) for dropdown presentation
        usort($numberedPeriods, function($a, $b) {
            return $b['start']->timestamp <=> $a['start']->timestamp;
        });

        return $numberedPeriods;
    }
}
