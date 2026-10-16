<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AtlasBotController extends Controller
{
    public function sync(Request $request)
    {
        $token = $request->header('X-Bot-Token') ?? $request->input('bot_token');
        $expected = config('services.atlas.bot_token');
        if (!$expected || !is_string($token) || !hash_equals($expected, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rules = [
            'records' => 'required|array|min:1|max:500',
            'records.*' => 'required|array',
            'records.*.record_id' => 'required|string|regex:/^[a-fA-F0-9]{24}$/|distinct:ignore_case',
            'records.*.participant' => 'required|email|max:255',
            'records.*.task' => 'required|string|max:255',
            'records.*.recorded_utc' => 'required|date',
            'records.*.status' => 'nullable|string|max:255',
            'records.*.reconciliation' => 'nullable|string|max:20000',
        ];
        foreach (['duration_min', 'duration_sec', 'billable_min', 'billable_hours',
            'pending_min', 'pending_hours', 'rejected_min', 'rejected_hours',
            'deferred_min', 'deferred_hours'] as $field) {
            $rules["records.*.$field"] = 'sometimes|required|numeric|min:0|max:10000000';
        }
        $records = $request->validate($rules)['records'];
        foreach ($records as $i => $record) {
            foreach ([['duration_min', 'duration_sec'], ['billable_min', 'billable_hours'],
                ['pending_min', 'pending_hours'], ['rejected_min', 'rejected_hours'],
                ['deferred_min', 'deferred_hours']] as [$minutes, $alternate]) {
                if (!array_key_exists($minutes, $record) && !array_key_exists($alternate, $record)) {
                    throw ValidationException::withMessages(["records.$i.$minutes" => 'Explicit duration is required; missing data cannot be inferred as rejected.']);
                }
            }
        }

        DB::transaction(function () use ($records) {
            foreach ($records as $i => $rec) {
                $minutes = static fn (string $minuteKey, string $hourKey): float =>
                    round(isset($rec[$minuteKey]) ? (float) $rec[$minuteKey] : (float) $rec[$hourKey] * 60, 6);
                $approved = $minutes('billable_min', 'billable_hours');
                $pending = $minutes('pending_min', 'pending_hours');
                $rejected = $minutes('rejected_min', 'rejected_hours');
                $deferred = $minutes('deferred_min', 'deferred_hours');
                $total = round(isset($rec['duration_min']) ? (float) $rec['duration_min'] : (float) $rec['duration_sec'] / 60, 6);
                $date = CarbonImmutable::parse($rec['recorded_utc'], 'UTC')->utc();
                $worker = AtlasWorker::firstOrCreate(
                    ['atlas_email' => strtolower(trim($rec['participant']))],
                    ['group' => 'ASTRO', 'active' => true]
                );
                $id = strtolower($rec['record_id']);
                $matches = AtlasTask::where('atlas_task_id', $id)->lockForUpdate()->get();
                if ($matches->count() > 1) {
                    throw ValidationException::withMessages(["records.$i.record_id" => 'Duplicate recording ID exists; reconcile before syncing.']);
                }
                $task = $matches->first() ?? new AtlasTask(['atlas_task_id' => $id]);
                $status = 'UNDER_REVIEW';
                if ($approved > 0) {
                    $status = ($pending + $deferred + $rejected > 0) ? 'PARTIALLY_APPROVED' : 'APPROVED';
                } elseif ($deferred > 0 && $pending == 0 && $rejected == 0) {
                    $status = 'DEFERRED';
                } elseif ($rejected > 0 && $pending == 0 && $deferred == 0) {
                    $status = 'REJECTED';
                }
                $task->fill([
                    'atlas_worker_id' => $worker->id,
                    'task_date' => $date->toDateString(),
                    'recorded_at' => $date->format('Y-m-d H:i:s'),
                    'task_name' => $rec['task'],
                    'worked_minutes' => $total,
                    'approved_minutes' => $approved,
                    'rejected_minutes' => $rejected,
                    'review_minutes' => $pending,
                    'deferred_minutes' => $deferred,
                    'billable_minutes' => $approved,
                    'status' => $status,
                    'source_status' => $rec['status'] ?? null,
                    'notes' => $rec['reconciliation'] ?? null,
                ])->save();
                $worker->update(['last_synced_at' => now()]);
            }
        });

        return response()->json(['status' => 'success', 'count' => count($records)]);
    }
}
