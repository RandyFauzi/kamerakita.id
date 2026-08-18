<x-app-layout>
    <div class="space-y-4 sm:space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Riwayat Pembayaran Vendor</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2">
                <span class="block text-xs font-bold text-gray-800">Total Saldo (All Time)</span>
                <span class="block text-xl font-bold text-indigo-650 font-mono">Rp{{ number_format($totalEstimatedRevenue, 0, ',', '.') }}</span>
                <span class="text-[10px] text-gray-400">Dari {{ number_format($totalApprovedMinutes / 60, 1) }} jam video ACC</span>
            </div>
            
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2">
                <span class="block text-xs font-bold text-gray-800">Sudah Dibayar Pusat</span>
                <span class="block text-xl font-bold text-emerald-600 font-mono">Rp{{ number_format($totalPaid, 0, ',', '.') }}</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2">
                <span class="block text-xs font-bold text-gray-800">Pending (Belum Dibayar)</span>
                <span class="block text-xl font-bold text-amber-600 font-mono">Rp{{ number_format($totalUnpaid, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 border border-gray-150 shadow-sm overflow-hidden">
            <h3 class="font-bold text-gray-900 mb-4">Rincian Laporan ACC</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">Bulan/Tanggal</th>
                            <th class="py-3 text-left font-semibold">Worker</th>
                            <th class="py-3 text-left font-semibold">Durasi ACC</th>
                            <th class="py-3 text-left font-semibold">Estimasi Tagihan</th>
                            <th class="py-3 text-left font-semibold">Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($approvedReports as $report)
                            <tr>
                                <td class="py-3 text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($report->submission_date)->format('d M Y') }}</td>
                                <td class="py-3 font-medium text-gray-900">{{ $report->partner->full_name }}</td>
                                <td class="py-3 font-bold text-gray-900">{{ $report->approved_duration_minutes ?? 0 }} min</td>
                                <td class="py-3 font-bold text-indigo-650">Rp{{ number_format(($report->approved_duration_minutes / 60) * 65000, 0, ',', '.') }}</td>
                                <td class="py-3">
                                    @if($report->payment_status === 'paid')
                                        <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider">PAID</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider">UNPAID</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-450 text-xs">Belum ada riwayat laporan ACC.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
