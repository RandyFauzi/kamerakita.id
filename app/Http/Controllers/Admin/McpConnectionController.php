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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class McpConnectionController extends Controller
{
    public function index(Request $request, McpAccess $access): Response
    {
        $user = $request->user();
        $abilities = $access->abilitiesFor($user);
        $clients = config('mcp.clients_ui', []);
        $requestedClient = $request->query('client');
        $client = is_string($requestedClient) && isset($clients[$requestedClient]) ? $requestedClient : 'codex';

        $tab = match ($request->query('tab')) {
            'connected' => 'connected',
            'tokens' => 'tokens',
            default => 'setup',
        };

        $baseQuery = McpConnection::query()
            ->with('user')
            ->when($user->role !== 'superadmin', fn ($q) => $q->where('user_id', $user->id));

        $connections = match ($tab) {
            'connected' => (clone $baseQuery)->whereNotNull('last_used_at')->orderByDesc('last_used_at')->paginate(15)->withQueryString(),
            'tokens' => (clone $baseQuery)->whereNotNull('token_suffix')->latest()->paginate(15)->withQueryString(),
            default => null,
        };

        $oauthTokens = $tab === 'connected'
            ? McpOAuthAccessToken::query()
                ->with('client')
                ->where('user_id', $user->id)
                ->where('revoked', false)
                ->latest('created_at')
                ->get()
            : collect();

        $auditLogs = McpAuditLog::query()
            ->when($user->role !== 'superadmin', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->limit(15)
            ->get();

        $encryptedToken = $request->session()->pull('kamerakita_mcp_new_token');
        $newToken = $encryptedToken ? Crypt::decryptString($encryptedToken) : null;

        return response()->view('admin.integrations.mcp', [
            'user' => $user,
            'role' => $user->role,
            'abilities' => $abilities,
            'clients' => $clients,
            'client' => $client,
            'tab' => $tab,
            'connections' => $connections,
            'oauthTokens' => $oauthTokens,
            'auditLogs' => $auditLogs,
            'newToken' => $newToken,
            'endpoint' => url('/mcp/kamerakita'),
            'oauthEnabled' => (bool) config('mcp.oauth_enabled', true),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, McpAccess $access): RedirectResponse
    {
        $user = $request->user();

        if (! $access->canUseMcp($user)) {
            abort(403, 'Role Anda tidak memiliki izin akses MCP.');
        }

        $clients = config('mcp.clients_ui', []);
        $allowedAbilities = $access->abilitiesFor($user);

        $options = $request->validate([
            'mode' => ['sometimes', 'string', Rule::in(['quick', 'custom'])],
            'client' => ['nullable', 'string', Rule::in(array_keys($clients))],
        ]);

        $mode = $options['mode'] ?? 'custom';
        $clientKey = $options['client'] ?? 'codex';

        if ($mode === 'quick') {
            $name = $clients[$clientKey]['label'] ?? 'Kamerakita MCP';
            $selectedAbilities = $allowedAbilities;
            $expiresInDays = (int) config('mcp.token_days', 90);
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'abilities' => ['nullable', 'array'],
                'abilities.*' => ['required', 'string', Rule::in($allowedAbilities)],
                'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            ]);

            $name = $validated['name'];
            $selectedAbilities = ! empty($validated['abilities']) ? $validated['abilities'] : $allowedAbilities;
            $expiresInDays = isset($validated['expires_in_days'])
                ? (int) $validated['expires_in_days']
                : (int) config('mcp.token_days', 90);
        }

        $activeCount = McpConnection::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();

        if ($activeCount >= (int) config('mcp.max_connections', 20)) {
            throw ValidationException::withMessages([
                'name' => 'Batas koneksi aktif tercapai. Cabut koneksi yang sudah tidak dipakai.',
            ]);
        }

        $rawToken = McpConnection::generateRawToken();

        McpConnection::create([
            'user_id' => $user->id,
            'name' => $name,
            'client_type' => $clientKey,
            'token_hash' => McpConnection::hashToken($rawToken),
            'token_suffix' => substr($rawToken, -6),
            'token_encrypted' => $rawToken,
            'abilities' => $selectedAbilities,
            'account_version' => (int) ($user->account_version ?? 1),
            'expires_at' => $expiresInDays > 0 ? now()->addDays($expiresInDays) : null,
        ]);

        $query = $clientKey !== 'codex' ? ['client' => $clientKey] : [];

        return redirect()
            ->route('admin.integrations.mcp.index', $query)
            ->with('status', 'Kunci akses MCP berhasil dibuat.')
            ->with('kamerakita_mcp_new_token', Crypt::encryptString($rawToken));
    }

    public function reveal(Request $request, McpConnection $connection): JsonResponse
    {
        $user = $request->user();

        if ($connection->user_id !== $user->id && $user->role !== 'superadmin') {
            abort(403);
        }

        if (! $connection->isActive() || empty($connection->token_encrypted)) {
            return response()->json([
                'message' => 'Kunci akses tidak tersedia.',
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

        $tab = $request->input('tab') === 'connected' ? 'connected' : 'tokens';

        return redirect()
            ->route('admin.integrations.mcp.index', ['tab' => $tab])
            ->with('status', 'Koneksi MCP dicabut.');
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
            ->route('admin.integrations.mcp.index', ['tab' => 'connected'])
            ->with('status', 'Sesi OAuth MCP dicabut.');
    }
}
