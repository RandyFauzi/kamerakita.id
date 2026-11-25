<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\TaxPeriod;
use App\Models\TaxRecord;
use App\Services\AccountingLedgerService;
use App\Services\ActivityLogger;
use App\Services\TaxCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxCenterController extends Controller
{
    public function __construct(
        private readonly TaxCalculationService $taxService,
        private readonly AccountingLedgerService $ledgerService,
    ) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'tax_type' => ['nullable', Rule::in(['PPH21', 'PPH23'])],
            'search' => ['nullable', 'string', 'max:100'],
            'missing_tax_id' => ['nullable', 'boolean'],
        ]);

        $month = $validated['month'] ?? now('Asia/Makassar')->format('Y-m');
        $start = CarbonImmutable::createFromFormat('!Y-m', $month);
        $end = $start->addMonth();

        $period = TaxPeriod::firstOrCreate(
            ['period_month' => $month],
            ['status' => TaxPeriod::STATUS_DRAFT]
        );

        $records = TaxRecord::query()
            ->with(['partner', 'paymentCycle'])
            ->where('tax_period_id', $period->id)
            ->when($validated['tax_type'] ?? null, fn ($q, $type) => $q->where('tax_type', $type))
            ->when($validated['search'] ?? null, function ($q, $search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_email', 'like', "%{$search}%")
                        ->orWhere('recipient_npwp', 'like', "%{$search}%")
                        ->orWhere('bupot_number', 'like', "%{$search}%");
                });
            })
            ->when($request->boolean('missing_tax_id'), function ($q): void {
                $q->where(function ($sub): void {
                    $sub->whereNull('recipient_npwp')->orWhere('recipient_npwp', '');
                })->where(function ($sub): void {
                    $sub->whereNull('recipient_nik')->orWhere('recipient_nik', '');
                });
            })
            ->orderByDesc('gross_amount_idr')
            ->paginate(25)
            ->withQueryString();

        $invoices = Invoice::query()
            ->where('invoice_date', '>=', $start->toDateString())
            ->where('invoice_date', '<', $end->toDateString())
            ->orderByDesc('invoice_date')
            ->get();

        $unpostedCycles = PaymentCycle::query()
            ->where('total_pay_idr', '>', 0)
            ->whereNull('journal_posted_at')
            ->orderByDesc('id')
            ->get();

        $allPeriods = TaxPeriod::orderByDesc('period_month')->limit(12)->get();

        return view('tax.index', [
            'month' => $month,
            'period' => $period,
            'records' => $records,
            'invoices' => $invoices,
            'unpostedCycles' => $unpostedCycles,
            'allPeriods' => $allPeriods,
            'filters' => $validated,
        ]);
    }

    public function syncPeriod(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $period = $this->taxService->syncPeriod($data['month'], $request->user()->id);

        ActivityLogger::log(
            'tax.sync_period',
            "Menghitung ulang pajak periode {$period->period_month} (PPh21: Rp" . number_format($period->total_pph21_idr, 0, ',', '.') . ", PPh23: Rp" . number_format($period->total_pph23_idr, 0, ',', '.') . ').'
        );

        return redirect()
            ->route('tax.index', ['month' => $data['month']])
            ->with('success', "Kalkulasi pajak periode {$data['month']} berhasil disinkronkan.");
    }

    public function updateRecord(Request $request, TaxRecord $taxRecord): RedirectResponse
    {
        $period = $taxRecord->period;
        if (in_array($period->status, [TaxPeriod::STATUS_PAID, TaxPeriod::STATUS_REPORTED], true)) {
            return back()->with('error', 'Periode pajak sudah PAID/REPORTED sehingga rincian tidak dapat diubah.');
        }

        $validated = $request->validate([
            'recipient_npwp' => ['nullable', 'string', 'max:32'],
            'tax_type' => ['required', Rule::in([TaxRecord::TYPE_PPH21, TaxRecord::TYPE_PPH23])],
            'tax_object_code' => ['required', 'string', 'max:32'],
            'tax_rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_amount_idr' => ['nullable', 'integer', 'min:0'],
            'bupot_number' => ['nullable', 'string', 'max:64'],
            'status' => ['required', Rule::in([
                TaxRecord::STATUS_CALCULATED,
                TaxRecord::STATUS_WITHHELD,
                TaxRecord::STATUS_REPORTED,
                TaxRecord::STATUS_EXEMPT,
            ])],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $dpp = $validated['tax_type'] === TaxRecord::TYPE_PPH21
            ? (int) round($taxRecord->gross_amount_idr * 0.5)
            : (int) $taxRecord->gross_amount_idr;

        if ($validated['status'] === TaxRecord::STATUS_EXEMPT) {
            $dpp = 0;
            $taxAmount = 0;
        } elseif (($validated['tax_amount_idr'] ?? null) !== null) {
            $taxAmount = (int) $validated['tax_amount_idr'];
        } else {
            $taxAmount = (int) round($dpp * ((float) $validated['tax_rate_percent'] / 100));
        }

        $taxRecord->update([
            'recipient_npwp' => $validated['recipient_npwp'] ?? null,
            'tax_type' => $validated['tax_type'],
            'tax_object_code' => $validated['tax_object_code'],
            'dpp_amount_idr' => $dpp,
            'tax_rate_percent' => (float) $validated['tax_rate_percent'],
            'tax_amount_idr' => $taxAmount,
            'net_amount_idr' => max(0, (int) $taxRecord->gross_amount_idr - $taxAmount),
            'bupot_number' => $validated['bupot_number'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->taxService->recalculatePeriodTotals($period);

        ActivityLogger::log('tax.update_record', "Memperbarui record pajak {$taxRecord->recipient_name} (periode {$period->period_month}).");

        return back()->with('success', "Data pajak {$taxRecord->recipient_name} berhasil diperbarui.");
    }

    public function uploadBupot(Request $request, TaxRecord $taxRecord): RedirectResponse
    {
        $validated = $request->validate([
            'bupot_number' => ['nullable', 'string', 'max:64'],
            'bupot_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($taxRecord->bupot_path && Storage::disk('local')->exists($taxRecord->bupot_path)) {
            Storage::disk('local')->delete($taxRecord->bupot_path);
        }

        $path = $request->file('bupot_file')->store("tax-bupots/{$taxRecord->period->period_month}", 'local');

        $taxRecord->update([
            'bupot_path' => $path,
            'bupot_number' => ($validated['bupot_number'] ?? null) ?: $taxRecord->bupot_number,
            'status' => $taxRecord->status === TaxRecord::STATUS_CALCULATED
                ? TaxRecord::STATUS_WITHHELD
                : $taxRecord->status,
        ]);

        ActivityLogger::log('tax.upload_bupot', "Mengunggah bukti potong pajak untuk {$taxRecord->recipient_name}.");

        return back()->with('success', "Bukti potong untuk {$taxRecord->recipient_name} berhasil diunggah.");
    }

    public function downloadBupot(TaxRecord $taxRecord)
    {
        abort_unless($taxRecord->bupot_path && Storage::disk('local')->exists($taxRecord->bupot_path), 404);

        return Storage::disk('local')->download($taxRecord->bupot_path);
    }

    public function updatePeriodStatus(Request $request, TaxPeriod $taxPeriod): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(TaxPeriod::ALL_STATUSES)],
            'ntpn_code' => ['nullable', 'string', 'max:64'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($validated['status'], [TaxPeriod::STATUS_PAID, TaxPeriod::STATUS_REPORTED], true) && empty($validated['ntpn_code'])) {
            return back()->withInput()->with('error', 'Kode NTPN / bukti setor wajib diisi untuk menandai periode PAID atau REPORTED.');
        }

        $taxPeriod->update([
            'status' => $validated['status'],
            'ntpn_code' => $validated['ntpn_code'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'closed_at' => $validated['status'] === TaxPeriod::STATUS_REPORTED ? now() : null,
            'updated_by' => $request->user()->id,
        ]);

        ActivityLogger::log('tax.period_status', "Mengubah status periode pajak {$taxPeriod->period_month} menjadi {$validated['status']}.");

        return back()->with('success', "Status periode pajak {$taxPeriod->period_month} diperbarui ke {$validated['status']}.");
    }

    public function updatePartnerTaxProfile(Request $request, Partner $partner): RedirectResponse
    {
        $validated = $request->validate([
            'npwp' => ['nullable', 'string', 'max:32'],
            'tax_entity_type' => ['required', Rule::in(['orang_pribadi', 'badan'])],
            'ptkp_status' => ['required', 'string', 'max:16'],
            'is_tax_exempt' => ['nullable', 'boolean'],
        ]);

        $partner->update([
            'npwp' => $validated['npwp'] ?? null,
            'tax_entity_type' => $validated['tax_entity_type'],
            'ptkp_status' => $validated['ptkp_status'],
            'is_tax_exempt' => $request->boolean('is_tax_exempt'),
        ]);

        ActivityLogger::log('partner.tax_profile', "Memperbarui profil pajak {$partner->full_name} ({$partner->mitra_id}).");

        return back()->with('success', "Profil pajak {$partner->full_name} berhasil diperbarui.");
    }

    public function updateInvoiceTax(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'is_domestic' => ['nullable', 'boolean'],
            'ppn_rate' => ['required', 'numeric', 'min:0', 'max:20'],
            'pph23_withheld' => ['required', 'numeric', 'min:0'],
            'idr_exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'faktur_pajak_no' => ['nullable', 'string', 'max:64'],
        ]);

        $ppnAmount = round((float) $invoice->total_amount * ((float) $validated['ppn_rate'] / 100), 2);

        $invoice->update([
            'is_domestic' => $request->boolean('is_domestic'),
            'ppn_rate' => (float) $validated['ppn_rate'],
            'ppn_amount' => $ppnAmount,
            'pph23_withheld' => (float) $validated['pph23_withheld'],
            'idr_exchange_rate' => ($validated['idr_exchange_rate'] ?? null) !== null ? (float) $validated['idr_exchange_rate'] : null,
            'faktur_pajak_no' => $validated['faktur_pajak_no'] ?? null,
        ]);

        $month = ($invoice->invoice_date ?? now('Asia/Makassar'))->format('Y-m');
        if ($period = TaxPeriod::where('period_month', $month)->first()) {
            $this->taxService->recalculatePeriodTotals($period);
        }

        ActivityLogger::log('invoice.tax_update', "Memperbarui atribut pajak Invoice {$invoice->invoice_no}.");

        return back()->with('success', "Data pajak Invoice {$invoice->invoice_no} berhasil disimpan.");
    }

    public function postInvoiceToJournal(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'idr_exchange_rate' => ['nullable', 'numeric', 'min:1'],
        ]);

        $entry = $this->ledgerService->postPaidInvoiceToJournal(
            $invoice,
            isset($validated['idr_exchange_rate']) ? (float) $validated['idr_exchange_rate'] : null,
            $request->user()->id
        );

        ActivityLogger::log('accounting.post_invoice', "Memposting Invoice {$invoice->invoice_no} ke Jurnal Keuangan sebesar Rp" . number_format($entry->amount, 0, ',', '.'));

        return back()->with('success', "Invoice {$invoice->invoice_no} berhasil diposting ke Jurnal Keuangan (Rp" . number_format($entry->amount, 0, ',', '.') . ').');
    }

    public function postCycleToJournal(Request $request, PaymentCycle $cycle): RedirectResponse
    {
        $entry = $this->ledgerService->postPaymentCycleToJournal($cycle, $request->user()->id);

        ActivityLogger::log('accounting.post_cycle', "Memposting Siklus Payroll {$cycle->name} ke Jurnal Keuangan sebesar Rp" . number_format($entry->amount, 0, ',', '.'));

        return back()->with('success', "Siklus {$cycle->name} berhasil diposting ke Jurnal Keuangan (Rp" . number_format($entry->amount, 0, ',', '.') . ').');
    }

    public function exportCoretaxCsv(Request $request): StreamedResponse
    {
        $month = $request->validate(['month' => ['required', 'date_format:Y-m']])['month'];
        $period = TaxPeriod::firstOrCreate(['period_month' => $month]);
        $csv = $this->taxService->buildCoretaxCsv($period);

        ActivityLogger::log('tax.export_csv', "Mengunduh CSV Coretax/e-Bupot periode {$month}.");

        return response()->streamDownload(function () use ($csv): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $csv['headers']);
            foreach ($csv['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "coretax-bupot-{$month}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
