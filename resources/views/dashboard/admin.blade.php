<x-app-layout>
    <x-slot name="header">
        <div class="w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                {{ __('Dashboard Admin') }}
            </h2>
            <!-- Actions removed as requested -->
        </div>
    </x-slot>

    <div class="py-1 sm:py-4 space-y-4 sm:space-y-6" x-data="{ showBanner: true }">
        
        <!-- Top Banner: YOUR ACCOUNT IS ACTIVE -->
        <template x-if="showBanner">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-150 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex gap-3 sm:gap-4 items-start pr-8 md:pr-0">
                    <div class="p-2.5 sm:p-3 bg-blue-50 text-blue-600 rounded-xl sm:rounded-2xl shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <span class="block text-xs font-black tracking-widest text-blue-600 uppercase">SISTEM MONITORING AGENT</span>
                        <p class="text-sm text-gray-500 max-w-xl">Super Admin memiliki kontrol penuh atas verifikasi QC, penetapan rate pendapatan dasar mitra, serta penarikan ekspor CSV bank bulk transfer.</p>
                        <!-- Progress bar -->
                        <div class="w-full bg-blue-100 h-2 rounded-full mt-3 overflow-hidden">
                            <div class="bg-blue-650 h-full rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('video-submissions.qc-room') }}" class="w-full md:w-auto min-h-11 inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-indigo-700 transition">
                        Buka QC Room
                    </a>
                    <button type="button" aria-label="Tutup pemberitahuan" @click="showBanner = false" class="absolute top-4 right-4 w-9 h-9 inline-flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

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
            <!-- Left: Holographic balance card (2 cols) -->
            <div class="lg:col-span-2 bg-gradient-to-r from-purple-200 via-sky-100 to-yellow-100 rounded-2xl sm:rounded-[32px] p-5 sm:p-8 border border-white/40 shadow-sm flex flex-col justify-between min-h-[200px] sm:min-h-[220px] relative overflow-hidden group">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-[1px] pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">GLOBAL ALL-TIME MINUTES TIM APPROVED</span>
                    <div class="flex flex-wrap items-baseline gap-2 mt-3">
                        <span class="text-3xl sm:text-4xl font-black text-slate-900">{{ $metrics['global_all_time_hours_formatted'] }}</span>
                        <span class="text-xs text-slate-500 font-bold uppercase font-mono">Approved</span>
                    </div>
                    <span class="block text-xs text-slate-400 font-medium mt-1">Total durasi data terkumpul di agensi Kamerakita.ai</span>
                    
                    <!-- Queue Status Durations Breakdown (Sleek Inline Row) -->
                    <div class="mt-4 pt-4 border-t border-slate-900/10 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-mono font-medium text-slate-500">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">QUEUES:</span>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                            <span>Pending: <strong class="text-slate-800">{{ $metrics['global_pending_submitted_hours_formatted'] }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            <span>On Review: <strong class="text-slate-800">{{ $metrics['global_on_review_submitted_hours_formatted'] }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>Rejected: <strong class="text-slate-800">{{ $metrics['global_rejected_submitted_hours_formatted'] }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 relative z-10">
                    <a href="{{ route('payroll.export-csv') }}" class="flex items-center justify-center gap-2 py-3.5 bg-white hover:bg-gray-50 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition">
                        Ekspor CSV Payroll
                    </a>
                    <a href="{{ route('payroll.export-hourly-tracker-excel') }}" class="flex items-center justify-center gap-2 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-sm transition w-full">
                        📊 Ekspor Excel Tracker
                    </a>
                    <a href="{{ route('video-submissions.qc-room') }}" class="flex items-center justify-center gap-2 py-3.5 bg-white/60 hover:bg-white/80 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition">
                        Buka QC Room
                    </a>
                </div>
            </div>

            <!-- Right: Target Produksi Video Card (1 col) - TARGET MINGGUAN & BULANAN -->
            <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-7 border border-gray-150 shadow-sm flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">TARGET PRODUKSI VIDEO</span>
                        <span class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full font-mono flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Approved Video
                        </span>
                    </div>

                    <!-- Target Mingguan (100 Jam) -->
                    <div class="space-y-1.5 mb-4">
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-xs font-bold text-slate-800">Target Mingguan</span>
                                <span class="text-[10px] text-slate-400 font-medium block">Periode: {{ $metrics['weekly_period_label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-slate-900">{{ $metrics['weekly_approved_hours'] }} <span class="text-xs text-slate-400 font-normal">/ {{ $metrics['weekly_target_hours'] }} Jam</span></span>
                                <span class="text-[10px] font-bold block {{ $metrics['weekly_progress_percent'] >= 100 ? 'text-emerald-600' : 'text-indigo-600' }} font-mono">{{ $metrics['weekly_progress_percent'] }}%</span>
                            </div>
                        </div>
                        <!-- Progress Bar Mingguan -->
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $metrics['weekly_progress_percent'] >= 100 ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-indigo-500 to-blue-500' }}" style="width: {{ min(100, $metrics['weekly_progress_percent']) }}%"></div>
                        </div>
                    </div>

                    <!-- Target Bulanan (400 Jam) -->
                    <div class="space-y-1.5 pt-3 border-t border-slate-100">
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-xs font-bold text-slate-800">Target Bulanan</span>
                                <span class="text-[10px] text-slate-400 font-medium block">Bulan: {{ $metrics['monthly_period_label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-slate-900">{{ $metrics['monthly_approved_hours'] }} <span class="text-xs text-slate-400 font-normal">/ {{ $metrics['monthly_target_hours'] }} Jam</span></span>
                                <span class="text-[10px] font-bold block {{ $metrics['monthly_progress_percent'] >= 100 ? 'text-emerald-600' : 'text-purple-600' }} font-mono">{{ $metrics['monthly_progress_percent'] }}%</span>
                            </div>
                        </div>
                        <!-- Progress Bar Bulanan -->
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $metrics['monthly_progress_percent'] >= 100 ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-purple-500 to-indigo-500' }}" style="width: {{ min(100, $metrics['monthly_progress_percent']) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 mt-3 flex justify-between items-center text-[11px] text-slate-400">
                    <span>Total Akumulasi Terverifikasi</span>
                    <span class="font-bold text-slate-700 font-mono">{{ $metrics['global_all_time_hours_formatted'] }}</span>
                </div>
            </div>
        </div>

        @php
            $monthlyMonths = $monthlyData->pluck('month')->map(function($m) {
                return \Carbon\Carbon::parse($m . '-01')->format('M Y');
            })->toArray();
            $monthlyMinutes = $monthlyData->pluck('total_minutes')->map(function($m) {
                return round($m / 60, 2); // Convert to hours
            })->toArray();

            $dailyDates = $dailyAverageData->pluck('submission_date')->map(function($d) {
                return \Carbon\Carbon::parse($d)->format('d M');
            })->toArray();
            $dailyAvgs = $dailyAverageData->pluck('avg_minutes')->map(function($m) {
                return round($m, 1); // Average in minutes
            })->toArray();
        @endphp

        <!-- Analytics Section -->
        <div class="space-y-3">
            <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono">ANALISIS & GRAFIK KINERJA</span>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Monthly Performance Chart -->
                <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-6 border border-gray-150 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Total Jam Kerja Terverifikasi</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Akumulasi durasi yang disetujui (approved) per bulan</p>
                        </div>
                        <span class="bg-blue-50 text-blue-700 text-[10px] font-black px-2 py-0.5 rounded-md font-mono">Bulanan</span>
                    </div>
                    <div id="monthlyPerformanceChart" class="min-h-[280px]"></div>
                </div>

                <!-- Daily Average Duration Chart -->
                <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-6 border border-gray-150 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Rata-rata Durasi Kerja per Hari</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Rata-rata durasi (menit) yang dilaporkan per hari (7 hari terakhir)</p>
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
                        height: 280,
                        toolbar: { show: false },
                        parentHeightOffset: 0
                    },
                    colors: ['#3b82f6'],
                    series: [{
                        name: 'Total Jam',
                        data: @js($monthlyMinutes)
                    }],
                    xaxis: {
                        categories: @js($monthlyMonths),
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, sans-serif' }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, sans-serif' },
                            formatter: function (value) { return value + " Jam"; }
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            columnWidth: '40%'
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
                            formatter: function (value) { return value + " Jam Kerja"; }
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
                        name: 'Rata-rata Menit',
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

        <!-- OTHER FEATURES Section -->
        <div class="space-y-3">
            <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono">STATISTIK STRUKTUR AGENSI</span>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <!-- Feature 1: Total Workers -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Total Workers</span>
                    <span class="block text-[10px] text-gray-400 font-medium">{{ $metrics['total_workers'] }} Mitra</span>
                </div>

                <!-- Feature 2: Total Mitra -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition">
                    <div class="p-2.5 bg-indigo-50 text-indigo-650 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Total Mitra</span>
                    <span class="block text-[10px] text-gray-400 font-medium">{{ $metrics['total_mitra'] }} Koordinator</span>
                </div>

                <!-- Feature 3: WhatsApp support -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition">
                    <div class="p-2.5 bg-purple-50 text-purple-650 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">System Engine</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Laravel 11.x Active</span>
                </div>

                <!-- Feature 4: Status Pajak / Akun -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition relative">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Status Server</span>
                    <span class="bg-emerald-55 border border-emerald-100 text-emerald-700 text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                        SECURED
                    </span>
                </div>
            </div>
        </div>

        <!-- ADMINISTRATIVE ACTIONS Section -->
        <div class="space-y-3">
            <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono">ADMINISTRASI & GRUP</span>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4">
                <!-- Action 1: Kelola Kode Aktivasi -->
                <a href="{{ route('activation-codes.index') }}" class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md hover:border-indigo-200 transition">
                    <div class="p-2.5 bg-indigo-50 text-indigo-650 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-extrabold text-gray-800">Kelola Kode Aktivasi</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Buat & kelola grup A / B</span>
                </a>

                <!-- Action 2: Kelola Akun Admin -->
                <a href="{{ route('admin-users.index') }}" class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md hover:border-indigo-200 transition">
                    <div class="p-2.5 bg-slate-50 text-slate-700 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-extrabold text-gray-800">Kelola Akun Admin</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Atur hak akses</span>
                </a>

                <!-- Action 3: Kelola Mitra Demographics -->
                <a href="{{ route('partners.index') }}" class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md hover:border-indigo-200 transition">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-extrabold text-gray-800">Data Demografi Mitra</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Detail profil & ekspor kontak</span>
                </a>

                <!-- Action 4: Log Aktivitas Audit Trail -->
                <a href="{{ route('activity-logs.index') }}" class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md hover:border-indigo-200 transition">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-extrabold text-gray-800">Log Aktivitas Sistem</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Audit trail tindakan pengguna</span>
                </a>
            </div>
        </div>

        <!-- Global Submissions Queue -->
        <div class="bg-white rounded-[32px] p-6 border border-gray-150 shadow-sm">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <div>
                    <span class="block text-sm font-bold text-gray-900">Antrean Log Laporan Kerja Video Global</span>
                    <span class="text-xs text-gray-400">10 data pengiriman terbaru</span>
                </div>
                <a href="{{ route('video-submissions.qc-room') }}" class="text-xs font-bold text-indigo-650 hover:underline flex items-center gap-1">
                    QC Room
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">ID Laporan</th>
                            <th class="py-3 text-left font-semibold">Nama Worker</th>
                            <th class="py-3 text-left font-semibold">Tanggal Kerja</th>
                            <th class="py-3 text-left font-semibold">Durasi Kirim</th>
                            <th class="py-3 text-left font-semibold">Status QC</th>
                            <th class="py-3 text-left font-semibold">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($latestReports as $report)
                            <tr>
                                <td class="py-3.5 font-bold text-indigo-600">{{ substr($report->id, 0, 8) }}...</td>
                                <td class="py-3.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ $report->partner->full_name }}</span>
                                        <span class="text-xs text-gray-450 font-mono">{{ $report->partner->mitra_id }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 text-gray-600">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                <td class="py-3.5 text-slate-800 font-bold">{{ $report->submitted_duration_formatted }}</td>
                                <td class="py-3.5">
                                    @php
                                        $qcColors = [
                                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'on_review' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $qcColors[$report->qc_status] }}">
                                        {{ ucfirst($report->qc_status) }}
                                    </span>
                                </td>
                                <td class="py-3.5">
                                    @php
                                        $payColors = [
                                            'unpaid' => 'bg-gray-50 text-gray-600 border-gray-150',
                                            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $payColors[$report->payment_status] }}">
                                        {{ ucfirst($report->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-450 text-xs">Belum ada riwayat laporan video masuk.</td>
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
                    <span class="block text-sm font-bold text-gray-900">Tagihan Klien (Mytronlabs)</span>
                    <span class="text-xs text-gray-400">Pencatatan faktur penagihan bulanan agensi ke klien</span>
                </div>
                <!-- Projections summaries -->
                @php
                    $cair = $clientInvoices->where('status', 'paid_by_client')->sum('total_amount_usd');
                    $tertahan = $clientInvoices->where('status', 'unpaid_by_client')->sum('total_amount_usd');
                @endphp
                <div class="flex gap-4">
                    <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl px-4 py-2">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider font-mono">Total Dana Cair</span>
                        <span class="block text-sm font-black text-emerald-800">${{ number_format($cair, 2) }}</span>
                    </div>
                    <div class="bg-amber-50/50 border border-amber-100 rounded-xl px-4 py-2">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider font-mono">Dana Tertahan (Pending)</span>
                        <span class="block text-sm font-black text-amber-800">${{ number_format($tertahan, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">Bulan Invoice</th>
                            <th class="py-3 text-left font-semibold">Durasi Ditagihkan (Menit)</th>
                            <th class="py-3 text-left font-semibold">Durasi Ditagihkan (Jam)</th>
                            <th class="py-3 text-left font-semibold">Total Nilai Tagihan (USD)</th>
                            <th class="py-3 text-left font-semibold">Status Pembayaran Klien</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($clientInvoices as $invoice)
                            <tr>
                                <td class="py-3.5 font-bold text-gray-900">{{ $invoice->invoice_month }}</td>
                                <td class="py-3.5 text-gray-600 font-mono">{{ number_format($invoice->total_minutes_billed) }} menit</td>
                                <td class="py-3.5 text-slate-800 font-semibold">{{ round($invoice->total_minutes_billed / 60, 1) }} jam</td>
                                <td class="py-3.5 font-black text-slate-900">${{ number_format($invoice->total_amount_usd, 2) }}</td>
                                <td class="py-3.5">
                                    @if($invoice->status === 'paid_by_client')
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-100">
                                            Paid by Client
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border bg-amber-50 text-amber-700 border-amber-100 animate-pulse">
                                            Unpaid by Client
                                        </span>
                                    @endif
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
