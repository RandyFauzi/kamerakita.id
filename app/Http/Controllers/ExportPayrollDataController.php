<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

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
    public function exportHourlyTrackerExcel()
    {
        $reports = VideoWorkReport::with('partner')
            ->where('qc_status', 'approved')
            ->orderBy('submission_date', 'asc')
            ->get();

        if ($reports->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Tidak ada data laporan video (approved) yang dapat diekspor.');
        }

        $data = [];
        foreach ($reports as $report) {
            $partner = $report->partner;
            if (!$partner) continue;

            $totalMinutes = $report->approved_duration_minutes;
            // Fallback to submitted minutes if approved is 0 but it is approved
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

            $data[] = [
                'date_added' => $report->submission_date ? $report->submission_date->format('Y-m-d') : $report->created_at->format('Y-m-d'),
                'full_name' => $partner->full_name,
                'email' => $partner->email,
                'type' => $type,
                'hours' => (int)$hours,
                'minutes' => (int)$minutes,
                'seconds' => (int)$seconds,
            ];
        }

        // Write to temp file
        $tempJson = tempnam(sys_get_temp_dir(), 'kmk_excel_');
        file_put_contents($tempJson, json_encode($data));

        $tempOutputXlsx = tempnam(sys_get_temp_dir(), 'kmk_out_') . '.xlsx';

        // Run python script
        $scriptPath = base_path('app/Scripts/export_excel.py');
        $command = "python " . escapeshellarg($scriptPath) . " " . escapeshellarg($tempJson) . " " . escapeshellarg($tempOutputXlsx);
        
        exec($command, $output, $returnCode);

        // Delete temp JSON
        @unlink($tempJson);

        if ($returnCode !== 0 || !file_exists($tempOutputXlsx)) {
            \Illuminate\Support\Facades\Log::error("Excel export failed with exit code $returnCode. Output: " . implode("\n", $output));
            return redirect()->route('dashboard')->with('error', 'Gagal memproses ekspor Excel menggunakan Python. Pastikan python dan openpyxl terinstall.');
        }

        // Return download response and delete temp file after sending
        return response()->download($tempOutputXlsx, 'Hourly_Tracker_Indonesia_' . date('Y-m-d_H-i-s') . '.xlsx')->deleteFileAfterSend(true);
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
