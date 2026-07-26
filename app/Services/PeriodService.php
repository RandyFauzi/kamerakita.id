<?php

namespace App\Services;

use Carbon\Carbon;

class PeriodService
{
    /**
     * Get the Saturday (start) and Thursday (end) for the period containing the given date.
     * Note: Friday belongs to the previous Saturday - Thursday period (extending it to Friday).
     */
    public static function getPeriodRange(Carbon $date): array
    {
        $date = $date->copy()->startOfDay();
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 5 = Friday, 6 = Saturday

        if ($dayOfWeek === Carbon::FRIDAY) {
            // Friday: belongs to the period starting last Saturday and ending yesterday (Thursday)
            $start = $date->copy()->subDays(6); // Previous Saturday
            $end = $date->copy()->subDay(); // Yesterday (Thursday)
        } elseif ($dayOfWeek === Carbon::SATURDAY) {
            // Saturday: starts today, ends next Thursday
            $start = $date->copy();
            $end = $date->copy()->addDays(5);
        } else {
            // Sunday - Thursday: starts previous Saturday, ends this Thursday
            $subDays = $dayOfWeek + 1;
            $start = $date->copy()->subDays($subDays);
            $end = $start->copy()->addDays(5); // Thursday of this week
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Get a list of periods for reports, e.g. for dropdown.
     * It scans the dates of VideoWorkReport to find all periods that have reports.
     */
    public static function getAvailablePeriods(): array
    {
        $dates = \App\Models\VideoWorkReport::select('submission_date')
            ->groupBy('submission_date')
            ->orderBy('submission_date', 'desc')
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
                    'label' => $range['start']->translatedFormat('d M Y') . ' - ' . $range['end']->translatedFormat('d M Y'),
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
                'label' => $currentRange['start']->translatedFormat('d M Y') . ' - ' . $currentRange['end']->translatedFormat('d M Y'),
            ];
        }

        // Sort descending by start date
        usort($periods, function($a, $b) {
            return $b['start']->timestamp <=> $a['start']->timestamp;
        });

        return $periods;
    }
}
