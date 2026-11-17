<?php

namespace App\Http\Controllers;

use App\Models\MainAccountRequest;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MainAccountRequestController extends Controller
{
    /**
     * Display the application form and benefits for Main Account.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $partner = $user?->partner;

        $pendingRequest = null;
        if ($user) {
            $pendingRequest = MainAccountRequest::where('user_id', $user->id)
                ->pending()
                ->latest()
                ->first();
        }

        return view('main_account.request', compact('user', 'partner', 'pendingRequest'));
    }

    /**
     * Submit an application for Main Account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_owner' => ['nullable', 'string', 'max:100'],
            'estimated_accounts' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withInput($request->except('password'))->withErrors([
                'password' => 'Email atau kata sandi yang Anda masukkan tidak sesuai.'
            ]);
        }

        $partner = $user->partner;

        if (!$partner) {
            return back()->withInput($request->except('password'))->withErrors([
                'email' => 'Akun belum terdaftar sebagai Partner/Worker di sistem.'
            ]);
        }

        if ($partner->isMainAccount()) {
            return back()->withInput($request->except('password'))->withErrors([
                'email' => 'Akun Anda sudah resmi berstatus Main Account (Akun Induk).'
            ]);
        }

        if ($partner->isMitra()) {
            return back()->withInput($request->except('password'))->withErrors([
                'email' => 'Akun Anda sudah terdaftar sebagai Mitra.'
            ]);
        }

        $alreadyPending = MainAccountRequest::where('partner_id', $partner->id)
            ->pending()
            ->first();

        if ($alreadyPending) {
            return back()->withInput($request->except('password'))->with('info', 'Pengajuan Akun Induk Anda saat ini sudah ada di antrean verifikasi admin. Mohon menunggu proses review.');
        }

        // Auto-fill phone/bank into partner profile if empty
        $partnerUpdates = [];
        if ($request->filled('phone_number') && empty($partner->whatsapp_number)) {
            $partnerUpdates['whatsapp_number'] = $request->phone_number;
        }
        if ($request->filled('bank_name') && empty($partner->bank_name)) {
            $partnerUpdates['bank_name'] = $request->bank_name;
        }
        if ($request->filled('bank_account_number') && empty($partner->bank_account_number)) {
            $partnerUpdates['bank_account_number'] = $request->bank_account_number;
        }
        if ($request->filled('bank_account_owner') && empty($partner->bank_account_owner)) {
            $partnerUpdates['bank_account_owner'] = $request->bank_account_owner;
        }

        if (!empty($partnerUpdates)) {
            $partner->update($partnerUpdates);
        }

        MainAccountRequest::create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'email' => $user->email,
            'phone_number' => $partner->whatsapp_number ?? $request->phone_number,
            'bank_name' => $partner->bank_name ?? $request->bank_name,
            'bank_account_number' => $partner->bank_account_number ?? $request->bank_account_number,
            'bank_account_owner' => $partner->bank_account_owner ?? $request->bank_account_owner,
            'estimated_accounts' => $request->estimated_accounts,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pengajuan Akun Induk Anda berhasil dikirim! Tim Admin akan memvalidasi data Anda dalam waktu 1x24 jam.');
    }
}
