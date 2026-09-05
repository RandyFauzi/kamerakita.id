<?php

namespace App\Mcp\Tools;

use App\Models\User;

class SearchPartnerTool extends BaseTool
{
    public function getName(): string
    {
        return 'search_partner';
    }

    public function getDescription(): string
    {
        return 'Cari partner/worker berdasarkan nama atau email.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => 'Nama atau email yang dicari']
            ],
            'required' => ['keyword']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $keyword = $args['keyword'] ?? '';
        $users = User::where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->with(['partner'])
            ->get();
            
        return $this->maskSensitiveData($users);
    }
}
