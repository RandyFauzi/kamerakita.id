<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
            {{ __('Manajemen Pembayaran Gaji') }}
        </h2>
    </x-slot>

    @php
        $queuedAmount = collect($workers)->sum('total_amount');
        $queuedReportCount = collect($workers)->sum(fn ($worker) => count($worker['reports']));
        $paidAmount = collect($payoutHistory)->sum('total_amount');
    @endphp

    <div class="py-2 sm:py-8" x-data="{
        currentTab: 'queue',
        showPayModal: false,
        activeWorker: {},
        evidenceImage: null,
        notes: '',
        copySuccess: false,
        
        openPayModal(worker) {
            this.activeWorker = worker;
            this.showPayModal = true;
            this.copySuccess = false;
        },
        
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.copySuccess = true;
                setTimeout(() => this.copySuccess = false, 2000);
            } catch (err) {
                console.error('Gagal menyalin rekening: ', err);
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success / Error Notification Toast -->
            @if(session('success'))
                <div x-data="{ showToast: true }" 
                     x-show="showToast" 
                     x-init="setTimeout(() => showToast = false, 3000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2 md:translate-y-0 md:translate-x-4"
                     x-transition:enter-end="opacity-100 translate-y-0 md:translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed top-6 right-6 z-50 max-w-sm w-full bg-emerald-50 border border-emerald-200 rounded-2xl shadow-xl p-4 flex items-start gap-3 animate-in slide-in-from-right duration-300" 
                     role="alert">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-emerald-950 leading-tight">Sukses</p>
                        <p class="text-xs text-emerald-800 mt-1 font-medium leading-relaxed">{{ session('success') }}</p>
                    </div>
                    <button @click="showToast = false" class="flex-shrink-0 text-emerald-400 hover:text-emerald-600 transition rounded-lg hover:bg-emerald-100 p-0.5 -mt-1 -mr-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
                    <p class="text-sm font-bold text-red-800">Pembayaran belum berhasil diproses</p>
                    <p class="mt-1 text-xs leading-5 text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Page Header Card -->
            <section class="overflow-hidden rounded-2xl bg-slate-950 p-5 text-white shadow-sm sm:rounded-3xl sm:p-7">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Payroll Desk</span>
                        <h3 class="mt-2 text-xl font-black tracking-tight sm:text-2xl">Pembayaran Gaji Worker</h3>
                        <p class="mt-2 text-xs leading-5 text-slate-300 sm:text-sm">
                            Kelola laporan yang telah disetujui, salin rekening tujuan, lalu catat pembayaran dengan bukti transfer.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 border-t border-slate-800 pt-4 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Siap Dibayar</span>
                            <strong class="mt-1 block text-lg font-black text-white">{{ $queuedReportCount }} laporan</strong>
                        </div>
                        <span class="h-9 w-px bg-slate-800"></span>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Antrean</span>
                            <strong class="mt-1 block text-lg font-black text-emerald-400">Rp {{ number_format($queuedAmount, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Worker Menunggu</span>
                            <strong class="mt-2 block text-2xl font-black text-slate-900">{{ count($workers) }}</strong>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Nominal Menunggu</span>
                            <strong class="mt-2 block text-xl font-black text-indigo-700">Rp {{ number_format($queuedAmount, 0, ',', '.') }}</strong>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2zm12 9h.01"/>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Total Sudah Dibayar</span>
                            <strong class="mt-2 block text-xl font-black text-emerald-700">Rp {{ number_format($paidAmount, 0, ',', '.') }}</strong>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dropdown Pilihan Periode Mingguan -->
            <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150 p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <form action="{{ route('payments.manage') }}" method="GET" class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-3">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <label for="period" class="shrink-0 text-xs font-bold text-gray-500 uppercase tracking-wider font-mono">Periode:</label>
                        <select name="period" id="period" onchange="this.form.submit()" class="block w-full sm:w-48 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-700 font-medium">
                            <option value="all" {{ $selectedPeriodKey === 'all' ? 'selected' : '' }}>Semua Periode</option>
                            @foreach($periods as $p)
                                <option value="{{ $p['start']->format('Y-m-d') . '|' . $p['end']->format('Y-m-d') }}" 
                                    {{ $selectedPeriodKey === ($p['start']->format('Y-m-d') . '|' . $p['end']->format('Y-m-d')) ? 'selected' : '' }}>
                                    {{ $p['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative w-full sm:w-auto flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="block w-full sm:w-64 pl-9 pr-3 py-2 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="submit" class="hidden">Search</button>
                </form>
                <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
                    <div class="text-xs font-semibold text-gray-400 text-right">
                        Rentang Laporan: <span class="text-slate-800 font-bold font-mono">{{ $startDate ? $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y') : 'Semua Waktu' }}</span>
                    </div>

                    @if(auth()->user()->role === 'superadmin' && count($workers) > 0)
                        <form action="{{ route('payments.batch-pay') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses SEMUA pembayaran untuk periode ini dengan bukti dummy?');">
                            @csrf
                            <input type="hidden" name="period_start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : 'all' }}">
                            <input type="hidden" name="period_end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : 'all' }}">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-sm shadow-indigo-200 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Batch Pay
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Tab Switchers -->
            <div class="grid grid-cols-2 gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 sm:inline-grid sm:min-w-[430px]">
                <button @click="currentTab = 'queue'"
                        :class="currentTab === 'queue' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                        class="min-h-10 rounded-lg px-3 py-2 text-[11px] font-black uppercase tracking-wider transition focus:outline-none sm:px-4">
                    Antrean <span class="ml-1 rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700">{{ count($workers) }}</span>
                </button>
                <button @click="currentTab = 'history'"
                        :class="currentTab === 'history' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                        class="min-h-10 rounded-lg px-3 py-2 text-[11px] font-black uppercase tracking-wider transition focus:outline-none sm:px-4">
                    Riwayat <span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-slate-700">{{ count($payoutHistory) }}</span>
                </button>
            </div>

            <!-- Workers Payout Accordion Queue -->
            <div x-show="currentTab === 'queue'" class="space-y-4">
                @forelse($workers as $index => $w)
                    <div x-data="{ expanded: false }" class="bg-white rounded-2xl border border-gray-150 shadow-sm overflow-hidden transition-all duration-300" :class="expanded ? 'shadow-md border-indigo-200' : 'hover:shadow-sm'">
                        <!-- Accordion Header -->
                        <div class="p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 cursor-pointer select-none" @click="expanded = !expanded">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 border border-gray-200 rounded-xl flex items-center justify-center font-black text-lg text-slate-700">
                                    {{ strtoupper(substr($w['partner']->full_name, 0, 2)) }}
                                </div>
                                <div class="space-y-0.5">
                                    <h4 class="text-base font-black text-slate-900">{{ $w['partner']->full_name }}</h4>
                                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-400 font-medium">
                                        <span>ID: {{ $w['partner']->mitra_id }}</span>
                                        <span aria-hidden="true">&middot;</span>
                                        <span>Rek: {{ $w['partner']->bank_name ?? 'BCA' }} ({{ $w['partner']->bank_account_number ?? $w['partner']->account_number ?? '-' }})</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                                <div class="text-left md:text-right">
                                    <span class="block text-[10px] font-bold text-gray-450 uppercase tracking-wider">Total Tagihan</span>
                                    <span class="block text-lg font-black text-indigo-600 leading-tight">Rp {{ number_format($w['total_amount'], 0, ',', '.') }}</span>
                                    <div class="flex items-center gap-1.5 justify-start md:justify-end mt-0.5">
                                        <span class="text-[10px] font-medium text-gray-400">Untuk {{ count($w['reports']) }} Laporan</span>
                                        <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 rounded">{{ $w['total_minutes'] }} Menit</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click.stop="openPayModal(@js($w))"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-750 text-white rounded-xl text-xs font-bold tracking-wider transition shadow-sm shadow-indigo-100">
                                        Proses Bayar
                                    </button>
                                    <div class="p-1 text-gray-400 hover:bg-gray-50 rounded-lg transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Body (Approved Reports List) -->
                        <div x-show="expanded" x-collapse x-cloak>
                            <div class="px-6 pb-6 border-t border-gray-100 bg-slate-50/50">
                                <div class="overflow-x-auto mt-4">
                                    <table class="min-w-full divide-y divide-gray-200/60 text-xs">
                                        <thead>
                                            <tr class="text-gray-450 font-bold text-left uppercase tracking-wider">
                                                <th class="pb-3 pt-2">ID Laporan</th>
                                                <th class="pb-3 pt-2">Tanggal Kerja</th>
                                                <th class="pb-3 pt-2">Durasi Disetujui</th>
                                                <th class="pb-3 pt-2">Est. Gaji (Rp)</th>
                                                <th class="pb-3 pt-2 text-right">Status QC</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 font-medium text-slate-700">
                                            @foreach($w['reports'] as $report)
                                                <tr>
                                                    <td class="py-3 font-mono text-gray-500">{{ substr($report->id, 0, 8) }}...</td>
                                                    <td class="py-3">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                                    <td class="py-3 font-bold">{{ $report->approved_duration_formatted }}</td>
                                                    <td class="py-3">
                                                        Rp {{ number_format(round(($report->approved_duration_minutes / 60) * $w['rate']), 0, ',', '.') }}
                                                    </td>
                                                    <td class="py-3 text-right">
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border bg-emerald-50 border-emerald-150 text-emerald-800">
                                                            Approved
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex min-h-64 items-center justify-center rounded-2xl border border-gray-150 bg-white px-6 py-10 text-center shadow-sm">
                        <div class="max-w-md space-y-4">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            </div>
                            <div>
                            <h4 class="text-base font-black text-slate-900">Tidak Ada Antrean Pembayaran</h4>
                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                Semua laporan yang telah disetujui sudah dibayar. Antrean baru akan muncul setelah admin menyetujui laporan worker.
                            </p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Payout History Tab -->
            <div x-show="currentTab === 'history'" class="space-y-4" x-cloak>
                @forelse($payoutHistory as $index => $pay)
                    <div x-data="{ expanded: false }" class="bg-white rounded-2xl border border-gray-150 shadow-sm overflow-hidden transition-all duration-300" :class="expanded ? 'shadow-md border-indigo-200' : 'hover:shadow-sm'">
                        <!-- Accordion Header -->
                        <div class="p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 cursor-pointer select-none" @click="expanded = !expanded">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 border border-gray-200 rounded-xl flex items-center justify-center font-black text-lg text-slate-700">
                                    {{ strtoupper(substr($pay['partner']->full_name, 0, 2)) }}
                                </div>
                                <div class="space-y-0.5">
                                    <h4 class="text-base font-black text-slate-900">{{ $pay['partner']->full_name }}</h4>
                                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-400 font-medium">
                                        <span>Tanggal Bayar: {{ $pay['paid_at']->translatedFormat('d F Y - H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                                <div class="text-left md:text-right">
                                    <span class="block text-[10px] font-bold text-gray-450 uppercase tracking-wider">Total Dibayar</span>
                                    <span class="block text-lg font-black text-emerald-600 leading-tight">Rp {{ number_format($pay['total_amount'], 0, ',', '.') }}</span>
                                    <span class="block text-[10px] font-medium text-gray-400">Untuk {{ count($pay['reports']) }} Laporan</span>
                                </div>

                                <div class="flex items-center gap-3">
                                    @if($pay['proof_url'])
                                        <a href="{{ $pay['proof_url'] }}" target="_blank" @click.stop
                                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 text-indigo-750 rounded-xl text-xs font-bold transition duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Bukti
                                        </a>
                                    @endif

                                    <form action="{{ route('payments.cancel') }}" method="POST" @click.stop onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus riwayat pembayaran ini? Seluruh laporan dalam batch ini akan otomatis dikembalikan ke status Unpaid.')" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="batch_id" value="{{ $pay['batch_id'] }}">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 border border-rose-100 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold transition duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Batal Bayar
                                        </button>
                                    </form>

                                    <div class="p-1.5 text-gray-400 hover:bg-gray-50 rounded-lg transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Body (Paid Reports List) -->
                        <div x-show="expanded" x-collapse x-cloak>
                            <div class="px-6 pb-6 border-t border-gray-100 bg-slate-50/50">
                                <div class="overflow-x-auto mt-4">
                                    <table class="min-w-full divide-y divide-gray-200/60 text-xs">
                                        <thead>
                                            <tr class="text-gray-450 font-bold text-left uppercase tracking-wider">
                                                <th class="pb-3 pt-2">ID Laporan</th>
                                                <th class="pb-3 pt-2">Tanggal Kerja</th>
                                                <th class="pb-3 pt-2">Durasi Kerja</th>
                                                <th class="pb-3 pt-2 text-right">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 font-medium text-slate-700">
                                            @foreach($pay['reports'] as $report)
                                                <tr>
                                                    <td class="py-3 font-mono text-gray-500">{{ substr($report->id, 0, 8) }}...</td>
                                                    <td class="py-3">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                                    <td class="py-3 font-bold">{{ $report->approved_duration_formatted }}</td>
                                                    <td class="py-3 text-right">
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border bg-emerald-50 border-emerald-150 text-emerald-800">
                                                            Paid
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex min-h-64 items-center justify-center rounded-2xl border border-gray-150 bg-white px-6 py-10 text-center shadow-sm">
                        <div class="max-w-md space-y-4">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 text-gray-400">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            </div>
                            <div>
                            <h4 class="text-base font-black text-slate-900">Belum Ada Riwayat Pembayaran</h4>
                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                Pembayaran yang telah dikonfirmasi akan tersimpan dan ditampilkan pada bagian ini.
                            </p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Payment Process Modal -->
        <template x-if="showPayModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
                <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200 flex flex-col my-8">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-950 text-white flex justify-between items-center">
                        <div>
                            <span class="text-[10px] uppercase font-black text-indigo-400 tracking-widest">Kirim Gaji Kolektif</span>
                            <h3 class="text-base font-black leading-tight" x-text="activeWorker.partner.full_name"></h3>
                        </div>
                        <button @click="showPayModal = false" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form :action="'/payments/manage/' + activeWorker.partner.id + '/pay'" method="POST" enctype="multipart/form-data" class="flex-1 p-6 space-y-5">
                        @csrf
                        <input type="hidden" name="period_start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : 'all' }}">
                        <input type="hidden" name="period_end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : 'all' }}">
                        
                        <!-- Earnings Summary Card -->
                        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 text-white rounded-2xl p-5 shadow-inner flex justify-between items-center">
                            <div>
                                <span class="block text-[9px] uppercase tracking-wider text-indigo-200 font-bold">Total Nilai Transfer</span>
                                <span class="block text-2xl font-black tracking-tight" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(activeWorker.total_amount)"></span>
                                <span class="block text-[10px] text-indigo-100 mt-1" x-text="'Total Menit: ' + activeWorker.total_minutes + ' m (' + Number(activeWorker.hours).toFixed(2) + ' jam)'"></span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[9px] uppercase tracking-wider text-indigo-200 font-bold">Rate / Jam</span>
                                <span class="block text-sm font-black" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(activeWorker.rate)"></span>
                            </div>
                        </div>

                        <!-- Bank Detail Copy Box -->
                        <div class="bg-slate-50 border border-gray-200 rounded-2xl p-4 space-y-3">
                            <span class="block text-[10px] font-bold text-gray-450 uppercase tracking-wider">Rekening Tujuan</span>
                            
                            <div class="grid grid-cols-2 gap-4 text-xs font-semibold text-slate-700">
                                <div>
                                    <span class="block text-[9px] text-gray-400 font-normal uppercase">Nama Bank</span>
                                    <span class="block font-bold text-slate-800" x-text="activeWorker.partner.bank_name || 'BCA'"></span>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-gray-400 font-normal uppercase">Pemilik Rekening</span>
                                    <span class="block font-bold text-slate-800" x-text="activeWorker.partner.bank_account_owner || activeWorker.partner.account_owner_name || activeWorker.partner.full_name"></span>
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl p-3 flex justify-between items-center shadow-sm">
                                <div class="space-y-0.5">
                                    <span class="block text-[9px] text-gray-400 uppercase leading-none">Nomor Rekening</span>
                                    <span class="font-mono text-sm font-black tracking-wider text-indigo-650" x-text="activeWorker.partner.bank_account_number || activeWorker.partner.account_number || '-'"></span>
                                </div>
                                <button type="button" 
                                        @click="copyToClipboard(activeWorker.partner.bank_account_number || activeWorker.partner.account_number)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-indigo-100 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-bold hover:bg-indigo-100 transition duration-150">
                                    <span x-text="copySuccess ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Upload Proof Input -->
                        <div class="space-y-1.5">
                            <label for="payment_proof" class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Unggah Bukti Transfer <span class="text-red-500">*</span></label>
                            <input type="file" name="payment_proof" id="payment_proof" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition file:cursor-pointer">
                            <p class="text-[10px] text-gray-400">Pastikan bukti transfer mencantumkan nominal transfer yang tepat dan nama rekening tujuan yang sesuai.</p>
                        </div>

                        <!-- Admin Fee Note -->
                        <div class="bg-orange-50/50 border border-orange-100/50 rounded-xl p-3 flex gap-2">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-orange-550" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-[10px] text-orange-800 leading-normal font-medium">
                                Biaya admin transfer antar bank (LLG / RTGS / BI-Fast) sebesar <strong>Rp 2.500</strong> sepenuhnya dibebankan kepada penerima (Mitra).
                            </p>
                        </div>

                        <!-- Modal Action Buttons -->
                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" @click="showPayModal = false" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 transition">
                                Batalkan
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-750 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-100 transition">
                                Konfirmasi & Bayar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
