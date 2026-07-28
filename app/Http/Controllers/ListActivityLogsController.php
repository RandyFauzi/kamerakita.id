<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ListActivityLogsController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = $request->input('search');
        $activity = $request->input('activity');
        $userId = $request->input('user_id');

        $logs = ActivityLog::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('activity', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($qu) use ($search) {
                          $qu->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($activity, function ($query, $activity) {
                $query->where('activity', $activity);
            })
            ->when($userId, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $users = User::orderBy('name')->get();
        
        $activities = ActivityLog::select('activity')
            ->distinct()
            ->orderBy('activity')
            ->pluck('activity');

        return view('activity-logs.index', compact('logs', 'users', 'activities'));
    }
}
