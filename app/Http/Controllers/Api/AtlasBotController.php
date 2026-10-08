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

        if (!$request->has('records') || !is_array($request->input('records'))) {
            return response()->json(['error' => 'Invalid or empty JSON payload. Ensure Content-Type is application/json.'], 400);
        }

        $records = $request->input('records', []);
        
        $count = 0;
        $now = now();
        
        foreach ($records as $rec) {
            // Get or create AtlasWorker mapping
            $email = $rec['participant'];
            $worker = \App\Models\AtlasWorker::firstOrCreate(
                ['atlas_email' => $email],
                ['group' => 'ASTRO', 'active' => true]
            );

            // Update last sync time
            $worker->update(['last_synced_at' => $now]);

            // Map status logic
            $approved = $rec['billable_min'] ?? ($rec['billable_hours'] * 60 ?? 0);
            $rejected = $rec['rejected_min'] ?? ($rec['rejected_hours'] * 60 ?? 0);
            $pending = $rec['pending_min'] ?? ($rec['pending_hours'] * 60 ?? 0);
            $total = $rec['duration_min'] ?? ($rec['duration_sec'] / 60 ?? 0);
            
            $status = 'UNDER_REVIEW';
            if ($approved > 0 && $rejected == 0 && $pending == 0) {
                $status = 'APPROVED';
            } elseif ($rejected > 0 && $approved == 0 && $pending == 0) {
                $status = 'REJECTED';
            } elseif ($approved > 0 && $rejected > 0) {
                $status = 'PARTIALLY_APPROVED';
            }

            \App\Models\AtlasTask::updateOrCreate(
                [
                    'atlas_worker_id' => $worker->id,
                    'atlas_task_id' => (string) $rec['record_id'],
                ],
                [
                    'task_date' => date('Y-m-d', strtotime($rec['recorded_utc'])),
                    'recorded_at' => date('Y-m-d H:i:s', strtotime($rec['recorded_utc'])),
                    'task_name' => $rec['task'],
                    'worked_minutes' => $total,
                    'approved_minutes' => $approved,
                    'rejected_minutes' => $rejected,
                    'review_minutes' => $pending,
                    'billable_minutes' => $approved, // For now, mapped directly, can be updated later per rules
                    'status' => $status,
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
