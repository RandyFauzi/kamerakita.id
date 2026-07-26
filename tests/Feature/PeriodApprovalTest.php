<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use App\Models\PeriodApproval;
use App\Services\PeriodService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_service_range_calculation()
    {
        // 1. Saturday 2026-07-25
        $date = Carbon::parse('2026-07-25');
        $range = PeriodService::getPeriodRange($date);
        $this->assertEquals('2026-07-25', $range['start']->format('Y-m-d'));
        $this->assertEquals('2026-07-30', $range['end']->format('Y-m-d')); // Thursday

        // 2. Monday 2026-07-27
        $date = Carbon::parse('2026-07-27');
        $range = PeriodService::getPeriodRange($date);
        $this->assertEquals('2026-07-25', $range['start']->format('Y-m-d'));
        $this->assertEquals('2026-07-30', $range['end']->format('Y-m-d'));

        // 3. Friday 2026-07-31
        $date = Carbon::parse('2026-07-31');
        $range = PeriodService::getPeriodRange($date);
        $this->assertEquals('2026-07-25', $range['start']->format('Y-m-d'));
        $this->assertEquals('2026-07-30', $range['end']->format('Y-m-d'));
    }

    public function test_admin_can_save_draft_period_approval()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create();

        // Create a daily report under the period 2026-07-25 to 2026-07-30
        $report = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'submission_date' => '2026-07-27',
            'submitted_duration_minutes' => 60,
            'approved_duration_minutes' => 0,
            'qc_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('video-submissions.save-draft'), [
                'partner_id' => $partner->id,
                'period_start_date' => '2026-07-25',
                'period_end_date' => '2026-07-30',
                'approved_minutes' => 50,
                'verifier_notes' => 'Draft notes',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('period_approvals', [
            'partner_id' => $partner->id,
            'approved_minutes' => 50,
            'status' => 'draft',
            'verifier_notes' => 'Draft notes',
        ]);

        // Daily report status should be updated to review but not approved
        $report->refresh();
        $this->assertEquals('on_review', $report->qc_status);
        $this->assertEquals(0, $report->approved_duration_minutes);
    }

    public function test_admin_can_finalize_period_approval_with_proportional_distribution()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create();

        // Create 2 daily reports under the period 2026-07-25 to 2026-07-30
        $report1 = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'submission_date' => '2026-07-27',
            'submitted_duration_minutes' => 40,
            'approved_duration_minutes' => 0,
            'qc_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $report2 = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'submission_date' => '2026-07-28',
            'submitted_duration_minutes' => 80,
            'approved_duration_minutes' => 0,
            'qc_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('video-submissions.finalize'), [
                'partner_id' => $partner->id,
                'period_start_date' => '2026-07-25',
                'period_end_date' => '2026-07-30',
                'approved_minutes' => 90,
                'verifier_notes' => 'Final SOP approved',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('period_approvals', [
            'partner_id' => $partner->id,
            'approved_minutes' => 90,
            'status' => 'approved',
            'verifier_notes' => 'Final SOP approved',
        ]);

        $report1->refresh();
        $report2->refresh();

        // 40 / 120 * 90 = 30 mins
        $this->assertEquals('approved', $report1->qc_status);
        $this->assertEquals(30, $report1->approved_duration_minutes);

        // 80 / 120 * 90 = 60 mins
        $this->assertEquals('approved', $report2->qc_status);
        $this->assertEquals(60, $report2->approved_duration_minutes);
    }

    public function test_admin_can_reject_daily_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create();
        $report = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'submission_date' => '2026-07-27',
            'submitted_duration_minutes' => 60,
            'qc_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('video-submissions.reject-report', $report->id), [
                'reason' => 'Fake screenshot or typo duration',
            ]);

        $response->assertRedirect();
        
        $report->refresh();
        $this->assertEquals('rejected', $report->qc_status);
        $this->assertEquals(0, $report->approved_duration_minutes);
        $this->assertEquals('Fake screenshot or typo duration', $report->verifier_notes);
    }

    public function test_admin_can_delete_daily_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create();
        $report = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'submission_date' => '2026-07-27',
            'submitted_duration_minutes' => 60,
            'qc_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('video-submissions.destroy', $report->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('video_work_reports', ['id' => $report->id]);
    }

    public function test_admin_can_restore_rejected_daily_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create();
        $report = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'submission_date' => '2026-07-27',
            'submitted_duration_minutes' => 60,
            'qc_status' => 'rejected',
            'verifier_notes' => 'Some rejection notes',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('video-submissions.restore-report', $report->id));

        $response->assertRedirect();
        
        $report->refresh();
        $this->assertEquals('on_review', $report->qc_status);
        $this->assertNull($report->verifier_notes);
    }

    public function test_admin_can_export_pdf_report_for_period()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create();
        VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'submission_date' => '2026-07-27',
            'submitted_duration_minutes' => 60,
            'qc_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('video-submissions.export-pdf', ['period' => '2026-07-25|2026-07-30']));

        $response->assertOk();
        $response->assertViewIs('video-submissions.export-pdf');
        $response->assertSee($partner->full_name);
    }
}
