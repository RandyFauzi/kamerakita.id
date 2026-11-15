<?php

namespace Tests\Feature;

use App\Models\{FinanceEntry, FinancePeriod, FinanceSetting, User};
use App\Services\FinanceJournal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceJournalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function entry(string $type, int $amount, string $date = '2026-10-12'): FinanceEntry
    {
        return FinanceEntry::create(['entry_date' => $date, 'type' => $type, 'amount' => $amount, 'category' => 'Operasional', 'description' => 'Test journal']);
    }

    public function test_only_admins_can_access_finance_and_write_transactions(): void
    {
        $this->get(route('admin.finance.index'))->assertRedirect('/login');
        foreach (['worker', 'mitra', 'rekruter'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('admin.finance.index'))->assertForbidden();
            $this->post(route('admin.finance.store'), [])->assertForbidden();
            $this->post(route('admin.finance.settings'), [])->assertForbidden();
            $this->post(route('admin.finance.distribute'), [])->assertForbidden();
        }
        $this->actingAs($this->admin())->get(route('admin.finance.index'))->assertOk()->assertSee('Jurnal transaksi')->assertSee('Pembagian profit');
    }

    public function test_journal_crud_filters_and_monthly_totals(): void
    {
        $this->actingAs($this->admin());
        $payload = ['entry_date' => '2026-10-12', 'type' => 'income', 'amount' => 1000000, 'category' => 'Proyek', 'description' => 'Client payment'];
        $this->post(route('admin.finance.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $entry = FinanceEntry::firstOrFail();
        $this->entry('expense', 200000);
        $this->entry('income', 80000, '2026-09-12');
        $this->get(route('admin.finance.index', ['month' => '2026-10', 'type' => 'income', 'search' => 'Client']))
            ->assertOk()->assertViewHas('entries', fn ($rows) => $rows->total() === 1)
            ->assertViewHas('totals', fn ($t) => $t['income'] === 1000000 && $t['expense'] === 200000 && $t['profit'] === 800000);
        $this->put(route('admin.finance.update', $entry), array_replace($payload, ['amount' => 1200000]))->assertSessionHasNoErrors();
        $this->assertSame(1000000, app(FinanceJournal::class)->totals('2026-10')['profit']);
        $this->delete(route('admin.finance.destroy', $entry))->assertSessionHasNoErrors();
        $this->assertSoftDeleted($entry);
        $this->assertSame(-200000, app(FinanceJournal::class)->totals('2026-10')['profit']);
        $this->post(route('admin.finance.store'), array_replace($payload, ['amount' => '1.5']))->assertSessionHasErrors('amount');
    }

    public function test_profit_distribution_is_exact_immutable_and_not_an_operating_expense(): void
    {
        $this->actingAs($this->admin());
        $income = $this->entry('income', 103);
        $this->entry('expense', 2);
        $settings = ['reserve' => 0, 'recipients' => [['name' => 'A', 'percent' => 33.33], ['name' => 'B', 'percent' => 33.33], ['name' => 'C', 'percent' => 33.34]]];
        $this->post(route('admin.finance.settings'), $settings)->assertSessionHasNoErrors();
        $this->post(route('admin.finance.distribute'), ['month' => '2026-10'])->assertSessionHasNoErrors();
        $journal = app(FinanceJournal::class);
        $this->assertSame(101, (int) FinanceEntry::where('type', 'distribution')->sum('amount'));
        $this->assertSame(101, $journal->totals('2026-10')['profit']);
        $this->assertSame(0, $journal->balance());
        $this->assertNotNull(FinancePeriod::where('month', '2026-10')->first()->closed_at);
        $this->get(route('admin.finance.index', ['month' => '2026-10']))->assertOk()->assertSee('Dibagikan');
        $snapshot = FinancePeriod::where('month', '2026-10')->first()->distribution;
        $this->post(route('admin.finance.distribute'), ['month' => '2026-10'])->assertSessionHasErrors('month');
        $this->assertSame(3, FinanceEntry::where('type', 'distribution')->count());
        $this->delete(route('admin.finance.destroy', $income))->assertSessionHasErrors('month');
        $this->post(route('admin.finance.store'), ['entry_date' => '2026-10-15', 'type' => 'income', 'amount' => 1, 'category' => 'X', 'description' => 'Late entry'])->assertSessionHasErrors('month');
        $this->post(route('admin.finance.settings'), array_replace($settings, ['reserve' => 50]))->assertSessionHasNoErrors();
        $this->assertEquals($snapshot, FinancePeriod::where('month', '2026-10')->first()->distribution);
    }

    public function test_invalid_shares_losses_and_insufficient_cash_do_not_create_distributions(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.finance.settings'), ['reserve' => 20, 'recipients' => [['name' => 'A', 'percent' => 99]]])->assertSessionHasErrors('recipients');
        $this->post(route('admin.finance.settings'), ['reserve' => 20, 'recipients' => [['name' => 'A', 'percent' => 100]]])->assertSessionHasNoErrors();
        $this->entry('expense', 100, '2026-09-12');
        $this->post(route('admin.finance.distribute'), ['month' => '2026-09'])->assertSessionHasErrors('month');
        $this->entry('income', 50);
        $this->post(route('admin.finance.distribute'), ['month' => '2026-10'])->assertSessionHasErrors('month');
        $this->assertSame(0, FinanceEntry::where('type', 'distribution')->count());
        $this->assertSame(0, FinancePeriod::whereNotNull('closed_at')->count());
    }

    public function test_capital_adds_cash_without_inflating_profit(): void
    {
        $this->actingAs($this->admin())->post(route('admin.finance.store'), ['entry_date' => '2026-10-12',
            'type' => 'capital', 'amount' => 5000000, 'category' => 'Modal', 'description' => 'Saldo awal'])
            ->assertSessionHasNoErrors();
        $this->assertSame(5000000, app(FinanceJournal::class)->balance());
        $this->assertSame(0, app(FinanceJournal::class)->totals('2026-10')['profit']);
        $this->get(route('admin.finance.index', ['month' => '2026-10']))->assertOk()->assertSee('Modal / saldo awal');
    }
}
