<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ExportPayrollDataController extends Controller
{
    private const DEFAULT_HOURLY_RATE_IDR = 54000;

    /**
     * Export unpaid approved video work reports into bank-ready Bulk Transfer CSV format.
     */
    public function exportCsv()
    {
        $unpaidReports = VideoWorkReport::with('partner')
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid')
            ->get();

        if ($unpaidReports->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Tidak ada data payroll pending (unpaid & approved) yang dapat diekspor.');
        }

        // Group by partner_id
        $grouped = $unpaidReports->groupBy('partner_id');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bulk_payroll_transfer_' . date('Y-m-d_H-i-s') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($grouped) {
            $file = fopen('php://output', 'w');
            
            // CSV Header Row
            fputcsv($file, [
                'Nomor Rekening',
                'Nama Pemilik Rekening',
                'Nama Bank',
                'ID Mitra',
                'Mata Uang',
                'Rate per Jam Rupiah',
                'Total Menit Kerja',
                'Total Jam Kerja',
                'Total Nominal Rupiah'
            ]);

            foreach ($grouped as $partnerId => $reports) {
                $partner = $reports->first()->partner;
                
                $totalMinutes = $reports->sum('approved_duration_minutes');
                $hours = $totalMinutes / 60;
                $hourlyRate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
                $totalEarnings = round($hours * $hourlyRate);

                fputcsv($file, [
                    $partner->account_number ?? '0000000000',
                    $partner->account_owner_name ?? $partner->full_name,
                    $partner->bank_name ?? 'BCA',
                    $partner->mitra_id,
                    'IDR',
                    $hourlyRate,
                    $totalMinutes,
                    number_format($hours, 2, '.', ''),
                    $totalEarnings
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export all approved video work reports into custom Hourly Tracker Excel format.
     */
    public function exportHourlyTrackerExcel(Request $request)
    {
        $status = $request->query('status', 'approved');

        $query = VideoWorkReport::with('partner')->orderBy('submission_date', 'asc');
        
        if ($status !== 'all') {
            $query->where('qc_status', $status);
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            $statusName = $status === 'all' ? 'apa pun' : $status;
            return redirect()->route('dashboard')->with('error', "Tidak ada data laporan video dengan status ({$statusName}) yang dapat diekspor.");
        }

        $templatePath = public_path('Assets/Team Nanda Hourly tracker & Participant Information Indonesia.xlsx');

        if (!file_exists($templatePath)) {
            return redirect()->route('dashboard')->with('error', 'Berkas template Excel tidak ditemukan di folder public/Assets.');
        }

        if (! class_exists(IOFactory::class)) {
            Log::error('Hourly Tracker Excel Export Error: PhpSpreadsheet dependency is not installed.');

            return redirect()
                ->route('dashboard')
                ->with('error', 'Fitur ekspor Excel belum siap karena dependency spreadsheet belum terpasang. Jalankan composer install di server.');
        }

        try {
            // Load template using native PhpSpreadsheet
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Fill data starting from row 3
            $row = 3;
            foreach ($reports as $report) {
                $partner = $report->partner;
                if (!$partner) {
                    continue;
                }

                $totalMinutes = $report->approved_duration_minutes;
                if ($totalMinutes <= 0) {
                    $totalMinutes = $report->submitted_duration_minutes;
                }

                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $seconds = 0;

                $type = 'Residential';
                if ($partner->smartphone_type && (
                    str_contains(strtolower($partner->smartphone_type), 'comm') || 
                    str_contains(strtolower($partner->smartphone_type), 'bisnis') || 
                    str_contains(strtolower($partner->smartphone_type), 'kantor')
                )) {
                    $type = 'Commercial';
                }

                $dateStr = $report->submission_date ? $report->submission_date->format('Y-m-d') : $report->created_at->format('Y-m-d');

                $sheet->setCellValue('A' . $row, $dateStr);
                $sheet->setCellValue('B' . $row, $partner->full_name);
                $sheet->setCellValue('C' . $row, $partner->email);
                $sheet->setCellValue('D' . $row, $type);
                $sheet->setCellValue('E' . $row, (int)$hours);
                $sheet->setCellValue('F' . $row, (int)$minutes);
                $sheet->setCellValue('G' . $row, (int)$seconds);

                $row++;
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $filename = 'Hourly_Tracker_Indonesia_' . date('Y-m-d_H-i-s') . '.xlsx';

            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);

        } catch (Throwable $e) {
            Log::error('Native Excel Export Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('dashboard')->with('error', 'Gagal memproses ekspor Excel: ' . $e->getMessage());
        }
    }

    /**
     * Mark all approved unpaid reports as paid.
     */
    public function markAsPaid()
    {
        $updatedCount = VideoWorkReport::where('qc_status', 'approved')
            ->where('payment_status', 'unpaid')
            ->update(['payment_status' => 'paid']);

        if ($updatedCount > 0) {
            return redirect()->route('dashboard')->with('success', "Berhasil menandai {$updatedCount} laporan kerja video sebagai telah dibayar (Paid)!");
        }

        return redirect()->route('dashboard')->with('info', 'Tidak ada tagihan tertunda yang dapat ditandai.');
    }
}
