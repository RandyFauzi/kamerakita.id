<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class McpAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->input('id'),
                'error' => [
                    'code' => -32000,
                    'message' => 'Missing Bearer token.'
                ]
            ], 401);
        }

        $clients = config('mcp.clients', []);

        if (!array_key_exists($token, $clients)) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->input('id'),
                'error' => [
                    'code' => -32000,
                    'message' => 'Invalid MCP token. Connection rejected.'
                ]
            ], 401);
        }

        // Inject client data into request for downstream use (authorization and logging)
        $request->attributes->set('mcp_client', $clients[$token]);

        return $next($request);
    }
}
