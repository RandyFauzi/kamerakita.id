<x-app-layout>
    <x-slot:title>Dashboard Admin</x-slot:title>
    <x-slot:metaDescription>Pusat kontrol dan metrik global untuk administrator dan tim finance Kamerakita.</x-slot:metaDescription>
    
    <x-slot name="header">
        <div class="w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                {{ __('dashboard.general.admin_dashboard') }}
            </h2>
            <!-- Actions removed as requested -->
        </div>
    </x-slot>

    <div class="py-1 sm:py-4 space-y-4 sm:space-y-6">
        
        <!-- Greeting Header Card -->
        @php
            $adminUser = Auth::user();
            $roleLabel = strtoupper(str_replace('_', ' ', $adminUser?->role ?? 'admin'));
            $todayWita = now('Asia/Makassar')->locale(app()->getLocale())->translatedFormat('l, d F Y');
        @endphp
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shrink-0 font-bold text-lg">
                    {{ strtoupper(mb_substr($adminUser?->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">
                            Halo, {{ $adminUser?->name ?? 'Admin' }}
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-blue-50 text-blue-600 whitespace-nowrap">
                            {{ $roleLabel }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500">
                        {{ $todayWita }} &middot; Ringkasan progres target produksi dan aktivitas tim hari ini.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto shrink-0">
                <a href="{{ route('admin.recordings.index') }}" class="w-full md:w-auto min-h-11 inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-blue-700 transition whitespace-nowrap">
                    Buka Recording
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-100 flex items-center gap-2" role="alert">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-100 flex items-center gap-2" role="alert">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 text-sm text-blue-800 rounded-xl bg-blue-50 border border-blue-100 flex items-center gap-2" role="alert">
                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">{{ session('info') }}</span>
            </div>
        @endif

        <!-- Dynamic Holographic Card & Info Balance -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Left: Hero Global Approved Card (2 cols) -->
            <div class="lg:col-span-2 relative overflow-hidden rounded-2xl sm:rounded-[32px] bg-gradient-to-br from-blue-600 via-[#3b82f6] to-[#0ea5e9] p-5 sm:p-8 text-white shadow-lg shadow-blue-500/20 border border-blue-400/30 flex flex-col justify-between gap-6">
                <!-- Bright Ambient Glows -->
                <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-cyan-300/30 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-16 w-64 h-64 rounded-full bg-white/15 blur-3xl pointer-events-none"></div>
                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/50 to-transparent pointer-events-none"></div>

                <!-- Top Section: Title, Live Context & Main Metric -->
                <div class="relative z-10 flex flex-col gap-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-blue-100 font-mono">
                                <span class="w-2 h-2 rounded-full bg-emerald-300 shadow-[0_0_8px_rgba(110,231,183,0.9)]"></span>
                                {{ __('dashboard.general.global_all_time_approved') }}
                            </span>
                            <div class="flex flex-wrap items-center gap-3 mt-3">
                                <span class="text-4xl sm:text-5xl font-black tracking-tight text-white tabular-nums drop-shadow-sm">{{ $metrics['global_all_time_hours_formatted'] }}</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 border border-white/30 backdrop-blur-sm text-[11px] font-bold uppercase tracking-wider text-white font-mono">
                                    <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ __('dashboard.general.approved') }}
                                </span>
                            </div>
                            <span class="block text-xs text-blue-50/90 font-medium mt-2">{{ __('dashboard.general.total_duration_desc') }}</span>
                        </div>

                        @if(isset($metrics['total_workers'], $metrics['total_mitra']))
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 border border-white/25 backdrop-blur-sm text-[11px] font-semibold text-white">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                            <span>{{ number_format($metrics['total_workers']) }} Workers · {{ number_format($metrics['total_mitra']) }} Mitra</span>
                        </div>
                        @endif
                    </div>

                    <!-- Middle Section: Queue Status KPI Tiles -->
                    <div class="pt-4 border-t border-white/20">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-blue-100 font-mono">{{ __('dashboard.general.queues') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Pending Tile -->
                            <div class="rounded-2xl bg-white/15 hover:bg-white/20 border border-white/25 backdrop-blur-sm px-4 py-3 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-100 font-mono">{{ __('dashboard.general.pending') }}</span>
                                    <span class="w-2 h-2 rounded-full bg-amber-300 ring-4 ring-amber-300/25"></span>
                                </div>
                                <p class="mt-2 text-lg sm:text-xl font-extrabold text-white tabular-nums tracking-tight">{{ $metrics['global_pending_submitted_hours_formatted'] }}</p>
                            </div>

                            <!-- On Review Tile -->
                            <div class="rounded-2xl bg-white/15 hover:bg-white/20 border border-white/25 backdrop-blur-sm px-4 py-3 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-100 font-mono">{{ __('dashboard.general.on_review') }}</span>
                                    <span class="w-2 h-2 rounded-full bg-cyan-200 ring-4 ring-cyan-200/25"></span>
                                </div>
                                <p class="mt-2 text-lg sm:text-xl font-extrabold text-white tabular-nums tracking-tight">{{ $metrics['global_on_review_submitted_hours_formatted'] }}</p>
                            </div>

                            <!-- Rejected Tile -->
                            <div class="rounded-2xl bg-white/15 hover:bg-white/20 border border-white/25 backdrop-blur-sm px-4 py-3 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-100 font-mono">{{ __('dashboard.general.rejected') }}</span>
                                    <span class="w-2 h-2 rounded-full bg-rose-300 ring-4 ring-rose-300/25"></span>
                                </div>
                                <p class="mt-2 text-lg sm:text-xl font-extrabold text-white tabular-nums tracking-tight">{{ $metrics['global_rejected_submitted_hours_formatted'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section: Action Buttons -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 relative z-10">
                    <a href="{{ route('admin.recordings.index') }}" class="flex items-center justify-center gap-2 py-3.5 px-4 bg-white hover:bg-blue-50 text-blue-700 font-bold text-xs rounded-2xl shadow-sm transition">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                        <span>Recording</span>
                    </a>
                    <a href="{{ route('payments.manage') }}" class="flex items-center justify-center gap-2 py-3.5 px-4 bg-white/15 hover:bg-white/25 text-white border border-white/25 backdrop-blur-sm font-bold text-xs rounded-2xl transition">
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Pembayaran</span>
                    </a>
                </div>
            </div>

            <!-- Right: Target Produksi Video Card (1 col) - TARGET MINGGUAN & BULANAN -->
            <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-7 border border-gray-150 shadow-sm flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">{{ __('dashboard.general.video_production_target') }}</span>
                        <span class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full font-mono flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Approved Video
                        </span>
                    </div>

                    <!-- {{ __('dashboard.general.daily_target') }} (450 Jam) -->
                    <div class="space-y-1.5 mb-3">
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-xs font-bold text-slate-800">{{ __('dashboard.general.daily_target') }}</span>
                                <span class="text-[10px] text-slate-400 font-medium block">{{ __('dashboard.general.period') }}: {{ $metrics['daily_period_label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-slate-900">{{ $metrics['daily_approved_hours'] }} <span class="text-xs text-slate-400 font-normal">/ {{ number_format($metrics['daily_target_hours'], 0, ',', '.') }} Jam</span></span>
                                <div class="flex items-center justify-end gap-2 mt-0.5">
                                    <span class="text-[10px] text-blue-500 font-medium">Submitted: {{ $metrics['daily_submitted_hours'] }} Jam ({{ $metrics['daily_submitted_percent'] }}%)</span>
                                    <span class="text-[10px] font-bold {{ $metrics['daily_progress_percent'] >= 100 ? 'text-emerald-600' : 'text-emerald-500' }} font-mono">Approved: {{ $metrics['daily_progress_percent'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <!-- Progress Bar Harian -->
                        <div class="w-full bg-slate-200/60 rounded-full h-3 overflow-hidden relative shadow-inner">
                            <!-- Layer 1: Submitted (Light Blue) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out"
                                 style="width: {{ min(100, $metrics['daily_submitted_percent']) }}%; background: linear-gradient(90deg, #93c5fd 0%, #3b82f6 100%); box-shadow: 0 0 10px rgba(59,130,246,0.5);"></div>

                            <!-- Layer 2: Approved (Green) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out delay-150"
                                 style="width: {{ min(100, $metrics['daily_progress_percent']) }}%; background: linear-gradient(90deg, #34d399 0%, #059669 100%); box-shadow: 0 0 10px rgba(16,185,129,0.5);"></div>
                        </div>
                    </div>

                    <!-- {{ __('dashboard.general.weekly_target') }} (2.000 Jam) -->
                    <div class="space-y-1.5 mb-3 pt-2.5 border-t border-slate-100">
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-xs font-bold text-slate-800">{{ __('dashboard.general.weekly_target') }}</span>
                                <span class="text-[10px] text-slate-400 font-medium block">{{ __('dashboard.general.period') }}: {{ $metrics['weekly_period_label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-slate-900">{{ $metrics['weekly_approved_hours'] }} <span class="text-xs text-slate-400 font-normal">/ {{ number_format($metrics['weekly_target_hours'], 0, ',', '.') }} Jam</span></span>
                                <div class="flex items-center justify-end gap-2 mt-0.5">
                                    <span class="text-[10px] text-blue-500 font-medium">Submitted: {{ $metrics['weekly_submitted_hours'] }} Jam ({{ $metrics['weekly_submitted_percent'] }}%)</span>
                                    <span class="text-[10px] font-bold {{ $metrics['weekly_progress_percent'] >= 100 ? 'text-emerald-600' : 'text-emerald-500' }} font-mono">Approved: {{ $metrics['weekly_progress_percent'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <!-- Progress Bar Mingguan -->
                        <div class="w-full bg-slate-200/60 rounded-full h-3 overflow-hidden relative shadow-inner">
                            <!-- Layer 1: Submitted (Light Blue) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out" 
                                 style="width: {{ min(100, $metrics['weekly_submitted_percent']) }}%; background: linear-gradient(90deg, #93c5fd 0%, #3b82f6 100%); box-shadow: 0 0 10px rgba(59,130,246,0.5);"></div>
                            
                            <!-- Layer 2: Approved (Green) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out delay-150" 
                                 style="width: {{ min(100, $metrics['weekly_progress_percent']) }}%; background: linear-gradient(90deg, #34d399 0%, #059669 100%); box-shadow: 0 0 10px rgba(16,185,129,0.5);"></div>
                        </div>
                    </div>

                    <!-- {{ __('dashboard.general.biweekly_target') }} (4.000 Jam) -->
                    <div class="space-y-1.5 mb-3 pt-2.5 border-t border-slate-100">
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-xs font-bold text-slate-800">{{ __('dashboard.general.biweekly_target') }}</span>
                                <span class="text-[10px] text-slate-400 font-medium block">{{ __('dashboard.general.period') }}: {{ $metrics['biweekly_period_label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-slate-900">{{ $metrics['biweekly_approved_hours'] }} <span class="text-xs text-slate-400 font-normal">/ {{ number_format($metrics['biweekly_target_hours'], 0, ',', '.') }} Jam</span></span>
                                <div class="flex items-center justify-end gap-2 mt-0.5">
                                    <span class="text-[10px] text-blue-500 font-medium">Submitted: {{ $metrics['biweekly_submitted_hours'] }} Jam ({{ $metrics['biweekly_submitted_percent'] }}%)</span>
                                    <span class="text-[10px] font-bold {{ $metrics['biweekly_progress_percent'] >= 100 ? 'text-emerald-600' : 'text-emerald-500' }} font-mono">Approved: {{ $metrics['biweekly_progress_percent'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <!-- Progress Bar Per 2 Minggu -->
                        <div class="w-full bg-slate-200/60 rounded-full h-3 overflow-hidden relative shadow-inner">
                            <!-- Layer 1: Submitted (Light Blue) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out"
                                 style="width: {{ min(100, $metrics['biweekly_submitted_percent']) }}%; background: linear-gradient(90deg, #93c5fd 0%, #3b82f6 100%); box-shadow: 0 0 10px rgba(59,130,246,0.5);"></div>

                            <!-- Layer 2: Approved (Green) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out delay-150"
                                 style="width: {{ min(100, $metrics['biweekly_progress_percent']) }}%; background: linear-gradient(90deg, #34d399 0%, #059669 100%); box-shadow: 0 0 10px rgba(16,185,129,0.5);"></div>
                        </div>
                    </div>

                    <!-- {{ __('dashboard.general.monthly_target') }} (8.000 Jam) -->
                    <div class="space-y-1.5 pt-2.5 border-t border-slate-100">
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-xs font-bold text-slate-800">{{ __('dashboard.general.monthly_target') }}</span>
                                <span class="text-[10px] text-slate-400 font-medium block">{{ __('dashboard.general.period') }}: {{ $metrics['monthly_period_label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-slate-900">{{ $metrics['monthly_approved_hours'] }} <span class="text-xs text-slate-400 font-normal">/ {{ number_format($metrics['monthly_target_hours'], 0, ',', '.') }} Jam</span></span>
                                <div class="flex items-center justify-end gap-2 mt-0.5">
                                    <span class="text-[10px] text-blue-500 font-medium">Submitted: {{ $metrics['monthly_submitted_hours'] }} Jam ({{ $metrics['monthly_submitted_percent'] }}%)</span>
                                    <span class="text-[10px] font-bold {{ $metrics['monthly_progress_percent'] >= 100 ? 'text-emerald-600' : 'text-emerald-500' }} font-mono">Approved: {{ $metrics['monthly_progress_percent'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <!-- Progress Bar Bulanan -->
                        <div class="w-full bg-slate-200/60 rounded-full h-3 overflow-hidden relative shadow-inner">
                            <!-- Layer 1: Submitted (Light Blue) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out" 
                                 style="width: {{ min(100, $metrics['monthly_submitted_percent']) }}%; background: linear-gradient(90deg, #93c5fd 0%, #3b82f6 100%); box-shadow: 0 0 10px rgba(59,130,246,0.5);"></div>
                            
                            <!-- Layer 2: Approved (Green) -->
                            <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out delay-150" 
                                 style="width: {{ min(100, $metrics['monthly_progress_percent']) }}%; background: linear-gradient(90deg, #34d399 0%, #059669 100%); box-shadow: 0 0 10px rgba(16,185,129,0.5);"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 mt-3 flex justify-between items-center text-[11px] text-slate-400">
                    <span>{{ __('dashboard.general.total_accumulated_verified') }}</span>
                    <span class="font-bold text-slate-700 font-mono">{{ $metrics['global_all_time_hours_formatted'] }}</span>
                </div>
            </div>
        </div>

        @php
            $monthlyMonths = $monthlyData->pluck('month')->map(function($m) {
                return \Carbon\Carbon::parse($m . '-01')->format('M Y');
            })->toArray();
            $monthlyApprovedHours = $monthlyData->pluck('approved_minutes')->map(function($m) {
                return round(($m ?? 0) / 60, 2); // Convert to hours
            })->toArray();
            $monthlySubmittedHours = $monthlyData->pluck('submitted_minutes')->map(function($m) {
                return round(($m ?? 0) / 60, 2); // Convert to hours
            })->toArray();
            
            $monthlySisaHours = [];
            foreach ($monthlySubmittedHours as $i => $sub) {
                $app = $monthlyApprovedHours[$i] ?? 0;
                $monthlySisaHours[] = round($sub - $app, 2);
            }

            $dailyDates = $dailyAverageData->pluck('submission_date')->map(function($d) {
                return \Carbon\Carbon::parse($d)->format('d M');
            })->toArray();
            $dailyAvgs = $dailyAverageData->map(function($item) {
                return round((float) ($item['peak_minutes'] ?? $item['avg_minutes'] ?? 0), 1);
            })->toArray();
        @endphp

        <!-- Analytics Section -->
        <div class="space-y-3">
            <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono">{{ __('dashboard.general.analysis_performance_chart') }}</span>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Monthly Performance Chart -->
                <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-6 border border-gray-150 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Perbandingan Jam Kerja (Kirim vs Verifikasi)</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Durasi terlapor dibandingkan yang disetujui per bulan</p>
                        </div>
                        <span class="bg-blue-50 text-blue-700 text-[10px] font-black px-2 py-0.5 rounded-md font-mono">Bulanan</span>
                    </div>
                    <div id="monthlyPerformanceChart" class="min-h-[280px]"></div>
                </div>

                <!-- Daily Highest Duration Chart -->
                <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-6 border border-gray-150 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Durasi Kerja Tertinggi Harian</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Durasi kerja tertinggi (menit) oleh worker per hari (7 hari terakhir)</p>
                        </div>
                        <span class="bg-indigo-50 text-indigo-700 text-[10px] font-black px-2 py-0.5 rounded-md font-mono">Harian</span>
                    </div>
                    <div id="dailyAverageChart" class="min-h-[280px]"></div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Monthly Chart
                const monthlyOptions = {
                    chart: {
                        type: 'bar',
                        stacked: true,
                        height: 280,
                        toolbar: { show: false },
                        parentHeightOffset: 0
                    },
                    colors: ['#3b82f6', '#e2e8f0'],
                    plotOptions: { 
                        bar: { 
                            columnWidth: '40%', 
                            borderRadius: 6, 
                            borderRadiusApplication: 'end' 
                        } 
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: false
                    },
                    series: [
                        { name: 'Approved', data: @js($monthlyApprovedHours) },
                        { name: 'Sisa Durasi', data: @js($monthlySisaHours) }
                    ],
                    xaxis: {
                        categories: @js($monthlyMonths),
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, sans-serif' }
                        },
                        axisBorder: { show: false }, 
                        axisTicks: { show: false },
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, sans-serif' },
                            formatter: function (value) { return value + " Jam"; }
                        }
                    },
                    grid: {
                        borderColor: '#f1f5f9',
                        strokeDashArray: 4,
                        xaxis: { lines: { show: false } },
                        yaxis: { lines: { show: true } }
                    },
                    legend: { show: true, position: 'top', horizontalAlign: 'right' },
                    tooltip: {
                        theme: 'light',
                        y: {
                            formatter: function (value) { return value + " Jam"; }
                        }
                    }
                };

                const monthlyChart = new ApexCharts(document.querySelector("#monthlyPerformanceChart"), monthlyOptions);
                monthlyChart.render();

                // Daily Chart
                const dailyOptions = {
                    chart: {
                        type: 'area',
                        height: 280,
                        toolbar: { show: false },
                        parentHeightOffset: 0
                    },
                    colors: ['#4f46e5'],
                    series: [{
                        name: 'Durasi Tertinggi',
                        data: @js($dailyAvgs)
                    }],
                    xaxis: {
                        categories: @js($dailyDates),
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, sans-serif' }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, sans-serif' },
                            formatter: function (value) { return value + " Mnt"; }
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [0, 100]
                        }
                    },
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: '#f1f5f9',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'light',
                        y: {
                            formatter: function (value) { return value + " Menit"; }
                        }
                    }
                };

                const dailyChart = new ApexCharts(document.querySelector("#dailyAverageChart"), dailyOptions);
                dailyChart.render();
            });
        </script>

        <!-- AGENCY STRUCTURE STATS Section -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="block text-xs font-black tracking-widest text-slate-500 uppercase font-mono">{{ __('dashboard.general.agency_structure_statistics') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Stat 1: Total Workers -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 leading-tight">{{ __('dashboard.general.total_workers') }}</span>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums tracking-tight">{{ number_format($metrics['total_workers']) }}</p>
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-0.5">Worker / Mitra Terdaftar</p>
                    </div>
                </div>

                <!-- Stat 2: Total Mitra -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 leading-tight">{{ __('dashboard.general.total_mitra') }}</span>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums tracking-tight">{{ number_format($metrics['total_mitra']) }}</p>
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-0.5">Koordinator Aktif</p>
                    </div>
                </div>

                <!-- Stat 3: System Engine -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 leading-tight">{{ __('dashboard.general.system_engine') }}</span>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-violet-50 text-violet-600 ring-1 ring-violet-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Laravel 11.x</p>
                        <div class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-md">
                            <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                            Engine Active
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Server Status -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 leading-tight">{{ __('dashboard.general.server_status') }}</span>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            SECURED
                        </span>
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1.5">Proteksi &amp; SSL Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ADMINISTRATIVE ACTIONS Section -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="block text-xs font-black tracking-widest text-slate-500 uppercase font-mono">{{ __('dashboard.general.administration_and_group') }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Action 1: Kelola Kode Aktivasi -->
                <a href="{{ route('activation-codes.index') }}" class="group bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-bold text-slate-900 group-hover:text-indigo-700 transition truncate">{{ __('dashboard.general.manage_activation_code') }}</span>
                            <span class="block text-xs text-slate-500 font-medium mt-0.5 truncate">Buat &amp; kelola grup A / B</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Action 2: Kelola Akun Admin -->
                <a href="{{ route('admin-users.index') }}" class="group bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-400 transition flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 ring-1 ring-slate-200 group-hover:bg-slate-800 group-hover:text-white transition flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-bold text-slate-900 group-hover:text-slate-700 transition truncate">{{ __('dashboard.general.manage_admin_account') }}</span>
                            <span class="block text-xs text-slate-500 font-medium mt-0.5 truncate">Atur hak akses &amp; role</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-600 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Action 3: Data Demografi Mitra -->
                <a href="{{ route('partners.index') }}" class="group bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-sky-300 transition flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 group-hover:bg-sky-600 group-hover:text-white transition flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-bold text-slate-900 group-hover:text-sky-700 transition truncate">{{ __('dashboard.general.partner_demographics_data') }}</span>
                            <span class="block text-xs text-slate-500 font-medium mt-0.5 truncate">Detail profil &amp; ekspor kontak</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-sky-500 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Action 4: Log Aktivitas Sistem -->
                <a href="{{ route('activity-logs.index') }}" class="group bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-300 transition flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition truncate">{{ __('dashboard.general.system_activity_log') }}</span>
                            <span class="block text-xs text-slate-500 font-medium mt-0.5 truncate">Audit trail tindakan pengguna</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Global Submissions Queue -->
        <div class="bg-white rounded-[32px] p-6 border border-gray-150 shadow-sm">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <div>
                    <span class="block text-sm font-bold text-gray-900">Antrean Log Rekaman Global</span>
                    <span class="text-xs text-gray-400">10 data rekaman terbaru</span>
                </div>
                <a href="{{ route('admin.recordings.index') }}" class="text-xs font-bold text-indigo-650 hover:underline flex items-center gap-1">
                    Semua Rekaman
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">ID Rekaman</th>
                            <th class="py-3 text-left font-semibold">Worker (Email)</th>
                            <th class="py-3 text-left font-semibold">Tanggal Task</th>
                            <th class="py-3 text-left font-semibold">Durasi Kerja</th>
                            <th class="py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($latestReports as $report)
                            <tr>
                                <td class="py-3.5 font-bold text-indigo-600">{{ substr($report->atlas_task_id ?? $report->id, 0, 8) }}</td>
                                <td class="py-3.5">
                                    <div class="flex flex-col">
                                        @if(!$report->atlasWorker)
                                            <span class="font-medium text-red-600">Unknown Worker</span>
                                            <span class="text-xs text-gray-450 font-mono">-</span>
                                        @else
                                            <span class="font-medium text-gray-900">{{ $report->atlasWorker->user->name ?? explode('@', $report->atlasWorker->atlas_email)[0] }}</span>
                                            <span class="text-xs text-gray-450 font-mono">{{ $report->atlasWorker->atlas_email }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 text-gray-600">{{ \Carbon\Carbon::parse($report->task_date)->translatedFormat('d F Y') }}</td>
                                <td class="py-3.5 text-slate-800 font-bold">{{ floor($report->worked_minutes) }}m {{ round(($report->worked_minutes - floor($report->worked_minutes)) * 60) }}s</td>
                                <td class="py-3.5">
                                    @php
                                        $statusClasses = [
                                            'UNDER_REVIEW' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'APPROVED' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'REJECTED' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        ];
                                        $statusClass = $statusClasses[$report->status] ?? 'bg-gray-50 text-gray-600 border-gray-100';
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                        {{ str_replace('_', ' ', $report->status ?? 'UNKNOWN') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-450 text-xs">Belum ada riwayat rekaman masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION KHUSUS: Tagihan Klien Mytronlabs -->
        <div class="bg-white rounded-[32px] p-6 border border-gray-150 shadow-sm space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 border-b border-gray-100 gap-4">
                <div>
                    <span class="block text-sm font-bold text-gray-900">{{ __('dashboard.general.latest_client_billing') }}</span>
                    <span class="text-xs text-gray-400">{{ __('dashboard.general.monthly_agency_invoice_recording_to_client') }}</span>
                </div>
                <!-- Projections summaries -->
                @php
                    $cair = $clientInvoices->where('status', 'PAID')->sum('total_amount');
                    $tertahan = $clientInvoices->whereIn('status', ['ISSUED', 'SENT'])->sum('total_amount');
                @endphp
                <div class="flex gap-4">
                    <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl px-4 py-2">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider font-mono">Total Dana Cair (PAID)</span>
                        <span class="block text-sm font-black text-emerald-800">${{ number_format($cair, 2) }}</span>
                    </div>
                    <div class="bg-amber-50/50 border border-amber-100 rounded-xl px-4 py-2">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider font-mono">Dana Tertahan (ISSUED/SENT)</span>
                        <span class="block text-sm font-black text-amber-800">${{ number_format($tertahan, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">{{ __('dashboard.general.invoice_no') }}</th>
                            <th class="py-3 text-left font-semibold">{{ __('dashboard.general.client') }}</th>
                            <th class="py-3 text-left font-semibold">{{ __('dashboard.general.billed_duration_hours') }}</th>
                            <th class="py-3 text-left font-semibold">{{ __('dashboard.general.total_billing_value') }}</th>
                            <th class="py-3 text-left font-semibold">{{ __('dashboard.general.payment_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($clientInvoices as $invoice)
                            <tr>
                                <td class="py-3.5 font-bold text-gray-900">{{ $invoice->invoice_no }}</td>
                                <td class="py-3.5 text-gray-900">{{ $invoice->client_name }}</td>
                                <td class="py-3.5 text-slate-800 font-semibold">{{ number_format($invoice->billable_hours, 2) }} jam</td>
                                <td class="py-3.5 font-black text-slate-900">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="py-3.5">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border 
                                        {{ $invoice->status == 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $invoice->status == 'ISSUED' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $invoice->status == 'SENT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $invoice->status == 'PAID' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : '' }}
                                        {{ $invoice->status == 'VOID' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $invoice->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-450 text-xs">Belum ada riwayat tagihan klien yang terbit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
