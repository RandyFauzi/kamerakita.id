<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_can_access_report_submission_form(): void
    {
        $user = User::factory()->create(['role' => 'verifikator']);
        $partner = Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => 'worker',
        ]);

        $response = $this->actingAs($user)->get('/submit-report');
        $response->assertStatus(200);
        $response->assertSee('Kirim Laporan Kerja Video');
    }

    public function test_worker_can_submit_valid_report(): void
    {
        Storage::fake('evidence');
        Storage::fake('backup');

        $user = User::factory()->create(['role' => 'verifikator']);
        $partner = Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => 'worker',
        ]);

        $postData = [
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 120,
            'evidence_email_image_path' => UploadedFile::fake()->image('email.jpg'),
            'evidence_app_quality_image_path' => UploadedFile::fake()->image('quality.jpg'),
        ];

        $response = $this->actingAs($user)->post('/submit-report', $postData);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('video_work_reports', [
            'partner_id' => $partner->id,
            'submitted_duration_minutes' => 120,
            'qc_status' => 'pending',
        ]);
    }
}
