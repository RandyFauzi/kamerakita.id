<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Models\PeriodApproval;
use App\Services\CheckRecruiterMilestone;
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

        $startDate = null;
        $endDate = null;

        if ($selectedPeriodKey && $selectedPeriodKey !== 'all') {
            $parts = explode('|', $selectedPeriodKey);
            if (count($parts) === 2) {
                $startDate = Carbon::parse($parts[0])->startOfDay();
                $endDate = Carbon::parse($parts[1])->endOfDay();
            }
        } elseif ($selectedPeriodKey === null) {
            $range = PeriodService::getPeriodRange(now());
            $startDate = $range['start'];
            $endDate = $range['end'];
            $selectedPeriodKey = $startDate->format('Y-m-d') . '|' . $endDate->format('Y-m-d');
        }

        // 2. Fetch partners who submitted reports in this period
        $search = $request->input('search');
        $selectedGroup = $request->input('group');
        $query = Partner::whereHas('videoWorkReports', function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
        });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('mitra_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($selectedGroup) {
            $query->where('group_name', $selectedGroup);
        }

        $partners = $query->paginate(15)->withQueryString();

        // 3. For each partner, load their reports in this period and their period approval status
        foreach ($partners as $partner) {
            $reportsQuery = VideoWorkReport::where('partner_id', $partner->id)->orderBy('submission_date', 'asc');
            if ($startDate && $endDate) {
                $reportsQuery->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
            $reports = $reportsQuery->get();

            $totalReportedMinutes = $reports->sum('submitted_duration_minutes');
            
            // Get period approval
            $approval = null;
            if ($startDate && $endDate) {
                $approval = PeriodApproval::where('partner_id', $partner->id)
                    ->where('period_start_date', $startDate->format('Y-m-d'))
                    ->where('period_end_date', $endDate->format('Y-m-d'))
                    ->first();
            }

            $partner->period_reports = $reports;
            $partner->total_reported_minutes = $totalReportedMinutes;
            $partner->period_approval = $approval;
            
            // Default input values
            $partner->input_approved_minutes = $approval ? $approval->approved_minutes : $totalReportedMinutes;
            $partner->input_verifier_notes = $approval ? $approval->verifier_notes : '';
            $partner->approval_status = $approval ? $approval->status : 'none'; // none, draft, approved, paid
        }

        // Stats summary — ikut filter search DAN grup agar konsisten dengan tabel
        $reportedQuery = VideoWorkReport::query();
        $approvedQuery = VideoWorkReport::where('qc_status', 'approved');

        if ($startDate && $endDate) {
            $reportedQuery->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            $approvedQuery->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }

        if ($selectedGroup) {
            $reportedQuery->whereHas('partner', function ($q) use ($selectedGroup) {
                $q->where('group_name', $selectedGroup);
            });
            $approvedQuery->whereHas('partner', function ($q) use ($selectedGroup) {
                $q->where('group_name', $selectedGroup);
            });
        }

        // Jika ada search, total hanya hitung mitra yang match
        if ($search) {
            $reportedQuery->whereHas('partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('mitra_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
            $approvedQuery->whereHas('partner', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('mitra_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $periodReportedMin = $reportedQuery->sum('submitted_duration_minutes');
        $periodApprovedMin = $approvedQuery->sum('approved_duration_minutes');

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
            'selectedGroup',
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

        // Log Activity
        $partner = Partner::find($partnerId);
        if ($partner) {
            \App\Services\ActivityLogger::log('report.approve', "Menyetujui periode {$startDate} s/d {$endDate} untuk mitra {$partner->full_name} dengan durasi disetujui {$approvedMinutes} menit.");

            // Check if this worker has reached the 20-hour milestone for their Rekruter's commission
            CheckRecruiterMilestone::check($partner);
        }

        // Send WhatsApp notification
        if ($partner && $partner->whatsapp_number) {
            $formattedStart = \Carbon\Carbon::parse($startDate)->format('d M Y');
            $formattedEnd = \Carbon\Carbon::parse($endDate)->format('d M Y');
            $waMessage = "Halo *{$partner->full_name}*,\n\nLaporan video Anda untuk periode *{$formattedStart} s/d {$formattedEnd}* telah selesai diverifikasi!\n\n*Total Durasi Disetujui:* {$approvedMinutes} menit.\n\nTerimakasih atas kerja kerasnya! 👍";
            app(\App\Services\WhatsAppNotificationService::class)->queueMessage($partner->whatsapp_number, $waMessage);
        }

        return redirect()->back()->with('success', 'Persetujuan periode berhasil difinalisasi dan dirilis ke mitra.');
    }

    public function approveReport(Request $request, VideoWorkReport $report)
    {
        $validated = $request->validate([
            'adjusted_minutes' => 'required|integer|min:0',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $report->update([
            'qc_status' => 'approved',
            'approved_duration_minutes' => $validated['adjusted_minutes'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verifier_notes' => $validated['admin_note'],
        ]);

        return redirect()->back()->with('success', 'Laporan berhasil disetujui dengan durasi ' . $validated['adjusted_minutes'] . ' menit.');
    }

    public function batchApprove(Request $request)
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'report_ids' => 'required|string',
            'total_approved_minutes' => 'required|integer|min:0',
        ]);

        $reportIds = explode(',', $validated['report_ids']);
        $totalApprovedMinutes = (int) $validated['total_approved_minutes'];

        $reports = VideoWorkReport::whereIn('id', $reportIds)
            ->where('qc_status', '!=', 'approved')
            ->orderBy('submission_date', 'asc')
            ->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Laporan tidak ditemukan atau sudah disetujui sebelumnya.');
        }

        $partnerId = $reports->first()->partner_id;
        $totalReported = $reports->sum('submitted_duration_minutes');

        DB::transaction(function () use ($reports, $totalApprovedMinutes, $totalReported) {
            $distributed = 0;
            
            foreach ($reports as $index => $report) {
                if ($totalReported == 0) {
                    $appDur = 0;
                } else {
                    if ($index === count($reports) - 1) {
                        $appDur = max(0, $totalApprovedMinutes - $distributed);
                    } else {
                        $appDur = min($report->submitted_duration_minutes, max(0, $totalApprovedMinutes - $distributed));
                    }
                }
                
                $distributed += $appDur;

                $report->update([
                    'qc_status' => 'approved',
                    'approved_duration_minutes' => $appDur,
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'verifier_notes' => 'Batch Approved',
                ]);
            }
        });

        $partner = Partner::find($partnerId);
        if ($partner) {
            \App\Services\ActivityLogger::log('report.batch_approve', "Menyetujui " . count($reports) . " laporan terpilih untuk mitra {$partner->full_name} dengan total durasi {$totalApprovedMinutes} menit.");
            
            CheckRecruiterMilestone::check($partner);
        }

        return redirect()->back()->with('success', count($reports) . ' laporan berhasil disetujui dengan total durasi ' . $totalApprovedMinutes . ' menit.');
    }

    public function destroy(VideoWorkReport $report)
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }
        
        $reportPartnerName = $report->partner ? $report->partner->full_name : 'Unknown';
        \App\Services\ActivityLogger::log('report.delete', "Menghapus laporan video ID {$report->id} tanggal {$report->submission_date} milik mitra {$reportPartnerName}");
        
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

        // Log Activity
        \App\Services\ActivityLogger::log('report.reject', "Menolak laporan video ID {$report->id} tanggal {$report->submission_date} milik {$report->partner->full_name} dengan alasan: {$validated['reason']}");

        // Send WhatsApp notification
        $partner = $report->partner;
        if ($partner && $partner->whatsapp_number) {
            $formattedDate = \Carbon\Carbon::parse($report->submission_date)->format('d M Y');
            $waMessage = "Halo *{$partner->full_name}*,\n\nLaporan video Anda untuk tanggal *{$formattedDate}* telah *DITOLAK* oleh tim QC.\n\n*Alasan Penolakan:* \"{$validated['reason']}\"\n\nSilakan segera login ke dashboard untuk merevisi laporan Anda:\n" . route('dashboard');
            app(\App\Services\WhatsAppNotificationService::class)->queueMessage($partner->whatsapp_number, $waMessage);
        }

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

    public function exportPdf(Request $request)
    {
        // 1. Get available periods and determine selected period
        $periods = PeriodService::getAvailablePeriods();
        
        $selectedPeriodKey = $request->input('period');
        if (!$selectedPeriodKey && !empty($periods)) {
            $selectedPeriodKey = $periods[0]['start']->format('Y-m-d') . '|' . $periods[0]['end']->format('Y-m-d');
        }

        $startDate = null;
        $endDate = null;

        if ($selectedPeriodKey && $selectedPeriodKey !== 'all') {
            $parts = explode('|', $selectedPeriodKey);
            if (count($parts) === 2) {
                $startDate = Carbon::parse($parts[0])->startOfDay();
                $endDate = Carbon::parse($parts[1])->endOfDay();
            }
        } elseif ($selectedPeriodKey === null) {
            $range = PeriodService::getPeriodRange(now());
            $startDate = $range['start'];
            $endDate = $range['end'];
        }

        // 2. Fetch partners who submitted reports in this period
        $group = $request->input('group');
        $query = Partner::whereHas('videoWorkReports', function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
        });

        if ($group) {
            $query->where('group_name', $group);
        }

        $partners = $query->get();

        foreach ($partners as $partner) {
            $reportsQuery = VideoWorkReport::where('partner_id', $partner->id)->orderBy('submission_date', 'asc');
            if ($startDate && $endDate) {
                $reportsQuery->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
            $reports = $reportsQuery->get();

            $totalReportedMinutes = $reports->sum('submitted_duration_minutes');
            
            $approval = null;
            if ($startDate && $endDate) {
                $approval = PeriodApproval::where('partner_id', $partner->id)
                    ->where('period_start_date', $startDate->format('Y-m-d'))
                    ->where('period_end_date', $endDate->format('Y-m-d'))
                    ->first();
            }

            $partner->period_reports = $reports;
            $partner->total_reported_minutes = $totalReportedMinutes;
            $partner->period_approval = $approval;
            
            // Set friendly statuses
            $partner->approval_status = $approval ? $approval->status : 'none';
        }

        $periodLabel = 'Semua Periode';
        if ($selectedPeriodKey && $selectedPeriodKey !== 'all' && !empty($periods)) {
            foreach ($periods as $p) {
                $key = $p['start']->format('Y-m-d') . '|' . $p['end']->format('Y-m-d');
                if ($key === $selectedPeriodKey) {
                    $periodLabel = $p['label'];
                    break;
                }
            }
        }

        return view('video-submissions.export-pdf', [
            'partners' => $partners,
            'startDate' => $startDate->format('d M Y'),
            'endDate' => $endDate->format('d M Y'),
            'periodLabel' => $periodLabel,
        ]);
    }

    public function revertPeriodApproval(Request $request)
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date',
        ]);

        $partnerId = $validated['partner_id'];
        $startDateObj = \Carbon\Carbon::parse($validated['period_start_date'])->startOfDay();
        $endDateObj = \Carbon\Carbon::parse($validated['period_end_date'])->startOfDay();

        $approval = PeriodApproval::where('partner_id', $partnerId)
            ->where('period_start_date', $startDateObj)
            ->where('period_end_date', $endDateObj)
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', 'Persetujuan periode tidak ditemukan.');
        }

        if ($approval->status === 'paid') {
            return redirect()->back()->with('error', 'Tidak bisa membatalkan persetujuan periode yang sudah dibayar (Lunas).');
        }

        DB::transaction(function () use ($approval, $partnerId, $startDateObj, $endDateObj) {
            $approval->delete();

            VideoWorkReport::where('partner_id', $partnerId)
                ->whereBetween('submission_date', [$startDateObj->format('Y-m-d'), $endDateObj->format('Y-m-d')])
                ->update([
                    'qc_status' => 'on_review',
                    'approved_duration_minutes' => 0,
                    'verified_by' => null,
                    'verified_at' => null,
                    'verifier_notes' => null,
                ]);
        });

        $partner = Partner::find($partnerId);
        if ($partner) {
            \App\Services\ActivityLogger::log('report.revert_approval', "Membatalkan persetujuan periode {$startDateObj->format('Y-m-d')} s/d {$endDateObj->format('Y-m-d')} untuk mitra {$partner->full_name}");
        }

        return redirect()->back()->with('success', 'Persetujuan periode berhasil dibatalkan dan status laporan harian dikembalikan ke antrean review.');
    }
}
