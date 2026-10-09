<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">Master Tasks</h2>
        </div>
    </x-slot>

    <div class="py-2 sm:py-6">
        <div class="space-y-4 sm:space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Top 5 Approval Rate -->
                <div class="bg-white rounded-[24px] p-6 border border-gray-150 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Top 5 Task (Approval Rate Tertinggi)
                    </h3>
                    <div class="space-y-3">
                        @foreach($topApproved as $idx => $task)
                            <div class="flex justify-between items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">{{ $idx + 1 }}</div>
                                    <div class="text-sm font-semibold text-gray-800 truncate max-w-[200px] sm:max-w-xs" title="{{ $task->task_name }}">{{ $task->task_name }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-emerald-600">{{ round($task->approval_rate, 1) }}%</div>
                                    <div class="text-[10px] text-gray-400 font-medium">{{ round($task->total_worked / 60, 1) }} jam dikerjakan</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top 5 Reject Rate -->
                <div class="bg-white rounded-[24px] p-6 border border-gray-150 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Top 5 Task (Reject Rate Tertinggi)
                    </h3>
                    <div class="space-y-3">
                        @foreach($topRejected as $idx => $task)
                            <div class="flex justify-between items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">{{ $idx + 1 }}</div>
                                    <div class="text-sm font-semibold text-gray-800 truncate max-w-[200px] sm:max-w-xs" title="{{ $task->task_name }}">{{ $task->task_name }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-rose-600">{{ round($task->reject_rate, 1) }}%</div>
                                    <div class="text-[10px] text-gray-400 font-medium">{{ round($task->total_worked / 60, 1) }} jam dikerjakan</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- List of All Tasks -->
            <div class="bg-white rounded-[24px] border border-gray-150 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Semua Master Task</h3>
                        <p class="text-xs text-gray-500 mt-1">Daftar task yang terdeteksi dari sistem Atlas beserta status kerjanya.</p>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @forelse($masterTasks as $master)
                        @php
                            $stats = $master->stats;
                            $worked = $stats ? $stats->total_worked : 0;
                            $appRate = $stats ? round($stats->approval_rate, 1) : 0;
                            $rejRate = $stats ? round($stats->reject_rate, 1) : 0;
                            $totalOccur = $stats ? $stats->total_occurrences : 0;
                        @endphp
                        <div class="p-5 sm:p-6 hover:bg-gray-50/50 transition-colors flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                            <!-- Task Name & Info -->
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-gray-900">{{ $master->name }}</h4>
                                <div class="flex flex-wrap items-center gap-3 sm:gap-4 mt-2 text-xs text-gray-500">
                                    <span class="flex items-center gap-1.5 bg-gray-100/50 px-2.5 py-1 rounded-md border border-gray-100">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ floor($worked / 60) }} jam {{ round($worked % 60) }} mnt
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-gray-100/50 px-2.5 py-1 rounded-md border border-gray-100">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        {{ $totalOccur }}x dikerjakan
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Stats Badges -->
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-4 sm:gap-6">
                                <!-- Approval Rate -->
                                <div class="flex flex-col items-start sm:items-end">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Approval</span>
                                    <span class="inline-flex items-center justify-center min-w-[3.5rem] px-2.5 py-1.5 rounded-lg text-xs font-bold border {{ $appRate >= 80 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($appRate >= 50 ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-gray-50 text-gray-600 border-gray-200') }}">
                                        {{ $appRate }}%
                                    </span>
                                </div>

                                <!-- Reject Rate -->
                                <div class="flex flex-col items-start sm:items-end">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Reject</span>
                                    <span class="inline-flex items-center justify-center min-w-[3.5rem] px-2.5 py-1.5 rounded-lg text-xs font-bold border {{ $rejRate >= 20 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                        {{ $rejRate }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-400">
                            <p class="text-sm font-medium">Belum ada task yang terdeteksi.</p>
                        </div>
                    @endforelse
                </div>
                
                @if($masterTasks->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    {{ $masterTasks->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
