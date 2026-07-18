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

    <div class="space-y-4 sm:space-y-6">
        <!-- Page Header Card -->
        <div class="overflow-hidden shadow-sm rounded-2xl sm:rounded-3xl p-4 sm:p-6 text-white relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);">
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
                    <a href="{{ route('video-submissions.submit-report.create') }}" class="w-full md:w-auto min-h-11 inline-flex items-center justify-center gap-2 px-5 py-3 bg-white hover:bg-slate-50 text-slate-900 rounded-xl sm:rounded-2xl text-xs font-black shadow-sm transition duration-200 shrink-0">
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

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Laporan</span>
                <strong class="block text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['total_reports'], 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Menunggu QC</span>
                <strong class="block text-2xl font-black text-amber-700 mt-2">{{ number_format($summary['pending_reports'], 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Approved</span>
                <strong class="block text-2xl font-black text-emerald-700 mt-2">{{ number_format($summary['approved_reports'], 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-rose-100 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Ditolak</span>
                <strong class="block text-2xl font-black text-rose-700 mt-2">{{ number_format($summary['rejected_reports'], 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-150 shadow-sm">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Durasi Unpaid</span>
                <strong class="block text-2xl font-black text-indigo-650 mt-2">{{ $formatMinutes($summary['unpaid_minutes']) }}</strong>
            </div>
        </div>

        <form method="GET" action="{{ route('video-submissions.report-history') }}" class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-150 shadow-sm">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <!-- Cari Laporan -->
                <div class="flex-1 w-full">
                    <label for="search" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Cari Laporan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari berdasarkan ID Laporan..." class="block w-full min-h-11 pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                    </div>
                </div>

                <!-- Status QC -->
                <div class="w-full md:w-48">
                    <label for="qc_status" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Status QC</label>
                    <select id="qc_status" name="qc_status" class="block w-full min-h-11 py-2.5 px-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white">
                        <option value="">Semua QC</option>
                        <option value="pending" @selected(request('qc_status') === 'pending')>Pending</option>
                        <option value="on_review" @selected(request('qc_status') === 'on_review')>On Review</option>
                        <option value="approved" @selected(request('qc_status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('qc_status') === 'rejected')>Rejected</option>
                    </select>
                </div>

                <!-- Status Bayar -->
                <div class="w-full md:w-48">
                    <label for="payment_status" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Status Bayar</label>
                    <select id="payment_status" name="payment_status" class="block w-full min-h-11 py-2.5 px-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white">
                        <option value="">Semua Bayar</option>
                        <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2 w-full md:w-auto shrink-0">
                    <button type="submit" class="flex-1 md:flex-none justify-center inline-flex items-center px-6 py-2.5 bg-gray-900 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-gray-800 transition-colors duration-200 shadow-sm shadow-gray-900/10">
                        Filter
                    </button>
                    <a href="{{ route('video-submissions.report-history') }}" class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-250 transition-all">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="bg-white rounded-2xl sm:rounded-[32px] p-4 sm:p-6 border border-gray-150 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 pb-4 border-b border-gray-100 mb-4">
                <span class="block text-sm font-bold text-gray-900">Daftar Riwayat Laporan</span>
                <span class="text-xs text-gray-400">{{ $reports->total() }} data ditemukan</span>
            </div>

            <div class="space-y-3 sm:hidden">
                @forelse($reports as $report)
                    <article class="rounded-xl border border-gray-150 p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <strong class="block text-sm text-slate-900 truncate">{{ $report->partner->full_name }}</strong>
                                <span class="block text-xs text-slate-400">{{ $report->partner->mitra_id }} · {{ $report->submission_date->translatedFormat('d M Y') }}</span>
                            </div>
                            <span class="inline-flex shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $qcColors[$report->qc_status] ?? 'bg-gray-50 text-gray-600 border-gray-150' }}">
                                {{ ucfirst(str_replace('_', ' ', $report->qc_status)) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div><span class="block text-slate-400">Durasi kirim</span><strong class="text-slate-800">{{ $report->submitted_duration_formatted }}</strong></div>
                            <div><span class="block text-slate-400">Disetujui</span><strong class="text-slate-800">{{ $report->approved_duration_formatted }}</strong></div>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <span class="font-mono text-[10px] text-slate-400">{{ substr($report->id, 0, 8) }}...</span>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $payColors[$report->payment_status] ?? 'bg-gray-50 text-gray-600 border-gray-150' }}">{{ ucfirst($report->payment_status) }}</span>
                        </div>
                        @if($report->verifier_notes)
                            <p class="text-xs leading-5 text-slate-500 bg-slate-50 rounded-lg p-3">{{ $report->verifier_notes }}</p>
                        @endif
                        @if($partner->partner_role === 'worker' && $report->qc_status === 'rejected')
                            <a href="{{ route('video-submissions.rejected.edit', $report) }}" class="min-h-11 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-indigo-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 3.487a2.1 2.1 0 013 2.97L8.25 18.07 4 19l.93-4.25L16.862 3.487z"/>
                                </svg>
                                Edit
                            </a>
                        @endif
                    </article>
                @empty
                    <p class="py-8 text-center text-slate-400 text-sm font-semibold">Belum ada riwayat laporan yang sesuai.</p>
                @endforelse
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 pr-4 text-left font-semibold hidden sm:table-cell">ID Laporan</th>
                            <th class="py-3 pr-4 text-left font-semibold">Worker</th>
                            <th class="py-3 pr-4 text-left font-semibold">Tanggal Kerja</th>
                            <th class="py-3 pr-4 text-left font-semibold">Durasi Kirim</th>
                            <th class="py-3 pr-4 text-left font-semibold">Durasi Disetujui</th>
                            <th class="py-3 pr-4 text-left font-semibold">Status QC</th>
                            <th class="py-3 pr-4 text-left font-semibold">Status Bayar</th>
                            <th class="py-3 text-left font-semibold hidden md:table-cell">Catatan Verifikasi</th>
                            <th class="py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($reports as $report)
                            <tr>
                                <td class="py-4 pr-4 align-top hidden sm:table-cell">
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
                                <td class="py-4 pr-4 align-top text-xs text-slate-500 max-w-xs hidden md:table-cell">
                                    {{ $report->verifier_notes ?: '-' }}
                                </td>
                                <td class="py-4 align-top text-right">
                                    @if($partner->partner_role === 'worker' && $report->qc_status === 'rejected')
                                        <a href="{{ route('video-submissions.rejected.edit', $report) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-2 text-xs font-black text-indigo-700 transition hover:bg-indigo-100">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 3.487a2.1 2.1 0 013 2.97L8.25 18.07 4 19l.93-4.25L16.862 3.487z"/>
                                            </svg>
                                            Edit
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center">
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
