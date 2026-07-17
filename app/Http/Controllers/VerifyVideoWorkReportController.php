<?php

namespace App\Http\Controllers;

use App\Models\VideoWorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyVideoWorkReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $pendingReports = VideoWorkReport::query()
            ->with(['partner'])
            ->where('qc_status', 'pending')
            ->when($search, function ($query, $search) {
                $query->whereHas('partner', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('mitra_id', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            })
            ->orderBy('submission_date', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Calculate statistics for stat cards
        $totalPendingCount = VideoWorkReport::where('qc_status', 'pending')->count();
        $totalApprovedCountToday = VideoWorkReport::where('qc_status', 'approved')
            ->whereDate('verified_at', today())
            ->count();
        $totalRejectedCountToday = VideoWorkReport::where('qc_status', 'rejected')
            ->whereDate('verified_at', today())
            ->count();

        return view('video-submissions.qc-room', compact(
            'pendingReports', 
            'search',
            'totalPendingCount',
            'totalApprovedCountToday',
            'totalRejectedCountToday'
        ));
    }

    public function verify(Request $request, VideoWorkReport $report, \App\Actions\VerifyVideoWorkReportAction $verifyAction)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve_full,approve_partial,reject',
            'approved_duration_minutes' => 'nullable|integer|min:0|max:' . $report->submitted_duration_minutes,
            'verifier_notes' => 'required_if:action,reject|nullable|string|max:1000',
        ], [
            'verifier_notes.required_if' => 'Alasan penolakan wajib diisi jika laporan ditolak.',
        ]);

        $msg = $verifyAction->execute($report, $validated, Auth::id());

        return redirect()->route('video-submissions.qc-room')->with('success', $msg);
    }
}
