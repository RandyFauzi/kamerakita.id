<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log a user activity.
     *
     * @param string $activity The activity key (e.g., 'auth.login')
     * @param string|null $description The detailed message description
     * @return ActivityLog
     */
    public static function log(string $activity, ?string $description = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => $activity,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
