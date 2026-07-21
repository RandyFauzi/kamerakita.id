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
        $status = $request->input('status', 'pending'); // default to pending if not specified
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = VideoWorkReport::query()
            ->with(['partner'])
            // Status filter
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('qc_status', $status);
            })
            // Date filters
            ->when($startDate, function ($query, $startDate) {
                $query->where('submission_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->where('submission_date', '<=', $endDate);
            })
            // Search filter
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('partner', function ($sub) use ($search) {
                        $sub->where('full_name', 'like', "%{$search}%")
                            ->orWhere('mitra_id', 'like', "%{$search}%");
                    })->orWhere('id', 'like', "%{$search}%");
                });
            });

        // Clone base query to calculate dynamic stats
        $totalSubmittedMin = (clone $query)->sum('submitted_duration_minutes');
        $totalApprovedMin = (clone $query)->sum('approved_duration_minutes');

        // Helper to format minutes to a clean "Xh Ym" string.
        $formatDur = function (int $minutes) {
            $hours = floor($minutes / 60);
            $remaining = $minutes % 60;
            if ($hours > 0) {
                return "{$hours}j {$remaining}m";
            }
            return "{$remaining}m";
        };

        $filteredSubmittedDuration = $formatDur($totalSubmittedMin);
        $filteredApprovedDuration = $formatDur($totalApprovedMin);

        $reports = $query->orderBy('submission_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calculate statistics for stat cards
        $totalPendingCount = VideoWorkReport::where('qc_status', 'pending')->count();
        $totalOnReviewCount = VideoWorkReport::where('qc_status', 'on_review')->count();
        $totalApprovedCountToday = VideoWorkReport::where('qc_status', 'approved')
            ->whereDate('verified_at', today())
            ->count();
        $totalRejectedCountToday = VideoWorkReport::where('qc_status', 'rejected')
            ->whereDate('verified_at', today())
            ->count();

        return view('video-submissions.qc-room', compact(
            'reports', 
            'search',
            'status',
            'startDate',
            'endDate',
            'totalPendingCount',
            'totalOnReviewCount',
            'totalApprovedCountToday',
            'totalRejectedCountToday',
            'filteredSubmittedDuration',
            'filteredApprovedDuration'
        ));
    }

    public function verify(Request $request, VideoWorkReport $report, \App\Actions\VerifyVideoWorkReportAction $verifyAction)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve_full,approve_partial,reject,start_review,revert',
            'approved_duration_minutes' => 'nullable|integer|min:0|max:' . $report->submitted_duration_minutes,
            'verifier_notes' => 'required_if:action,reject,approve_partial|nullable|string|max:1000',
        ], [
            'verifier_notes.required_if' => 'Alasan wajib diisi jika laporan ditolak atau disetujui sebagian.',
        ]);

        try {
            $msg = $verifyAction->execute($report, $validated, Auth::id());
            return redirect()->route('video-submissions.qc-room', ['status' => $report->qc_status])->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(VideoWorkReport $report)
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }
        
        $report->delete();

        return redirect()->route('video-submissions.qc-room')->with('success', 'Laporan video berhasil dihapus dari sistem.');
    }

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $reports = VideoWorkReport::query()
            ->with(['partner'])
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('qc_status', $status);
            })
            ->when($startDate, function ($query, $startDate) {
                $query->where('submission_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->where('submission_date', '<=', $endDate);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('partner', function ($sub) use ($search) {
                        $sub->where('full_name', 'like', "%{$search}%")
                            ->orWhere('mitra_id', 'like', "%{$search}%");
                    })->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->orderBy('submission_date', 'desc')
            ->get(); // get all filtered reports for printing (no pagination)

        return view('video-submissions.export-pdf', compact('reports', 'status', 'startDate', 'endDate'));
    }
}
