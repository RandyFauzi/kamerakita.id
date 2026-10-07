<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">Atlas Recordings</h2>
    </x-slot>

    <div class="py-2 sm:py-6">
        <div class="space-y-4 sm:space-y-6">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-2">
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Total Jam Kerja</h3>
                    <div class="text-2xl font-black text-gray-900">{{ floor($stats['total_worked'] / 60) }}h {{ round($stats['total_worked'] % 60) }}m</div>
                </div>
                <div class="bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-emerald-50"></div>
                    <h3 class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-2 relative z-10">Total Approved</h3>
                    <div class="text-2xl font-black text-emerald-700 relative z-10">{{ floor($stats['total_approved'] / 60) }}h {{ round($stats['total_approved'] % 60) }}m</div>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-amber-50"></div>
                    <h3 class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mb-2 relative z-10">Total Review</h3>
                    <div class="text-2xl font-black text-amber-700 relative z-10">{{ floor($stats['total_review'] / 60) }}h {{ round($stats['total_review'] % 60) }}m</div>
                </div>
                <div class="bg-white rounded-2xl border border-rose-100 p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-rose-50"></div>
                    <h3 class="text-[10px] font-bold text-rose-600 uppercase tracking-wider mb-2 relative z-10">Total Rejected</h3>
                    <div class="text-2xl font-black text-rose-700 relative z-10">{{ floor($stats['total_rejected'] / 60) }}h {{ round($stats['total_rejected'] % 60) }}m</div>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm mb-2">
                <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Grafik Tren Pekerjaan (Menit)</h3>
                <div id="admin-trend-chart"></div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white border border-gray-100 p-4 shadow-sm sm:p-6">
                <!-- Header / Filters -->
                <form action="{{ route('admin.recordings.index') }}" method="GET" class="flex flex-col items-end gap-4 md:flex-row mb-6">
                    <div class="w-full flex-1">
                        <label for="search" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Pencarian</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari email, task..." class="block w-full rounded-xl border border-gray-200 py-2.5 px-4 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="w-full md:w-48">
                        <label for="start_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="w-full md:w-48">
                        <label for="end_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700 md:w-auto">
                            Filter
                        </button>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Tanggal & Waktu</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Worker (Email)</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Task</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider text-center">Durasi Total</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider text-center">Approve</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider text-center">Reject</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider text-center">Pending</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($recordings as $record)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($record->task_date)->format('d M Y') }}</div>
                                        @if($record->recorded_at)
                                            <div class="text-[11px] text-gray-500 font-medium">{{ \Carbon\Carbon::parse($record->recorded_at)->format('H:i') }} UTC</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $record->atlasWorker->atlas_email ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-800 line-clamp-2" title="{{ $record->task_name }}">{{ $record->task_name }}</div>
                                        @if($record->notes)
                                            <div class="text-[10px] text-amber-600 mt-1 line-clamp-1" title="{{ $record->notes }}"><span class="font-bold">Note:</span> {{ $record->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center font-bold text-gray-900">
                                        {{ number_format($record->worked_minutes, 1) }} mnt
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        @if($record->approved_minutes > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                                                {{ number_format($record->approved_minutes, 1) }} m
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        @if($record->rejected_minutes > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 border border-rose-200">
                                                {{ number_format($record->rejected_minutes, 1) }} m
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        @if($record->review_minutes > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2.5 py-1 text-[11px] font-bold text-yellow-700 border border-yellow-200">
                                                {{ number_format($record->review_minutes, 1) }} m
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            <span class="font-medium">Belum ada data rekaman Atlas yang tersinkronisasi.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $recordings->links() }}
                </div>
            </div>
            
        </div>
    </div>
    <!-- ApexCharts for Admin -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var trendData = @json($trend);
            
            if(trendData.length > 0 && document.querySelector("#admin-trend-chart")) {
                var categories = trendData.map(item => item.task_date);
                var workedSeries = trendData.map(item => parseFloat(item.worked).toFixed(1));
                var approvedSeries = trendData.map(item => parseFloat(item.approved).toFixed(1));
                
                var options = {
                    series: [{
                        name: 'Total Durasi',
                        data: workedSeries
                    }, {
                        name: 'Approved',
                        data: approvedSeries
                    }],
                    chart: {
                        type: 'area',
                        height: 250,
                        toolbar: { show: false },
                        animations: { enabled: true }
                    },
                    colors: ['#818cf8', '#10b981'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: {
                        categories: categories,
                        labels: { style: { colors: '#9ca3af', fontSize: '11px' } },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
                    },
                    grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
                    legend: { show: false }
                };

                var chart = new ApexCharts(document.querySelector("#admin-trend-chart"), options);
                chart.render();
            }
        });
    </script>
</x-app-layout>
