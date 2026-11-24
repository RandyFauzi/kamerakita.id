<?php

namespace Tests\Feature;

use App\Models\{AtlasTask, AtlasWorker, Partner, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PartnerRecordingHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function recording(?int $userId, string $email, string $name): void
    {
        $worker = AtlasWorker::create(['user_id' => $userId, 'atlas_email' => $email]);
        AtlasTask::create(['atlas_worker_id' => $worker->id, 'atlas_task_id' => bin2hex(random_bytes(12)),
            'task_name' => $name, 'task_date' => '2026-11-20', 'recorded_at' => '2026-11-20 10:00:00',
            'worked_minutes' => 60, 'approved_minutes' => 30, 'review_minutes' => 15,
            'rejected_minutes' => 10, 'deferred_minutes' => 5, 'ingest_source' => '1618']);
    }

    public function test_eye_link_is_available_for_every_role_and_group_even_without_recordings(): void
    {
        Http::preventStrayRequests();
        $admin = User::factory()->create(['role' => 'admin']);
        $partners = collect(Partner::ALL_ROLES)->map(fn ($role) => Partner::factory()->create([
            'partner_role' => $role, 'group_name' => 'SPECTRE - (1618 Team)', 'full_name' => 'History '.$role]));
        $response = $this->actingAs($admin)->get(route('partners.index', ['per_page' => 'all']))->assertOk();
        foreach ($partners as $partner) {
            $response->assertSee(route('admin.recordings.index', ['partner_id' => $partner->id]))
                ->assertSee('Lihat riwayat recording '.$partner->full_name);
        }
    }

    public function test_account_and_team_scope_are_exact_and_preserved_by_other_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (Partner::TEAM_PARENT_ROLES as $role) {
            $parent = Partner::factory()->create(['partner_role' => $role, 'email' => $role.'-old@example.com']);
            $child = Partner::factory()->create(['partner_role' => 'worker', 'mitra_parent_id' => $parent->id]);
            $outside = Partner::factory()->create(['partner_role' => 'worker']);
            $this->recording($parent->user_id, $role.'-linked@example.com', 'OWN '.$role);
            $this->recording(null, strtolower($parent->user->email), 'UNLINKED '.$role);
            $this->recording($child->user_id, $role.'-child@example.com', 'CHILD '.$role);
            $this->recording($outside->user_id, $parent->email, 'FOREIGN-LINK '.$role);
            $filters = ['partner_id' => $parent->id, 'source' => '1618'];
            $this->actingAs($admin)->get(route('admin.recordings.index', $filters + ['account_scope' => 'account']))->assertOk()
                ->assertSee('OWN '.$role)->assertSee('UNLINKED '.$role)->assertDontSee('CHILD '.$role)
                ->assertDontSee('FOREIGN-LINK '.$role)
                ->assertSee('name="partner_id"', false)
                ->assertViewHas('stats', fn ($stats) => (float) $stats['total_worked'] === 120.0);
            $this->get(route('admin.recordings.index', $filters))->assertOk()
                ->assertSee('CHILD '.$role)->assertDontSee('FOREIGN-LINK '.$role)
                ->assertViewHas('trend', fn ($trend) => (float) $trend->sum('worked') === 180.0);
            $this->get(route('admin.recordings.index', ['partner_id' => $child->id]))->assertOk()
                ->assertSee('CHILD '.$role)->assertDontSee('OWN '.$role)->assertDontSee('FOREIGN-LINK '.$role);
            $this->actingAs($child->user)->get(route('admin.recordings.index', $filters + ['account_scope' => 'account']))->assertForbidden();
        }
    }

    public function test_unlinked_stale_email_cannot_claim_another_users_recordings(): void
    {
        $owner = User::factory()->create(['email' => 'actual-owner@example.com']);
        $partner = Partner::factory()->create(['email' => $owner->email]);
        $this->recording(null, $owner->email, 'NOT-THIS-ACCOUNT');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.recordings.index', ['partner_id' => $partner->id]))->assertOk()
            ->assertDontSee('NOT-THIS-ACCOUNT')
            ->assertViewHas('recordings', fn ($rows) => $rows->total() === 0);
    }
}
