<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\RecruiterCommission;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RekruterController extends Controller
{
    /**
     * Display the list of all Rekruter partners and their commission summaries.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $rekruterList = Partner::where('partner_role', Partner::ROLE_REKRUTER)
            ->withCount('recruitedWorkers')
            ->with(['recruiterCommissions'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('mitra_id', 'like', "%{$search}%");
            }))
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        return view('rekruter.index', compact('rekruterList', 'search'));
    }

    /**
     * Display detail of a single Rekruter: their recruited workers and commissions.
     */
    public function show(Partner $rekruter): View
    {
        abort_if($rekruter->partner_role !== Partner::ROLE_REKRUTER, 404);

        $rekruter->load(['recruitedWorkers', 'recruiterCommissions.worker']);

        return view('rekruter.show', compact('rekruter'));
    }

    /**
     * Mark a pending commission as paid.
     */
    public function markCommissionPaid(RecruiterCommission $commission): RedirectResponse
    {
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($commission->status === 'paid') {
            return redirect()->back()->with('error', 'Komisi ini sudah ditandai sebagai lunas sebelumnya.');
        }

        $commission->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $recruiterName = $commission->recruiter?->full_name ?? 'N/A';
        $workerName = $commission->worker?->full_name ?? 'N/A';
        ActivityLogger::log('rekruter.commission_paid', "Melunasi komisi Rekruter {$recruiterName} untuk worker {$workerName} (Rp " . number_format($commission->commission_amount, 0, ',', '.') . ")");

        return redirect()->back()->with('success', "Komisi untuk Rekruter {$recruiterName} berhasil ditandai lunas.");
    }
}
