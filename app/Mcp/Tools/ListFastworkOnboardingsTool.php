<?php

namespace App\Mcp\Tools;

use App\Models\FastworkOnboarding;

class ListFastworkOnboardingsTool extends BaseTool
{
    public function getName(): string
    {
        return 'list_fastwork_onboardings';
    }

    public function getDescription(): string
    {
        return 'Lihat daftar pendaftaran Fastwork Onboarding terbaru (nama, tipe perangkat, username Fastwork, kepemilikan headstrap).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => 'Cari nama atau username Fastwork'],
                'device_type' => ['type' => 'string', 'description' => 'Filter tipe perangkat'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah data terbaru (default 20, maks 50)'],
            ],
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $limit = min(max((int) ($args['limit'] ?? 20), 1), 50);

        $query = FastworkOnboarding::query();

        if (! empty($args['keyword'])) {
            $kw = $args['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('full_name', 'LIKE', "%{$kw}%")
                    ->orWhere('fastwork_username', 'LIKE', "%{$kw}%");
            });
        }
        if (! empty($args['device_type'])) {
            $query->where('device_type', 'LIKE', '%' . $args['device_type'] . '%');
        }

        return [
            'total' => FastworkOnboarding::count(),
            'onboardings' => $query->latest()->limit($limit)->get([
                'id', 'full_name', 'device_type', 'fastwork_username', 'has_headstrap', 'created_at',
            ]),
        ];
    }
}
