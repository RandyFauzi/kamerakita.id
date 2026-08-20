<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasUuids, HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const ROLE_WORKER = 'worker';
    public const ROLE_MITRA = 'mitra';
    public const ROLE_REKRUTER = 'rekruter';

    /** Roles that own a referral code and can recruit workers */
    public const RECRUITER_ROLES = [self::ROLE_MITRA, self::ROLE_REKRUTER];

    protected $fillable = [
        'partner_role',           // worker, mitra, rekruter
        'mitra_parent_id',        // referencing the parent Mitra
        'mitra_id',               // e.g., KMK-001
        'nik',
        'full_name',
        'registration_date',
        'whatsapp_number',
        'email',
        'full_address',
        'bank_name',
        'bank_account_number',
        'bank_account_owner',
        'account_number',         // legacy fallback support
        'account_owner_name',     // legacy fallback support
        'smartphone_type',
        'has_headstrap',
        'status',
        'group_name',
        'referral_code',          // personal code for Mitra/Rekruter to share with workers
        'recruiter_partner_id',   // FK to Mitra/Rekruter who recruited this worker
        'is_client_registered',
        'base_hourly_rate',
        'user_id',
        'is_vip',
    ];

    /**
     * Auto-generate a unique referral code when a Mitra or Rekruter partner is created.
     */
    protected static function booted(): void
    {
        static::creating(function (Partner $partner) {
            if (empty($partner->registration_date)) {
                $partner->registration_date = now()->toDateString();
            }

            if (in_array($partner->partner_role, self::RECRUITER_ROLES) && empty($partner->referral_code)) {
                do {
                    $code = 'REF-' . strtoupper(Str::random(6));
                } while (self::where('referral_code', $code)->exists());

                $partner->referral_code = $code;
            }
        });
    }

    protected $casts = [
        'registration_date' => 'date',
        'has_headstrap' => 'boolean',
        'is_client_registered' => 'boolean',
        'is_vip' => 'boolean',
    ];

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_SUSPENDED) {
            return 'Suspended';
        }

        $latestDate = $this->video_work_reports_max_submission_date;
        if ($latestDate === null && $this->relationLoaded('videoWorkReports')) {
            $latestDate = $this->videoWorkReports->max('submission_date');
        }
        if ($latestDate === null) {
            $latestDate = $this->videoWorkReports()->max('submission_date');
        }

        if (!$latestDate) {
            return 'Inactive';
        }

        $days = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($latestDate)->startOfDay());
        if ($days === 0) {
            return 'Aktif Hari Ini';
        }

        return "Aktif {$days} hari yang lalu";
    }

    public function statusBadgeClasses(): string
    {
        if ($this->status === self::STATUS_SUSPENDED) {
            return 'bg-red-50 text-red-700 border-red-200';
        }

        $latestDate = $this->video_work_reports_max_submission_date;
        if ($latestDate === null && $this->relationLoaded('videoWorkReports')) {
            $latestDate = $this->videoWorkReports->max('submission_date');
        }
        if ($latestDate === null) {
            $latestDate = $this->videoWorkReports()->max('submission_date');
        }

        if (!$latestDate) {
            return 'bg-gray-50 text-gray-600 border-gray-200';
        }

        $days = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($latestDate)->startOfDay());
        if ($days === 0) {
            return 'bg-green-50 text-green-700 border-green-200';
        }

        return 'bg-amber-50 text-amber-700 border-amber-200';
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

    /**
     * Relationship: The Mitra/Rekruter who recruited this Worker
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'recruiter_partner_id');
    }

    /**
     * Relationship: Workers recruited by this Mitra/Rekruter
     */
    public function recruitedWorkers(): HasMany
    {
        return $this->hasMany(Partner::class, 'recruiter_partner_id');
    }

    /**
     * Relationship: Commissions earned by this Rekruter
     */
    public function recruiterCommissions(): HasMany
    {
        return $this->hasMany(RecruiterCommission::class, 'recruiter_partner_id');
    }

    /**
     * Relationship: Partner has many recordings (Mobile App)
     */
    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class, 'partner_id');
    }
}
