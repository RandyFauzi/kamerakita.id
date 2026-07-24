<?php

namespace App\Http\Controllers;

use App\Models\ReferralCode;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManageReferralCodesController extends Controller
{
    public function index()
    {
        $codes = ReferralCode::latest()->get();

        // Calculate count of partners in each group
        $groupCounts = Partner::select('group_name', DB::raw('count(*) as count'))
            ->groupBy('group_name')
            ->pluck('count', 'group_name');

        return view('referral-codes.index', compact('codes', 'groupCounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:referral_codes,code|max:50|regex:/^[A-Z0-9\-_]+$/i',
            'group_name' => 'required|string|max:50',
        ], [
            'code.required' => 'Kode referal wajib diisi.',
            'code.unique' => 'Kode referal ini sudah terdaftar.',
            'code.regex' => 'Kode hanya boleh berupa huruf, angka, strip (-), dan garis bawah (_).',
            'group_name.required' => 'Nama grup wajib diisi.',
        ]);

        ReferralCode::create([
            'code' => strtoupper($validated['code']),
            'group_name' => $validated['group_name'],
        ]);

        return back()->with('success', 'Kode referal baru berhasil dibuat!');
    }

    public function destroy(ReferralCode $referralCode)
    {
        $referralCode->delete();
        return back()->with('success', 'Kode referal berhasil dihapus.');
    }
}
