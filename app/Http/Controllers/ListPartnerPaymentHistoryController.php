<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\Auth;

class ListPartnerPaymentHistoryController extends Controller
{
    private const DEFAULT_HOURLY_RATE_IDR = 54000;

    /**
     * Display the payment (payout) history for the authenticated Worker/Mitra.
     */
    public function __invoke()
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner || !in_array($partner->partner_role, ['worker', 'mitra'], true)) {
            return redirect()->route('dashboard')->with('error', 'Hanya akun Mitra/Worker yang dapat mengakses halaman ini.');
        }

        $paidReports = VideoWorkReport::where('partner_id', $partner->id)
            ->where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->orderBy('paid_at', 'desc')
            ->get();

        $grouped = $paidReports->groupBy(function ($item) {
            $paidAt = $item->paid_at instanceof \Carbon\Carbon ? $item->paid_at : \Carbon\Carbon::parse($item->paid_at);
            return $paidAt->format('Y-m-d H:i:s') . '_' . $item->payment_reference_proof_path;
        });

        $payments = [];
        foreach ($grouped as $key => $reports) {
            $first = $reports->first();
            $totalMinutes = $reports->sum('approved_duration_minutes');
            $hours = $totalMinutes / 60;
            $rate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            
            $totalAmount = 0;
            $hasCustomRate = false;
            foreach ($reports as $r) {
                $rRate = $r->rate_applied ?: $rate;
                if ($rRate != $rate) {
                    $hasCustomRate = true;
                }
                $totalAmount += ($r->approved_duration_minutes / 60) * $rRate;
            }
            $totalAmount = round($totalAmount);

            $payments[] = [
                'paid_at' => $first->paid_at,
                'proof_url' => $first->payment_proof_url,
                'reports' => $reports->sortByDesc('submission_date'),
                'total_minutes' => $totalMinutes,
                'total_amount' => $totalAmount,
                'has_custom_rate' => $hasCustomRate,
            ];
        }

        return view('video-submissions.payment-history', compact('payments', 'partner'));
    }
}
