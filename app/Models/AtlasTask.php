<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtlasTask extends Model
{
    protected $guarded = [];

    public function atlasWorker(): BelongsTo
    {
        return $this->belongsTo(AtlasWorker::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
