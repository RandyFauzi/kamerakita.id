<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                My Atlas Performance
            </h2>
            @if($worker && $worker->last_synced_at)
                <div class="text-right">
                    <div class="text-xs text-gray-500 font-medium">Atlas data updated</div>
                    <div class="text-sm font-bold {{ $worker->last_synced_at->diffInHours(now()) > 3 ? 'text-amber-600' : 'text-emerald-600' }} flex items-center gap-1.5 justify-end">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $worker->last_synced_at->format('d M Y, H:i') }}
                        @if($worker->last_synced_at->diffInHours(now()) > 3)
                            <span class="text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded ml-1">Delayed</span>
                        @else
                            <span class="text-xs bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded ml-1">Synced</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-right text-xs text-gray-500">Belum ada data sinkronisasi</div>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- LEVEL 1: OVERVIEW (TODAY) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Today's Overview</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="text-xs font-bold text-gray-500 uppercase mb-1">Total Tasks</div>
                        <div class="text-2xl font-black text-gray-900">{{ $todayStats['total_tasks'] }}</div>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
                        <div class="text-xs font-bold text-indigo-500 uppercase mb-1">Worked</div>
                        <div class="text-2xl font-black text-indigo-700">{{ number_format($todayStats['total_worked'], 1) }} <span class="text-sm">min</span></div>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                        <div class="text-xs font-bold text-emerald-600 uppercase mb-1">Approved</div>
                        <div class="text-2xl font-black text-emerald-700">{{ number_format($todayStats['approved'], 1) }} <span class="text-sm">min</span></div>
                    </div>
                    <div class="bg-rose-50 rounded-xl p-4 border border-rose-100">
                        <div class="text-xs font-bold text-rose-600 uppercase mb-1">Rejected</div>
                        <div class="text-2xl font-black text-rose-700">{{ number_format($todayStats['rejected'], 1) }} <span class="text-sm">min</span></div>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
                        <div class="text-xs font-bold text-yellow-600 uppercase mb-1">Under Review</div>
                        <div class="text-2xl font-black text-yellow-700">{{ number_format($todayStats['review'], 1) }} <span class="text-sm">min</span></div>
                    </div>
                </div>
            </div>

            <!-- LEVEL 2: PERFORMANCE (ALL TIME) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Work Performance (All Time)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col justify-center">
                        <div class="text-sm font-bold text-gray-500 uppercase mb-2">Total Accumulated Work</div>
                        <div class="text-5xl font-black text-gray-900">
                            {{ floor($allTimeStats['total_worked'] / 60) }}h {{ round($allTimeStats['total_worked'] % 60) }}m
                        </div>
                    </div>

                    <div class="space-y-4">
                        @php
                            $total = $allTimeStats['total_worked'] > 0 ? $allTimeStats['total_worked'] : 1;
                            $pctApproved = round(($allTimeStats['approved'] / $total) * 100, 1);
                            $pctRejected = round(($allTimeStats['rejected'] / $total) * 100, 1);
                            $pctReview = round(($allTimeStats['review'] / $total) * 100, 1);
                        @endphp
                        
                        <!-- Approved Bar -->
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-bold text-emerald-700">Approved</span>
                                <span class="font-bold text-gray-700">{{ floor($allTimeStats['approved'] / 60) }}h {{ round($allTimeStats['approved'] % 60) }}m ({{ $pctApproved }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $pctApproved }}%"></div>
                            </div>
                        </div>

                        <!-- Rejected Bar -->
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-bold text-rose-700">Rejected</span>
                                <span class="font-bold text-gray-700">{{ floor($allTimeStats['rejected'] / 60) }}h {{ round($allTimeStats['rejected'] % 60) }}m ({{ $pctRejected }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-rose-500 h-2.5 rounded-full" style="width: {{ $pctRejected }}%"></div>
                            </div>
                        </div>

                        <!-- Under Review Bar -->
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-bold text-yellow-700">Under Review</span>
                                <span class="font-bold text-gray-700">{{ floor($allTimeStats['review'] / 60) }}h {{ round($allTimeStats['review'] % 60) }}m ({{ $pctReview }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: {{ $pctReview }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LEVEL 3: TASK HISTORY -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 border-b pb-2">Task History</h3>
                
                <div class="space-y-8">
                    @forelse($history as $date => $tasks)
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <h4 class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</h4>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full font-bold">{{ $tasks->count() }} Tasks</span>
                                <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full font-bold">{{ number_format($tasks->sum('worked_minutes'), 1) }} min total</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($tasks as $task)
                                    <div class="border rounded-xl p-4 hover:shadow-md transition-shadow bg-white">
                                        <div class="flex justify-between items-start mb-2">
                                            <h5 class="font-bold text-sm text-gray-900 line-clamp-2" title="{{ $task->task_name }}">{{ $task->task_name }}</h5>
                                            @if($task->status === 'APPROVED')
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">APPROVED</span>
                                            @elseif($task->status === 'REJECTED')
                                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20">REJECTED</span>
                                            @elseif($task->status === 'PARTIALLY_APPROVED')
                                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">PARTIAL</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-[10px] font-bold text-yellow-800 ring-1 ring-inset ring-yellow-600/20">REVIEW</span>
                                            @endif
                                        </div>
                                        
                                        <div class="text-xs text-gray-500 font-medium mb-3">
                                            Duration: {{ number_format($task->worked_minutes, 1) }} min
                                        </div>
                                        
                                        <div class="space-y-1">
                                            @if($task->approved_minutes > 0)
                                                <div class="flex items-center text-xs">
                                                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    <span class="text-gray-600">Approved <span class="font-bold text-gray-900">{{ number_format($task->approved_minutes, 1) }}</span></span>
                                                </div>
                                            @endif
                                            
                                            @if($task->rejected_minutes > 0)
                                                <div class="flex items-center text-xs">
                                                    <svg class="w-3.5 h-3.5 mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    <span class="text-gray-600">Rejected <span class="font-bold text-gray-900">{{ number_format($task->rejected_minutes, 1) }}</span></span>
                                                </div>
                                            @endif
                                            
                                            @if($task->review_minutes > 0)
                                                <div class="flex items-center text-xs">
                                                    <svg class="w-3.5 h-3.5 mr-1.5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <span class="text-gray-600">Review <span class="font-bold text-gray-900">{{ number_format($task->review_minutes, 1) }}</span></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Riwayat Task</h3>
                            <p class="mt-1 text-sm text-gray-500">Tugas yang Anda kerjakan di Atlas akan otomatis muncul di sini setelah sinkronisasi.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
