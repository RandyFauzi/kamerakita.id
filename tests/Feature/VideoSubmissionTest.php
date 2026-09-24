<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('evidence');
        \Illuminate\Support\Facades\Event::fake([\Illuminate\Database\Events\TransactionCommitted::class]);
    }

    public function test_worker_can_submit_atlas_report()
    {
        $user = User::factory()->create();
        Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);
        $this->actingAs($user);

        $response = $this->post(route('video-submissions.submit-report.store'), [
            'project_name' => 'atlas',
            'submission_date' => now()->format('Y-m-d'),
            'submitted_duration_minutes' => 120,
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg', 1920, 1920)->size(150),
            'evidence_submitted_image_paths' => [
                UploadedFile::fake()->image('atlas1.jpg', 1920, 1920)->size(150),
                UploadedFile::fake()->image('atlas2.jpg', 1920, 1920)->size(150),
            ],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('video_work_reports', [
            'project_name' => 'atlas',
            'submitted_duration_minutes' => 120,
        ]);
    }

    public function test_worker_can_submit_minutes_data_report()
    {
        $user = User::factory()->create();
        Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);
        $this->actingAs($user);

        $response = $this->post(route('video-submissions.submit-report.store'), [
            'project_name' => 'minutes_data',
            'submission_date' => now()->format('Y-m-d'),
            'submitted_duration_minutes' => 60,
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg', 1920, 1920)->size(150),
            'evidence_app_quality_image_path' => UploadedFile::fake()->image('quality.jpg', 1920, 1920)->size(150),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('video_work_reports', [
            'project_name' => 'minutes_data',
            'submitted_duration_minutes' => 60,
        ]);
    }

    public function test_validation_fails_for_missing_required_fields()
    {
        $user = User::factory()->create();
        Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);
        $this->actingAs($user);

        $response = $this->post(route('video-submissions.submit-report.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'project_name',
            'submission_date',
            'submitted_duration_minutes',
            'evidence_email_image_path',
        ]);
    }

    public function test_returns_redirect_error_for_empty_payload_meaning_oversized()
    {
        $user = User::factory()->create();
        Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);
        $this->actingAs($user);

        // Simulate an empty $_POST but a positive CONTENT_LENGTH (PHP Drops Payload)
        $response = $this->call('POST', route('video-submissions.submit-report.store'), [], [], [], [
            'CONTENT_LENGTH' => 50000000,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Gagal mengirim laporan: Total ukuran file terlalu besar.');
    }
}
