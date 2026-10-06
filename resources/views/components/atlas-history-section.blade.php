@props(['atlasData' => null])

@if($atlasData !== null)
    <div class="mt-8 mb-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-black tracking-tight text-gray-900">Atlas Synchronized Tasks</h3>
            <span class="text-xs font-semibold px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-100">Otomatis dari Atlas</span>
        </div>

        @if($atlasData->isEmpty())
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-6 text-center shadow-sm">
                <svg class="mx-auto h-8 w-8 text-yellow-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm font-semibold text-yellow-800">
                    Data kosong. Pastikan akun yang terdaftar di atlas menggunakan akun yang sekarang dipakai atau hubungi admin 0895366583095
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($atlasData as $date => $tasks)
                    @php
                        $dayApprovedMins = $tasks->sum('approved_mins');
                        $dayRejectedMins = $tasks->sum('rejected_mins');
                        $dayPendingMins = $tasks->sum('pending_mins');
                        $dayTotalMins = $tasks->sum('total_video_mins');
                        if ($dayTotalMins == 0) $dayTotalMins = $dayApprovedMins + $dayRejectedMins + $dayPendingMins;
                        if ($dayTotalMins == 0) $dayTotalMins = 1; // prevent div by zero
                        
                        $taskCount = $tasks->count();
                        
                        $dayStatus = 'Disetujui penuh';
                        $dayStatusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if ($dayApprovedMins > 0 && $dayRejectedMins > 0) {
                            $dayStatus = 'Disetujui sebagian';
                            $dayStatusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                        } else if ($dayApprovedMins == 0 && $dayRejectedMins > 0) {
                            $dayStatus = 'Ditolak';
                            $dayStatusClass = 'bg-rose-50 text-rose-700 border-rose-200';
                        } else if ($dayPendingMins > 0 && $dayApprovedMins == 0 && $dayRejectedMins == 0) {
                            $dayStatus = 'Sedang Ditinjau';
                            $dayStatusClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                        }
                    @endphp
                    
                    <div x-data="{ open: false }" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Header Accordion -->
                        <div @click="open = !open" class="cursor-pointer px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center shrink-0 border border-indigo-100">
                                    <span class="text-xs font-black text-indigo-700">{{ \Carbon\Carbon::parse($date)->format('d') }}</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMM Y') }}</h4>
                                        <span class="text-[10px] font-bold px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md">{{ $taskCount }} Task</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Klik untuk buka/tutup rincian task harian</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border {{ $dayStatusClass }}">{{ $dayStatus }}</span>
                                <div class="text-right">
                                    <div class="font-black text-sm text-gray-900">{{ number_format($dayTotalMins, 1) }} mnt <span class="text-gray-400 font-medium text-xs">({{ number_format($dayTotalMins/60, 2) }} h)</span></div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        
                        <!-- Body Accordion -->
                        <div x-show="open" style="display: none;" class="border-t border-gray-100 bg-gray-50/50 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h5 class="text-xs font-black text-gray-600 uppercase tracking-widest">Rincian Tugas ({{ $taskCount }} Task)</h5>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden sm:block">ATLAS VIDEO LOG TASK BREAKDOWN</span>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($tasks as $index => $t)
                                    @php
                                        $tAppr = $t->approved_mins;
                                        $tRej = $t->rejected_mins;
                                        $tPend = $t->pending_mins;
                                        $tTotal = $t->total_video_mins > 0 ? $t->total_video_mins : ($tAppr + $tRej + $tPend);
                                        if($tTotal == 0) $tTotal = 1;
                                        
                                        $tApprPct = min(100, max(0, ($tAppr / $tTotal) * 100));
                                        $tPendPct = min(100, max(0, ($tPend / $tTotal) * 100));
                                        $tRejPct = min(100, max(0, ($tRej / $tTotal) * 100));
                                    @endphp
                                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm space-y-2.5">
                                        <div class="flex justify-between items-start sm:items-center gap-3 flex-col sm:flex-row">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-5 h-5 rounded-full bg-gray-50 text-gray-500 font-extrabold text-[10px] flex items-center justify-center border border-gray-200 shrink-0">{{ $index + 1 }}</span>
                                                <h6 class="text-xs font-bold text-gray-900">{{ $t->task_name }}</h6>
                                                @if($t->time_str)
                                                    <span class="text-[10px] text-gray-400 font-medium hidden sm:inline-block">• {{ $t->time_str }}</span>
                                                @endif
                                            </div>
                                            <div class="text-right text-[11px] font-bold text-gray-700 shrink-0">
                                                {{ number_format($tTotal, 1) }} mnt <span class="text-gray-400 font-normal">({{ number_format($tTotal/60, 2) }}h)</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Progress Bar -->
                                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden flex">
                                            @if($tAppr > 0)<div class="h-full bg-emerald-500" style="width: {{ $tApprPct }}%"></div>@endif
                                            @if($tPend > 0)<div class="h-full bg-yellow-400" style="width: {{ $tPendPct }}%"></div>@endif
                                            @if($tRej > 0)<div class="h-full bg-rose-500" style="width: {{ $tRejPct }}%"></div>@endif
                                        </div>
                                        
                                        <!-- Details -->
                                        <div class="flex flex-wrap items-center gap-4 text-[10px] font-semibold">
                                            @if($tAppr > 0)
                                                <div class="text-emerald-700 flex items-center gap-1">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                                    {{ number_format($tAppr, 1) }} mnt lolos ({{ number_format($tAppr/60, 2) }}h)
                                                </div>
                                            @endif
                                            @if($tRej > 0)
                                                <div class="text-rose-700 flex items-center gap-1">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div>
                                                    {{ number_format($tRej, 1) }} mnt ditolak ({{ number_format($tRej/60, 2) }}h)
                                                </div>
                                            @endif
                                            @if($tPend > 0)
                                                <div class="text-yellow-700 flex items-center gap-1">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                                                    {{ number_format($tPend, 1) }} mnt ditinjau ({{ number_format($tPend/60, 2) }}h)
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($t->notes)
                                            <div class="mt-2 text-[11px] text-amber-800 bg-amber-50 p-2 rounded-lg border border-amber-100">
                                                <strong>Catatan Harian:</strong> {{ $t->notes }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        <div class="flex items-center gap-3 my-8">
            <div class="h-px bg-gray-200 flex-1"></div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Laporan Manual</span>
            <div class="h-px bg-gray-200 flex-1"></div>
        </div>
    </div>
@endif
