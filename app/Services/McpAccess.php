<?php

namespace App\Services;

use App\Models\McpConnection;
use App\Models\User;
use Illuminate\Http\Request;

class McpAccess
{
    /**
     * Resolve dynamic abilities for a user based on their current role.
     *
     * @return array<int, string>
     */
    public function abilitiesFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $roleMap = config('mcp.role_abilities', []);

        return $roleMap[$user->role] ?? [];
    }

    public function canUseMcp(?User $user): bool
    {
        return count($this->abilitiesFor($user)) > 0;
    }

    public function allows(string $requiredPermission, ?Request $request = null): bool
    {
        $request ??= request();

        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            $abilities = $this->abilitiesFor($user);

            if (in_array('*', $abilities, true) || in_array($requiredPermission, $abilities, true)) {
                return true;
            }

            return false;
        }

        $legacyClient = $request->attributes->get('mcp_client');

        if (is_array($legacyClient)) {
            $permissions = $legacyClient['permissions'] ?? [];

            return in_array('*', $permissions, true) || in_array($requiredPermission, $permissions, true);
        }

        return false;
    }

    public function clientContext(Request $request): array
    {
        $client = $request->attributes->get('mcp_client');
        if (is_array($client)) {
            return $client;
        }

        $user = $request->user();
        if ($user instanceof User) {
            return [
                'name' => $user->name . ' (' . $user->email . ')',
                'user_id' => $user->id,
                'role' => $user->role,
                'permissions' => $this->abilitiesFor($user),
            ];
        }

        return [
            'name' => 'Unknown',
            'permissions' => [],
        ];
    }

    public function connectionFromRequest(Request $request): ?McpConnection
    {
        $connection = $request->attributes->get('mcp_connection');

        return $connection instanceof McpConnection ? $connection : null;
    }
}
