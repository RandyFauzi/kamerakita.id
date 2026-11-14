<?php

namespace App\Services;

use App\Models\RoboticsSyncRun;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class Robotics1618Sync
{
    public function enqueue(string $trigger, ?int $userId = null): RoboticsSyncRun
    {
        $active = RoboticsSyncRun::where('active_slot', '1618')->first();
        if ($active) {
            return $active;
        }
        // Button spam cannot create repeated downloads, even after fast completion/failure.
        $recent = RoboticsSyncRun::where('created_at', '>', now()->subMinutes(2))->latest('id')->first();
        if ($recent) {
            return $recent;
        }
        try {
            return RoboticsSyncRun::create(['active_slot' => '1618', 'trigger' => $trigger,
                'requested_by' => $userId, 'status' => 'queued', 'phase' => 'Menunggu scheduler']);
        } catch (UniqueConstraintViolationException) {
            return RoboticsSyncRun::where('active_slot', '1618')->firstOrFail();
        }
    }

    public function work(): ?RoboticsSyncRun
    {
        $directory = storage_path('app/private/robotics1618');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $lock = fopen($directory.'/sync.lock', 'c');
        if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock) {
                fclose($lock);
            }

            return null;
        }
        $run = null;
        try {
            // An OS lock never expires while a live process owns it. If we own it,
            // a 'running' row was abandoned by a killed process and can be retried.
            RoboticsSyncRun::where('active_slot', '1618')->where('status', 'running')->update([
                'status' => 'failed', 'active_slot' => null, 'finished_at' => now(),
                'message' => 'Proses sebelumnya terhenti. Silakan perbarui lagi; batch selesai tetap tersimpan.']);
            $run = RoboticsSyncRun::where('active_slot', '1618')->where('status', 'queued')->first();
            if (! $run) {
                return null;
            }
            $run->update(['status' => 'running', 'started_at' => now(), 'phase' => 'Mengambil sumber 1618']);
            $source = app(Robotics1618Source::class);
            $before = $source->refresh();
            $digest = $source->download($directory.'/download.csv');
            $metadata = $source->metadata();
            if ($before['recordings_csv'] !== $metadata['recordings_csv']) {
                throw new RuntimeException('Snapshot 1618 berubah saat diunduh. Silakan coba lagi.');
            }
            $run->update(['phase' => 'Memvalidasi seluruh CSV']);
            $validated = fopen($directory.'/validated.jsonl', 'wb');
            $count = 0;
            try {
                foreach (app(Robotics1618Csv::class)->records($directory.'/download.csv', $metadata) as $record) {
                    $source->checkDeadline();
                    $line = json_encode($record, JSON_THROW_ON_ERROR)."\n";
                    if (fwrite($validated, $line) !== strlen($line)) {
                        throw new RuntimeException('Penyimpanan data validasi gagal.');
                    }
                    $count++;
                }
            } finally {
                fclose($validated);
            }
            $observed = Robotics1618Csv::date($metadata['recordings_csv']['fetched_utc'])->startOfSecond();
            $stats = ['records' => $count, 'written' => 0, 'skipped' => 0,
                'source_observed_at' => $observed->toIso8601String(), 'unchanged_snapshot' => false];
            $run->update(['total' => $count, 'result' => $stats, 'phase' => 'Menyimpan recording']);
            $existing = DB::table('recording_import_runs')->where('source', '1618')
                ->where('snapshot_hash', $digest)->where('observed_at', $observed->format('Y-m-d H:i:s'))
                ->where('expected_records', $count)->orderByDesc('created_at')->get()
                ->first(function ($row) use ($metadata) {
                    $prior = json_decode($row->metadata, true);

                    return $prior['from'] === $metadata['recordings_csv']['from'] && $prior['to'] === $metadata['recordings_csv']['to'];
                });
            if ($existing && $existing->completed_at) {
                $stats['unchanged_snapshot'] = true;
            } elseif ($count) {
                // Python and Laravel encode batches differently. Resume only our
                // own manifest; already committed rows from other clients remain idempotent.
                if ($existing && (json_decode($existing->metadata, true)['connector'] ?? null) !== 'laravel-v1') {
                    $existing = null;
                }
                $manifest = ['id' => $existing?->id ?? (string) Str::uuid(), 'snapshot_hash' => $digest,
                    'total_records' => $count, 'total_batches' => (int) ceil($count / 500),
                    'metadata' => $existing ? json_decode($existing->metadata, true) : [
                        'from' => $metadata['recordings_csv']['from'], 'to' => $metadata['recordings_csv']['to'],
                        'generated_utc' => $metadata['generated_utc'], 'connector' => 'laravel-v1']];
                $input = fopen($directory.'/validated.jsonl', 'rb');
                try {
                    $index = 0;
                    $batch = [];
                    while (($line = fgets($input)) !== false) {
                        $batch[] = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                        if (count($batch) === 500 || feof($input)) {
                            $this->batch($source, $run, $batch, $manifest, $index++, $observed, $stats);
                            $batch = [];
                        }
                    }
                    if ($batch) {
                        $this->batch($source, $run, $batch, $manifest, $index, $observed, $stats);
                    }
                } finally {
                    fclose($input);
                }
            }
            $run->update(['status' => 'success', 'active_slot' => null, 'phase' => 'Selesai',
                'processed' => $count, 'result' => $stats, 'finished_at' => now(),
                'message' => $stats['unchanged_snapshot'] ? 'Snapshot sumber sama; tidak diimpor ulang.' : 'Pembaruan selesai.']);

            return $run;
        } catch (\Throwable $error) {
            // Do not log HTTP requests, cookies, CSV content or credentials.
            $safe = $error instanceof RuntimeException && ! ($error instanceof QueryException)
                ? mb_substr($error->getMessage(), 0, 250) : 'Sinkronisasi gagal saat validasi/penyimpanan. Periksa konfigurasi dan pemetaan akun.';
            $run?->update(['status' => 'failed', 'active_slot' => null, 'message' => $safe, 'finished_at' => now()]);

            return $run;
        } finally {
            foreach (['download.csv', 'validated.jsonl'] as $file) {
                if (is_file($directory.'/'.$file)) {
                    unlink($directory.'/'.$file);
                }
            }
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function batch(Robotics1618Source $source, RoboticsSyncRun $run, array $batch, array $manifest,
        int $index, CarbonImmutable $observed, array &$stats): void
    {
        $source->checkDeadline();
        $result = app(RecordingIngestor::class)->ingest(['observed_at' => $observed->toIso8601String(),
            'records' => $batch, 'import' => $manifest + ['batch_index' => $index]], '1618');
        $stats['written'] += $result['written'];
        $stats['skipped'] += $result['skipped'];
        $run->update(['processed' => min(($index + 1) * 500, $manifest['total_records']), 'result' => $stats]);
        usleep(100000); // Yield between batches instead of flooding the shared database.
    }
}
