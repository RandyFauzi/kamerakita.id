<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRecord extends Model
{
    public const TYPE_PPH21 = 'PPH21';
    public const TYPE_PPH23 = 'PPH23';

    public const STATUS_CALCULATED = 'CALCULATED';
    public const STATUS_WITHHELD = 'WITHHELD';
    public const STATUS_REPORTED = 'REPORTED';
    public const STATUS_EXEMPT = 'EXEMPT';

    protected $fillable = [
        'tax_period_id',
        'payment_cycle_id',
        'payment_cycle_entry_id',
        'partner_id',
        'recipient_name',
        'recipient_email',
        'recipient_npwp',
        'recipient_nik',
        'tax_type',
        'tax_object_code',
        'gross_amount_idr',
        'dpp_amount_idr',
        'tax_rate_percent',
        'tax_amount_idr',
        'net_amount_idr',
        'bupot_number',
        'bupot_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'gross_amount_idr' => 'integer',
        'dpp_amount_idr' => 'integer',
        'tax_rate_percent' => 'float',
        'tax_amount_idr' => 'integer',
        'net_amount_idr' => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(TaxPeriod::class, 'tax_period_id');
    }

    public function paymentCycle(): BelongsTo
    {
        return $this->belongsTo(PaymentCycle::class, 'payment_cycle_id');
    }

    public function paymentCycleEntry(): BelongsTo
    {
        return $this->belongsTo(PaymentCycleEntry::class, 'payment_cycle_entry_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}
