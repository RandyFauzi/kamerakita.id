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
                @php
                    $mins = $metrics['today_submitted_minutes'];
                    $hours = floor($mins / 60);
                    $completedLaps = min(3, floor($mins / 120));
                    
                    if ($mins == 0) {
                        $tb = "Ayo Mulai!";
                        $tn = "Semangat kerjakan";
                        $sub = "video pertama Anda hari ini.";
                    } elseif ($mins < 120) {
                        $tb = "Sedikit lagi!";
                        $tn = "Selesaikan target";
                        $sub = "menuju 2 jam pertama Anda.";
                    } elseif ($mins < 240) {
                        $tb = "Hebat!";
                        $tn = "Anda melewati 2 jam.";
                        $sub = "Lanjut selesaikan menuju 4 jam?";
                    } elseif ($mins < 360) {
                        $tb = "Luar Biasa!";
                        $tn = "Menuju batas maksimal.";
                        $sub = "Sedikit lagi capai 6 jam!";
                    } else {
                        $tb = "Misi Selesai!";
                        $tn = "Target tercapai.";
                        $sub = "Lebih dari 6 jam pun tetap dihitung!";
                    }
                    
                    $percentage = min(100, round(($mins / 360) * 100));
                    $fLeft = $hours . " jam diselesaikan";
                    $fRight = "Total tertinggi 6 jam";
                @endphp

                <mission-card 
                    theme="light" 
                    completed="{{ $completedLaps }}" 
                    total="3" 
                    percentage="{{ $percentage }}"
                    title-bold="{{ $tb }}" 
                    title-normal="{{ $tn }}" 
                    subtitle="{{ $sub }}"
                    footer-left="{{ $fLeft }}"
                    footer-right="{{ $fRight }}">
                </mission-card>

                <script>
                    class MissionCard extends HTMLElement {
                        connectedCallback() {
                            const completed = this.getAttribute('completed') || '0';
                            const total = this.getAttribute('total') || '3';
                            const titleBold = this.getAttribute('title-bold') || 'Good job!';
                            const titleNormal = this.getAttribute('title-normal') || '';
                            const subtitle = this.getAttribute('subtitle') || '';
                            const theme = this.getAttribute('theme') || 'light';
                            const rawPercentage = this.getAttribute('percentage');
                            
                            const percentage = rawPercentage !== null ? parseInt(rawPercentage) : Math.round((parseInt(completed) / parseInt(total)) * 100);
                            const remaining = parseInt(total) - parseInt(completed);

                            const footerLeft = this.getAttribute('footer-left') || `${completed} Missions Completed`;
                            const footerRight = this.getAttribute('footer-right') || `${remaining} Missions Remaining`;

                            let dotsHtml = '';
                            for (let i = 0; i < remaining; i++) {
                                dotsHtml += `<div class="track-dot ${i === 0 ? 'active-next' : ''}"></div>`;
                            }

                            const isLight = theme === 'light';
                            const cardBg = isLight ? 'rgba(255, 255, 255, 0.7)' : 'rgba(255, 255, 255, 0.03)';
                            const cardBorder = isLight ? 'rgba(226, 232, 240, 1)' : 'rgba(255, 255, 255, 0.08)';
                            const cardShadow = isLight 
                                ? '0 20px 40px rgba(0, 0, 0, 0.05), inset 0 1px 0 rgba(255, 255, 255, 1)' 
                                : '0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1)';
                            const cardHoverShadow = isLight 
                                ? '0 30px 50px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 1)' 
                                : '0 40px 80px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
                                
                            const textColor1 = isLight ? '#111827' : '#ffffff';
                            const textColor2 = isLight ? '#4b5563' : '#d1d5db';
                            const textColor3 = isLight ? '#6b7280' : '#9ca3af';

                            const trackBg = isLight ? 'rgba(226, 232, 240, 1)' : 'rgba(0, 0, 0, 0.4)';
                            const trackShadow = isLight ? 'inset 0 2px 4px rgba(0,0,0,0.05)' : 'inset 0 2px 10px rgba(0,0,0,0.8), 0 1px 0 rgba(255,255,255,0.05)';
                            const dotBg = isLight ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.2)';

                            // Override warna ke Biru sesuai permintaan
                            const barGradient = 'linear-gradient(90deg, #3b82f6 0%, #22d3ee 100%)';
                            const activeDotColor = '#22d3ee';
                            const activeDotShadow = '0 0 10px #22d3ee';
                            const flareShadow = '0 0 30px 15px rgba(34, 211, 238, 0.8)';

                            this.innerHTML = `
                                <style>
                                    .mc-container-${theme} { font-family: 'Inter', sans-serif; width: 100%; height: 100%; }
                                    .glass-card {
                                        position: relative; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center;
                                        background: ${cardBg}; backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); 
                                        border: 1px solid ${cardBorder}; border-radius: 32px; padding: 32px; box-shadow: ${cardShadow}; 
                                        overflow: hidden; transition: transform 0.4s ease, box-shadow 0.4s ease;
                                    }
                                    .glass-card:hover { transform: translateY(-5px); box-shadow: ${cardHoverShadow}; }
                                    .card-pattern { position: absolute; top: 0; left: 0; right: 0; height: 60%; background-image: radial-gradient(${isLight ? 'rgba(0, 0, 0, 0.05)' : 'rgba(255, 255, 255, 0.15)'} 1px, transparent 1px); background-size: 4px 4px; -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%); mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%); z-index: 0; pointer-events: none; }
                                    .badge-container { position: relative; width: 64px; height: 64px; border-radius: 50%; background: ${isLight ? 'rgba(59, 130, 246, 0.1)' : 'rgba(0, 0, 0, 0.5)'}; display: flex; justify-content: center; align-items: center; box-shadow: inset 0 0 20px rgba(59, 130, 246, 0.2); }
                                    .badge-glow { position: absolute; inset: -10px; background: radial-gradient(circle, rgba(59, 130, 246, ${isLight ? '0.3' : '0.6'}) 0%, transparent 70%); filter: blur(12px); z-index: -1; animation: pulse-glow 3s infinite alternate; }
                                    .badge-3d-proxy { width: 32px; height: 40px; background: linear-gradient(135deg, #60a5fa, #1e40af); border-radius: 6px; transform: rotate(-15deg) skewX(10deg); box-shadow: -2px 2px 5px rgba(0,0,0,0.5), inset 2px 2px 4px rgba(255,255,255,0.4), 0 0 15px rgba(59, 130, 246, 0.8); position: relative; overflow: hidden; }
                                    .badge-3d-proxy::after { content: ''; position: absolute; top: 50%; left: 55%; transform: translate(-50%, -50%); width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-left: 10px solid white; opacity: 0.85; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.3)); }
                                    .progress-track { position: relative; width: 100%; height: 20px; background: ${trackBg}; border-radius: 999px; box-shadow: ${trackShadow}; display: flex; align-items: center; }
                                    .progress-fill { position: absolute; left: 0; top: 0; height: 100%; width: 0%; background: ${barGradient}; border-radius: 999px; animation: fill-bar 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: 0.5s; }
                                    .progress-flare { content: ''; position: absolute; right: 0; top: 50%; transform: translateY(-50%); width: 40px; height: 20px; background: #ffffff; border-radius: 50%; filter: blur(8px); box-shadow: ${flareShadow}; opacity: 0; animation: flare-ignite 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: 0.5s; }
                                    .track-dots { position: absolute; right: 20px; display: flex; gap: 28px; }
                                    .track-dot { width: 6px; height: 6px; border-radius: 50%; background: ${dotBg}; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); }
                                    .track-dot.active-next { background: ${activeDotColor}; box-shadow: ${activeDotShadow}; }
                                    @keyframes fill-bar { to { width: ${percentage}%; } }
                                    @keyframes flare-ignite { 0% { opacity: 0; } 50% { opacity: 1; } 100% { opacity: 1; } }
                                    @keyframes pulse-glow { from { opacity: 0.6; transform: scale(0.95); } to { opacity: 1; transform: scale(1.05); } }
                                    @keyframes entrance { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
                                    .animate-entrance { animation: entrance 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
                                </style>
                                <div class="mc-container-${theme}">
                                    <div class="glass-card animate-entrance">
                                        <div class="card-pattern"></div>
                                        <div class="relative z-10 text-left">
                                            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 32px;">
                                                <div class="badge-container" style="flex-shrink: 0;">
                                                    <div class="badge-glow"></div>
                                                    <div class="badge-3d-proxy"></div>
                                                </div>
                                                <div>
                                                    <h2 style="color: ${textColor1}; font-size: 18px; line-height: 1.2; margin: 0 0 4px 0; font-weight: 500;">
                                                        <span style="font-weight: 700;">${titleBold}</span> ${titleNormal}
                                                    </h2>
                                                    <p style="color: ${textColor2}; font-size: 14px; line-height: 1.2; margin: 0;">
                                                        ${subtitle}
                                                    </p>
                                                </div>
                                            </div>
                                            <div style="margin-bottom: 12px;">
                                                <div class="progress-track">
                                                    <div class="progress-fill">
                                                        <div class="progress-flare"></div>
                                                    </div>
                                                    <div class="track-dots">
                                                        ${dotsHtml}
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 4px;">
                                                <span style="color: ${textColor3}; font-size: 12px; font-weight: 500; letter-spacing: 0.02em;">${footerLeft}</span>
                                                <span style="color: ${textColor3}; font-size: 12px; font-weight: 500; letter-spacing: 0.02em;">${footerRight}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    }
                    if (!customElements.get('mission-card')) {
                        customElements.define('mission-card', MissionCard);
                    }
                </script>
            </div>

            <!-- Leaderboard Mobile Banner -->
            <a href="{{ route('leaderboard.index') }}" class="group relative block w-full rounded-[28px] overflow-hidden bg-gradient-to-br from-sky-100 to-blue-100 p-[1px] shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 mt-6 border border-sky-100">
                <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative bg-gradient-to-br from-white to-sky-50/80 rounded-[27px] p-5 sm:p-6 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-sky-100/50 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-sky-200/50 shadow-sm shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-sky-600 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871m-4.008 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M18.75 4.236c.982.143 1.954.317 2.916.52a6.003 6.003 0 01-5.395 4.972m0 0a8.001 8.001 0 00-1.588-4.982m-1.226 0H9.102m1.226 0a8.001 8.001 0 011.588-4.982" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-slate-800 font-extrabold text-lg sm:text-xl tracking-tight leading-tight">Papan Peringkat</h3>
                            <p class="text-slate-500 text-[11px] sm:text-xs font-medium mt-1 leading-tight">Lihat posisi Anda di antara mitra lainnya!</p>
                        </div>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-full flex items-center justify-center shadow-sm border border-sky-100 transform group-hover:translate-x-1 transition-transform duration-300 shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-sky-600 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>

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

        <!-- CTA Mailbox -->
        <div class="mt-8 bg-white border border-indigo-100 rounded-3xl p-5 md:p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-5 transition-all hover:shadow-md hover:border-indigo-200">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center shrink-0 border border-indigo-100">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[16px] md:text-lg font-bold text-gray-900 mb-0.5">Kotak Masuk (Mailbox)</h3>
                    <p class="text-gray-500 text-[13px] md:text-sm leading-snug">Cek pesan, pengumuman, dan pembaruan terbaru dari tim KameraKita.</p>
                </div>
            </div>
            
            <a href="{{ route('mailbox.index') }}" class="w-full md:w-auto shrink-0 bg-indigo-600 text-white hover:bg-indigo-700 px-6 py-3 rounded-xl font-bold text-[14px] shadow-sm transition-colors text-center flex items-center justify-center gap-2 group">
                Buka Mailbox
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

    </div>
</x-app-layout>
