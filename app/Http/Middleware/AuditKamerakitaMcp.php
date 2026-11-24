<?php

namespace App\Http\Middleware;

use App\Models\McpAuditLog;
use App\Models\McpConnection;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditKamerakitaMcp
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $executionTime = (microtime(true) - $startTime) * 1000;

        $this->logRequest($request, $response, $executionTime);

        return $response;
    }

    private function logRequest(Request $request, Response $response, float $executionTime): void
    {
        $method = (string) $request->input('method', 'unknown');

        if (in_array($method, ['initialize', 'notifications/initialized', 'tools/list', 'ping'], true)) {
            return;
        }

        $client = $request->attributes->get('mcp_client');
        $connection = $request->attributes->get('mcp_connection');
        $user = $request->user();

        $clientName = is_array($client)
            ? ($client['name'] ?? 'Unknown')
            : ($user ? ($user->name . ' (' . $user->email . ')') : 'Unknown/Unauthenticated');

        $toolName = null;
        $payload = null;

        if ($method === 'tools/call') {
            $toolName = $request->input('params.name');
            $rawPayload = $request->input('params.arguments');
            $payload = is_array($rawPayload) ? $this->sanitizePayload($rawPayload) : null;
        }

        $status = 'success';
        $errorMessage = null;

        if ($response->getStatusCode() >= 400) {
            $status = 'error';
            $responseData = json_decode((string) $response->getContent(), true);
            $errorMessage = $responseData['error']['message'] ?? ('HTTP ' . $response->getStatusCode());
        }

        try {
            McpAuditLog::create([
                'user_id' => $user?->id,
                'mcp_connection_id' => $connection instanceof McpConnection ? $connection->id : null,
                'client_name' => $clientName,
                'method' => $method,
                'tool_name' => $toolName,
                'payload' => $payload,
                'status' => $status,
                'error_message' => $errorMessage,
                'execution_time_ms' => $executionTime,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to write MCP Audit Log: ' . $e->getMessage());
        }
    }

    private function sanitizePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($key) && preg_match('/password|secret|token|key/i', $key)) {
                $payload[$key] = '***';
            } elseif (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
            }
        }

        return $payload;
    }
}
