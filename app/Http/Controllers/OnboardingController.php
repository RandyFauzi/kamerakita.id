<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $countryCode = $request->input('country_code', 'ID');
        $validCountries = array_keys(config('countries'));

        if (!in_array($countryCode, $validCountries)) {
            return back()->withErrors(['country_code' => 'Negara tidak valid.']);
        }

        $rules = [
            'country_code' => 'required|string|size:2',
            'whatsapp_number' => 'required|string|min:6|max:20',
            'tos_accepted' => 'required|accepted',
        ];

        $messages = [
            'tos_accepted.accepted' => 'Anda wajib menyetujui Syarat dan Ketentuan untuk melanjutkan.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'bank_name.required' => 'Pilih bank dari daftar.',
            'bank_account_number.required' => 'Nomor Rekening wajib diisi.',
            'bank_account_owner.required' => 'Nama Pemilik Rekening wajib diisi.',
            'airtm_username.required' => 'Username AirTM wajib diisi untuk pembayaran internasional.'
        ];

        if ($countryCode === 'ID') {
            $rules['payment_method'] = 'required|in:bank_transfer';
            $rules['bank_name'] = 'required|string';
            $rules['bank_account_number'] = 'required|string';
            $rules['bank_account_owner'] = 'required|string';
        } else {
            $rules['payment_method'] = 'required|in:airtm';
            $rules['airtm_username'] = 'required|string';
        }

        $request->validate($rules, $messages);

        $normalizedPhone = \App\Helpers\PhoneHelper::normalize($request->whatsapp_number, $countryCode);

        $partnerData = [
            'whatsapp_number' => $normalizedPhone,
            'country_code' => $countryCode,
            'payment_method' => $request->payment_method,
        ];

        if ($countryCode === 'ID') {
            $partnerData['bank_name'] = $request->bank_name;
            $partnerData['bank_account_number'] = $request->bank_account_number;
            $partnerData['bank_account_owner'] = $request->bank_account_owner;
            $partnerData['airtm_username'] = null;
        } else {
            $partnerData['airtm_username'] = $request->airtm_username;
            $partnerData['bank_name'] = null;
            $partnerData['bank_account_number'] = null;
            $partnerData['bank_account_owner'] = null;
        }

        DB::beginTransaction();
        try {
            // Simpan data ke tabel partners
            $partner = Partner::where('user_id', $user->id)->first();
            if ($partner) {
                $partner->update($partnerData);
            } else {
                $partnerData['user_id'] = $user->id;
                Partner::create($partnerData);
            }

            // Tandai user sudah onboarding
            $user->has_completed_onboarding = true;
            $user->tos_accepted_at = Carbon::now();
            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan profil, silakan coba lagi.']);
        }

        return redirect()->route('dashboard')->with('success', 'Selamat datang! Terima kasih telah melengkapi data profil Anda.');
    }
}
