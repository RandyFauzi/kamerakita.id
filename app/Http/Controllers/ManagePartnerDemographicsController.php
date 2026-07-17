<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class ManagePartnerDemographicsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');

        $partners = Partner::query()
            ->with(['mitraParent'])
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

        return view('partners.index', compact('partners', 'search', 'role', 'status'));
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
        $nextMitraId = 'KMK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

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
            'email' => 'nullable|email|max:100',
            'full_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_owner' => 'nullable|string|max:255',
            'smartphone_type' => 'nullable|string|max:100',
            'status' => 'required|in:active,suspended',
            'base_hourly_rate' => 'required|numeric|min:0',
        ]);

        // Keep fallback support for older migration fields
        $validated['account_number'] = $validated['bank_account_number'] ?? null;
        $validated['account_owner_name'] = $validated['bank_account_owner'] ?? null;

        Partner::create($validated);

        return redirect()->route('partners.index')->with('success', 'Mitra/Worker berhasil didaftarkan!');
    }

    public function edit(Partner $partner)
    {
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
            'nik' => 'nullable|string|max:30|unique:partners,nik,' . $partner->id,
            'full_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'full_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_owner' => 'nullable|string|max:255',
            'smartphone_type' => 'nullable|string|max:100',
            'status' => 'required|in:active,suspended',
            'base_hourly_rate' => 'required|numeric|min:0',
        ]);

        // Keep fallback support for older migration fields
        $validated['account_number'] = $validated['bank_account_number'] ?? null;
        $validated['account_owner_name'] = $validated['bank_account_owner'] ?? null;

        $partner->update($validated);

        return redirect()->route('partners.index')->with('success', 'Data mitra/worker berhasil diperbarui!');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        return redirect()->route('partners.index')->with('success', 'Mitra/Worker berhasil dihapus dari sistem.');
    }
}
