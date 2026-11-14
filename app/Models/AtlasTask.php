<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtlasTask extends Model
{
    protected $guarded = [];

    protected $casts = ['source_observed_at' => 'datetime', 'source_details' => 'array', 'qc_changed_at' => 'datetime'];

    public function getQcDeltaAttribute(): ?array
    {
        if (! $this->qc_changed_at) {
            return null;
        }

        return [
            'approved' => round((float) $this->approved_minutes - (float) $this->prev_approved_minutes, 2),
            'rejected' => round((float) $this->rejected_minutes - (float) $this->prev_rejected_minutes, 2),
        ];
    }

    public function atlasWorker(): BelongsTo
    {
        return $this->belongsTo(AtlasWorker::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
