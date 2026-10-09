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
        ]);

        $workerId = $request->atlas_worker_id;
        $periodStart = $request->period_start;
        $periodEnd = $request->period_end;

        // Automatically determine rate
        $worker = AtlasWorker::with('user.partner')->find($workerId);
        $partner = $worker->user->partner ?? null;
        
        $ratePerHour = 60000; // Default flat rate
        if ($partner) {
            if ($partner->base_hourly_rate > 0) {
                $ratePerHour = $partner->base_hourly_rate;
            } else {
                // If under mitra, 50k. Otherwise 60k.
                if (!empty($partner->mitra_parent_id) || !empty($partner->mitra_id)) {
                    $ratePerHour = 50000;
                } else {
                    $ratePerHour = 60000;
                }
            }
        }

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
            $payroll = Payroll::create([
                'atlas_worker_id' => $workerId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_approved_minutes' => $totalApprovedMinutes,
                'amount_rupiah' => $totalRupiah,
                'status' => 'UNPAID',
            ]);

            AtlasTask::whereIn('id', $tasksToLock->pluck('id'))->update([
                'payroll_id' => $payroll->id,
            ]);

            DB::commit();

            return back()->with('success', 'Payroll berhasil di-generate sejumlah Rp ' . number_format($totalRupiah, 0, ',', '.') . ' (Rate: Rp '.number_format($ratePerHour, 0, ',', '.').'/jam).');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Payroll $payroll)
    {
        DB::beginTransaction();
        try {
            AtlasTask::where('payroll_id', $payroll->id)->update([
                'payroll_id' => null,
            ]);
            $payroll->delete();
            DB::commit();
            return back()->with('success', 'Data tagihan berhasil dihapus dan task dikembalikan ke status Unpaid.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
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
