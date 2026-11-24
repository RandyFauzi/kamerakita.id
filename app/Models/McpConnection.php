<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class McpConnection extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'oauth_client_id',
        'name',
        'client_type',
        'token_hash',
        'token_suffix',
        'token_encrypted',
        'abilities',
        'account_version',
        'expires_at',
        'revoked_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'token_encrypted' => 'encrypted',
            'abilities' => 'array',
            'account_version' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function usableBy(?User $user): bool
    {
        if (! $user || ! $this->isActive()) {
            return false;
        }

        $userVersion = (int) ($user->account_version ?? 1);

        return $this->account_version === $userVersion;
    }

    public function revoke(): void
    {
        $this->forceFill([
            'token_hash' => null,
            'token_encrypted' => null,
            'revoked_at' => now(),
        ])->save();
    }

    public static function generateRawToken(): string
    {
        $prefix = (string) config('mcp.token_prefix', 'kkm_');

        return $prefix . Str::random(48);
    }

    public static function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}
