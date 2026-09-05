<?php

namespace App\Mcp\Tools;

class FetchRecordsTool extends BaseTool
{
    public function getName(): string
    {
        return 'fetch_records';
    }

    public function getDescription(): string
    {
        return 'Membaca/mengambil data dari tabel dengan filter dinamis.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'resource' => ['type' => 'string', 'description' => 'Tabel target (contoh: partners, users, video_work_reports, captured_emails)'],
                'select' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Kolom yang diambil'],
                'filters' => ['type' => 'object', 'description' => 'Filter dalam bentuk key-value (mendukung string/angka atau object {in:[]}, {between:[]})'],
                'relations' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Relasi yang disertakan'],
                'limit' => ['type' => 'integer'],
                'offset' => ['type' => 'integer']
            ],
            'required' => ['resource']
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

        $query = $this->getQueryForResource($resource);

        if (isset($args['select']) && is_array($args['select'])) {
            $query->select($args['select']);
        }

        if (isset($args['relations']) && is_array($args['relations'])) {
            $query->with($args['relations']);
        }

        if (isset($args['filters'])) {
            $this->applyFilters($query, $args['filters']);
        }

        $limit = $args['limit'] ?? 100; // Default limit
        $offset = $args['offset'] ?? 0;

        $totalCount = $query->count();
        
        $data = $query->limit(min(1000, $limit))->offset($offset)->get();

        return [
            'total' => $totalCount,
            'count' => $data->count(),
            'data' => $this->maskSensitiveData($data)
        ];
    }
}
