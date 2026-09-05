<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'invoice_date',
        'total_workers',
        'total_approved_hours',
        'total_amount',
        'client_id',
        'client_name',
        'client_email',
        'client_address',
        'client_tax_id',
        'unit_rate',
        'currency',
        'period_start',
        'period_end',
        'source_approved_hours',
        'billable_hours',
        'adjustment_hours',
        'adjustment_reason',
        'status',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'voided_at' => 'datetime',
        'source_approved_hours' => 'decimal:2',
        'billable_hours' => 'decimal:2',
        'adjustment_hours' => 'decimal:2',
        'total_approved_hours' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'unit_rate' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function issue()
    {
        $this->update(['status' => 'ISSUED']);
    }

    public function markAsSent()
    {
        $this->update(['status' => 'SENT']);
    }

    public function markAsPaid()
    {
        $this->update(['status' => 'PAID']);
    }

    public function voidInvoice($reason, $user = null)
    {
        $this->update([
            'status' => 'VOID',
            'voided_at' => now(),
            'voided_by' => $user,
            'void_reason' => $reason,
        ]);
    }

    protected static function booted(): void
    {
        $clearCache = function () {
            \Illuminate\Support\Facades\Cache::forget('admin_client_invoices');
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }
}
