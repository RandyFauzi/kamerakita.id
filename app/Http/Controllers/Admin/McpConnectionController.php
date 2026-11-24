<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\McpAuditLog;
use App\Models\McpConnection;
use App\Models\McpOAuthAccessToken;
use App\Services\McpAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class McpConnectionController extends Controller
{
    public function index(Request $request, McpAccess $access): View
    {
        $user = $request->user();

        $connectionsQuery = McpConnection::query()
            ->with('user')
            ->latest();

        if ($user->role !== 'superadmin') {
            $connectionsQuery->where('user_id', $user->id);
        }

        $connections = $connectionsQuery->get();

        $oauthTokens = McpOAuthAccessToken::query()
            ->with('client')
            ->where('user_id', $user->id)
            ->where('revoked', false)
            ->latest('created_at')
            ->get();

        $auditLogsQuery = McpAuditLog::query()->latest()->limit(25);
        if ($user->role !== 'superadmin') {
            $auditLogsQuery->where('user_id', $user->id);
        }
        $auditLogs = $auditLogsQuery->get();

        return view('admin.integrations.mcp', [
            'connections' => $connections,
            'oauthTokens' => $oauthTokens,
            'auditLogs' => $auditLogs,
            'endpointUrl' => url('/mcp/kamerakita'),
            'abilities' => $access->abilitiesFor($user),
            'oauthEnabled' => (bool) config('mcp.oauth_enabled', true),
        ]);
    }

    public function store(Request $request, McpAccess $access): RedirectResponse
    {
        $user = $request->user();

        if (! $access->canUseMcp($user)) {
            abort(403, 'Role Anda tidak memiliki izin akses MCP.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'client_type' => ['nullable', 'string', 'in:antigravity,claude_code,gemini_cli,codex,manual'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $rawToken = McpConnection::generateRawToken();

        McpConnection::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'client_type' => $validated['client_type'] ?? 'manual',
            'token_hash' => McpConnection::hashToken($rawToken),
            'token_suffix' => substr($rawToken, -6),
            'token_encrypted' => $rawToken,
            'abilities' => $access->abilitiesFor($user),
            'account_version' => (int) ($user->account_version ?? 1),
            'expires_at' => isset($validated['expires_in_days'])
                ? now()->addDays((int) $validated['expires_in_days'])
                : null,
        ]);

        return redirect()
            ->route('admin.integrations.mcp.index')
            ->with('status', 'Kunci akses MCP berhasil dibuat.')
            ->with('new_mcp_token', $rawToken);
    }

    public function reveal(Request $request, McpConnection $connection): JsonResponse
    {
        $user = $request->user();

        if ($connection->user_id !== $user->id && $user->role !== 'superadmin') {
            abort(403);
        }

        if (! $connection->isActive() || empty($connection->token_encrypted)) {
            return response()->json([
                'message' => 'Token tidak tersedia atau sudah dicabut.',
            ], 404)->header('Cache-Control', 'no-store');
        }

        return response()->json([
            'token' => $connection->token_encrypted,
        ])->header('Cache-Control', 'no-store');
    }

    public function destroy(Request $request, McpConnection $connection): RedirectResponse
    {
        $user = $request->user();

        if ($connection->user_id !== $user->id && $user->role !== 'superadmin') {
            abort(403);
        }

        $connection->revoke();

        return redirect()
            ->route('admin.integrations.mcp.index')
            ->with('status', 'Koneksi MCP berhasil dicabut.');
    }

    public function revokeOAuth(Request $request, string $tokenId): RedirectResponse
    {
        $user = $request->user();

        $token = McpOAuthAccessToken::query()
            ->where('id', $tokenId)
            ->when($user->role !== 'superadmin', fn ($q) => $q->where('user_id', $user->id))
            ->firstOrFail();

        $token->revoke();

        return redirect()
            ->route('admin.integrations.mcp.index')
            ->with('status', 'Sesi OAuth MCP berhasil dicabut.');
    }
}
