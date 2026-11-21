<?php

namespace Tests\Feature;

use App\Models\ActivationCode;
use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\CapturedEmail;
use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use App\Services\CalculatePartnerMetricsService;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainAccountRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_main_account_partner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $storeResponse = $this->actingAs($admin)->post(route('partners.store'), [
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'mitra_id' => 'KMK-501',
            'full_name' => 'Induk Kontributor Satu',
            'whatsapp_number' => '6281234567890',
            'email' => 'induk1@kamerakitaid.site',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'has_headstrap' => 1,
            'status' => 'active',
            'is_client_registered' => 1,
            'base_hourly_rate' => 60000,
        ]);

        $storeResponse->assertRedirect(route('partners.index'));
        $this->assertDatabaseHas('partners', [
            'mitra_id' => 'KMK-501',
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'base_hourly_rate' => 60000,
        ]);

        $partner = Partner::where('mitra_id', 'KMK-501')->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put(route('partners.update', $partner), [
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'full_name' => 'Induk Kontributor Updated',
            'whatsapp_number' => '6281234567891',
            'email' => 'induk1@kamerakitaid.site',
            'has_headstrap' => 1,
            'status' => 'active',
            'is_client_registered' => 1,
            'base_hourly_rate' => 60000,
        ]);

        $updateResponse->assertRedirect(route('partners.index'));
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'full_name' => 'Induk Kontributor Updated',
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
        ]);
    }

    public function test_main_account_creates_sub_worker_with_60k_rate_and_bank_bypass(): void
    {
        $mainUser = User::factory()->create(['role' => 'main_account']);
        $mainAccount = Partner::factory()->create([
            'user_id' => $mainUser->id,
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'full_name' => 'Owner Akun Induk',
            'bank_name' => 'BCA',
            'bank_account_number' => '777888999',
            'bank_account_owner' => 'Owner Akun Induk',
            'payment_method' => 'bank_transfer',
            'bypass_worker_bank_onboarding' => true,
            'base_hourly_rate' => 60000,
        ]);

        $response = $this->actingAs($mainUser)->post(route('vendor.workers.store'), [
            'name' => 'Sub Akun 01',
            'username' => 'subakun01',
            'whatsapp_number' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors()->assertSessionHas('success');

        $subUser = User::where('email', 'subakun01@kamerakitaid.site')->firstOrFail();
        $this->assertTrue((bool) $subUser->has_completed_onboarding);

        $subPartner = Partner::where('user_id', $subUser->id)->firstOrFail();
        $this->assertSame(Partner::ROLE_WORKER, $subPartner->partner_role);
        $this->assertSame($mainAccount->id, $subPartner->mitra_parent_id);
        $this->assertSame(60000, (int) $subPartner->base_hourly_rate);
        $this->assertSame('BCA', $subPartner->bank_name);
        $this->assertSame('777888999', $subPartner->bank_account_number);
    }

    public function test_mitra_creates_worker_with_50k_rate_and_10k_commission_total_60k(): void
    {
        $mitraUser = User::factory()->create(['role' => 'mitra']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => Partner::ROLE_MITRA,
            'bank_name' => 'BRI',
            'bank_account_number' => '111222333',
            'bank_account_owner' => 'Mitra Owner',
            'bypass_worker_bank_onboarding' => true,
        ]);

        $this->actingAs($mitraUser)->post(route('vendor.workers.store'), [
            'name' => 'Mitra Worker 01',
            'username' => 'mitraworker01',
            'whatsapp_number' => '081299998888',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $worker = Partner::where('email', 'mitraworker01@kamerakitaid.site')->firstOrFail();
        $this->assertSame(50000, (int) $worker->base_hourly_rate);

        VideoWorkReport::create([
            'partner_id' => $worker->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 120,
            'approved_duration_minutes' => 120,
            'qc_status' => 'approved',
            'payment_status' => 'unpaid',
            'evidence_email_image_path' => 'dummy.jpg',
        ]);

        $metrics = app(CalculatePartnerMetricsService::class)->getMitraMetrics($mitra);
        $this->assertSame(50000, $metrics['sub_worker_hourly_rate']);
        $this->assertSame(10000, $metrics['commission_hourly_rate']);
        $this->assertEquals(100000, $metrics['team_pending_earnings']);
        $this->assertEquals(20000, $metrics['commission_pending_earnings']);

        $paymentsResponse = $this->actingAs($mitraUser)->get(route('vendor.payments.index'));
        $paymentsResponse->assertOk();
        $paymentsResponse->assertSee('120.000');
    }

    public function test_main_account_metrics_and_payments_use_60k_rate_with_zero_commission(): void
    {
        $mainUser = User::factory()->create(['role' => 'main_account']);
        $mainAccount = Partner::factory()->create([
            'user_id' => $mainUser->id,
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'base_hourly_rate' => 60000,
        ]);

        $subWorker = Partner::factory()->create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mainAccount->id,
            'base_hourly_rate' => 60000,
        ]);

        VideoWorkReport::create([
            'partner_id' => $subWorker->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 120,
            'approved_duration_minutes' => 120, // 2 hours
            'qc_status' => 'approved',
            'payment_status' => 'unpaid',
            'evidence_email_image_path' => 'dummy.jpg',
        ]);

        $metrics = app(CalculatePartnerMetricsService::class)->getMitraMetrics($mainAccount);
        $this->assertSame(60000, $metrics['sub_worker_hourly_rate']);
        $this->assertSame(0, $metrics['commission_hourly_rate']);
        $this->assertEquals(0, $metrics['commission_pending_earnings']);
        $this->assertEquals(120000, $metrics['team_pending_earnings']);
        $this->assertEquals(120000, $metrics['team_total_earnings']);

        $this->actingAs($mainUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('MAIN ACCOUNT HUB')
            ->assertSee('120.000');

        $this->actingAs($mainUser)->get(route('vendor.workers.index'))
            ->assertOk()
            ->assertSee('Manajemen Sub-Akun')
            ->assertSee('60.000/jam');

        $this->actingAs($mainUser)->get(route('vendor.reports.index'))->assertOk();
        $this->actingAs($mainUser)->get(route('vendor.recordings.index'))->assertOk();
        $this->actingAs($mainUser)->get(route('vendor.payments.index'))
            ->assertOk()
            ->assertSee('120.000');
    }

    public function test_main_account_can_read_otp_emails_of_sub_workers_only(): void
    {
        $mainAccount = Partner::factory()->create(['partner_role' => Partner::ROLE_MAIN_ACCOUNT]);
        $otherMain = Partner::factory()->create(['partner_role' => Partner::ROLE_MAIN_ACCOUNT]);

        $subWorker = Partner::factory()->create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mainAccount->id,
        ]);
        $outsideWorker = Partner::factory()->create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $otherMain->id,
        ]);

        $ownOtp = CapturedEmail::create([
            'user_id' => $mainAccount->user_id,
            'sender_address' => 'otp@client.test',
            'subject' => 'OTP Main',
            'message_content' => '111222',
            'received_at' => now(),
        ]);
        $subOtp = CapturedEmail::create([
            'user_id' => $subWorker->user_id,
            'sender_address' => 'otp@client.test',
            'subject' => 'OTP Sub Akun',
            'message_content' => '654321',
            'received_at' => now(),
        ]);
        $outsideOtp = CapturedEmail::create([
            'user_id' => $outsideWorker->user_id,
            'sender_address' => 'otp@client.test',
            'subject' => 'OTP Outside',
            'message_content' => '999000',
            'received_at' => now(),
        ]);

        $this->actingAs($mainAccount->user)->getJson('/mailbox/api/emails?search=OTP')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/mailbox/api/emails/'.$ownOtp->id)->assertOk();
        $this->getJson('/mailbox/api/emails/'.$subOtp->id)->assertOk();
        $this->getJson('/mailbox/api/emails/'.$outsideOtp->id)->assertForbidden();
    }

    public function test_self_registration_via_main_account_referral_assigns_60k_rate(): void
    {
        ActivationCode::create([
            'code' => 'ACT-MAIN',
            'group_name' => 'Group A',
            'is_used' => false,
        ]);

        $mainAccount = Partner::factory()->create([
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'referral_code' => 'REF-MAIN-01',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '900100200',
            'bank_account_owner' => 'Main Induk',
            'bypass_worker_bank_onboarding' => true,
        ]);

        $response = $this->post(route('register'), [
            'name' => 'Sub Mandiri',
            'username' => 'submandiri01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activation_code' => 'ACT-MAIN',
            'referral_code' => 'REF-MAIN-01',
        ]);

        $response->assertRedirect(route('dashboard'));

        $subPartner = Partner::where('email', 'submandiri01@kamerakitaid.site')->firstOrFail();
        $this->assertSame($mainAccount->id, $subPartner->mitra_parent_id);
        $this->assertSame(60000, (int) $subPartner->base_hourly_rate);
        $this->assertSame('Mandiri', $subPartner->bank_name);
    }

    public function test_main_account_aggregates_sub_workers_in_leaderboard_and_payroll_fallback(): void
    {
        $mainAccount = Partner::factory()->create([
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'full_name' => 'Main Leaderboard Hub',
            'mitra_id' => 'KMK-700',
        ]);

        $sub1 = Partner::factory()->create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mainAccount->id,
            'base_hourly_rate' => 0,
        ]);

        $this->assertEquals(60000.0, $sub1->fresh()->effectiveHourlyRate());

        $atlas1 = AtlasWorker::create([
            'user_id' => $sub1->user_id,
            'atlas_email' => 'sub1@kamerakitaid.site',
        ]);

        AtlasTask::create([
            'atlas_worker_id' => $atlas1->id,
            'atlas_task_id' => 'T-MAIN-1',
            'task_name' => 'Task Main Test',
            'task_date' => now()->toDateString(),
            'worked_minutes' => 180,
            'approved_minutes' => 150,
            'review_minutes' => 0,
            'rejected_minutes' => 0,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('payments.manage'))
            ->assertOk();

        $leaderboard = collect(app(LeaderboardService::class)->getWeeklyLeaderboard(10));
        $entry = $leaderboard->firstWhere('entity_id', (string) $mainAccount->id);

        $this->assertNotNull($entry);
        $this->assertSame('Main Leaderboard Hub', $entry['name']);
        $this->assertEquals(180.0, $entry['worked_minutes']);
        $this->assertEquals(150.0, $entry['approved_minutes']);
    }
}
