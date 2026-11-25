<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtlasTask;
use App\Models\{Partner, AtlasWorker, User};
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
        $request->validate(['source' => ['nullable', 'in:atlas,1618'],
            'partner_id' => ['required_with:account_scope', 'nullable', 'uuid', 'exists:partners,id'],
            'account_scope' => ['nullable', 'in:account,team']]);
        $query = AtlasTask::with('atlasWorker');
        $recordingPartner = null;
        $recordingScope = 'account';
        if ($request->filled('partner_id')) {
            $recordingPartner = Partner::with('user:id,email')->findOrFail($request->input('partner_id'));
            $recordingScope = $request->input('account_scope') ?? ($recordingPartner->isTeamParent() ? 'team' : 'account');
            abort_if($recordingScope === 'team' && !$recordingPartner->isTeamParent(), 422, 'Akun ini tidak memiliki cakupan tim.');
            $owners = Partner::with('user:id,email')->where(function ($owners) use ($recordingPartner, $recordingScope) {
                $owners->whereKey($recordingPartner->id);
                if ($recordingScope === 'team') {
                    $owners->orWhere(fn ($children) => $children->where('mitra_parent_id', $recordingPartner->id)
                        ->where('partner_role', Partner::ROLE_WORKER));
                }
            })->get(['id', 'user_id', 'email']);
            $userIds = $owners->pluck('user_id')->filter()->unique()->values();
            $emails = $owners->flatMap(fn ($owner) => [$owner->email, $owner->user?->email])
                ->filter()->map(fn ($email) => strtolower(trim($email)))->unique()->values();
            $workers = AtlasWorker::where(function ($workers) use ($userIds, $emails) {
                $workers->whereIn('user_id', $userIds)->orWhere(function ($unlinked) use ($userIds, $emails) {
                    // Explicit ownership wins. Stale partner emails must never
                    // claim an Atlas account assigned to a different user.
                    $unlinked->whereNull('user_id')->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(atlas_email)'), $emails)
                        ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(atlas_email)'),
                            User::whereNotIn('id', $userIds)->selectRaw('LOWER(email)'));
                });
            });
            $query->whereIn('atlas_worker_id', $workers->select('id'));
        }

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

        // Aggregate Stats in a single query
        $statsQuery = clone $query;
        $statsQuery->setEagerLoads([]);
        $statsRow = $statsQuery->selectRaw('
            COALESCE(SUM(worked_minutes), 0) as total_worked,
            COALESCE(SUM(approved_minutes), 0) as total_approved,
            COALESCE(SUM(CASE WHEN payroll_id IS NOT NULL THEN approved_minutes ELSE 0 END), 0) as total_paid,
            COALESCE(SUM(CASE WHEN payroll_id IS NULL THEN approved_minutes ELSE 0 END), 0) as total_unpaid,
            COALESCE(SUM(review_minutes), 0) as total_review,
            COALESCE(SUM(deferred_minutes), 0) as total_deferred,
            COALESCE(SUM(rejected_minutes), 0) as total_rejected
        ')->first();

        $stats = [
            'total_worked' => (float) ($statsRow->total_worked ?? 0),
            'total_approved' => (float) ($statsRow->total_approved ?? 0),
            'total_paid' => (float) ($statsRow->total_paid ?? 0),
            'total_unpaid' => (float) ($statsRow->total_unpaid ?? 0),
            'total_review' => (float) ($statsRow->total_review ?? 0),
            'total_deferred' => (float) ($statsRow->total_deferred ?? 0),
            'total_rejected' => (float) ($statsRow->total_rejected ?? 0),
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
        return view('admin.atlas-recordings.index', compact('recordings', 'stats', 'trend', 'syncRun', 'recordingPartner', 'recordingScope'));
    }
}
