<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteRoleProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_non_admin_user_cannot_access_partner_management_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('partners.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('partners.create'))
            ->assertForbidden();
    }

    public function test_verified_non_admin_user_cannot_access_admin_user_management_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin-users.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin-users.create'))
            ->assertForbidden();
    }

    public function test_verified_non_admin_user_cannot_access_qc_routes_directly(): void
    {
        $user = User::factory()->create([
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('video-submissions.qc-room'))
            ->assertForbidden();
    }

    public function test_admin_can_render_qc_room(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('video-submissions.qc-room'))
            ->assertOk()
            ->assertSee('QC Video Room');
    }

    public function test_verified_non_admin_user_cannot_access_payroll_routes_directly(): void
    {
        $user = User::factory()->create([
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('payroll.export-csv'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('payroll.mark-as-paid'))
            ->assertForbidden();
    }
}
