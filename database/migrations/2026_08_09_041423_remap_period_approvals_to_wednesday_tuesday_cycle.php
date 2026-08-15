<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Migration: Remap period_approvals from Saturday-Thursday (6-day)
 * to Wednesday-Tuesday (7-day) period boundaries.
 *
 * SAFE: Only updates period_start_date and period_end_date.
 *       Does NOT delete any data (except duplicate merges).
 *       Preserves status, approved_minutes, verifier_notes.
 */
return new class extends Migration
{
    private function getNewRange(string $dateStr): array
    {
        $date               = Carbon::parse($dateStr)->startOfDay();
        $dayOfWeek          = $date->dayOfWeek;
        $daysSinceWednesday = ($dayOfWeek - Carbon::WEDNESDAY + 7) % 7;

        $start = $date->copy()->subDays($daysSinceWednesday);
        $end   = $start->copy()->addDays(6);

        return [
            'start' => $start->format('Y-m-d'),
            'end'   => $end->format('Y-m-d'),
        ];
    }

    private function mergeStatus(string $a, string $b): string
    {
        $priority = ['paid' => 3, 'approved' => 2, 'draft' => 1];
        return ($priority[$a] ?? 0) >= ($priority[$b] ?? 0) ? $a : $b;
    }

    public function up(): void
    {
        // Step 1: Add a plain index on partner_id alone so FK constraint is still supported
        //         while we temporarily drop the composite unique index.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE period_approvals ADD INDEX pa_partner_id_temp (partner_id)');
            DB::statement('ALTER TABLE period_approvals DROP INDEX partner_period_unique');
        }

        // Step 3: Re-map each record to the new Wednesday-Tuesday period
        $records = DB::table('period_approvals')->get();

        // Group: partner_id -> new_period_key -> merged data
        $grouped = [];
        foreach ($records as $rec) {
            $range = $this->getNewRange($rec->period_start_date);
            $key   = $rec->partner_id . '|' . $range['start'] . '|' . $range['end'];

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'partner_id'        => $rec->partner_id,
                    'period_start_date' => $range['start'],
                    'period_end_date'   => $range['end'],
                    'approved_minutes'  => 0,
                    'status'            => 'draft',
                    'verifier_notes'    => null,
                    'ids'               => [],
                    'updated_at'        => $rec->updated_at,
                    'created_at'        => $rec->created_at,
                ];
            }

            $grouped[$key]['approved_minutes'] += (int) $rec->approved_minutes;
            $grouped[$key]['status']            = $this->mergeStatus($grouped[$key]['status'], $rec->status);
            $grouped[$key]['ids'][]             = $rec->id;

            if (!empty($rec->verifier_notes)) {
                $existing = $grouped[$key]['verifier_notes'];
                $grouped[$key]['verifier_notes'] = $existing
                    ? $existing . ' | ' . $rec->verifier_notes
                    : $rec->verifier_notes;
            }
        }

        // Step 4: Update first record of each group, delete duplicates (merged rows)
        foreach ($grouped as $data) {
            $ids     = $data['ids'];
            $keepId  = array_shift($ids); // keep the first record
            $extraIds = $ids;             // delete the rest (they are merged in)

            DB::table('period_approvals')->where('id', $keepId)->update([
                'period_start_date' => $data['period_start_date'],
                'period_end_date'   => $data['period_end_date'],
                'approved_minutes'  => $data['approved_minutes'],
                'status'            => $data['status'],
                'verifier_notes'    => $data['verifier_notes'],
                'updated_at'        => now(),
            ]);

            if (!empty($extraIds)) {
                // These are duplicates after merging — same partner + same new period
                DB::table('period_approvals')->whereIn('id', $extraIds)->delete();
            }
        }

        if (DB::getDriverName() !== 'sqlite') {
            // Restore the composite unique constraint
            DB::statement('ALTER TABLE period_approvals ADD UNIQUE partner_period_unique (partner_id, period_start_date, period_end_date)');

            // Remove the temporary plain index (FK is now supported by the unique index again)
            DB::statement('ALTER TABLE period_approvals DROP INDEX pa_partner_id_temp');
        }
    }

    public function down(): void
    {
        // Cannot safely reverse: old Saturday-Thursday boundaries are lost after merging.
        // Restore from a database backup if rollback is needed.
    }
};
