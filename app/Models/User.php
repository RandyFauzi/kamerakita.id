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
        return in_array($this->role, ['superadmin', 'admin'], true);
    }

    public function canAccessQcRoom(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'finance'], true);
    }

    public function canAccessPayroll(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'finance'], true);
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
