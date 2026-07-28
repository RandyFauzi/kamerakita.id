<!-- Mobile Sidebar Overlay Backdrop -->
<div class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs z-30 md:hidden transition-opacity duration-300 opacity-0 pointer-events-none" id="sidebar-overlay" style="z-index: 30" onclick="
    document.getElementById('sidebar').classList.add('-translate-x-full');
    this.classList.add('opacity-0', 'pointer-events-none');
    this.classList.remove('opacity-100', 'pointer-events-auto');
    document.body.classList.remove('overflow-hidden');
"></div>

<div class="fixed inset-y-0 left-0 z-40 w-64 max-w-[85vw] bg-white border-r border-gray-200/60 flex flex-col justify-between transition-transform duration-300 transform md:translate-x-0 -translate-x-full shadow-xl md:shadow-none" id="sidebar" style="z-index: 40; width: min(16rem, 85vw)">
    <div class="flex flex-col">
        <!-- Logo Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-gray-100 overflow-hidden">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-lg overflow-hidden shrink-0 flex items-center justify-center bg-white">
                    <img src="{{ asset('images/Logo.webp') }}" alt="Kamerakita.ai" class="max-h-8 max-w-8 object-contain">
                </span>
                <span class="whitespace-nowrap text-sm font-black tracking-[-0.02em] text-slate-950">KameraKita<span class="ml-0.5 bg-gradient-to-r from-sky-500 to-indigo-600 bg-clip-text text-transparent">AI</span></span>
            </a>
            <!-- Mobile Close Button (X) -->
            <button type="button" aria-label="Tutup menu navigasi" class="w-10 h-10 shrink-0 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-50 md:hidden focus:outline-none" onclick="
                document.getElementById('sidebar').classList.add('-translate-x-full');
                const overlay = document.getElementById('sidebar-overlay');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                document.body.classList.remove('overflow-hidden');
            ">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1" onclick="
            if (event.target.closest('a')) {
                document.getElementById('sidebar').classList.add('-translate-x-full');
                const overlay = document.getElementById('sidebar-overlay');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                document.body.classList.remove('overflow-hidden');
            }
        ">
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ringkasan
            </a>

            @if(Auth::user()->canAccessQcRoom())
                <a href="{{ route('video-submissions.qc-room') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('video-submissions.qc-room') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('video-submissions.qc-room') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Verifikasi Laporan
                </a>
            @endif

            @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'finance']))
                <a href="{{ route('payments.manage') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('payments.*') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('payments.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Pembayaran Gaji
                </a>
            @endif

            @if(Auth::user()->hasFullAdminAccess())
                <a href="{{ route('partners.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('partners.*') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('partners.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Mitra & Pekerja
                </a>

                <a href="{{ route('activation-codes.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('activation-codes.*') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('activation-codes.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Kode Aktivasi
                </a>

                <a href="{{ route('admin.onboardings.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.onboardings.*') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.onboardings.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/>
                    </svg>
                    Pendaftar Fastwork
                </a>

                <a href="{{ route('admin-users.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin-users.*') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin-users.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3M13 7a4 4 0 11-8 0 4 4 0 018 0zM3 21v-1a6 6 0 0112 0v1H3z"/>
                    </svg>
                    Admin
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
                    Kirim Laporan
                </a>
            @endif
            @if($partner && in_array($partner->partner_role, ['worker', 'mitra'], true))
                <a href="{{ route('video-submissions.report-history') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('video-submissions.report-history') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('video-submissions.report-history') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Riwayat Laporan
                </a>
                <a href="{{ route('video-submissions.payment-history') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('video-submissions.payment-history') ? 'bg-indigo-50/80 text-indigo-750' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('video-submissions.payment-history') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Riwayat Gaji
                </a>
            @endif

            {{-- Rekruter: link ke dashboard tim --}}
            @if($partner && $partner->partner_role === 'rekruter')
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 text-slate-500 hover:bg-slate-50 hover:text-slate-900">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Kode Referral & Tim
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
            <a href="{{ route('profile.edit') }}" class="overflow-hidden">
                <span class="block text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</span>
                <span class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider">
                    @if($partner && $partner->partner_role === 'worker')
                        Kontributor
                    @elseif($partner && $partner->partner_role === 'mitra')
                        Mitra (Koordinator)
                    @elseif($partner && $partner->partner_role === 'rekruter')
                        Rekruter
                    @else
                        {{ Auth::user()->role }}
                    @endif
                </span>
            </a>
        </div>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-650 hover:bg-red-50 rounded-lg transition-colors" title="Keluar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</div>
