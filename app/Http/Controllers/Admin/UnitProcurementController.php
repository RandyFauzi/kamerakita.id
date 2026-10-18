<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitProcurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UnitProcurementController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitProcurement::with(['requester', 'approver']);

        // Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('procurement_code', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Metrics for top cards
        $stats = [
            'total_active' => UnitProcurement::whereIn('status', ['pending_approval', 'approved', 'purchasing'])->count(),
            'pending_approval' => UnitProcurement::where('status', 'pending_approval')->count(),
            'total_estimated_budget' => (float) UnitProcurement::whereIn('status', ['pending_approval', 'approved', 'purchasing'])->sum('total_estimated_price'),
            'total_received_units' => (int) UnitProcurement::where('status', 'received')->sum('quantity'),
        ];

        $procurements = $query->orderByRaw("FIELD(status, 'pending_approval', 'approved', 'purchasing', 'draft', 'received', 'rejected', 'cancelled')")
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.unit-procurements.index', compact('procurements', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => ['required', Rule::in(['camera', 'lens', 'smartphone', 'audio_mic', 'lighting_rig', 'storage_pc', 'accessory', 'other'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'quantity' => 'required|integer|min:1',
            'estimated_unit_price' => 'required|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'purchase_url' => 'nullable|url|max:1000',
            'purpose_notes' => 'nullable|string|max:2000',
        ]);

        $validated['procurement_code'] = UnitProcurement::generateProcurementCode();
        $validated['total_estimated_price'] = $validated['quantity'] * $validated['estimated_unit_price'];
        $validated['requested_by'] = Auth::id();
        $validated['status'] = 'pending_approval';

        UnitProcurement::create($validated);

        return redirect()->route('admin.unit-procurements.index')
            ->with('success', 'Pengajuan pengadaan unit baru berhasil disimpan dengan kode ' . $validated['procurement_code']);
    }

    public function update(Request $request, UnitProcurement $unitProcurement)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => ['required', Rule::in(['camera', 'lens', 'smartphone', 'audio_mic', 'lighting_rig', 'storage_pc', 'accessory', 'other'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'quantity' => 'required|integer|min:1',
            'estimated_unit_price' => 'required|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'purchase_url' => 'nullable|url|max:1000',
            'purpose_notes' => 'nullable|string|max:2000',
        ]);

        $validated['total_estimated_price'] = $validated['quantity'] * $validated['estimated_unit_price'];
        $unitProcurement->update($validated);

        return redirect()->route('admin.unit-procurements.index')
            ->with('success', 'Data pengadaan ' . $unitProcurement->procurement_code . ' berhasil diperbarui.');
    }

    public function updateStatus(Request $request, UnitProcurement $unitProcurement)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending_approval', 'approved', 'purchasing', 'received', 'rejected', 'cancelled'])],
            'actual_total_price' => 'nullable|numeric|min:0',
            'serial_number' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:2000',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $unitProcurement->admin_notes,
        ];

        // Status transition handlers
        if ($validated['status'] === 'approved') {
            $updateData['approved_by'] = Auth::id();
            $updateData['approved_at'] = now();
        }

        if ($request->filled('actual_total_price')) {
            $updateData['actual_total_price'] = $validated['actual_total_price'];
        }

        if ($validated['status'] === 'received') {
            $updateData['received_at'] = now();
            if ($request->filled('serial_number')) {
                $updateData['serial_number'] = $validated['serial_number'];
            }
        }

        if ($request->hasFile('receipt')) {
            if ($unitProcurement->receipt_path && Storage::disk('public')->exists($unitProcurement->receipt_path)) {
                Storage::disk('public')->delete($unitProcurement->receipt_path);
            }
            $updateData['receipt_path'] = $request->file('receipt')->store('procurements/receipts', 'public');
        }

        $unitProcurement->update($updateData);

        return redirect()->route('admin.unit-procurements.index')
            ->with('success', 'Status pengadaan ' . $unitProcurement->procurement_code . ' berhasil diperbarui ke: ' . $unitProcurement->status_badge['label']);
    }

    public function destroy(UnitProcurement $unitProcurement)
    {
        $code = $unitProcurement->procurement_code;
        $unitProcurement->delete();

        return redirect()->route('admin.unit-procurements.index')
            ->with('success', "Pengadaan unit {$code} berhasil dihapus.");
    }
}
