<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtlasTask;
use App\Models\RoboticsSyncRun;
use App\Services\Robotics1618Sync;
use Illuminate\Http\Request;

class AtlasRecordingController extends Controller
{
    public function refresh1618(Request $request, Robotics1618Sync $sync)
    {
        abort_unless(config('services.robotics1618.enabled'), 503, 'Sinkronisasi server 1618 belum diaktifkan.');
        $sync->enqueue('manual', $request->user()->id);
        return redirect()->route('admin.recordings.index')->with('sync_message', 'Permintaan pembaruan disimpan. Scheduler akan memprosesnya dalam sekitar satu menit.');
    }

    public function syncStatus()
    {
        return response()->json(RoboticsSyncRun::latest('id')->first());
    }

    public function index(Request $request)
    {
        $request->validate(['source' => ['nullable', 'in:atlas,1618']]);
        $query = AtlasTask::with('atlasWorker');

        if ($request->filled('source')) {
            $query->where('ingest_source', $request->input('source'));
        }

        // Search by email, task_name, or notes
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('atlasWorker', function($w) use ($search) {
                      $w->where('atlas_email', 'like', "%{$search}%");
                  })
                  ->orWhere('task_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $isMysql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql';
        $dateExpr = $isMysql
            ? "COALESCE(DATE(DATE_ADD(recorded_at, INTERVAL 8 HOUR)), task_date)"
            : "COALESCE(DATE(datetime(recorded_at, '+8 hours')), task_date)";

        // Filter by date range (aligned with Makassar / WITA local time)
        if ($request->filled('start_date')) {
            $query->whereRaw("{$dateExpr} >= ?", [$request->input('start_date')]);
        }
        if ($request->filled('end_date')) {
            $query->whereRaw("{$dateExpr} <= ?", [$request->input('end_date')]);
        }

        // Filter by salary status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'paid') {
                $query->whereNotNull('payroll_id');
            } elseif ($status === 'pending') {
                $query->whereNull('payroll_id')
                      ->where('task_name', 'not like', '%(Sisa)')
                      ->where(function($q) {
                          $q->where('approved_minutes', '>', 0)
                            ->orWhere('review_minutes', '>', 0)
                            ->orWhere('deferred_minutes', '>', 0)
                            ->orWhere('rejected_minutes', '=', 0); // Exclude fully rejected
                      });
            } elseif ($status === 'sisa') {
                $query->whereNull('payroll_id')->where('task_name', 'like', '%(Sisa)');
            } elseif ($status === 'rejected') {
                $query->whereNull('payroll_id')
                      ->where('approved_minutes', 0)
                      ->where('review_minutes', 0)
                      ->where('deferred_minutes', 0)
                      ->where('rejected_minutes', '>', 0);
            }
        }

        // Aggregate Stats
        $statsQuery = clone $query;
        $statsQuery->setEagerLoads([]);
        $stats = [
            'total_worked' => $statsQuery->sum('worked_minutes'),
            'total_approved' => $statsQuery->sum('approved_minutes'),
            'total_paid' => (clone $statsQuery)->whereNotNull('payroll_id')->sum('approved_minutes'),
            'total_unpaid' => (clone $statsQuery)->whereNull('payroll_id')->sum('approved_minutes'),
            'total_review' => $statsQuery->sum('review_minutes'),
            'total_deferred' => $statsQuery->sum('deferred_minutes'),
            'total_rejected' => $statsQuery->sum('rejected_minutes'),
        ];

        // Chart Data (Group by date, aligned with Makassar / WITA local time)
        $chartQuery = clone $query;
        $chartQuery->setEagerLoads([]);
        $trend = $chartQuery->selectRaw("{$dateExpr} as task_date, SUM(worked_minutes) as worked, SUM(approved_minutes) as approved, SUM(review_minutes) as review, SUM(rejected_minutes) as rejected, SUM(deferred_minutes) as deferred")
                            ->groupBy(\Illuminate\Support\Facades\DB::raw($dateExpr))
                            ->orderBy(\Illuminate\Support\Facades\DB::raw($dateExpr), 'desc')
                            ->limit(90)
                            ->get()
                            ->reverse()
                            ->values();

        $recordings = $query->orderBy('recorded_at', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(50)
                            ->withQueryString();

        $syncRun = RoboticsSyncRun::latest('id')->first();
        return view('admin.atlas-recordings.index', compact('recordings', 'stats', 'trend', 'syncRun'));
    }
}
