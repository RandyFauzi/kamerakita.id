<?php

namespace App\Services;

use App\Models\AtlasTask;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    public const CATEGORY_WORKER = 'worker';
    public const CATEGORY_MITRA = 'mitra';

    /**
     * Get the date range for the current active leaderboard week (Monday to Sunday).
     */
    public function getCurrentWeekRange(): array
    {
        return [
            Carbon::now()->startOfWeek()->toDateString(),
            Carbon::now()->endOfWeek()->toDateString(),
        ];
    }

    /**
     * Resolve which leaderboard category a partner belongs to.
     * Mitra and workers under a Mitra belong to 'mitra'.
     * Solo workers and Main Accounts (plus their sub-accounts) belong to 'worker'.
     */
    public function resolvePartnerCategory(Partner $partner): string
    {
        $partner->loadMissing('mitraParent');

        if ($partner->isMitra() || ($partner->mitraParent && $partner->mitraParent->isMitra())) {
            return self::CATEGORY_MITRA;
        }

        return self::CATEGORY_WORKER;
    }

    /**
     * Get the weekly leaderboard scores based on Atlas recording tasks.
     * Supports filtering by category ('worker' or 'mitra'), or null for combined.
     * Caches the result for 5 minutes.
     */
    public function getWeeklyLeaderboard(int $limit = 10, ?string $category = null): array
    {
        $catKey = $category ?: 'all';

        return Cache::remember("weekly_leaderboard_v4_{$catKey}_{$limit}", 300, function () use ($limit, $category) {
            $range = $this->getCurrentWeekRange();

            $results = AtlasTask::selectRaw('atlas_worker_id, SUM(worked_minutes) as total_worked, SUM(approved_minutes) as total_approved')
                ->whereBetween('task_date', $range)
                ->groupBy('atlas_worker_id')
                ->having('total_worked', '>', 0)
                ->with(['atlasWorker.user.partner.mitraParent.user'])
                ->get();

            return $this->aggregateByCategory($results, $limit, $category);
        });
    }

    /**
     * Get all-time leaderboard scores based on Atlas recording tasks.
     * Supports filtering by category ('worker' or 'mitra'), or null for combined.
     * Caches the result for 10 minutes.
     */
    public function getAllTimeLeaderboard(int $limit = 10, ?string $category = null): array
    {
        $catKey = $category ?: 'all';

        return Cache::remember("alltime_leaderboard_v4_{$catKey}_{$limit}", 600, function () use ($limit, $category) {
            $results = AtlasTask::selectRaw('atlas_worker_id, SUM(worked_minutes) as total_worked, SUM(approved_minutes) as total_approved')
                ->groupBy('atlas_worker_id')
                ->having('total_worked', '>', 0)
                ->with(['atlasWorker.user.partner.mitraParent.user'])
                ->get();

            return $this->aggregateByCategory($results, $limit, $category);
        });
    }

    /**
     * Aggregate task scores by category:
     * - Category 'worker': Solo workers + Main Accounts (aggregated with all their sub-accounts).
     * - Category 'mitra': Mitra teams (aggregated with all workers under them).
     */
    protected function aggregateByCategory($results, int $limit, ?string $category = null): array
    {
        // Pre-fetch partners by email for any AtlasWorker lacking a direct user relation
        $unlinkedEmails = $results->map(function ($item) {
            $w = $item->atlasWorker;
            return (!$w?->user?->partner && $w?->atlas_email) ? $w->atlas_email : null;
        })->filter()->unique()->values();

        $partnersByEmail = collect();
        if ($unlinkedEmails->isNotEmpty()) {
            $partnersByEmail = Partner::with(['mitraParent.user', 'user'])
                ->whereIn('email', $unlinkedEmails)
                ->get()
                ->keyBy('email');
        }

        $entities = [];

        foreach ($results as $item) {
            $worker = $item->atlasWorker;
            if (!$worker) {
                continue;
            }

            // 1. Resolve Partner
            $partner = $worker->user?->partner ?? ($partnersByEmail->get($worker->atlas_email) ?? null);

            // 2. Determine Leaderboard Entity & Category
            if ($partner && $partner->mitra_parent_id && $partner->mitraParent) {
                $parent = $partner->mitraParent;
                $entityId = (string) $parent->id;
                $mitraId = $parent->mitra_id;
                $isTeam = true;

                if ($parent->isMitra()) {
                    $entityCategory = self::CATEGORY_MITRA;
                    $entityKey = 'mitra_' . $parent->id;
                    $entityType = 'mitra';
                    $name = $parent->full_name ?: ($parent->user?->name ?: 'Mitra KameraKita');
                } else {
                    // Main Account sub-accounts aggregate into their Main Account in the 'worker' category
                    $entityCategory = self::CATEGORY_WORKER;
                    $entityKey = 'main_' . $parent->id;
                    $entityType = 'main_account';
                    $name = $parent->full_name ?: ($parent->user?->name ?: 'Main Account KameraKita');
                }
            } elseif ($partner && $partner->isMitra()) {
                $entityCategory = self::CATEGORY_MITRA;
                $entityId = (string) $partner->id;
                $entityKey = 'mitra_' . $partner->id;
                $entityType = 'mitra';
                $isTeam = true;
                $name = $partner->full_name ?: ($partner->user?->name ?: 'Mitra KameraKita');
                $mitraId = $partner->mitra_id;
            } elseif ($partner && $partner->isMainAccount()) {
                $entityCategory = self::CATEGORY_WORKER;
                $entityId = (string) $partner->id;
                $entityKey = 'main_' . $partner->id;
                $entityType = 'main_account';
                $isTeam = true;
                $name = $partner->full_name ?: ($partner->user?->name ?: 'Main Account KameraKita');
                $mitraId = $partner->mitra_id;
            } elseif ($partner) {
                $entityCategory = self::CATEGORY_WORKER;
                $entityId = (string) $partner->id;
                $entityKey = 'solo_' . $partner->id;
                $entityType = 'solo';
                $isTeam = false;
                $name = $partner->full_name ?: ($partner->user?->name ?: 'Worker');
                $mitraId = $partner->mitra_id;
            } else {
                $entityCategory = self::CATEGORY_WORKER;
                $entityId = 'atlas_' . $worker->id;
                $entityKey = 'atlas_' . $worker->id;
                $entityType = 'solo';
                $isTeam = false;
                $name = $worker->user?->name ?: ($worker->atlas_email ? explode('@', $worker->atlas_email)[0] : 'Worker');
                $mitraId = null;
            }

            // Filter by requested category when specified
            if ($category !== null && $entityCategory !== $category) {
                continue;
            }

            // 3. Accumulate Scores
            if (!isset($entities[$entityKey])) {
                $entities[$entityKey] = [
                    'entity_id'        => $entityId,
                    'entity_type'      => $entityType,
                    'category'         => $entityCategory,
                    'is_team'          => $isTeam,
                    'name'             => $name,
                    'mitra_id'         => $mitraId,
                    'worked_minutes'   => 0.0,
                    'approved_minutes' => 0.0,
                    'total_score'      => 0.0,
                    'contributor_ids'  => [],
                    'partner_ids'      => [],
                ];
            }

            $entities[$entityKey]['worked_minutes'] += (float) $item->total_worked;
            $entities[$entityKey]['approved_minutes'] += (float) $item->total_approved;
            $entities[$entityKey]['total_score'] += (float) $item->total_worked;
            $entities[$entityKey]['contributor_ids'][$worker->id] = true;

            if ($partner) {
                $entities[$entityKey]['partner_ids'][$partner->id] = true;
            }
            if (in_array($entityType, ['mitra', 'main_account'], true)) {
                $entities[$entityKey]['partner_ids'][$entityId] = true;
            }
        }

        // 4. Sort and Format
        return collect($entities)->map(function ($entity) {
            $membersCount = count($entity['contributor_ids']);

            return [
                'worker_id'        => $entity['entity_id'],
                'entity_id'        => $entity['entity_id'],
                'entity_type'      => $entity['entity_type'],
                'category'         => $entity['category'],
                'is_team'          => $entity['is_team'],
                'members_count'    => $membersCount,
                'name'             => $entity['name'],
                'worked_minutes'   => $entity['worked_minutes'],
                'approved_minutes' => $entity['approved_minutes'],
                'total_score'      => $entity['total_score'],
                'partner'          => ['full_name' => $entity['name']],
                'partner_ids'      => array_keys($entity['partner_ids']),
            ];
        })
        ->sortByDesc('worked_minutes')
        ->values()
        ->take($limit)
        ->all();
    }

    /**
     * Check if a specific partner or worker is in the top ranks for the current week.
     * Matches either the partner ID directly, or if they are a worker/sub-account under a parent.
     * Returns their rank (1, 2, 3...) or null if they are not in the top.
     */
    public function getPartnerWeeklyRank(string $partnerId, int $topLimit = 3, ?string $category = null): ?int
    {
        $topEntities = $this->getWeeklyLeaderboard($topLimit, $category);

        if (!is_iterable($topEntities)) {
            return null;
        }

        foreach ($topEntities as $index => $topEntity) {
            $entityId = data_get($topEntity, 'entity_id') ?: data_get($topEntity, 'worker_id');
            $partnerIds = data_get($topEntity, 'partner_ids', []);

            if ($entityId && (string) $entityId === (string) $partnerId) {
                return $index + 1;
            }

            if (in_array((string) $partnerId, array_map('strval', $partnerIds), true)) {
                return $index + 1;
            }
        }

        return null;
    }
}
