<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">Atlas Recordings</h2>
            @php
                $lastSync = \App\Models\AtlasTask::max('updated_at');
                $lastSyncText = $lastSync ? \Carbon\Carbon::parse($lastSync)->timezone('Asia/Makassar')->format('d M Y, H:i') . ' WITA' : '-';
            @endphp
            <div class="inline-flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-gray-100 shadow-sm text-sm font-medium text-gray-600">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Update Terakhir: <span class="font-bold text-gray-800">{{ $lastSyncText }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-2 sm:py-6">
        <div class="space-y-4 sm:space-y-6">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Total Jam Kerja (Main Highlight) -->
                <div class="rounded-[24px] p-7 relative overflow-hidden transition-all duration-300 hover:-translate-y-1" style="background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); box-shadow: 0 12px 30px -10px rgba(37, 99, 235, 0.5);">
                    <!-- Soft top-right ambient glow instead of hard rings -->
                    <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full bg-blue-400 opacity-20 blur-3xl"></div>
                    
                    <div class="flex justify-between items-start mb-8 relative z-10">
                        <div>
                            <h3 class="text-xs font-semibold text-blue-100 uppercase tracking-wider">Total Jam Kerja</h3>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center backdrop-blur-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    
                    <div class="relative z-10 mt-2">
                        <div class="text-4xl sm:text-5xl font-black text-white tracking-tight flex items-baseline gap-1.5 drop-shadow-sm">
                            {{ floor($stats['total_worked'] / 60) }}<span class="text-2xl font-bold text-blue-200/80">h</span>
                            {{ round($stats['total_worked'] % 60) }}<span class="text-2xl font-bold text-blue-200/80">m</span>
                        </div>
                        
                        <div class="mt-6 flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white/15 text-white border border-white/10 tracking-wide uppercase">
                                Seluruh Pekerja
                            </span>
                            <span class="text-[11px] font-medium text-blue-200/80">Durasi Atlas V.2</span>
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div class="bg-white rounded-[24px] p-7 transition-all duration-300 hover:-translate-y-1" style="box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06); border: 1px solid #f8fafc;">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Total Approved</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #ecfdf5;">
                            <svg class="w-5 h-5" style="color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl font-black text-gray-800 tracking-tighter flex items-baseline gap-1 mb-2">
                            {{ floor($stats['total_approved'] / 60) }}<span class="text-lg font-bold text-gray-400">h</span>
                            {{ round($stats['total_approved'] % 60) }}<span class="text-lg font-bold text-gray-400">m</span>
                        </div>
                        @php $pctApp = $stats['total_worked'] > 0 ? round(($stats['total_approved'] / $stats['total_worked']) * 100, 1) : 0; @endphp
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded text-[11px] font-bold" style="background-color: #ecfdf5; color: #10b981;">{{ $pctApp }}%</span>
                            <span class="text-[11px] font-semibold text-gray-400">dari total durasi</span>
                        </div>
                    </div>
                </div>

                <!-- Dalam Ulasan -->
                <div class="bg-white rounded-[24px] p-7 transition-all duration-300 hover:-translate-y-1" style="box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06); border: 1px solid #f8fafc;">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Dalam Ulasan</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #fffbeb;">
                            <svg class="w-5 h-5" style="color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl font-black text-gray-800 tracking-tighter flex items-baseline gap-1 mb-2">
                            {{ floor($stats['total_review'] / 60) }}<span class="text-lg font-bold text-gray-400">h</span>
                            {{ round($stats['total_review'] % 60) }}<span class="text-lg font-bold text-gray-400">m</span>
                        </div>
                        @php $pctRev = $stats['total_worked'] > 0 ? round(($stats['total_review'] / $stats['total_worked']) * 100, 1) : 0; @endphp
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded text-[11px] font-bold" style="background-color: #fffbeb; color: #d97706;">{{ $pctRev }}%</span>
                            <span class="text-[11px] font-semibold text-gray-400">dari total durasi</span>
                        </div>
                    </div>
                </div>

                <!-- Ditolak -->
                <div class="bg-white rounded-[24px] p-7 transition-all duration-300 hover:-translate-y-1" style="box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06); border: 1px solid #f8fafc;">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Ditolak</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #fff1f2;">
                            <svg class="w-5 h-5" style="color: #f43f5e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl font-black text-gray-800 tracking-tighter flex items-baseline gap-1 mb-2">
                            {{ floor($stats['total_rejected'] / 60) }}<span class="text-lg font-bold text-gray-400">h</span>
                            {{ round($stats['total_rejected'] % 60) }}<span class="text-lg font-bold text-gray-400">m</span>
                        </div>
                        @php $pctRej = $stats['total_worked'] > 0 ? round(($stats['total_rejected'] / $stats['total_worked']) * 100, 1) : 0; @endphp
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded text-[11px] font-bold" style="background-color: #fff1f2; color: #e11d48;">{{ $pctRej }}%</span>
                            <span class="text-[11px] font-semibold text-gray-400">dari total durasi</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white rounded-[20px] border border-gray-100 p-6 shadow-sm mb-4 relative z-0">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Performance Overview</h3>
                        <p class="text-xs font-medium text-gray-400 mt-1">Total Durasi & Approved (Dalam Menit)</p>
                    </div>
                </div>
                <div class="overflow-x-auto pb-2" style="scrollbar-width: none;">
                    <div id="admin-trend-chart" class="-ml-2" style="min-width: 800px;"></div>
                </div>
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
                                        <div class="text-[11px] text-gray-500 font-medium">{{ $record->created_at ? $record->created_at->format('H:i') . ' WITA' : '-' }}</div>
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
                var categories = trendData.map(item => {
                    var d = new Date(item.task_date);
                    var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    return d.getDate() + ' ' + months[d.getMonth()];
                });
                
                var workedSeries = trendData.map(item => parseFloat(item.worked));
                var approvedSeries = trendData.map(item => parseFloat(item.approved));
                var sisaSeries = trendData.map(item => parseFloat(item.worked) - parseFloat(item.approved));
                
                var options = {
                    series: [{
                        name: 'Approved',
                        data: approvedSeries
                    }, {
                        name: 'Sisa Durasi',
                        data: sisaSeries
                    }],
                    chart: {
                        type: 'bar',
                        stacked: true,
                        height: 320,
                        toolbar: { show: false },
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    colors: ['#3b82f6', '#e2e8f0'],
                    plotOptions: {
                        bar: {
                            columnWidth: '40%',
                            borderRadius: 6,
                            borderRadiusApplication: 'end',
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { show: false },
                    xaxis: {
                        categories: categories,
                        labels: { 
                            rotate: 0, 
                            hideOverlappingLabels: false,
                            style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } 
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    yaxis: {
                        title: { text: 'Total Menit', style: { color: '#94a3b8', fontSize: '10px', fontWeight: 600, cssClass: 'uppercase tracking-widest' } },
                        labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 500 } }
                    },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
                    legend: { show: false },
                    fill: {
                        type: 'solid',
                        opacity: 1
                    },
                    tooltip: {
                        shared: true,
                        custom: function({series, seriesIndex, dataPointIndex, w}) {
                            var approved = series[0][dataPointIndex];
                            var total = workedSeries[dataPointIndex];
                            return '<div class="p-3 bg-white shadow-xl rounded-xl border border-gray-100 min-w-[140px]">' +
                                '<div class="font-bold text-gray-800 mb-2 border-b border-gray-100 pb-2">' + w.globals.labels[dataPointIndex] + '</div>' +
                                '<div class="flex items-center justify-between gap-4 mb-1.5"><span class="text-gray-500 text-xs font-semibold flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>Approved</span> <span class="font-bold text-gray-900">' + approved.toFixed(1) + ' mnt</span></div>' +
                                '<div class="flex items-center justify-between gap-4"><span class="text-gray-500 text-xs font-semibold flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>Total Durasi</span> <span class="font-bold text-gray-900">' + total.toFixed(1) + ' mnt</span></div>' +
                                '</div>';
                        }
                    }
                };

                // Dynamic min-width so it scrolls nicely on many dates
                var minW = Math.max(800, categories.length * 60);
                document.getElementById('admin-trend-chart').style.minWidth = minW + 'px';

                var chart = new ApexCharts(document.querySelector("#admin-trend-chart"), options);
                chart.render();
            }
        });
    </script>
</x-app-layout>
