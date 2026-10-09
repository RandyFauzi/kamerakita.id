<?php

namespace App\Http\Controllers;

use App\Models\AtlasTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterTaskController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'most_frequent');
        $search = $request->get('search');

        // Base Query with Aggregation directly on AtlasTask
        $query = AtlasTask::select(
                'task_name as name',
                DB::raw('COUNT(id) as total_occurrences'),
                DB::raw('COALESCE(SUM(worked_minutes), 0) as total_worked'),
                DB::raw('COALESCE(SUM(approved_minutes), 0) as total_approved'),
                DB::raw('COALESCE(SUM(rejected_minutes), 0) as total_rejected'),
                DB::raw('CASE WHEN SUM(worked_minutes) > 0 THEN (SUM(approved_minutes) / SUM(worked_minutes)) * 100 ELSE 0 END as approval_rate'),
                DB::raw('CASE WHEN SUM(worked_minutes) > 0 THEN (SUM(rejected_minutes) / SUM(worked_minutes)) * 100 ELSE 0 END as reject_rate')
            )
            ->whereNotNull('task_name')
            ->where('task_name', '!=', '')
            ->groupBy('task_name');

        // Apply Search
        if ($search) {
            $query->where('task_name', 'like', "%{$search}%");
        }

        // Apply Sorting
        switch ($sort) {
            case 'most_frequent':
                $query->orderByDesc('total_occurrences');
                break;
            case 'least_frequent':
                $query->having('total_occurrences', '>', 0)->orderBy('total_occurrences');
                break;
            case 'highest_approval': // Paling Mudah
                $query->orderByDesc('approval_rate')->orderByDesc('total_occurrences');
                break;
            case 'highest_reject': // Paling Sulit
                $query->orderByDesc('reject_rate')->orderByDesc('total_occurrences');
                break;
            case 'most_hours':
                $query->orderByDesc('total_worked');
                break;
            case 'name_asc':
                $query->orderBy('task_name');
                break;
            default:
                $query->orderByDesc('total_occurrences');
        }

        $masterTasks = $query->paginate(30)->withQueryString();

        // Top 5 Approval Rate (Only tasks with > 60 mins work to prevent 100% on 1min tasks)
        $topApproved = AtlasTask::select(
            'task_name',
            DB::raw('COUNT(*) as total_occurrences'),
            DB::raw('SUM(worked_minutes) as total_worked'),
            DB::raw('CASE WHEN SUM(worked_minutes) > 0 THEN (SUM(approved_minutes) / SUM(worked_minutes)) * 100 ELSE 0 END as approval_rate')
        )
        ->groupBy('task_name')
        ->having('total_worked', '>', 60)
        ->orderByDesc('approval_rate')
        ->take(5)->get();
        
        // Top 5 Reject Rate
        $topRejected = AtlasTask::select(
            'task_name',
            DB::raw('COUNT(*) as total_occurrences'),
            DB::raw('SUM(worked_minutes) as total_worked'),
            DB::raw('CASE WHEN SUM(worked_minutes) > 0 THEN (SUM(rejected_minutes) / SUM(worked_minutes)) * 100 ELSE 0 END as reject_rate')
        )
        ->groupBy('task_name')
        ->having('total_worked', '>', 60)
        ->orderByDesc('reject_rate')
        ->take(5)->get();

        return view('tasks.index', compact('masterTasks', 'topApproved', 'topRejected', 'sort', 'search'));
    }
}
