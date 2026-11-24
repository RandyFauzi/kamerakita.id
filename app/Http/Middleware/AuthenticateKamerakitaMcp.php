<?php

namespace App\Http\Middleware;

use App\Models\McpConnection;
use App\Services\McpAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateKamerakitaMcp
{
    public function __construct(protected McpAccess $access)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mcp.enabled', true)) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->input('id'),
                'error' => [
                    'code' => -32000,
                    'message' => 'MCP server is currently disabled.',
                ],
            ], 503);
        }

        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthorizedResponse($request, 'Missing Bearer token.');
        }

        $prefix = (string) config('mcp.token_prefix', 'kkm_');

        if (str_starts_with($token, $prefix)) {
            $connection = McpConnection::query()
                ->with('user')
                ->where('token_hash', McpConnection::hashToken($token))
                ->first();

            if (! $connection || ! $connection->usableBy($connection->user) || ! $this->access->canUseMcp($connection->user)) {
                return $this->unauthorizedResponse($request, 'Invalid or revoked MCP token.');
            }

            $connection->forceFill(['last_used_at' => now()])->saveQuietly();

            Auth::setUser($connection->user);
            $request->setUserResolver(fn () => $connection->user);
            $request->attributes->set('mcp_connection', $connection);
            $request->attributes->set('mcp_client', [
                'name' => $connection->name,
                'user_id' => $connection->user_id,
                'role' => $connection->user->role,
                'permissions' => $this->access->abilitiesFor($connection->user),
            ]);

            return $next($request);
        }

        $legacyClients = config('mcp.clients', []);
        if (array_key_exists($token, $legacyClients)) {
            $request->attributes->set('mcp_client', $legacyClients[$token]);

            return $next($request);
        }

        if (config('mcp.oauth_enabled', true)) {
            $oauthUser = Auth::guard('kamerakita-mcp-oauth')->user();

            if ($oauthUser && $this->access->canUseMcp($oauthUser)) {
                Auth::setUser($oauthUser);
                $request->setUserResolver(fn () => $oauthUser);
                $request->attributes->set('mcp_client', [
                    'name' => 'OAuth: ' . $oauthUser->email,
                    'user_id' => $oauthUser->id,
                    'role' => $oauthUser->role,
                    'permissions' => $this->access->abilitiesFor($oauthUser),
                ]);

                return $next($request);
            }
        }

        return $this->unauthorizedResponse($request, 'Invalid MCP token or unauthorized OAuth session.');
    }

    protected function unauthorizedResponse(Request $request, string $message): Response
    {
        $response = response()->json([
            'jsonrpc' => '2.0',
            'id' => $request->input('id'),
            'error' => [
                'code' => -32000,
                'message' => $message,
            ],
        ], 401);

        if (config('mcp.oauth_enabled', true)) {
            $resourceMetadataUrl = url('/.well-known/oauth-protected-resource/mcp/kamerakita');
            $response->header(
                'WWW-Authenticate',
                sprintf(
                    'Bearer realm="Kamerakita MCP", resource_metadata="%s", scope="mcp:use"',
                    $resourceMetadataUrl
                )
            );
        }

        return $response;
    }
}
