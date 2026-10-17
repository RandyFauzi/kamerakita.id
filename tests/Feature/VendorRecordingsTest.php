<?php

namespace Tests\Feature;

use App\Models\{AtlasTask, AtlasWorker, Partner, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VendorRecordingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function vendor(): Partner
    {
        return Partner::factory()->create(['partner_role' => 'mitra', 'user_id' => User::factory()->create(['role' => 'mitra'])->id]);
    }

    private function worker(Partner $vendor, string $email): Partner
    {
        return Partner::factory()->create(['partner_role' => 'worker', 'mitra_parent_id' => $vendor->id,
            'email' => $email, 'user_id' => User::factory()->create(['role' => 'worker'])->id]);
    }

    private function recording(?int $userId, string $email, string $name): AtlasTask
    {
        $worker = AtlasWorker::create(['user_id' => $userId, 'atlas_email' => $email]);
        return AtlasTask::create(['atlas_worker_id' => $worker->id, 'atlas_task_id' => bin2hex(random_bytes(12)),
            'task_name' => $name, 'task_date' => '2026-10-16', 'recorded_at' => '2026-10-16 12:34:56',
            'worked_minutes' => 60, 'approved_minutes' => 30, 'review_minutes' => 10,
            'rejected_minutes' => 5, 'deferred_minutes' => 15, 'source_status' => 'processing']);
    }

    public function test_only_own_team_recordings_and_totals_are_visible(): void
    {
        $vendor = $this->vendor();
        $worker = $this->worker($vendor, 'own@example.com');
        $this->recording($worker->user_id, 'atlas-own@example.com', 'OWN-LINKED');
        $this->recording(null, 'own@example.com', 'OWN-EMAIL');
        $other = $this->worker($this->vendor(), 'other@example.com');
        $this->recording($other->user_id, 'other@example.com', 'FOREIGN-RECORDING');
        $this->actingAs($vendor->user)->get(route('vendor.recordings.index'))->assertOk()
            ->assertSee('OWN-LINKED')->assertSee('OWN-EMAIL')->assertDontSee('FOREIGN-RECORDING')
            ->assertDontSee('other@example.com')->assertSee('16 Oct 2026 12:34:56')
            ->assertViewHas('stats', fn ($stats) => $stats->recordings === 2 && (float) $stats->worked === 120.0
                && (float) $stats->rejected === 10.0 && (float) $stats->deferred === 30.0);
    }

    public function test_explicit_foreign_link_cannot_be_claimed_by_matching_team_email(): void
    {
        $vendor = $this->vendor();
        $this->worker($vendor, 'shared@example.com');
        $outsider = $this->worker($this->vendor(), 'foreign@example.com');
        $this->recording($outsider->user_id, 'shared@example.com', 'PRIVATE-FOREIGN');
        $this->actingAs($vendor->user)->get(route('vendor.recordings.index'))->assertOk()
            ->assertDontSee('PRIVATE-FOREIGN')->assertViewHas('stats', fn ($s) => $s->recordings === 0);
    }

    public function test_filters_never_escape_team_scope_and_summaries_follow_filters(): void
    {
        $vendor = $this->vendor();
        $worker = $this->worker($vendor, 'own@example.com');
        $own = $this->recording($worker->user_id, 'own@example.com', 'PACK-SUITCASE');
        $foreign = $this->recording(null, 'outside@example.com', 'OUTSIDE');
        $this->actingAs($vendor->user)->get(route('vendor.recordings.index', ['worker_id' => $foreign->atlas_worker_id]))
            ->assertOk()->assertDontSee('OUTSIDE')->assertDontSee('PACK-SUITCASE')
            ->assertViewHas('stats', fn ($s) => $s->recordings === 0);
        $this->get(route('vendor.recordings.index', ['search' => 'PACK', 'worker_id' => $own->atlas_worker_id, 'end_date' => '2026-10-16']))
            ->assertOk()->assertSee('PACK-SUITCASE')->assertViewHas('stats', fn ($s) => $s->recordings === 1);
        $this->get(route('vendor.recordings.index', ['start_date' => '2026-10-17']))
            ->assertOk()->assertDontSee('PACK-SUITCASE')->assertViewHas('stats', fn ($s) => $s->recordings === 0);
    }

    public function test_guests_and_non_mitra_cannot_access_recordings(): void
    {
        $this->get(route('vendor.recordings.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'worker']))->get(route('vendor.recordings.index'))->assertForbidden();
        $worker = Partner::factory()->create(['partner_role' => 'worker']);
        $this->actingAs($worker->user)->get(route('vendor.recordings.index'))->assertForbidden();
    }

    public function test_pagination_preserves_filters_and_totals_cover_all_filtered_rows(): void
    {
        $vendor = $this->vendor();
        $worker = $this->worker($vendor, 'own@example.com');
        $record = $this->recording($worker->user_id, 'own@example.com', 'PAGINATED');
        for ($i = 0; $i < 50; $i++) {
            $record->replicate()->fill(['atlas_task_id' => bin2hex(random_bytes(12))])->save();
        }
        $this->actingAs($vendor->user)->get(route('vendor.recordings.index', ['search' => 'PAGINATED', 'page' => 2]))
            ->assertOk()->assertSee('search=PAGINATED', false)
            ->assertViewHas('records', fn ($rows) => $rows->total() === 51 && $rows->count() === 1)
            ->assertViewHas('stats', fn ($s) => $s->recordings === 51 && (float) $s->worked === 3060.0);
    }

    public function test_empty_team_has_no_data_and_bad_dates_are_rejected(): void
    {
        $vendor = $this->vendor();
        $this->recording(null, 'outside@example.com', 'PRIVATE');
        $this->actingAs($vendor->user)->get(route('vendor.recordings.index'))->assertOk()
            ->assertSee('Belum ada recording yang sesuai')->assertDontSee('PRIVATE');
        $this->get(route('vendor.recordings.index', ['start_date' => 'invalid']))->assertSessionHasErrors('start_date');
        $this->get(route('vendor.recordings.index', ['start_date' => '2026-10-17', 'end_date' => '2026-10-16']))->assertSessionHasErrors('end_date');
    }
}
