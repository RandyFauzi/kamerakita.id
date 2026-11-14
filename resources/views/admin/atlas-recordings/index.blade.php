<x-app-layout>
    @php $isVendorView = $isVendorView ?? false; @endphp
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">{{ $isVendorView ? 'Recording Tim' : 'Atlas Recordings' }}</h2>
            @php
                $lastSync = $isVendorView ? $lastSync : \App\Models\AtlasWorker::max('last_synced_at');
            @endphp
            @if($isVendorView)
            <div class="inline-flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-gray-100 shadow-sm text-sm font-medium text-gray-600">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Update Terakhir: <span id="last-sync-time" data-utc="{{ $lastSync ? \Carbon\Carbon::parse($lastSync)->toISOString() : '' }}" class="font-bold text-gray-800">-</span>
            </div>
            @else
                @include('admin.atlas-recordings.sync-panel')
            @endif
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
                                {{ $isVendorView ? 'Seluruh Worker Tim' : 'Seluruh Pekerja' }}
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
                        @unless($isVendorView)
                        <div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-3">
                            <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider">
                                <span class="text-blue-500">PAID (Lunas)</span>
                                <span class="text-gray-700 bg-blue-50 px-2 py-0.5 rounded">{{ floor($stats['total_paid'] / 60) }}h {{ round($stats['total_paid'] % 60) }}m</span>
                            </div>
                            <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider">
                                <span class="text-gray-400">UNPAID (Pending)</span>
                                <span class="text-gray-700 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded">{{ floor($stats['total_unpaid'] / 60) }}h {{ round($stats['total_unpaid'] % 60) }}m</span>
                            </div>
                        </div>
                        @endunless
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
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Performance Overview</h3>
                        <p class="text-xs font-medium text-gray-400 mt-1">Approved, Rejected, Pending & Deferred (Dalam Menit)</p>
                    </div>
                    @if(count($trend) > 0)
                    <div class="inline-flex bg-gray-100/80 p-1 rounded-xl items-center shadow-inner">
                        <button onclick="updateChartTimeframe('daily')" id="btn-tf-daily" class="tf-btn px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all focus:outline-none bg-white shadow-sm text-blue-600">Harian</button>
                        <button onclick="updateChartTimeframe('weekly')" id="btn-tf-weekly" class="tf-btn px-3.5 py-1.5 text-xs font-bold text-gray-500 rounded-lg transition-all hover:text-gray-900 focus:outline-none">Mingguan</button>
                        <button onclick="updateChartTimeframe('monthly')" id="btn-tf-monthly" class="tf-btn px-3.5 py-1.5 text-xs font-bold text-gray-500 rounded-lg transition-all hover:text-gray-900 focus:outline-none">Bulanan</button>
                    </div>
                    @endif
                </div>
                <div id="chart-scroll-container" class="overflow-x-auto pb-4 custom-scrollbar">
                    @if(count($trend) > 0)
                        <div id="admin-trend-chart" class="-ml-2 pr-4" style="min-width: 800px;"></div>
                    @else
                        <div class="flex items-center justify-center h-48 bg-gray-50 rounded-xl border border-gray-100 border-dashed">
                            <span class="text-gray-400 font-medium text-sm">Belum ada data rekaman (Chart kosong)</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white border border-gray-100 p-4 shadow-sm sm:p-6">
                <!-- Header / Filters -->
                <form action="{{ route($isVendorView ? 'vendor.recordings.index' : 'admin.recordings.index') }}" method="GET" class="flex flex-col items-end gap-3 md:flex-row mb-6">
                    <div class="w-full" style="flex: 1 1 260px; min-width: 220px;">
                        <label for="search" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Pencarian</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari email, task..." class="block w-full rounded-xl border border-gray-200 py-2.5 px-4 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="w-full md:w-auto" style="flex: 0 0 auto; width: 155px;">
                        <label for="source" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">{{ app()->getLocale() === 'en' ? 'Data source' : 'Sumber Data' }}</label>
                        <select name="source" id="source" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">{{ app()->getLocale() === 'en' ? 'All sources' : 'Semua sumber' }}</option>
                            <option value="atlas" @selected(request('source') === 'atlas')>Atlas</option>
                            <option value="1618" @selected(request('source') === '1618')>1618</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto" style="flex: 0 0 auto; width: 155px;">
                        <label for="start_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    </div>
                    <div class="w-full md:w-auto" style="flex: 0 0 auto; width: 155px;">
                        <label for="end_date" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    </div>
                    <div class="w-full md:w-auto" style="flex: 0 0 auto; width: 140px;">
                        @if($isVendorView)
                        <label for="worker_id" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Akun Worker</label>
                        <select name="worker_id" id="worker_id" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Semua akun tim</option>
                            @foreach($workers as $worker)<option value="{{ $worker->id }}" @selected(request('worker_id') == $worker->id)>{{ $worker->atlas_email }}</option>@endforeach
                        </select>
                        @else
                        <label for="status" class="mb-2 block font-mono text-xs font-bold uppercase tracking-wider text-gray-400">Status Gaji</label>
                        <select name="status" id="status" class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Semua</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>PAID</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>PENDING</option>
                            <option value="sisa" {{ request('status') === 'sisa' ? 'selected' : '' }}>SISA</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>REJECTED</option>
                        </select>
                        @endif
                    </div>
                    <div class="w-full md:w-auto" style="flex: 0 0 auto;">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700 md:w-auto shadow-sm">
                            Filter
                        </button>
                    </div>
                </form>

                <p class="text-sm text-violet-700">Deferred: {{ number_format($stats['total_deferred'], 2) }} menit. Ditampilkan terpisah dari pending dan rejected.</p>
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
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider text-center">Deferred</th>
                                @unless($isVendorView)<th scope="col" class="px-6 py-4 font-bold tracking-wider text-center">Status Gaji</th>@endunless
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($recordings as $record)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="local-date font-bold text-gray-900" data-utc="{{ $record->recorded_at ? \Carbon\Carbon::parse($record->recorded_at)->toISOString() : ($record->created_at ? $record->created_at->toISOString() : '') }}">{{ \Carbon\Carbon::parse($record->task_date)->format('d M Y') }}</div>
                                        <div class="local-time text-[11px] text-gray-500 font-medium" data-utc="{{ $record->recorded_at ? \Carbon\Carbon::parse($record->recorded_at)->toISOString() : ($record->created_at ? $record->created_at->toISOString() : '') }}">-</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $record->atlasWorker->atlas_email ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-800 line-clamp-2" title="{{ $record->task_name }}">{{ $record->task_name }}</div>
                                        @if($record->source_status)
                                            <div class="text-xs text-gray-600">Atlas: {{ $record->source_status }}</div>
                                            @if($record->ingest_source === '1618')
                                                <div class="text-[10px] text-indigo-600 mt-1">Sumber: 1618</div>
                                            @endif
                                        @endif
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
                                        <x-recording-qc-delta :record="$record" category="approved" />
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        @if($record->rejected_minutes > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 border border-rose-200">
                                                {{ number_format($record->rejected_minutes, 1) }} m
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                        <x-recording-qc-delta :record="$record" category="rejected" />
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
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-violet-700">
                                        {{ number_format($record->deferred_minutes, 2) }} m
                                    </td>
                                    @unless($isVendorView)
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        @if($record->payroll_id)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 border border-blue-200">
                                                PAID
                                            </span>
                                        @elseif($record->approved_minutes == 0 && $record->review_minutes == 0 && $record->deferred_minutes == 0 && $record->rejected_minutes > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-700 border border-red-200">
                                                REJECTED
                                            </span>
                                        @elseif(str_ends_with($record->task_name, '(Sisa)'))
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 border border-amber-200" title="Sisa durasi dari pemotongan presisi (Belum Dibayar)">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                SISA
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1 text-[11px] font-bold text-gray-500 border border-gray-200">
                                                PENDING
                                            </span>
                                        @endif
                                    </td>
                                    @endunless
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isVendorView ? 8 : 9 }}" class="px-6 py-12 text-center text-gray-500">
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
        window.adminTrendChart = null;
        window.rawTrendData = [];
        window.currentWorkedSeries = [];
        window.parseTaskDate = function(dateStr) {
            if (!dateStr) return new Date();
            var p = String(dateStr).split('-');
            if (p.length === 3) {
                return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
            }
            return new Date(dateStr);
        };
        var parseTaskDate = window.parseTaskDate;
        var chartMonths = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        window.recordingStatusSeries = function(rows) {
            return [['Approved', 'approved'], ['Rejected', 'rejected'], ['Pending', 'pending'], ['Deferred', 'deferred']]
                .map(function(status) { return { name: status[0], data: rows.map(function(row) { return row[status[1]] || 0; }) }; });
        };

        window.updateChartTimeframe = function(tf) {
            if(!window.adminTrendChart || !window.rawTrendData.length) return;
            
            document.querySelectorAll('.tf-btn').forEach(el => {
                el.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                el.classList.add('text-gray-500');
            });
            let btn = document.getElementById('btn-tf-' + tf);
            if(btn) {
                btn.classList.remove('text-gray-500');
                btn.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
            }

            var grouped = [];
            var months = chartMonths;

            if (tf === 'daily') {
                grouped = window.rawTrendData.slice(-30).map(item => {
                    var d = parseTaskDate(item.task_date);
                    return {
                        label: d.getDate() + ' ' + months[d.getMonth()],
                        worked: parseFloat(item.worked) || 0,
                        approved: parseFloat(item.approved) || 0,
                        rejected: parseFloat(item.rejected) || 0,
                        pending: parseFloat(item.review) || 0,
                        deferred: parseFloat(item.deferred) || 0
                    };
                });
            } else if (tf === 'weekly') {
                var weeksMap = {};
                window.rawTrendData.forEach(item => {
                    var d = parseTaskDate(item.task_date);
                    var day = d.getDay();
                    var diff = d.getDate() - day + (day === 0 ? -6 : 1); 
                    var monday = new Date(d.setDate(diff));
                    var key = monday.getDate() + ' ' + months[monday.getMonth()];
                    if(!weeksMap[key]) weeksMap[key] = { worked: 0, approved: 0, rejected: 0, pending: 0, deferred: 0 };
                    weeksMap[key].worked += (parseFloat(item.worked) || 0);
                    weeksMap[key].approved += (parseFloat(item.approved) || 0);
                    weeksMap[key].rejected += (parseFloat(item.rejected) || 0);
                    weeksMap[key].pending += (parseFloat(item.review) || 0);
                    weeksMap[key].deferred += (parseFloat(item.deferred) || 0);
                });
                for(var k in weeksMap) {
                    grouped.push({ label: 'Mgg ' + k, worked: weeksMap[k].worked, approved: weeksMap[k].approved, rejected: weeksMap[k].rejected, pending: weeksMap[k].pending, deferred: weeksMap[k].deferred });
                }
                grouped = grouped.slice(-12);
            } else if (tf === 'monthly') {
                var mthsMap = {};
                window.rawTrendData.forEach(item => {
                    var d = parseTaskDate(item.task_date);
                    var key = months[d.getMonth()] + ' ' + d.getFullYear();
                    if(!mthsMap[key]) mthsMap[key] = { worked: 0, approved: 0, rejected: 0, pending: 0, deferred: 0 };
                    mthsMap[key].worked += (parseFloat(item.worked) || 0);
                    mthsMap[key].approved += (parseFloat(item.approved) || 0);
                    mthsMap[key].rejected += (parseFloat(item.rejected) || 0);
                    mthsMap[key].pending += (parseFloat(item.review) || 0);
                    mthsMap[key].deferred += (parseFloat(item.deferred) || 0);
                });
                for(var k in mthsMap) {
                    grouped.push({ label: k, worked: mthsMap[k].worked, approved: mthsMap[k].approved, rejected: mthsMap[k].rejected, pending: mthsMap[k].pending, deferred: mthsMap[k].deferred });
                }
            }

            var cats = grouped.map(item => item.label);

            window.currentWorkedSeries = grouped.map(item => item.worked);

            var minW = Math.max(800, cats.length * 60);
            document.getElementById('admin-trend-chart').style.minWidth = minW + 'px';

            window.adminTrendChart.updateOptions({ xaxis: { categories: cats } });
            window.adminTrendChart.updateSeries(window.recordingStatusSeries(grouped));
        };

        document.addEventListener("DOMContentLoaded", function() {
            var trendData = @json($trend);
            window.rawTrendData = trendData;
            
            if(trendData.length > 0 && document.querySelector("#admin-trend-chart")) {
                var months = chartMonths;
                
                var initialData = trendData.slice(-30).map(item => {
                    var d = parseTaskDate(item.task_date);
                    return {
                        label: d.getDate() + ' ' + months[d.getMonth()],
                        worked: parseFloat(item.worked) || 0,
                        approved: parseFloat(item.approved) || 0,
                        rejected: parseFloat(item.rejected) || 0,
                        pending: parseFloat(item.review) || 0,
                        deferred: parseFloat(item.deferred) || 0
                    };
                });

                var categories = initialData.map(i => i.label);
                window.currentWorkedSeries = initialData.map(i => i.worked);

                
                var options = {
                    series: window.recordingStatusSeries(initialData),
                    chart: {
                        type: 'bar',
                        stacked: true,
                        height: 320,
                        toolbar: { show: false },
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    colors: ['#3b82f6', '#ef4444', '#f59e0b', '#8b5cf6'],
                    plotOptions: { bar: { columnWidth: '40%', borderRadius: 6, borderRadiusApplication: 'end' } },
                    dataLabels: { enabled: false },
                    stroke: { show: false },
                    xaxis: {
                        categories: categories,
                        labels: { rotate: 0, hideOverlappingLabels: false, style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
                        axisBorder: { show: false }, axisTicks: { show: false },
                    },
                    yaxis: {
                        title: { text: 'Total Menit', style: { color: '#94a3b8', fontSize: '10px', fontWeight: 600, cssClass: 'uppercase tracking-widest' } },
                        labels: { 
                            style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 500 },
                            formatter: function (val) { return Math.round(val); }
                        }
                    },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
                    legend: { show: true, position: 'top', horizontalAlign: 'right' },
                    tooltip: {
                        custom: function({series, seriesIndex, dataPointIndex, w}) {
                            var formatHours = function(mins) {
                                if (!mins || mins <= 0) return '0 jam';
                                var h = mins / 60;
                                var rounded = Math.round(h * 10) / 10;
                                return (rounded % 1 === 0 ? rounded.toFixed(0) : rounded.toFixed(1)) + ' jam';
                            };
                            var rows = w.config.series.map(function(status, index) {
                                var value = (series[index] || [])[dataPointIndex] || 0;
                                var mins = Math.round(value);
                                var hoursStr = formatHours(value);
                                return '<div style="display:flex;justify-content:space-between;gap:16px;margin-top:6px;align-items:center">' +
                                    '<span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;background:' + w.config.colors[index] + '"></span>' + status.name + '</span>' +
                                    '<span><strong>' + mins + ' mnt</strong> <span style="color:#94a3b8;font-weight:normal;font-size:11px;margin-left:4px">(' + hoursStr + ')</span></span></div>';
                            }).join('');
                            var totalVal = window.currentWorkedSeries[dataPointIndex] || 0;
                            var totalMins = Math.round(totalVal);
                            var totalHoursStr = formatHours(totalVal);
                            return '<div style="padding:12px;background:white;border:1px solid #e2e8f0;border-radius:12px;color:#0f172a">' +
                                '<strong>' + w.globals.labels[dataPointIndex] + '</strong>' + rows +
                                '<div style="margin-top:8px;border-top:1px solid #e2e8f0;padding-top:8px;display:flex;justify-content:space-between;gap:16px;align-items:center">' +
                                '<span>Total Durasi:</span>' +
                                '<span><strong>' + totalMins + ' mnt</strong> <span style="color:#94a3b8;font-weight:normal;font-size:11px;margin-left:4px">(' + totalHoursStr + ')</span></span></div></div>';
                        }
                    }
                };

                var minW = Math.max(800, categories.length * 60);
                document.getElementById('admin-trend-chart').style.minWidth = minW + 'px';

                try {
                    window.adminTrendChart = new ApexCharts(document.querySelector("#admin-trend-chart"), options);
                    window.adminTrendChart.render().then(() => {
                        var scrollContainer = document.getElementById('chart-scroll-container');
                        if (scrollContainer) {
                            scrollContainer.scrollLeft = scrollContainer.scrollWidth;
                        }
                    });
                } catch (e) {
                    console.error("ApexCharts Render Error:", e);
                    document.querySelector("#admin-trend-chart").innerHTML = "<p class='text-red-500 text-sm'>Failed to load chart: " + e.message + "</p>";
                }
            }

            // Convert UTC times to local browser time (WIB/WITA/WIT dynamically)
            function getLocalTZ(date) {
                var offset = -date.getTimezoneOffset() / 60;
                return offset === 7 ? 'WIB' : (offset === 8 ? 'WITA' : (offset === 9 ? 'WIT' : 'Lokal'));
            }

            document.querySelectorAll('.local-date').forEach(function(el) {
                var utcStr = el.getAttribute('data-utc');
                if(utcStr) {
                    var d = new Date(utcStr);
                    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    el.innerText = d.getDate().toString().padStart(2, '0') + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
                }
            });

            document.querySelectorAll('.local-time').forEach(function(el) {
                var utcStr = el.getAttribute('data-utc');
                if(utcStr) {
                    var d = new Date(utcStr);
                    var timeStr = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
                    el.innerText = timeStr + ' ' + getLocalTZ(d);
                }
            });

            var headerSync = document.getElementById('last-sync-time');
            if(headerSync && headerSync.dataset.utc) {
                var d2 = new Date(headerSync.dataset.utc);
                var timeStr2 = d2.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
                var dateStr2 = d2.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                headerSync.innerText = dateStr2 + ', ' + timeStr2 + ' ' + getLocalTZ(d2);
            }
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</x-app-layout>
