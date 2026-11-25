<?php

namespace App\Mcp\Tools;

use App\Models\MainAccountRequest;

class ListMainAccountRequestsTool extends BaseTool
{
    public function getName(): string
    {
        return 'list_main_account_requests';
    }

    public function getDescription(): string
    {
        return 'Lihat jumlah antrean dan daftar permintaan Main Account (pending, approved, rejected) beserta email, estimasi akun, dan catatan.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'description' => 'Filter status: pending, approved, atau rejected'],
                'email' => ['type' => 'string', 'description' => 'Filter berdasarkan email (partial match)'],
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

        $query = MainAccountRequest::query()->with('user:id,name,email');

        if (! empty($args['status'])) {
            $query->where('status', strtolower((string) $args['status']));
        }
        if (! empty($args['email'])) {
            $query->where('email', 'LIKE', '%' . $args['email'] . '%');
        }

        return [
            'counts' => [
                'pending' => MainAccountRequest::where('status', 'pending')->count(),
                'approved' => MainAccountRequest::where('status', 'approved')->count(),
                'rejected' => MainAccountRequest::where('status', 'rejected')->count(),
                'total' => MainAccountRequest::count(),
            ],
            'requests' => $query->latest()->limit($limit)->get([
                'id', 'user_id', 'email', 'estimated_accounts', 'status', 'notes', 'created_at', 'approved_at', 'rejected_at',
            ]),
        ];
    }
}
