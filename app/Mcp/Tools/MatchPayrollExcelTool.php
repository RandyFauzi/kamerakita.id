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
                    'description' => 'Array of objects berisi {email: string, target_hours: float}',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => ['type' => 'string'],
                            'target_hours' => ['type' => 'number']
                        ]
                    ]
                ]
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
        $results = [];
        
        // Group by match dates to find consensus
        $datesCount = [];

        foreach ($usersData as $u) {
            $email = $u['email'];
            $targetHours = (float) $u['target_hours'];

            $worker = AtlasWorker::where('atlas_email', $email)->first();
            if (!$worker) {
                $results[] = [
                    'email' => $email,
                    'error' => 'Worker not found'
                ];
                continue;
            }

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

                // If difference is less than 1 minute (tolerance due to decimal precision)
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
                'matched' => $matchedDate !== null,
                'diff_minutes' => round($closestDiff, 2)
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
