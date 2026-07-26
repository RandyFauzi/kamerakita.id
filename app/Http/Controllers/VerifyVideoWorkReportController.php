<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Models\PeriodApproval;
use App\Services\PeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifyVideoWorkReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get available periods and determine selected period
        $periods = PeriodService::getAvailablePeriods();
        
        $selectedPeriodKey = $request->input('period');
        if (!$selectedPeriodKey && !empty($periods)) {
            $selectedPeriodKey = $periods[0]['start']->format('Y-m-d') . '|' . $periods[0]['end']->format('Y-m-d');
        }

        if ($selectedPeriodKey) {
            $parts = explode('|', $selectedPeriodKey);
            $startDate = Carbon::parse($parts[0])->startOfDay();
            $endDate = Carbon::parse($parts[1])->endOfDay();
        } else {
            $range = PeriodService::getPeriodRange(now());
            $startDate = $range['start'];
            $endDate = $range['end'];
        }

        // 2. Fetch partners who submitted reports in this period
        $search = $request->input('search');
        $query = Partner::whereHas('videoWorkReports', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('mitra_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $partners = $query->paginate(15)->withQueryString();

        // 3. For each partner, load their reports in this period and their period approval status
        foreach ($partners as $partner) {
            $reports = VideoWorkReport::where('partner_id', $partner->id)
                ->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('submission_date', 'asc')
                ->get();

            $totalReportedMinutes = $reports->sum('submitted_duration_minutes');
            
            // Get period approval
            $approval = PeriodApproval::where('partner_id', $partner->id)
                ->where('period_start_date', $startDate->format('Y-m-d'))
                ->where('period_end_date', $endDate->format('Y-m-d'))
                ->first();

            $partner->period_reports = $reports;
            $partner->total_reported_minutes = $totalReportedMinutes;
            $partner->period_approval = $approval;
            
            // Default input values
            $partner->input_approved_minutes = $approval ? $approval->approved_minutes : $totalReportedMinutes;
            $partner->input_verifier_notes = $approval ? $approval->verifier_notes : '';
            $partner->approval_status = $approval ? $approval->status : 'none'; // none, draft, approved, paid
        }

        // Stats summary for the selected period
        $periodReportedMin = VideoWorkReport::whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->sum('submitted_duration_minutes');
        $periodApprovedMin = VideoWorkReport::whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('qc_status', 'approved')
            ->sum('approved_duration_minutes');

        $formatDur = function (int $minutes) {
            $hours = floor($minutes / 60);
            $remaining = $minutes % 60;
            if ($hours > 0) {
                return "{$hours}j {$remaining}m";
            }
            return "{$remaining}m";
        };

        $filteredSubmittedDuration = $formatDur($periodReportedMin);
        $filteredApprovedDuration = $formatDur($periodApprovedMin);

        // General stats for badge counts
        $totalPendingCount = VideoWorkReport::where('qc_status', 'pending')->count();
        $totalOnReviewCount = VideoWorkReport::where('qc_status', 'on_review')->count();

        return view('video-submissions.qc-room', compact(
            'partners',
            'periods',
            'selectedPeriodKey',
            'startDate',
            'endDate',
            'search',
            'totalPendingCount',
            'totalOnReviewCount',
            'filteredSubmittedDuration',
            'filteredApprovedDuration'
        ));
    }

    /**
     * Save draft verification minutes for a period (admin only sees it, not released to worker)
     */
    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date',
            'approved_minutes' => 'required|integer|min:0',
            'verifier_notes' => 'nullable|string|max:1000',
        ]);

        $approval = PeriodApproval::updateOrCreate([
            'partner_id' => $validated['partner_id'],
            'period_start_date' => $validated['period_start_date'],
            'period_end_date' => $validated['period_end_date'],
        ], [
            'approved_minutes' => $validated['approved_minutes'],
            'verifier_notes' => $validated['verifier_notes'],
            'status' => 'draft',
        ]);

        // Keep daily reports as on_review or pending when draft is saved
        VideoWorkReport::where('partner_id', $validated['partner_id'])
            ->whereBetween('submission_date', [$validated['period_start_date'], $validated['period_end_date']])
            ->update(['qc_status' => 'on_review']);

        return redirect()->back()->with('success', 'Draf durasi persetujuan periode berhasil disimpan.');
    }

    /**
     * Finalize and release approval to worker (marks reports as approved and releases to payroll)
     */
    public function finalizeApproval(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date',
            'approved_minutes' => 'required|integer|min:0',
            'verifier_notes' => 'nullable|string|max:1000',
        ]);

        $partnerId = $validated['partner_id'];
        $startDate = $validated['period_start_date'];
        $endDate = $validated['period_end_date'];
        $approvedMinutes = $validated['approved_minutes'];

        DB::transaction(function () use ($partnerId, $startDate, $endDate, $approvedMinutes, $validated) {
            // 1. Update or create period approval record as approved
            PeriodApproval::updateOrCreate([
                'partner_id' => $partnerId,
                'period_start_date' => $startDate,
                'period_end_date' => $endDate,
            ], [
                'approved_minutes' => $approvedMinutes,
                'verifier_notes' => $validated['verifier_notes'],
                'status' => 'approved',
            ]);

            // 2. Fetch all work reports in this period for the partner
            $reports = VideoWorkReport::where('partner_id', $partnerId)
                ->whereBetween('submission_date', [$startDate, $endDate])
                ->get();

            $totalReported = $reports->sum('submitted_duration_minutes');
            
            // 3. Distribute minutes proportionally and set to approved
            $distributed = 0;
            foreach ($reports as $index => $report) {
                if ($totalReported == 0) {
                    $appDur = 0;
                } else {
                    if ($index === count($reports) - 1) {
                        $appDur = $approvedMinutes - $distributed;
                    } else {
                        $appDur = round(($report->submitted_duration_minutes / $totalReported) * $approvedMinutes);
                        $distributed += $appDur;
                    }
                }

                $report->update([
                    'qc_status' => 'approved',
                    'approved_duration_minutes' => $appDur,
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'verifier_notes' => $validated['verifier_notes'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Persetujuan periode berhasil difinalisasi dan dirilis ke mitra.');
    }

    public function destroy(VideoWorkReport $report)
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }
        
        $report->delete();

        return redirect()->back()->with('success', 'Laporan video berhasil dihapus dari sistem.');
    }

    public function rejectReport(Request $request, VideoWorkReport $report)
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $report->update([
            'qc_status' => 'rejected',
            'approved_duration_minutes' => 0,
            'verifier_notes' => $validated['reason'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Laporan video berhasil ditolak dan diminta revisi.');
    }

    public function restoreReport(VideoWorkReport $report)
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $report->update([
            'qc_status' => 'on_review',
            'approved_duration_minutes' => 0,
            'verifier_notes' => null,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return redirect()->back()->with('success', 'Status penolakan laporan berhasil dibatalkan dan dikembalikan ke antrean review.');
    }
}
