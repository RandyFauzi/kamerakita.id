<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateStudioIntegration
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (string) config('services.studio_integration.token');
        $provided = (string) $request->bearerToken();

        abort_if($configured === '' || $provided === '' || ! hash_equals($configured, $provided), 401, 'Kredensial integrasi tidak valid.');

        return $next($request);
    }
}
