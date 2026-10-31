<?php

namespace Tests\Feature;

use App\Models\ActivationCode;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraWorkerBankSyncAndOnboardingBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_worker_creation_by_mitra_auto_fills_bank_and_marks_onboarding_completed()
    {
        $mitraUser = User::factory()->create(['role' => 'user']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'full_name' => 'Mitra Pembina',
            'bank_name' => 'BCA',
            'bank_account_number' => '888999111',
            'bank_account_owner' => 'Mitra Pembina BCA',
            'payment_method' => 'bank_transfer',
        ]);

        $this->actingAs($mitraUser);

        $response = $this->post(route('vendor.workers.store'), [
            'name' => 'Pekerja Baru Tim',
            'username' => 'pekerjabaru',
            'whatsapp_number' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $workerUser = User::where('email', 'pekerjabaru@kamerakitaid.site')->first();
        $this->assertNotNull($workerUser);
        $this->assertTrue((bool)$workerUser->has_completed_onboarding);

        $workerPartner = Partner::where('user_id', $workerUser->id)->first();
        $this->assertNotNull($workerPartner);
        $this->assertEquals($mitra->id, $workerPartner->mitra_parent_id);
        $this->assertEquals('BCA', $workerPartner->bank_name);
        $this->assertEquals('888999111', $workerPartner->bank_account_number);
        $this->assertEquals('Mitra Pembina BCA', $workerPartner->bank_account_owner);

        // When the worker logs in, they should go directly to dashboard without seeing get-started wizard
        $this->actingAs($workerUser);
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertStatus(200);
    }

    public function test_self_registration_with_mitra_referral_code_auto_fills_bank_and_bypasses_wizard()
    {
        ActivationCode::create([
            'code' => 'ACTIVE-TEST',
            'group_name' => 'Group A',
            'is_used' => false,
        ]);

        $mitraUser = User::factory()->create(['role' => 'user']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'full_name' => 'Mitra Koordinator',
            'referral_code' => 'REF-MITRA-KOOR',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '135000123456',
            'bank_account_owner' => 'Mitra Koordinator Mandiri',
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->post(route('register'), [
            'name' => 'Pekerja Mandiri',
            'username' => 'pekerjamandiri',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activation_code' => 'ACTIVE-TEST',
            'referral_code' => 'REF-MITRA-KOOR',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));

        $workerUser = User::where('email', 'pekerjamandiri@kamerakitaid.site')->first();
        $this->assertNotNull($workerUser);
        $this->assertTrue((bool)$workerUser->has_completed_onboarding);

        $workerPartner = Partner::where('user_id', $workerUser->id)->first();
        $this->assertNotNull($workerPartner);
        $this->assertEquals($mitra->id, $workerPartner->mitra_parent_id);
        $this->assertEquals('Mandiri', $workerPartner->bank_name);
        $this->assertEquals('135000123456', $workerPartner->bank_account_number);
        $this->assertEquals('Mitra Koordinator Mandiri', $workerPartner->bank_account_owner);

        // Worker can access dashboard without being redirected to onboarding.start
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertStatus(200);
    }

    public function test_middleware_auto_syncs_existing_mitra_worker_and_bypasses_onboarding()
    {
        $mitra = Partner::factory()->create([
            'partner_role' => 'mitra',
            'bank_name' => 'BNI',
            'bank_account_number' => '0987654321',
            'bank_account_owner' => 'Mitra BNI',
        ]);

        $workerUser = User::factory()->create([
            'role' => 'worker',
            'has_completed_onboarding' => false,
        ]);

        $workerPartner = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => 'worker',
            'mitra_parent_id' => $mitra->id,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_owner' => null,
        ]);

        $this->actingAs($workerUser);

        // Visiting dashboard triggers EnsureOnboardingCompleted
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Verify partner bank and user onboarding state updated
        $workerUser->refresh();
        $workerPartner->refresh();

        $this->assertTrue((bool)$workerUser->has_completed_onboarding);
        $this->assertEquals('BNI', $workerPartner->bank_name);
        $this->assertEquals('0987654321', $workerPartner->bank_account_number);
        $this->assertEquals('Mitra BNI', $workerPartner->bank_account_owner);
    }

    public function test_sync_mitra_workers_bank_artisan_command()
    {
        $mitra = Partner::factory()->create([
            'partner_role' => 'mitra',
            'bank_name' => 'BRI',
            'bank_account_number' => '1122334455',
            'bank_account_owner' => 'Mitra BRI',
        ]);

        $workerUser = User::factory()->create([
            'role' => 'worker',
            'has_completed_onboarding' => false,
        ]);

        $workerPartner = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => 'worker',
            'mitra_parent_id' => $mitra->id,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_owner' => null,
        ]);

        $this->artisan('partner:sync-mitra-workers-bank')
            ->expectsOutputToContain('Successfully synced bank details for 1 worker(s)')
            ->assertExitCode(0);

        $workerUser->refresh();
        $workerPartner->refresh();

        $this->assertTrue((bool)$workerUser->has_completed_onboarding);
        $this->assertEquals('BRI', $workerPartner->bank_name);
        $this->assertEquals('1122334455', $workerPartner->bank_account_number);
    }
}
