<header class="h-16 flex items-center justify-between px-8 sticky top-0 z-10 bg-transparent">
    <div class="flex items-center gap-4">
        <!-- Sidebar Toggle (Mobile) -->
        <button class="p-1.5 rounded-xl text-gray-500 hover:bg-gray-100 md:hidden" onclick="
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            if (sidebar.classList.contains('-translate-x-full')) {
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
            } else {
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-100', 'pointer-events-auto');
            }
        ">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        @if(isset($header))
            {{ $header }}
        @else
            <h2 class="font-bold text-lg text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        @endif
    </div>

    <div></div>
</header>
