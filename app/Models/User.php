<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'email_verified_at', 'has_completed_onboarding', 'tos_accepted_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions, HasApiTokens;

    public const POV_OWNER_EMAIL = 'randyfauzi24@gmail.com';

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_FINANCE = 'finance';
    public const ROLE_ACCOUNTANT_TAX = 'accountant_tax';

    /** @var list<string> */
    public const INTERNAL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_FINANCE,
        self::ROLE_ACCOUNTANT_TAX,
    ];

    /**
     * @return array<string, array{label: string, badge_class: string, description: string}>
     */
    public static function internalRoleOptions(): array
    {
        return [
            self::ROLE_ADMIN => [
                'label' => 'Admin',
                'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'description' => 'Akses penuh operasional, QC, mitra, payroll, dan manajemen akun.',
            ],
            self::ROLE_FINANCE => [
                'label' => 'Finance',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'description' => 'Akses pembayaran siklus, ekspor payroll, QC room, dan invoice klien.',
            ],
            self::ROLE_ACCOUNTANT_TAX => [
                'label' => 'Accountant & Tax Manager',
                'badge_class' => 'bg-violet-50 text-violet-700 border-violet-200',
                'description' => 'Akses jurnal keuangan, tax center (PPh/PPN), laporan Laba Rugi, invoice, payroll audit, dan audit pajak mitra.',
            ],
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_completed_onboarding' => 'boolean',
            'tos_accepted_at' => 'datetime',
        ];
    }

    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class);
    }

    public function mainAccountRequests(): HasMany
    {
        return $this->hasMany(MainAccountRequest::class);
    }

    public function capturedEmails(): HasMany
    {
        return $this->hasMany(CapturedEmail::class);
    }

    public function hasFullAdminAccess(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN], true);
    }

    public function isAccountantTax(): bool
    {
        return $this->role === self::ROLE_ACCOUNTANT_TAX;
    }

    public function canAccessQcRoom(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN, self::ROLE_FINANCE], true);
    }

    public function canAccessPayroll(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_FINANCE,
            self::ROLE_ACCOUNTANT_TAX,
        ], true);
    }

    public function canAccessFinanceJournal(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_ACCOUNTANT_TAX,
        ], true);
    }

    public function canAccessInvoices(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_FINANCE,
            self::ROLE_ACCOUNTANT_TAX,
        ], true);
    }

    public function canAccessTaxCenter(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_ACCOUNTANT_TAX,
        ], true);
    }

    public function canAccessAccountingReports(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_ACCOUNTANT_TAX,
        ], true);
    }

    public function canViewPartnerTaxAudit(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_ACCOUNTANT_TAX,
        ], true);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_FINANCE => 'Finance',
            self::ROLE_ACCOUNTANT_TAX => 'Accountant & Tax Manager',
            default => ucfirst((string) $this->role),
        };
    }

    public function canUseAccountPov(): bool
    {
        return strtolower((string) $this->email) === self::POV_OWNER_EMAIL
            && $this->role === 'superadmin';
    }

    public function atlasWorker(): HasOne
    {
        return $this->hasOne(AtlasWorker::class);
    }
}
