<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PartnerActivityStatusService
{
    private const STALE_DAYS = 2;
    private const SYNC_CACHE_KEY = 'partners:activity-status:last-sync';

    public function syncAllIfDue(int $minutes = 30): array
    {
        if (! Cache::add(self::SYNC_CACHE_KEY, now()->toIso8601String(), now()->addMinutes($minutes))) {
            return [
                'checked' => 0,
                'activated' => 0,
                'inactivated' => 0,
                'skipped' => true,
            ];
        }

        return $this->syncAll();
    }

    public function syncAll(): array
    {
        $result = [
            'checked' => 0,
            'activated' => 0,
            'inactivated' => 0,
            'skipped' => false,
        ];

        Partner::query()
            ->whereIn('partner_role', ['worker', 'mitra'])
            ->where('status', '!=', Partner::STATUS_SUSPENDED)
            ->select(['id', 'partner_role', 'status', 'created_at'])
            ->chunkById(100, function ($partners) use (&$result): void {
                foreach ($partners as $partner) {
                    $previousStatus = $partner->status;
                    $this->syncPartner($partner);

                    $result['checked']++;

                    if ($previousStatus !== $partner->status) {
                        if ($partner->status === Partner::STATUS_ACTIVE) {
                            $result['activated']++;
                        }

                        if ($partner->status === Partner::STATUS_INACTIVE) {
                            $result['inactivated']++;
                        }
                    }
                }
            });

        return $result;
    }

    public function syncPartner(Partner $partner): Partner
    {
        if ($partner->status === Partner::STATUS_SUSPENDED) {
            return $partner;
        }

        $targetStatus = $this->shouldBeInactive($partner)
            ? Partner::STATUS_INACTIVE
            : Partner::STATUS_ACTIVE;

        if ($partner->status !== $targetStatus) {
            $partner->forceFill(['status' => $targetStatus])->save();
        }

        return $partner->refresh();
    }

    public function markActiveAfterReport(Partner $partner): void
    {
        if ($partner->status === Partner::STATUS_INACTIVE) {
            $partner->forceFill(['status' => Partner::STATUS_ACTIVE])->save();
        }
    }

    private function shouldBeInactive(Partner $partner): bool
    {
        $cutoffDate = now()->startOfDay()->subDays(self::STALE_DAYS);

        $hasRecentReport = VideoWorkReport::query()
            ->where('partner_id', $partner->id)
            ->whereNotNull('evidence_email_image_path')
            ->whereDate('submission_date', '>=', $cutoffDate->toDateString())
            ->exists();

        if ($hasRecentReport) {
            return false;
        }

        $latestReportDate = VideoWorkReport::query()
            ->where('partner_id', $partner->id)
            ->whereNotNull('evidence_email_image_path')
            ->max('submission_date');

        if ($latestReportDate) {
            return Carbon::parse($latestReportDate)->startOfDay()->lt($cutoffDate);
        }

        return $partner->created_at
            ? $partner->created_at->copy()->startOfDay()->lt($cutoffDate)
            : true;
    }
}
