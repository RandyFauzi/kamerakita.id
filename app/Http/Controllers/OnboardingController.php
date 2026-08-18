<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->has_completed_onboarding) {
            return redirect()->route('dashboard'); // atau rute utama worker
        }
        
        $partner = Partner::where('user_id', $user->id)->first();
        return view('onboarding.wizard', compact('partner'));
    }

    public function save(Request $request)
    {
        $user = auth()->user();
        
        if ($user->has_completed_onboarding) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'whatsapp_number' => 'required|string|min:9|max:15',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_owner' => 'required|string',
            'tos_accepted' => 'required|accepted',
        ], [
            'tos_accepted.accepted' => 'Anda wajib menyetujui Syarat dan Ketentuan untuk melanjutkan.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'bank_name.required' => 'Pilih bank dari daftar.',
            'bank_account_number.required' => 'Nomor Rekening wajib diisi.',
            'bank_account_owner.required' => 'Nama Pemilik Rekening wajib diisi.'
        ]);

        // Simpan data ke tabel partners
        $partner = Partner::where('user_id', $user->id)->first();
        if ($partner) {
            $partner->update([
                'whatsapp_number' => $request->whatsapp_number,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_owner' => $request->bank_account_owner,
            ]);
        } else {
            // Jika untuk alasan tertentu belum ada data partner
            Partner::create([
                'user_id' => $user->id,
                'whatsapp_number' => $request->whatsapp_number,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_owner' => $request->bank_account_owner,
                // tambahkan field default lainnya jika perlu
            ]);
        }

        // Tandai user sudah onboarding
        $user->has_completed_onboarding = true;
        $user->tos_accepted_at = Carbon::now();
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Selamat datang! Terima kasih telah melengkapi data profil Anda.');
    }
}
