<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use App\Services\PartnerActivityStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ManagePartnerDemographicsController extends Controller
{
    public function index(Request $request)
    {
        app(PartnerActivityStatusService::class)->syncAllIfDue();

        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');
        $group = $request->input('group');
        
        // Spreadsheet Column-Specific Filters
        $mitraId = $request->input('mitra_id');
        $fullName = $request->input('full_name');
        $hourlyRate = $request->input('hourly_rate');
        $headstrap = $request->input('headstrap');
        $whatsapp = $request->input('whatsapp');
        $mitraParent = $request->input('mitra_parent');
        $clientRegistered = $request->input('client_registered');

        $todayDateString = now()->toDateString();
        
        $totalUsers = Partner::count();
        $totalWorkers = Partner::where('partner_role', 'worker')->count();
        $totalMitra = Partner::where('partner_role', 'mitra')->count();
        $totalRekruter = Partner::where('partner_role', 'rekruter')->count();
        $totalSuspended = Partner::where('status', 'suspended')->count();
        
        // Pernah aktif = pernah mengirim laporan minimal 1x (sepanjang waktu)
        $totalActive = Partner::where('status', '!=', 'suspended')
            ->whereHas('videoWorkReports')
            ->count();

        // Belum pernah aktif = belum pernah kirim laporan sama sekali
        $totalInactive = Partner::where('status', '!=', 'suspended')
            ->whereDoesntHave('videoWorkReports')
            ->count();

        $summary = [
            'total_users' => $totalUsers,
            'total_workers' => $totalWorkers,
            'total_mitra' => $totalMitra,
            'total_rekruter' => $totalRekruter,
            'total_active' => $totalActive,
            'total_inactive' => $totalInactive,
            'total_suspended' => $totalSuspended,
        ];

        $partners = Partner::query()
            ->with(['mitraParent', 'user'])
            ->withMax('videoWorkReports', 'submission_date')
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
                if ($status === 'active') {
                    // Pernah aktif = pernah kirim laporan (sepanjang waktu)
                    $query->where('status', '!=', 'suspended')
                        ->whereHas('videoWorkReports');
                } elseif ($status === 'inactive') {
                    // Belum pernah aktif = belum pernah kirim laporan sama sekali
                    $query->where('status', '!=', 'suspended')
                        ->whereDoesntHave('videoWorkReports');
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($group, function ($query, $group) {
                $query->where('group_name', $group);
            })
            ->when($mitraId, function ($query, $mitraId) {
                $query->where('mitra_id', 'like', "%{$mitraId}%");
            })
            ->when($fullName, function ($query, $fullName) {
                $query->where('full_name', 'like', "%{$fullName}%");
            })
            ->when($hourlyRate, function ($query, $hourlyRate) {
                $query->where('base_hourly_rate', $hourlyRate);
            })
            ->when($headstrap !== null && $headstrap !== '', function ($query) use ($headstrap) {
                $query->where('has_headstrap', $headstrap === 'yes' ? 1 : 0);
            })
            ->when($whatsapp, function ($query, $whatsapp) {
                $query->where('whatsapp_number', 'like', "%{$whatsapp}%");
            })
            ->when($mitraParent, function ($query, $mitraParent) {
                $query->where('mitra_parent_id', $mitraParent);
            })
            ->when($clientRegistered !== null && $clientRegistered !== '', function ($query) use ($clientRegistered) {
                $query->where('is_client_registered', $clientRegistered === 'yes' ? 1 : 0);
            })
            ->orderBy('mitra_id', 'asc')
            ->paginate(15)
            ->withQueryString();

        $mitraList = Partner::where('partner_role', 'mitra')->get();

        return view('partners.index', compact(
            'partners', 'search', 'role', 'status', 'group', 'summary',
            'mitraId', 'fullName', 'hourlyRate', 'headstrap', 'whatsapp', 'mitraParent', 'clientRegistered', 'mitraList'
        ));
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
            'partner_role' => 'required|in:worker,mitra,rekruter',
            'mitra_parent_id' => 'nullable|exists:partners,id',
            'mitra_id' => 'required|string|unique:partners,mitra_id',
            'nik' => 'nullable|string|max:30|unique:partners,nik',
            'full_name' => 'required|string|max:255',
            'registration_date' => 'nullable|date',
            'whatsapp_number' => 'required|string|max:20',
            'email' => ['required', 'email', 'max:100', 'unique:partners,email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'full_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_owner' => 'nullable|string|max:255',
            'smartphone_type' => 'nullable|string|max:100',
            'has_headstrap' => 'required|boolean',
            'status' => 'required|in:active,inactive,suspended',
            'group_name' => 'nullable|string|max:50',
            'is_client_registered' => 'required|boolean',
            'base_hourly_rate' => 'required|numeric|min:0',
            'is_vip' => 'nullable|boolean',
        ]);

        // Keep fallback support for older migration fields
        $validated['account_number'] = $validated['bank_account_number'] ?? null;
        $validated['account_owner_name'] = $validated['bank_account_owner'] ?? null;

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'worker',
                'email_verified_at' => now(),
            ]);

            unset($validated['password']);

            Partner::create([
                ...$validated,
                'is_vip' => $validated['is_vip'] ?? false,
                'user_id' => $user->id,
            ]);
        });

        \App\Services\ActivityLogger::log('partner.create', "Mendaftarkan mitra/worker baru: {$validated['full_name']} (Role: {$validated['partner_role']}, ID: {$validated['mitra_id']})");

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
            'partner_role' => 'required|in:worker,mitra,rekruter',
            'mitra_parent_id' => 'nullable|exists:partners,id',
            'nik' => 'nullable|string|max:30|unique:partners,nik,'.$partner->id,
            'full_name' => 'required|string|max:255',
            'registration_date' => 'nullable|date',
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
            'status' => 'required|in:active,inactive,suspended',
            'group_name' => 'nullable|string|max:50',
            'is_client_registered' => 'required|boolean',
            'base_hourly_rate' => 'required|numeric|min:0',
            'is_vip' => 'nullable|boolean',
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
            $user->role = $user->role ?: 'worker';
            $user->email_verified_at = $user->email_verified_at ?: now();

            if ($password) {
                $user->password = Hash::make($password);
            }

            $user->save();

            $partner->update([
                ...$validated,
                'is_vip' => $validated['is_vip'] ?? false,
                'user_id' => $user->id,
            ]);
        });

        \App\Services\ActivityLogger::log('partner.update', "Memperbarui data mitra/worker: {$validated['full_name']} (ID: {$partner->mitra_id})");

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

        \App\Services\ActivityLogger::log('partner.delete', "Menghapus mitra/worker: {$partner->full_name} (ID: {$partner->mitra_id})");

        return redirect()->route('partners.index')->with('success', 'Mitra/Worker berhasil dihapus dari sistem.');
    }

    public function exportContacts(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');
        $group = $request->input('group');
        
        $mitraId = $request->input('mitra_id');
        $fullName = $request->input('full_name');
        $hourlyRate = $request->input('hourly_rate');
        $headstrap = $request->input('headstrap');
        $whatsapp = $request->input('whatsapp');
        $mitraParent = $request->input('mitra_parent');
        $clientRegistered = $request->input('client_registered');

        $partners = Partner::query()
            ->with(['user'])
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
            ->when($group, function ($query, $group) {
                $query->where('group_name', $group);
            })
            ->when($mitraId, function ($query, $mitraId) {
                $query->where('mitra_id', 'like', "%{$mitraId}%");
            })
            ->when($fullName, function ($query, $fullName) {
                $query->where('full_name', 'like', "%{$fullName}%");
            })
            ->when($hourlyRate, function ($query, $hourlyRate) {
                $query->where('base_hourly_rate', $hourlyRate);
            })
            ->when($headstrap !== null && $headstrap !== '', function ($query) use ($headstrap) {
                $query->where('has_headstrap', $headstrap === 'yes' ? 1 : 0);
            })
            ->when($whatsapp, function ($query, $whatsapp) {
                $query->where('whatsapp_number', 'like', "%{$whatsapp}%");
            })
            ->when($mitraParent, function ($query, $mitraParent) {
                $query->where('mitra_parent_id', $mitraParent);
            })
            ->when($clientRegistered !== null && $clientRegistered !== '', function ($query) use ($clientRegistered) {
                $query->where('is_client_registered', $clientRegistered === 'yes' ? 1 : 0);
            })
            ->orderBy('mitra_id', 'asc')
            ->get();

        $output = "";
        foreach ($partners as $partner) {
            $email = $partner->user?->email ?? 'Tidak ada email';
            $output .= "{$partner->full_name} ({$email})\n";
        }

        return response($output, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:partners,id',
            'group_name' => 'nullable|string|in:no_change,clear,Group A,Group B',
            'status' => 'nullable|string|in:no_change,active,inactive,suspended',
            'has_headstrap' => 'nullable|string|in:no_change,yes,no',
            'is_client_registered' => 'nullable|string|in:no_change,yes,no',
            'base_hourly_rate' => 'nullable|numeric|min:0',
            'mitra_parent_id' => 'nullable|string|in:no_change,clear,other',
            'selected_parent_id' => 'nullable|exists:partners,id',
        ]);

        $ids = $validated['ids'];
        $updateData = [];

        if (isset($validated['group_name']) && $validated['group_name'] !== 'no_change') {
            $updateData['group_name'] = $validated['group_name'] === 'clear' ? null : $validated['group_name'];
        }

        if (isset($validated['status']) && $validated['status'] !== 'no_change') {
            $updateData['status'] = $validated['status'];
        }

        if (isset($validated['has_headstrap']) && $validated['has_headstrap'] !== 'no_change') {
            $updateData['has_headstrap'] = $validated['has_headstrap'] === 'yes' ? 1 : 0;
        }

        if (isset($validated['is_client_registered']) && $validated['is_client_registered'] !== 'no_change') {
            $updateData['is_client_registered'] = $validated['is_client_registered'] === 'yes' ? 1 : 0;
        }

        if ($request->filled('base_hourly_rate')) {
            $updateData['base_hourly_rate'] = $validated['base_hourly_rate'];
        }

        if (isset($validated['mitra_parent_id']) && $validated['mitra_parent_id'] !== 'no_change') {
            if ($validated['mitra_parent_id'] === 'clear') {
                $updateData['mitra_parent_id'] = null;
            } elseif ($validated['mitra_parent_id'] === 'other' && $request->filled('selected_parent_id')) {
                $updateData['mitra_parent_id'] = $validated['selected_parent_id'];
            }
        }

        if (!empty($updateData)) {
            Partner::whereIn('id', $ids)->update($updateData);
        }

        $count = count($ids);
        $changedFields = implode(', ', array_keys($updateData));
        \App\Services\ActivityLogger::log('partner.bulk_update', "Melakukan sunting massal untuk {$count} mitra/worker. Kolom yang diubah: {$changedFields}");

        return redirect()->back()->with('success', count($ids) . ' akun kemitraan berhasil disunting secara massal!');
    }
}
