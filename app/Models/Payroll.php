<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
    ];

    public function atlasWorker()
    {
        return $this->belongsTo(AtlasWorker::class);
    }

    public function atlasTasks()
    {
        return $this->hasMany(AtlasTask::class);
    }

    public function getPaymentProofUrlAttribute()
    {
        if ($this->payment_proof_path) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($this->payment_proof_path);
        }
        return null;
    }
}
