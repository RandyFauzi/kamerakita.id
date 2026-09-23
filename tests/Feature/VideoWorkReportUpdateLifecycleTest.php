<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoWorkReportUpdateLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('evidence');
        Storage::fake('backup');
    }

    private function setupReport(string $projectName = 'atlas')
    {
        $user = User::factory()->create(['role' => 'worker']);
        $partner = Partner::factory()->create(['user_id' => $user->id, 'partner_role' => 'worker']);
        
        $report = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'qc_status' => 'rejected',
            'payment_status' => 'unpaid',
            'project_name' => $projectName,
            'evidence_email_image_path' => 'evidences/email/old-email.jpg',
            'evidence_app_quality_image_path' => $projectName === 'minutes_data' ? 'evidences/quality/old-quality.jpg' : null,
            'evidence_submitted_image_paths' => $projectName === 'atlas' ? ['evidences/submitted/old-submitted.jpg'] : null,
        ]);

        Storage::disk('evidence')->put('evidences/email/old-email.jpg', 'fake-image');
        if ($projectName === 'minutes_data') {
            Storage::disk('evidence')->put('evidences/quality/old-quality.jpg', 'fake-image');
        }
        if ($projectName === 'atlas') {
            Storage::disk('evidence')->put('evidences/submitted/old-submitted.jpg', 'fake-image');
        }

        return [$user, $report];
    }

    public function test_replace_email_only_keeps_quality_and_submitted()
    {
        [$user, $report] = $this->setupReport('atlas');

        $response = $this->actingAs($user)->put(route('video-submissions.edit-rejected-report.update', $report), [
            'project_name' => 'atlas',
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 60,
            'evidence_email_image_path' => UploadedFile::fake()->image('new-email.jpg'),
        ]);

        $response->assertRedirect();
        
        // Assert old email is deleted, but old submitted is kept
        Storage::disk('evidence')->assertMissing('evidences/email/old-email.jpg');
        Storage::disk('evidence')->assertExists('evidences/submitted/old-submitted.jpg');
    }

    public function test_atlas_to_minutes_data_cleans_up_submitted_and_requires_quality()
    {
        [$user, $report] = $this->setupReport('atlas');

        $response = $this->actingAs($user)->put(route('video-submissions.edit-rejected-report.update', $report), [
            'project_name' => 'minutes_data',
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 60,
            'evidence_app_quality_image_path' => UploadedFile::fake()->image('new-quality.jpg'),
        ]);

        $response->assertRedirect();
        
        // Assert old submitted is deleted
        Storage::disk('evidence')->assertMissing('evidences/submitted/old-submitted.jpg');
        
        $report->refresh();
        $this->assertNull($report->evidence_submitted_image_paths);
        $this->assertNotNull($report->evidence_app_quality_image_path);
    }
}
