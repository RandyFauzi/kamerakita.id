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
                <div class="relative w-full bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[32px] p-6 sm:p-8 shadow-[0_20px_40px_rgba(0,0,0,0.05),inset_0_1px_0_rgba(255,255,255,1)] overflow-hidden transition-all duration-400 hover:-translate-y-1 hover:shadow-[0_30px_50px_rgba(0,0,0,0.08),inset_0_1px_0_rgba(255,255,255,1)] flex flex-col justify-between" x-data="dailyTargetData({{ $metrics['today_submitted_minutes'] }})" x-init="initComponent()">
                    
                    <style>
                        @keyframes pulse-glow-badge {
                            from { opacity: 0.5; transform: scale(0.95); }
                            to { opacity: 1; transform: scale(1.05); }
                        }
                    </style>

                    <!-- Halftone Pattern -->
                    <div class="absolute inset-x-0 top-0 h-[60%] pointer-events-none z-0 opacity-50" style="background-image: radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px); background-size: 5px 5px; mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%); -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%);"></div>
                    
                    <div class="relative z-10 flex-grow text-left">
                        
                        <!-- Header / Badge + Text -->
                        <div class="flex items-center gap-4 sm:gap-5 mb-8">
                            
                            <!-- Glowing 3D Badge -->
                            <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-rose-500/10 flex justify-center items-center shadow-[inset_0_0_20px_rgba(255,60,0,0.15)] shrink-0">
                                <!-- Outer Glow -->
                                <div class="absolute inset-[-10px] bg-[radial-gradient(circle,rgba(255,60,0,0.25)_0%,transparent_70%)] blur-[8px] -z-10 animate-[pulse-glow-badge_3s_infinite_alternate]"></div>
                                <!-- 3D Proxy Object -->
                                <div class="w-7 h-9 sm:w-8 sm:h-10 rounded-md relative overflow-hidden transform -rotate-[15deg] skew-x-[10deg] shadow-[-2px_2px_5px_rgba(0,0,0,0.3),inset_2px_2px_4px_rgba(255,255,255,0.4),0_0_15px_rgba(255,60,0,0.6)]" style="background: linear-gradient(135deg, #ff4d4d, #b30000);">
                                    <!-- 3D Proxy Details (the 3 dots) -->
                                    <div class="absolute top-[5px] right-[5px] w-1 h-1 bg-white rounded-full opacity-60 shadow-[0_7px_0_white,0_14px_0_white]"></div>
                                </div>
                            </div>
                            
                            <!-- Motivating Text -->
                            <div>
                                <h2 class="text-slate-900 text-base sm:text-lg leading-snug sm:leading-tight mb-1 font-medium">
                                    <span class="font-extrabold" x-text="titleBold"></span> <span x-text="titleNormal"></span>
                                </h2>
                                <p class="text-slate-600 text-xs sm:text-[13px] leading-snug sm:leading-tight m-0" x-text="subtitle"></p>
                            </div>
                        </div>

                        <!-- Progress Track -->
                        <div class="mb-4 relative w-full">
                            <div class="relative w-full h-5 bg-slate-200/80 rounded-full flex items-center shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
                                
                                <!-- Fill with width animation (BLUE GRADIENT) -->
                                <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] bg-gradient-to-r from-blue-600 to-cyan-400 z-0" style="width: 0%" :style="showAnimations ? 'width: ' + progressPercent + '%' : 'width: 0%'">
                                    <!-- Flare / Glow at tip -->
                                    <div class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-5 bg-white rounded-full blur-[6px] shadow-[0_0_20px_10px_rgba(34,211,238,0.7)] transition-opacity duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] delay-500 opacity-0" :class="showAnimations && mins > 0 ? '!opacity-100' : ''"></div>
                                </div>

                                <!-- Laps Markers / Dots -->
                                <div class="absolute right-5 flex gap-5 sm:gap-7 z-10">
                                    <template x-for="i in remainingLaps" :key="i">
                                        <div class="w-1.5 h-1.5 rounded-full transition-all duration-500" 
                                             :class="i === 1 ? 'bg-cyan-300 shadow-[0_0_10px_#22d3ee]' : 'bg-black/10 shadow-[inset_0_1px_2px_rgba(0,0,0,0.1)]'"></div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-between items-center px-1">
                            <span class="text-slate-500 text-[10px] sm:text-[11px] font-bold tracking-wider uppercase"><span x-text="completedLaps"></span> Lap Diselesaikan</span>
                            <span class="text-slate-500 text-[10px] sm:text-[11px] font-bold tracking-wider uppercase"><span x-text="remainingLaps"></span> Lap Tersisa</span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dailyTargetData', (submittedMins) => ({
                    mins: submittedMins,
                    mounted: false,
                    showAnimations: false,
                    
                    initComponent() {
                        setTimeout(() => this.mounted = true, 50);
                        setTimeout(() => this.showAnimations = true, 500); // Trigger fill bar after mount
                    },
                    
                    get completedLaps() {
                        return Math.min(3, Math.floor(this.mins / 120));
                    },
                    
                    get remainingLaps() {
                        return 3 - this.completedLaps;
                    },

                    get titleBold() {
                        if (this.mins === 0) return 'Ayo Mulai!';
                        if (this.mins < 120) return 'Sedikit lagi!';
                        if (this.mins < 240) return 'Hebat!';
                        if (this.mins < 360) return 'Luar Biasa!';
                        return 'Misi Selesai!';
                    },
                    
                    get titleNormal() {
                        if (this.mins === 0) return 'Semangat kerjakan';
                        if (this.mins < 120) return `Selesaikan target`;
                        if (this.mins < 240) return 'Anda melewati 2 jam.';
                        if (this.mins < 360) return 'Menuju batas maksimal.';
                        return 'Target maksimal tercapai.';
                    },
                    
                    get subtitle() {
                        if (this.mins === 0) return 'video pertama Anda hari ini.';
                        if (this.mins < 120) return `menuju 2 jam pertama Anda.`;
                        if (this.mins < 240) return 'Lanjut selesaikan menuju 4 jam?';
                        if (this.mins < 360) return 'Sedikit lagi capai 6 jam!';
                        return 'Silakan beristirahat dengan tenang.';
                    },
                    
                    get progressPercent() {
                        // Max cap at 360 mins (6 hours)
                        return Math.min(100, (this.mins / 360) * 100);
                    }
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
