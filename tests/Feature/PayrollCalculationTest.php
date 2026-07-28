<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_calculations_sum_correctly_in_csv_export(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $partner = Partner::factory()->create([
            'full_name' => 'John Doe',
            'base_hourly_rate' => 60000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_owner_name' => 'John Doe Owner',
        ]);

        // Create approved, unpaid work reports
        VideoWorkReport::create([
            'partner_id' => $partner->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 60,
            'approved_duration_minutes' => 60, // 1 hour
            'qc_status' => 'approved',
            'payment_status' => 'unpaid',
            'evidence_email_image_path' => 'dummy.jpg',
            'evidence_app_quality_image_path' => 'dummy_quality.jpg',
        ]);

        VideoWorkReport::create([
            'partner_id' => $partner->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 30,
            'approved_duration_minutes' => 30, // 0.5 hour
            'qc_status' => 'approved',
            'payment_status' => 'unpaid',
            'evidence_email_image_path' => 'dummy2.jpg',
            'evidence_app_quality_image_path' => 'dummy2_quality.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('payroll.export-csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        
        // John Doe has total 90 minutes = 1.5 hours. Rate 60,000. Total earnings: 90,000
        $this->assertStringContainsString('John Doe Owner', $content);
        $this->assertStringContainsString('1234567890', $content);
        $this->assertStringContainsString('90000', $content);
    }

    public function test_marking_as_paid_updates_all_approved_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create();

        $report = VideoWorkReport::create([
            'partner_id' => $partner->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 60,
            'approved_duration_minutes' => 60,
            'qc_status' => 'approved',
            'payment_status' => 'unpaid',
            'evidence_email_image_path' => 'dummy.jpg',
            'evidence_app_quality_image_path' => 'dummy_quality.jpg',
        ]);

        $response = $this->actingAs($admin)->post(route('payroll.mark-as-paid'));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('video_work_reports', [
            'id' => $report->id,
            'payment_status' => 'paid',
        ]);
    }
}
