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
                    <a href="{{ route('video-submissions.submit-report.create') }}" class="w-full md:w-auto min-h-11 inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-xs font-bold text-white shadow-sm transition">
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

        <!-- Dynamic Holographic Card & Info Balance -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Left: Holographic balance card (2 cols) -->
            <div class="lg:col-span-2 bg-gradient-to-r from-sky-200 via-pink-100 to-yellow-100 rounded-2xl sm:rounded-[32px] p-5 sm:p-8 border border-white/40 shadow-sm flex flex-col justify-between min-h-[200px] sm:min-h-[220px] relative overflow-hidden group">
                <!-- Soft grid blur overlay for holographic look -->
                <div class="absolute inset-0 bg-white/20 backdrop-blur-[1px] pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="block text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-550 font-mono leading-5">ESTIMASI PENDAPATAN (Rp{{ number_format($metrics['hourly_rate'], 0, ',', '.') }}/JAM)</span>
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1 mt-3 min-w-0">
                        <span class="text-3xl sm:text-4xl font-black text-slate-900 break-all">Rp{{ number_format($metrics['total_earnings'], 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-550 font-bold uppercase font-mono">Total ({{ $metrics['all_time_hours_formatted'] }})</span>
                    </div>
                    <span class="block text-xs text-slate-450 font-medium mt-1">Gaji terhitung otomatis berdasarkan jam approved.</span>
                </div>

                <!-- Minimalist Actions -->
                <div class="mt-6 relative z-10">
                    <a href="{{ route('video-submissions.submit-report.create') }}" class="flex items-center justify-center gap-2 py-3.5 bg-white hover:bg-gray-50 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Kirim Laporan
                    </a>
                </div>
            </div>

            <!-- Right: Investment balance card (1 col) -->
            <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-8 border border-gray-150 shadow-sm flex flex-col justify-between gap-5 min-h-[180px] sm:min-h-[220px]">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">PENDAPATAN PENDING</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-3">Rp{{ number_format($metrics['pending_earnings'], 0, ',', '.') }}</h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-xs text-slate-400">Durasi pending:</span>
                        <span class="bg-amber-50 border border-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ $metrics['pending_hours_formatted'] }}
                        </span>
                    </div>
                </div>

                <a href="https://wa.me/6287886272647?text=Halo%20Koordinator%20Kamerakita.id,%20saya%20ingin%20bertanya%20terkait%20status%20laporan%20video%20kerja%20saya." target="_blank" class="w-full py-3.5 bg-gray-50 border border-gray-200 hover:bg-gray-100 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition flex items-center justify-center">
                    Hubungi Koordinator
                </a>
            </div>
        </div>

        <!-- OTHER FEATURES Section -->
        <div class="space-y-3">
            <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono">FITUR LAINNYA</span>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <!-- Feature 1: Gaji Dibayarkan -->
                <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition min-h-32">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Telah Dicairkan</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Rp{{ number_format($metrics['paid_earnings'], 0, ',', '.') }}</span>
                </div>

                <!-- Feature 2: Bank Info -->
                <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition min-h-32">
                    <div class="p-2.5 bg-indigo-50 text-indigo-650 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Rekening Bank</span>
                    <span class="block text-[10px] text-gray-450 font-bold font-mono">{{ $partner->bank_name ?? '-' }}</span>
                </div>

                <!-- Feature 3: Smartphone -->
                <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition min-h-32">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Perangkat Rekam</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Kualitas Terverifikasi</span>
                </div>

                <!-- Feature 4: Status Pajak / Akun -->
                <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition relative min-h-32">
                    <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Status Kemitraan</span>
                    <span class="bg-rose-50 border border-rose-100 text-rose-700 text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                        {{ $partner->status }}
                    </span>
                </div>
            </div>
        </div>

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
