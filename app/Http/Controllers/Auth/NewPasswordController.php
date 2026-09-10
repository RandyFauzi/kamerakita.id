<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => __('We can\'t find a user with that e-mail address.')]);
        }

        // Find a valid token
        $validTokenRecord = \App\Models\PasswordRecoveryToken::where('user_id', $user->id)
            ->where('used_at', null)
            ->where('expires_at', '>', now())
            ->get()
            ->first(function ($record) use ($request) {
                return Hash::check($request->token, $record->token_hash);
            });

        if (!$validTokenRecord) {
            return back()->withErrors(['email' => __('This password reset token is invalid or has expired.')]);
        }

        // Reset the password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Invalidate token
        $validTokenRecord->update([
            'used_at' => now()
        ]);

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', __('Your password has been reset!'));
    }
}
