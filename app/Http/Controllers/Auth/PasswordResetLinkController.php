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
            'email' => ['required', 'string'],
        ]);

        $email = strtolower(trim($request->email));
        if (!str_contains($email, '@')) {
            $email .= '@kamerakitaid.site';
        }

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

        $adminWa = '0895366583095';
        $adminWaGateway = \App\Helpers\PhoneHelper::formatForGateway($adminWa);
        $waMessage = "Halo Admin KameraKita, saya sudah mengajukan permohonan reset password untuk akun email: {$email}. Mohon bantuannya untuk disetujui. Terima kasih!";
        $adminWaLink = "https://wa.me/{$adminWaGateway}?text=" . rawurlencode($waMessage);

        return back()->with([
            'status' => __('Permintaan pemulihan akun berhasil diajukan. Silakan hubungi Admin melalui WhatsApp untuk verifikasi.'),
            'recovery_submitted' => true,
            'submitted_email' => $email,
            'admin_wa' => $adminWa,
            'admin_wa_link' => $adminWaLink,
        ]);
    }
}
