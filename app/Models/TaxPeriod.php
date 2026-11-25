<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxPeriod extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_CALCULATED = 'CALCULATED';
    public const STATUS_PAID = 'PAID';
    public const STATUS_REPORTED = 'REPORTED';

    public const ALL_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_CALCULATED,
        self::STATUS_PAID,
        self::STATUS_REPORTED,
    ];

    protected $fillable = [
        'period_month',
        'status',
        'total_gross_idr',
        'total_pph21_idr',
        'total_pph23_idr',
        'total_ppn_idr',
        'total_client_pph23_idr',
        'ntpn_code',
        'payment_date',
        'notes',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_gross_idr' => 'integer',
        'total_pph21_idr' => 'integer',
        'total_pph23_idr' => 'integer',
        'total_ppn_idr' => 'integer',
        'total_client_pph23_idr' => 'integer',
        'payment_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(TaxRecord::class)->orderByDesc('gross_amount_idr');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function totalWithholdingTaxIdr(): int
    {
        return (int) $this->total_pph21_idr + (int) $this->total_pph23_idr;
    }
}
