<x-app-layout>
    <x-slot name="metaTitle">Tax Center — PPh 21 &amp; PPh 23 — Kamerakita</x-slot>
    <x-slot name="metaDescription">Pusat perhitungan pemotongan pajak PPh 21/23 payroll, manajemen SPT Masa, arsip bukti potong, dan ekspor Coretax.</x-slot>

    <div class="space-y-6 pb-12" x-data="{ editingRecordId: null }">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 flex items-center gap-2.5" role="alert">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 flex items-center gap-2.5" role="alert">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">{{ $errors->first() }}</div>
        @endif

        <!-- Hero Banner: Tax Center Control Header -->
        <div class="relative overflow-hidden rounded-2xl sm:rounded-[32px] bg-gradient-to-br from-slate-900 via-[#162033] to-indigo-950 p-5 sm:p-8 text-white shadow-lg border border-slate-800/80">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-16 w-64 h-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/25 to-transparent pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-indigo-200/80 font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        TAX CENTER · PPH 21 &amp; PPH 23 PAYROLL
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        Manajemen Pajak Periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $month)->translatedFormat('F Y') }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                        Kalkulasi otomatis PPh 21 Bukan Pegawai (PMK 168/2023) &amp; PPh 23 Vendor Badan dari siklus payroll, unggah bukti potong, dan ekspor CSV Coretax. Pemasukan klien dikelola terpisah di Jurnal Keuangan.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                    <form method="GET" class="flex items-center gap-2 bg-white/10 border border-white/15 rounded-2xl p-1.5 backdrop-blur-sm">
                        <label for="tax-month" class="sr-only">Pilih Bulan Pajak</label>
                        <input id="tax-month" type="month" name="month" value="{{ $month }}" class="bg-transparent border-0 text-white text-xs sm:text-sm font-semibold focus:ring-0 px-2.5 py-1.5 [color-scheme:dark]">
                        <button type="submit" class="rounded-xl bg-white/15 hover:bg-white/25 px-3 py-1.5 text-xs font-bold text-white transition">Buka</button>
                    </form>

                    <div class="grid grid-cols-2 sm:flex items-center gap-2.5">
                        <form action="{{ route('tax.periods.sync') }}" method="POST" class="flex-1 sm:flex-none">
                            @csrf
                            <input type="hidden" name="month" value="{{ $month }}">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-white hover:bg-slate-100 px-4 py-3 text-xs font-bold text-slate-900 shadow-sm transition whitespace-nowrap">
                                <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Hitung / Sync Pajak</span>
                            </button>
                        </form>
                        <a href="{{ route('tax.export-csv', ['month' => $month]) }}" class="flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 hover:bg-emerald-400 px-4 py-3 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition whitespace-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>CSV Coretax</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 KPI Pajak Periode -->
        @php
            $totalPph = $period->totalWithholdingTaxIdr();
            $totalNetPayroll = max(0, $period->total_gross_idr - $totalPph);
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500">Total Bruto Payroll</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-black text-slate-900 tabular-nums tracking-tight">Rp{{ number_format($period->total_gross_idr, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Dasar penghasilan siklus {{ $month }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500">PPh 21 (Perorangan)</span>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-black text-indigo-700 tabular-nums tracking-tight">Rp{{ number_format($period->total_pph21_idr, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kode objek 21-100-09 (PMK 168)</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500">PPh 23 (Badan)</span>
                    <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 ring-1 ring-violet-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                    </div>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-black text-violet-700 tabular-nums tracking-tight">Rp{{ number_format($period->total_pph23_idr, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kode objek 24-104-01 (2% / 4%)</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500">Net Dibayar Payroll</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-black text-emerald-700 tabular-nums tracking-tight">Rp{{ number_format($totalNetPayroll, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Total setor PPh: <strong class="text-slate-700">Rp{{ number_format($totalPph, 0, ',', '.') }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Panel Status SPT Masa & Stepper -->
        <div class="bg-white rounded-2xl sm:rounded-[28px] border border-slate-200/80 p-5 sm:p-6 shadow-sm space-y-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Pelaporan SPT Masa Pajak · {{ $month }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black border {{ $period->status === 'REPORTED' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($period->status === 'PAID' ? 'bg-blue-50 text-blue-700 border-blue-200' : ($period->status === 'CALCULATED' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-amber-50 text-amber-700 border-amber-200')) }}">
                            {{ $period->status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        @if($period->ntpn_code)
                            NTPN Terdaftar: <strong class="font-mono text-slate-800">{{ $period->ntpn_code }}</strong>
                            @if($period->payment_date) · Tgl Setor: {{ $period->payment_date->translatedFormat('d M Y') }} @endif
                        @else
                            Lengkapi NTPN &amp; tanggal setor ketika pajak periode ini telah dibayar ke kas negara.
                        @endif
                    </p>
                </div>

                <!-- Visual Stepper -->
                @php
                    $stepOrder = ['DRAFT' => 1, 'CALCULATED' => 2, 'PAID' => 3, 'REPORTED' => 4];
                    $currentStep = $stepOrder[$period->status] ?? 1;
                @endphp
                <div class="flex items-center gap-1.5 sm:gap-2 text-[11px] font-bold">
                    @foreach($stepOrder as $label => $num)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $currentStep >= $num ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400' }}">
                            <span>{{ $num }}.</span>
                            <span>{{ $label }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('tax.periods.status', $period) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label for="period-status" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status Periode</label>
                    <select id="period-status" name="status" class="block w-full rounded-xl border-slate-200 text-sm font-semibold focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach(\App\Models\TaxPeriod::ALL_STATUSES as $st)
                            <option value="{{ $st }}" @selected($period->status === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ntpn-code" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Kode NTPN Setor</label>
                    <input id="ntpn-code" type="text" name="ntpn_code" value="{{ old('ntpn_code', $period->ntpn_code) }}" placeholder="Wajib untuk PAID / REPORTED" class="block w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="payment-date" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Tanggal Setor</label>
                    <input id="payment-date" type="date" name="payment_date" value="{{ old('payment_date', $period->payment_date?->toDateString()) }}" class="block w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <button type="submit" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-2.5 text-sm font-bold text-white transition">
                        Simpan Status SPT
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Pemotongan PPh 21 / PPh 23 Payroll -->
        <div class="bg-white rounded-2xl sm:rounded-[28px] border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Rincian Pemotongan PPh 21 &amp; PPh 23</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Klik <strong>Atur &amp; Bupot</strong> pada baris mana pun untuk memperbarui NPWP, tarif, nomor bupot, atau unggah PDF bukti potong.</p>
                </div>

                <form method="GET" class="grid grid-cols-1 sm:flex sm:flex-wrap items-center gap-2">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama, email, atau NPWP..." class="rounded-xl border-slate-200 text-xs sm:w-56">
                    <select name="tax_type" class="rounded-xl border-slate-200 text-xs">
                        <option value="">Semua Jenis</option>
                        <option value="PPH21" @selected(($filters['tax_type'] ?? '') === 'PPH21')>PPh 21</option>
                        <option value="PPH23" @selected(($filters['tax_type'] ?? '') === 'PPH23')>PPh 23</option>
                    </select>
                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 px-2">
                        <input type="checkbox" name="missing_tax_id" value="1" @checked(!empty($filters['missing_tax_id'])) class="rounded border-slate-300 text-indigo-600">
                        Belum ada NPWP/NIK
                    </label>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">Terapkan</button>
                </form>
            </div>

            <!-- Mobile View: Card List -->
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse($records as $rec)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ $rec->recipient_name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $rec->recipient_email }}</p>
                                <p class="text-[11px] font-mono mt-1 {{ $rec->recipient_npwp || $rec->recipient_nik ? 'text-slate-600' : 'text-rose-600 font-bold' }}">
                                    NPWP/NIK: {{ $rec->recipient_npwp ?: ($rec->recipient_nik ?: 'BELUM DIISI') }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold shrink-0 {{ $rec->tax_type === 'PPH23' ? 'bg-violet-50 text-violet-700' : 'bg-indigo-50 text-indigo-700' }}">
                                {{ $rec->tax_type }} · {{ $rec->status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Bruto</span>
                                <strong class="text-slate-800 tabular-nums">Rp{{ number_format($rec->gross_amount_idr, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">PPh</span>
                                <strong class="text-rose-700 tabular-nums">Rp{{ number_format($rec->tax_amount_idr, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Net</span>
                                <strong class="text-emerald-700 tabular-nums">Rp{{ number_format($rec->net_amount_idr, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <button type="button" @click="editingRecordId = editingRecordId === {{ $rec->id }} ? null : {{ $rec->id }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                Atur &amp; Bupot
                            </button>
                            @if($rec->bupot_path)
                                <a href="{{ route('tax.records.bupot.download', $rec) }}" class="text-xs font-bold text-emerald-700 underline">Unduh Bupot</a>
                            @endif
                        </div>

                        <div x-show="editingRecordId === {{ $rec->id }}" x-cloak class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 space-y-3">
                            <form action="{{ route('tax.records.update', $rec) }}" method="POST" class="space-y-2.5">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="tax_type" value="{{ $rec->tax_type }}">
                                <input type="hidden" name="tax_object_code" value="{{ $rec->tax_object_code }}">
                                <input type="hidden" name="tax_rate_percent" value="{{ $rec->tax_rate_percent }}">
                                <input type="text" name="recipient_npwp" value="{{ $rec->recipient_npwp }}" placeholder="NPWP / NITKU..." class="block w-full rounded-lg border-slate-200 text-xs">
                                <input type="text" name="bupot_number" value="{{ $rec->bupot_number }}" placeholder="Nomor Bukti Potong..." class="block w-full rounded-lg border-slate-200 text-xs">
                                <select name="status" class="block w-full rounded-lg border-slate-200 text-xs">
                                    @foreach(['CALCULATED', 'WITHHELD', 'REPORTED', 'EXEMPT'] as $rst)
                                        <option value="{{ $rst }}" @selected($rec->status === $rst)>{{ $rst }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full rounded-lg bg-slate-900 py-2 text-xs font-bold text-white">Simpan Perubahan</button>
                            </form>
                            <form action="{{ route('tax.records.bupot', $rec) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-slate-200 space-y-2">
                                @csrf
                                <input type="file" name="bupot_file" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full text-xs">
                                <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-xs font-bold text-white">Upload File Bupot</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-slate-400">
                        Belum ada data pemotongan pajak untuk periode {{ $month }}. Klik <strong>Hitung / Sync Pajak</strong> di atas setelah siklus payroll tersedia.
                    </div>
                @endforelse
            </div>

            <!-- Desktop View: Clean Table with Expandable Drawer -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-semibold">Penerima &amp; Identitas Pajak</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Jenis &amp; Kode Objek</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Bruto</th>
                            <th class="px-5 py-3.5 text-right font-semibold">DPP</th>
                            <th class="px-5 py-3.5 text-right font-semibold">PPh Terpotong</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Net Diterima</th>
                            <th class="px-5 py-3.5 text-center font-semibold">Status &amp; Bupot</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($records as $rec)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900 text-sm">{{ $rec->recipient_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $rec->recipient_email }}</p>
                                    <p class="text-[11px] font-mono mt-0.5 {{ $rec->recipient_npwp || $rec->recipient_nik ? 'text-slate-600' : 'text-rose-600 font-bold' }}">
                                        NPWP/NIK: {{ $rec->recipient_npwp ?: ($rec->recipient_nik ?: 'BELUM DIISI') }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $rec->tax_type === 'PPH23' ? 'bg-violet-50 text-violet-700' : 'bg-indigo-50 text-indigo-700' }}">{{ $rec->tax_type }}</span>
                                    <p class="text-[11px] text-slate-500 font-mono mt-1">{{ $rec->tax_object_code }} ({{ $rec->tax_rate_percent }}%)</p>
                                    <p class="text-[11px] text-slate-400">{{ $rec->notes }}</p>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-slate-800 tabular-nums">Rp{{ number_format($rec->gross_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right text-slate-600 tabular-nums">Rp{{ number_format($rec->dpp_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right font-bold text-rose-700 tabular-nums">Rp{{ number_format($rec->tax_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right font-bold text-emerald-700 tabular-nums">Rp{{ number_format($rec->net_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-center space-y-1">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ $rec->status }}</span>
                                    @if($rec->bupot_number)
                                        <p class="text-[10px] font-mono text-slate-500">{{ $rec->bupot_number }}</p>
                                    @endif
                                    @if($rec->bupot_path)
                                        <div><a href="{{ route('tax.records.bupot.download', $rec) }}" class="text-[11px] font-bold text-emerald-700 underline">Unduh PDF</a></div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" @click="editingRecordId = editingRecordId === {{ $rec->id }} ? null : {{ $rec->id }}" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                        <span>Atur &amp; Bupot</span>
                                    </button>
                                </td>
                            </tr>
                            <tr x-show="editingRecordId === {{ $rec->id }}" x-cloak class="bg-slate-50/90">
                                <td colspan="8" class="px-6 py-4">
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-end">
                                        <form action="{{ route('tax.records.update', $rec) }}" method="POST" class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-5 gap-2.5 items-end">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="tax_type" value="{{ $rec->tax_type }}">
                                            <input type="hidden" name="tax_object_code" value="{{ $rec->tax_object_code }}">
                                            <input type="hidden" name="tax_rate_percent" value="{{ $rec->tax_rate_percent }}">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">NPWP / NITKU</label>
                                                <input type="text" name="recipient_npwp" value="{{ $rec->recipient_npwp }}" class="w-full rounded-lg border-slate-200 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">PPh Override (Rp)</label>
                                                <input type="number" name="tax_amount_idr" value="{{ $rec->tax_amount_idr }}" min="0" class="w-full rounded-lg border-slate-200 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">No Bukti Potong</label>
                                                <input type="text" name="bupot_number" value="{{ $rec->bupot_number }}" class="w-full rounded-lg border-slate-200 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Status</label>
                                                <select name="status" class="w-full rounded-lg border-slate-200 text-xs">
                                                    @foreach(['CALCULATED', 'WITHHELD', 'REPORTED', 'EXEMPT'] as $rst)
                                                        <option value="{{ $rst }}" @selected($rec->status === $rst)>{{ $rst }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <button type="submit" class="w-full rounded-lg bg-slate-900 py-2 text-xs font-bold text-white hover:bg-slate-800">Simpan</button>
                                            </div>
                                        </form>

                                        <form action="{{ route('tax.records.bupot', $rec) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-2 border-t lg:border-t-0 lg:border-l border-slate-200 pt-3 lg:pt-0 lg:pl-4">
                                            @csrf
                                            <div class="flex-1 min-w-0">
                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Upload File Bukti Potong (PDF/Img)</label>
                                                <input type="file" name="bupot_file" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full text-xs text-slate-600">
                                            </div>
                                            <button type="submit" class="rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-indigo-700 shrink-0">Upload</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="mx-auto max-w-md space-y-2">
                                        <p class="text-sm font-bold text-slate-700">Belum ada data pemotongan pajak pada periode {{ $month }}</p>
                                        <p class="text-xs text-slate-500">Pastikan siklus pembayaran bulan tersebut sudah dibuat di menu Pembayaran, lalu klik tombol <strong>Hitung / Sync Pajak</strong> di bagian atas halaman.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
                <div class="p-4 border-t border-slate-100">{{ $records->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
