<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCreateReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('evidence');
        \Illuminate\Support\Facades\Event::fake([\Illuminate\Database\Events\TransactionCommitted::class]);
    }

    public function test_admin_can_submit_atlas_report_with_worker_notes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partnerUser = User::factory()->create();
        $partner = Partner::factory()->create([
            'user_id' => $partnerUser->id,
            'partner_role' => 'worker',
            'full_name' => 'Mitra Test',
        ]);

        $this->actingAs($admin);

        // Simulate browser submitting form where minutes_data file input was empty
        $emptyQualityFile = new UploadedFile(
            '',
            '',
            'application/octet-stream',
            UPLOAD_ERR_NO_FILE,
            true
        );

        $response = $this->post(route('video-submissions.create-by-admin'), [
            'partner_id' => $partner->id,
            'project_name' => 'atlas',
            'submission_date' => now()->format('Y-m-d'),
            'submitted_duration_minutes' => 120,
            'worker_notes' => 'Catatan dari admin untuk mitra',
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg', 800, 600)->size(200),
            'evidence_app_quality_image_path' => $emptyQualityFile,
            'evidence_submitted_image_paths' => [
                UploadedFile::fake()->image('atlas1.jpg', 800, 600)->size(200),
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('video_work_reports', [
            'partner_id' => $partner->id,
            'project_name' => 'atlas',
            'submitted_duration_minutes' => 120,
            'worker_notes' => 'Catatan dari admin untuk mitra',
            'qc_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_admin_can_submit_minutes_data_report_with_worker_notes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partnerUser = User::factory()->create();
        $partner = Partner::factory()->create([
            'user_id' => $partnerUser->id,
            'partner_role' => 'worker',
            'full_name' => 'Mitra Test',
        ]);

        $this->actingAs($admin);

        // Simulate browser submitting form where atlas file input was empty
        $emptyFile = new UploadedFile(
            '',
            '',
            'application/octet-stream',
            UPLOAD_ERR_NO_FILE,
            true
        );

        $response = $this->post(route('video-submissions.create-by-admin'), [
            'partner_id' => $partner->id,
            'project_name' => 'minutes_data',
            'submission_date' => now()->format('Y-m-d'),
            'submitted_duration_minutes' => 90,
            'worker_notes' => 'Catatan minutes data',
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg', 800, 600)->size(200),
            'evidence_app_quality_image_path' => UploadedFile::fake()->image('quality.jpg', 800, 600)->size(200),
            'evidence_submitted_image_paths' => [$emptyFile],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('video_work_reports', [
            'partner_id' => $partner->id,
            'project_name' => 'minutes_data',
            'submitted_duration_minutes' => 90,
            'worker_notes' => 'Catatan minutes data',
            'qc_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_admin_can_submit_report_without_worker_notes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partnerUser = User::factory()->create();
        $partner = Partner::factory()->create([
            'user_id' => $partnerUser->id,
            'partner_role' => 'worker',
            'full_name' => 'Mitra Test',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('video-submissions.create-by-admin'), [
            'partner_id' => $partner->id,
            'project_name' => 'atlas',
            'submission_date' => now()->format('Y-m-d'),
            'submitted_duration_minutes' => 60,
            'worker_notes' => null,
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg', 800, 600)->size(200),
            'evidence_submitted_image_paths' => [
                UploadedFile::fake()->image('atlas1.jpg', 800, 600)->size(200),
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('video_work_reports', [
            'partner_id' => $partner->id,
            'worker_notes' => null,
        ]);
    }

    public function test_validation_fails_when_required_fields_missing()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('video-submissions.create-by-admin'), []);

        $response->assertSessionHasErrors([
            'partner_id',
            'project_name',
            'submission_date',
            'submitted_duration_minutes',
            'evidence_email_image_path',
        ]);
    }

    public function test_non_admin_cannot_access_create_by_admin()
    {
        $workerUser = User::factory()->create(['role' => 'user']);
        $this->actingAs($workerUser);

        $response = $this->post(route('video-submissions.create-by-admin'), []);
        $response->assertStatus(403);
    }
}
