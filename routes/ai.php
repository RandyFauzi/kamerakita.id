<?php

use App\Http\Middleware\AuditKamerakitaMcp;
use App\Http\Middleware\AuthenticateKamerakitaMcp;
use App\Mcp\Servers\KamerakitaServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

$rateLimit = (int) config('mcp.rate_limit', 120);

Mcp::web('/mcp/kamerakita', KamerakitaServer::class)
    ->middleware([
        AuditKamerakitaMcp::class,
        'throttle:' . $rateLimit . ',1',
        AuthenticateKamerakitaMcp::class,
    ]);

if (config('mcp.oauth_enabled', true)) {
    $authServerMetadata = static fn (): array => array_filter([
        'issuer' => config('mcp.authorization_server') ?? url('/'),
        'authorization_response_iss_parameter_supported' => true,
        'authorization_endpoint' => route('passport.authorizations.authorize'),
        'token_endpoint' => route('passport.token'),
        'registration_endpoint' => Route::has('mcp.oauth.register') ? route('mcp.oauth.register') : url('/oauth/register'),
        'response_types_supported' => ['code'],
        'code_challenge_methods_supported' => ['S256'],
        'scopes_supported' => ['mcp:use'],
        'grant_types_supported' => ['authorization_code', 'refresh_token'],
        'token_endpoint_auth_methods_supported' => ['none', 'client_secret_basic', 'client_secret_post'],
    ], fn (mixed $value): bool => $value !== null);

    Route::get('/.well-known/oauth-authorization-server', static fn () => response()->json($authServerMetadata()))
        ->name('mcp.oauth.authorization-server');

    Route::get('/.well-known/oauth-authorization-server/{path}', static fn () => response()->json($authServerMetadata()))
        ->where('path', '.*')
        ->name('mcp.oauth.authorization-server.nested');

    Mcp::oauthRoutes();
}
