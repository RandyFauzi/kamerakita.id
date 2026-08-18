<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Cek jika user login, role adalah worker (atau nanti vendor), dan belum onboarding
        if ($user && in_array($user->role, ['worker', 'partner', 'vendor'])) {
            if (!$user->has_completed_onboarding) {
                // Hindari infinite redirect jika sudah berada di halaman onboarding
                if (!$request->is('get-started')) {
                    return redirect()->route('onboarding.start');
                }
            }
        }

        return $next($request);
    }
}
