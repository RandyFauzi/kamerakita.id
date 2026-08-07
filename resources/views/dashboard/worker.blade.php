<x-app-layout>
    <div class="space-y-4 sm:space-y-6" x-data="{ showBanner: true }">
        
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
                        <span class="block text-xs font-black tracking-widest text-blue-600 uppercase">AKUN PEKERJA AKTIF</span>
                        <p class="text-sm leading-5 text-gray-500 max-w-xl">Kirim laporan kerja harian beserta bukti untuk diproses oleh tim verifikasi.</p>
                        <!-- Progress bar -->
                        <div class="w-full bg-blue-100 h-2 rounded-full mt-3 overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('video-submissions.submit-report.create') }}" class="w-full md:w-auto min-h-11 inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl text-xs font-bold text-white shadow-md shadow-blue-500/30 transition-all duration-300 transform hover:-translate-y-0.5">
                        Kirim Laporan Baru
                    </a>
                    <button type="button" aria-label="Tutup pemberitahuan" @click="showBanner = false" class="absolute top-4 right-4 w-9 h-9 inline-flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Dynamic Holographic Card & Info Balance -->
        <div class="w-full">
            <x-glass-orb-card 
                title="Rp{{ number_format($metrics['total_earnings'], 0, ',', '.') }}"
                subtitle="ESTIMASI PENDAPATAN (Rp{{ number_format($metrics['hourly_rate'], 0, ',', '.') }}/JAM)"
                label1="TOTAL JAM"
                value1="{{ $metrics['all_time_hours_formatted'] }}"
                label2="KETERANGAN"
                value2="Gaji berdasar jam approved"
                actionText="Kirim Laporan Baru"
                actionUrl="{{ route('video-submissions.submit-report.create') }}"
            />
        </div>

        <!-- Statistik Progres Video Section -->
        <div class="space-y-4 sm:space-y-6">
            <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono">Statistik Progres Video</span>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- 1. Total Kirim vs Approved -->
                @php
                    $totalSub = $metrics['total_submitted_minutes'];
                    $totalApp = $metrics['all_time_minutes'];
                    $percentApp = $totalSub > 0 ? min(100, round(($totalApp / $totalSub) * 100)) : 0;
                @endphp
                <div class="bg-slate-200/50 rounded-[28px] p-2 flex flex-col h-full">
                    <div class="text-center py-2 pb-3">
                        <span class="text-[13px] font-bold text-slate-500">Statistik Anda</span>
                    </div>
                    <div class="bg-[#f0f1f3] rounded-[24px] p-5 sm:p-6 flex-grow flex flex-col justify-center border border-white/60 shadow-sm">
                        
                        <div class="flex justify-between items-center text-center">
                            <!-- Dikirim -->
                            <div class="flex-1">
                                <h3 class="text-[13px] font-semibold text-slate-400 mb-2">Dikirim</h3>
                                <div class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                                    {{ $metrics['total_submitted_minutes'] }}
                                </div>
                            </div>
                            
                            <!-- Divider -->
                            <div class="w-px h-10 bg-slate-200/80"></div>
                            
                            <!-- Approved -->
                            <div class="flex-1">
                                <h3 class="text-[13px] font-semibold text-slate-400 mb-2">Approved</h3>
                                <div class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                                    {{ $metrics['all_time_minutes'] }}
                                </div>
                            </div>
                            
                            <!-- Divider -->
                            <div class="w-px h-10 bg-slate-200/80"></div>
                            
                            <!-- Rate -->
                            <div class="flex-1">
                                <h3 class="text-[13px] font-semibold text-slate-400 mb-2">Tingkat</h3>
                                <div class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                                    {{ $percentApp }}<span class="text-base sm:text-lg text-slate-800 font-bold ml-0.5">%</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Badge at bottom -->
                        <div class="mt-6 flex justify-start">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-full text-[11px] font-bold text-indigo-700 shadow-sm">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L14.5 9L22 10.5L16 15.5L17.5 23L12 19L6.5 23L8 15.5L2 10.5L9.5 9L12 2Z" />
                                </svg>
                                Selalu perhatikan kualitas
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Target Harian Laps -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-700 shadow-lg relative overflow-hidden text-white flex flex-col justify-between" x-data="dailyTargetData({{ $metrics['today_submitted_minutes'] }})">
                    <div class="absolute right-0 bottom-0 w-32 h-32 rounded-full blur-3xl opacity-20 -mr-10 -mb-10 transition-colors duration-700" :class="glowClass"></div>
                    <div class="relative z-10 flex-grow">
                        <div class="flex justify-between items-center mb-1">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Target Harian</h3>
                            <div class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wider transition-colors duration-500" :class="lapBadgeClass" x-text="lapText"></div>
                        </div>
                        
                        <div class="flex items-baseline gap-1.5 mb-5 mt-2">
                            <span class="text-3xl font-black" x-text="hours">0</span>
                            <span class="text-sm font-bold text-slate-400">jam</span>
                            <span class="text-3xl font-black ml-1" x-text="minutes">0</span>
                            <span class="text-sm font-bold text-slate-400">menit</span>
                        </div>
                    </div>
                        
                    <div class="relative z-10 mt-2">
                        <!-- Progress Bar segmented for Laps -->
                        <div class="relative w-full h-4 bg-slate-700/50 rounded-full overflow-hidden shadow-inner">
                            <div class="absolute top-0 left-0 h-full transition-all duration-1000 ease-out rounded-full" :class="barColorClass" :style="'width: ' + progressPercent + '%'"></div>
                            <!-- Markers -->
                            <div class="absolute top-0 left-[33.33%] w-px h-full bg-slate-800/80 z-10"></div>
                            <div class="absolute top-0 left-[66.66%] w-px h-full bg-slate-800/80 z-10"></div>
                        </div>
                        
                        <div class="flex justify-between mt-2 text-[10px] font-bold text-slate-400 px-1 relative h-4">
                            <span>0j</span>
                            <span class="absolute left-[33.33%] -translate-x-1/2 transition-colors duration-500" :class="target1Active ? 'text-blue-400' : ''">2j</span>
                            <span class="absolute left-[66.66%] -translate-x-1/2 transition-colors duration-500" :class="target2Active ? 'text-amber-400' : ''">4j</span>
                            <span class="absolute right-0 transition-colors duration-500" :class="target3Active ? 'text-emerald-400' : ''">6j</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dailyTargetData', (submittedMins) => ({
                    mins: submittedMins,
                    
                    get hours() { return Math.floor(this.mins / 60); },
                    get minutes() { return this.mins % 60; },
                    
                    get currentLap() {
                        if (this.mins >= 360) return 3; // Maxed out
                        if (this.mins >= 240) return 3; // Working towards 6h
                        if (this.mins >= 120) return 2; // Working towards 4h
                        return 1; // Working towards 2h
                    },
                    
                    get lapText() {
                        if (this.mins >= 360) return 'MAX LAP REACHED 🏁';
                        return `LAP ${this.currentLap}`;
                    },
                    
                    get lapBadgeClass() {
                        if (this.mins >= 360) return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                        if (this.currentLap === 3) return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
                        if (this.currentLap === 2) return 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30';
                        return 'bg-slate-700 text-slate-300';
                    },
                    
                    get barColorClass() {
                        if (this.mins >= 360) return 'bg-gradient-to-r from-emerald-400 to-emerald-500 shadow-[0_0_10px_rgba(52,211,153,0.5)]';
                        if (this.currentLap === 3) return 'bg-gradient-to-r from-amber-400 to-amber-500 shadow-[0_0_10px_rgba(251,191,36,0.5)]';
                        if (this.currentLap === 2) return 'bg-gradient-to-r from-indigo-400 to-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.5)]';
                        return 'bg-gradient-to-r from-blue-400 to-blue-500';
                    },
                    
                    get glowClass() {
                        if (this.mins >= 360) return 'bg-emerald-500';
                        if (this.currentLap === 3) return 'bg-amber-500';
                        if (this.currentLap === 2) return 'bg-indigo-500';
                        return 'bg-blue-500';
                    },
                    
                    get progressPercent() {
                        // Max cap at 360 mins (6 hours)
                        return Math.min(100, (this.mins / 360) * 100);
                    },
                    
                    get target1Active() { return this.mins >= 120; },
                    get target2Active() { return this.mins >= 240; },
                    get target3Active() { return this.mins >= 360; },
                }))
            })
        </script>

        <!-- Recent reports list -->
        <div class="bg-white rounded-2xl sm:rounded-[32px] p-4 sm:p-6 border border-gray-150 shadow-sm">
            <div class="flex justify-between items-center gap-3 pb-4 border-b border-gray-100 mb-4">
                <span class="block text-sm font-bold text-gray-900">Riwayat Laporan Video Terakhir</span>
                <a href="{{ route('video-submissions.report-history') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Lihat semua</a>
            </div>
            @php
                $qcColors = [
                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                    'on_review' => 'bg-blue-50 text-blue-700 border-blue-100',
                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
                ];
                $payColors = [
                    'unpaid' => 'bg-gray-50 text-gray-600 border-gray-150',
                    'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                ];
            @endphp

            <div class="space-y-3 sm:hidden">
                @forelse($reports as $report)
                    <article class="rounded-xl border border-gray-150 p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="block text-xs text-gray-400">Tanggal kerja</span>
                                <strong class="block text-sm text-gray-900 mt-0.5">{{ $report->submission_date->translatedFormat('d F Y') }}</strong>
                            </div>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $qcColors[$report->qc_status] }}">
                                {{ ucfirst(str_replace('_', ' ', $report->qc_status)) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div><span class="block text-gray-400">Durasi kirim</span><strong class="text-gray-800">{{ $report->submitted_duration_formatted }}</strong></div>
                            <div><span class="block text-gray-400">Disetujui</span><strong class="text-gray-800">{{ $report->approved_duration_formatted }}</strong></div>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-400">Status pembayaran</span>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $payColors[$report->payment_status] }}">{{ ucfirst($report->payment_status) }}</span>
                        </div>
                    </article>
                @empty
                    <p class="py-6 text-center text-gray-450 text-xs">Belum ada riwayat laporan video dikirim.</p>
                @endforelse
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">Tanggal Kerja</th>
                            <th class="py-3 text-left font-semibold">Durasi Kirim</th>
                            <th class="py-3 text-left font-semibold">Durasi Disetujui</th>
                            <th class="py-3 text-left font-semibold">Status QC</th>
                            <th class="py-3 text-left font-semibold">Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($reports as $report)
                            <tr>
                                <td class="py-3.5 text-gray-900 font-medium">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                <td class="py-3.5 text-gray-600">{{ $report->submitted_duration_formatted }}</td>
                                <td class="py-3.5 text-slate-800 font-bold">{{ $report->approved_duration_formatted }}</td>
                                <td class="py-3.5">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $qcColors[$report->qc_status] }}">
                                        {{ ucfirst($report->qc_status) }}
                                    </span>
                                </td>
                                <td class="py-3.5">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $payColors[$report->payment_status] }}">
                                        {{ ucfirst($report->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-450 text-xs">Belum ada riwayat laporan video dikirim.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
