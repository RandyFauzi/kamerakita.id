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

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

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
        'group_name',
        'base_hourly_rate',
        'user_id',
    ];

    protected $casts = [
        'has_headstrap' => 'boolean',
    ];

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-green-50 text-green-700 border-green-200',
            self::STATUS_INACTIVE => 'bg-amber-50 text-amber-700 border-amber-200',
            self::STATUS_SUSPENDED => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-gray-50 text-gray-600 border-gray-200',
        };
    }

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
