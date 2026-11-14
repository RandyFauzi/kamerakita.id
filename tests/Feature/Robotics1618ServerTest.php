<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\User;
use App\Services\RecordingIngestor;
use App\Services\Robotics1618Sync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Robotics1618ServerTest extends TestCase
{
    use RefreshDatabase;

    private function metadata(int $rows = 1): array
    {
        return ['generated_utc' => now()->toIso8601String(), 'export_utc' => now()->toIso8601String(),
            'recordings_csv' => ['rows' => $rows, 'recordings' => $rows, 'fetched_utc' => now()->subHour()->toIso8601String(),
                'from' => now()->subMonth()->toDateString(), 'to' => now()->toDateString()]];
    }

    private function csv(array $overrides = []): string
    {
        $record = array_replace(['recording_id' => str_repeat('a', 24), 'email' => 'worker@example.com',
            'task' => 'Server recording', 'created_utc' => now()->subDay()->toIso8601String(), 'duration_s' => '1800',
            'status' => 'processed', 'billable_h' => '0.4', 'rejected_h' => '0.1', 'pending_h' => '0',
            'deferred_h' => '0', 'submitted_utc' => '', 'reviewed_utc' => '', 'reconciliation' => 'ok',
            'rejection_reasons' => '', 'pay_period_start' => '', 'latest_row' => 'yes'], $overrides);
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, array_keys($record), ',', '"', '');
        fputcsv($stream, array_values($record), ',', '"', '');
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfSecond());
        config(['services.robotics1618.enabled' => true, 'services.robotics1618.refresh_wait' => 0]);
        Http::preventStrayRequests();
    }

    private function fakeSource(?array $metadata = null, ?string $csv = null): void
    {
        $metadata ??= $this->metadata();
        $csv ??= $this->csv();
        Http::fake([
            '*/data.json' => fn () => Http::response($metadata ?? $this->metadata()),
            '*/recordings.csv' => fn () => Http::response($csv ?? $this->csv()),
            '*/refresh' => fn () => Http::response(['ok' => true]),
        ]);
    }

    public function test_full_server_sync_and_identical_snapshot_skip_without_second_import(): void
    {
        $this->fakeSource();
        $sync = app(Robotics1618Sync::class);
        $sync->enqueue('cli');
        $run = $sync->work();
        $this->assertSame('success', $run->status, $run->message ?? '');
        $this->assertEquals(24, AtlasTask::first()->approved_minutes);
        $this->assertDatabaseCount('recording_import_batches', 1);
        $this->travel(3)->minutes();
        $sync->enqueue('cli');
        $again = $sync->work();
        $this->assertSame('success', $again->status, $again->message ?? '');
        $this->assertTrue($again->result['unchanged_snapshot']);
        $this->assertDatabaseCount('recording_import_batches', 1);
    }

    public function test_invalid_last_csv_count_prevents_all_database_imports(): void
    {
        $this->fakeSource($this->metadata(2));
        $sync = app(Robotics1618Sync::class);
        $sync->enqueue('cli');
        $this->assertSame('failed', $sync->work()->status);
        $this->assertDatabaseCount('atlas_tasks', 0);
        $this->assertDatabaseCount('recording_import_runs', 0);
    }

    public function test_queue_deduplicates_manual_and_scheduled_and_honours_process_lock(): void
    {
        $sync = app(Robotics1618Sync::class);
        $one = $sync->enqueue('manual');
        $this->assertEquals($one->id, $sync->enqueue('scheduled')->id);
        $directory = storage_path('app/private/robotics1618');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $handle = fopen($directory.'/sync.lock', 'c');
        flock($handle, LOCK_EX);
        try {
            $this->assertNull($sync->work());
            $this->assertSame('queued', $one->fresh()->status);
            Http::assertNothingSent();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        $this->assertDatabaseCount('robotics_sync_runs', 1);
    }

    public function test_only_admin_can_request_and_status_is_protected_and_button_never_downloads(): void
    {
        $this->get('/admin/recordings/sync-status')->assertRedirect('/login');
        $worker = User::factory()->create(['role' => 'worker']);
        $this->actingAs($worker)->post('/admin/recordings/refresh-1618')->assertForbidden();
        $this->actingAs($worker)->get('/admin/recordings/sync-status')->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/recordings/refresh-1618')->assertRedirect(route('admin.recordings.index'));
        $this->post('/admin/recordings/refresh-1618')->assertRedirect();
        $this->get('/admin/recordings/sync-status')->assertOk()->assertJsonPath('status', 'queued');
        $this->get(route('admin.recordings.index'))->assertOk()->assertSee('Bot 1618')->assertSee('Sedang memperbarui')->assertSee('bot-1618-menu');
        $this->assertDatabaseCount('robotics_sync_runs', 1);
        Http::assertNothingSent();
    }

    public function test_qc_delta_baseline_real_changes_noop_stale_and_payroll_preserved(): void
    {
        $service = app(RecordingIngestor::class);
        $record = ['record_id' => str_repeat('b', 24), 'participant' => 'qc@example.com', 'task' => 'QC task',
            'recorded_utc' => now()->subDay()->toIso8601String(), 'duration_min' => 30,
            'billable_min' => 20, 'rejected_min' => 10, 'pending_min' => 0, 'deferred_min' => 0];
        $send = fn ($rec, $at) => $service->ingest(['observed_at' => $at->toIso8601String(), 'records' => [$rec]], 'atlas');
        $send($record, now()->subHours(3));
        $this->assertNull(AtlasTask::first()->qc_delta);
        $record['billable_min'] = 24;
        $record['rejected_min'] = 6;
        $send($record, now()->subHours(2));
        $changedAt = AtlasTask::first()->qc_changed_at;
        $this->assertSame(['approved' => 4.0, 'rejected' => -4.0], AtlasTask::first()->qc_delta);
        $send($record, now()->subHour());
        $this->assertTrue($changedAt->equalTo(AtlasTask::first()->qc_changed_at));
        $record['billable_min'] = 18;
        $record['rejected_min'] = 12;
        $result = $send($record, now()->subHours(4));
        $this->assertSame(1, $result['skipped']);
        $this->assertEquals(24, AtlasTask::first()->approved_minutes);
        $send($record, now());
        $this->assertSame(['approved' => -6.0, 'rejected' => 6.0], AtlasTask::first()->qc_delta);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.recordings.index'))->assertOk()->assertSee('QC task')->assertSee('6.00 m');
    }

    public function test_source_changed_mid_download_aborts_and_releases_slot(): void
    {
        $first = $this->metadata();
        $changed = $first;
        $changed['recordings_csv']['rows'] = 2;
        Http::fake(['*/data.json' => Http::sequence()->push($first)->push($first)->push($changed),
            '*/refresh' => Http::response(['ok' => true]), '*/recordings.csv' => Http::response($this->csv())]);
        $sync = app(Robotics1618Sync::class);
        $sync->enqueue('cli');
        $run = $sync->work();
        $this->assertSame('failed', $run->status);
        $this->assertNull($run->active_slot);
        $this->assertDatabaseCount('atlas_tasks', 0);
    }

    public function test_server_renews_login_without_following_external_redirects(): void
    {
        config(['services.robotics1618.username' => 'test-user', 'services.robotics1618.password' => 'test-pass']);
        $attempts = 0;
        Http::fake(function ($request) use (&$attempts) {
            if (str_ends_with($request->url(), '/login')) {
                return Http::response('', 302, ['Location' => '/kamerakita-ai/']);
            }
            if (str_ends_with($request->url(), '/data.json')) {
                return ++$attempts === 1 ? Http::response('', 302, ['Location' => '/login']) : Http::response($this->metadata());
            }
            if (str_ends_with($request->url(), '/refresh')) {
                return Http::response(['ok' => true]);
            }

            return Http::response($this->csv());
        });
        $sync = app(Robotics1618Sync::class);
        $sync->enqueue('cli');
        $run = $sync->work();
        $this->assertSame('success', $run->status, $run->message ?? '');
        Http::assertSent(fn ($request) => $request->url() === 'https://partners.1618robotics.com/login' && $request['username'] === 'test-user');
        Http::assertNotSent(fn ($request) => ! str_starts_with($request->url(), 'https://partners.1618robotics.com/'));
    }

    public function test_abandoned_run_is_released_but_recent_failure_has_cooldown(): void
    {
        $sync = app(Robotics1618Sync::class);
        $run = $sync->enqueue('cli');
        $run->update(['status' => 'running']); // No OS lock: simulates a killed worker.
        $this->assertNull($sync->work());
        $this->assertSame('failed', $run->fresh()->status);
        $this->assertNull($run->fresh()->active_slot);
        $this->assertEquals($run->id, $sync->enqueue('manual')->id);
        $this->travel(3)->minutes();
        $this->assertNotEquals($run->id, $sync->enqueue('manual')->id);
        Http::assertNothingSent();
    }

    public function test_operational_status_does_not_enqueue_or_contact_the_source(): void
    {
        $this->artisan('robotics1618:sync --status')->expectsOutputToContain('"enabled":true')->assertSuccessful();
        $this->assertDatabaseCount('robotics_sync_runs', 0);
        Http::assertNothingSent();
    }
}
