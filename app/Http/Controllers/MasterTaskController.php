<?php

namespace App\Http\Controllers;

use App\Models\AtlasTask;
use App\Models\MasterTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterTaskController extends Controller
{
    public function index(Request $request)
    {
        // Automatically sync new tasks from AtlasTask into MasterTask
        $existingTaskNames = MasterTask::pluck('name')->toArray();
        $uniqueAtlasTasks = AtlasTask::distinct('task_name')->pluck('task_name')->toArray();
        
        $newTasks = array_diff($uniqueAtlasTasks, $existingTaskNames);
        if (!empty($newTasks)) {
            $insertData = [];
            foreach ($newTasks as $task) {
                if (!empty($task)) {
                    $insertData[] = [
                        'name' => $task,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (count($insertData) > 0) {
                MasterTask::insert($insertData);
            }
        }

        $sort = $request->get('sort', 'most_frequent');
        $search = $request->get('search');

        // Base Query with Aggregation
        $query = MasterTask::leftJoin('atlas_tasks', 'master_tasks.name', '=', 'atlas_tasks.task_name')
            ->select(
                'master_tasks.name',
                DB::raw('COUNT(atlas_tasks.id) as total_occurrences'),
                DB::raw('COALESCE(SUM(atlas_tasks.worked_minutes), 0) as total_worked'),
                DB::raw('COALESCE(SUM(atlas_tasks.approved_minutes), 0) as total_approved'),
                DB::raw('COALESCE(SUM(atlas_tasks.rejected_minutes), 0) as total_rejected'),
                DB::raw('CASE WHEN SUM(atlas_tasks.worked_minutes) > 0 THEN (SUM(atlas_tasks.approved_minutes) / SUM(atlas_tasks.worked_minutes)) * 100 ELSE 0 END as approval_rate'),
                DB::raw('CASE WHEN SUM(atlas_tasks.worked_minutes) > 0 THEN (SUM(atlas_tasks.rejected_minutes) / SUM(atlas_tasks.worked_minutes)) * 100 ELSE 0 END as reject_rate')
            )
            ->groupBy('master_tasks.name', 'master_tasks.id');

        // Apply Search
        if ($search) {
            $query->where('master_tasks.name', 'like', "%{$search}%");
        }

        // Apply Sorting
        switch ($sort) {
            case 'most_frequent':
                $query->orderByDesc('total_occurrences');
                break;
            case 'least_frequent':
                $query->orderBy('total_occurrences')->where('total_occurrences', '>', 0);
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
                $query->orderBy('master_tasks.name');
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
