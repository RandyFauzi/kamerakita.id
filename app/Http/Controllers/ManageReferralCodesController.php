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
            'group_name' => 'required|string|max:50',
        ], [
            'group_name.required' => 'Nama grup wajib diisi.',
        ]);

        // Auto-generate next index/number
        $nextNum = ReferralCode::count() + 1;
        $numberStr = str_pad($nextNum, 2, '0', STR_PAD_LEFT);

        // Generate standard format: KMK-[NO][4 RANDOM UPPERCASE LETTERS]
        do {
            $randomLetters = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < 4; $i++) {
                $randomLetters .= $chars[rand(0, strlen($chars) - 1)];
            }
            $code = 'KMK-' . $numberStr . $randomLetters;
        } while (ReferralCode::where('code', $code)->exists());

        ReferralCode::create([
            'code' => $code,
            'group_name' => $validated['group_name'],
        ]);

        return back()->with('success', "Kode referal baru '{$code}' berhasil dibuat!");
    }

    public function destroy(ReferralCode $referralCode)
    {
        $referralCode->delete();
        return back()->with('success', 'Kode referal berhasil dihapus.');
    }
}
