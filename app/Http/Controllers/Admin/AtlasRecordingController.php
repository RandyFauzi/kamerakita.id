<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtlasTask;
use Illuminate\Http\Request;

class AtlasRecordingController extends Controller
{
    public function index(Request $request)
    {
        $query = AtlasTask::with('atlasWorker');

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

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('task_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->where('task_date', '<=', $request->input('end_date'));
        }

        // Aggregate Stats
        $statsQuery = clone $query;
        $statsQuery->setEagerLoads([]);
        $stats = [
            'total_worked' => $statsQuery->sum('worked_minutes'),
            'total_approved' => $statsQuery->sum('approved_minutes'),
            'total_review' => $statsQuery->sum('review_minutes'),
            'total_rejected' => $statsQuery->sum('rejected_minutes'),
        ];

        // Chart Data (Group by date)
        $chartQuery = clone $query;
        $chartQuery->setEagerLoads([]);
        $trend = $chartQuery->selectRaw('task_date, SUM(worked_minutes) as worked, SUM(approved_minutes) as approved, SUM(review_minutes) as review, SUM(rejected_minutes) as rejected')
                            ->groupBy('task_date')
                            ->orderBy('task_date', 'desc')
                            ->limit(90)
                            ->get()
                            ->reverse()
                            ->values();

        $recordings = $query->orderBy('recorded_at', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(50)
                            ->withQueryString();

        return view('admin.atlas-recordings.index', compact('recordings', 'stats', 'trend'));
    }
}
