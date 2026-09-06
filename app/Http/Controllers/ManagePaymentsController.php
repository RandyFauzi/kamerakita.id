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
        $search = $request->input('search');

        // 2. Fetch all unpaid approved reports unconditionally
        $unpaidReportsQuery = VideoWorkReport::with('partner')
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid');

        if ($search) {
            $unpaidReportsQuery->whereHas('partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $unpaidReports = $unpaidReportsQuery->get();

        $grouped = $unpaidReports->groupBy(function ($report) {
            $partnerRate = $report->partner ? ($report->partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR) : self::DEFAULT_HOURLY_RATE_IDR;
            $appliedRate = $report->rate_applied ?: $partnerRate;
            return $report->partner_id . '_' . $appliedRate;
        });

        $workers = [];
        foreach ($grouped as $key => $reports) {
            $partner = $reports->first()->partner;
            if (! $partner) {
                continue;
            }

            // Get period approval info if exists
            $periodApproval = null;

            $totalMinutes = $reports->sum('approved_duration_minutes');
            $hours = $totalMinutes / 60;
            $partnerRate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            $rate = $reports->first()->rate_applied ?: $partnerRate;
            
            $totalAmount = $hours * $rate;
            $totalAmount = round($totalAmount);
            $hasCustomRate = ($rate != $partnerRate);

            $workers[] = [
                'partner' => $partner,
                'reports' => $reports->sortByDesc('submission_date'),
                'total_minutes' => $totalMinutes,
                'hours' => $hours,
                'rate' => $rate,
                'total_amount' => $totalAmount,
                'has_custom_rate' => $hasCustomRate,
                'period_approval' => $periodApproval,
                'latest_date' => $reports->max('submission_date'),
            ];
        }

        $sort = $request->input('sort', 'date');
        if ($sort === 'name') {
            $workers = collect($workers)->sortBy(fn($w) => strtolower($w['partner']->full_name))->values()->all();
        } else {
            $workers = collect($workers)->sortByDesc(fn($w) => $w['latest_date'])->values()->all();
        }

        // 3. Fetch payout history (all payouts, order by date)
        $paidReportsQuery = VideoWorkReport::with('partner')
            ->where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->orderBy('paid_at', 'desc');

        if ($search) {
            $paidReportsQuery->whereHas('partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $paidReports = $paidReportsQuery->get();

        $groupedPaid = $paidReports->groupBy(function ($item) {
            $paidAt = $item->paid_at instanceof Carbon ? $item->paid_at : Carbon::parse($item->paid_at);
            $partnerRate = $item->partner ? ($item->partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR) : self::DEFAULT_HOURLY_RATE_IDR;
            $appliedRate = $item->rate_applied ?: $partnerRate;
            return $item->partner_id . '_' . $paidAt->format('Y-m-d H:i:s') . '_' . $item->payment_reference_proof_path . '_' . $appliedRate;
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
            $partnerRate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            $rate = $first->rate_applied ?: $partnerRate;
            
            $totalAmount = $hours * $rate;
            $totalAmount = round($totalAmount);
            $hasCustomRate = ($rate != $partnerRate);

            $payoutHistory[] = [
                'paid_at' => $first->paid_at,
                'proof_url' => $first->payment_proof_url,
                'proof_path' => $first->payment_reference_proof_path,
                'partner' => $partner,
                'reports' => $reports->sortByDesc('submission_date'),
                'total_minutes' => $totalMinutes,
                'total_amount' => $totalAmount,
                'has_custom_rate' => $hasCustomRate,
                'rate' => $rate,
                'batch_id' => base64_encode($partner->id . '|' . $first->paid_at->format('Y-m-d H:i:s') . '|' . $first->payment_reference_proof_path . '|' . $rate),
            ];
        }

        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        $currentItems = array_slice($payoutHistory, ($currentPage - 1) * $perPage, $perPage);
        $paginatedHistory = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, count($payoutHistory), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query()
        ]);

        $totalPayoutsCount = count($payoutHistory);
        $totalPaidAmount = collect($payoutHistory)->sum('total_amount');

        return view('payments.manage', [
            'workers' => $workers, 
            'payoutHistory' => $paginatedHistory, 
            'totalPayoutsCount' => $totalPayoutsCount,
            'totalPaidAmount' => $totalPaidAmount,
            'search' => $search, 
            'sort' => $sort
        ]);
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
            'rate' => 'required|numeric',
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

        $rate = $validated['rate'];
        $partnerRate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
        
        if ($rate == $partnerRate) {
            $reportsQuery->where(function($q) use ($partnerRate) {
                $q->whereNull('rate_applied')
                  ->orWhere('rate_applied', $partnerRate);
            });
        } else {
            $reportsQuery->where('rate_applied', $rate);
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
                    $periodsToCheck = [['start' => $startDate->format('Y-m-d'), 'end' => $endDate->format('Y-m-d')]];
                } else {
                    // Extract unique periods from the reports being paid
                    $periodsToCheck = collect();
                    foreach ($reports as $report) {
                        $range = \App\Services\PeriodService::getPeriodRange($report->submission_date);
                        $periodsToCheck->push([
                            'start' => $range['start']->format('Y-m-d'),
                            'end' => $range['end']->format('Y-m-d')
                        ]);
                    }
                    $periodsToCheck = $periodsToCheck->unique()->values()->all();
                }

                foreach ($periodsToCheck as $period) {
                    // Check if there are ANY unpaid reports left for this partner in this period
                    $remainingUnpaid = VideoWorkReport::where('partner_id', $partner->id)
                        ->where('qc_status', 'approved')
                        ->where('payment_status', 'unpaid')
                        ->whereBetween('submission_date', [$period['start'], $period['end']])
                        ->count();

                    if ($remainingUnpaid === 0) {
                        // Update PeriodApproval record status to paid only if everything is paid
                        PeriodApproval::updateOrCreate([
                            'partner_id' => $partner->id,
                            'period_start_date' => $period['start'],
                            'period_end_date' => $period['end'],
                        ], [
                            'status' => 'paid',
                        ]);
                    }
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
                    $periodsToCheck = [['start' => $startDate->format('Y-m-d'), 'end' => $endDate->format('Y-m-d')]];
                } else {
                    // Extract unique periods from the reports being paid
                    $periodsToCheck = collect();
                    foreach ($reports as $report) {
                        $range = \App\Services\PeriodService::getPeriodRange($report->submission_date);
                        $periodsToCheck->push([
                            'start' => $range['start']->format('Y-m-d'),
                            'end' => $range['end']->format('Y-m-d')
                        ]);
                    }
                    $periodsToCheck = $periodsToCheck->unique()->values()->all();
                }

                $partnerIds = $reports->pluck('partner_id')->unique();
                foreach ($partnerIds as $partnerId) {
                    foreach ($periodsToCheck as $period) {
                        // Check if there are ANY unpaid reports left for this partner in this period
                        $remainingUnpaid = VideoWorkReport::where('partner_id', $partnerId)
                            ->where('qc_status', 'approved')
                            ->where('payment_status', 'unpaid')
                            ->whereBetween('submission_date', [$period['start'], $period['end']])
                            ->count();
                            
                        if ($remainingUnpaid === 0) {
                            PeriodApproval::updateOrCreate([
                                'partner_id' => $partnerId,
                                'period_start_date' => $period['start'],
                                'period_end_date' => $period['end'],
                            ], [
                                'status' => 'paid',
                            ]);
                        }
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
            $rate = null;
            if (count($parts) < 3) {
                // Support legacy format for backward compatibility
                if (count($parts) === 2) {
                    $partnerId = null;
                    $paidAtStr = $parts[0];
                    $proofPath = $parts[1];
                } else {
                    return redirect()->back()->with('error', 'ID Batch pembayaran tidak valid.');
                }
            } else {
                $partnerId = $parts[0];
                $paidAtStr = $parts[1];
                $proofPath = $parts[2];
                if (count($parts) >= 4) {
                    $rate = $parts[3];
                }
            }

            $reportsQuery = VideoWorkReport::where('payment_status', 'paid')
                ->where('paid_at', $paidAtStr)
                ->where('payment_reference_proof_path', $proofPath);

            if ($partnerId) {
                $reportsQuery->where('partner_id', $partnerId);
                
                if ($rate !== null) {
                    $partner = Partner::find($partnerId);
                    $partnerRate = $partner ? ($partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR) : self::DEFAULT_HOURLY_RATE_IDR;
                    
                    if ($rate == $partnerRate) {
                        $reportsQuery->where(function($q) use ($partnerRate) {
                            $q->whereNull('rate_applied')
                              ->orWhere('rate_applied', $partnerRate);
                        });
                    } else {
                        $reportsQuery->where('rate_applied', $rate);
                    }
                }
            }

            $reports = $reportsQuery->get();

            if ($reports->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ditemukan batch pembayaran yang sesuai.');
            }

            DB::transaction(function () use ($reports, $proofPath, $partnerId, $paidAtStr) {
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

                // Check if there are any remaining reports in this specific batch (e.g. if we only cancelled one rate group)
                $remainingInBatch = VideoWorkReport::where('payment_status', 'paid')
                    ->where('paid_at', $paidAtStr)
                    ->where('payment_reference_proof_path', $proofPath);
                
                if ($partnerId) {
                    $remainingInBatch->where('partner_id', $partnerId);
                }
                
                // Delete local storage backup file of payment proof ONLY if no reports are using it anymore
                if ($remainingInBatch->count() === 0 && Storage::disk('public')->exists($proofPath) && $proofPath !== 'payment_proofs/dummy_batch.jpg') {
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
