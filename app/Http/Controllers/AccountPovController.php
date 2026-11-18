<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountPovService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountPovController extends Controller
{
    public function search(Request $request, AccountPovService $pov): JsonResponse
    {
        $pov->authorizeActor($request);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json([
            'accounts' => $pov->searchAccounts((string) ($validated['q'] ?? ''), 15),
        ]);
    }

    public function enter(Request $request, User $user, AccountPovService $pov): RedirectResponse
    {
        $actor = $pov->authorizeActor($request);

        if ((string) $user->id === (string) $actor->id && ! $pov->isImpersonating($request)) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Anda sudah berada di akun Super Admin utama.');
        }

        $target = $pov->enter($request, $user);
        $displayName = $target->partner?->full_name ?: $target->name;

        return redirect()
            ->route('dashboard')
            ->with('success', "Mode POV aktif: melihat tampilan akun {$displayName} ({$target->email}).");
    }

    public function leave(Request $request, AccountPovService $pov): RedirectResponse
    {
        $pov->authorizeActor($request);

        if (! $pov->isImpersonating($request)) {
            return redirect()->route('dashboard');
        }

        $result = $pov->leave($request);

        return redirect()
            ->to($result['return_url'])
            ->with('success', 'Berhasil keluar dari Mode POV dan kembali ke akun Super Admin.');
    }
}
