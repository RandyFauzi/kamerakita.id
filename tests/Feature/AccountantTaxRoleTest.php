<?php

namespace Tests\Feature;

use App\Models\FinanceEntry;
use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Models\TaxPeriod;
use App\Models\TaxRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountantTaxRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_accountant_tax_user_from_internal_accounts_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin-users.create'))
            ->assertOk()
            ->assertSee('Accountant &amp; Tax Manager', false);

        $response = $this->actingAs($admin)->post(route('admin-users.store'), [
            'name' => 'Siti Tax Manager',
            'email' => 'siti.tax@kamerakita.id',
            'role' => User::ROLE_ACCOUNTANT_TAX,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('admin-users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Siti Tax Manager',
            'email' => 'siti.tax@kamerakita.id',
            'role' => User::ROLE_ACCOUNTANT_TAX,
        ]);

        $taxUser = User::where('email', 'siti.tax@kamerakita.id')->firstOrFail();
        $this->assertNotNull($taxUser->email_verified_at);
    }

    public function test_accountant_tax_role_access_boundaries(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT_TAX,
            'email_verified_at' => now(),
        ]);
        $worker = User::factory()->create([
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        // Allowed pages for Accountant & Tax Manager
        $this->actingAs($accountant)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ACCOUNTANT & TAX MANAGER WORKSPACE');
        $this->actingAs($accountant)->get(route('admin.finance.index'))->assertOk();
        $this->actingAs($accountant)->get(route('tax.index'))->assertOk();
        $this->actingAs($accountant)->get(route('accounting.reports.index'))->assertOk();
        $this->actingAs($accountant)->get(route('payments.manage'))->assertOk();
        $this->actingAs($accountant)->get(route('invoices.index'))->assertOk();
        $this->actingAs($accountant)->get(route('partners.index'))->assertOk();
        $this->actingAs($accountant)->get(route('payroll.export-csv'))->assertRedirect(route('dashboard'));

        // Restricted operational pages must return 403
        $this->actingAs($accountant)->get(route('admin-users.index'))->assertForbidden();
        $this->actingAs($accountant)->get(route('partners.create'))->assertForbidden();
        $this->actingAs($accountant)->get(route('video-submissions.qc-room'))->assertForbidden();
        $this->actingAs($accountant)->get(route('activation-codes.index'))->assertForbidden();

        // Worker cannot open invoices, tax center, or accounting reports
        $this->actingAs($worker)->get(route('invoices.index'))->assertForbidden();
        $this->actingAs($worker)->get(route('tax.index'))->assertForbidden();
        $this->actingAs($worker)->get(route('accounting.reports.index'))->assertForbidden();
    }

    public function test_tax_center_calculates_pph21_pph23_exempt_and_posts_to_journal_idempotently(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT_TAX,
            'email_verified_at' => now(),
        ]);

        $individualPartner = Partner::create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-101',
            'nik' => '5171010101010001',
            'tax_entity_type' => 'orang_pribadi',
            'ptkp_status' => 'TK/0',
            'full_name' => 'Budi Freelancer',
            'whatsapp_number' => '081234567890',
            'email' => 'budi@example.com',
            'country_code' => 'ID',
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $companyPartner = Partner::create([
            'partner_role' => Partner::ROLE_MITRA,
            'mitra_id' => 'KMK-102',
            'npwp' => '01.234.567.8-901.000',
            'tax_entity_type' => 'badan',
            'full_name' => 'PT Mitra Digital',
            'whatsapp_number' => '081234567891',
            'email' => 'ptmitra@example.com',
            'country_code' => 'ID',
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $foreignPartner = Partner::create([
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-103',
            'full_name' => 'John Overseas',
            'whatsapp_number' => '+15551234567',
            'email' => 'john@example.com',
            'country_code' => 'US',
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $cycle = PaymentCycle::create([
            'name' => 'Cycle Nov 2026',
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-15',
            'revenue_idr' => 25000000,
            'worker_rate_idr' => 60000,
            'total_pay_idr' => 15000000,
            'created_by' => $accountant->id,
        ]);

        PaymentCycleEntry::create([
            'payment_cycle_id' => $cycle->id,
            'partner_id' => $individualPartner->id,
            'worker_email' => $individualPartner->email,
            'billable_hours' => 100,
            'amount_to_pay_idr' => 6000000,
        ]);

        PaymentCycleEntry::create([
            'payment_cycle_id' => $cycle->id,
            'partner_id' => $companyPartner->id,
            'worker_email' => $companyPartner->email,
            'billable_hours' => 100,
            'amount_to_pay_idr' => 6000000,
        ]);

        PaymentCycleEntry::create([
            'payment_cycle_id' => $cycle->id,
            'partner_id' => $foreignPartner->id,
            'worker_email' => $foreignPartner->email,
            'billable_hours' => 50,
            'amount_to_pay_idr' => 3000000,
        ]);

        // 1. Sync Tax Period 2026-11
        $this->actingAs($accountant)
            ->post(route('tax.periods.sync'), ['month' => '2026-11'])
            ->assertRedirect(route('tax.index', ['month' => '2026-11']));

        $period = TaxPeriod::where('period_month', '2026-11')->firstOrFail();
        // Individual: 6,000,000 * 50% * 5% = 150,000 PPh 21
        // Company (ber-NPWP): 6,000,000 * 2% = 120,000 PPh 23
        // Foreign (US): 0 (EXEMPT)
        $this->assertSame(15000000, $period->total_gross_idr);
        $this->assertSame(150000, $period->total_pph21_idr);
        $this->assertSame(120000, $period->total_pph23_idr);

        $this->assertDatabaseHas('tax_records', [
            'tax_period_id' => $period->id,
            'recipient_email' => 'john@example.com',
            'status' => TaxRecord::STATUS_EXEMPT,
            'tax_amount_idr' => 0,
        ]);

        // 2. Manual Finance Journal income + Cycle expense posting -> P&L Report
        $this->actingAs($accountant)->post(route('admin.finance.store'), [
            'entry_date' => '2026-11-20',
            'type' => 'income',
            'category' => 'Pemasukan Klien Real',
            'description' => 'Transfer wire aktual November',
            'amount' => 24000000,
        ])->assertSessionHasNoErrors();

        $this->actingAs($accountant)
            ->post(route('tax.post-cycle', $cycle))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('finance_entries', [
            'source_type' => 'payment_cycle',
            'source_id' => $cycle->id,
            'type' => 'expense',
            'amount' => 15000000,
        ]);

        // Re-posting same cycle must be blocked cleanly
        $this->actingAs($accountant)
            ->post(route('tax.post-cycle', $cycle))
            ->assertSessionHasErrors('cycle');

        // Confirm P&L reflection: 24,000,000 - 15,000,000 = 9,000,000
        $this->assertSame(1, FinanceEntry::where('source_type', 'payment_cycle')->count());
        $this->actingAs($accountant)
            ->get(route('accounting.reports.index', ['month' => '2026-11', 'year' => 2026]))
            ->assertOk()
            ->assertSee('Rp9.000.000');

        // 3. Export Coretax CSV works
        $this->actingAs($accountant)
            ->get(route('tax.export-csv', ['month' => '2026-11']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // 4. Period PAID requires NTPN
        $this->actingAs($accountant)
            ->post(route('tax.periods.status', $period), ['status' => TaxPeriod::STATUS_PAID])
            ->assertSessionHas('error');

        $this->actingAs($accountant)
            ->post(route('tax.periods.status', $period), [
                'status' => TaxPeriod::STATUS_PAID,
                'ntpn_code' => 'NTPN1234567890',
                'payment_date' => '2026-12-10',
            ])
            ->assertSessionHas('success');

        $this->assertSame(TaxPeriod::STATUS_PAID, $period->fresh()->status);
    }
}
