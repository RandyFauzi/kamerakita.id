<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitProcurement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unit_procurements';

    protected $fillable = [
        'procurement_code',
        'item_name',
        'category',
        'priority',
        'quantity',
        'estimated_unit_price',
        'total_estimated_price',
        'actual_total_price',
        'vendor_name',
        'purchase_url',
        'purpose_notes',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'receipt_path',
        'serial_number',
        'received_at',
        'admin_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_unit_price' => 'decimal:2',
        'total_estimated_price' => 'decimal:2',
        'actual_total_price' => 'decimal:2',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public static function generateProcurementCode(): string
    {
        $prefix = 'KK-PO-' . date('Ym') . '-';
        $latest = static::withTrashed()
            ->where('procurement_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->procurement_code, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'camera' => 'Kamera',
            'lens' => 'Lensa',
            'smartphone' => 'Smartphone',
            'audio_mic' => 'Audio / Mic',
            'lighting_rig' => 'Lighting & Rig',
            'storage_pc' => 'PC / Laptop / SSD',
            'accessory' => 'Aksesoris',
            default => 'Lainnya',
        };
    }

    public function getPriorityBadgeAttribute(): array
    {
        return match ($this->priority) {
            'urgent' => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Mendesak (Urgent)'],
            'high' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Tinggi'],
            'medium' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Sedang'],
            default => ['bg' => 'bg-gray-50 text-gray-700 border-gray-200', 'label' => 'Rendah'],
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft' => ['bg' => 'bg-gray-100 text-gray-700 border-gray-200', 'label' => 'Draft Usulan'],
            'pending_approval' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-300', 'label' => 'Menunggu Approval'],
            'approved' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-300', 'label' => 'Disetujui'],
            'purchasing' => ['bg' => 'bg-purple-100 text-purple-800 border-purple-300', 'label' => 'Proses Order/Beli'],
            'received' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'label' => 'Selesai & Diterima'],
            'rejected' => ['bg' => 'bg-rose-100 text-rose-800 border-rose-300', 'label' => 'Ditolak'],
            'cancelled' => ['bg' => 'bg-gray-200 text-gray-600 border-gray-300', 'label' => 'Dibatalkan'],
            default => ['bg' => 'bg-gray-100 text-gray-800 border-gray-200', 'label' => ucfirst($this->status)],
        };
    }
}
