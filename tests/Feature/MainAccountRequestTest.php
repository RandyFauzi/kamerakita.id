<?php

namespace Tests\Feature;

use App\Models\MainAccountRequest;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MainAccountRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_worker_can_view_request_page(): void
    {
        // Guest view
        $guestResponse = $this->get(route('main-account.request'));
        $guestResponse->assertOk();
        $guestResponse->assertSee('Pengajuan Akun Induk');
        $guestResponse->assertSee('Rate Penuh Rp60.000/Jam');

        // Authenticated worker view
        $user = User::factory()->create([
            'email' => 'worker1@kamerakitaid.site',
            'role' => 'partner',
        ]);
        Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $user->email,
        ]);

        $authResponse = $this->actingAs($user)->get(route('main-account.request'));
        $authResponse->assertOk();
        $authResponse->assertSee('worker1@kamerakitaid.site');
    }

    public function test_submission_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'worker2@kamerakitaid.site',
            'password' => Hash::make('secret123'),
            'role' => 'partner',
        ]);
        Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $user->email,
        ]);

        $response = $this->post(route('main-account.request.store'), [
            'email' => 'worker2@kamerakitaid.site',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('main_account_requests', [
            'email' => 'worker2@kamerakitaid.site',
        ]);
    }

    public function test_worker_can_submit_request_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Worker',
            'email' => 'budi@kamerakitaid.site',
            'password' => Hash::make('budiSecret123'),
            'role' => 'partner',
        ]);
        $partner = Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $user->email,
            'whatsapp_number' => '089999999999',
            'bank_name' => null,
        ]);

        $response = $this->actingAs($user)->post(route('main-account.request.store'), [
            'email' => 'budi@kamerakitaid.site',
            'password' => 'budiSecret123',
            'phone_number' => '081234567890',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456789',
            'bank_account_owner' => 'Budi Santoso',
            'estimated_accounts' => '6-10 Akun',
            'notes' => 'Punya 8 HP untuk rekam video harian.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('main_account_requests', [
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'email' => 'budi@kamerakitaid.site',
            'phone_number' => '089999999999',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456789',
            'bank_account_owner' => 'Budi Santoso',
            'estimated_accounts' => '6-10 Akun',
            'status' => 'pending',
        ]);

        // Partner profile should be updated with bank
        $partner->refresh();
        $this->assertEquals('BCA', $partner->bank_name);
    }

    public function test_worker_cannot_submit_duplicate_pending_request(): void
    {
        $user = User::factory()->create([
            'email' => 'worker_dup@kamerakitaid.site',
            'password' => Hash::make('password123'),
            'role' => 'partner',
        ]);
        $partner = Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $user->email,
        ]);

        MainAccountRequest::create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'email' => $user->email,
            'status' => 'pending',
        ]);

        $response = $this->post(route('main-account.request.store'), [
            'email' => 'worker_dup@kamerakitaid.site',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('info');
        $this->assertEquals(1, MainAccountRequest::where('partner_id', $partner->id)->count());
    }

    public function test_user_cannot_submit_if_already_main_account(): void
    {
        $user = User::factory()->create([
            'email' => 'main_already@kamerakitaid.site',
            'password' => Hash::make('password123'),
            'role' => 'partner',
        ]);
        Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'email' => $user->email,
        ]);

        $response = $this->post(route('main-account.request.store'), [
            'email' => 'main_already@kamerakitaid.site',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('main_account_requests', [
            'email' => 'main_already@kamerakitaid.site',
        ]);
    }

    public function test_admin_can_view_waiting_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $workerUser = User::factory()->create(['role' => 'partner']);
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $workerUser->email,
        ]);

        MainAccountRequest::create([
            'user_id' => $workerUser->id,
            'partner_id' => $worker->id,
            'email' => $workerUser->email,
            'status' => 'pending',
        ]);

        // Regular worker gets 403 Forbidden
        $workerResponse = $this->actingAs($workerUser)->get(route('admin.main-account-requests.index'));
        $workerResponse->assertForbidden();

        // Admin gets 200 OK
        $adminResponse = $this->actingAs($admin)->get(route('admin.main-account-requests.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee($workerUser->email);
    }

    public function test_admin_can_approve_request_and_role_switches_to_main_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $workerUser = User::factory()->create([
            'name' => 'Calon Induk',
            'email' => 'calon_induk@kamerakitaid.site',
            'role' => 'partner',
        ]);
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $workerUser->email,
            'whatsapp_number' => '081299998888',
            'referral_code' => null,
        ]);

        $requestRecord = MainAccountRequest::create([
            'user_id' => $workerUser->id,
            'partner_id' => $worker->id,
            'email' => $workerUser->email,
            'phone_number' => '081299998888',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.main-account-requests.approve', $requestRecord));

        $response->assertSessionHas('success');

        $requestRecord->refresh();
        $this->assertEquals('approved', $requestRecord->status);
        $this->assertEquals($admin->id, $requestRecord->approved_by);
        $this->assertNotNull($requestRecord->approved_at);

        $worker->refresh();
        $this->assertEquals(Partner::ROLE_MAIN_ACCOUNT, $worker->partner_role);
        $this->assertTrue($worker->isMainAccount());
        $this->assertTrue($worker->isTeamParent());
        $this->assertNotEmpty($worker->referral_code); // auto-generated
    }

    public function test_admin_can_reject_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $workerUser = User::factory()->create(['role' => 'partner']);
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $workerUser->email,
        ]);

        $requestRecord = MainAccountRequest::create([
            'user_id' => $workerUser->id,
            'partner_id' => $worker->id,
            'email' => $workerUser->email,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.main-account-requests.reject', $requestRecord), [
            'admin_notes' => 'Nomor rekening tidak valid.',
        ]);

        $response->assertSessionHas('info');

        $requestRecord->refresh();
        $this->assertEquals('rejected', $requestRecord->status);
        $this->assertEquals('Nomor rekening tidak valid.', $requestRecord->admin_notes);
        $this->assertNotNull($requestRecord->rejected_at);

        $worker->refresh();
        $this->assertEquals(Partner::ROLE_WORKER, $worker->partner_role); // role does not change
    }
}
