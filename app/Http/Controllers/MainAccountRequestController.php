<?php

namespace App\Http\Controllers;

use App\Models\MainAccountRequest;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MainAccountRequestController extends Controller
{
    /**
     * Display the application form and benefits for Main Account.
     * Redirects unauthenticated visitors to registration page ("Daftar").
     */
    public function create(Request $request)
    {
        if (!Auth::check()) {
            session(['url.intended' => route('main-account.request')]);

            return redirect()->route('register')->with(
                'status',
                'Silakan daftar akun (atau klik "Sudah terdaftar?" untuk masuk) terlebih dahulu untuk mengajukan Akun Induk.'
            );
        }

        $user = Auth::user();
        $partner = $user->partner ?? Partner::where('email', $user->email)->first();

        $pendingRequest = MainAccountRequest::where('user_id', $user->id)
            ->pending()
            ->latest()
            ->first();

        return view('main_account.request', compact('user', 'partner', 'pendingRequest'));
    }

    /**
     * Submit an application for Main Account.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            session(['url.intended' => route('main-account.request')]);

            return redirect()->route('register')->with(
                'status',
                'Silakan daftar akun (atau klik "Sudah terdaftar?" untuk masuk) terlebih dahulu untuk mengajukan Akun Induk.'
            );
        }

        $request->validate([
            'password' => ['required', 'string'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_owner' => ['nullable', 'string', 'max:100'],
            'estimated_accounts' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withInput($request->except('password'))->withErrors([
                'password' => 'Kata sandi yang Anda masukkan tidak sesuai.'
            ]);
        }

        $partner = $user->partner ?? Partner::where('email', $user->email)->first();

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
            return back()->withInput($request->except('password'))->with(
                'info',
                'Pengajuan Akun Induk Anda saat ini sudah ada di antrean verifikasi admin. Mohon menunggu proses review.'
            );
        }

        // Update phone & bank details on partner profile so user edits are persisted
        $partnerUpdates = [];
        if ($request->filled('phone_number')) {
            $partnerUpdates['whatsapp_number'] = $request->phone_number;
        }
        if ($request->filled('bank_name')) {
            $partnerUpdates['bank_name'] = $request->bank_name;
        }
        if ($request->filled('bank_account_number')) {
            $partnerUpdates['bank_account_number'] = $request->bank_account_number;
        }
        if ($request->filled('bank_account_owner')) {
            $partnerUpdates['bank_account_owner'] = $request->bank_account_owner;
        }

        if (!empty($partnerUpdates)) {
            $partner->update($partnerUpdates);
        }

        MainAccountRequest::create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'email' => $user->email,
            'phone_number' => $request->input('phone_number') ?: $partner->whatsapp_number,
            'bank_name' => $request->input('bank_name') ?: $partner->bank_name,
            'bank_account_number' => $request->input('bank_account_number') ?: ($partner->bank_account_number ?: $partner->account_number),
            'bank_account_owner' => $request->input('bank_account_owner') ?: ($partner->bank_account_owner ?: $partner->account_owner_name),
            'estimated_accounts' => $request->estimated_accounts,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return back()->with(
            'success',
            'Pengajuan Akun Induk Anda berhasil dikirim! Tim Admin akan memvalidasi data Anda dalam waktu 1x24 jam.'
        );
    }
}
