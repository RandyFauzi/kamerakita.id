<?php

namespace App\Http\Controllers\Astro;

use App\Http\Controllers\Controller;
use App\Models\AtlasWorker;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AstroRecordingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Find AtlasWorker mapping (fallback to email if not yet linked)
        $worker = AtlasWorker::where('user_id', $user->id)
            ->orWhere('atlas_email', $user->email)
            ->first();

        // If found by email but no user_id, map it now
        if ($worker && !$worker->user_id) {
            $worker->update(['user_id' => $user->id]);
        }

        // Stats containers
        $todayStats = [
            'total_worked' => 0,
            'approved' => 0,
            'rejected' => 0,
            'review' => 0,
            'total_tasks' => 0
        ];

        $allTimeStats = [
            'total_worked' => 0,
            'approved' => 0,
            'rejected' => 0,
            'review' => 0,
        ];

        $history = collect();

        if ($worker) {
            // Calculate Latest Active Day Stats instead of strict 'today'
            $latestTask = $worker->atlasTasks()->orderBy('task_date', 'desc')->first();
            $latestDate = $latestTask ? $latestTask->task_date : Carbon::today()->toDateString();
            
            $todayTasks = $worker->atlasTasks()->where('task_date', $latestDate)->get();
            
            $todayStats['total_tasks'] = $todayTasks->count();
            $todayStats['total_worked'] = $todayTasks->sum('worked_minutes');
            $todayStats['approved'] = $todayTasks->sum('approved_minutes');
            $todayStats['rejected'] = $todayTasks->sum('rejected_minutes');
            $todayStats['review'] = $todayTasks->sum('review_minutes');
            $todayStats['date'] = $latestDate;

            // Calculate All Time Stats
            $allTimeStats['total_worked'] = $worker->atlasTasks()->sum('worked_minutes');
            $allTimeStats['approved'] = $worker->atlasTasks()->sum('approved_minutes');
            $allTimeStats['rejected'] = $worker->atlasTasks()->sum('rejected_minutes');
            $allTimeStats['review'] = $worker->atlasTasks()->sum('review_minutes');

            // Get History Grouped By Date
            $history = $worker->atlasTasks()
                ->orderBy('task_date', 'desc')
                ->orderBy('recorded_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('task_date');
        }

        return view('astro.recording', [
            'worker' => $worker,
            'todayStats' => $todayStats,
            'allTimeStats' => $allTimeStats,
            'history' => $history
        ]);
    }
}
