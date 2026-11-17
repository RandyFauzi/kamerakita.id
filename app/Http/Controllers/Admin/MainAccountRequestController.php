<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneHelper;
use App\Http\Controllers\Controller;
use App\Models\MainAccountRequest;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainAccountRequestController extends Controller
{
    /**
     * Display a listing of Main Account requests.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $search = $request->input('search');

        $query = MainAccountRequest::with(['user', 'partner', 'approvedBy']);

        if ($status && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('partner', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('mitra_id', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest('created_at')->paginate(20)->withQueryString();

        $counts = [
            'all' => MainAccountRequest::count(),
            'pending' => MainAccountRequest::pending()->count(),
            'approved' => MainAccountRequest::approved()->count(),
            'rejected' => MainAccountRequest::rejected()->count(),
        ];

        return view('admin.main_account_requests.index', compact('requests', 'counts', 'status', 'search'));
    }

    /**
     * Approve a pending Main Account request and switch partner_role to main_account.
     */
    public function approve(MainAccountRequest $mainAccountRequest, Request $request)
    {
        if ($mainAccountRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah pernah diproses sebelumnya.');
        }

        DB::transaction(function () use ($mainAccountRequest, $request) {
            $partner = $mainAccountRequest->partner;
            $partner->makeMainAccount();

            $mainAccountRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->input('admin_notes'),
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
        });

        $user = $mainAccountRequest->user;
        $partner = $mainAccountRequest->partner;
        $phone = $mainAccountRequest->phone_number ?: $partner?->whatsapp_number;
        $formattedPhone = $phone ? PhoneHelper::formatForGateway($phone) : null;

        $dashboardUrl = route('dashboard');
        $waMessage = "Halo *{$user->name}*! 🎉\n\nSelamat, pengajuan Anda untuk menjadi *Main Account (Akun Induk)* di KameraKita telah *DISETUJUI* oleh Admin.\n\nSekarang Anda dapat:\n✅ Menghubungkan & memantau seluruh sub-akun di 1 dashboard\n✅ Kotak Masuk OTP terpadu untuk semua email sub-akun\n✅ Rate tetap penuh Rp60.000/jam tanpa potongan\n✅ Pencairan penghasilan terpusat ke satu rekening bank\n\nSilakan akses dashboard Anda:\n{$dashboardUrl}\n\nTerima kasih atas kerja sama dan kontribusi Anda! 🙏";

        $manualWaUrl = $formattedPhone
            ? "https://wa.me/{$formattedPhone}?text=" . rawurlencode($waMessage)
            : null;

        if ($request->boolean('send_wa_now') && $phone) {
            try {
                app(WhatsAppNotificationService::class)->sendMessage($phone, $waMessage);
            } catch (\Throwable $e) {
                Log::warning("WhatsApp send failed for MainAccountRequest #{$mainAccountRequest->id}: " . $e->getMessage());
            }
        }

        return back()->with([
            'success' => "Pengajuan berhasil disetujui! Akun {$user->name} ({$user->email}) kini resmi menjadi Main Account.",
            'target_name' => $user->name,
            'target_email' => $user->email,
            'target_phone' => $phone,
            'manual_wa_url' => $manualWaUrl,
        ]);
    }

    /**
     * Reject a pending Main Account request.
     */
    public function reject(MainAccountRequest $mainAccountRequest, Request $request)
    {
        if ($mainAccountRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah pernah diproses sebelumnya.');
        }

        $mainAccountRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes', 'Data belum memenuhi kriteria aktivasi Akun Induk.'),
            'rejected_at' => now(),
        ]);

        return back()->with('info', "Pengajuan Akun Induk dari {$mainAccountRequest->user->name} telah ditolak.");
    }
}
