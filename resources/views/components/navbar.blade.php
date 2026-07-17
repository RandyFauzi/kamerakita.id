<header class="h-16 flex items-center justify-between px-8 sticky top-0 z-10 bg-transparent">
    <div class="flex items-center gap-4">
        <!-- Sidebar Toggle (Mobile) -->
        <button class="p-1.5 rounded-xl text-gray-500 hover:bg-gray-100 md:hidden" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')">
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

    <!-- Right Area: Icons (Eye, Notification Bell) matching Airtm style -->
    <div class="flex items-center gap-4">
        <!-- View (Eye) Icon -->
        <button class="p-2 text-gray-600 hover:bg-gray-150 rounded-full transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </button>

        <!-- Notification Bell -->
        <button class="p-2 text-gray-600 hover:bg-gray-150 rounded-full transition relative">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-indigo-600 rounded-full border-2 border-white"></span>
        </button>
    </div>
</header>
