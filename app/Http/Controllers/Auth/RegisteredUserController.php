<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request with professional grade security.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]*$/'], // Enforce letters only for names
            'username' => ['required', 'string', 'lowercase', 'max:50', 'alpha_dash', function ($attribute, $value, $fail) {
                if (User::where('email', $value . '@kamerakitaid.site')->exists()) {
                    $fail('Username ini sudah digunakan.');
                }
            }],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
            ],
            'activation_code' => ['required', 'string', 'exists:activation_codes,code'],
            // Referral code is optional; if provided it must match an existing Mitra/Rekruter referral_code
            'referral_code' => ['nullable', 'string', 'exists:partners,referral_code'],
        ], [
            'name.regex' => 'Nama hanya boleh mengandung huruf dan spasi.',
            'username.alpha_dash' => 'Username hanya boleh mengandung huruf, angka, strip, atau garis bawah.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
            'activation_code.required' => 'Kode aktivasi wajib diisi.',
            'activation_code.exists' => 'Kode aktivasi tidak valid atau tidak terdaftar di sistem.',
            'referral_code.exists' => 'Kode referral tidak valid. Pastikan kode yang Anda masukkan benar.',
        ]);

        $actCode = \App\Models\ActivationCode::where('code', $request->activation_code)->first();
        $groupName = $actCode ? $actCode->group_name : 'Group A';

        // Look up the recruiter (Mitra/Rekruter) by their referral code
        $recruiterPartner = null;
        if ($request->filled('referral_code')) {
            $recruiterPartner = Partner::where('referral_code', $request->referral_code)->first();
        }

        // Construct the internal email address
        $internalEmail = $request->username . '@kamerakitaid.site';

        $user = User::create([
            'name' => $request->name,
            'email' => $internalEmail,
            'password' => Hash::make($request->password),
            'role' => 'worker', // Default role
        ]);

        // Auto-generate next KMK-XXX code
        $latestPartner = \App\Models\Partner::orderBy('mitra_id', 'desc')->first();
        if ($latestPartner && preg_match('/KMK-(\d+)/', $latestPartner->mitra_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $nextMitraId = 'KMK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Auto-create a Worker partner profile for the new user so they can use dashboard immediately
        \App\Models\Partner::create([
            'partner_role' => 'worker',
            'mitra_id' => $nextMitraId,
            'full_name' => $user->name,
            'whatsapp_number' => '08' . rand(100000000, 999999999),
            'email' => $internalEmail,
            'has_headstrap' => false,
            'status' => 'active',
            'group_name' => $groupName,
            'base_hourly_rate' => 50000, // default rate in IDR
            'user_id' => $user->id,
            // Link to recruiter if a valid referral code was provided
            'recruiter_partner_id' => $recruiterPartner ? $recruiterPartner->id : null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
