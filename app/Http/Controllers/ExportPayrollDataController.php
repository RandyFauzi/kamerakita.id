<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class ExportPayrollDataController extends Controller
{
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
                'Total Nominal Rupiah',
                'ID Mitra',
                'Total Menit Kerja'
            ]);

            foreach ($grouped as $partnerId => $reports) {
                $partner = $reports->first()->partner;
                
                $totalMinutes = $reports->sum('approved_duration_minutes');
                $hours = $totalMinutes / 60;
                $hourlyRate = $partner->base_hourly_rate ?? 54000;
                $totalEarnings = round($hours * $hourlyRate);

                fputcsv($file, [
                    $partner->account_number ?? '0000000000',
                    $partner->account_owner_name ?? $partner->full_name,
                    $partner->bank_name ?? 'BCA',
                    $totalEarnings,
                    $partner->mitra_id,
                    $totalMinutes
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
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
