<x-app-layout>
    <x-slot name="title">Pembayaran</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight">
                    Pembayaran
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($activeCycle)
                        {{ $activeCycle->name }}
                        &middot; Sheet: <span class="font-semibold text-slate-700">{{ $activeCycle->source_label }}</span>
                        @if($activeCycle->frozen_on)
                            &middot; Frozen {{ $activeCycle->frozen_on->format('d M Y') }}
                        @endif
                    @else
                        Upload file Excel / CSV pembayaran untuk mendeteksi &amp; mengintegrasikan akun otomatis
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-import-cycle-modal'))"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload Excel Cycle
                </button>

                @if($activeCycle)
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-financials-modal'))"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Atur Revenue / Kurs
                    </button>

                    <a
                        href="{{ route('payments.export-csv', array_merge(request()->query(), ['variant' => 'sheet'])) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Sheet
                    </a>

                    <a
                        href="{{ route('payments.export-csv', array_merge(request()->query(), ['variant' => 'bank'])) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 shadow-sm transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Export Rekening
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    @php
        $fmtHours = fn ($v) => number_format((float) $v, 2, ',', '.');
        $fmtIdr = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
        $fmtIdrDec = fn ($v) => 'Rp ' . number_format((float) $v, ((float) $v - floor((float) $v)) > 0.001 ? 2 : 0, ',', '.');

        $sortUrl = function (string $column) use ($filters) {
            $isCurrent = $filters['sort'] === $column;
            $nextDir = ($isCurrent && $filters['dir'] === 'desc') ? 'asc' : 'desc';
            return route('payments.manage', array_merge(request()->query(), [
                'sort' => $column,
                'dir' => $nextDir,
                'page' => 1,
            ]));
        };
    @endphp

    <div
        x-data="{
            showImportModal: false,
            showFinancialsModal: false,
            dragActive: false,
            selectedFileName: '',
            selectedIds: [],
            allPageIds: @json($displayRows->pluck('entry_id')->filter()->values()->all()),
            toggleAll(checked) {
                this.selectedIds = checked ? [...this.allPageIds] : [];
            },
            handleFilePicked(files) {
                if (files && files.length > 0) {
                    this.selectedFileName = files[0].name;
                }
            },
            handleDrop(event, inputRef) {
                this.dragActive = false;
                const files = event.dataTransfer?.files;
                if (files && files.length > 0 && inputRef) {
                    inputRef.files = files;
                    this.selectedFileName = files[0].name;
                }
            },
            submitSingleStatus(entryId, status) {
                const form = this.$refs.singleStatusForm;
                this.$refs.singleEntryId.value = entryId;
                this.$refs.singleEntryStatus.value = status;
                form.submit();
            }
        }"
        @open-import-cycle-modal.window="showImportModal = true"
        @open-financials-modal.window="showFinancialsModal = true"
        class="py-5 sm:py-7"
    >
        <div class="max-w-[1600px] mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-between gap-3">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!$activeCycle)
                <!-- Empty State: Drag & Drop Upload Excel Langsung -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm max-w-4xl mx-auto">
                    <div class="text-center max-w-xl mx-auto">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                            Integrasi Pembayaran Excel &amp; Database
                        </span>
                        <h3 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900">
                            Upload File Excel Payment Cycle
                        </h3>
                        <p class="mt-1.5 text-sm text-slate-600">
                            Upload file <code>.xlsx</code>, <code>.xls</code>, atau <code>.csv</code>. Sistem hanya membaca 1 sheet (<code>kamerakita-id</code> / <code>kamerakita-ai</code>), mencocokkan akun ke database, dan otomatis membuatkan akun baru bertanda jika belum terdaftar.
                        </p>
                    </div>

                    <form action="{{ route('payments.import-csv') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                        @csrf
                        <label
                            @dragover.prevent="dragActive = true"
                            @dragleave.prevent="dragActive = false"
                            @drop.prevent="handleDrop($event, $refs.emptyFileInput)"
                            :class="dragActive ? 'border-indigo-500 bg-indigo-50/70' : 'border-slate-300 bg-slate-50/70 hover:border-indigo-400 hover:bg-indigo-50/30'"
                            class="block cursor-pointer border-2 border-dashed rounded-2xl p-8 text-center transition-all"
                        >
                            <input
                                x-ref="emptyFileInput"
                                type="file"
                                name="excel_file"
                                accept=".xlsx,.xls,.csv,.txt"
                                required
                                @change="handleFilePicked($event.target.files)"
                                class="sr-only"
                            >
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <p class="mt-3 text-sm font-bold text-slate-800" x-text="selectedFileName ? 'File dipilih: ' + selectedFileName : 'Tarik & lepas file Excel (.xlsx / .xls / .csv) di sini'"></p>
                            <p class="mt-1 text-xs text-slate-500">Atau klik area ini untuk memilih file dari komputer Anda (maks. 20 MB)</p>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Cycle (opsional)</label>
                                <input type="text" name="cycle_name" placeholder="Otomatis dari header file" class="w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Rate Default (IDR/jam)</label>
                                <input type="number" name="worker_rate_idr" value="60000" min="1000" step="1000" class="w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Revenue IDR (opsional)</label>
                                <input type="number" name="revenue_idr" placeholder="Otomatis baca dari sheet" min="0" class="w-full rounded-xl border-slate-300 text-sm">
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-sm transition-colors">
                                Proses Excel &amp; Integrasikan Akun
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Bar Pilih Cycle Excel Tersimpan -->
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1">File Cycle:</span>

                        @foreach($cycles as $cycle)
                            @php $isSelected = $activeCycle->id === $cycle->id; @endphp
                            <a
                                href="{{ route('payments.manage', ['cycle_id' => $cycle->id]) }}"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors {{ $isSelected ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}"
                            >
                                <span>{{ $cycle->name }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                        <span>Rate: <strong class="text-slate-900">{{ $fmtIdr($activeCycle->worker_rate_idr) }}/jam</strong></span>
                        <span>&middot;</span>
                        <span>Invoice Klien: <strong class="text-slate-900">{{ $fmtHours($activeCycle->client_billable_hours) }} jam &times; US${{ number_format($activeCycle->client_rate_usd, 2) }} = US${{ number_format($activeCycle->client_total_usd, 2, ',', '.') }}</strong></span>

                        <form action="{{ route('payments.destroy-cycle', $activeCycle) }}" method="POST" onsubmit="return confirm('Hapus cycle {{ $activeCycle->name }} beserta daftar bayarnya?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold underline ml-1">
                                Hapus Cycle
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 4 Summary Cards Finansial (Total Bayar, Revenue, Omzet, Bagi 4) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Bayar</span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                {{ number_format($cycleTotals['worker_count'], 0, ',', '.') }} akun
                            </span>
                        </div>
                        <p class="mt-2 text-xl sm:text-2xl font-extrabold text-slate-900 tabular-nums">
                            {{ $fmtIdr($cycleTotals['total_pay_idr']) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $fmtHours($cycleTotals['billable_hours']) }} billable jam
                            &middot; Paid: <strong class="text-emerald-700">{{ $cycleTotals['paid_count'] }}/{{ $cycleTotals['worker_count'] }}</strong>
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Revenue</span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                                US${{ number_format($cycleTotals['client_total_usd'], 2, ',', '.') }}
                            </span>
                        </div>
                        <p class="mt-2 text-xl sm:text-2xl font-extrabold text-blue-700 tabular-nums">
                            {{ $cycleTotals['revenue_idr'] > 0 ? $fmtIdr($cycleTotals['revenue_idr']) : 'Belum Diisi' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            @if($cycleTotals['usd_to_idr_rate'])
                                Kurs efektif ~{{ $fmtIdr($cycleTotals['usd_to_idr_rate']) }}/USD
                            @else
                                Klik "Atur Revenue / Kurs" untuk isi IDR
                            @endif
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Omzet</span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                                Revenue - Bayar
                            </span>
                        </div>
                        <p class="mt-2 text-xl sm:text-2xl font-extrabold tabular-nums {{ $cycleTotals['omzet_idr'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $cycleTotals['revenue_idr'] > 0 ? $fmtIdr($cycleTotals['omzet_idr']) : '-' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Margin bersih setelah bayar worker
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Bagi 4</span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                                Omzet &divide; 4
                            </span>
                        </div>
                        <p class="mt-2 text-xl sm:text-2xl font-extrabold text-slate-900 tabular-nums">
                            {{ $cycleTotals['revenue_idr'] > 0 ? $fmtIdrDec($cycleTotals['bagi_4_idr']) : '-' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Estimasi pembagian per bagian
                        </p>
                    </div>
                </div>

                @if($cycleTotals['auto_created_count'] > 0)
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm text-amber-950">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-200 text-amber-900">
                                Auto DB
                            </span>
                            <span>
                                <strong>{{ $cycleTotals['auto_created_count'] }} akun</strong> belum ada di database sebelumnya dan sudah otomatis dibuatkan dengan tanda <strong>Akun Baru (Auto DB)</strong>.
                            </span>
                        </div>
                        <a
                            href="{{ route('payments.manage', ['cycle_id' => $activeCycle->id, 'account_type' => 'auto_created']) }}"
                            class="px-3 py-1 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition-colors"
                        >
                            Lihat {{ $cycleTotals['auto_created_count'] }} Akun Baru
                        </a>
                    </div>
                @endif

                <!-- Panel Filter & Search Per Akun -->
                <form action="{{ route('payments.manage') }}" method="GET" class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm space-y-3">
                    <input type="hidden" name="cycle_id" value="{{ $activeCycle->id }}">
                    <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                    <input type="hidden" name="dir" value="{{ $filters['dir'] }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Cari Akun / Email / Rekening</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ $filters['search'] }}"
                                    placeholder="Ketik email, nama partner, ID KMK, induk, bank..."
                                    class="w-full rounded-xl border-slate-300 text-sm pl-9 pr-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Tipe Akun</label>
                            <select name="account_type" onchange="this.form.submit()" class="w-full rounded-xl border-slate-300 text-sm py-2 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all" @selected($filters['account_type'] === 'all')>Semua Akun</option>
                                <option value="direct_worker" @selected($filters['account_type'] === 'direct_worker')>Worker Langsung</option>
                                <option value="under_mitra" @selected($filters['account_type'] === 'under_mitra')>Under Mitra / Main Account</option>
                                <option value="mitra" @selected($filters['account_type'] === 'mitra')>Mitra &amp; Timnya</option>
                                <option value="main_account" @selected($filters['account_type'] === 'main_account')>Main Account &amp; Sub-Akun</option>
                                <option value="rekruter" @selected($filters['account_type'] === 'rekruter')>Rekruter</option>
                                <option value="auto_created" @selected($filters['account_type'] === 'auto_created')>Akun Baru (Auto-Dibuat Excel)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Mitra / Akun Induk</label>
                            <select name="parent_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-300 text-sm py-2 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Induk</option>
                                @foreach($parentOptions as $parentOpt)
                                    <option value="{{ $parentOpt['id'] }}" @selected($filters['parent_id'] === $parentOpt['id'])>
                                        {{ $parentOpt['name'] }} {{ $parentOpt['mitra_id'] ? "({$parentOpt['mitra_id']})" : '' }} [{{ $parentOpt['count'] }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Status Bayar</label>
                            <select name="pay_status" onchange="this.form.submit()" class="w-full rounded-xl border-slate-300 text-sm py-2 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all" @selected($filters['pay_status'] === 'all')>Semua Status</option>
                                <option value="unpaid" @selected($filters['pay_status'] === 'unpaid')>Belum Dibayar</option>
                                <option value="paid" @selected($filters['pay_status'] === 'paid')>Sudah Dibayar</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="submit" class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-colors">
                                Terapkan Filter
                            </button>
                            @if($hasActiveFilter)
                                <a
                                    href="{{ route('payments.manage', ['cycle_id' => $activeCycle->id]) }}"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition-colors"
                                >
                                    Reset Filter
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-600">
                            <span>Tampilkan:</span>
                            <select name="per_page" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-xs py-1 px-2">
                                <option value="50" @selected($filters['per_page'] === '50')>50 baris</option>
                                <option value="100" @selected($filters['per_page'] === '100')>100 baris</option>
                                <option value="250" @selected($filters['per_page'] === '250')>250 baris</option>
                                <option value="all" @selected($filters['per_page'] === 'all')>Semua ({{ $filteredSummary['count'] }})</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Subtotal Filter -->
                @if($hasActiveFilter)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm">
                        <div class="flex flex-wrap items-center gap-3 text-indigo-950">
                            <span class="font-bold">Hasil Filter ({{ number_format($filteredSummary['count'], 0, ',', '.') }} akun):</span>
                            <span>Billable: <strong>{{ $fmtHours($filteredSummary['billable_hours']) }} jam</strong></span>
                            <span>&middot;</span>
                            <span>Rejected: <strong>{{ $fmtHours($filteredSummary['rejected_hours']) }} jam</strong></span>
                            <span>&middot;</span>
                            <span>Recordings: <strong>{{ number_format($filteredSummary['recordings_count'], 0, ',', '.') }}</strong></span>
                        </div>
                        <div class="font-extrabold text-indigo-700 tabular-nums">
                            Subtotal Yang Perlu dibayarkan: {{ $fmtIdr($filteredSummary['amount_to_pay_idr']) }}
                        </div>
                    </div>
                @endif

                <!-- Bulk Action Bar -->
                <div
                    x-show="selectedIds.length > 0"
                    style="display: none;"
                    class="bg-slate-900 text-white rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3 shadow-lg"
                >
                    <div class="text-xs sm:text-sm font-medium">
                        <strong x-text="selectedIds.length"></strong> akun dipilih pada halaman ini
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <form action="{{ route('payments.update-status') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="paid">
                            <template x-for="id in selectedIds" :key="'p-' + id">
                                <input type="hidden" name="entry_ids[]" :value="id">
                            </template>
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-xs font-bold text-white transition-colors">
                                Tandai Sudah Dibayar
                            </button>
                        </form>
                        <form action="{{ route('payments.update-status') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="unpaid">
                            <template x-for="id in selectedIds" :key="'u-' + id">
                                <input type="hidden" name="entry_ids[]" :value="id">
                            </template>
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold text-white transition-colors">
                                Kembalikan Unpaid
                            </button>
                        </form>
                    </div>
                </div>

                <form x-ref="singleStatusForm" action="{{ route('payments.update-status') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="entry_ids[]" x-ref="singleEntryId">
                    <input type="hidden" name="status" x-ref="singleEntryStatus">
                </form>

                <!-- Tabel Utama (Pas muat tanpa terpotong di desktop) -->
                <div class="bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto max-h-[75vh]">
                        <table class="min-w-full border-collapse text-xs">
                            <thead class="bg-[#2b2d30] text-white sticky top-0 z-10 select-none">
                                <tr class="divide-x divide-slate-600">
                                    <th class="px-2 py-2.5 text-center w-9 font-semibold">#</th>
                                    <th class="px-2 py-2.5 text-center w-9">
                                        <input
                                            type="checkbox"
                                            @change="toggleAll($event.target.checked)"
                                            :checked="allPageIds.length > 0 && selectedIds.length === allPageIds.length"
                                            class="rounded border-slate-500 bg-slate-700 text-indigo-500 focus:ring-0"
                                            title="Pilih semua baris di halaman ini"
                                        >
                                    </th>
                                    <th class="px-3 py-2.5 text-left font-bold">
                                        <a href="{{ $sortUrl('worker_email') }}" class="inline-flex items-center gap-1 hover:text-indigo-300">
                                            Worker email
                                            @if($filters['sort'] === 'worker_email')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </a>
                                    </th>
                                    <th class="px-3 py-2.5 text-left font-bold whitespace-nowrap">Rekening</th>
                                    <th class="px-2.5 py-2.5 text-right font-bold">
                                        <a href="{{ $sortUrl('billable_hours') }}" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                            Billable hours (to be paid)
                                            @if($filters['sort'] === 'billable_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </a>
                                    </th>
                                    <th class="px-2.5 py-2.5 text-right font-bold">
                                        <a href="{{ $sortUrl('rejected_hours') }}" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                            Rejected hours
                                            @if($filters['sort'] === 'rejected_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </a>
                                    </th>
                                    <th class="px-2.5 py-2.5 text-right font-bold">
                                        <a href="{{ $sortUrl('not_checked_hours') }}" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                            Not checked yet (hours)
                                            @if($filters['sort'] === 'not_checked_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </a>
                                    </th>
                                    <th class="px-2.5 py-2.5 text-right font-bold">
                                        <a href="{{ $sortUrl('total_hours') }}" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                            Total hours
                                            @if($filters['sort'] === 'total_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </a>
                                    </th>
                                    <th class="px-2.5 py-2.5 text-right font-bold">
                                        <a href="{{ $sortUrl('recordings_count') }}" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                            Recordings
                                            @if($filters['sort'] === 'recordings_count')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </a>
                                    </th>
                                    <th class="px-3 py-2.5 text-right font-bold whitespace-nowrap">
                                        <a href="{{ $sortUrl('amount_to_pay_idr') }}" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                            Yang Perlu dibayarkan
                                            @if($filters['sort'] === 'amount_to_pay_idr')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </a>
                                    </th>
                                    <th class="px-2.5 py-2.5 text-center font-bold whitespace-nowrap">Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse($displayRows as $idx => $row)
                                    @php
                                        $rowNum = $paginatedRows ? ($paginatedRows->firstItem() + $idx) : ($idx + 1);
                                        $roleBadgeClass = match($row['account_category']) {
                                            'direct_worker' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'under_mitra' => 'bg-violet-50 text-violet-700 border-violet-200',
                                            'under_main_account' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'mitra' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'main_account' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'rekruter' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                                        };
                                    @endphp
                                    <tr class="divide-x divide-slate-200 hover:bg-blue-50/50 transition-colors {{ $row['is_paid'] ? 'bg-emerald-50/30' : ($row['was_auto_created'] ? 'bg-amber-50/40' : ($idx % 2 === 1 ? 'bg-slate-50/60' : '')) }}">
                                        <td class="px-2 py-2 text-center text-slate-400 font-mono text-[11px] tabular-nums">
                                            {{ $rowNum }}
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <input
                                                type="checkbox"
                                                value="{{ $row['entry_id'] }}"
                                                x-model.number="selectedIds"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            >
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="font-medium text-slate-900 select-all">{{ $row['worker_email'] }}</span>
                                                @if($row['was_auto_created'])
                                                    <span
                                                        title="Akun belum ada di database saat upload Excel, otomatis dibuatkan sistem sebagai Worker baru."
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold"
                                                    >
                                                        Akun Baru (Auto DB)
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px]">
                                                <span class="inline-flex items-center px-1.5 rounded border font-semibold {{ $roleBadgeClass }}">
                                                    {{ $row['account_category_label'] }}
                                                </span>
                                                @if($row['partner_name'])
                                                    <span class="text-slate-600 font-medium">
                                                        {{ $row['partner_name'] }}
                                                        @if($row['mitra_id'])({{ $row['mitra_id'] }})@endif
                                                    </span>
                                                @endif
                                                @if($row['parent_name'])
                                                    <span class="text-slate-400">
                                                        &rarr; Induk: <strong class="text-slate-600">{{ $row['parent_name'] }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            @if($row['bank_name'] || $row['bank_account_number'])
                                                <div class="font-mono text-[11px] font-semibold text-slate-800 whitespace-nowrap select-all">
                                                    {{ $row['bank_name'] ?? '' }} {{ $row['bank_account_number'] ?? '' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500 flex flex-wrap items-center gap-1 mt-0.5">
                                                    @if($row['parent_name'] && str_starts_with((string) $row['bank_summary'], 'Rek. Induk:'))
                                                        <span class="px-1 rounded bg-violet-50 text-violet-700 font-semibold border border-violet-200">Rek. Induk</span>
                                                    @endif
                                                    @if($row['bank_account_owner'])
                                                        <span class="truncate max-w-[160px]" title="{{ $row['bank_account_owner'] }}">a.n. {{ $row['bank_account_owner'] }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">Belum ada rekening</span>
                                            @endif
                                        </td>
                                        <td class="px-2.5 py-2 text-right font-semibold text-slate-900 tabular-nums whitespace-nowrap">
                                            {{ $fmtHours($row['billable_hours']) }}
                                        </td>
                                        <td class="px-2.5 py-2 text-right text-slate-700 tabular-nums whitespace-nowrap">
                                            {{ $fmtHours($row['rejected_hours']) }}
                                        </td>
                                        <td class="px-2.5 py-2 text-right text-slate-600 tabular-nums whitespace-nowrap">
                                            {{ $fmtHours($row['not_checked_hours']) }}
                                        </td>
                                        <td class="px-2.5 py-2 text-right font-medium text-slate-800 tabular-nums whitespace-nowrap">
                                            {{ $fmtHours($row['total_hours']) }}
                                        </td>
                                        <td class="px-2.5 py-2 text-right text-slate-800 tabular-nums whitespace-nowrap">
                                            {{ number_format($row['recordings_count'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-bold text-slate-900 tabular-nums whitespace-nowrap">
                                            {{ $fmtIdr($row['amount_to_pay_idr']) }}
                                        </td>
                                        <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                            @if($row['is_paid'])
                                                <button
                                                    type="button"
                                                    @click="submitSingleStatus({{ $row['entry_id'] }}, 'unpaid')"
                                                    title="Klik untuk kembalikan ke Unpaid"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition-colors"
                                                >
                                                    Paid
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    @click="submitSingleStatus({{ $row['entry_id'] }}, 'paid')"
                                                    title="Klik untuk tandai Sudah Dibayar"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors"
                                                >
                                                    Unpaid
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-6 py-10 text-center text-slate-500">
                                            Tidak ada baris akun yang cocok dengan filter saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if($displayRows->isNotEmpty())
                                <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-slate-900">
                                    <tr class="divide-x divide-slate-200">
                                        <td colspan="4" class="px-3 py-2.5 text-right uppercase text-[11px] tracking-wider text-slate-600">
                                            Total ({{ number_format($filteredSummary['count'], 0, ',', '.') }} akun)
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['billable_hours']) }}</td>
                                        <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['rejected_hours']) }}</td>
                                        <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['not_checked_hours']) }}</td>
                                        <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['total_hours']) }}</td>
                                        <td class="px-2.5 py-2.5 text-right tabular-nums">{{ number_format($filteredSummary['recordings_count'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-2.5 text-right text-indigo-700 tabular-nums whitespace-nowrap">{{ $fmtIdr($filteredSummary['amount_to_pay_idr']) }}</td>
                                        <td class="px-2.5 py-2.5 text-center text-[11px] text-emerald-700 whitespace-nowrap">
                                            {{ $filteredSummary['paid_count'] }}/{{ $filteredSummary['count'] }} Paid
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    @if($paginatedRows && $paginatedRows->hasPages())
                        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                            {{ $paginatedRows->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Modal Upload Excel / CSV Cycle -->
        <div
            x-show="showImportModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            @click.self="showImportModal = false"
        >
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Upload Excel Payment Cycle</h3>
                        <p class="text-xs text-slate-500">Mendukung <code>.xlsx</code>, <code>.xls</code>, &amp; <code>.csv</code>. Akun belum terdaftar otomatis dibuat bertanda.</p>
                    </div>
                    <button type="button" @click="showImportModal = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('payments.import-csv') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <label
                        @dragover.prevent="dragActive = true"
                        @dragleave.prevent="dragActive = false"
                        @drop.prevent="handleDrop($event, $refs.modalFileInput)"
                        :class="dragActive ? 'border-indigo-500 bg-indigo-50/70' : 'border-slate-300 bg-slate-50 hover:border-indigo-400'"
                        class="block cursor-pointer border-2 border-dashed rounded-2xl p-5 text-center transition-all"
                    >
                        <input
                            x-ref="modalFileInput"
                            type="file"
                            name="excel_file"
                            accept=".xlsx,.xls,.csv,.txt"
                            required
                            @change="handleFilePicked($event.target.files)"
                            class="sr-only"
                        >
                        <p class="text-xs font-bold text-slate-800" x-text="selectedFileName ? 'File siap upload: ' + selectedFileName : 'Klik atau tarik file Excel (.xlsx / .xls / .csv) ke sini'"></p>
                        <p class="text-[11px] text-slate-500 mt-1">Hanya membaca 1 sheet target: <code>kamerakita-id</code> / <code>kamerakita-ai</code></p>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Cycle (opsional)</label>
                            <input type="text" name="cycle_name" placeholder="Otomatis dari header file" class="w-full rounded-xl border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sheet Excel (opsional)</label>
                            <input type="text" name="sheet_name" value="kamerakita-id" placeholder="kamerakita-id" class="w-full rounded-xl border-slate-300 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Rate Default (IDR/jam)</label>
                            <input type="number" name="worker_rate_idr" value="60000" min="1000" step="1000" class="w-full rounded-xl border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Revenue IDR (opsional)</label>
                            <input type="number" name="revenue_idr" placeholder="Otomatis baca baris Revenue" min="0" class="w-full rounded-xl border-slate-300 text-sm">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                            Upload &amp; Integrasikan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Atur Revenue / Kurs USD -->
        @if($activeCycle)
            <div
                x-show="showFinancialsModal"
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                @click.self="showFinancialsModal = false"
            >
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-900">Atur Finansial Cycle</h3>
                        <button type="button" @click="showFinancialsModal = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('payments.update-financials', $activeCycle) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Cycle</label>
                            <input type="text" name="name" value="{{ $activeCycle->name }}" required class="w-full rounded-xl border-slate-300 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Billable Hours Klien</label>
                                <input type="number" step="0.01" name="client_billable_hours" value="{{ $activeCycle->client_billable_hours }}" required class="w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Rate Klien (USD/jam)</label>
                                <input type="number" step="0.01" name="client_rate_usd" value="{{ $activeCycle->client_rate_usd }}" required class="w-full rounded-xl border-slate-300 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Total Revenue (IDR)</label>
                                <input type="number" name="revenue_idr" value="{{ $activeCycle->revenue_idr }}" min="0" class="w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kurs USD ke IDR (opsional)</label>
                                <input type="number" step="0.01" name="usd_to_idr_rate" value="{{ $activeCycle->usd_to_idr_rate }}" min="0" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Jika Revenue 0">
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button type="button" @click="showFinancialsModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
