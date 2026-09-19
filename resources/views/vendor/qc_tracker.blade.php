<x-app-layout>
    <div class="space-y-4 sm:space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">QC Tracker (Laporan Tim)</h2>
        </div>

        <div class="mb-4 rounded-[2rem] bg-white p-4 shadow-sm border border-gray-150 md:p-6">
            <form action="{{ route('vendor.reports.index') }}" method="GET" class="flex flex-col items-end gap-4 md:flex-row">
                <div class="w-full flex-1">
                    <label for="search" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Cari Worker / ID</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik nama atau ID..." class="block w-full rounded-xl border border-gray-200 py-2.5 pl-10 pr-3 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="w-full md:w-48">
                    <label for="status" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Status QC</label>
                    <select name="status" id="status" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="on_review" {{ request('status') === 'on_review' ? 'selected' : '' }}>On Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="w-full md:w-40">
                    <label for="start_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Dari Tgl</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="w-full md:w-40">
                    <label for="end_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Sampai Tgl</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex w-full gap-2 md:w-auto">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 md:flex-none">Filter</button>
                    <a href="{{ route('vendor.reports.index') }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 md:flex-none">Reset</a>
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
