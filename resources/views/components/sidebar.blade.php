<div class="fixed inset-y-0 left-0 z-20 w-64 bg-white border-r border-gray-200/60 flex flex-col justify-between transition-all duration-300 transform md:translate-x-0 -translate-x-full" id="sidebar">
    <div class="flex flex-col">
        <!-- Logo Header -->
        <div class="h-16 flex items-center px-6 border-b border-gray-100 gap-2.5">
            <!-- Modern Letter "A" Icon matching Airtm logo style -->
            <div class="w-8 h-8 rounded-xl bg-gray-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4L4 20h3l2-5h6l2 5h3L12 4zm-1.5 8.5L12 8l1.5 4.5h-3z"/>
                </svg>
            </div>
            <span class="text-gray-900 font-black text-lg tracking-wider">Kamerakita<span class="text-indigo-600">.ai</span></span>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard Overview
            </a>

            @if(Auth::user()->canAccessQcRoom())
                <a href="{{ route('video-submissions.qc-room') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('video-submissions.qc-room') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('video-submissions.qc-room') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    QC Video Submissions
                </a>
            @endif

            @if(Auth::user()->hasFullAdminAccess())
                <a href="{{ route('partners.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('partners.*') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('partners.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Kelola Mitra & Worker
                </a>
            @endif

            @php
                $partner = \App\Models\Partner::where('user_id', Auth::id())->first();
            @endphp
            @if($partner && $partner->partner_role === 'worker')
                <a href="{{ route('video-submissions.submit-report.create') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('video-submissions.submit-report.create') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('video-submissions.submit-report.create') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Kirim Laporan Video
                </a>
            @endif
        </nav>
    </div>

    <!-- User Profile Footer -->
    <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
            <!-- Mock Avatar matching Airtm logo/circle style -->
            <div class="w-9 h-9 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <span class="block text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</span>
                <span class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider">{{ Auth::user()->role }}</span>
            </div>
        </div>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-650 hover:bg-red-50 rounded-lg transition-colors" title="Log Out">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</div>
