<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-500">Tagihan & Rekap</span>
                <h2 class="font-black text-2xl sm:text-3xl text-gray-900 leading-tight tracking-tight mt-1">Pembuat Tagihan</h2>
            </div>
            <a href="{{ route('payments.manage') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-sm rounded-xl transition duration-200 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Ke Payments Gaji
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="px-4 sm:px-0">
                <div class="bg-white border border-indigo-100 rounded-3xl p-6 sm:p-8 relative overflow-hidden shadow-sm">
                    <div class="relative z-10 max-w-3xl">
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight text-indigo-900">Generator Tagihan Pekerja</h3>
                        <p class="text-slate-600 mt-2 text-sm sm:text-base leading-relaxed">Tarik semua pekerjaan yang sudah di-approve pada rentang tanggal tertentu, lalu jadikan sebagai 1 Invoice penagihan utuh per pekerja. Hasil generate akan langsung masuk otomatis ke antrean <span class="font-bold text-indigo-600">Payments Gaji</span>.</p>
                    </div>
                    <div class="absolute right-0 top-0 w-64 h-full bg-gradient-to-l from-indigo-50 to-transparent opacity-60"></div>
                </div>
            </div>

            @if(session('success'))
                <div class="mx-4 sm:mx-0 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-start gap-3 shadow-sm">
                    <div class="p-2 bg-emerald-100 rounded-xl text-emerald-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="mt-1.5">
                        <h4 class="text-sm font-black text-emerald-900">Berhasil!</h4>
                        <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-4 sm:mx-0 bg-rose-50 border border-rose-100 p-4 rounded-2xl flex items-start gap-3 shadow-sm">
                    <div class="p-2 bg-rose-100 rounded-xl text-rose-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div class="mt-1.5">
                        <h4 class="text-sm font-black text-rose-900">Gagal</h4>
                        <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 px-4 sm:px-0">
                <!-- Buat Payroll Baru (Unpaid) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden sticky top-6">
                        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
                            <div>
                                <h3 class="text-lg font-black text-gray-900">Form Generator</h3>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-1">Setup Tagihan Baru</p>
                            </div>
                            <div class="w-12 h-12 bg-white border border-gray-150 rounded-2xl flex items-center justify-center text-indigo-500 shadow-sm shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('payrolls.store') }}" method="POST" class="space-y-5">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-2">Pilih Worker</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <select name="atlas_worker_id" required class="block w-full pl-11 rounded-xl border-gray-200 bg-gray-50 py-3 text-sm font-semibold text-gray-700 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 transition-all">
                                            <option value="">-- Pilih Pekerja --</option>
                                            <option value="ALL" class="font-black text-indigo-700">⚡ GENERATE SEMUA PEKERJA SEKALIGUS</option>
                                            @foreach($unpaidWorkers as $worker)
                                                @if($worker->atlasWorker)
                                                    <option value="{{ $worker->atlas_worker_id }}">
                                                        {{ $worker->atlasWorker->atlas_email }} ({{ round($worker->total_approved_minutes / 60, 1) }} Jam - {{ $worker->total_tasks }} Tasks)
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-2">Mulai (Cutoff)</label>
                                        <input type="date" name="period_start" required class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-sm font-semibold text-gray-700 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-2">Akhir (Cutoff)</label>
                                        <input type="date" name="period_end" required value="{{ date('Y-m-d') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-sm font-semibold text-gray-700 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 transition-all">
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-100">
                                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin mengunci/membuat Invoice untuk periode ini?')" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-black rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-md shadow-indigo-600/20 overflow-hidden">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            Generate Invoice
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Histori Payrolls -->
                <div class="lg:col-span-7">
                    <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
                            <div>
                                <h3 class="text-lg font-black text-gray-900">Riwayat Tagihan</h3>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-1">Daftar Tagihan Yang Dibuat</p>
                            </div>
                        </div>
                        <div class="flex-1 p-0">
                            <ul class="divide-y divide-gray-100">
                                @forelse($payrolls as $pr)
                                    <li class="p-5 sm:p-6 hover:bg-slate-50 transition-colors group">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-xl {{ $pr->status === 'PAID' ? 'bg-emerald-50 text-emerald-500 border border-emerald-100' : 'bg-indigo-50 text-indigo-500 border border-indigo-100' }} flex items-center justify-center shrink-0">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-black text-slate-900">{{ $pr->atlasWorker->atlas_email ?? 'Unknown' }}</h4>
                                                    <p class="text-xs font-semibold text-gray-500 mt-1">Cutoff: <span class="text-gray-700">{{ $pr->period_start->format('d M') }} - {{ $pr->period_end->format('d M Y') }}</span></p>
                                                    <span class="inline-flex items-center gap-1.5 mt-2 text-[10px] font-black uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ round($pr->total_approved_minutes / 60, 2) }} Jam Billable
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col items-start sm:items-end gap-3 sm:gap-2 pl-16 sm:pl-0">
                                                <div class="text-left sm:text-right">
                                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Nominal</span>
                                                    <p class="text-lg font-black text-slate-900 leading-tight">Rp {{ number_format($pr->amount_rupiah, 0, ',', '.') }}</p>
                                                </div>
                                                
                                                @if($pr->status === 'PAID')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 uppercase tracking-widest border border-emerald-200">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        LUNAS
                                                    </span>
                                                @else
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black bg-amber-100 text-amber-800 uppercase tracking-widest border border-amber-200">
                                                            BELUM DIBAYAR
                                                        </span>
                                                        <form action="{{ route('payrolls.destroy', $pr) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" onclick="return confirm('Batalkan tagihan ini? Task akan dikembalikan ke status Unpaid.')" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Batalkan Tagihan">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <div class="p-12 text-center">
                                        <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-3xl flex items-center justify-center mx-auto text-gray-400 mb-4 shadow-sm">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        </div>
                                        <h4 class="text-base font-bold text-gray-900">Belum Ada Riwayat</h4>
                                        <p class="text-sm text-gray-500 mt-1">Buat tagihan baru melalui form di sebelah kiri.</p>
                                    </div>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
