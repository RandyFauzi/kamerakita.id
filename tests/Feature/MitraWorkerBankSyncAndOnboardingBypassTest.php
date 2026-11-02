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
            'bypass_worker_bank_onboarding' => true,
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
            'bypass_worker_bank_onboarding' => true,
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

    public function test_when_mitra_disables_bypass_manual_worker_creation_requires_worker_onboarding()
    {
        $mitraUser = User::factory()->create(['role' => 'user']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'full_name' => 'Mitra Mandiri',
            'bank_name' => 'BCA',
            'bank_account_number' => '888999111',
            'bank_account_owner' => 'Mitra Mandiri BCA',
            'payment_method' => 'bank_transfer',
            'bypass_worker_bank_onboarding' => false, // TURNED OFF
        ]);

        $this->actingAs($mitraUser);

        $response = $this->post(route('vendor.workers.store'), [
            'name' => 'Worker Wajib Onboard',
            'username' => 'wajibofficial',
            'whatsapp_number' => '081234567899',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $workerUser = User::where('email', 'wajibofficial@kamerakitaid.site')->first();
        $this->assertNotNull($workerUser);
        $this->assertFalse((bool)$workerUser->has_completed_onboarding); // Must be FALSE

        $workerPartner = Partner::where('user_id', $workerUser->id)->first();
        $this->assertNotNull($workerPartner);
        $this->assertEquals($mitra->id, $workerPartner->mitra_parent_id);
        $this->assertNull($workerPartner->bank_name);
        $this->assertNull($workerPartner->bank_account_number);

        // When this worker logs in and visits dashboard, they should be redirected to get-started wizard
        $this->actingAs($workerUser);
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertRedirect(route('onboarding.start'));
    }

    public function test_when_mitra_disables_bypass_self_registration_requires_worker_onboarding()
    {
        ActivationCode::create([
            'code' => 'ACTIVE-TEST2',
            'group_name' => 'Group A',
            'is_used' => false,
        ]);

        $mitraUser = User::factory()->create(['role' => 'user']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'full_name' => 'Mitra Rekening Mandiri',
            'referral_code' => 'REF-MANDIRI-2',
            'bank_name' => 'CIMB',
            'bank_account_number' => '700123456',
            'bypass_worker_bank_onboarding' => false, // TURNED OFF
        ]);

        $response = $this->post(route('register'), [
            'name' => 'Pekerja Mandiri Rekening',
            'username' => 'mandirirek',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activation_code' => 'ACTIVE-TEST2',
            'referral_code' => 'REF-MANDIRI-2',
        ]);

        $response->assertSessionHasNoErrors();

        $workerUser = User::where('email', 'mandirirek@kamerakitaid.site')->first();
        $this->assertNotNull($workerUser);
        $this->assertFalse((bool)$workerUser->has_completed_onboarding);

        $workerPartner = Partner::where('user_id', $workerUser->id)->first();
        $this->assertNotNull($workerPartner);
        $this->assertEquals($mitra->id, $workerPartner->mitra_parent_id);
        $this->assertNull($workerPartner->bank_account_number);

        // Redirected to onboarding wizard
        $this->actingAs($workerUser);
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertRedirect(route('onboarding.start'));
    }

    public function test_vendor_can_toggle_worker_bank_bypass_from_dashboard()
    {
        $mitraUser = User::factory()->create(['role' => 'user']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'bypass_worker_bank_onboarding' => true,
        ]);

        $this->actingAs($mitraUser);

        // Toggle to false
        $response = $this->post(route('vendor.workers.toggle-bank-bypass'), [
            'bypass' => '0',
        ]);
        $response->assertSessionHas('success');
        $mitra->refresh();
        $this->assertFalse($mitra->bypassesWorkerBankOnboarding());

        // Toggle back to true
        $response2 = $this->post(route('vendor.workers.toggle-bank-bypass'), [
            'bypass' => '1',
        ]);
        $response2->assertSessionHas('success');
        $mitra->refresh();
        $this->assertTrue($mitra->bypassesWorkerBankOnboarding());
    }

    public function test_admin_can_toggle_worker_bank_bypass_from_partner_management()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = Partner::factory()->create([
            'partner_role' => 'mitra',
            'bypass_worker_bank_onboarding' => true,
        ]);

        $this->actingAs($admin);

        // Admin toggles to false via endpoint
        $response = $this->post(route('partners.toggle-bank-bypass', $mitra->id), [
            'bypass' => '0',
        ]);
        $response->assertSessionHas('success');
        $mitra->refresh();
        $this->assertFalse($mitra->bypassesWorkerBankOnboarding());
    }

    public function test_middleware_auto_syncs_existing_mitra_worker_and_bypasses_onboarding()
    {
        $mitra = Partner::factory()->create([
            'partner_role' => 'mitra',
            'bank_name' => 'BNI',
            'bank_account_number' => '0987654321',
            'bank_account_owner' => 'Mitra BNI',
            'bypass_worker_bank_onboarding' => true,
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
            'bypass_worker_bank_onboarding' => true,
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
