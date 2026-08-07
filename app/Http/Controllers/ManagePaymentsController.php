<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Models\PeriodApproval;
use App\Services\PeriodService;
use App\Services\EvidenceFileBackupService;
use App\Services\StoreEvidenceImageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ManagePaymentsController extends Controller
{
    private const DEFAULT_HOURLY_RATE_IDR = 50000;

    /**
     * Display list of workers with approved, unpaid work reports grouped by period, and payout history.
     */
    public function index(Request $request)
    {
        // 1. Get available periods
        $periods = PeriodService::getAvailablePeriods();
        
        $selectedPeriodKey = $request->input('period');
        if (!$selectedPeriodKey && !empty($periods)) {
            $selectedPeriodKey = $periods[0]['start']->format('Y-m-d') . '|' . $periods[0]['end']->format('Y-m-d');
        }

        if ($selectedPeriodKey === 'all') {
            $startDate = null;
            $endDate = null;
        } elseif ($selectedPeriodKey) {
            $parts = explode('|', $selectedPeriodKey);
            $startDate = Carbon::parse($parts[0])->startOfDay();
            $endDate = Carbon::parse($parts[1])->endOfDay();
        } else {
            $range = PeriodService::getPeriodRange(now());
            $startDate = $range['start'];
            $endDate = $range['end'];
        }

        // 2. Fetch unpaid approved reports for the selected period
        $unpaidReportsQuery = VideoWorkReport::with('partner')
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid');
            
        if ($startDate && $endDate) {
            $unpaidReportsQuery->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }
        
        $unpaidReports = $unpaidReportsQuery->get();

        $grouped = $unpaidReports->groupBy('partner_id');

        $workers = [];
        foreach ($grouped as $partnerId => $reports) {
            $partner = $reports->first()->partner;
            if (! $partner) {
                continue;
            }

            // Get period approval info if exists
            $periodApproval = null;
            if ($startDate && $endDate) {
                $periodApproval = PeriodApproval::where('partner_id', $partnerId)
                    ->where('period_start_date', $startDate->format('Y-m-d'))
                    ->where('period_end_date', $endDate->format('Y-m-d'))
                    ->first();
            }

            $totalMinutes = $reports->sum('approved_duration_minutes');
            $hours = $totalMinutes / 60;
            $rate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            $totalAmount = round($hours * $rate);

            $workers[] = [
                'partner' => $partner,
                'reports' => $reports->sortByDesc('submission_date'),
                'total_minutes' => $totalMinutes,
                'hours' => $hours,
                'rate' => $rate,
                'total_amount' => $totalAmount,
                'period_approval' => $periodApproval,
            ];
        }

        // 3. Fetch payout history (all payouts, order by date)
        $paidReports = VideoWorkReport::with('partner')
            ->where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->orderBy('paid_at', 'desc')
            ->get();

        $groupedPaid = $paidReports->groupBy(function ($item) {
            $paidAt = $item->paid_at instanceof Carbon ? $item->paid_at : Carbon::parse($item->paid_at);
            return $paidAt->format('Y-m-d H:i:s') . '_' . $item->payment_reference_proof_path;
        });

        $payoutHistory = [];
        foreach ($groupedPaid as $key => $reports) {
            $first = $reports->first();
            $partner = $first->partner;
            if (! $partner) {
                continue;
            }

            $totalMinutes = $reports->sum('approved_duration_minutes');
            $hours = $totalMinutes / 60;
            $rate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            $totalAmount = round($hours * $rate);

            $payoutHistory[] = [
                'paid_at' => $first->paid_at,
                'proof_url' => $first->payment_proof_url,
                'proof_path' => $first->payment_reference_proof_path,
                'partner' => $partner,
                'reports' => $reports->sortByDesc('submission_date'),
                'total_minutes' => $totalMinutes,
                'total_amount' => $totalAmount,
                'batch_id' => base64_encode($first->paid_at->format('Y-m-d H:i:s') . '|' . $first->payment_reference_proof_path),
            ];
        }

        return view('payments.manage', compact('workers', 'payoutHistory', 'periods', 'selectedPeriodKey', 'startDate', 'endDate'));
    }

    /**
     * Process payout for a specific worker: save transfer proof and mark reports as paid.
     */
    public function processPayment(Request $request, Partner $partner, StoreEvidenceImageService $imageService, EvidenceFileBackupService $backupService)
    {
        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'period_start_date' => 'required|string',
            'period_end_date' => 'required|string',
        ]);

        $reportsQuery = VideoWorkReport::where('partner_id', $partner->id)
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid');
            
        $startDate = null;
        $endDate = null;
        if ($validated['period_start_date'] !== 'all' && $validated['period_end_date'] !== 'all') {
            $startDate = Carbon::parse($validated['period_start_date']);
            $endDate = Carbon::parse($validated['period_end_date']);
            $reportsQuery->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }
        
        $reports = $reportsQuery->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada laporan yang perlu dibayar untuk mitra ini pada periode tersebut.');
        }

        try {
            DB::transaction(function () use ($partner, $reports, $request, $imageService, $backupService, $startDate, $endDate, $validated) {
                // Upload payment proof
                $uploadedPath = $imageService->store($request->file('payment_proof'), 'payment_proofs');
                
                // Backup
                $backupService->backup($uploadedPath);

                $now = now();
                foreach ($reports as $report) {
                    $report->update([
                        'payment_status' => 'paid',
                        'paid_at' => $now,
                        'payment_reference_proof_path' => $uploadedPath,
                    ]);
                }

                if ($startDate && $endDate) {
                    // Update PeriodApproval record status to paid
                    PeriodApproval::updateOrCreate([
                        'partner_id' => $partner->id,
                        'period_start_date' => $startDate->format('Y-m-d'),
                        'period_end_date' => $endDate->format('Y-m-d'),
                    ], [
                        'status' => 'paid',
                    ]);
                }
            });

            $logPeriod = $startDate && $endDate ? "periode {$startDate->format('Y-m-d')} s/d {$endDate->format('Y-m-d')}" : "semua periode";
            \App\Services\ActivityLogger::log('payment.process', "Memproses pembayaran gaji untuk mitra {$partner->full_name} untuk {$logPeriod}");

            return redirect()->back()->with('success', 'Pembayaran gaji mitra berhasil diproses dan disimpan.');
        } catch (Throwable $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Process batch payout for all unpaid approved reports.
     * Accessible only by superadmin.
     */
    public function batchPay(Request $request)
    {
        $validated = $request->validate([
            'period_start_date' => 'required|string',
            'period_end_date' => 'required|string',
        ]);

        $reportsQuery = VideoWorkReport::where('qc_status', 'approved')
            ->where('payment_status', 'unpaid');
            
        $startDate = null;
        $endDate = null;
        if ($validated['period_start_date'] !== 'all' && $validated['period_end_date'] !== 'all') {
            $startDate = Carbon::parse($validated['period_start_date']);
            $endDate = Carbon::parse($validated['period_end_date']);
            $reportsQuery->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }
        
        $reports = $reportsQuery->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada laporan yang perlu dibayar pada periode tersebut.');
        }

        try {
            DB::transaction(function () use ($reports, $startDate, $endDate) {
                $now = now();
                $dummyProofPath = 'payment_proofs/dummy_batch.jpg';
                
                foreach ($reports as $report) {
                    $report->update([
                        'payment_status' => 'paid',
                        'paid_at' => $now,
                        'payment_reference_proof_path' => $dummyProofPath,
                    ]);
                }

                if ($startDate && $endDate) {
                    $partnerIds = $reports->pluck('partner_id')->unique();
                    foreach ($partnerIds as $partnerId) {
                        PeriodApproval::updateOrCreate([
                            'partner_id' => $partnerId,
                            'period_start_date' => $startDate->format('Y-m-d'),
                            'period_end_date' => $endDate->format('Y-m-d'),
                        ], [
                            'status' => 'paid',
                        ]);
                    }
                }
            });

            $logPeriod = $startDate && $endDate ? "periode {$startDate->format('Y-m-d')} s/d {$endDate->format('Y-m-d')}" : "semua periode";
            \App\Services\ActivityLogger::log('payment.batch_process', "Memproses pembayaran batch (semua mitra) untuk {$logPeriod} dengan bukti dummy.");

            return redirect()->back()->with('success', 'Pembayaran batch berhasil diproses dengan bukti transfer dummy.');
        } catch (Throwable $e) {
            Log::error('Error processing batch payment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pembayaran batch: ' . $e->getMessage());
        }
    }

    /**
     * Cancel/Revert a payment batch.
     */
    public function cancelPayment(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|string',
        ]);

        try {
            $decoded = base64_decode($validated['batch_id']);
            $parts = explode('|', $decoded);
            if (count($parts) < 2) {
                return redirect()->back()->with('error', 'ID Batch pembayaran tidak valid.');
            }

            $paidAtStr = $parts[0];
            $proofPath = $parts[1];

            $reports = VideoWorkReport::where('payment_status', 'paid')
                ->where('paid_at', $paidAtStr)
                ->where('payment_reference_proof_path', $proofPath)
                ->get();

            if ($reports->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ditemukan batch pembayaran yang sesuai.');
            }

            DB::transaction(function () use ($reports, $proofPath) {
                foreach ($reports as $report) {
                    $startDate = PeriodService::getPeriodRange($report->submission_date)['start'];
                    $endDate = PeriodService::getPeriodRange($report->submission_date)['end'];

                    $report->update([
                        'payment_status' => 'unpaid',
                        'paid_at' => null,
                        'payment_reference_proof_path' => null,
                    ]);

                    // Revert PeriodApproval status back to approved
                    PeriodApproval::where('partner_id', $report->partner_id)
                        ->where('period_start_date', $startDate->format('Y-m-d'))
                        ->where('period_end_date', $endDate->format('Y-m-d'))
                        ->update(['status' => 'approved']);
                }

                // Delete local storage backup file of payment proof
                if (Storage::disk('public')->exists($proofPath)) {
                    Storage::disk('public')->delete($proofPath);
                }
            });

            \App\Services\ActivityLogger::log('payment.cancel', "Pembayaran untuk batch {$validated['batch_id']} (bukti: {$proofPath}) berhasil dibatalkan.");

            return redirect()->back()->with('success', 'Pembayaran berhasil dibatalkan dan status dikembalikan ke unpaid.');
        } catch (Throwable $e) {
            Log::error('Error cancelling payment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membatalkan pembayaran: ' . $e->getMessage());
        }
    }
}
