<?php

namespace App\Mcp\Tools;

use Illuminate\Support\Facades\DB;

class AggregateRecordsTool extends BaseTool
{
    public function getName(): string
    {
        return 'aggregate_records';
    }

    public function getDescription(): string
    {
        return 'Menghitung aggregasi data dari tabel secara dinamis.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'resource' => ['type' => 'string', 'description' => 'Tabel target (contoh: video_work_reports)'],
                'aggregations' => ['type' => 'object', 'description' => 'Aggregasi, contoh: {"total_submitted": {"sum": "submitted_duration_minutes"}}'],
                'filters' => ['type' => 'object', 'description' => 'Filter dinamis, sama seperti fetch_records'],
                'group_by' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Kolom pengelompokan']
            ],
            'required' => ['resource', 'aggregations']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $resource = $args['resource'] ?? null;
        if (!$resource) throw new \Exception("Resource is required");

        $aggregations = $args['aggregations'] ?? [];
        if (empty($aggregations) || !is_array($aggregations)) {
            throw new \Exception("Aggregations object is required");
        }

        $tableMap = [
            'users' => 'users',
            'partners' => 'partners',
            'video_work_reports' => 'video_work_reports',
            'captured_emails' => 'captured_emails',
        ];

        if (!isset($tableMap[$resource])) {
            throw new \Exception("Resource not allowed: {$resource}");
        }

        $query = DB::table($tableMap[$resource]);

        if (isset($args['filters'])) {
            $this->applyFilters($query, $args['filters']);
        }

        $groupBy = $args['group_by'] ?? [];
        if (!empty($groupBy)) {
            $query->groupBy($groupBy);
            $selects = $groupBy;
        } else {
            $selects = [];
        }

        foreach ($aggregations as $alias => $operation) {
            foreach ($operation as $type => $column) {
                $type = strtoupper($type);
                if (in_array($type, ['SUM', 'COUNT', 'AVG', 'MIN', 'MAX'])) {
                    $cleanCol = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                    $cleanAlias = preg_replace('/[^a-zA-Z0-9_]/', '', $alias);
                    $selects[] = DB::raw("{$type}({$cleanCol}) as {$cleanAlias}");
                }
            }
        }

        if (!empty($selects)) {
            $query->select($selects);
        }

        return [
            'data' => $query->get()
        ];
    }
}
