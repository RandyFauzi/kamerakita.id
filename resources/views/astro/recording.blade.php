@php
    $l = app()->getLocale() == 'id' ? 'id' : 'en';
    $t = [
        'data_updated' => $l == 'id' ? 'Data Atlas diperbarui' : 'Atlas data updated',
        'delayed' => $l == 'id' ? 'Tertunda' : 'Delayed',
        'synced' => $l == 'id' ? 'Sinkron' : 'Synced',
        'no_sync' => $l == 'id' ? 'Belum ada data sinkronisasi' : 'No sync data yet',
        'overview' => $l == 'id' ? 'Aktivitas Terakhir' : 'Latest Activity',
        'total_tasks' => $l == 'id' ? 'Total Tugas' : 'Total Tasks',
        'worked' => $l == 'id' ? 'Dikerjakan' : 'Worked',
        'approved' => $l == 'id' ? 'Disetujui' : 'Approved',
        'rejected' => $l == 'id' ? 'Ditolak' : 'Rejected',
        'under_review' => $l == 'id' ? 'Dalam Ulasan' : 'Under Review',
        'overall' => $l == 'id' ? 'Performa Keseluruhan' : 'Overall Performance',
        'total_worked' => $l == 'id' ? 'Total Dikerjakan' : 'Total Worked',
        'history' => $l == 'id' ? 'Riwayat Tugas' : 'Task History',
        'task' => $l == 'id' ? 'Tugas' : 'Task',
        'min' => $l == 'id' ? 'mnt' : 'min',
        'task_list' => $l == 'id' ? 'Daftar Tugas' : 'Task List',
        'duration' => $l == 'id' ? 'Durasi' : 'Duration',
        'review' => $l == 'id' ? 'Ulasan' : 'Review',
        'all_time_perf' => $l == 'id' ? 'Performa Kerja (Total Keseluruhan)' : 'Work Performance (All Time)',
        'total_accumulated' => $l == 'id' ? 'TOTAL PEKERJAAN TERAKUMULASI' : 'TOTAL ACCUMULATED WORK',
        'no_history' => $l == 'id' ? 'Belum Ada Riwayat Tugas' : 'No Task History Yet',
        'no_history_desc' => $l == 'id' ? 'Tugas yang Anda kerjakan di Atlas akan otomatis muncul di sini setelah sinkronisasi.' : 'Tasks you work on in Atlas will automatically appear here after synchronization.'
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Recording
            </h2>
            @if($worker && $worker->last_synced_at)
                <div class="text-right">
                    <div class="text-xs text-gray-500 font-medium">{{ $t['data_updated'] }}</div>
                    <div class="text-sm font-bold {{ $worker->last_synced_at->diffInHours(now()) > 3 ? 'text-amber-600' : 'text-emerald-600' }} flex items-center gap-1.5 justify-end">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $worker->last_synced_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA
                        @if($worker->last_synced_at->diffInHours(now()) > 3)
                            <span class="text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded ml-1">{{ $t['delayed'] }}</span>
                        @else
                            <span class="text-xs bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded ml-1">{{ $t['synced'] }}</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-right text-xs text-gray-500">{{ $t['no_sync'] }}</div>
            @endif
        </div>
    </x-slot>

    <div class="py-2">
        <div class="space-y-6">

            <!-- LEVEL 1: OVERVIEW (TODAY) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-extrabold text-gray-900">{{ $t['overview'] }}</h3>
                    @if(isset($todayStats['date']))
                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ \Carbon\Carbon::parse($todayStats['date'])->format('d M Y') }}</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3 sm:gap-4">
                    <div class="flex-1 min-w-[130px] bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1">{{ $t['total_tasks'] }}</div>
                        <div class="text-2xl font-black text-gray-900">{{ $todayStats['total_tasks'] }}</div>
                    </div>
                    <div class="flex-1 min-w-[130px] bg-indigo-50/50 rounded-xl p-4 border border-indigo-100">
                        <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wide mb-1">{{ $t['worked'] }}</div>
                        <div class="text-2xl font-black text-indigo-700">{{ number_format($todayStats['total_worked'], 1) }} <span class="text-xs font-semibold">{{ $t['min'] }}</span></div>
                    </div>
                    <div class="flex-1 min-w-[130px] bg-emerald-50/50 rounded-xl p-4 border border-emerald-100">
                        <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wide mb-1">{{ $t['approved'] }}</div>
                        <div class="text-2xl font-black text-emerald-700">{{ number_format($todayStats['approved'], 1) }} <span class="text-xs font-semibold">{{ $t['min'] }}</span></div>
                    </div>
                    <div class="flex-1 min-w-[130px] bg-rose-50/50 rounded-xl p-4 border border-rose-100">
                        <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wide mb-1">{{ $t['rejected'] }}</div>
                        <div class="text-2xl font-black text-rose-700">{{ number_format($todayStats['rejected'], 1) }} <span class="text-xs font-semibold">{{ $t['min'] }}</span></div>
                    </div>
                    <div class="flex-1 min-w-[130px] bg-amber-50/50 rounded-xl p-4 border border-amber-100/60">
                        <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wide mb-1">{{ $t['under_review'] }}</div>
                        <div class="text-2xl font-black text-amber-800">{{ number_format($todayStats['review'], 1) }} <span class="text-xs font-semibold">{{ $t['min'] }}</span></div>
                    </div>
                </div>
            </div>

            <!-- LEVEL 2: PERFORMANCE (ALL TIME) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-5 sm:p-6">
                <h3 class="text-base font-extrabold text-gray-900 mb-5">{{ $t['all_time_perf'] }}</h3>
                
                @if($allTimeStats['total_worked'] > 0)
                    @php
                        $total = $allTimeStats['total_worked'];
                        $pctApproved = round(($allTimeStats['approved'] / $total) * 100, 1);
                        $pctRejected = round(($allTimeStats['rejected'] / $total) * 100, 1);
                        $pctReview = round(($allTimeStats['review'] / $total) * 100, 1);
                        
                        $statusBadge = ['text' => 'Perlu Ditingkatkan', 'class' => 'text-rose-600 bg-rose-50 border-rose-200'];
                        if ($pctApproved >= 90) $statusBadge = ['text' => 'Sangat Baik (Excellent)', 'class' => 'text-emerald-600 bg-emerald-50 border-emerald-200'];
                        elseif ($pctApproved >= 70) $statusBadge = ['text' => 'Baik (Good)', 'class' => 'text-indigo-600 bg-indigo-50 border-indigo-200'];
                    @endphp
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
                        <!-- Graphic Chart (ApexCharts) -->
                        <div class="flex justify-center lg:col-span-1 w-full py-4">
                            <div id="performance-chart" class="w-full max-w-[280px] drop-shadow-md transition-transform hover:scale-105 duration-500"></div>
                        </div>

                        <!-- Details & Bars -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Overall Time -->
                            <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-gray-100 pb-4 gap-4">
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $t['total_accumulated'] }}</div>
                                    <div class="text-4xl sm:text-5xl font-black text-gray-900 tracking-tight">
                                        {{ floor($allTimeStats['total_worked'] / 60) }}h {{ round($allTimeStats['total_worked'] % 60) }}m
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Rating Kinerja</div>
                                    <div class="inline-block text-xs font-bold px-3 py-1.5 rounded-lg border {{ $statusBadge['class'] }}">{{ $statusBadge['text'] }}</div>
                                </div>
                            </div>

                            <!-- Unified Progress Bar & Stats List -->
                            <div class="mt-2 bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Detail Status Waktu</h4>
                                </div>
                                
                                <!-- Unified Bar -->
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden flex mb-5 shadow-inner">
                                    <div class="h-full transition-all duration-1000" style="width: {{ $pctApproved }}%; background: linear-gradient(to right, #34d399, #10b981);"></div>
                                    <div class="h-full transition-all duration-1000" style="width: {{ $pctReview }}%; background: linear-gradient(to right, #fcd34d, #fbbf24);"></div>
                                    <div class="h-full transition-all duration-1000" style="width: {{ $pctRejected }}%; background: linear-gradient(to right, #fb7185, #f43f5e);"></div>
                                </div>

                                <!-- Modern List -->
                                <ul class="space-y-3">
                                    <li class="flex justify-between items-center text-sm font-medium">
                                        <span class="flex items-center gap-3 text-gray-600 text-xs">
                                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm"></span> {{ $t['approved'] }}
                                        </span>
                                        <div class="text-right flex items-center gap-2">
                                            <span class="text-gray-900 font-bold text-xs">{{ floor($allTimeStats['approved'] / 60) }}h {{ round($allTimeStats['approved'] % 60) }}m</span>
                                            <span class="text-emerald-600 text-[10px] font-bold bg-emerald-50 px-1.5 py-0.5 rounded w-10 text-center">{{ $pctApproved }}%</span>
                                        </div>
                                    </li>
                                    <li class="flex justify-between items-center text-sm font-medium border-t border-gray-50 pt-3">
                                        <span class="flex items-center gap-3 text-gray-600 text-xs">
                                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm"></span> {{ $t['under_review'] }}
                                        </span>
                                        <div class="text-right flex items-center gap-2">
                                            <span class="text-gray-900 font-bold text-xs">{{ floor($allTimeStats['review'] / 60) }}h {{ round($allTimeStats['review'] % 60) }}m</span>
                                            <span class="text-amber-600 text-[10px] font-bold bg-amber-50 px-1.5 py-0.5 rounded w-10 text-center">{{ $pctReview }}%</span>
                                        </div>
                                    </li>
                                    <li class="flex justify-between items-center text-sm font-medium border-t border-gray-50 pt-3">
                                        <span class="flex items-center gap-3 text-gray-600 text-xs">
                                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm"></span> {{ $t['rejected'] }}
                                        </span>
                                        <div class="text-right flex items-center gap-2">
                                            <span class="text-gray-900 font-bold text-xs">{{ floor($allTimeStats['rejected'] / 60) }}h {{ round($allTimeStats['rejected'] % 60) }}m</span>
                                            <span class="text-rose-600 text-[10px] font-bold bg-rose-50 px-1.5 py-0.5 rounded w-10 text-center">{{ $pctRejected }}%</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- EMPTY STATE -->
                    <div class="flex flex-col items-center justify-center py-12 px-4 bg-gray-50/50 rounded-xl border border-gray-100 border-dashed">
                        <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center mb-4 border border-gray-100">
                            <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h4 class="text-sm font-extrabold text-gray-900 mb-1">Belum Ada Data Kinerja</h4>
                        <p class="text-xs text-gray-500 text-center max-w-sm">Grafik performa akan muncul di sini secara otomatis setelah Anda mulai mencatat waktu di Atlas.</p>
                    </div>
                @endif
            </div>

            <!-- LEVEL 3: TASK HISTORY -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-5 sm:p-6">
                <h3 class="text-base font-extrabold text-gray-900 mb-5">{{ $t['history'] }}</h3>
                
                <div class="space-y-6">
                    @forelse($history as $date => $tasks)
                        <div x-data="{ open: false }" class="bg-white border border-gray-200/90 rounded-2xl shadow-sm overflow-hidden transition-all hover:border-gray-300">
                            <!-- DAY ACCORDION HEADER (Clickable) -->
                            <div class="p-4 bg-gray-50/70 border-b border-gray-100 cursor-pointer hover:bg-gray-100/60 transition-colors select-none"
                                 @click="open = !open">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 font-extrabold text-xs flex items-center justify-center shrink-0">
                                            H{{ $loop->iteration }}
                                        </div>
                                        
                                        <div class="flex items-center gap-1.5 bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                                        </div>

                                        <span class="bg-gray-200/70 text-gray-600 text-[11px] font-extrabold px-2 py-0.5 rounded-full">{{ $tasks->count() }} {{ $t['task'] }}</span>
                                    </div>

                                    <div class="flex items-center gap-3 self-end sm:self-center">
                                        <!-- Day Total Duration -->
                                        <div class="text-right">
                                            <span class="text-xs font-black text-gray-900">{{ number_format($tasks->sum('worked_minutes'), 1) }} {{ $t['min'] }}</span>
                                            <span class="text-[11px] text-gray-400 font-medium">({{ floor($tasks->sum('worked_minutes') / 60) }}h {{ round($tasks->sum('worked_minutes') % 60) }}m)</span>
                                        </div>

                                        <!-- Chevron Icon -->
                                        <div class="w-6 h-6 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 transition-transform duration-200"
                                             :class="open ? 'rotate-180' : ''">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DAY COLLAPSIBLE CONTENT -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-4"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-4"
                                 class="p-4 sm:p-5 bg-white space-y-4" style="display: none;">
                                <div class="flex justify-between items-center text-xs font-bold text-gray-500 uppercase tracking-wider pb-2 border-b border-gray-100">
                                    <span>{{ $t['task_list'] }} ({{ $tasks->count() }})</span>
                                </div>
                                <!-- VERTICAL TIMELINE -->
                                <div class="relative border-l-2 border-gray-100 ml-4 space-y-6 mt-4 pb-2">
                                    @foreach($tasks as $task)
                                        @php
                                            $total = $task->worked_minutes > 0 ? $task->worked_minutes : 1;
                                            
                                            // Determine timeline node color
                                            $nodeColor = 'bg-indigo-400';
                                            $nodeRing = 'ring-indigo-50';
                                            
                                            if ($task->rejected_minutes > 0) {
                                                $nodeColor = 'bg-rose-500';
                                                $nodeRing = 'ring-rose-50';
                                            } elseif ($task->review_minutes > 0 && $task->approved_minutes == 0) {
                                                $nodeColor = 'bg-amber-400';
                                                $nodeRing = 'ring-amber-50';
                                            } elseif ($task->approved_minutes > 0 && $task->rejected_minutes == 0) {
                                                $nodeColor = 'bg-emerald-500';
                                                $nodeRing = 'ring-emerald-50';
                                            }
                                        @endphp
                                        
                                        <div class="relative pl-6">
                                            <!-- Timeline Dot -->
                                            <div class="absolute -left-[9px] top-2.5 w-4 h-4 rounded-full border-[3px] border-white shadow-sm ring-4 {{ $nodeColor }} {{ $nodeRing }}"></div>
                                            
                                            <!-- Task Content Card -->
                                            <div class="bg-gray-50/50 hover:bg-gray-50 border border-gray-100 rounded-xl p-4 transition-colors">
                                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-2">
                                                    <h5 class="font-bold text-sm text-gray-900 leading-tight" title="{{ $task->task_name }}">{{ $task->task_name }}</h5>
                                                    <div class="flex items-center gap-2 mt-1 sm:mt-0">
                                                        @if($task->recorded_at)
                                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-white border border-gray-100 px-2 py-0.5 rounded-md">{{ \Carbon\Carbon::parse($task->recorded_at)->format('H:i') }} UTC</span>
                                                        @endif
                                                        <span class="text-xs font-black text-gray-800 bg-white border border-gray-200 px-2.5 py-1 rounded-md shadow-sm">{{ number_format($task->worked_minutes, 1) }} Min</span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Breakdown -->
                                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] font-semibold mt-3">
                                                    @if($task->approved_minutes > 0)
                                                        <div class="text-emerald-700 flex items-center gap-1.5">
                                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                                            {{ number_format($task->approved_minutes, 1) }} {{ $t['min'] }} App
                                                        </div>
                                                    @endif
                                                    @if($task->review_minutes > 0)
                                                        <div class="text-amber-700 flex items-center gap-1.5">
                                                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                                            {{ number_format($task->review_minutes, 1) }} {{ $t['min'] }} Rev
                                                        </div>
                                                    @endif
                                                    @if($task->rejected_minutes > 0)
                                                        <div class="text-rose-700 flex items-center gap-1.5">
                                                            <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div>
                                                            {{ number_format($task->rejected_minutes, 1) }} {{ $t['min'] }} Rej
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Notes -->
                                                @if($task->notes)
                                                    @php
                                                        $noteStyle = 'bg-indigo-50 border-indigo-100 text-indigo-800';
                                                        $iconStyle = 'text-indigo-500';
                                                        $noteTitle = 'Catatan';
                                                        if ($task->rejected_minutes > 0) {
                                                            $noteStyle = 'bg-rose-50 border-rose-100 text-rose-800';
                                                            $iconStyle = 'text-rose-500';
                                                            $noteTitle = 'Alasan Penolakan';
                                                        } elseif ($task->review_minutes > 0 && $task->approved_minutes == 0) {
                                                            $noteStyle = 'bg-amber-50 border-amber-100 text-amber-800';
                                                            $iconStyle = 'text-amber-500';
                                                            $noteTitle = 'Status Review';
                                                        }
                                                    @endphp
                                                    <div class="mt-3 text-xs p-3 rounded-xl border flex items-start gap-2.5 {{ $noteStyle }}">
                                                        <svg class="w-4 h-4 shrink-0 mt-0.5 {{ $iconStyle }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        <div>
                                                            <span class="font-bold uppercase tracking-wider text-[9px] opacity-80 block mb-0.5">{{ $noteTitle }}</span>
                                                            <span class="font-medium leading-relaxed">{{ $task->notes }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t['no_history'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $t['no_history_desc'] }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>
    </div>

    <!-- ApexCharts Library & Init -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (document.querySelector("#performance-chart")) {
                var options = {
                    series: [{{ isset($pctApproved) ? round($pctApproved, 1) : 0 }}],
                    chart: {
                        type: 'radialBar',
                        height: 320,
                        offsetY: -10,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: { enabled: true, delay: 150 },
                            dynamicAnimation: { enabled: true, speed: 350 }
                        },
                        sparkline: { enabled: true }
                    },
                    colors: ['#10b981'],
                    plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            track: {
                                background: "#f3f4f6",
                                strokeWidth: '100%',
                                margin: 0,
                                dropShadow: {
                                    enabled: true,
                                    top: 0,
                                    left: 0,
                                    color: '#999',
                                    opacity: 0.1,
                                    blur: 3
                                }
                            },
                            dataLabels: {
                                name: {
                                    show: true,
                                    fontSize: '11px',
                                    fontFamily: 'inherit',
                                    fontWeight: 800,
                                    color: '#9ca3af',
                                    offsetY: 25
                                },
                                value: {
                                    show: true,
                                    fontSize: '36px',
                                    fontFamily: 'inherit',
                                    fontWeight: 900,
                                    color: '#111827',
                                    offsetY: -5,
                                    formatter: function (val) { return val + "%" }
                                }
                            }
                        }
                    },
                    labels: ['APPROVAL RATE'],
                    stroke: { lineCap: 'round' },
                    tooltip: { enabled: false }
                };

                var chart = new ApexCharts(document.querySelector("#performance-chart"), options);
                chart.render();
            }
        });
    </script>
</x-app-layout>
