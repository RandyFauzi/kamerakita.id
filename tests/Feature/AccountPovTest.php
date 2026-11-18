<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountPovTest extends TestCase
{
    use RefreshDatabase;

    private function createPovOwner(): User
    {
        return User::factory()->create([
            'name' => 'Randy Fauzi',
            'email' => 'randyfauzi24@gmail.com',
            'role' => 'superadmin',
            'email_verified_at' => now(),
            'has_completed_onboarding' => true,
        ]);
    }

    public function test_only_randyfauzi24_superadmin_can_use_account_pov_endpoints(): void
    {
        $otherSuperadmin = User::factory()->create([
            'email' => 'other-superadmin@example.com',
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $worker = User::factory()->create([
            'email' => 'worker@example.com',
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        foreach ([$otherSuperadmin, $admin, $worker] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->getJson(route('account-pov.search'))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->post(route('account-pov.enter', $worker))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->post(route('account-pov.leave'))
                ->assertForbidden();
        }
    }

    public function test_randy_with_non_superadmin_role_is_also_denied(): void
    {
        $downgraded = User::factory()->create([
            'email' => 'randyfauzi24@gmail.com',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $target = User::factory()->create(['role' => 'worker']);

        $this->actingAs($downgraded)
            ->post(route('account-pov.enter', $target))
            ->assertForbidden();
    }

    public function test_pov_owner_can_enter_switch_and_leave_pov_cleanly_without_login_log_pollution(): void
    {
        $owner = $this->createPovOwner();

        $workerUser = User::factory()->create([
            'name' => 'Worker Satu',
            'email' => 'worker1@kamerakitaid.site',
            'role' => 'worker',
            'email_verified_at' => now(),
            'has_completed_onboarding' => true,
        ]);

        $workerPartner = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'mitra_id' => 'KMK-101',
            'full_name' => 'Worker Satu',
            'email' => 'worker1@kamerakitaid.site',
            'partner_role' => Partner::ROLE_WORKER,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $mitraUser = User::factory()->create([
            'name' => 'Mitra Dua',
            'email' => 'mitra2@kamerakitaid.site',
            'role' => 'worker',
            'email_verified_at' => now(),
            'has_completed_onboarding' => true,
        ]);

        Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'mitra_id' => 'KMK-202',
            'full_name' => 'Mitra Dua',
            'email' => 'mitra2@kamerakitaid.site',
            'partner_role' => Partner::ROLE_MITRA,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        // Owner sees POV button on dashboard/partners
        $this->actingAs($owner)
            ->get(route('partners.index'))
            ->assertOk()
            ->assertSee('Mode POV')
            ->assertSee(route('account-pov.enter', $workerUser), false);

        // Enter Worker 1 POV
        $enterResponse = $this->actingAs($owner)
            ->post(route('account-pov.enter', $workerUser), [
                'return_url' => '/partners?role=worker',
            ]);

        $enterResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($workerUser);

        // While inside Worker POV, user sees worker POV banner and cannot access admin /partners directly
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mode POV')
            ->assertSee($workerPartner->email)
            ->assertSee('Keluar POV');

        $this->get(route('partners.index'))
            ->assertForbidden();

        // Can search and switch straight into Mitra 2 POV while already inside POV
        $this->getJson(route('account-pov.search', ['q' => 'KMK-202']))
            ->assertOk()
            ->assertJsonFragment([
                'email' => 'mitra2@kamerakitaid.site',
                'mitra_id' => 'KMK-202',
                'role_label' => 'Mitra',
            ]);

        $this->post(route('account-pov.enter', $mitraUser))
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($mitraUser);

        // Leave POV restores original owner and returns to original return_url
        $leaveResponse = $this->post(route('account-pov.leave'));
        $leaveResponse->assertRedirect('/partners?role=worker');
        $this->assertAuthenticatedAs($owner);

        // Ensure no fake auth.login / auth.logout logs were created; audit logs belong to owner
        $this->assertSame(0, ActivityLog::whereIn('activity', ['auth.login', 'auth.logout'])->count());
        $this->assertSame(2, ActivityLog::where('activity', 'account_pov.enter')->where('user_id', $owner->id)->count());
        $this->assertSame(1, ActivityLog::where('activity', 'account_pov.leave')->where('user_id', $owner->id)->count());
    }

    public function test_pov_exit_works_from_unverified_or_unonboarded_target_account(): void
    {
        $owner = $this->createPovOwner();

        $unverifiedWorker = User::factory()->create([
            'name' => 'Unverified Worker',
            'email' => 'unverified@kamerakitaid.site',
            'role' => 'worker',
            'email_verified_at' => null,
            'has_completed_onboarding' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('account-pov.enter', $unverifiedWorker), [
                'return_url' => 'https://evil.example/phish',
            ])
            ->assertRedirect(route('dashboard'));

        // /dashboard redirects un-onboarded target to /get-started
        $this->get(route('dashboard'))
            ->assertRedirect(route('onboarding.start'));

        // Onboarding wizard still renders Exit POV banner
        $this->get(route('onboarding.start'))
            ->assertOk()
            ->assertSee('Keluar POV');

        // Leaving POV rejects external open-redirect return_url and falls back to /dashboard
        $this->post(route('account-pov.leave'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_other_admin_never_sees_pov_ui_controls(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin2@example.com',
            'role' => 'admin',
            'email_verified_at' => now(),
            'has_completed_onboarding' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('partners.index'))
            ->assertOk()
            ->assertDontSee('account-pov-modal')
            ->assertDontSee('Buka Mode POV Akun');
    }
}
