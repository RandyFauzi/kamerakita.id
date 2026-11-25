<?php

namespace App\Mcp\Tools;

use App\Models\PasswordRecoveryRequest;

class ListPasswordRecoveriesTool extends BaseTool
{
    public function getName(): string
    {
        return 'list_password_recoveries';
    }

    public function getDescription(): string
    {
        return 'Lihat jumlah antrean dan daftar permintaan Password Recovery (pending, approved, rejected, blocked) beserta identifier dan waktu pengajuan.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'description' => 'Filter status: pending, approved, rejected, atau blocked'],
                'identifier' => ['type' => 'string', 'description' => 'Filter berdasarkan email/identifier (partial match)'],
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

        $query = PasswordRecoveryRequest::query()->with('user:id,name,email');

        if (! empty($args['status'])) {
            $query->where('status', strtolower((string) $args['status']));
        }
        if (! empty($args['identifier'])) {
            $query->where('identifier', 'LIKE', '%' . $args['identifier'] . '%');
        }

        return [
            'counts' => [
                'pending' => PasswordRecoveryRequest::where('status', 'pending')->count(),
                'approved' => PasswordRecoveryRequest::where('status', 'approved')->count(),
                'rejected' => PasswordRecoveryRequest::where('status', 'rejected')->count(),
                'blocked' => PasswordRecoveryRequest::where('status', 'blocked')->count(),
                'total' => PasswordRecoveryRequest::count(),
            ],
            'requests' => $query->latest('requested_at')->limit($limit)->get([
                'id', 'user_id', 'identifier', 'status', 'requested_at', 'approved_at', 'rejected_at', 'expires_at',
            ]),
        ];
    }
}
