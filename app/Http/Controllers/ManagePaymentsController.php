<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Payroll;
use App\Services\StoreEvidenceImageService;
use App\Services\EvidenceFileBackupService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagePaymentsController extends Controller
{
    const DEFAULT_HOURLY_RATE_IDR = 54000;

    /**
     * Display list of workers with approved, unpaid work reports grouped by period, and payout history.
     */
        public function index(Request $request)
    {
        $search = $request->input('search');

        $workers = [];

        // 1A. Fetch UNPAID Payrolls (AtlasTask)
        $unpaidPayrollsQuery = Payroll::with('atlasWorker.user.partner')->where('status', 'UNPAID');
        if ($search) {
            $unpaidPayrollsQuery->whereHas('atlasWorker.user.partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%");
            });
        }
        $unpaidPayrolls = $unpaidPayrollsQuery->get();

        foreach ($unpaidPayrolls as $pr) {
            $partner = $pr->atlasWorker->user->partner ?? null;
            if (!$partner) continue;
            
            $rate = 0;
            if ($pr->total_approved_minutes > 0) {
                $rate = $pr->amount_rupiah / ($pr->total_approved_minutes / 60);
            }
            
            $workers[] = [
                'partner' => $partner,
                'reports' => collect([]),
                'total_minutes' => $pr->total_approved_minutes,
                'hours' => $pr->total_approved_minutes / 60,
                'rate' => $rate,
                'total_amount' => $pr->amount_rupiah,
                'has_custom_rate' => false,
                'period_approval' => null,
                'latest_date' => $pr->period_end,
                'is_payroll' => true,
                'atlas_worker_id' => $pr->atlas_worker_id,
                'task_count' => $pr->atlasTasks()->count(),
                'payroll' => $pr
            ];
        }

        // 1B. Fetch UNPAID VideoWorkReports (Legacy)
        $unpaidReportsQuery = VideoWorkReport::with('partner')
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid');
            
        if ($search) {
            $unpaidReportsQuery->whereHas('partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%");
            });
        }
        $unpaidReports = $unpaidReportsQuery->get();
        $groupedUnpaid = $unpaidReports->groupBy('partner_id');

        foreach ($groupedUnpaid as $partnerId => $reports) {
            $partner = $reports->first()->partner;
            if (!$partner) continue;
            
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
                'has_custom_rate' => false,
                'period_approval' => null,
                'latest_date' => $reports->max('submission_date'),
                'is_payroll' => false,
                'task_count' => $reports->count(),
            ];
        }

        $sort = $request->input('sort', 'date');
        if ($sort === 'name') {
            $workers = collect($workers)->sortBy(fn($w) => strtolower($w['partner']->full_name))->values()->all();
        } else {
            $workers = collect($workers)->sortByDesc(fn($w) => $w['latest_date'])->values()->all();
        }

        // 2A. Fetch payout history from Payrolls (AtlasTask) where status = PAID
        $paidPayrollsQuery = Payroll::with('atlasWorker.user.partner')
            ->where('status', 'PAID')
            ->orderBy('paid_at', 'desc');

        if ($search) {
            $paidPayrollsQuery->whereHas('atlasWorker.user.partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%");
            });
        }

        $paidPayrolls = $paidPayrollsQuery->get();

        $payoutHistory = [];
        foreach ($paidPayrolls as $pr) {
            $partner = $pr->atlasWorker->user->partner ?? null;
            if (!$partner) continue;
            
            $rate = 0;
            if ($pr->total_approved_minutes > 0) {
                $rate = $pr->amount_rupiah / ($pr->total_approved_minutes / 60);
            }
            
            $payoutHistory[] = [
                'paid_at' => $pr->paid_at ?? clone $pr->updated_at,
                'proof_url' => $pr->payment_proof_url ?? null,
                'proof_path' => $pr->payment_proof_path ?? null,
                'partner' => $partner,
                'reports' => collect([]),
                'task_count' => $pr->atlasTasks()->count(),
                'total_minutes' => $pr->total_approved_minutes,
                'total_amount' => $pr->amount_rupiah,
                'has_custom_rate' => false,
                'rate' => $rate,
                'batch_id' => 'payroll_' . $pr->id,
                'is_payroll' => true,
                'payroll' => $pr,
            ];
        }

        // 2B. Fetch payout history from VideoWorkReports (Legacy) where status = PAID
        $paidReportsQuery = VideoWorkReport::with('partner')
            ->where('payment_status', 'paid')
            ->orderBy('paid_at', 'desc');

        if ($search) {
            $paidReportsQuery->whereHas('partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%");
            });
        }
        
        $paidReports = $paidReportsQuery->get();
        $groupedPaid = $paidReports->groupBy(function($item) {
            return ($item->paid_at ? $item->paid_at->format('Y-m-d H:i:s') : '') . '|' . $item->payment_reference_proof_path;
        });
        
        foreach ($groupedPaid as $key => $reports) {
            $first = $reports->first();
            $partner = $first->partner;
            if (!$partner) continue;
            
            $totalMinutes = $reports->sum('approved_duration_minutes');
            $hours = $totalMinutes / 60;
            $rate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            $totalAmount = round($hours * $rate);
            
            $payoutHistory[] = [
                'paid_at' => $first->paid_at ?? clone $first->updated_at,
                'proof_url' => $first->payment_proof_url,
                'proof_path' => $first->payment_reference_proof_path,
                'partner' => $partner,
                'reports' => $reports->sortByDesc('submission_date'),
                'task_count' => $reports->count(),
                'total_minutes' => $totalMinutes,
                'total_amount' => $totalAmount,
                'has_custom_rate' => false,
                'rate' => $rate,
                'batch_id' => base64_encode(($first->paid_at ? $first->paid_at->format('Y-m-d H:i:s') : '') . '|' . $first->payment_reference_proof_path),
                'is_payroll' => false,
            ];
        }

        // Sort merged payout history by paid_at descending
        $payoutHistory = collect($payoutHistory)->sortByDesc('paid_at')->values()->all();

        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        $currentItems = array_slice($payoutHistory, ($currentPage - 1) * $perPage, $perPage);
        $paginatedHistory = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, count($payoutHistory), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query()
        ]);

        $totalPayoutsCount = count($payoutHistory);
        $totalPaidAmount = collect($payoutHistory)->sum('total_amount');
        
        $queuedAmount = collect($workers)->sum('total_amount');
        $queuedReportCount = collect($workers)->sum('task_count'); // Sum of tasks instead of reports

        return view('payments.manage', [
            'workers' => $workers, 
            'payoutHistory' => $paginatedHistory, 
            'totalPayoutsCount' => $totalPayoutsCount,
            'totalPaidAmount' => $totalPaidAmount,
            'queuedAmount' => $queuedAmount,
            'queuedReportCount' => $queuedReportCount,
            'search' => $search, 
            'sort' => $sort
        ]);
    }

    /**
     * Process payout for a specific worker: create Payroll, attach Tasks, save transfer proof and mark as paid.
     */
        public function processPayment(Request $request, Partner $partner, StoreEvidenceImageService $imageService, EvidenceFileBackupService $backupService)
    {
        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'payroll_id' => 'nullable|exists:payrolls,id',
        ]);

        if ($request->filled('payroll_id')) {
            $payroll = Payroll::find($request->payroll_id);
            if ($payroll && $payroll->status === 'UNPAID') {
                try {
                    DB::transaction(function () use ($payroll, $request, $imageService, $backupService) {
                        $uploadedPath = $imageService->store($request->file('payment_proof'), 'payment_proofs');
                        $backupService->backup($uploadedPath);
                        $payroll->update([
                            'status' => 'PAID',
                            'paid_at' => now(),
                            'payment_proof_path' => $uploadedPath
                        ]);
                    });
                    return redirect()->back()->with('success', "Tagihan Payroll berhasil dibayar!");
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
                }
            }
        } else {
            // Legacy VideoWorkReport logic
            $reports = VideoWorkReport::where('partner_id', $partner->id)
                ->where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->get();

            if ($reports->isNotEmpty()) {
                try {
                    DB::transaction(function () use ($reports, $request, $imageService, $backupService) {
                        $uploadedPath = $imageService->store($request->file('payment_proof'), 'evidences/payments');
                        $backupService->backup($uploadedPath);
                        VideoWorkReport::whereIn('id', $reports->pluck('id'))->update([
                            'payment_status' => 'paid',
                            'payment_reference_proof_path' => $uploadedPath,
                            'paid_at' => now(),
                        ]);
                    });
                    return redirect()->back()->with('success', "Pembayaran untuk Mitra {$partner->full_name} berhasil diproses!");
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
                }
            }
        }
        
        return redirect()->back()->with('error', 'Tagihan tidak valid atau sudah dibayar.');
    }

    /**
     * Batch process all queued payments.
     */
    public function batchPay(Request $request, StoreEvidenceImageService $imageService)
    {
        // ... (Keep existing or update later, for now just disable or redirect back)
        return redirect()->back()->with('error', 'Fitur Batch Pay sedang dinonaktifkan sementara.');
    }

    /**
     * Cancel a payout batch and revert reports to unpaid.
     */
    public function cancelPayment(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|string',
        ]);

        if (str_starts_with($validated['batch_id'], 'payroll_')) {
            $payrollId = str_replace('payroll_', '', $validated['batch_id']);
            $payroll = Payroll::find($payrollId);
            if ($payroll) {
                try {
                    DB::transaction(function() use ($payroll) {
                        AtlasTask::where('payroll_id', $payroll->id)->update(['payroll_id' => null]);
                        $payroll->delete();
                    });
                    return redirect()->back()->with('success', 'Tagihan Payroll berhasil dibatalkan dan Task dikembalikan ke antrean.');
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Gagal membatalkan tagihan: ' . $e->getMessage());
                }
            }
        } else {
            // Legacy VideoWorkReport logic
            try {
                $decoded = base64_decode($validated['batch_id']);
                if (str_contains($decoded, '|')) {
                    list($paidAtStr, $proofPath) = explode('|', $decoded);
                    
                    $reportsQuery = VideoWorkReport::where('payment_reference_proof_path', $proofPath);
                    if ($paidAtStr) {
                        $reportsQuery->where('paid_at', $paidAtStr);
                    }
                    $reports = $reportsQuery->get();

                    if ($reports->isNotEmpty()) {
                        DB::transaction(function() use ($reports) {
                            VideoWorkReport::whereIn('id', $reports->pluck('id'))->update([
                                'payment_status' => 'unpaid',
                                'payment_reference_proof_path' => null,
                                'paid_at' => null,
                            ]);
                        });
                        return redirect()->back()->with('success', 'Riwayat pembayaran lama berhasil dihapus dan dikembalikan ke antrean.');
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal membatalkan tagihan lama: ' . $e->getMessage());
            }
        }
        
        return redirect()->back()->with('error', 'Tagihan tidak ditemukan.');
    }
}
