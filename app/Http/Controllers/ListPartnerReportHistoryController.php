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

        $partnerIds = [$partner->id];

        if ($partner->partner_role === 'mitra') {
            $workerIds = $partner->workers()
                ->pluck('id')
                ->all();

            $partnerIds = array_merge($partnerIds, $workerIds);
        }

        $baseQuery = VideoWorkReport::query()
            ->with(['partner', 'verifier'])
            ->whereIn('partner_id', $partnerIds);

        $summary = [
            'total_reports' => (clone $baseQuery)->count(),
            'pending_reports' => (clone $baseQuery)->where('qc_status', 'pending')->count(),
            'approved_reports' => (clone $baseQuery)->where('qc_status', 'approved')->count(),
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

        $qcStatus = $request->string('qc_status')->toString();
        $paymentStatus = $request->string('payment_status')->toString();

        if (in_array($qcStatus, ['pending', 'on_review', 'approved', 'rejected'], true)) {
            $reportsQuery->where('qc_status', $qcStatus);
        }

        if (in_array($paymentStatus, ['unpaid', 'paid'], true)) {
            $reportsQuery->where('payment_status', $paymentStatus);
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
        ]);
    }
}
