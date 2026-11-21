<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCycleEntry extends Model
{
    protected $fillable = [
        'payment_cycle_id',
        'partner_id',
        'atlas_worker_id',
        'worker_email',
        'billable_hours',
        'rejected_hours',
        'not_checked_hours',
        'total_hours',
        'approved_share',
        'recordings_count',
        'main_rejection_reason',
        'on_list_now',
        'removed_from_program',
        'amount_to_pay_idr',
        'is_paid',
        'paid_at',
        'paid_by',
        'sort_order',
    ];

    protected $casts = [
        'billable_hours' => 'float',
        'rejected_hours' => 'float',
        'not_checked_hours' => 'float',
        'total_hours' => 'float',
        'approved_share' => 'float',
        'recordings_count' => 'integer',
        'amount_to_pay_idr' => 'integer',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PaymentCycle::class, 'payment_cycle_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function atlasWorker(): BelongsTo
    {
        return $this->belongsTo(AtlasWorker::class, 'atlas_worker_id');
    }

    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
