<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\McpAuditLog;

class McpAuditLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $executionTime = (microtime(true) - $startTime) * 1000; // in milliseconds

        $this->logRequest($request, $response, $executionTime);

        return $response;
    }

    private function logRequest(Request $request, Response $response, float $executionTime)
    {
        $client = $request->attributes->get('mcp_client');
        $clientName = $client['name'] ?? 'Unknown/Unauthenticated';

        $method = $request->input('method', 'unknown');
        
        // Don't log basic initialized pings
        if (in_array($method, ['initialize', 'notifications/initialized', 'tools/list'])) {
            return;
        }

        $toolName = null;
        $payload = null;

        if ($method === 'tools/call') {
            $toolName = $request->input('params.name');
            $payload = $request->input('params.arguments');
        }

        $status = 'success';
        $errorMessage = null;

        if ($response->getStatusCode() >= 400) {
            $status = 'error';
            // Try to extract JSON-RPC error message
            $responseData = json_decode($response->getContent(), true);
            $errorMessage = $responseData['error']['message'] ?? 'HTTP ' . $response->getStatusCode();
        }

        try {
            McpAuditLog::create([
                'client_name' => $clientName,
                'method' => $method,
                'tool_name' => $toolName,
                'payload' => $payload,
                'status' => $status,
                'error_message' => $errorMessage,
                'execution_time_ms' => $executionTime,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to write MCP Audit Log: ' . $e->getMessage());
        }
    }
}
