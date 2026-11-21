<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ManagePaymentsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_access_and_legacy_payroll_routes_removed(): void
    {
        $worker = User::factory()->create(['role' => 'worker', 'email_verified_at' => now()]);
        $finance = User::factory()->create(['role' => 'finance', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'superadmin', 'email_verified_at' => now()]);

        $this->actingAs($worker)->get(route('payments.manage'))->assertForbidden();
        $this->actingAs($finance)->get(route('payments.manage'))->assertOk()->assertSeeText('Pembayaran');
        $this->actingAs($admin)->get(route('payments.manage'))->assertOk()->assertSeeText('Pembayaran');
        $this->actingAs($admin)->get('/admin/payrolls')->assertNotFound();
    }

    public function test_import_kamerakita_ai_csv_ignores_sub_sheets_links_partners_and_filters_cleanly(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin', 'email_verified_at' => now()]);

        $mitra = Partner::factory()->create([
            'partner_role' => Partner::ROLE_MITRA,
            'full_name' => 'Bos Mitra',
            'mitra_id' => 'KMK-100',
            'bank_name' => 'BCA',
            'bank_account_number' => '888000111',
            'bypass_worker_bank_onboarding' => true,
        ]);

        Partner::factory()->create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'email' => 'dimass211119@kamerakitaid.site',
            'full_name' => 'Dimas Tim',
            'mitra_id' => 'KMK-101',
        ]);

        Partner::factory()->create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => null,
            'email' => 'maki0808@kamerakitaid.site',
            'full_name' => 'Maki Langsung',
            'mitra_id' => 'KMK-102',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '999000222',
        ]);

        $csv = <<<CSV
kamerakita-ai: payment cycle 3 hours per worker,,,,,,,,,,
"Payment cycle 3 = 2026-11-01 to 2026-11-15 (UTC). Paid figures, frozen on 2026-11-20: 4,383.43 billable hours x US\$4.50 = US\$19,725.44.",,,,,,,,,,
,,,,,,,,,,
Worker email,Billable hours (to be paid),Rejected hours,Not checked yet (hours),Total hours,Approved share,Recordings,Main rejection reason,On the list now,Removed from the program,Yang Perlu dibayarkan
maki0808@kamerakitaid.site,39.18,1.36,0.51,41.05,0.966401,85,Fake Task,Yes,,2350800
dimass211119@kamerakitaid.site,"5,67","1,34","0,00","7,01",81%,14,No Segments Produced,Yes,,"Rp 340.200"
,,,,,,,,,Total Bayar,2691000
,,,,,,,,,Revenue,5000000
,,,,,,,,,Omzet,2309000
,,,,,,,,,Bagi 4,577250
Worker email,Billable hours (to be paid),Rejected hours,Not checked yet (hours),Total hours,Approved share,Recordings,Main rejection reason,On the list now,Removed from the program,,,
extra_sheet@kamerakitaid.site,10,0,0,10,1,20,Fake Task,Yes,0,800000,,
CSV;

        $file = UploadedFile::fake()->createWithContent('cycle3.csv', $csv);

        $this->actingAs($admin)
            ->post(route('payments.import-csv'), ['csv_file' => $file])
            ->assertRedirect();

        $cycle = PaymentCycle::firstOrFail();
        $this->assertSame('Payment Cycle 3 (2026-11-01 s/d 2026-11-15)', $cycle->name);
        $this->assertSame(5000000, $cycle->revenue_idr);
        $this->assertSame(2691000, $cycle->total_pay_idr);
        $this->assertDatabaseCount('payment_cycle_entries', 2);
        $this->assertDatabaseMissing('payment_cycle_entries', ['worker_email' => 'extra_sheet@kamerakitaid.site']);

        // Direct worker filter only shows Maki
        $this->actingAs($admin)
            ->get(route('payments.manage', ['cycle_id' => $cycle->id, 'account_type' => 'direct_worker']))
            ->assertOk()
            ->assertSee('maki0808@kamerakitaid.site')
            ->assertSee('Rp 2.350.800')
            ->assertDontSee('dimass211119@kamerakitaid.site');

        // Mitra & team filter shows Dimas + centralized parent bank
        $this->actingAs($admin)
            ->get(route('payments.manage', ['cycle_id' => $cycle->id, 'account_type' => 'mitra', 'parent_id' => $mitra->id]))
            ->assertOk()
            ->assertSee('dimass211119@kamerakitaid.site')
            ->assertSee('Rp 340.200')
            ->assertSee('888000111')
            ->assertDontSee('maki0808@kamerakitaid.site');

        // Mark paid & export CSV
        $entry = PaymentCycleEntry::where('worker_email', 'dimass211119@kamerakitaid.site')->firstOrFail();
        $this->actingAs($admin)
            ->post(route('payments.update-status'), [
                'entry_ids' => [$entry->id],
                'status' => 'paid',
            ])
            ->assertRedirect();

        $this->assertTrue($entry->fresh()->is_paid);

        $export = $this->actingAs($admin)->get(route('payments.export-csv', [
            'cycle_id' => $cycle->id,
            'variant' => 'sheet',
        ]));
        $export->assertOk();
        $this->assertStringContainsString('maki0808@kamerakitaid.site', $export->streamedContent());
        $this->assertStringContainsString('Yang Perlu dibayarkan', $export->streamedContent());
    }

    public function test_live_atlas_mode_and_snapshot_create_cycle(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $partner = Partner::factory()->create([
            'partner_role' => Partner::ROLE_WORKER,
            'email' => 'liveworker@kamerakitaid.site',
            'status' => Partner::STATUS_ACTIVE,
        ]);
        $aw = AtlasWorker::create([
            'user_id' => $partner->user_id,
            'atlas_email' => 'liveworker@kamerakitaid.site',
            'active' => true,
        ]);

        AtlasTask::create([
            'atlas_worker_id' => $aw->id,
            'atlas_task_id' => 'LIVE-1',
            'task_name' => 'Recording 1',
            'task_date' => '2026-11-10',
            'worked_minutes' => 150,
            'approved_minutes' => 120,
            'billable_minutes' => 120,
            'rejected_minutes' => 30,
            'review_minutes' => 0,
            'source_details' => ['rejection_reasons' => 'Fake Task'],
        ]);

        $this->actingAs($admin)
            ->get(route('payments.manage', ['source' => 'live']))
            ->assertOk()
            ->assertSee('liveworker@kamerakitaid.site')
            ->assertSee('2,00')
            ->assertSee('0,50')
            ->assertSee('80%')
            ->assertSee('Fake Task')
            ->assertSee('Rp 120.000');

        $this->actingAs($admin)
            ->post(route('payments.snapshot-live'), [
                'cycle_name' => 'Snapshot Nov',
                'worker_rate_idr' => 60000,
                'client_rate_usd' => 4.50,
                'revenue_idr' => 150000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payment_cycles', [
            'name' => 'Snapshot Nov',
            'total_pay_idr' => 120000,
            'revenue_idr' => 150000,
        ]);
    }
}
