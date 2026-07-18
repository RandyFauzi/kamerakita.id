<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListPartnerReportHistoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $partner = Partner::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless(in_array($partner->partner_role, ['worker', 'mitra'], true), 403);

        $baseQuery = VideoWorkReport::query()
            ->with(['partner', 'verifier'])
            ->where('partner_id', $partner->id);

        $summary = [
            'total_reports' => (clone $baseQuery)->count(),
            'pending_reports' => (clone $baseQuery)->where('qc_status', 'pending')->count(),
            'on_review_reports' => (clone $baseQuery)->where('qc_status', 'on_review')->count(),
            'approved_reports' => (clone $baseQuery)->where('qc_status', 'approved')->count(),
            'rejected_reports' => (clone $baseQuery)->where('qc_status', 'rejected')->count(),
            'unpaid_minutes' => (clone $baseQuery)
                ->where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->sum('approved_duration_minutes'),
        ];

        $reportsQuery = clone $baseQuery;

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $reportsQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhereHas('partner', function ($partnerQuery) use ($search) {
                        $partnerQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('mitra_id', 'like', "%{$search}%")
                            ->orWhere('whatsapp_number', 'like', "%{$search}%");
                    });
            });
        }

        $status = $request->string('status')->toString() ?: 'all';
        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();

        if (in_array($status, ['pending', 'on_review', 'approved', 'rejected'], true)) {
            $reportsQuery->where('qc_status', $status);
        }

        if ($startDate !== '') {
            $reportsQuery->whereDate('submission_date', '>=', $startDate);
        }

        if ($endDate !== '') {
            $reportsQuery->whereDate('submission_date', '<=', $endDate);
        }

        $reports = $reportsQuery
            ->orderByDesc('submission_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('video-submissions.report-history', [
            'partner' => $partner,
            'reports' => $reports,
            'summary' => $summary,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
