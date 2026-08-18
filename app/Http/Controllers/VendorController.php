<?php

namespace App\Http\Controllers;

use App\Models\VideoWorkReport;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    public function qcTracker(Request $request)
    {
        $user = Auth::user();
        $partner = $user->partner;

        if (!$partner || $partner->partner_role !== 'mitra') {
            abort(403, 'Unauthorized access.');
        }

        $reports = VideoWorkReport::with('partner')
            ->whereHas('partner', function ($query) use ($partner) {
                $query->where('mitra_parent_id', $partner->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('vendor.qc_tracker', compact('reports'));
    }

    public function payments(Request $request)
    {
        $user = Auth::user();
        $partner = $user->partner;

        if (!$partner || $partner->partner_role !== 'mitra') {
            abort(403, 'Unauthorized access.');
        }

        $approvedReports = VideoWorkReport::with('partner')
            ->whereHas('partner', function ($query) use ($partner) {
                $query->where('mitra_parent_id', $partner->id);
            })
            ->where('qc_status', 'approved')
            ->orderBy('submission_date', 'desc')
            ->get();

        $vendorRatePerHour = 65000;
        $vendorRatePerMinute = $vendorRatePerHour / 60;

        $totalApprovedMinutes = $approvedReports->sum('approved_duration_minutes');
        $totalEstimatedRevenue = $totalApprovedMinutes * $vendorRatePerMinute;

        $paidReports = $approvedReports->where('payment_status', 'paid');
        $unpaidReports = $approvedReports->where('payment_status', 'unpaid');

        $totalPaid = $paidReports->sum('approved_duration_minutes') * $vendorRatePerMinute;
        $totalUnpaid = $unpaidReports->sum('approved_duration_minutes') * $vendorRatePerMinute;

        return view('vendor.payments', compact(
            'approvedReports',
            'totalApprovedMinutes',
            'totalEstimatedRevenue',
            'totalPaid',
            'totalUnpaid'
        ));
    }
}
