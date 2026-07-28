<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class NotifyInactiveWorkersTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_command_notifies_inactive_workers_successfully(): void
    {
        // Mock WhatsAppNotificationService
        $this->mock(WhatsAppNotificationService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->with('08123456789', \Mockery::type('string'))
                ->andReturn(true);
        });

        // 1. Worker inactive for 2 days (latest report is 2 days ago)
        $twoDaysAgo = now()->subDays(2)->toDateString();
        $inactivePartner = Partner::factory()->create([
            'partner_role' => 'worker',
            'status' => 'active',
            'whatsapp_number' => '08123456789',
        ]);

        VideoWorkReport::create([
            'partner_id' => $inactivePartner->id,
            'submission_date' => $twoDaysAgo,
            'submitted_duration_minutes' => 60,
            'approved_duration_minutes' => 60,
            'qc_status' => 'approved',
            'payment_status' => 'paid',
            'evidence_email_image_path' => 'dummy1.jpg',
            'evidence_app_quality_image_path' => 'dummy1_quality.jpg',
        ]);

        // 2. Active worker (latest report is today)
        $activePartner = Partner::factory()->create([
            'partner_role' => 'worker',
            'status' => 'active',
            'whatsapp_number' => '08987654321',
        ]);

        VideoWorkReport::create([
            'partner_id' => $activePartner->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 60,
            'approved_duration_minutes' => 60,
            'qc_status' => 'approved',
            'payment_status' => 'paid',
            'evidence_email_image_path' => 'dummy2.jpg',
            'evidence_app_quality_image_path' => 'dummy2_quality.jpg',
        ]);

        // Run the command
        $exitCode = Artisan::call('partners:notify-inactive');

        $this->assertEquals(0, $exitCode);
    }
}
