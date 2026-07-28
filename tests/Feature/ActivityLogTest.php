<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_login_logs_activity_automatically(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'activity' => 'auth.login',
        ]);
    }

    public function test_admin_can_view_activity_logs_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)
            ->get(route('activity-logs.index'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_view_activity_logs_page(): void
    {
        $user = User::factory()->create(['role' => 'verifikator', 'email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->get(route('activity-logs.index'));

        $response->assertForbidden();
    }

    public function test_report_rejection_logs_activity(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $partner = Partner::factory()->create();
        $report = VideoWorkReport::create([
            'partner_id' => $partner->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 60,
            'approved_duration_minutes' => 0,
            'qc_status' => 'pending',
            'payment_status' => 'unpaid',
            'evidence_email_image_path' => 'dummy1.jpg',
            'evidence_app_quality_image_path' => 'dummy1_quality.jpg',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('video-submissions.reject-report', $report), [
                'reason' => 'Video resolution is too low'
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'activity' => 'report.reject',
        ]);
    }
}
