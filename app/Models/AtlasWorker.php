<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtlasWorker extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function atlasTasks(): HasMany
    {
        return $this->hasMany(AtlasTask::class);
    }
}
