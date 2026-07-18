<x-app-layout>
    @php
        $formatMinutes = function ($minutes) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;

            return $hours > 0 ? "{$hours}h {$remainingMinutes}m" : "{$remainingMinutes}m";
        };

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

    <div class="space-y-6">
        <!-- Page Header Card -->
        <div class="overflow-hidden shadow-sm sm:rounded-3xl p-6 text-white relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <span class="bg-indigo-500/20 text-indigo-300 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Laporan Kerja</span>
                    <h3 class="text-xl font-black tracking-tight">Riwayat Laporan Video</h3>
                    <p class="text-xs text-slate-350 max-w-xl leading-normal">
                        @if($partner->partner_role === 'mitra')
                            Menampilkan laporan pribadi dan seluruh worker direct di bawah akun mitra Anda.
                        @else
                            Menampilkan seluruh laporan yang pernah Anda kirimkan.
                        @endif
                    </p>
                </div>
                @if($partner->partner_role === 'worker')
                    <a href="{{ route('video-submissions.submit-report.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-white hover:bg-slate-50 text-slate-900 rounded-2xl text-xs font-black shadow-sm transition duration-200 shrink-0">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                        </svg>
                        Kirim Laporan Baru
                    </a>
                @endif
            </div>
            <!-- Premium subtle background glows -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Laporan</span>
                <strong class="block text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['total_reports'], 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Menunggu QC</span>
                <strong class="block text-2xl font-black text-amber-700 mt-2">{{ number_format($summary['pending_reports'], 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Approved</span>
                <strong class="block text-2xl font-black text-emerald-700 mt-2">{{ number_format($summary['approved_reports'], 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Durasi Unpaid</span>
                <strong class="block text-2xl font-black text-indigo-650 mt-2">{{ $formatMinutes($summary['unpaid_minutes']) }}</strong>
            </div>
        </div>

        <form method="GET" action="{{ route('video-submissions.report-history') }}" class="bg-white rounded-3xl p-6 border border-gray-150 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Cari Laporan (6/12 cols) -->
                <div class="col-span-1 md:col-span-6">
                    <label for="search" class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Cari Laporan</label>
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input id="search" name="search" value="{{ request('search') }}" type="text" placeholder="Masukkan ID laporan..." class="w-full pl-11 pr-4 py-2.5 border border-gray-250 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50/50">
                    </div>
                </div>

                <!-- Status QC (2/12 cols) -->
                <div class="col-span-1 md:col-span-2">
                    <label for="qc_status" class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Status QC</label>
                    <select id="qc_status" name="qc_status" class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50/50">
                        <option value="">Semua QC</option>
                        <option value="pending" @selected(request('qc_status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('qc_status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('qc_status') === 'rejected')>Rejected</option>
                    </select>
                </div>

                <!-- Status Bayar (2/12 cols) -->
                <div class="col-span-1 md:col-span-2">
                    <label for="payment_status" class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Status Bayar</label>
                    <select id="payment_status" name="payment_status" class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50/50">
                        <option value="">Semua Bayar</option>
                        <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                    </select>
                </div>

                <!-- Buttons (2/12 cols) -->
                <div class="col-span-1 md:col-span-2 flex gap-2 w-full justify-end">
                    <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm shadow-slate-900/10">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('video-submissions.report-history') }}" class="w-full py-2.5 bg-white hover:bg-gray-50 border border-gray-250 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center">Reset</a>
                </div>
            </div>
        </form>

        <div class="bg-white rounded-[32px] p-6 border border-gray-150 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 pb-4 border-b border-gray-100 mb-4">
                <span class="block text-sm font-bold text-gray-900">Daftar Riwayat Laporan</span>
                <span class="text-xs text-gray-400">{{ $reports->total() }} data ditemukan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 pr-4 text-left font-semibold">ID Laporan</th>
                            <th class="py-3 pr-4 text-left font-semibold">Worker</th>
                            <th class="py-3 pr-4 text-left font-semibold">Tanggal Kerja</th>
                            <th class="py-3 pr-4 text-left font-semibold">Durasi Kirim</th>
                            <th class="py-3 pr-4 text-left font-semibold">Durasi Disetujui</th>
                            <th class="py-3 pr-4 text-left font-semibold">Status QC</th>
                            <th class="py-3 pr-4 text-left font-semibold">Status Bayar</th>
                            <th class="py-3 text-left font-semibold">Catatan Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($reports as $report)
                            <tr>
                                <td class="py-4 pr-4 align-top">
                                    <span class="block max-w-[130px] truncate font-mono text-xs font-bold text-indigo-650" title="{{ $report->id }}">{{ $report->id }}</span>
                                    <span class="block text-[10px] text-slate-400 mt-1">{{ $report->created_at->format('d M Y H:i') }}</span>
                                </td>
                                <td class="py-4 pr-4 align-top">
                                    <span class="block font-bold text-slate-900">{{ $report->partner->full_name }}</span>
                                    <span class="block text-xs text-slate-400">{{ $report->partner->mitra_id }}</span>
                                </td>
                                <td class="py-4 pr-4 align-top font-medium text-slate-800">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                <td class="py-4 pr-4 align-top text-slate-600">{{ $report->submitted_duration_formatted }}</td>
                                <td class="py-4 pr-4 align-top font-bold text-slate-900">{{ $report->approved_duration_formatted }}</td>
                                <td class="py-4 pr-4 align-top">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $qcColors[$report->qc_status] ?? 'bg-gray-50 text-gray-600 border-gray-150' }}">
                                        {{ ucfirst($report->qc_status) }}
                                    </span>
                                </td>
                                <td class="py-4 pr-4 align-top">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $payColors[$report->payment_status] ?? 'bg-gray-50 text-gray-600 border-gray-150' }}">
                                        {{ ucfirst($report->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-4 align-top text-xs text-slate-500 max-w-xs">
                                    {{ $report->verifier_notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2 text-slate-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-semibold">Belum ada riwayat laporan yang sesuai.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div class="pt-5">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
