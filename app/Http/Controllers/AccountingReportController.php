<?php

namespace App\Http\Controllers;

use App\Services\AccountingLedgerService;
use App\Services\ActivityLogger;
use App\Services\FinanceJournal;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingReportController extends Controller
{
    public function __construct(
        private readonly AccountingLedgerService $ledgerService,
        private readonly FinanceJournal $journal,
    ) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ]);

        $month = $validated['month'] ?? now('Asia/Makassar')->format('Y-m');
        $year = (int) ($validated['year'] ?? substr($month, 0, 4));
        $report = $this->ledgerService->buildReport($month, $year);

        return view('accounting.reports', compact('report'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = $validated['month'];
        $entries = $this->journal->entries($month)->orderBy('entry_date')->orderBy('id')->get();
        $totals = $this->journal->totals($month);

        ActivityLogger::log('accounting.export_report_csv', "Mengunduh laporan buku besar & laba rugi bulan {$month}.");

        return response()->streamDownload(function () use ($month, $entries, $totals): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['LAPORAN LABA RUGI & BUKU BESAR', $month]);
            fputcsv($handle, ['Total Pemasukan (IDR)', $totals['income']]);
            fputcsv($handle, ['Total Biaya Operasional (IDR)', $totals['expense']]);
            fputcsv($handle, ['Laba Bersih (IDR)', $totals['profit']]);
            fputcsv($handle, ['Bagi Profit (IDR)', $totals['distributed']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Tanggal', 'Jenis', 'Kategori', 'Deskripsi', 'Nominal (IDR)', 'Sumber']);

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->entry_date->toDateString(),
                    $entry->type,
                    $entry->category,
                    $entry->description,
                    $entry->amount,
                    $entry->source_type ? "{$entry->source_type}#{$entry->source_id}" : 'manual',
                ]);
            }

            fclose($handle);
        }, "laporan-akuntansi-{$month}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
