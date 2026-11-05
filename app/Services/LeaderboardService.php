<?php

namespace App\Services;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    /**
     * Get the date range for the current active leaderboard week (Monday to Sunday).
     */
    public function getCurrentWeekRange(): array
    {
        return [
            Carbon::now()->startOfWeek()->toDateString(),
            Carbon::now()->endOfWeek()->toDateString()
        ];
    }

    /**
     * Get the weekly leaderboard scores based on Atlas recording tasks.
     * Workers under a Mitra are aggregated into the parent Mitra's account.
     * Caches the result for 5 minutes.
     */
    public function getWeeklyLeaderboard(int $limit = 10): array
    {
        return Cache::remember("weekly_leaderboard_aggregated_v3_{$limit}", 300, function () use ($limit) {
            $range = $this->getCurrentWeekRange();

            $results = AtlasTask::selectRaw('atlas_worker_id, SUM(worked_minutes) as total_worked, SUM(approved_minutes) as total_approved')
                ->whereBetween('task_date', $range)
                ->groupBy('atlas_worker_id')
                ->having('total_worked', '>', 0)
                ->with(['atlasWorker.user.partner.mitraParent.user'])
                ->get();

            return $this->aggregateByMitraOrSolo($results, $limit);
        });
    }

    /**
     * Get all-time leaderboard scores based on Atlas recording tasks.
     * Workers under a Mitra are aggregated into the parent Mitra's account.
     * Caches the result for 10 minutes.
     */
    public function getAllTimeLeaderboard(int $limit = 10): array
    {
        return Cache::remember("alltime_leaderboard_aggregated_v3_{$limit}", 600, function () use ($limit) {
            $results = AtlasTask::selectRaw('atlas_worker_id, SUM(worked_minutes) as total_worked, SUM(approved_minutes) as total_approved')
                ->groupBy('atlas_worker_id')
                ->having('total_worked', '>', 0)
                ->with(['atlasWorker.user.partner.mitraParent.user'])
                ->get();

            return $this->aggregateByMitraOrSolo($results, $limit);
        });
    }

    /**
     * Aggregate task scores by Mitra (for workers under a vendor) or by solo worker.
     */
    protected function aggregateByMitraOrSolo($results, int $limit): array
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

            // 2. Determine Leaderboard Entity
            if ($partner && $partner->mitra_parent_id && $partner->mitraParent) {
                // Case A: Worker under a Mitra -> Credit goes to the Parent Mitra!
                $mitra = $partner->mitraParent;
                $entityId = (string) $mitra->id;
                $entityKey = 'mitra_' . $mitra->id;
                $entityType = 'mitra';
                $isTeam = true;
                $name = $mitra->full_name ?: ($mitra->user?->name ?: 'Mitra KameraKita');
                $mitraId = $mitra->mitra_id;
            } elseif ($partner && $partner->partner_role === Partner::ROLE_MITRA) {
                // Case B: The Mitra themselves recorded -> Credit goes to the Mitra!
                $entityId = (string) $partner->id;
                $entityKey = 'mitra_' . $partner->id;
                $entityType = 'mitra';
                $isTeam = true;
                $name = $partner->full_name ?: ($partner->user?->name ?: 'Mitra KameraKita');
                $mitraId = $partner->mitra_id;
            } elseif ($partner) {
                // Case C: Solo / Independent Worker -> Credit goes to the Worker!
                $entityId = (string) $partner->id;
                $entityKey = 'solo_' . $partner->id;
                $entityType = 'solo';
                $isTeam = false;
                $name = $partner->full_name ?: ($partner->user?->name ?: 'Worker');
                $mitraId = $partner->mitra_id;
            } else {
                // Case D: Fallback unlinked Atlas Worker
                $entityId = 'atlas_' . $worker->id;
                $entityKey = 'atlas_' . $worker->id;
                $entityType = 'solo';
                $isTeam = false;
                $name = $worker->user?->name ?: ($worker->atlas_email ? explode('@', $worker->atlas_email)[0] : 'Worker');
                $mitraId = null;
            }

            // 3. Accumulate Scores
            if (!isset($entities[$entityKey])) {
                $entities[$entityKey] = [
                    'entity_id'        => $entityId,
                    'entity_type'      => $entityType,
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
            if ($entityType === 'mitra') {
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
     * Matches either the partner ID directly, or if they are a worker under a ranked Mitra.
     * Returns their rank (1, 2, 3...) or null if they are not in the top.
     */
    public function getPartnerWeeklyRank(string $partnerId, int $topLimit = 3): ?int
    {
        $topEntities = $this->getWeeklyLeaderboard($topLimit);

        if (!is_iterable($topEntities)) {
            return null;
        }

        foreach ($topEntities as $index => $topEntity) {
            $entityId = data_get($topEntity, 'entity_id') ?: data_get($topEntity, 'worker_id');
            $partnerIds = data_get($topEntity, 'partner_ids', []);

            if ($entityId && (string) $entityId === (string) $partnerId) {
                return $index + 1; // 1-indexed rank
            }

            if (in_array((string) $partnerId, array_map('strval', $partnerIds), true)) {
                return $index + 1;
            }
        }

        return null;
    }
}
