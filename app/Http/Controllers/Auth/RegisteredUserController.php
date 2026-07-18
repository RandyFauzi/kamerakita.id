<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:'.User::class], // DNS lookup verification
            'password' => [
                'required', 
                'confirmed', 
                Rules\Password::min(8)
            ],
        ], [
            'name.regex' => 'Nama hanya boleh mengandung huruf dan spasi.',
            'email.email' => 'Format email tidak valid atau domain tidak terdaftar.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'verifikator', // Default role
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
            'email' => $user->email,
            'status' => 'active',
            'base_hourly_rate' => 54000, // default rate in IDR
            'user_id' => $user->id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
