<x-app-layout>
    @php
        $formatMinutes = function ($minutes) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;

            return $hours > 0 ? "{$hours}h {$remainingMinutes}m" : "{$remainingMinutes}m";
        };
    @endphp

    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">Riwayat Laporan</h2>
    </x-slot>

    <div class="py-2 sm:py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            <div class="overflow-hidden rounded-2xl bg-slate-950 p-4 text-white shadow-sm sm:rounded-3xl sm:p-6">
                <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-300">Laporan Kerja</span>
                        <h3 class="mt-1 text-xl font-black tracking-tight">Riwayat Laporan Video</h3>
                        <p class="mt-1 text-xs text-slate-300">Menampilkan seluruh laporan yang dikirim oleh akun Anda.</p>
                    </div>

                    @if($partner->partner_role === 'worker')
                        <a href="{{ route('video-submissions.submit-report.create') }}" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-xs font-black text-slate-900 transition hover:bg-slate-50 md:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m7-7H5"/>
                            </svg>
                            Kirim Laporan Baru
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Total Laporan</span>
                    <strong class="mt-2 block text-2xl font-black text-slate-900">{{ number_format($summary['total_reports'], 0, ',', '.') }}</strong>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Menunggu QC</span>
                    <strong class="mt-2 block text-2xl font-black text-amber-700">{{ number_format($summary['pending_reports'], 0, ',', '.') }}</strong>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Approved</span>
                    <strong class="mt-2 block text-2xl font-black text-emerald-700">{{ number_format($summary['approved_reports'], 0, ',', '.') }}</strong>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-white p-4 shadow-sm sm:p-5">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Ditolak</span>
                    <strong class="mt-2 block text-2xl font-black text-rose-700">{{ number_format($summary['rejected_reports'], 0, ',', '.') }}</strong>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Durasi Unpaid</span>
                    <strong class="mt-2 block text-2xl font-black text-indigo-700">{{ $formatMinutes($summary['unpaid_minutes']) }}</strong>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('video-submissions.report-history', array_merge(request()->except('page'), ['status' => 'all'])) }}" class="rounded-xl px-4 py-2 text-xs font-bold uppercase transition {{ $status === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-500 hover:bg-gray-50' }}">
                    Semua Laporan
                </a>
                <a href="{{ route('video-submissions.report-history', array_merge(request()->except('page'), ['status' => 'pending'])) }}" class="rounded-xl px-4 py-2 text-xs font-bold uppercase transition {{ $status === 'pending' ? 'border border-yellow-200 bg-yellow-100 text-yellow-800 shadow-sm' : 'border border-gray-200 bg-white text-gray-500 hover:bg-gray-50' }}">
                    Pending ({{ $summary['pending_reports'] }})
                </a>
                <a href="{{ route('video-submissions.report-history', array_merge(request()->except('page'), ['status' => 'on_review'])) }}" class="rounded-xl px-4 py-2 text-xs font-bold uppercase transition {{ $status === 'on_review' ? 'border border-blue-200 bg-blue-100 text-blue-800 shadow-sm' : 'border border-gray-200 bg-white text-gray-500 hover:bg-gray-50' }}">
                    On Review ({{ $summary['on_review_reports'] }})
                </a>
                <a href="{{ route('video-submissions.report-history', array_merge(request()->except('page'), ['status' => 'approved'])) }}" class="rounded-xl px-4 py-2 text-xs font-bold uppercase transition {{ $status === 'approved' ? 'border border-emerald-200 bg-emerald-100 text-emerald-800 shadow-sm' : 'border border-gray-200 bg-white text-gray-500 hover:bg-gray-50' }}">
                    Approved ({{ $summary['approved_reports'] }})
                </a>
                <a href="{{ route('video-submissions.report-history', array_merge(request()->except('page'), ['status' => 'rejected'])) }}" class="rounded-xl px-4 py-2 text-xs font-bold uppercase transition {{ $status === 'rejected' ? 'border border-rose-200 bg-rose-100 text-rose-800 shadow-sm' : 'border border-gray-200 bg-white text-gray-500 hover:bg-gray-50' }}">
                    Rejected ({{ $summary['rejected_reports'] }})
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                <form action="{{ route('video-submissions.report-history') }}" method="GET" class="flex flex-col items-end gap-4 md:flex-row">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="w-full flex-1">
                        <label for="search" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Cari Laporan</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari berdasarkan ID Laporan..." class="block w-full rounded-xl border border-gray-200 py-2.5 pl-10 pr-3 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="w-full md:w-48">
                        <label for="start_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="w-full md:w-48">
                        <label for="end_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex w-full gap-2 md:w-auto">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 md:flex-none">Filter</button>
                        <a href="{{ route('video-submissions.report-history') }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 md:flex-none">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">ID Laporan</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal Kerja</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Durasi Kirim</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Durasi Disetujui</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status QC</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status Bayar</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Catatan Admin</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($reports as $report)
                                <tr class="transition-colors hover:bg-gray-50/50">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="block max-w-32 truncate text-sm font-semibold text-indigo-600" title="{{ $report->id }}">{{ substr($report->id, 0, 8) }}...</span>
                                        <span class="mt-1 block text-[10px] text-gray-400">{{ $report->created_at->format('d M Y H:i') }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $report->submitted_duration_formatted }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $report->approved_duration_formatted }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if($report->qc_status === 'pending')
                                            <span class="inline-flex rounded-full border border-yellow-200 bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-800">Pending</span>
                                        @elseif($report->qc_status === 'on_review')
                                            <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">On Review</span>
                                        @elseif($report->qc_status === 'approved')
                                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Approved</span>
                                        @else
                                            <span class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $report->payment_status === 'paid' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                                            {{ $report->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                                        </span>
                                    </td>
                                    <td class="max-w-xs px-6 py-4 text-xs leading-5 text-gray-500">{{ $report->verifier_notes ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        @if($partner->partner_role === 'worker' && $report->qc_status === 'rejected' && $report->payment_status === 'unpaid')
                                            <a href="{{ route('video-submissions.rejected.edit', $report) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 transition hover:bg-indigo-100">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 3.487a2.1 2.1 0 013 2.97L8.25 18.07 4 19l.93-4.25L16.862 3.487z"/>
                                                </svg>
                                                Edit
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm font-semibold text-gray-400">Belum ada riwayat laporan yang sesuai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">{{ $reports->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
