<header class="min-h-16 flex items-start sm:items-center px-4 py-3 sm:px-6 lg:px-8 sticky top-0 z-20 bg-[#f8f8f6]/95 backdrop-blur-md border-b border-gray-200/70" style="z-index: 20">
    <div class="flex items-start sm:items-center gap-3 w-full min-w-0">
        <!-- Sidebar Toggle (Mobile) -->
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

        <div class="flex-1 min-w-0 pt-1.5 sm:pt-0">
            @if(isset($header))
                {{ $header }}
            @else
                <h2 class="font-bold text-lg text-gray-800 leading-tight truncate">
                    {{ __('Ringkasan') }}
                </h2>
            @endif
        </div>
    </div>
</header>
