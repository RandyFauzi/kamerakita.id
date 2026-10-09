<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtlasTask;
use App\Models\MasterTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterTaskController extends Controller
{
    public function index()
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

        // Aggregate statistics for each task
        $taskStats = AtlasTask::select(
            'task_name',
            DB::raw('COUNT(*) as total_occurrences'),
            DB::raw('SUM(worked_minutes) as total_worked'),
            DB::raw('SUM(approved_minutes) as total_approved'),
            DB::raw('SUM(rejected_minutes) as total_rejected')
        )
        ->groupBy('task_name')
        ->having('total_worked', '>', 0)
        ->get()
        ->map(function ($task) {
            $task->approval_rate = ($task->total_approved / $task->total_worked) * 100;
            $task->reject_rate = ($task->total_rejected / $task->total_worked) * 100;
            return $task;
        });

        // Top 5 Approval Rate
        $topApproved = $taskStats->sortByDesc('approval_rate')->take(5)->values();
        
        // Top 5 Reject Rate
        $topRejected = $taskStats->sortByDesc('reject_rate')->take(5)->values();

        // All Master Tasks with stats
        $masterTasks = MasterTask::orderBy('name')->paginate(50);
        
        // Attach stats to paginated collection
        $masterTasks->getCollection()->transform(function ($master) use ($taskStats) {
            $stats = $taskStats->firstWhere('task_name', $master->name);
            $master->stats = $stats;
            return $master;
        });

        return view('admin.tasks.index', compact('masterTasks', 'topApproved', 'topRejected'));
    }
}
