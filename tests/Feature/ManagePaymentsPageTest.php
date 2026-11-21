<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ManagePaymentsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_access_and_empty_excel_upload_screen(): void
    {
        $worker = User::factory()->create(['role' => 'worker', 'email_verified_at' => now()]);
        $finance = User::factory()->create(['role' => 'finance', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'superadmin', 'email_verified_at' => now()]);

        $this->actingAs($worker)->get(route('payments.manage'))->assertForbidden();
        $this->actingAs($finance)->get(route('payments.manage'))->assertOk()->assertSeeText('Upload File Excel Payment Cycle');
        $this->actingAs($admin)->get(route('payments.manage'))->assertOk()->assertSeeText('Pembayaran');
        $this->actingAs($admin)->get('/admin/payrolls')->assertNotFound();
    }

    public function test_import_xlsx_focuses_kamerakita_ai_sheet_auto_creates_missing_accounts_with_badge_and_filters(): void
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

        // Build real multi-sheet .xlsx workbook (other sheet + kamerakita-ai sheet with formulas)
        $spreadsheet = new Spreadsheet();
        $otherSheet = $spreadsheet->getActiveSheet();
        $otherSheet->setTitle('Ringkasan Lain');
        $otherSheet->setCellValue('A1', 'Sheet lain');

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('kamerakita-ai');
        $sheet->fromArray([
            ['kamerakita-ai: payment cycle 3 hours per worker'],
            ['Payment cycle 3 = 2026-11-01 to 2026-11-15 (UTC). Paid figures, frozen on 2026-11-20: 4,383.43 billable hours x US$4.50 = US$19,725.44.'],
            [],
            ['Worker email', 'Billable hours (to be paid)', 'Rejected hours', 'Not checked yet (hours)', 'Total hours', 'Approved share', 'Recordings', 'Main rejection reason', 'On the list now', 'Removed from the program', 'Yang Perlu dibayarkan'],
            ['dimass211119@kamerakitaid.site', 5.67, 1.34, 0, 7.01, 0.81, 14, 'No Segments Produced', 'Yes', '', '=B5*60000'],
            ['baru_excel01@kamerakitaid.site', 10.00, 1.00, 0.50, 11.50, 0.91, 22, 'Fake Task', 'Yes', '', '=B6*60000'],
            ['', '', '', '', '', '', '', '', '', 'Total Bayar', '=SUM(K5:K6)'],
            ['', '', '', '', '', '', '', '', '', 'Revenue', 1500000],
        ], null, 'A1');

        $tmpPath = tempnam(sys_get_temp_dir(), 'pay_xlsx_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $file = new UploadedFile(
            $tmpPath,
            'payment-cycle-3.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($admin)
            ->post(route('payments.import-csv'), ['excel_file' => $file])
            ->assertRedirect();

        @unlink($tmpPath);

        $cycle = PaymentCycle::firstOrFail();
        $this->assertSame('Payment Cycle 3 (2026-11-01 s/d 2026-11-15)', $cycle->name);
        $this->assertSame('kamerakita-ai', $cycle->source_label);
        $this->assertSame(1500000, $cycle->revenue_idr);
        $this->assertSame(940200, $cycle->total_pay_idr);

        // Missing email should be automatically created in users, partners, atlas_workers & flagged
        $this->assertDatabaseHas('users', ['email' => 'baru_excel01@kamerakitaid.site', 'role' => 'worker']);
        $this->assertDatabaseHas('partners', ['email' => 'baru_excel01@kamerakitaid.site', 'partner_role' => Partner::ROLE_WORKER]);
        $this->assertDatabaseHas('atlas_workers', ['atlas_email' => 'baru_excel01@kamerakitaid.site']);
        $this->assertDatabaseHas('payment_cycle_entries', [
            'payment_cycle_id' => $cycle->id,
            'worker_email' => 'baru_excel01@kamerakitaid.site',
            'was_auto_created' => true,
            'amount_to_pay_idr' => 600000,
        ]);
        $this->assertDatabaseHas('payment_cycle_entries', [
            'payment_cycle_id' => $cycle->id,
            'worker_email' => 'dimass211119@kamerakitaid.site',
            'was_auto_created' => false,
            'amount_to_pay_idr' => 340200,
        ]);

        // Unwanted columns must not appear in table UI
        $page = $this->actingAs($admin)->get(route('payments.manage', ['cycle_id' => $cycle->id]));
        $page->assertOk()
            ->assertSee('Akun Baru (Auto DB)')
            ->assertSee('baru_excel01@kamerakitaid.site')
            ->assertSee('Rp 600.000')
            ->assertSee('dimass211119@kamerakitaid.site')
            ->assertSee('Rp 340.200')
            ->assertDontSeeText('Approved share')
            ->assertDontSeeText('Main rejection reason')
            ->assertDontSeeText('On the list now')
            ->assertDontSeeText('Removed from the program');

        // Auto-created filter only shows newly created accounts
        $this->actingAs($admin)
            ->get(route('payments.manage', ['cycle_id' => $cycle->id, 'account_type' => 'auto_created']))
            ->assertOk()
            ->assertSee('baru_excel01@kamerakitaid.site')
            ->assertDontSee('dimass211119@kamerakitaid.site');

        // Mitra & team filter shows Dimas + centralized parent bank
        $this->actingAs($admin)
            ->get(route('payments.manage', ['cycle_id' => $cycle->id, 'account_type' => 'mitra', 'parent_id' => $mitra->id]))
            ->assertOk()
            ->assertSee('dimass211119@kamerakitaid.site')
            ->assertSee('888000111')
            ->assertDontSee('baru_excel01@kamerakitaid.site');

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
        $csvOut = $export->streamedContent();
        $this->assertStringContainsString('baru_excel01@kamerakitaid.site', $csvOut);
        $this->assertStringContainsString('Yang Perlu dibayarkan', $csvOut);
        $this->assertStringNotContainsString('Approved share', $csvOut);
    }
}
