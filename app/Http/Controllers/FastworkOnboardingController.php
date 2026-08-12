<?php

namespace App\Http\Controllers;

use App\Models\FastworkOnboarding;
use Illuminate\Http\Request;

class FastworkOnboardingController extends Controller
{
    public function showForm()
    {
        return view('onboarding.form');
    }

    public function showRegisterForm()
    {
        return view('onboarding.form');
    }

    public function handleSubmission(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:30',
            'device_type' => 'required|string|max:100',
            'fastwork_username' => 'nullable|string|max:100',
            'has_headstrap' => 'nullable',
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi untuk koordinasi QC.',
            'device_type.required' => 'Tipe perangkat Apple Anda wajib dipilih.',
        ]);

        $validated['has_headstrap'] = $request->has('has_headstrap');

        FastworkOnboarding::create($validated);

        // Return JSON if requested via AJAX
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        // Redirect to official WhatsApp coordination group
        return redirect()->away('https://chat.whatsapp.com/EWzTpticIllFogSNYx0TTt');
    }
}
