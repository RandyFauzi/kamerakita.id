@props(['variant' => 'bar'])

@php
    $povService = app(\App\Services\AccountPovService::class);
    $isImpersonating = $povService->isImpersonating();
@endphp

@if($isImpersonating)
    @php
        $activeUser = Auth::user();
        $activePartner = $activeUser?->partner;
        $displayName = $activePartner?->full_name ?: ($activeUser?->name ?? '-');
        $roleLabel = $activeUser ? $povService->formatRoleLabel($activeUser, $activePartner) : '-';
        $needsOnboarding = $activeUser
            && in_array($activeUser->role, ['worker', 'partner', 'vendor'], true)
            && ! $activeUser->has_completed_onboarding;
    @endphp

    @if($variant === 'floating')
        <div class="fixed bottom-3 left-1/2 -translate-x-1/2 z-[90] w-[calc(100%-1.5rem)] max-w-xl">
            <div class="bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-amber-400/40 px-4 py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="relative flex h-2.5 w-2.5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] font-extrabold tracking-wider uppercase px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300 border border-amber-400/30">Mode POV</span>
                            <span class="text-xs sm:text-sm font-bold truncate">{{ $displayName }}</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-white/10 text-slate-200">{{ $roleLabel }}</span>
                        </div>
                        <p class="text-[11px] text-slate-300 truncate">{{ $activeUser->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('account-pov.leave') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-extrabold transition shadow-sm">
                        Keluar POV
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="bg-slate-900 text-white border-b border-amber-400/35 px-3 sm:px-6 py-2">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <span class="relative flex h-2.5 w-2.5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                    </span>
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0 text-xs">
                        <span class="font-extrabold tracking-wider uppercase px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300 border border-amber-400/30">Mode POV</span>
                        <span class="font-bold text-white truncate max-w-[180px] sm:max-w-xs">{{ $displayName }}</span>
                        <span class="hidden sm:inline text-slate-400">•</span>
                        <span class="text-slate-300 truncate max-w-[200px] sm:max-w-xs">{{ $activeUser->email }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">{{ $roleLabel }}</span>
                        @if($activePartner?->mitra_id)
                            <span class="hidden md:inline px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white/10 text-slate-200">{{ $activePartner->mitra_id }}</span>
                        @endif
                        @if($needsOnboarding)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/25 text-amber-200 border border-amber-400/30">Belum Onboarding</span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('account-pov.leave') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-extrabold transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar POV</span>
                    </button>
                </form>
            </div>
        </div>
    @endif
@endif
