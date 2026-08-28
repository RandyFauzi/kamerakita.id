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

    public function workers(Request $request, \App\Services\CalculatePartnerMetricsService $metricsService)
    {
        $user = Auth::user();
        $partner = $user->partner;

        if (!$partner || $partner->partner_role !== 'mitra') {
            abort(403, 'Unauthorized access.');
        }

        $metrics = $metricsService->getMitraMetrics($partner);

        return view('vendor.workers', compact('metrics', 'partner'));
    }

    public function storeWorker(Request $request)
    {
        $user = Auth::user();
        $partner = $user->partner;

        if (!$partner || $partner->partner_role !== 'mitra') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]*$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, atau underscore tanpa spasi.'
        ]);

        $generatedEmail = strtolower(trim($request->username)) . '@kamerakitaid.site';

        // Check if the generated email already exists (just to be safe)
        if (\App\Models\User::where('email', $generatedEmail)->exists()) {
            return redirect()->back()->withErrors(['username' => 'Username ini sudah digunakan, silakan pilih yang lain.'])->withInput();
        }

        $newUser = \App\Models\User::create([
            'name' => $request->name,
            'email' => $generatedEmail,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        $latestPartner = \App\Models\Partner::orderBy('mitra_id', 'desc')->first();
        if ($latestPartner && preg_match('/KMK-(\d+)/', $latestPartner->mitra_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $nextMitraId = 'KMK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        \App\Models\Partner::create([
            'partner_role' => 'worker',
            'mitra_id' => $nextMitraId,
            'full_name' => $newUser->name,
            'whatsapp_number' => $request->whatsapp_number,
            'email' => $generatedEmail,
            'has_headstrap' => false,
            'status' => 'active',
            'group_name' => $partner->group_name ?? 'Group A',
            'base_hourly_rate' => 50000,
            'user_id' => $newUser->id,
            'mitra_parent_id' => $partner->id,
            'recruiter_partner_id' => $partner->id,
        ]);

        return redirect()->back()->with('success', 'Worker baru berhasil didaftarkan dengan Email: ' . $generatedEmail . ' dan masuk ke tim Anda!');
    }
}
