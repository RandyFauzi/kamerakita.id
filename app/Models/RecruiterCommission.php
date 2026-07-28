<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterCommission extends Model
{
    protected $fillable = [
        'recruiter_partner_id',
        'worker_partner_id',
        'approved_hours_at_milestone',
        'commission_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'approved_hours_at_milestone' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * The Rekruter (Mitra or Rekruter role) who earns this commission.
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'recruiter_partner_id');
    }

    /**
     * The Worker who triggered the milestone.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'worker_partner_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
