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
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-6 h-6 shrink-0 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">{{ $idx + 1 }}</div>
                                    <div class="text-sm font-semibold text-gray-800 truncate" title="{{ $task->task_name }}">{{ $task->task_name }}</div>
                                </div>
                                <div class="text-right shrink-0 ml-3">
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
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-6 h-6 shrink-0 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">{{ $idx + 1 }}</div>
                                    <div class="text-sm font-semibold text-gray-800 truncate" title="{{ $task->task_name }}">{{ $task->task_name }}</div>
                                </div>
                                <div class="text-right shrink-0 ml-3">
                                    <div class="text-sm font-bold text-rose-600">{{ round($task->reject_rate, 1) }}%</div>
                                    <div class="text-[10px] text-gray-400 font-medium">{{ round($task->total_worked / 60, 1) }} jam dikerjakan</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

                <div class="p-6 border-b border-gray-100 bg-white">
                    <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-col gap-5">
                        <!-- Header & Search -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Semua Master Task</h3>
                                <p class="text-xs text-gray-500 mt-1">Daftar task yang terdeteksi dari sistem Atlas beserta status kerjanya.</p>
                            </div>
                            <div class="relative w-full sm:w-72">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari task..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full transition-all">
                            </div>
                        </div>

                        <!-- Filter Pills -->
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
                            <input type="hidden" name="sort" id="sort-input" value="{{ request('sort', 'most_frequent') }}">
                            
                            <button type="button" onclick="document.getElementById('sort-input').value='new_task'; this.form.submit();" class="whitespace-nowrap px-4 py-2 flex items-center rounded-xl text-xs font-bold transition-all {{ request('sort') === 'new_task' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                Task Baru
                            </button>
                            <button type="button" onclick="document.getElementById('sort-input').value='most_frequent'; this.form.submit();" class="whitespace-nowrap px-4 py-2 flex items-center rounded-xl text-xs font-bold transition-all {{ request('sort', 'most_frequent') === 'most_frequent' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
                                Paling Sering
                            </button>
                            <button type="button" onclick="document.getElementById('sort-input').value='least_frequent'; this.form.submit();" class="whitespace-nowrap px-4 py-2 flex items-center rounded-xl text-xs font-bold transition-all {{ request('sort') === 'least_frequent' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                Paling Jarang
                            </button>
                            <button type="button" onclick="document.getElementById('sort-input').value='highest_approval'; this.form.submit();" class="whitespace-nowrap px-4 py-2 flex items-center rounded-xl text-xs font-bold transition-all {{ request('sort') === 'highest_approval' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-200 border border-emerald-600' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                Termudah (Approval)
                            </button>
                            <button type="button" onclick="document.getElementById('sort-input').value='highest_reject'; this.form.submit();" class="whitespace-nowrap px-4 py-2 flex items-center rounded-xl text-xs font-bold transition-all {{ request('sort') === 'highest_reject' ? 'bg-rose-600 text-white shadow-md shadow-rose-200 border border-rose-600' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Tersulit (Reject)
                            </button>
                            <button type="button" onclick="document.getElementById('sort-input').value='most_hours'; this.form.submit();" class="whitespace-nowrap px-4 py-2 flex items-center rounded-xl text-xs font-bold transition-all {{ request('sort') === 'most_hours' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Durasi Terlama
                            </button>
                            <button type="button" onclick="document.getElementById('sort-input').value='name_asc'; this.form.submit();" class="whitespace-nowrap px-4 py-2 flex items-center rounded-xl text-xs font-bold transition-all {{ request('sort') === 'name_asc' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                                Abjad (A-Z)
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="p-5 sm:p-6 bg-gray-50/50">
                    <div class="grid grid-cols-1 gap-4">
                        @forelse($masterTasks as $master)
                            @php
                                $worked = $master->total_worked ?? 0;
                                $appRate = round($master->approval_rate ?? 0, 1);
                                $rejRate = round($master->reject_rate ?? 0, 1);
                                $totalOccur = $master->total_occurrences ?? 0;
                            @endphp
                            <div class="group bg-white rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-100/50 transition-all duration-300 overflow-hidden">
                                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                                    
                                    <!-- Left: Task Name & Badges -->
                                    <div class="flex items-start gap-4 flex-1 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 text-indigo-500 border border-indigo-100 flex items-center justify-center shrink-0 group-hover:from-indigo-500 group-hover:to-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        </div>
                                        <div class="min-w-0 flex-1 mt-0.5">
                                            <h4 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors truncate" title="{{ $master->name }}">{{ $master->name }}</h4>
                                            
                                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-100 text-[11px] font-semibold text-slate-600">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    {{ floor($worked / 60) }}j {{ round($worked % 60) }}m
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-100 text-[11px] font-semibold text-slate-600">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                    {{ $totalOccur }}x submit
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Right: Rate Metrics -->
                                    <div class="flex items-center gap-3 w-full sm:w-auto border-t sm:border-t-0 border-gray-100 pt-4 sm:pt-0">
                                        <div class="flex-1 sm:flex-none">
                                            <div class="flex items-center justify-between sm:justify-start gap-3 px-3 py-2 rounded-xl {{ $appRate >= 80 ? 'bg-emerald-50/80 border-emerald-100' : ($appRate >= 50 ? 'bg-yellow-50/80 border-yellow-100' : 'bg-gray-50 border-gray-200') }} border">
                                                <div class="flex items-center gap-1.5">
                                                    <div class="w-1.5 h-1.5 rounded-full {{ $appRate >= 80 ? 'bg-emerald-500' : ($appRate >= 50 ? 'bg-yellow-500' : 'bg-gray-400') }}"></div>
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Approval</span>
                                                </div>
                                                <span class="text-sm font-black {{ $appRate >= 80 ? 'text-emerald-700' : ($appRate >= 50 ? 'text-yellow-700' : 'text-gray-700') }}">{{ $appRate }}%</span>
                                            </div>
                                        </div>
                                        <div class="flex-1 sm:flex-none">
                                            <div class="flex items-center justify-between sm:justify-start gap-3 px-3 py-2 rounded-xl {{ $rejRate >= 20 ? 'bg-rose-50/80 border-rose-100' : 'bg-gray-50 border-gray-200' }} border">
                                                <div class="flex items-center gap-1.5">
                                                    <div class="w-1.5 h-1.5 rounded-full {{ $rejRate >= 20 ? 'bg-rose-500' : 'bg-gray-400' }}"></div>
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Reject</span>
                                                </div>
                                                <span class="text-sm font-black {{ $rejRate >= 20 ? 'text-rose-700' : 'text-gray-700' }}">{{ $rejRate }}%</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center text-gray-400 bg-white rounded-2xl border border-dashed border-gray-200">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-sm font-medium">Belum ada task yang terdeteksi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                @if($masterTasks->hasPages())
                <div class="p-5 border-t border-gray-100 bg-white">
                    {{ $masterTasks->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
