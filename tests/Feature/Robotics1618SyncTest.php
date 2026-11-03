<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class Robotics1618SyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->travelTo(now()->startOfSecond());
        config(['services.atlas.bot_token' => 'atlas-test', 'services.robotics1618.bot_token' => '1618-test']);
    }

    private function record(array $changes = []): array
    {
        return array_replace(['record_id' => str_repeat('a', 24), 'participant' => 'worker@example.com',
            'task' => 'CSV recording', 'recorded_utc' => now()->subDay()->toISOString(), 'duration_sec' => 3600,
            'billable_hours' => 0.25, 'rejected_hours' => 0.25, 'pending_hours' => 0, 'deferred_hours' => 0.5,
            'status' => 'ready_for_review', 'rejection_reasons' => 'Example QA reason'], $changes);
    }

    private function payload(array $records, ?string $observed = null): array
    {
        return ['observed_at' => $observed ?? now()->subHour()->toISOString(), 'records' => $records,
            'import' => ['id' => (string) Str::uuid(), 'snapshot_hash' => str_repeat('b', 64),
                'total_records' => count($records), 'total_batches' => (int) ceil(count($records) / 500), 'batch_index' => 0,
                'metadata' => ['from' => now()->subMonth()->toDateString(), 'to' => now()->addMonth()->toDateString(), 'generated_utc' => now()->toISOString()]]];
    }

    private function csv(array $payload)
    {
        return $this->postJson('/api/bot/1618/sync', $payload, ['X-Bot-Token' => '1618-test']);
    }

    public function test_csv_creates_existing_recording_model_and_retry_is_idempotent(): void
    {
        $payload = $this->payload([$this->record()]);
        $this->csv($payload)->assertOk()->assertJsonPath('count', 1)->assertJsonPath('written', 1);
        $this->csv($payload)->assertOk()->assertJsonPath('count', 1);
        $this->assertDatabaseCount('atlas_tasks', 1);
        $this->assertDatabaseCount('recording_import_batches', 1);
        $task = AtlasTask::first();
        $this->assertSame('1618', $task->ingest_source);
        $this->assertEquals(30, $task->deferred_minutes);
        $this->assertEquals(15, $task->rejected_minutes);
        $this->assertSame('Example QA reason', $task->source_details['rejection_reasons']);
        $this->assertNotNull(DB::table('recording_import_runs')->first()->completed_at);
    }

    public function test_same_atlas_id_is_updated_by_fresh_csv_and_old_snapshot_cannot_revert_it(): void
    {
        $record = $this->record(['task' => 'Atlas original']);
        $this->postJson('/api/bot/atlas/sync', ['observed_at' => now()->subHours(2)->toISOString(), 'records' => [$record]], ['X-Bot-Token' => 'atlas-test'])->assertOk();
        $originalId = AtlasTask::first()->id;
        $this->csv($this->payload([$this->record(['task' => '1618 corrected', 'participant' => 'new@example.com'])]))->assertOk()->assertJsonPath('written', 1);
        $this->assertDatabaseCount('atlas_tasks', 1);
        $this->assertEquals($originalId, AtlasTask::first()->id);
        $this->csv($this->payload([$record], now()->subHours(3)->toISOString()))->assertOk()->assertJsonPath('skipped', 1);
        $this->assertSame('1618 corrected', AtlasTask::first()->task_name);
        $this->postJson('/api/bot/atlas/sync', ['observed_at' => now()->toISOString(), 'records' => [$record]], ['X-Bot-Token' => 'atlas-test'])->assertOk();
        $this->csv($this->payload([$this->record(['task' => 'must not win tie'])], now()->toISOString()))->assertOk()->assertJsonPath('skipped', 1);
        $this->assertSame('Atlas original', AtlasTask::first()->task_name);
        $this->assertDatabaseCount('atlas_tasks', 1);
    }

    public function test_tokens_are_separate_and_missing_categories_are_not_inferred(): void
    {
        $payload = $this->payload([$this->record()]);
        $this->postJson('/api/bot/1618/sync', $payload, ['X-Bot-Token' => 'atlas-test'])->assertUnauthorized();
        unset($payload['records'][0]['deferred_hours']);
        $this->csv($payload)->assertUnprocessable();
        $this->assertDatabaseCount('atlas_tasks', 0);
    }

    public function test_existing_user_is_linked_by_email_and_vendor_sees_record_in_same_page(): void
    {
        $vendor = \App\Models\Partner::factory()->create(['partner_role' => 'mitra', 'user_id' => \App\Models\User::factory()->create(['role' => 'mitra'])->id]);
        $user = \App\Models\User::factory()->create(['email' => 'worker@example.com', 'role' => 'worker']);
        \App\Models\Partner::factory()->create(['partner_role' => 'worker', 'mitra_parent_id' => $vendor->id, 'user_id' => $user->id, 'email' => null]);
        $this->csv($this->payload([$this->record()]))->assertOk();
        $this->assertEquals($user->id, AtlasTask::first()->atlasWorker->user_id);
        $this->actingAs($vendor->user)->get(route('vendor.recordings.index'))->assertOk()
            ->assertSee('CSV recording')->assertSee('Sumber: 1618');
    }

    public function test_changed_batch_or_manifest_is_rejected_after_acknowledgement(): void
    {
        $payload = $this->payload([$this->record()]);
        $this->csv($payload)->assertOk();
        $payload['records'][0]['task'] = 'Changed content';
        $this->csv($payload)->assertUnprocessable();
        $this->assertSame('CSV recording', AtlasTask::first()->task_name);
        $payload['import']['snapshot_hash'] = str_repeat('c', 64);
        $this->csv($payload)->assertUnprocessable();
    }

    public function test_ambiguous_account_mapping_rolls_back_import_instead_of_claiming_another_user(): void
    {
        \App\Models\User::factory()->create(['email' => 'worker@example.com']);
        \App\Models\Partner::factory()->create(['email' => 'worker@example.com']);
        $this->csv($this->payload([$this->record()]))->assertUnprocessable();
        $this->assertDatabaseCount('atlas_tasks', 0);
        $this->assertDatabaseCount('recording_import_runs', 0);
    }

    public function test_deployment_before_migration_keeps_atlas_available_and_blocks_1618(): void
    {
        \Illuminate\Support\Facades\Schema::shouldReceive('hasColumn')->with('atlas_tasks', 'source_observed_at')->andReturn(false);
        $this->postJson('/api/bot/atlas/sync', ['records' => [$this->record()]], ['X-Bot-Token' => 'atlas-test'])->assertOk();
        $this->csv($this->payload([$this->record()]))->assertStatus(503);
        $this->assertDatabaseCount('atlas_tasks', 1);
    }

    public function test_duplicate_ids_in_same_payload_or_in_later_batch_do_not_duplicate_data(): void
    {
        $record = $this->record();
        $this->csv($this->payload([$record, $record]))->assertUnprocessable();
        $records = [];
        for ($i = 0; $i < 501; $i++) {
            $records[] = $this->record(['record_id' => sprintf('%024x', $i)]);
        }
        $payload = $this->payload($records);
        $payload['records'] = array_slice($records, 0, 500);
        $this->csv($payload)->assertOk();
        $payload['import']['batch_index'] = 1;
        $payload['records'] = [$records[0]];
        $this->csv($payload)->assertUnprocessable();
        $this->assertDatabaseCount('atlas_tasks', 500);
        $this->assertNull(DB::table('recording_import_runs')->first()->completed_at);
        $payload['records'] = [$records[500]];
        $this->csv($payload)->assertOk();
        $this->assertNotNull(DB::table('recording_import_runs')->first()->completed_at);
    }
}
