<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recording extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'partner_id',
        'category_id',
        'video_url',
        'imu_data_url',
        'duration_seconds',
        'frequency_hz',
        'status',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
