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

        $recordings = $query->orderBy('task_date', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(50)
                            ->withQueryString();

        return view('admin.atlas-recordings.index', compact('recordings'));
    }
}
