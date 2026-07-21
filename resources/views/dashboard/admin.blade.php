<x-app-layout>
    <x-slot name="header">
        <div class="w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                {{ __('Dashboard Admin') }}
            </h2>
            <div class="grid grid-cols-2 sm:flex gap-2 w-full lg:w-auto">
                <a href="{{ route('payroll.export-csv') }}" class="min-h-11 inline-flex items-center justify-center px-3 sm:px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-650 border border-transparent rounded-xl font-semibold text-[10px] sm:text-xs text-white uppercase tracking-wider hover:from-blue-700 hover:to-indigo-700 focus:outline-none transition shadow-md shadow-indigo-100 text-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ekspor CSV
                </a>
                <form class="min-w-0" action="{{ route('payroll.mark-as-paid') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menandai semua tagihan approved sebagai paid?')">
                    @csrf
                    <button type="submit" class="w-full min-h-11 inline-flex items-center justify-center px-3 sm:px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 border border-transparent rounded-xl font-semibold text-[10px] sm:text-xs text-white uppercase tracking-wider transition shadow-md shadow-emerald-100 text-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tandai Dibayar
                    </button>
                </form>
            </div>
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
            <div class="p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-100 flex items-center gap-2 animate-bounce" role="alert">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
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
                    
                    <!-- Queue Status Durations Breakdown -->
                    <div class="mt-4 pt-4 border-t border-slate-900/10 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate-600 font-semibold font-mono">
                        <div>Pending Queue: <span class="text-yellow-600 font-extrabold">{{ $metrics['global_pending_submitted_hours_formatted'] }}</span></div>
                        <div>On Review Queue: <span class="text-blue-600 font-extrabold">{{ $metrics['global_on_review_submitted_hours_formatted'] }}</span></div>
                        <div>Rejected Queue: <span class="text-rose-600 font-extrabold">{{ $metrics['global_rejected_submitted_hours_formatted'] }}</span></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 relative z-10">
                    <a href="{{ route('payroll.export-csv') }}" class="flex items-center justify-center gap-2 py-3.5 bg-white hover:bg-gray-50 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition">
                        Ekspor CSV Payroll
                    </a>
                    <a href="{{ route('video-submissions.qc-room') }}" class="flex items-center justify-center gap-2 py-3.5 bg-white/60 hover:bg-white/80 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition">
                        Buka QC Room
                    </a>
                </div>
            </div>

            <!-- Right: Investment balance card (1 col) - MARGIN AGENSI -->
            <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-8 border border-gray-150 shadow-sm flex flex-col justify-between min-h-[200px] sm:min-h-[220px]">
                <div>
                    <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">MARGIN BERSIH AGENSI</span>
                        <span class="bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded-full font-mono">
                            Live $1 = Rp{{ number_format($metrics['usd_to_idr_rate'], 0, ',', '.') }}
                        </span>
                    </div>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">
                        ${{ number_format($metrics['agency_net_margin_usd'], 2) }}
                        <span class="text-xs text-gray-500 font-semibold block mt-1.5">
                            Setara: <strong class="text-slate-800">Rp{{ number_format($metrics['agency_net_margin'], 0, ',', '.') }} IDR</strong> (Laba Bersih Agensi)
                        </span>
                    </h3>
                </div>

                <div class="pt-4 border-t border-gray-100 mt-4 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-450 font-semibold">Total Billing Klien:</span>
                        <span class="font-bold text-indigo-700">${{ number_format($metrics['client_paid_amount_usd'] + $metrics['client_pending_amount_usd'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-450 font-semibold">Rate Billing Klien:</span>
                        <span class="font-bold text-gray-800">$4.00 / jam</span>
                    </div>
                </div>
            </div>
        </div>

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
