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

        if (!$partner || !in_array(strtolower(trim($partner->partner_role)), ['worker', 'mitra', 'rekruter'])) {
            return redirect()->route('dashboard')->with('error', 'Hanya akun Mitra/Worker/Rekruter yang dapat mengakses halaman ini.');
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
                'is_payroll' => false,
            ];
        }

        // New Payroll Payments
        $atlasWorker = \App\Models\AtlasWorker::where('user_id', Auth::id())->first();
        if ($atlasWorker) {
            $payrolls = \App\Models\Payroll::where('atlas_worker_id', $atlasWorker->id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($payrolls as $pr) {
                $payments[] = [
                    'paid_at' => $pr->paid_at ?? $pr->created_at,
                    'proof_url' => null,
                    'reports' => collect([]), // No old reports
                    'total_minutes' => $pr->total_approved_minutes,
                    'total_amount' => $pr->amount_rupiah,
                    'has_custom_rate' => false,
                    'is_payroll' => true,
                    'payroll_period' => $pr->period_start->format('d M') . ' - ' . $pr->period_end->format('d M Y'),
                    'payroll_status' => $pr->status,
                ];
            }
        }

        // Sort by paid_at descending
        usort($payments, function($a, $b) {
            return $b['paid_at'] <=> $a['paid_at'];
        });

        return view('video-submissions.payment-history', compact('payments', 'partner'));
    }
}
