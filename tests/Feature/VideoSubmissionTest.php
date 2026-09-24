<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VideoSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_worker_can_access_report_submission_form(): void
    {
        $user = User::factory()->create(['role' => 'worker']);
        Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);

        $response = $this->actingAs($user)->get(route('video-submissions.submit-report.create'));
        $response->assertStatus(200);
    }

    public function test_worker_can_submit_atlas_report(): void
    {
        Storage::fake('evidence');
        
        $user = User::factory()->create(['role' => 'worker']);
        $partner = Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);

        $postData = [
            'project_name' => 'atlas',
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 120,
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg'),
            'evidence_submitted_image_paths' => [UploadedFile::fake()->image('submit1.jpg')],
            '_ajax' => '1'
        ];

        $response = $this->actingAs($user)->postJson(route('video-submissions.submit-report.store'), $postData);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
                 
        $this->assertDatabaseHas('video_work_reports', [
            'partner_id' => $partner->id,
            'project_name' => 'atlas',
            'submitted_duration_minutes' => 120,
            'qc_status' => 'pending',
        ]);
        
        Queue::assertPushed(\App\Jobs\ProcessSubmittedReport::class);
    }

    public function test_worker_can_submit_minutes_data_report(): void
    {
        Storage::fake('evidence');
        
        $user = User::factory()->create(['role' => 'worker']);
        $partner = Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);

        $postData = [
            'project_name' => 'minutes_data',
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 90,
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg'),
            'evidence_app_quality_image_path' => UploadedFile::fake()->image('quality.jpg'),
            '_ajax' => '1'
        ];

        $response = $this->actingAs($user)->postJson(route('video-submissions.submit-report.store'), $postData);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
                 
        $this->assertDatabaseHas('video_work_reports', [
            'partner_id' => $partner->id,
            'project_name' => 'minutes_data',
            'qc_status' => 'pending',
        ]);
    }

    public function test_validation_fails_for_missing_required_fields(): void
    {
        $user = User::factory()->create(['role' => 'worker']);
        Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);

        $response = $this->actingAs($user)->postJson(route('video-submissions.submit-report.store'), ['_ajax' => '1']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['project_name', 'submission_date', 'submitted_duration_minutes', 'evidence_email_image_path']);
    }

    public function test_returns_413_for_empty_payload_meaning_oversized(): void
    {
        $user = User::factory()->create(['role' => 'worker']);
        Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);

        // Simulate empty POST body but with CONTENT_LENGTH > 0
        $response = $this->actingAs($user)
            ->withServerVariables(['CONTENT_LENGTH' => 1000])
            ->postJson(route('video-submissions.submit-report.store'), []);

        $response->assertStatus(413)
                 ->assertJson(['success' => false]);
    }
}
