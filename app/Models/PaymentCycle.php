<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentCycle extends Model
{
    protected $fillable = [
        'name',
        'source_label',
        'period_start',
        'period_end',
        'frozen_on',
        'client_billable_hours',
        'client_rate_usd',
        'client_total_usd',
        'usd_to_idr_rate',
        'revenue_idr',
        'worker_rate_idr',
        'total_pay_idr',
        'created_by',
        'journal_posted_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'frozen_on' => 'date',
        'client_billable_hours' => 'float',
        'client_rate_usd' => 'float',
        'client_total_usd' => 'float',
        'usd_to_idr_rate' => 'float',
        'revenue_idr' => 'integer',
        'worker_rate_idr' => 'integer',
        'total_pay_idr' => 'integer',
        'journal_posted_at' => 'datetime',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(PaymentCycleEntry::class)->orderBy('sort_order')->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateTotalPay(): void
    {
        $this->total_pay_idr = (int) $this->entries()->sum('amount_to_pay_idr');
        $this->save();
    }

    public function omzetIdr(): int
    {
        return (int) $this->revenue_idr - (int) $this->total_pay_idr;
    }

    public function splitFourIdr(): float
    {
        return $this->omzetIdr() / 4;
    }
}
