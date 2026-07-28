<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManageActivationCodesController extends Controller
{
    public function index()
    {
        // Self-healing database check for old activation codes format
        $hasOldA = ActivationCode::where('code', 'KMK-GROUP-A')->exists();
        $hasOldB = ActivationCode::where('code', 'KMK-GROUP-B')->exists();
        if ($hasOldA || $hasOldB) {
            ActivationCode::where('code', 'KMK-GROUP-A')->update(['code' => 'KMK-01ASQW']);
            ActivationCode::where('code', 'KMK-GROUP-B')->update(['code' => 'KMK-02SADN']);
        }

        $codes = ActivationCode::latest()->get();

        // Calculate count of partners in each group
        $groupCounts = Partner::select('group_name', DB::raw('count(*) as count'))
            ->groupBy('group_name')
            ->pluck('count', 'group_name');

        return view('activation-codes.index', compact('codes', 'groupCounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|unique:activation_codes,group_name|max:50',
        ], [
            'group_name.required' => 'Nama grup wajib diisi.',
            'group_name.unique' => 'Grup ini sudah memiliki kode aktivasi. 1 grup hanya boleh memiliki 1 kode aktivasi.',
        ]);

        // Auto-generate next index/number
        $nextNum = ActivationCode::count() + 1;
        $numberStr = str_pad($nextNum, 2, '0', STR_PAD_LEFT);

        // Generate standard format: KMK-[NO][4 RANDOM UPPERCASE LETTERS]
        do {
            $randomLetters = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < 4; $i++) {
                $randomLetters .= $chars[rand(0, strlen($chars) - 1)];
            }
            $code = 'KMK-' . $numberStr . $randomLetters;
        } while (ActivationCode::where('code', $code)->exists());

        ActivationCode::create([
            'code' => $code,
            'group_name' => $validated['group_name'],
        ]);

        return back()->with('success', "Kode aktivasi baru '{$code}' berhasil dibuat!");
    }

    public function destroy(ActivationCode $activationCode)
    {
        $activationCode->delete();
        return back()->with('success', 'Kode aktivasi berhasil dihapus.');
    }
}
