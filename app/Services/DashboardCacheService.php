<?php

namespace App\Services;

use App\Models\AtlasWorker;
use App\Models\Partner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardCacheService
{
    public const TTL_OWNER_GLOBAL = 60;
    public const TTL_WORKER = 30;
    public const TTL_MITRA = 300;
    public const TTL_REKRUTER = 300;
    public const TTL_CHARTS = 600;

    public static function ownerGlobalKey(): string
    {
        return 'dashboard:owner:global_metrics:v1';
    }

    public static function workerKey(string $partnerId): string
    {
        return "dashboard:worker:{$partnerId}:v1";
    }

    public static function mitraKey(string $partnerId): string
    {
        return "dashboard:mitra:{$partnerId}:v2";
    }

    public static function rekruterKey(string $partnerId): string
    {
        return "dashboard:rekruter:{$partnerId}:v2";
    }

    /**
     * Read/populate cache with automatic fallback to database if cache backend (e.g. Redis) fails.
     */
    public function remember(string $key, int $ttlSeconds, \Closure $callback): mixed
    {
        try {
            return Cache::remember($key, $ttlSeconds, $callback);
        } catch (\Throwable $e) {
            Log::warning('Dashboard cache read/write failed; falling back to database.', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $callback();
        }
    }

    public function forget(string ...$keys): void
    {
        foreach ($keys as $key) {
            try {
                Cache::forget($key);
            } catch (\Throwable $e) {
                Log::warning('Dashboard cache invalidation failed.', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function invalidateOwnerDashboard(): void
    {
        $this->forget(
            self::ownerGlobalKey(),
            'admin_monthly_data_v3',
            'admin_daily_average_data_v4'
        );
    }

    public function invalidateForPartner(?Partner $partner): void
    {
        if (! $partner) {
            $this->invalidateOwnerDashboard();

            return;
        }

        $keys = [
            self::workerKey($partner->id),
            self::mitraKey($partner->id),
            self::rekruterKey($partner->id),
        ];

        if ($partner->mitra_parent_id) {
            $keys[] = self::mitraKey($partner->mitra_parent_id);
        }

        if ($partner->recruiter_partner_id) {
            $keys[] = self::rekruterKey($partner->recruiter_partner_id);
        }

        $this->forget(...$keys);
        $this->invalidateOwnerDashboard();
    }

    public function invalidateForAtlasWorker(?int $atlasWorkerId): void
    {
        if (! $atlasWorkerId) {
            $this->invalidateOwnerDashboard();

            return;
        }

        $atlasWorker = AtlasWorker::find($atlasWorkerId);
        if (! $atlasWorker) {
            $this->invalidateOwnerDashboard();

            return;
        }

        $partners = Partner::query()
            ->where(function ($q) use ($atlasWorker) {
                if ($atlasWorker->user_id) {
                    $q->where('user_id', $atlasWorker->user_id);
                }
                if (! empty($atlasWorker->atlas_email)) {
                    $atlasWorker->user_id
                        ? $q->orWhere('email', $atlasWorker->atlas_email)
                        : $q->where('email', $atlasWorker->atlas_email);
                }
            })
            ->get();

        if ($partners->isEmpty()) {
            $this->invalidateOwnerDashboard();

            return;
        }

        foreach ($partners as $partner) {
            $this->invalidateForPartner($partner);
        }
    }
}
