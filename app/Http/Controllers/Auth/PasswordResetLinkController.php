<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;
        $user = \App\Models\User::where('email', $email)->first();

        // Check if there's already a pending request to prevent spam
        $recentRequest = \App\Models\PasswordRecoveryRequest::where('identifier', $email)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->first();

        if (!$recentRequest) {
            \App\Models\PasswordRecoveryRequest::create([
                'user_id' => $user?->id,
                'identifier' => $email,
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
                'request_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Optional: send notification to admin here
        }

        // Always return the exact same generic response to prevent enumeration
        return back()->with('status', __('Jika akun tersebut terdaftar, kami akan memproses permintaan pemulihan akun Anda segera.'));
    }
}
