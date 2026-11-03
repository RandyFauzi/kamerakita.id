<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AtlasBotController extends Controller
{
    public function sync(Request $request)
    {
        return $this->ingest($request, 'atlas');
    }

    public function sync1618(Request $request)
    {
        return $this->ingest($request, '1618');
    }

    private function ingest(Request $request, string $source)
    {
        $token = $request->header('X-Bot-Token') ?? $request->input('bot_token');
        $expected = config($source === '1618' ? 'services.robotics1618.bot_token' : 'services.atlas.bot_token');
        if (!$expected || !is_string($token) || !hash_equals($expected, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $hasTracking = Schema::hasColumn('atlas_tasks', 'source_observed_at') && Schema::hasTable('recording_import_runs');
        if ($source === '1618' && !$hasTracking) {
            return response()->json(['error' => 'Recording source migration has not been applied.'], 503);
        }

        $rules = [
            'observed_at' => [($source === '1618' ? 'required' : 'sometimes'), 'date', 'before_or_equal:'.now()->addMinutes(5)->toIso8601String()],
            'records' => 'required|array|min:1|max:500',
            'records.*' => 'required|array',
            'records.*.record_id' => 'required|string|regex:/^[a-fA-F0-9]{24}$/|distinct:ignore_case',
            'records.*.participant' => 'required|email|max:255',
            'records.*.task' => 'required|string|max:255',
            'records.*.recorded_utc' => 'required|date',
            'records.*.status' => 'nullable|string|max:255',
            'records.*.reconciliation' => 'nullable|string|max:20000',
            'records.*.submitted_utc' => 'nullable|date',
            'records.*.reviewed_utc' => 'nullable|date',
            'records.*.rejection_reasons' => 'nullable|string|max:20000',
            'records.*.pay_period_start' => 'nullable|date',
        ];
        foreach (['duration_min', 'duration_sec', 'billable_min', 'billable_hours',
            'pending_min', 'pending_hours', 'rejected_min', 'rejected_hours',
            'deferred_min', 'deferred_hours'] as $field) {
            $rules["records.*.$field"] = 'sometimes|required|numeric|min:0|max:10000000';
        }
        if ($source === '1618') {
            $rules += [
                'import.id' => 'required|uuid', 'import.snapshot_hash' => 'required|string|size:64|regex:/^[a-f0-9]+$/',
                'import.total_records' => 'required|integer|min:1|max:1000000',
                'import.total_batches' => 'required|integer|min:1|max:2000',
                'import.batch_index' => 'required|integer|min:0|max:1999',
                'import.metadata' => 'required|array',
                'import.metadata.from' => 'required|date_format:Y-m-d',
                'import.metadata.to' => 'required|date_format:Y-m-d|after_or_equal:import.metadata.from',
                'import.metadata.generated_utc' => 'required|date',
            ];
        }
        $validated = $request->validate($rules);
        $records = $validated['records'];
        $observed = (isset($validated['observed_at']) ? CarbonImmutable::parse($validated['observed_at'])->utc() : CarbonImmutable::now('UTC'))->startOfSecond();
        $import = $validated['import'] ?? null;
        if ($import && ($import['batch_index'] >= $import['total_batches'] || $import['total_batches'] !== (int) ceil($import['total_records'] / 500))) {
            throw ValidationException::withMessages(['import' => 'Invalid batch manifest.']);
        }
        foreach ($records as $i => $record) {
            foreach ([['duration_min', 'duration_sec'], ['billable_min', 'billable_hours'],
                ['pending_min', 'pending_hours'], ['rejected_min', 'rejected_hours'],
                ['deferred_min', 'deferred_hours']] as [$minutes, $alternate]) {
                if (!array_key_exists($minutes, $record) && !array_key_exists($alternate, $record)) {
                    throw ValidationException::withMessages(["records.$i.$minutes" => 'Explicit duration is required; missing data cannot be inferred as rejected.']);
                }
            }
        }

        $result = DB::transaction(function () use ($records, $source, $observed, $import, $hasTracking) {
            $written = 0; $skipped = 0;
            $payloadHash = hash('sha256', json_encode($records, JSON_THROW_ON_ERROR));
            if ($import) {
                DB::table('recording_import_runs')->insertOrIgnore([
                    'id' => $import['id'], 'source' => $source, 'snapshot_hash' => $import['snapshot_hash'],
                    'observed_at' => $observed->format('Y-m-d H:i:s'), 'expected_records' => $import['total_records'],
                    'expected_batches' => $import['total_batches'], 'metadata' => json_encode($import['metadata'], JSON_THROW_ON_ERROR),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $run = DB::table('recording_import_runs')->where('id', $import['id'])->lockForUpdate()->first();
                if ($run->source !== $source || $run->snapshot_hash !== $import['snapshot_hash'] || $run->observed_at !== $observed->format('Y-m-d H:i:s') || (int) $run->expected_records !== $import['total_records'] || (int) $run->expected_batches !== $import['total_batches'] || json_decode($run->metadata, true) !== $import['metadata']) {
                    throw ValidationException::withMessages(['import' => 'Import manifest changed; use a new import ID.']);
                }
                $receipt = DB::table('recording_import_batches')->where('import_id', $import['id'])->where('batch_index', $import['batch_index'])->first();
                if ($receipt) {
                    if ($receipt->payload_hash !== $payloadHash) {
                        throw ValidationException::withMessages(['import' => 'Batch contents changed after acknowledgement.']);
                    }
                    return ['written' => (int) $receipt->written, 'skipped' => (int) $receipt->skipped];
                }
                $expectedCount = min(500, $import['total_records'] - $import['batch_index'] * 500);
                if (count($records) !== $expectedCount) {
                    throw ValidationException::withMessages(['records' => 'Incomplete import batch.']);
                }
                $ids = array_map('strtolower', array_column($records, 'record_id'));
                foreach (DB::table('recording_import_batches')->where('import_id', $import['id'])->pluck('record_ids') as $priorIds) {
                    if (array_intersect($ids, json_decode($priorIds, true))) {
                        throw ValidationException::withMessages(['records' => 'Duplicate recording across import batches.']);
                    }
                }
            }
            foreach ($records as $i => $rec) {
                $minutes = static fn (string $minuteKey, string $hourKey): float =>
                    round(isset($rec[$minuteKey]) ? (float) $rec[$minuteKey] : (float) $rec[$hourKey] * 60, 6);
                $approved = $minutes('billable_min', 'billable_hours');
                $pending = $minutes('pending_min', 'pending_hours');
                $rejected = $minutes('rejected_min', 'rejected_hours');
                $deferred = $minutes('deferred_min', 'deferred_hours');
                $total = round(isset($rec['duration_min']) ? (float) $rec['duration_min'] : (float) $rec['duration_sec'] / 60, 6);
                $date = CarbonImmutable::parse($rec['recorded_utc'], 'UTC')->utc();
                $id = strtolower($rec['record_id']);
                $matches = AtlasTask::where('atlas_task_id', $id)->lockForUpdate()->get();
                if ($matches->count() > 1) {
                    throw ValidationException::withMessages(["records.$i.record_id" => 'Duplicate recording ID exists; reconcile before syncing.']);
                }
                $task = $matches->first() ?? new AtlasTask(['atlas_task_id' => $id]);
                if ($hasTracking && $task->exists) {
                    $previous = $task->source_observed_at ?? $task->updated_at;
                    if ($previous && ($observed->lessThan($previous) || ($observed->equalTo($previous) && $source === '1618' && $task->ingest_source === 'atlas'))) {
                        $skipped++;
                        continue;
                    }
                }
                $email = strtolower(trim($rec['participant']));
                $worker = AtlasWorker::firstOrCreate(['atlas_email' => $email], ['group' => 'ASTRO', 'active' => true]);
                if (!$worker->user_id) {
                    $users = User::whereRaw('LOWER(email) = ?', [$email])->pluck('id')
                        ->merge(Partner::whereRaw('LOWER(email) = ?', [$email])->whereNotNull('user_id')->pluck('user_id'))->unique();
                    if ($users->count() > 1) {
                        throw ValidationException::withMessages(["records.$i.participant" => 'Email maps to multiple accounts; resolve account mapping before importing.']);
                    }
                    if ($users->count() === 1) {
                        $worker->update(['user_id' => $users->first()]);
                    }
                }
                $status = 'UNDER_REVIEW';
                if ($approved > 0) {
                    $status = ($pending + $deferred + $rejected > 0) ? 'PARTIALLY_APPROVED' : 'APPROVED';
                } elseif ($deferred > 0 && $pending == 0 && $rejected == 0) {
                    $status = 'DEFERRED';
                } elseif ($rejected > 0 && $pending == 0 && $deferred == 0) {
                    $status = 'REJECTED';
                }
                $values = [
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
                ];
                if ($hasTracking) {
                    $values += [
                        'ingest_source' => $source,
                        'source_observed_at' => $observed,
                        'source_details' => array_intersect_key($rec, array_flip(['submitted_utc', 'reviewed_utc', 'rejection_reasons', 'pay_period_start'])),
                    ];
                }
                $task->fill($values)->save();
                $written++;
                $worker->update(['last_synced_at' => now()]);
            }
            if ($import) {
                DB::table('recording_import_batches')->insert([
                    'import_id' => $import['id'], 'batch_index' => $import['batch_index'], 'payload_hash' => $payloadHash,
                    'records' => count($records), 'written' => $written, 'skipped' => $skipped,
                    'record_ids' => json_encode($ids, JSON_THROW_ON_ERROR),
                ]);
                $batches = DB::table('recording_import_batches')->where('import_id', $import['id']);
                if ((clone $batches)->count() === $import['total_batches'] && (int) (clone $batches)->sum('records') === $import['total_records']) {
                    DB::table('recording_import_runs')->where('id', $import['id'])->update(['completed_at' => now(), 'updated_at' => now()]);
                }
            }
            return compact('written', 'skipped');
        }, 5);

        return response()->json(['status' => 'success', 'count' => count($records)] + $result);
    }
}
