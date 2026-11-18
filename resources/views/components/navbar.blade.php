@php
    $partner = \App\Models\Partner::where('user_id', Auth::id())->first();
    $isUser = $partner && $partner->hasStandardPortalAccess();
    $canUseAccountPov = app(\App\Services\AccountPovService::class)->canCurrentSessionUsePov();
@endphp
<header class="min-h-16 flex items-start sm:items-center px-4 py-3 sm:px-6 lg:px-8 bg-[#f8f8f6]/95 backdrop-blur-md border-b border-gray-200/70">
    <div class="flex items-start sm:items-center gap-3 w-full min-w-0">
        <!-- Sidebar Toggle (Mobile) -->
        @if(!$isUser)
        <button type="button" aria-label="Buka menu navigasi" class="w-11 h-11 shrink-0 inline-flex items-center justify-center rounded-xl text-gray-600 hover:bg-gray-100 md:hidden" onclick="
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            if (sidebar.classList.contains('-translate-x-full')) {
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                document.body.classList.remove('overflow-hidden');
            } else {
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-100', 'pointer-events-auto');
                document.body.classList.add('overflow-hidden');
            }
        ">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        @endif

        <div class="flex-1 min-w-0 pt-1.5 sm:pt-0">
            @if(isset($header))
                {{ $header }}
            @else
                <h2 class="font-bold text-lg text-gray-800 leading-tight truncate">
                    {{ __('dashboard.sidebar.summary') }}
                </h2>
            @endif
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($canUseAccountPov)
                <button
                    type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-account-pov-modal'))"
                    title="Buka Mode POV Akun (Ctrl+Shift+P)"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/80 text-xs font-bold transition shadow-xs"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="hidden sm:inline">Mode POV</span>
                    <span class="sm:hidden">POV</span>
                </button>
            @endif
            <x-language-switcher />
        </div>
    </div>
</header>
