<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'partner_role',       // worker, mitra
        'mitra_parent_id',    // referencing the parent Mitra
        'mitra_id',           // e.g., KMK-001
        'nik',
        'full_name',
        'whatsapp_number',
        'email',
        'full_address',
        'bank_name',
        'bank_account_number',
        'bank_account_owner',
        'account_number',     // legacy fallback support
        'account_owner_name', // legacy fallback support
        'smartphone_type',
        'has_headstrap',
        'status',
        'base_hourly_rate',
        'user_id',
    ];

    protected $casts = [
        'has_headstrap' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: A Worker belongs to a Mitra (Coordinator/Manager)
     */
    public function mitraParent(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'mitra_parent_id');
    }

    /**
     * Relationship: A Mitra manages many Workers
     */
    public function workers(): HasMany
    {
        return $this->hasMany(Partner::class, 'mitra_parent_id');
    }

    /**
     * Relationship: Partner has many video submissions
     */
    public function videoWorkReports(): HasMany
    {
        return $this->hasMany(VideoWorkReport::class, 'partner_id');
    }
}
