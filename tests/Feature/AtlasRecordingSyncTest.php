<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AtlasRecordingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.atlas.bot_token' => 'local-test-token']);
    }

    private function record(array $changes = []): array
    {
        return array_replace([
            'record_id' => str_repeat('a', 24),
            'participant' => 'worker@example.com',
            'task' => 'Recording task',
            'recorded_utc' => '2026-10-15T01:00:00+07:00',
            'duration_sec' => 3600,
            'billable_hours' => 0.5,
            'rejected_hours' => 0,
            'pending_hours' => 0,
            'deferred_hours' => 0.5,
            'status' => 'Partially reviewed',
            'reconciliation' => 'Awaiting decision',
        ], $changes);
    }

    private function sync(array $records)
    {
        return $this->postJson('/api/bot/atlas/sync', ['records' => $records], ['X-Bot-Token' => 'local-test-token']);
    }

    public function test_deferred_is_separate_and_utc_is_normalized(): void
    {
        $this->sync([$this->record()])->assertOk()->assertJsonPath('count', 1);
        $this->assertDatabaseHas('atlas_tasks', [
            'approved_minutes' => 30, 'deferred_minutes' => 30, 'rejected_minutes' => 0,
            'review_minutes' => 0, 'task_date' => '2026-10-14',
            'recorded_at' => '2026-10-14 18:00:00', 'source_status' => 'Partially reviewed',
        ]);
    }

    public function test_resync_updates_metadata_precise_durations_and_worker_without_duplicates(): void
    {
        $this->sync([$this->record()])->assertOk();
        $id = AtlasTask::first()->id;
        $updated = $this->record([
            'task' => 'Corrected task', 'participant' => 'corrected@example.com',
            'recorded_utc' => '2026-10-16T12:00:00Z', 'duration_sec' => 3660,
            'billable_hours' => 0.504, 'rejected_hours' => 0.016,
        ]);
        $this->sync([$updated])->assertOk();
        $this->sync([$updated])->assertOk();
        $this->assertDatabaseCount('atlas_tasks', 1);
        $task = AtlasTask::with('atlasWorker')->findOrFail($id);
        $this->assertEqualsWithDelta(30.24, $task->approved_minutes, 0.00001);
        $this->assertSame('corrected@example.com', $task->atlasWorker->atlas_email);
        $this->assertSame('Corrected task', $task->task_name);
        $this->assertEquals(61, $task->worked_minutes);
        $this->assertSame('2026-10-16 12:00:00', $task->recorded_at);
    }

    public function test_incomplete_or_invalid_batch_writes_nothing(): void
    {
        $invalid = $this->record(['record_id' => str_repeat('b', 24), 'duration_sec' => -1]);
        $this->sync([$this->record(), $invalid])->assertUnprocessable();
        $this->assertDatabaseCount('atlas_tasks', 0);
        $missing = $this->record();
        unset($missing['rejected_hours']);
        $this->sync([$missing])->assertUnprocessable();
        $this->assertDatabaseCount('atlas_workers', 0);
    }

    public function test_missing_breakdown_is_not_assumed_rejected(): void
    {
        $this->sync([$this->record(['billable_hours' => 0, 'deferred_hours' => 0])])->assertOk();
        $this->assertDatabaseHas('atlas_tasks', ['rejected_minutes' => 0, 'status' => 'UNDER_REVIEW']);
    }

    public function test_recording_page_is_read_only_and_displays_deferred(): void
    {
        $this->sync([$this->record()])->assertOk();
        $task = AtlasTask::first();
        $before = $task->getAttributes();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/recordings')->assertOk()->assertSee('Deferred')->assertSee('30.00 m');
        $this->assertSame($before, $task->fresh()->getAttributes());
    }

    public function test_sync_requires_configured_shared_secret(): void
    {
        config(['services.atlas.bot_token' => null]);
        $this->sync([$this->record()])->assertUnauthorized();
        $this->assertDatabaseCount('atlas_tasks', 0);
    }

    public function test_ambiguous_existing_id_rolls_back_the_entire_batch(): void
    {
        $this->sync([$this->record()])->assertOk();
        $worker = AtlasWorker::create(['atlas_email' => 'duplicate@example.com']);
        $duplicate = AtlasTask::first()->replicate();
        $duplicate->atlas_worker_id = $worker->id;
        $duplicate->save();
        $this->sync([
            $this->record(['record_id' => str_repeat('c', 24), 'task' => 'Must roll back']),
            $this->record(),
        ])->assertUnprocessable();
        $this->assertDatabaseCount('atlas_tasks', 2);
        $this->assertDatabaseMissing('atlas_tasks', ['task_name' => 'Must roll back']);
    }
}
