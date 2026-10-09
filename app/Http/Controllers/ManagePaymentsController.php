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

        // ONLY Fetch UNPAID Payrolls (already generated)
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
                'is_payroll' => true, // Already a payroll
                'atlas_worker_id' => $pr->atlas_worker_id,
                'task_count' => $pr->atlasTasks()->count(),
                'payroll' => $pr
            ];
        }

        $sort = $request->input('sort', 'date');
        if ($sort === 'name') {
            $workers = collect($workers)->sortBy(fn($w) => strtolower($w['partner']->full_name))->values()->all();
        } else {
            $workers = collect($workers)->sortByDesc(fn($w) => $w['latest_date'])->values()->all();
        }

        // 2. Fetch payout history from Payrolls where status = PAID
        $paidPayrollsQuery = Payroll::with('atlasWorker.user.partner')
            ->where('status', 'PAID')
            ->orderBy('paid_at', 'desc');

        // Apply search if needed...
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
            'payroll_id' => 'required|exists:payrolls,id',
        ]);

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
        
        return redirect()->back()->with('error', 'Tagihan tidak valid atau sudah dibayar.');
    }
        }

        $tasks = AtlasTask::where('atlas_worker_id', $validated['atlas_worker_id'])
            ->where('status', 'Approved')
            ->whereNull('payroll_id')
            ->get();

        if ($tasks->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada task yang perlu dibayar untuk mitra ini.');
        }

        try {
            DB::transaction(function () use ($partner, $tasks, $request, $imageService, $backupService, $validated) {
                // In case we want to store proof later, we can add a column.
                $uploadedPath = $imageService->store($request->file('payment_proof'), 'payment_proofs');
                $backupService->backup($uploadedPath);
                
                $totalMinutes = $tasks->sum('approved_minutes');
                $amount = ($totalMinutes / 60) * $validated['rate'];

                $payroll = Payroll::create([
                    'atlas_worker_id' => $validated['atlas_worker_id'],
                    'period_start' => clone $tasks->min('task_date'),
                    'period_end' => clone $tasks->max('task_date'),
                    'total_approved_minutes' => $totalMinutes,
                    'amount_rupiah' => $amount,
                    'status' => 'PAID',
                    'paid_at' => now(),
                    'payment_proof_path' => $uploadedPath // Add this column in a migration
                ]);

                foreach ($tasks as $task) {
                    $task->update(['payroll_id' => $payroll->id]);
                }
            });
            return redirect()->back()->with('success', "Pembayaran berhasil diproses dan invoice Payroll dibuat!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
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
        }
        
        return redirect()->back()->with('error', 'Tagihan tidak ditemukan.');
    }
}
