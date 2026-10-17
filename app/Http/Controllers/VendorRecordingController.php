<?php

namespace App\Http\Controllers;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use Illuminate\Http\Request;

class VendorRecordingController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user()->partner;
        abort_unless($partner && $partner->partner_role === Partner::ROLE_MITRA, 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'worker_id' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', ...($request->filled('start_date') ? ['after_or_equal:start_date'] : [])],
        ]);

        $team = Partner::where('mitra_parent_id', $partner->id)->where('partner_role', Partner::ROLE_WORKER);
        $userIds = (clone $team)->whereNotNull('user_id')->select('user_id');
        // Explicit account links take precedence. Email fallback only applies to
        // unlinked Atlas accounts, never to an account linked to another team.
        $workersQuery = AtlasWorker::where(function ($query) use ($team, $userIds) {
            $query->whereIn('user_id', $userIds)
                ->orWhere(function ($unlinked) use ($team) {
                    $unlinked->whereNull('user_id')->whereIn('atlas_email', (clone $team)->whereNotNull('email')->select('email'));
                });
        });
        $workers = (clone $workersQuery)->with('user:id,name')->orderBy('atlas_email')->get();
        $query = AtlasTask::whereIn('atlas_worker_id', (clone $workersQuery)->select('id'));

        if ($request->filled('worker_id')) {
            $query->where('atlas_worker_id', $filters['worker_id']);
        }
        if ($request->filled('search')) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($query) use ($search) {
                $query->where('task_name', 'like', $search)->orWhere('atlas_task_id', 'like', $search)
                    ->orWhereHas('atlasWorker', fn ($worker) => $worker->where('atlas_email', 'like', $search)
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $search)));
            });
        }
        foreach (['start_date' => '>=', 'end_date' => '<='] as $key => $operator) {
            if ($request->filled($key)) {
                $query->where('task_date', $operator, $filters[$key]);
            }
        }

        $stats = (clone $query)->selectRaw('COUNT(*) AS recordings, COUNT(DISTINCT atlas_worker_id) AS workers,
            COALESCE(SUM(worked_minutes), 0) AS worked, COALESCE(SUM(approved_minutes), 0) AS approved,
            COALESCE(SUM(rejected_minutes), 0) AS rejected, COALESCE(SUM(review_minutes), 0) AS pending,
            COALESCE(SUM(deferred_minutes), 0) AS deferred')->first();
        $records = $query->with('atlasWorker.user:id,name')->orderByDesc('recorded_at')->orderByDesc('id')
            ->paginate(50)->withQueryString();

        return view('vendor.recordings', compact('records', 'stats', 'workers'));
    }
}
