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
                // Jika worker ini berada di bawah naungan Mitra, pembayaran dilakukan via Mitra,
                // sehingga rekening otomatis mengikuti Mitra dan worker tidak perlu mengisi form onboarding rekening.
                $partner = $user->partner;
                if ($partner && $partner->mitra_parent_id && $partner->mitraParent) {
                    $mitra = $partner->mitraParent;
                    
                    if (empty($partner->bank_account_number) && !empty($mitra->bank_account_number)) {
                        $partner->update([
                            'bank_name' => $mitra->bank_name,
                            'bank_account_number' => $mitra->bank_account_number,
                            'bank_account_owner' => $mitra->bank_account_owner,
                            'payment_method' => $mitra->payment_method ?: 'bank_transfer',
                            'airtm_username' => $mitra->airtm_username,
                        ]);
                    }

                    $user->update([
                        'has_completed_onboarding' => true,
                        'tos_accepted_at' => $user->tos_accepted_at ?: now(),
                    ]);

                    return $next($request);
                }

                // Hindari infinite redirect jika sudah berada di halaman onboarding
                if (!$request->is('get-started')) {
                    return redirect()->route('onboarding.start');
                }
            }
        }

        return $next($request);
    }
}
