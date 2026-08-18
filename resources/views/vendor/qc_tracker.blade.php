<x-app-layout>
    <div class="space-y-4 sm:space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">QC Tracker (Laporan Tim)</h2>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 border border-gray-150 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">Tgl Submit</th>
                            <th class="py-3 text-left font-semibold">ID Vendor</th>
                            <th class="py-3 text-left font-semibold">Worker</th>
                            <th class="py-3 text-left font-semibold">Status</th>
                            <th class="py-3 text-left font-semibold">Catatan Verifikator</th>
                            <th class="py-3 text-left font-semibold">Durasi ACC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($reports as $report)
                            <tr>
                                <td class="py-3 text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($report->submission_date)->format('d M Y') }}</td>
                                <td class="py-3 font-bold text-indigo-650">{{ $report->partner->mitra_id }}</td>
                                <td class="py-3 font-medium text-gray-900">{{ $report->partner->full_name }}</td>
                                <td class="py-3">
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
                                <td class="py-3 text-gray-600 max-w-xs truncate" title="{{ $report->rejection_reason }}">{{ $report->rejection_reason ?? '-' }}</td>
                                <td class="py-3 font-bold text-gray-900">{{ $report->approved_duration_minutes ?? 0 }} min</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-450 text-xs">Belum ada laporan dari tim Anda.</td>
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
