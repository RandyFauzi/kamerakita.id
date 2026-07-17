<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VideoWorkReportEvidenceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_private_evidence_with_signed_url(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $partner = Partner::factory()->create();
        $report = VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'evidence_email_image_path' => 'evidences/email/report.jpg',
        ]);

        Storage::disk('local')->put('evidences/email/report.jpg', 'fake-image-content');

        $url = URL::temporarySignedRoute(
            'video-submissions.evidence.show',
            now()->addMinutes(5),
            ['report' => $report->id, 'type' => 'email']
        );

        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertSee('fake-image-content', false);
    }

    public function test_signed_evidence_url_rejects_non_internal_user(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'verifikator',
            'email_verified_at' => now(),
        ]);
        $report = VideoWorkReport::factory()->create([
            'evidence_email_image_path' => 'evidences/email/report.jpg',
        ]);

        Storage::disk('local')->put('evidences/email/report.jpg', 'fake-image-content');

        $url = URL::temporarySignedRoute(
            'video-submissions.evidence.show',
            now()->addMinutes(5),
            ['report' => $report->id, 'type' => 'email']
        );

        $this->actingAs($user)
            ->get($url)
            ->assertForbidden();
    }

    public function test_evidence_route_requires_valid_signature(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $report = VideoWorkReport::factory()->create([
            'evidence_email_image_path' => 'evidences/email/report.jpg',
        ]);

        Storage::disk('local')->put('evidences/email/report.jpg', 'fake-image-content');

        $this->actingAs($admin)
            ->get(route('video-submissions.evidence.show', ['report' => $report->id, 'type' => 'email']))
            ->assertForbidden();
    }
}
