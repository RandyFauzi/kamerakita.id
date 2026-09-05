<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Models\Partner;
use Illuminate\Support\Facades\Hash;

class CreateCustomUserTool extends BaseTool
{
    public function getName(): string
    {
        return 'create_custom_user';
    }

    public function getDescription(): string
    {
        return 'Mendaftarkan custom user/vendor baru.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'email' => ['type' => 'string'],
                'password' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'role' => ['type' => 'string'],
                'partner_role' => ['type' => 'string']
            ],
            'required' => ['email', 'password']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.critical';
    }

    public function execute(array $args, array $client)
    {
        $email = $args['email'] ?? null;
        $password = $args['password'] ?? null;
        $name = $args['name'] ?? 'Vendor User';
        $role = $args['role'] ?? 'worker';
        $partnerRole = $args['partner_role'] ?? 'worker';

        if (!$email || !$password) {
            throw new \Exception("Parameter 'email' dan 'password' wajib diisi.");
        }

        if (User::where('email', $email)->exists()) {
            throw new \Exception("Email '{$email}' sudah terdaftar.");
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'email_verified_at' => now(),
        ]);

        $latestPartner = Partner::orderBy('mitra_id', 'desc')->first();
        if ($latestPartner && preg_match('/KMK-(\d+)/', $latestPartner->mitra_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $nextMitraId = 'KMK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $partner = Partner::create([
            'partner_role' => $partnerRole,
            'mitra_id' => $nextMitraId,
            'full_name' => $user->name,
            'whatsapp_number' => '08' . rand(100000000, 999999999),
            'email' => $email,
            'has_headstrap' => false,
            'status' => 'active',
            'group_name' => 'Group A',
            'base_hourly_rate' => 50000,
            'user_id' => $user->id,
        ]);

        return [
            'message' => "Berhasil mendaftarkan akun baru: {$email}",
            'data' => [
                'user_id' => $user->id,
                'partner_id' => $partner->id,
                'mitra_id' => $nextMitraId,
                'name' => $user->name,
                'email' => $email,
                'role' => $role,
                'partner_role' => $partnerRole
            ]
        ];
    }
}
