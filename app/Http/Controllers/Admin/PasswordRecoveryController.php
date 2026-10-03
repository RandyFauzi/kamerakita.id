<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordRecoveryRequest;
use App\Models\PasswordRecoveryToken;
use App\Services\WhatsAppNotificationService;
use App\Helpers\PhoneHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PasswordRecoveryController extends Controller
{
    public function index()
    {
        $requests = PasswordRecoveryRequest::with(['user.partner'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.password_recoveries.index', compact('requests'));
    }

    public function approve(PasswordRecoveryRequest $recoveryRequest, Request $request)
    {
        if ($recoveryRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah pernah diproses.');
        }

        if (!$recoveryRequest->user_id) {
            return back()->with('error', 'Tidak dapat menyetujui permintaan untuk user yang tidak terdaftar.');
        }

        // Generate cryptographically secure token
        $plainToken = Str::random(64);

        // Store hash in DB (active for 60 minutes)
        PasswordRecoveryToken::create([
            'recovery_request_id' => $recoveryRequest->id,
            'user_id' => $recoveryRequest->user_id,
            'token_hash' => Hash::make($plainToken),
            'expires_at' => now()->addMinutes(60),
        ]);

        $recoveryRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'approved_ip' => $request->ip(),
        ]);

        // Generate full reset URL
        $resetUrl = route('password.reset', [
            'token' => $plainToken,
            'email' => $recoveryRequest->identifier
        ]);

        // Cache the active reset link for 60 minutes so admin can copy or view it
        Cache::put("pwd_reset_link_{$recoveryRequest->id}", $resetUrl, now()->addMinutes(60));

        // Get Partner phone number if available
        $user = $recoveryRequest->user;
        $partner = $user?->partner;
        $phone = $partner?->whatsapp_number;
        $formattedPhone = $phone ? PhoneHelper::formatForGateway($phone) : null;

        $waMessage = "Halo *{$user->name}*,\n\nPermintaan reset kata sandi akun KameraKita Anda telah *DISETUJUI* oleh Admin.\n\nSilakan klik tautan di bawah ini untuk membuat kata sandi baru:\n{$resetUrl}\n\n_Catatan: Tautan ini hanya berlaku selama 60 menit dan hanya untuk 1 kali penggunaan._\n\nJika bukan Anda yang meminta, segera hubungi Admin.\nTerima kasih! 🙏";

        $manualWaUrl = $formattedPhone 
            ? "https://wa.me/{$formattedPhone}?text=" . rawurlencode($waMessage)
            : null;

        // Auto-send via WhatsApp gateway if requested
        $waSentSuccess = false;
        if ($request->boolean('send_wa_now') && $phone) {
            try {
                $response = app(WhatsAppNotificationService::class)->sendMessage($phone, $waMessage);
                if (($response['status'] ?? '') === 'success') {
                    $waSentSuccess = true;
                }
            } catch (\Throwable $waErr) {
                Log::warning("WhatsApp send failed for recovery #{$recoveryRequest->id}: " . $waErr->getMessage());
            }
        }

        // Send Email as silent background fallback
        try {
            Mail::send('emails.password-reset', [
                'token' => $plainToken,
                'email' => $recoveryRequest->identifier,
                'user' => $recoveryRequest->user
            ], function ($message) use ($recoveryRequest) {
                $message->to($recoveryRequest->identifier)
                    ->subject('Permintaan Pemulihan Akun Anda Telah Disetujui');
            });
        } catch (\Throwable $mailErr) {
            Log::info("Reset password email delivery skipped/failed: " . $mailErr->getMessage());
        }

        return back()->with([
            'success' => $waSentSuccess 
                ? 'Permintaan disetujui & link reset otomatis terkirim ke WhatsApp Mitra!' 
                : 'Permintaan berhasil disetujui! Link reset password telah dibuat.',
            'generated_reset_url' => $resetUrl,
            'target_name' => $user->name,
            'target_email' => $recoveryRequest->identifier,
            'target_phone' => $phone,
            'manual_wa_url' => $manualWaUrl,
            'recovery_request_id' => $recoveryRequest->id,
            'wa_sent' => $waSentSuccess,
        ]);
    }

    public function sendWhatsApp(PasswordRecoveryRequest $recoveryRequest, Request $request)
    {
        $resetUrl = Cache::get("pwd_reset_link_{$recoveryRequest->id}");
        if (!$resetUrl) {
            return back()->with('error', 'Link reset password untuk permintaan ini sudah kedaluwarsa atau belum dibuat. Silakan ajukan persetujuan ulang jika perlu.');
        }

        $user = $recoveryRequest->user;
        $partner = $user?->partner;
        $phone = $partner?->whatsapp_number;

        if (!$phone) {
            return back()->with('error', 'Mitra ini tidak memiliki nomor WhatsApp yang terdaftar di profilnya.');
        }

        $waMessage = "Halo *{$user->name}*,\n\nPermintaan reset kata sandi akun KameraKita Anda telah *DISETUJUI* oleh Admin.\n\nSilakan klik tautan di bawah ini untuk membuat kata sandi baru:\n{$resetUrl}\n\n_Catatan: Tautan ini hanya berlaku selama 60 menit dan hanya untuk 1 kali penggunaan._\n\nJika bukan Anda yang meminta, segera hubungi Admin.\nTerima kasih! 🙏";

        try {
            $response = app(WhatsAppNotificationService::class)->sendMessage($phone, $waMessage);
            if (($response['status'] ?? '') === 'success') {
                return back()->with('success', "Link reset berhasil dikirimkan via WhatsApp ke nomor {$phone}!");
            }
            return back()->with('error', 'Gagal mengirim pesan WhatsApp: ' . ($response['message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem pengiriman WhatsApp: ' . $e->getMessage());
        }
    }

    public function reject(PasswordRecoveryRequest $recoveryRequest, Request $request)
    {
        if ($recoveryRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah pernah diproses.');
        }

        $recoveryRequest->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
        ]);

        Cache::forget("pwd_reset_link_{$recoveryRequest->id}");

        return back()->with('success', 'Permintaan berhasil ditolak.');
    }

    public function block(PasswordRecoveryRequest $recoveryRequest, Request $request)
    {
        if ($recoveryRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah pernah diproses.');
        }

        $recoveryRequest->update([
            'status' => 'blocked',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
        ]);

        Cache::forget("pwd_reset_link_{$recoveryRequest->id}");

        return back()->with('success', 'Permintaan berhasil ditolak dan diblokir.');
    }
}
