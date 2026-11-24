<?php

namespace Tests\Feature;

use App\Models\McpAuditLog;
use App\Models\McpConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpServerContractTest extends TestCase
{
    use RefreshDatabase;

    private function createTokenFor(User $user, string $name = 'Test Client'): array
    {
        $raw = McpConnection::generateRawToken();

        $connection = McpConnection::create([
            'user_id' => $user->id,
            'name' => $name,
            'client_type' => 'antigravity',
            'token_hash' => McpConnection::hashToken($raw),
            'token_suffix' => substr($raw, -6),
            'token_encrypted' => $raw,
            'abilities' => ['*'],
            'account_version' => (int) ($user->account_version ?? 1),
        ]);

        return [$raw, $connection];
    }

    public function test_unauthenticated_mcp_request_returns_401_with_www_authenticate_header(): void
    {
        $response = $this->postJson('/mcp/kamerakita', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ]);

        $response->assertStatus(401);
        $header = (string) $response->headers->get('WWW-Authenticate');
        $this->assertStringContainsString('resource_metadata=', $header);
        $this->assertStringContainsString('oauth-protected-resource/mcp/kamerakita', $header);
    }

    public function test_oauth_discovery_endpoints_return_rfc_compliant_metadata(): void
    {
        $protected = $this->getJson('/.well-known/oauth-protected-resource/mcp/kamerakita');
        $protected->assertOk()
            ->assertJsonStructure(['resource', 'authorization_servers', 'scopes_supported'])
            ->assertJsonPath('scopes_supported.0', 'mcp:use');

        $authServer = $this->getJson('/.well-known/oauth-authorization-server');
        $authServer->assertOk()
            ->assertJsonPath('authorization_response_iss_parameter_supported', true)
            ->assertJsonPath('code_challenge_methods_supported.0', 'S256')
            ->assertJsonStructure([
                'issuer',
                'authorization_endpoint',
                'token_endpoint',
                'registration_endpoint',
            ]);
    }

    public function test_dynamic_client_registration_registers_pkce_client(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'ChatGPT Custom MCP',
            'redirect_uris' => ['https://chatgpt.com/connector_platform_oauth_redirect'],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['client_id', 'redirect_uris']);
    }

    public function test_superadmin_sees_all_17_tools_with_output_schema(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'account_version' => 1]);
        [$token] = $this->createTokenFor($superadmin);

        $response = $this->withToken($token)->postJson('/mcp/kamerakita', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ]);

        $response->assertOk();
        $tools = $response->json('result.tools');

        $this->assertIsArray($tools);
        $this->assertCount(17, $tools);

        foreach ($tools as $tool) {
            $this->assertArrayHasKey('name', $tool);
            $this->assertArrayHasKey('inputSchema', $tool);
            $this->assertArrayHasKey('outputSchema', $tool);
            $this->assertFalse($tool['outputSchema']['additionalProperties'] ?? true);
        }
    }

    public function test_finance_role_only_sees_read_tools_dynamically(): void
    {
        $finance = User::factory()->create(['role' => 'finance', 'account_version' => 1]);
        [$token] = $this->createTokenFor($finance);

        $response = $this->withToken($token)->postJson('/mcp/kamerakita', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
        ]);

        $response->assertOk();
        $toolNames = collect($response->json('result.tools'))->pluck('name')->all();

        $this->assertContains('search_partner', $toolNames);
        $this->assertContains('qc_stats', $toolNames);
        $this->assertNotContains('create_worker', $toolNames);
        $this->assertNotContains('soft_delete_email', $toolNames);
        $this->assertNotContains('send_wa', $toolNames);
    }

    public function test_tools_call_executes_and_writes_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Budi Admin', 'account_version' => 1]);
        [$token, $connection] = $this->createTokenFor($admin);

        $response = $this->withToken($token)->postJson('/mcp/kamerakita', [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => 'search_partner',
                'arguments' => [
                    'keyword' => 'Budi',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.ok', true)
            ->assertJsonPath('result.structuredContent.tool', 'search_partner')
            ->assertJsonPath('result.structuredContent.simulated', false)
            ->assertJsonPath('result.structuredContent.meta.role', 'admin');

        $this->assertDatabaseHas(McpAuditLog::class, [
            'user_id' => $admin->id,
            'mcp_connection_id' => $connection->id,
            'tool_name' => 'search_partner',
            'status' => 'success',
        ]);
    }

    public function test_revoked_or_stale_account_version_token_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'account_version' => 1]);
        [$token, $connection] = $this->createTokenFor($admin);

        $admin->forceFill(['account_version' => 2])->save();

        $this->withToken($token)->postJson('/mcp/kamerakita', [
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'tools/list',
        ])->assertStatus(401);

        $admin->forceFill(['account_version' => 1])->save();
        $connection->revoke();

        $this->withToken($token)->postJson('/mcp/kamerakita', [
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/list',
        ])->assertStatus(401);
    }

    public function test_admin_can_manage_and_reveal_mcp_connections_in_ui(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'account_version' => 1]);

        $this->actingAs($admin)
            ->get(route('admin.integrations.mcp.index'))
            ->assertOk()
            ->assertSee('Integrasi MCP');

        $store = $this->actingAs($admin)->post(route('admin.integrations.mcp.store'), [
            'name' => 'Claude Code',
            'client_type' => 'claude_code',
        ]);

        $store->assertRedirect(route('admin.integrations.mcp.index'));
        $connection = McpConnection::where('user_id', $admin->id)->firstOrFail();

        $reveal = $this->actingAs($admin)->postJson(route('admin.integrations.mcp.reveal', $connection));
        $reveal->assertOk()->assertJsonStructure(['token']);
        $this->assertStringContainsString('no-store', (string) $reveal->headers->get('Cache-Control'));

        $this->actingAs($admin)
            ->delete(route('admin.integrations.mcp.destroy', $connection))
            ->assertRedirect(route('admin.integrations.mcp.index'));

        $connection->refresh();
        $this->assertNotNull($connection->revoked_at);
        $this->assertNull($connection->token_hash);
        $this->assertNull($connection->token_encrypted);
    }
}
