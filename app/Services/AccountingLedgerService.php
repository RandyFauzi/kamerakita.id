<?php

namespace App\Services;

use App\Models\FinanceEntry;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Models\TaxPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingLedgerService
{
    public function __construct(
        private readonly FinanceJournal $journal,
    ) {}

    public function postPaymentCycleToJournal(PaymentCycle $cycle, int $actorId): FinanceEntry
    {
        if ((int) $cycle->total_pay_idr <= 0) {
            throw ValidationException::withMessages([
                'cycle' => 'Total pembayaran siklus bernilai 0, tidak ada nominal untuk diposting.',
            ]);
        }

        return DB::transaction(function () use ($cycle, $actorId): FinanceEntry {
            $lockedCycle = PaymentCycle::whereKey($cycle->id)->lockForUpdate()->firstOrFail();

            $alreadyPosted = $lockedCycle->journal_posted_at !== null
                || FinanceEntry::where('source_type', 'payment_cycle')
                    ->where('source_id', $lockedCycle->id)
                    ->exists();

            if ($alreadyPosted) {
                throw ValidationException::withMessages([
                    'cycle' => "Siklus pembayaran {$lockedCycle->name} sudah pernah diposting ke jurnal.",
                ]);
            }

            $entryDate = ($lockedCycle->period_end ?? $lockedCycle->frozen_on ?? $lockedCycle->created_at ?? now('Asia/Makassar'))->toDateString();
            $month = substr($entryDate, 0, 7);
            $this->journal->openPeriod($month);

            $entry = FinanceEntry::create([
                'entry_date' => $entryDate,
                'type' => 'expense',
                'category' => 'Gaji & Payroll Mitra',
                'description' => "Payroll Siklus: {$lockedCycle->name}",
                'amount' => (int) $lockedCycle->total_pay_idr,
                'source_type' => 'payment_cycle',
                'source_id' => $lockedCycle->id,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $lockedCycle->update([
                'journal_posted_at' => now(),
            ]);

            return $entry;
        }, 3);
    }

    /**
     * Build monthly & annual P&L, category breakdowns, cash position, and operational liabilities.
     *
     * @return array<string, mixed>
     */
    public function buildReport(string $month, int $year): array
    {
        $monthlyTotals = $this->journal->totals($month);
        $monthlyIncomeByCategory = $this->journal->entries($month)
            ->where('type', 'income')
            ->selectRaw('category, SUM(amount) AS total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $monthlyExpenseByCategory = $this->journal->entries($month)
            ->where('type', 'expense')
            ->selectRaw('category, SUM(amount) AS total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $annualRows = [];
        $annualSummary = ['income' => 0, 'expense' => 0, 'profit' => 0, 'distributed' => 0];

        for ($m = 1; $m <= 12; $m++) {
            $ym = sprintf('%04d-%02d', $year, $m);
            $totals = $this->journal->totals($ym);
            $annualSummary['income'] += $totals['income'];
            $annualSummary['expense'] += $totals['expense'];
            $annualSummary['profit'] += $totals['profit'];
            $annualSummary['distributed'] += $totals['distributed'];

            $annualRows[] = [
                'month' => $ym,
                'label' => CarbonImmutable::createFromFormat('!Y-m', $ym)->translatedFormat('F Y'),
                ...$totals,
            ];
        }

        $unpaidPayrollIdr = (int) PaymentCycleEntry::where('is_paid', false)->sum('amount_to_pay_idr');
        $paidPayrollIdr = (int) PaymentCycleEntry::where('is_paid', true)->sum('amount_to_pay_idr');

        $unpaidTaxPeriods = TaxPeriod::whereIn('status', [TaxPeriod::STATUS_DRAFT, TaxPeriod::STATUS_CALCULATED])->get();
        $taxPayableIdr = (int) $unpaidTaxPeriods->sum(
            fn (TaxPeriod $p): int => $p->total_pph21_idr + $p->total_pph23_idr
        );

        $unpostedCycles = PaymentCycle::where('total_pay_idr', '>', 0)
            ->whereNull('journal_posted_at')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return [
            'month' => $month,
            'year' => $year,
            'cashBalanceIdr' => $this->journal->balance(),
            'monthlyTotals' => $monthlyTotals,
            'monthlyIncomeByCategory' => $monthlyIncomeByCategory,
            'monthlyExpenseByCategory' => $monthlyExpenseByCategory,
            'annualRows' => $annualRows,
            'annualSummary' => $annualSummary,
            'obligations' => [
                'unpaid_payroll_idr' => $unpaidPayrollIdr,
                'paid_payroll_idr' => $paidPayrollIdr,
                'tax_payable_idr' => $taxPayableIdr,
            ],
            'unpostedCycles' => $unpostedCycles,
        ];
    }
}
