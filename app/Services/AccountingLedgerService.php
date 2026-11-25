<?php

namespace App\Services;

use App\Models\FinanceEntry;
use App\Models\Invoice;
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

    public function postPaidInvoiceToJournal(Invoice $invoice, ?float $exchangeRate, int $actorId): FinanceEntry
    {
        if ($invoice->status !== 'PAID') {
            throw ValidationException::withMessages([
                'invoice' => 'Hanya invoice berstatus PAID yang dapat diposting ke jurnal keuangan.',
            ]);
        }

        $isIdr = strtoupper((string) $invoice->currency) === 'IDR';
        $rate = $isIdr ? 1.0 : (float) ($exchangeRate ?: $invoice->idr_exchange_rate ?: 0);

        if (! $isIdr && $rate <= 0) {
            throw ValidationException::withMessages([
                'idr_exchange_rate' => 'Masukkan kurs konversi ke IDR yang valid sebelum memposting invoice valas.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $isIdr, $rate, $actorId): FinanceEntry {
            $lockedInvoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $alreadyPosted = $lockedInvoice->journal_posted_at !== null
                || FinanceEntry::where('source_type', 'invoice')
                    ->where('source_id', $lockedInvoice->id)
                    ->exists();

            if ($alreadyPosted) {
                throw ValidationException::withMessages([
                    'invoice' => "Invoice {$lockedInvoice->invoice_no} sudah pernah diposting ke jurnal.",
                ]);
            }

            $entryDate = ($lockedInvoice->invoice_date ?? now('Asia/Makassar'))->toDateString();
            $month = substr($entryDate, 0, 7);
            $this->journal->openPeriod($month);

            $netForeignAmount = (float) $lockedInvoice->total_amount
                + (float) $lockedInvoice->ppn_amount
                - (float) $lockedInvoice->pph23_withheld;
            $amountIdr = max(1, (int) round($netForeignAmount * $rate));

            $rateNote = $isIdr ? 'IDR' : "{$lockedInvoice->currency} @ Rp" . number_format($rate, 0, ',', '.');
            $entry = FinanceEntry::create([
                'entry_date' => $entryDate,
                'type' => 'income',
                'category' => 'Pembayaran Klien',
                'description' => "Invoice {$lockedInvoice->invoice_no} · {$lockedInvoice->client_name} ({$rateNote})",
                'amount' => $amountIdr,
                'source_type' => 'invoice',
                'source_id' => $lockedInvoice->id,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $lockedInvoice->update([
                'idr_exchange_rate' => $rate,
                'journal_posted_at' => now(),
            ]);

            return $entry;
        }, 3);
    }

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

        $receivableInvoices = Invoice::whereIn('status', ['ISSUED', 'SENT'])->get();
        $receivablesIdrEstimated = 0;
        $receivablesUsd = 0.0;
        foreach ($receivableInvoices as $inv) {
            if (strtoupper((string) $inv->currency) === 'IDR') {
                $receivablesIdrEstimated += (int) round((float) $inv->total_amount);
            } else {
                $receivablesUsd += (float) $inv->total_amount;
                if ((float) $inv->idr_exchange_rate > 0) {
                    $receivablesIdrEstimated += (int) round((float) $inv->total_amount * (float) $inv->idr_exchange_rate);
                }
            }
        }

        $unpaidTaxPeriods = TaxPeriod::whereIn('status', [TaxPeriod::STATUS_DRAFT, TaxPeriod::STATUS_CALCULATED])->get();
        $taxPayableIdr = (int) $unpaidTaxPeriods->sum(
            fn (TaxPeriod $p): int => $p->total_pph21_idr + $p->total_pph23_idr + $p->total_ppn_idr
        );

        $unpostedInvoices = Invoice::where('status', 'PAID')
            ->whereNull('journal_posted_at')
            ->orderByDesc('invoice_date')
            ->limit(15)
            ->get();

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
                'receivables_usd' => $receivablesUsd,
                'receivables_idr_estimated' => $receivablesIdrEstimated,
                'receivables_count' => $receivableInvoices->count(),
                'tax_payable_idr' => $taxPayableIdr,
            ],
            'unpostedInvoices' => $unpostedInvoices,
            'unpostedCycles' => $unpostedCycles,
        ];
    }
}
