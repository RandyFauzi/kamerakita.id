<?php

namespace App\Http\Controllers;

use App\Models\AtlasWorker;
use App\Models\Payroll;
use App\Models\AtlasTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index()
    {
        // View generated payrolls
        $payrolls = Payroll::with('atlasWorker')->orderByDesc('created_at')->paginate(20);

        // Preview Unpaid / Siap Generate (tasks with approved_minutes > 0 and payroll_id = null)
        $unpaidWorkers = AtlasTask::whereNull('payroll_id')
            ->where('approved_minutes', '>', 0)
            ->with('atlasWorker')
            ->select('atlas_worker_id', DB::raw('SUM(approved_minutes) as total_approved_minutes'), DB::raw('MIN(task_date) as earliest_date'), DB::raw('MAX(task_date) as latest_date'), DB::raw('COUNT(id) as total_tasks'))
            ->groupBy('atlas_worker_id')
            ->get();

        return view('admin.payrolls.index', compact('payrolls', 'unpaidWorkers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'atlas_worker_id' => 'required|exists:atlas_workers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'rate_per_hour' => 'required|numeric|min:0',
        ]);

        $workerId = $request->atlas_worker_id;
        $periodStart = $request->period_start;
        $periodEnd = $request->period_end;
        $ratePerHour = $request->rate_per_hour;

        // Find all unpaid tasks for this worker within the selected period.
        // Even though "carry forward" ignores task_date, we let admin specify the boundary to avoid locking tasks that are too new if they don't want to.
        // Wait, best practice for "rollover": lock ALL unpaid tasks up to period_end.
        $tasksToLock = AtlasTask::where('atlas_worker_id', $workerId)
            ->whereNull('payroll_id')
            ->where('approved_minutes', '>', 0)
            ->where('task_date', '<=', $periodEnd)
            ->get();

        if ($tasksToLock->isEmpty()) {
            return back()->with('error', 'Tidak ada task approved yang bisa di-generate untuk periode ini.');
        }

        $totalApprovedMinutes = $tasksToLock->sum('approved_minutes');
        $totalHours = $totalApprovedMinutes / 60;
        $totalRupiah = $totalHours * $ratePerHour;

        DB::beginTransaction();
        try {
            // 1. Create Payroll Record
            $payroll = Payroll::create([
                'atlas_worker_id' => $workerId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_approved_minutes' => $totalApprovedMinutes,
                'amount_rupiah' => $totalRupiah,
                'status' => 'UNPAID',
            ]);

            // 2. Lock the tasks
            AtlasTask::whereIn('id', $tasksToLock->pluck('id'))->update([
                'payroll_id' => $payroll->id,
            ]);

            DB::commit();

            return back()->with('success', 'Payroll berhasil di-generate sejumlah Rp ' . number_format($totalRupiah, 0, ',', '.') . ' untuk ' . $tasksToLock->count() . ' tasks.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function markAsPaid(Payroll $payroll)
    {
        $payroll->update([
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Payroll berhasil ditandai sebagai PAID.');
    }
}
