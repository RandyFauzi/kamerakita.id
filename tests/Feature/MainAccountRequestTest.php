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

    public function test_guest_is_redirected_to_register_when_visiting_or_posting_request_page(): void
    {
        $guestGet = $this->get(route('main-account.request'));
        $guestGet->assertRedirect(route('register'));

        $guestPost = $this->post(route('main-account.request.store'), [
            'password' => 'anything',
        ]);
        $guestPost->assertRedirect(route('register'));
    }

    public function test_authenticated_worker_sees_prefilled_data_and_landing_page_assets(): void
    {
        $user = User::factory()->create([
            'email' => 'worker1@kamerakitaid.site',
            'role' => 'partner',
        ]);
        Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_WORKER,
            'email' => $user->email,
            'whatsapp_number' => '081122334455',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1122334455',
            'bank_account_owner' => 'Worker Satu',
        ]);

        $response = $this->actingAs($user)->get(route('main-account.request'));
        $response->assertOk();
        $response->assertSee('worker1@kamerakitaid.site');
        $response->assertSee('081122334455');
        $response->assertSee('Mandiri');
        $response->assertSee('1122334455');
        $response->assertSee('Worker Satu');
        $response->assertSee('assets/figma/benefits-wallet-figma.png');
        $response->assertSee('assets/figma/benefits-ai-figma.png');
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

        $response = $this->actingAs($user)->post(route('main-account.request.store'), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('main_account_requests', [
            'email' => 'worker2@kamerakitaid.site',
        ]);
    }

    public function test_worker_can_submit_request_and_edit_phone_and_bank_but_email_stays_locked(): void
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
            'bank_name' => 'BRI',
            'bank_account_number' => '999999',
            'bank_account_owner' => 'Budi Lama',
        ]);

        $response = $this->actingAs($user)->post(route('main-account.request.store'), [
            'email' => 'spoofed_other@kamerakitaid.site', // Must be ignored in favor of Auth::user()->email
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
            'phone_number' => '081234567890',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456789',
            'bank_account_owner' => 'Budi Santoso',
            'estimated_accounts' => '6-10 Akun',
            'status' => 'pending',
        ]);

        // Partner profile should also be updated with the edited values
        $partner->refresh();
        $this->assertEquals('081234567890', $partner->whatsapp_number);
        $this->assertEquals('BCA', $partner->bank_name);
        $this->assertEquals('123456789', $partner->bank_account_number);
        $this->assertEquals('Budi Santoso', $partner->bank_account_owner);
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

        $response = $this->actingAs($user)->post(route('main-account.request.store'), [
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

        $response = $this->actingAs($user)->post(route('main-account.request.store'), [
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
