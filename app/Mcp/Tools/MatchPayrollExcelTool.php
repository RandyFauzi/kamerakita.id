<?php

namespace App\Mcp\Tools;

use App\Models\AtlasWorker;
use App\Models\AtlasTask;

class MatchPayrollExcelTool extends BaseTool
{
    public function getName(): string
    {
        return 'match_payroll_excel';
    }

    public function getDescription(): string
    {
        return 'Mencocokkan data billable hours dari Excel dengan AtlasTask untuk menemukan tanggal cutoff per worker.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'users_data' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => ['type' => 'string'],
                            'target_hours' => ['type' => 'number']
                        ]
                    ]
                ],
                'mode' => ['type' => 'string', 'description' => 'accumulate or test_date_range'],
                'start_date' => ['type' => 'string'],
                'end_date' => ['type' => 'string']
            ],
            'required' => ['users_data']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $usersData = $args['users_data'] ?? [];
        $mode = $args['mode'] ?? 'accumulate';
        $startDate = $args['start_date'] ?? '2026-09-06';
        $endDate = $args['end_date'] ?? '2026-09-22 23:59:59';
        
        $results = [];
        $datesCount = [];

        foreach ($usersData as $u) {
            $email = $u['email'];
            $targetHours = (float) $u['target_hours'];

            $worker = AtlasWorker::where('atlas_email', $email)->first();
            if (!$worker) continue;

            if ($mode === 'test_date_range') {
                $tasks = AtlasTask::where('atlas_worker_id', $worker->id)
                    ->where('status', 'Approved')
                    ->whereNull('payroll_id')
                    ->whereBetween('task_date', [$startDate, $endDate])
                    ->get();
                    
                $sumMinutes = $tasks->sum('approved_minutes');
                $sumHours = $sumMinutes / 60;
                $diff = abs($sumHours - $targetHours);
                
                $results[] = [
                    'email' => $email,
                    'target_hours' => round($targetHours, 2),
                    'range_hours' => round($sumHours, 2),
                    'diff' => round($diff, 2),
                    'matched' => $diff < 0.1
                ];
            } else {
                // Get all approved unbilled tasks ordered by date
                $tasks = AtlasTask::where('atlas_worker_id', $worker->id)
                    ->where('status', 'Approved')
                    ->whereNull('payroll_id')
                    ->orderBy('task_date', 'asc')
                    ->get();

                $cumulativeMinutes = 0;
                $matchedDate = null;
                $targetMinutes = $targetHours * 60;
                $closestDiff = PHP_FLOAT_MAX;
                $closestDate = null;
                $closestSum = 0;

                foreach ($tasks as $task) {
                    $cumulativeMinutes += $task->approved_minutes;
                    $diff = abs($cumulativeMinutes - $targetMinutes);
                    
                    if ($diff < $closestDiff) {
                        $closestDiff = $diff;
                        $closestDate = $task->task_date;
                        $closestSum = $cumulativeMinutes;
                    }

                    if ($diff < 1) {
                        $matchedDate = $task->task_date;
                        break;
                    }
                }

                if ($matchedDate) {
                    $dateStr = explode(' ', $matchedDate)[0];
                    $datesCount[$dateStr] = ($datesCount[$dateStr] ?? 0) + 1;
                } else if ($closestDate) {
                    $dateStr = explode(' ', $closestDate)[0];
                    $datesCount[$dateStr] = ($datesCount[$dateStr] ?? 0) + 1;
                }

                $results[] = [
                    'email' => $email,
                    'target_hours' => round($targetHours, 2),
                    'closest_hours' => round($closestSum / 60, 2),
                    'closest_date' => $closestDate,
                    'diff_minutes' => round($closestDiff, 2)
                ];
            }
        }

        if ($mode === 'test_date_range') {
            return [
                'summary' => [
                    'mode' => 'test_date_range',
                    'range' => "$startDate to $endDate",
                    'total_matched' => collect($results)->where('matched', true)->count(),
                    'total_analyzed' => count($results)
                ],
                'details' => $results
            ];
        }

        arsort($datesCount);
        return [
            'summary' => [
                'total_analyzed' => count($usersData),
                'consensus_dates' => $datesCount
            ],
            'details' => $results
        ];
    }
}
