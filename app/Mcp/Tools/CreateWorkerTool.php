<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Models\Partner;
use Illuminate\Support\Facades\Hash;

class CreateWorkerTool extends BaseTool
{
    public function getName(): string
    {
        return 'create_worker';
    }

    public function getDescription(): string
    {
        return 'Mendaftarkan worker/partner baru.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'username' => ['type' => 'string'],
                'whatsapp_number' => ['type' => 'string'],
                'group_name' => ['type' => 'string']
            ],
            'required' => ['name', 'username']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.critical';
    }

    public function execute(array $args, array $client)
    {
        $name = $args['name'] ?? null;
        $username = $args['username'] ?? null;
        $whatsapp = $args['whatsapp_number'] ?? null;
        $groupName = $args['group_name'] ?? 'Group A';

        if (!$name || !$username) {
            throw new \Exception("Parameter 'name' dan 'username' wajib diisi.");
        }

        $username = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $username));
        $email = $username . '@kamerakitaid.site';

        if (User::where('email', $email)->exists()) {
            throw new \Exception("Username '{$username}' sudah digunakan.");
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('KameraKita123!'),
            'role' => 'verifikator', // Default role
        ]);

        // Auto-generate next KMK-XXX code
        $latestPartner = Partner::orderBy('mitra_id', 'desc')->first();
        if ($latestPartner && preg_match('/KMK-(\d+)/', $latestPartner->mitra_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $nextMitraId = 'KMK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        if (!$whatsapp) {
            $whatsapp = '08' . rand(100000000, 999999999);
        }

        $partner = Partner::create([
            'partner_role' => 'worker',
            'mitra_id' => $nextMitraId,
            'full_name' => $user->name,
            'whatsapp_number' => $whatsapp,
            'email' => $email,
            'has_headstrap' => false,
            'status' => 'active',
            'group_name' => $groupName,
            'base_hourly_rate' => 50000,
            'user_id' => $user->id,
        ]);

        return [
            'message' => "Berhasil mendaftarkan akun Worker baru.",
            'data' => [
                'user_id' => $user->id,
                'mitra_id' => $nextMitraId,
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => 'KameraKita123!',
                'whatsapp' => $whatsapp
            ]
        ];
    }
}
