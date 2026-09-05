<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollExportCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_export_uses_explicit_idr_columns_and_partner_rate(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $partner = Partner::factory()->create([
            'base_hourly_rate' => 60000,
            'bank_account_number' => '1234567890',
            'bank_account_owner' => 'Worker Satu',
            'bank_name' => 'BCA',
            'mitra_id' => 'KMK-900',
        ]);

        VideoWorkReport::factory()->create([
            'partner_id' => $partner->id,
            'qc_status' => 'approved',
            'payment_status' => 'unpaid',
            'approved_duration_minutes' => 90,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('payroll.export-csv'))
            ->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('"Mata Uang"', $csv);
        $this->assertStringContainsString('"Rate per Jam Rupiah"', $csv);
        $this->assertStringContainsString('"Total Nominal Rupiah"', $csv);
        $this->assertStringContainsString('KMK-900,IDR,60000,90,1.50,90000', $csv);
    }
}
