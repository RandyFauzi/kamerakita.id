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
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Semua Master Task</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Daftar task yang terdeteksi dari sistem Atlas beserta status kerjanya.</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-white">
                            <tr class="text-gray-500">
                                <th class="py-4 pl-6 pr-4 text-left font-semibold text-[11px] uppercase tracking-wider">Nama Task</th>
                                <th class="py-4 px-4 text-left font-semibold text-[11px] uppercase tracking-wider">Total Dikerjakan</th>
                                <th class="py-4 px-4 text-left font-semibold text-[11px] uppercase tracking-wider">Approval Rate</th>
                                <th class="py-4 px-4 text-left font-semibold text-[11px] uppercase tracking-wider">Reject Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($masterTasks as $master)
                                @php
                                    $stats = $master->stats;
                                    $worked = $stats ? $stats->total_worked : 0;
                                    $appRate = $stats ? round($stats->approval_rate, 1) : 0;
                                    $rejRate = $stats ? round($stats->reject_rate, 1) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 pl-6 pr-4">
                                        <div class="font-bold text-gray-800">{{ $master->name }}</div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-gray-900 font-medium">{{ floor($worked / 60) }} jam {{ round($worked % 60) }} menit</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $stats ? $stats->total_occurrences : 0 }}x dikerjakan</div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold {{ $appRate >= 80 ? 'bg-emerald-50 text-emerald-700' : ($appRate >= 50 ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                            {{ $appRate }}%
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold {{ $rejRate >= 20 ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $rejRate }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400">
                                        <p class="text-sm font-medium">Belum ada task yang terdeteksi.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($masterTasks->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $masterTasks->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
