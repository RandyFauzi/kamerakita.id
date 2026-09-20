<x-app-layout>
    <div class="space-y-4 sm:space-y-6">
                <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">QC Tracker (Laporan Tim)</h2>
        </div>

                <!-- Highlight Info / Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-2">
            <!-- Card 1: Total Laporan -->
            <div class="rounded-[2rem] bg-indigo-500 p-6 shadow-sm border border-indigo-400 flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-100 mb-1">Total Laporan</p>
                        <p class="text-3xl font-black text-white">{{ number_format($stats['total_reports']) }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-400/30 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-[10px] text-indigo-100 font-medium bg-indigo-600/50 px-2.5 py-1 rounded-full border border-indigo-400/30">Sesuai filter aktif</span>
                </div>
            </div>
            
            <!-- Card 2: Durasi Dikirim -->
            <div class="rounded-[2rem] bg-white p-6 shadow-sm border border-gray-150 flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Dikirim</p>
                        <div class="flex items-baseline gap-1">
                            @if($stats['total_submitted'] >= 60)
                                <p class="text-3xl font-black text-gray-800">{{ floor($stats['total_submitted'] / 60) }}</p>
                                <p class="text-sm font-bold text-gray-400">j</p>
                                <p class="text-3xl font-black text-gray-800 ml-1">{{ $stats['total_submitted'] % 60 }}</p>
                                <p class="text-sm font-bold text-gray-400">m</p>
                            @else
                                <p class="text-3xl font-black text-gray-800">{{ $stats['total_submitted'] }}</p>
                                <p class="text-sm font-bold text-gray-400">m</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-50 text-gray-400 border border-gray-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-[10px] text-gray-500 font-medium bg-gray-50 px-2.5 py-1 rounded-full border border-gray-200">Telah disubmit worker</span>
                </div>
            </div>

            <!-- Card 3: Durasi Approved -->
            <div class="rounded-[2rem] bg-white p-6 shadow-sm border border-gray-150 flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-500 mb-1">Durasi Approved</p>
                        <div class="flex items-baseline gap-1">
                            @if($stats['total_approved'] >= 60)
                                <p class="text-3xl font-black text-gray-800">{{ floor($stats['total_approved'] / 60) }}</p>
                                <p class="text-sm font-bold text-gray-400">j</p>
                                <p class="text-3xl font-black text-gray-800 ml-1">{{ $stats['total_approved'] % 60 }}</p>
                                <p class="text-sm font-bold text-gray-400">m</p>
                            @else
                                <p class="text-3xl font-black text-gray-800">{{ $stats['total_approved'] }}</p>
                                <p class="text-sm font-bold text-gray-400">m</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-[10px] text-emerald-600 font-medium bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">Disetujui Admin</span>
                </div>
            </div>

            <!-- Card 4: Durasi Pending -->
            <div class="rounded-[2rem] bg-white p-6 shadow-sm border border-gray-150 flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-amber-500 mb-1">Durasi Pending</p>
                        <div class="flex items-baseline gap-1">
                            @if($stats['total_pending'] >= 60)
                                <p class="text-3xl font-black text-gray-800">{{ floor($stats['total_pending'] / 60) }}</p>
                                <p class="text-sm font-bold text-gray-400">j</p>
                                <p class="text-3xl font-black text-gray-800 ml-1">{{ $stats['total_pending'] % 60 }}</p>
                                <p class="text-sm font-bold text-gray-400">m</p>
                            @else
                                <p class="text-3xl font-black text-gray-800">{{ $stats['total_pending'] }}</p>
                                <p class="text-sm font-bold text-gray-400">m</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500 border border-amber-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-[10px] text-amber-600 font-medium bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100">Menunggu QC</span>
                </div>
            </div>
        </div>

        <div class="mb-4 rounded-[2rem] bg-white p-4 shadow-sm border border-gray-150 md:p-6">

            <form action="{{ route('vendor.reports.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <!-- Search -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="search" class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-500">Cari Worker / ID</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik nama / ID..." class="block w-full rounded-full border border-gray-200 bg-gray-50/50 py-2.5 pl-10 pr-4 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                
                <!-- Status QC -->
                <div class="sm:col-span-1 lg:col-span-2">
                    <label for="status" class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-500">Status QC</label>
                    <select name="status" id="status" class="block w-full rounded-full border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="on_review" {{ request('status') === 'on_review' ? 'selected' : '' }}>On Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                
                <!-- Dari Tgl -->
                <div class="sm:col-span-1 lg:col-span-2">
                    <label for="start_date" class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-500">Dari Tgl</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="block w-full rounded-full border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <!-- Sampai Tgl -->
                <div class="sm:col-span-1 lg:col-span-2">
                    <label for="end_date" class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-500">Sampai Tgl</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="block w-full rounded-full border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <!-- Buttons -->
                <div class="sm:col-span-1 lg:col-span-3 flex w-full gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-full bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 shadow-sm">Filter</button>
                    <a href="{{ route('vendor.reports.index') }}" class="inline-flex flex-1 items-center justify-center rounded-full border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 shadow-sm">Reset</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 border border-gray-150 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">Tgl Submit</th>
                            <th class="py-3 text-left font-semibold">ID Vendor</th>
                            <th class="py-3 text-left font-semibold">Worker</th>
                            <th class="py-3 text-left font-semibold">Durasi Kirim</th>
                            <th class="py-3 text-left font-semibold">Durasi ACC</th>
                            <th class="py-3 text-left font-semibold">Status</th>
                            <th class="py-3 text-left font-semibold">Catatan Verifikator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($reports as $report)
                            <tr>
                                <td class="py-3 text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($report->submission_date)->format('d M Y') }}</td>
                                <td class="py-3 font-bold text-indigo-650">{{ $report->partner->mitra_id }}</td>
                                <td class="py-3 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $report->partner->full_name }}</div>
                                    <div class="text-xs text-gray-400 font-normal">{{ $report->partner->user->email ?? $report->partner->email ?? '' }}</div>
                                </td>
                                <td class="py-3 whitespace-nowrap">
                                    <span class="font-bold text-slate-800">{{ $report->submitted_duration_minutes ?? 0 }} min</span>
                                    @if(($report->submitted_duration_minutes ?? 0) >= 60)
                                        <span class="text-xs text-gray-400 font-normal">({{ $report->submitted_duration_formatted }})</span>
                                    @endif
                                </td>
                                <td class="py-3 whitespace-nowrap">
                                    <span class="font-bold text-gray-900">{{ $report->approved_duration_minutes ?? 0 }} min</span>
                                    @if(($report->approved_duration_minutes ?? 0) >= 60)
                                        <span class="text-xs text-emerald-600 font-normal">({{ $report->approved_duration_formatted }})</span>
                                    @endif
                                </td>
                                <td class="py-3 whitespace-nowrap">
                                    @if($report->qc_status === 'pending')
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold">Pending</span>
                                    @elseif($report->qc_status === 'on_review')
                                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">On Review</span>
                                    @elseif($report->qc_status === 'approved')
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Approved</span>
                                    @elseif($report->qc_status === 'rejected')
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Rejected</span>
                                    @endif
                                </td>
                                <td class="py-3 text-gray-600 max-w-xs truncate" title="{{ $report->verifier_notes ?? $report->rejection_reason }}">{{ $report->verifier_notes ?? $report->rejection_reason ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-450 text-xs">Belum ada laporan dari tim Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</x-app-layout>


