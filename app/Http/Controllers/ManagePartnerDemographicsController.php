<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ManagePartnerDemographicsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');

        $summaryRow = Partner::query()
            ->selectRaw('COUNT(*) as total_users')
            ->selectRaw("SUM(CASE WHEN partner_role = 'worker' THEN 1 ELSE 0 END) as total_workers")
            ->selectRaw("SUM(CASE WHEN partner_role = 'mitra' THEN 1 ELSE 0 END) as total_mitra")
            ->selectRaw("SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as total_suspended")
            ->first();

        $summary = [
            'total_users' => (int) $summaryRow->total_users,
            'total_workers' => (int) $summaryRow->total_workers,
            'total_mitra' => (int) $summaryRow->total_mitra,
            'total_suspended' => (int) $summaryRow->total_suspended,
        ];

        $partners = Partner::query()
            ->with(['mitraParent', 'user'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('mitra_id', 'like', "%{$search}%")
                        ->orWhere('whatsapp_number', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->where('partner_role', $role);
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('mitra_id', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('partners.index', compact('partners', 'search', 'role', 'status', 'summary'));
    }

    public function create()
    {
        // Get potential Mitra (Coordinators) for parent selection
        $mitraList = Partner::where('partner_role', 'mitra')->get();

        // Auto-generate next KMK-XXX code
        $latestPartner = Partner::orderBy('mitra_id', 'desc')->first();
        if ($latestPartner && preg_match('/KMK-(\d+)/', $latestPartner->mitra_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $nextMitraId = 'KMK-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('partners.create', compact('mitraList', 'nextMitraId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_role' => 'required|in:worker,mitra',
            'mitra_parent_id' => 'nullable|exists:partners,id',
            'mitra_id' => 'required|string|unique:partners,mitra_id',
            'nik' => 'nullable|string|max:30|unique:partners,nik',
            'full_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'email' => ['required', 'email', 'max:100', 'unique:partners,email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'full_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_owner' => 'nullable|string|max:255',
            'smartphone_type' => 'nullable|string|max:100',
            'has_headstrap' => 'required|boolean',
            'status' => 'required|in:active,suspended',
            'base_hourly_rate' => 'required|numeric|min:0',
        ]);

        // Keep fallback support for older migration fields
        $validated['account_number'] = $validated['bank_account_number'] ?? null;
        $validated['account_owner_name'] = $validated['bank_account_owner'] ?? null;

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'verifikator',
                'email_verified_at' => now(),
            ]);

            unset($validated['password']);

            Partner::create([
                ...$validated,
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('partners.index')->with('success', 'Mitra/Worker berhasil didaftarkan dan akun login sudah dibuat!');
    }

    public function edit(Partner $partner)
    {
        $partner->load('user');

        $mitraList = Partner::where('partner_role', 'mitra')
            ->where('id', '!=', $partner->id)
            ->get();

        return view('partners.edit', compact('partner', 'mitraList'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'partner_role' => 'required|in:worker,mitra',
            'mitra_parent_id' => 'nullable|exists:partners,id',
            'nik' => 'nullable|string|max:30|unique:partners,nik,'.$partner->id,
            'full_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('partners', 'email')->ignore($partner->id),
                Rule::unique('users', 'email')->ignore($partner->user_id),
            ],
            'password' => [$partner->user_id ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'full_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_owner' => 'nullable|string|max:255',
            'smartphone_type' => 'nullable|string|max:100',
            'has_headstrap' => 'required|boolean',
            'status' => 'required|in:active,suspended',
            'base_hourly_rate' => 'required|numeric|min:0',
        ]);

        // Keep fallback support for older migration fields
        $validated['account_number'] = $validated['bank_account_number'] ?? null;
        $validated['account_owner_name'] = $validated['bank_account_owner'] ?? null;

        DB::transaction(function () use ($partner, $validated) {
            $password = $validated['password'] ?? null;
            unset($validated['password']);

            $user = $partner->user ?: new User;
            $user->name = $validated['full_name'];
            $user->email = $validated['email'];
            $user->role = $user->role ?: 'verifikator';
            $user->email_verified_at = $user->email_verified_at ?: now();

            if ($password) {
                $user->password = Hash::make($password);
            }

            $user->save();

            $partner->update([
                ...$validated,
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('partners.index')->with('success', 'Data mitra/worker dan akun login berhasil diperbarui!');
    }

    public function destroy(Partner $partner)
    {
        DB::transaction(function () use ($partner) {
            $user = $partner->user;
            $partner->delete();

            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('partners.index')->with('success', 'Mitra/Worker berhasil dihapus dari sistem.');
    }
}
