<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PrivateStudioPartnerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_TOKEN = 'secret-test-studio-token-1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.studio_integration.token', self::TEST_TOKEN);
    }

    private function withIntegrationToken(array $headers = []): array
    {
        return array_merge([
            'Authorization' => 'Bearer '.self::TEST_TOKEN,
            'Accept' => 'application/json',
        ], $headers);
    }

    private function createActiveParent(string $mitraId = 'KMK-001', string $role = Partner::ROLE_MAIN_ACCOUNT): Partner
    {
        $user = User::factory()->create([
            'role' => $role,
            'email' => strtolower($mitraId).'@example.com',
        ]);

        return Partner::create([
            'partner_role' => $role,
            'mitra_id' => $mitraId,
            'full_name' => 'Akun Utama '.$mitraId,
            'whatsapp_number' => '08123456789',
            'email' => $user->email,
            'status' => Partner::STATUS_ACTIVE,
            'user_id' => $user->id,
            'bypass_worker_bank_onboarding' => true,
        ]);
    }

    public function test_it_rejects_unauthorized_requests(): void
    {
        // No token
        $this->getJson(route('api.private.studio.parents'))
            ->assertStatus(401);

        // Wrong token
        $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
            ->getJson(route('api.private.studio.parents'))
            ->assertStatus(401);
    }

    public function test_it_lists_non_suspended_team_parent_partners_with_whatsapp(): void
    {
        $activeMain = $this->createActiveParent('KMK-001', Partner::ROLE_MAIN_ACCOUNT);
        $activeMitra = $this->createActiveParent('KMK-002', Partner::ROLE_MITRA);

        // Inactive parent (should also be included unless suspended)
        $inactive = Partner::create([
            'partner_role' => Partner::ROLE_MITRA,
            'mitra_id' => 'KMK-003',
            'full_name' => 'Inactive Parent',
            'whatsapp_number' => '08123456780',
            'email' => 'inactive@example.com',
            'status' => Partner::STATUS_INACTIVE,
        ]);

        // Suspended parent (must NOT be included)
        $suspended = Partner::create([
            'partner_role' => Partner::ROLE_MITRA,
            'mitra_id' => 'KMK-005',
            'full_name' => 'Suspended Parent',
            'whatsapp_number' => '08123456785',
            'email' => 'suspended@example.com',
            'status' => Partner::STATUS_SUSPENDED,
        ]);

        // Normal worker (must NOT be included)
        $worker = Partner::create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-004',
            'full_name' => 'Normal Worker',
            'whatsapp_number' => '08123456781',
            'email' => 'worker@example.com',
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $response = $this->withHeaders($this->withIntegrationToken())
            ->getJson(route('api.private.studio.parents'))
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($activeMain->id, $ids);
        $this->assertContains($activeMitra->id, $ids);
        $this->assertContains($inactive->id, $ids);
        $this->assertNotContains($suspended->id, $ids);
        $this->assertNotContains($worker->id, $ids);

        // Pastikan nomor WhatsApp ada pada payload
        $found = collect($response->json('data'))->firstWhere('id', $inactive->id);
        $this->assertEquals('08123456780', $found['whatsapp_number']);
    }

    public function test_it_creates_user_and_worker_partner_in_a_single_transaction(): void
    {
        $parent = $this->createActiveParent();

        $payload = [
            'full_name' => 'Mitra Studio Randy',
            'email' => 'mitra.randy@example.com',
            'password' => 'StrongPassword123!',
            'whatsapp_number' => '08987654321',
            'mitra_parent_id' => $parent->id,
            'phone_key' => 'merah',
            'account_name' => 'HP Merah Akun 1',
        ];

        $response = $this->withHeaders($this->withIntegrationToken())
            ->postJson(route('api.private.studio.partners.store'), $payload)
            ->assertStatus(201);

        $data = $response->json('data');
        $this->assertEquals('mitra.randy@example.com', $data['email']);
        $this->assertEquals(Partner::STATUS_ACTIVE, $data['status']);
        $this->assertEquals(Partner::ROLE_WORKER, $data['partner_role']);
        $this->assertMatchesRegularExpression('/^KMK-\d+$/', $data['mitra_id']);

        // Assert database records
        $this->assertDatabaseHas('users', [
            'email' => 'mitra.randy@example.com',
            'role' => Partner::ROLE_WORKER,
            'has_completed_onboarding' => 1,
        ]);

        $this->assertDatabaseHas('partners', [
            'email' => 'mitra.randy@example.com',
            'partner_role' => Partner::ROLE_WORKER,
            'status' => Partner::STATUS_ACTIVE,
            'mitra_parent_id' => $parent->id,
            'recruiter_partner_id' => $parent->id,
            'base_hourly_rate' => $parent->subWorkerHourlyRate(),
            'bypass_worker_bank_onboarding' => 1,
        ]);

        $partner = Partner::where('email', 'mitra.randy@example.com')->firstOrFail();
        $this->assertNotNull($partner->user);
        $this->assertEquals($partner->user->name, 'Mitra Studio Randy');
    }

    public function test_it_is_idempotent_on_retry_and_does_not_duplicate_worker(): void
    {
        $parent = $this->createActiveParent();

        $payload = [
            'full_name' => 'Mitra Idempotent',
            'email' => 'mitra.idempoten@example.com',
            'password' => 'StrongPassword123!',
            'whatsapp_number' => '08987654322',
            'mitra_parent_id' => $parent->id,
        ];

        // First attempt: creates
        $first = $this->withHeaders($this->withIntegrationToken())
            ->postJson(route('api.private.studio.partners.store'), $payload)
            ->assertStatus(201);

        $firstId = $first->json('data.id');

        // Retry with same payload: returns existing with status 200
        $second = $this->withHeaders($this->withIntegrationToken())
            ->postJson(route('api.private.studio.partners.store'), $payload)
            ->assertStatus(200);

        $this->assertEquals($firstId, $second->json('data.id'));

        $otherVendor = $this->createActiveParent('KMK-050', Partner::ROLE_MITRA);
        $this->withHeaders($this->withIntegrationToken())
            ->postJson(route('api.private.studio.partners.store'), [
                ...$payload,
                'mitra_parent_id' => $otherVendor->id,
            ])
            ->assertUnprocessable();

        $this->assertSame($parent->id, Partner::findOrFail($firstId)->mitra_parent_id);
        $this->assertEquals(1, Partner::where('email', 'mitra.idempoten@example.com')->count());
        $this->assertEquals(1, User::where('email', 'mitra.idempoten@example.com')->count());
    }

    public function test_it_rejects_creation_when_email_belongs_to_non_worker_account(): void
    {
        $parent = $this->createActiveParent();

        // Existing admin user
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $payload = [
            'full_name' => 'Non Mitra Attempt',
            'email' => 'admin@example.com',
            'password' => 'StrongPassword123!',
            'whatsapp_number' => '08987654323',
            'mitra_parent_id' => $parent->id,
        ];

        $this->withHeaders($this->withIntegrationToken())
            ->postJson(route('api.private.studio.partners.store'), $payload)
            ->assertStatus(422);
    }

    public function test_it_finds_worker_by_email(): void
    {
        $parent = $this->createActiveParent();

        // Not created yet -> returns null
        $response = $this->withHeaders($this->withIntegrationToken())
            ->getJson(route('api.private.studio.partners.show', ['email' => 'search@example.com']))
            ->assertOk();
        $this->assertNull($response->json('data'));

        // Create partner
        $user = User::factory()->create(['email' => 'search@example.com', 'role' => Partner::ROLE_WORKER]);
        Partner::create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-099',
            'full_name' => 'Search Mitra',
            'whatsapp_number' => '08123450000',
            'email' => 'search@example.com',
            'status' => Partner::STATUS_ACTIVE,
            'user_id' => $user->id,
            'mitra_parent_id' => $parent->id,
        ]);

        $found = $this->withHeaders($this->withIntegrationToken())
            ->getJson(route('api.private.studio.partners.show', ['email' => 'search@example.com']))
            ->assertOk();
        $this->assertEquals('KMK-099', $found->json('data.mitra_id'));
    }

    public function test_it_updates_worker_status_and_syncs_user_role(): void
    {
        $parent = $this->createActiveParent();
        $user = User::factory()->create(['email' => 'status.toggle@example.com', 'role' => Partner::ROLE_WORKER]);
        $partner = Partner::create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-077',
            'full_name' => 'Toggle Status Mitra',
            'whatsapp_number' => '08123457777',
            'email' => 'status.toggle@example.com',
            'status' => Partner::STATUS_ACTIVE,
            'user_id' => $user->id,
            'mitra_parent_id' => $parent->id,
        ]);

        // Suspend
        $this->withHeaders($this->withIntegrationToken())
            ->patchJson(route('api.private.studio.partners.status', $partner), ['status' => 'suspended'])
            ->assertOk();

        $this->assertEquals(Partner::STATUS_SUSPENDED, $partner->fresh()->status);
        $this->assertEquals('suspended', $user->fresh()->role);

        // Reactivate
        $this->withHeaders($this->withIntegrationToken())
            ->patchJson(route('api.private.studio.partners.status', $partner), ['status' => 'active'])
            ->assertOk();

        $this->assertEquals(Partner::STATUS_ACTIVE, $partner->fresh()->status);
        $this->assertEquals(Partner::ROLE_WORKER, $user->fresh()->role);
    }

    public function test_it_updates_worker_profile(): void
    {
        $parent = $this->createActiveParent();
        $user = User::factory()->create(['email' => 'edit.profile@example.com', 'role' => Partner::ROLE_WORKER]);
        $partner = Partner::create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-088',
            'full_name' => 'Old Full Name',
            'whatsapp_number' => '0811111111',
            'email' => 'edit.profile@example.com',
            'status' => Partner::STATUS_ACTIVE,
            'user_id' => $user->id,
            'mitra_parent_id' => $parent->id,
        ]);

        $newParent = $this->createActiveParent('KMK-009');

        $this->withHeaders($this->withIntegrationToken())
            ->putJson(route('api.private.studio.partners.update', $partner), [
                'full_name' => 'New Full Name',
                'whatsapp_number' => '0822222222',
                'mitra_parent_id' => $newParent->id,
            ])
            ->assertOk();

        $partner->refresh();
        $this->assertEquals('New Full Name', $partner->full_name);
        $this->assertEquals('0822222222', $partner->whatsapp_number);
        $this->assertEquals($newParent->id, $partner->mitra_parent_id);
        $this->assertEquals('New Full Name', $user->fresh()->name);
    }

    public function test_it_denies_detail_and_update_for_workers_without_a_team(): void
    {
        $worker = Partner::create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-090',
            'full_name' => 'Just a Worker',
            'whatsapp_number' => '0833333333',
            'email' => 'worker.only@example.com',
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $this->withHeaders($this->withIntegrationToken())
            ->getJson(route('api.private.studio.partners.detail', $worker))
            ->assertNotFound();

        $this->withHeaders($this->withIntegrationToken())
            ->patchJson(route('api.private.studio.partners.status', $worker), ['status' => 'suspended'])
            ->assertNotFound();

        $this->withHeaders($this->withIntegrationToken())
            ->putJson(route('api.private.studio.partners.update', $worker), [
                'full_name' => 'Illegal Edit',
                'whatsapp_number' => '0833333334',
                'mitra_parent_id' => $this->createActiveParent()->id,
            ])
            ->assertNotFound();
    }

    public function test_it_can_delete_worker_partner_without_locked_history(): void
    {
        $parent = $this->createActiveParent();
        $response = $this->withHeaders($this->withIntegrationToken())
            ->postJson(route('api.private.studio.partners.store'), [
                'full_name' => 'Akun Dihapus',
                'email' => 'hapus.mitra@example.com',
                'password' => 'StrongSecretPass123!',
                'whatsapp_number' => '08123456789',
                'mitra_parent_id' => $parent->id,
            ])
            ->assertStatus(201);

        $partnerId = $response->json('data.id');
        $partner = Partner::findOrFail($partnerId);
        $userId = $partner->user_id;

        $this->withHeaders($this->withIntegrationToken())
            ->deleteJson(route('api.private.studio.partners.destroy', $partner))
            ->assertOk()
            ->assertJson(['deleted' => true]);

        $this->assertDatabaseMissing('partners', ['id' => $partnerId]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}
