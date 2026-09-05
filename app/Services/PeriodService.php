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
     * Generate or sync missing periods into the ReportPeriod table mathematically.
     */
    public static function syncPeriods(): void
    {
        $minDateStr = \App\Models\VideoWorkReport::min('submission_date');
        $minDate = $minDateStr ? Carbon::parse($minDateStr) : now();

        $start = self::getPeriodRange($minDate)['start'];
        $now = now();
        $targetEnd = $now->dayOfWeek === Carbon::TUESDAY ? $now->copy()->addDays(7) : $now->copy();

        $counter = 1;
        while ($start->lte($targetEnd)) {
            $end = $start->copy()->addDays(6);

            \App\Models\ReportPeriod::firstOrCreate(
                [
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                ],
                [
                    'period_number' => $counter,
                    'status' => 'active',
                ]
            );

            $start->addDays(7);
            $counter++;
        }
    }

    /**
     * Get a list of periods for reports, e.g. for dropdown.
     */
    public static function getAvailablePeriods(): array
    {
        self::syncPeriods(); // Auto-sync periods safely

        $periods = \App\Models\ReportPeriod::orderBy('period_start', 'desc')->get();
        
        $numberedPeriods = [];
        foreach ($periods as $p) {
            $numberedPeriods[] = [
                'start' => $p->period_start,
                'end' => $p->period_end,
                'label' => "Periode {$p->period_number} (" . $p->period_start->translatedFormat('d M Y') . ' - ' . $p->period_end->translatedFormat('d M Y') . ")",
            ];
        }

        return $numberedPeriods;
    }
}
