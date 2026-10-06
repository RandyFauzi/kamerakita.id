<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtlasBotController extends Controller
{
    public function sync(Request $request)
    {
        // Simple security token check
        $token = $request->header('X-Bot-Token') ?? $request->input('bot_token');
        if ($token !== 'ATLAS_SYNC_KAMERAKITA_2026_SECRET') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $records = $request->input('records', []);
        
        $count = 0;
        foreach ($records as $rec) {
            \App\Models\AtlasTask::updateOrCreate(
                [
                    'email' => $rec['participant'], // the bot passes the email here
                    'task_date' => date('Y-m-d', strtotime($rec['recorded_utc'])),
                    'task_name' => $rec['task'],
                ],
                [
                    'time_str' => date('H:i:s', strtotime($rec['recorded_utc'])),
                    'approved_mins' => $rec['billable_min'] ?? ($rec['billable_hours'] * 60 ?? 0),
                    'rejected_mins' => $rec['rejected_min'] ?? ($rec['rejected_hours'] * 60 ?? 0),
                    'pending_mins' => $rec['pending_min'] ?? ($rec['pending_hours'] * 60 ?? 0),
                    'total_video_mins' => $rec['duration_min'] ?? ($rec['duration_sec'] / 60 ?? 0),
                    'notes' => $rec['reconciliation'] ?? null,
                ]
            );
            $count++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Successfully synced $count records."
        ]);
    }
}
