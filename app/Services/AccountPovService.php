<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AccountPovService
{
    public const SESSION_IMPERSONATOR_ID = 'account_pov.impersonator_id';
    public const SESSION_RETURN_URL = 'account_pov.return_url';

    private static bool $suppressAuthEvents = false;

    public static function isSuppressingAuthEvents(): bool
    {
        return self::$suppressAuthEvents;
    }

    /**
     * Resolve the authorized superadmin actor even while viewing another user's POV.
     */
    public function resolveActor(?Request $request = null): ?User
    {
        $request ??= request();

        if ($request->hasSession() && $request->session()->has(self::SESSION_IMPERSONATOR_ID)) {
            $impersonator = User::find($request->session()->get(self::SESSION_IMPERSONATOR_ID));

            return $impersonator && $impersonator->canUseAccountPov() ? $impersonator : null;
        }

        $user = $request->user() ?? Auth::user();

        return $user instanceof User && $user->canUseAccountPov() ? $user : null;
    }

    /**
     * Abort with 403 if the current session does not belong to the authorized POV owner.
     */
    public function authorizeActor(Request $request): User
    {
        $actor = $this->resolveActor($request);

        if (! $actor) {
            if ($request->hasSession()) {
                $request->session()->forget([self::SESSION_IMPERSONATOR_ID, self::SESSION_RETURN_URL]);
            }

            abort(403, 'Akses Mode POV hanya tersedia untuk akun superadmin utama.');
        }

        return $actor;
    }

    public function canCurrentSessionUsePov(?Request $request = null): bool
    {
        return $this->resolveActor($request) !== null;
    }

    public function isImpersonating(?Request $request = null): bool
    {
        $request ??= request();

        if (! Auth::check() || ! $request->hasSession() || ! $request->session()->has(self::SESSION_IMPERSONATOR_ID)) {
            return false;
        }

        $actor = $this->resolveActor($request);

        return $actor !== null && (string) Auth::id() !== (string) $actor->id;
    }

    /**
     * Enter target user's POV without polluting the target user's login activity log.
     */
    public function enter(Request $request, User $target): User
    {
        $actor = $this->authorizeActor($request);

        if ((string) $target->id === (string) $actor->id) {
            if ($this->isImpersonating($request)) {
                $this->leave($request);
            }

            return $actor;
        }

        if (! $request->session()->has(self::SESSION_IMPERSONATOR_ID)) {
            $request->session()->put(self::SESSION_IMPERSONATOR_ID, $actor->id);
            $request->session()->put(
                self::SESSION_RETURN_URL,
                $this->sanitizeReturnUrl($request->input('return_url') ?: url()->previous())
            );
        }

        $this->switchGuardUser($target);

        ActivityLogger::logAs(
            $actor->id,
            'account_pov.enter',
            "Superadmin {$actor->email} membuka Mode POV akun {$target->name} ({$target->email}, role: {$target->role})."
        );

        return $target;
    }

    /**
     * Restore the original superadmin session and return metadata for redirect.
     *
     * @return array{actor: User, return_url: string, left_from: ?User}
     */
    public function leave(Request $request): array
    {
        $actor = $this->authorizeActor($request);
        $leftFrom = Auth::user();
        $returnUrl = $this->sanitizeReturnUrl($request->session()->get(self::SESSION_RETURN_URL));

        $request->session()->forget([self::SESSION_IMPERSONATOR_ID, self::SESSION_RETURN_URL]);

        if (! $leftFrom || (string) $leftFrom->id !== (string) $actor->id) {
            $this->switchGuardUser($actor);

            $targetLabel = $leftFrom ? "{$leftFrom->name} ({$leftFrom->email})" : 'akun target';
            ActivityLogger::logAs(
                $actor->id,
                'account_pov.leave',
                "Superadmin {$actor->email} keluar dari Mode POV {$targetLabel}."
            );
        }

        return [
            'actor' => $actor,
            'return_url' => $returnUrl,
            'left_from' => $leftFrom,
        ];
    }

    /**
     * Search switchable accounts across users & linked partners cleanly.
     */
    public function searchAccounts(string $query = '', int $limit = 15): Collection
    {
        $search = trim($query);
        $currentId = Auth::id();

        $usersQuery = User::query()
            ->with(['partner:id,user_id,mitra_id,full_name,partner_role,status,group_name,whatsapp_number'])
            ->when($search !== '', function ($q) use ($search): void {
                $escaped = addcslashes($search, '%_\\');
                $like = "%{$escaped}%";

                $q->where(function ($sub) use ($like): void {
                    $sub->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('role', 'like', $like)
                        ->orWhereHas('partner', function ($partnerQuery) use ($like): void {
                            $partnerQuery->where('full_name', 'like', $like)
                                ->orWhere('mitra_id', 'like', $like)
                                ->orWhere('whatsapp_number', 'like', $like)
                                ->orWhere('partner_role', 'like', $like);
                        });
                });
            })
            ->orderByRaw('CASE WHEN LOWER(email) = ? THEN 1 ELSE 0 END ASC', [User::POV_OWNER_EMAIL])
            ->orderByDesc('updated_at')
            ->limit($limit);

        return $usersQuery->get()->map(function (User $user) use ($currentId): array {
            $partner = $user->partner;

            return [
                'id' => $user->id,
                'name' => $partner?->full_name ?: $user->name,
                'email' => $user->email,
                'role_label' => $this->formatRoleLabel($user, $partner),
                'mitra_id' => $partner?->mitra_id,
                'group_name' => $partner?->group_name,
                'status' => $partner?->status,
                'is_verified' => $user->email_verified_at !== null,
                'has_completed_onboarding' => (bool) $user->has_completed_onboarding,
                'is_current' => (string) $user->id === (string) $currentId,
                'enter_url' => route('account-pov.enter', $user),
            ];
        })->values();
    }

    public function formatRoleLabel(User $user, ?Partner $partner = null): string
    {
        $partner ??= $user->partner;

        if ($partner) {
            return match ($partner->partner_role) {
                Partner::ROLE_WORKER => 'Worker',
                Partner::ROLE_MITRA => 'Mitra',
                Partner::ROLE_MAIN_ACCOUNT => 'Main Account',
                Partner::ROLE_REKRUTER => 'Rekruter',
                default => ucfirst((string) $partner->partner_role),
            };
        }

        return match ($user->role) {
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'finance' => 'Finance',
            default => ucfirst((string) $user->role),
        };
    }

    private function switchGuardUser(User $user): void
    {
        self::$suppressAuthEvents = true;

        try {
            Auth::guard('web')->login($user, false);
        } finally {
            self::$suppressAuthEvents = false;
        }
    }

    private function sanitizeReturnUrl(?string $candidate): string
    {
        $fallback = route('dashboard');

        if (! is_string($candidate) || trim($candidate) === '') {
            return $fallback;
        }

        $parsed = parse_url($candidate);
        if (! is_array($parsed)) {
            return $fallback;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $requestHost = request()->getHost();
        if (! empty($parsed['host']) && ! in_array($parsed['host'], array_filter([$appHost, $requestHost]), true)) {
            return $fallback;
        }

        $path = $parsed['path'] ?? '/';
        if (! str_starts_with($path, '/') || str_starts_with($path, '/account-pov')) {
            return $fallback;
        }

        $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';

        return $path . $query;
    }
}
