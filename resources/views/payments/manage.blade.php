<x-app-layout>
    <x-slot name="title">Pembayaran</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight">
                Pembayaran
            </h2>
            @if($activeCycle)
                <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ $activeCycle->name }}
                </span>
            @endif
        </div>
    </x-slot>

    @php
        $fmtHours = fn ($v) => number_format((float) $v, 2, ',', '.');
        $fmtIdr = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    @endphp

    <div
        x-data="{
            showImportModal: false,
            showFinancialsModal: false,
            showRatesModal: false,
            rateModalScope: 'parent',
            rateModalParentId: @js($filters['parent_id'] ?? ''),
            rateModalEntryId: null,
            rateModalLabel: '',
            rateModalValue: {{ $activeCycle?->worker_rate_idr ?? 60000 }},
            dragActive: false,
            selectedFileName: '',
            selectedIds: [],
            allPageIds: @json($displayRows->pluck('entry_id')->filter()->values()->all()),
            cycleId: @js($activeCycle?->id),
            filterState: {
                search: @js($filters['search']),
                account_type: @js($filters['account_type']),
                parent_id: @js($filters['parent_id']),
                pay_status: @js($filters['pay_status']),
                sort: @js($filters['sort']),
                dir: @js($filters['dir']),
                per_page: @js($filters['per_page']),
                page: 1,
            },
            isFiltering: false,
            hasActiveFilter: @js($hasActiveFilter),
            filteredCount: {{ $filteredSummary['count'] }},
            searchTimer: null,
            fetchSeq: 0,
            hasActiveFilters() {
                return (this.filterState.search || '').trim() !== ''
                    || this.filterState.account_type !== 'all'
                    || this.filterState.parent_id !== ''
                    || this.filterState.pay_status !== 'all';
            },
            buildFilterParams(extra = {}) {
                const params = new URLSearchParams();
                if (this.cycleId) params.set('cycle_id', this.cycleId);
                if (this.filterState.search.trim() !== '') params.set('search', this.filterState.search.trim());
                if (this.filterState.account_type !== 'all') params.set('account_type', this.filterState.account_type);
                if (this.filterState.parent_id !== '') params.set('parent_id', this.filterState.parent_id);
                if (this.filterState.pay_status !== 'all') params.set('pay_status', this.filterState.pay_status);
                if (this.filterState.sort !== 'default') params.set('sort', this.filterState.sort);
                if (this.filterState.sort !== 'default' && this.filterState.dir !== 'asc') params.set('dir', this.filterState.dir);
                if (this.filterState.per_page !== '100') params.set('per_page', this.filterState.per_page);
                if (this.filterState.page > 1) params.set('page', this.filterState.page);
                Object.entries(extra).forEach(([k, v]) => params.set(k, v));
                return params;
            },
            exportUrl(variant) {
                return '{{ route('payments.export-csv') }}?' + this.buildFilterParams({ variant }).toString();
            },
            onSearchInput() {
                clearTimeout(this.searchTimer);
                this.filterState.page = 1;
                this.searchTimer = setTimeout(() => this.applyFilter(), 180);
            },
            setFilter(key, value) {
                if (this.filterState[key] === value && key !== 'page') return;
                this.filterState[key] = value;
                if (key !== 'page') this.filterState.page = 1;
                this.applyFilter();
            },
            toggleSort(col) {
                if (this.filterState.sort === col) {
                    this.filterState.dir = this.filterState.dir === 'desc' ? 'asc' : 'desc';
                } else {
                    this.filterState.sort = col;
                    this.filterState.dir = col === 'worker_email' ? 'asc' : 'desc';
                }
                this.filterState.page = 1;
                this.applyFilter();
            },
            resetFilters() {
                this.filterState.search = '';
                this.filterState.account_type = 'all';
                this.filterState.parent_id = '';
                this.filterState.pay_status = 'all';
                this.filterState.page = 1;
                this.applyFilter();
            },
            handlePaginationClick(event) {
                const link = event.target.closest('a[href]');
                if (!link) return;
                event.preventDefault();
                try {
                    const url = new URL(link.href);
                    const p = parseInt(url.searchParams.get('page') || '1', 10);
                    this.filterState.page = Number.isNaN(p) ? 1 : p;
                    this.applyFilter();
                } catch (e) {}
            },
            async applyFilter() {
                if (!this.cycleId) return;
                const seq = ++this.fetchSeq;
                this.isFiltering = true;
                const cleanParams = this.buildFilterParams();
                const partialParams = this.buildFilterParams({ partial: '1' });

                const browserUrl = '{{ route('payments.manage') }}' + (cleanParams.toString() ? '?' + cleanParams.toString() : '');
                window.history.replaceState({}, '', browserUrl);

                try {
                    const res = await fetch('{{ route('payments.manage') }}?' + partialParams.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('Network response failed');
                    const data = await res.json();
                    if (seq !== this.fetchSeq) return;
                    if (this.$refs.resultsContainer) {
                        this.$refs.resultsContainer.innerHTML = data.html;
                    }
                    this.allPageIds = data.allPageIds || [];
                    this.selectedIds = [];
                    this.hasActiveFilter = !!data.hasActiveFilter;
                    this.filteredCount = data.filteredCount ?? 0;
                } catch (err) {
                    window.location.href = browserUrl;
                } finally {
                    if (seq === this.fetchSeq) this.isFiltering = false;
                }
            },
            openTeamRateModal(parentId = '') {
                this.rateModalScope = 'parent';
                if (parentId) {
                    this.rateModalParentId = parentId;
                } else if (this.filterState.parent_id) {
                    this.rateModalParentId = this.filterState.parent_id;
                }
                this.rateModalEntryId = null;
                this.rateModalLabel = '';
                this.showRatesModal = true;
            },
            openSingleRateModal(entryId, email, currentRate) {
                this.rateModalScope = 'entries';
                this.rateModalEntryId = entryId;
                this.rateModalLabel = email;
                this.rateModalValue = currentRate;
                this.showRatesModal = true;
            },
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
        @open-rates-modal.window="openTeamRateModal()"
        class="py-5 sm:py-6"
    >
        <div class="max-w-[1600px] mx-auto px-3 sm:px-6 lg:px-8 space-y-4">
            <!-- Top Action Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-600">
                    @if($activeCycle)
                        <span class="font-semibold text-slate-800">{{ $activeCycle->name }}</span>
                        &middot; Sheet: <span class="font-semibold text-indigo-700">{{ $activeCycle->source_label }}</span>
                        @if($activeCycle->frozen_on)
                            &middot; Frozen {{ $activeCycle->frozen_on->format('d M Y') }}
                        @endif
                    @else
                        <span>Upload file Excel / CSV pembayaran untuk mendeteksi &amp; mengintegrasikan akun otomatis</span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="showImportModal = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Upload Excel Cycle
                    </button>

                    @if($activeCycle)
                        <button
                            type="button"
                            @click="openTeamRateModal()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-amber-50 border border-amber-300 text-amber-800 hover:bg-amber-100 shadow-sm transition-colors"
                        >
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Atur Rate Mitra / Tim
                        </button>

                        <button
                            type="button"
                            @click="showFinancialsModal = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors"
                        >
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Atur Revenue / Kurs
                        </button>

                        <a
                            :href="exportUrl('sheet')"
                            href="{{ route('payments.export-csv', array_merge(request()->query(), ['variant' => 'sheet'])) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors"
                        >
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export Sheet
                        </a>

                        <a
                            :href="exportUrl('bank')"
                            href="{{ route('payments.export-csv', array_merge(request()->query(), ['variant' => 'bank'])) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 shadow-sm transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Export Rekening
                        </a>
                    @endif
                </div>
            </div>
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
                        <span>Rate Default: <strong class="text-slate-900">{{ $fmtIdr($activeCycle->worker_rate_idr) }}/jam</strong></span>
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

                <!-- 3 Summary Cards Finansial (Total Bayar, Revenue, Omzet) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                        <button
                            type="button"
                            @click="setFilter('account_type', 'auto_created')"
                            class="px-3 py-1 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition-colors"
                        >
                            Lihat {{ $cycleTotals['auto_created_count'] }} Akun Baru
                        </button>
                    </div>
                @endif

                <!-- Filter Bar Modern (Live Search Otomatis + Filter Pills 1-Klik + Kontrol Ringkas) -->
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-sm space-y-3.5">
                    <!-- Baris 1: Live Search + Pilih Mitra Induk + Status Bayar + Baris -->
                    <div class="flex flex-col xl:flex-row xl:items-center gap-3">
                        <!-- Live Search Instant -->
                        <div class="relative flex-1 min-w-[240px]">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                type="text"
                                x-model="filterState.search"
                                @input="onSearchInput()"
                                @keydown.enter.prevent="clearTimeout(searchTimer); applyFilter()"
                                placeholder="Cari cepat email, nama, ID KMK, mitra induk, atau rekening (otomatis filter)..."
                                class="w-full rounded-xl border-slate-200 bg-slate-50/70 focus:bg-white text-sm pl-10 pr-20 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                            >
                            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                <span x-show="isFiltering" style="display: none;" class="inline-flex items-center text-[11px] font-semibold text-indigo-600">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </span>
                                <button
                                    type="button"
                                    x-show="(filterState.search || '').length > 0"
                                    style="display: none;"
                                    @click="filterState.search = ''; filterState.page = 1; applyFilter()"
                                    class="px-2 py-0.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-[11px] font-bold transition-colors"
                                    title="Hapus pencarian"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>

                        <!-- Mitra / Akun Induk Selector -->
                        <div class="w-full sm:w-auto xl:min-w-[260px]">
                            <select
                                x-model="filterState.parent_id"
                                @change="filterState.page = 1; applyFilter()"
                                class="w-full rounded-xl border-slate-200 bg-slate-50/70 focus:bg-white text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 font-medium text-slate-700"
                            >
                                <option value="">Semua Mitra / Akun Induk</option>
                                @foreach($parentOptions as $parentOpt)
                                    <option value="{{ $parentOpt['id'] }}">
                                        {{ $parentOpt['name'] }} {{ $parentOpt['mitra_id'] ? "({$parentOpt['mitra_id']})" : '' }} &middot; {{ $parentOpt['count'] }} akun
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Segmented Status Bayar -->
                        <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 border border-slate-200/80 self-start xl:self-auto shrink-0">
                            <button
                                type="button"
                                @click="setFilter('pay_status', 'all')"
                                :class="filterState.pay_status === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap"
                            >
                                Semua
                            </button>
                            <button
                                type="button"
                                @click="setFilter('pay_status', 'unpaid')"
                                :class="filterState.pay_status === 'unpaid' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap"
                            >
                                Unpaid ({{ $categoryCounts['unpaid'] }})
                            </button>
                            <button
                                type="button"
                                @click="setFilter('pay_status', 'paid')"
                                :class="filterState.pay_status === 'paid' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap"
                            >
                                Paid ({{ $categoryCounts['paid'] }})
                            </button>
                        </div>

                        <!-- Segmented Baris per Halaman -->
                        <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 border border-slate-200/80 self-start xl:self-auto shrink-0">
                            @foreach(['50' => '50', '100' => '100', '250' => '250', 'all' => 'Semua'] as $perVal => $perLabel)
                                <button
                                    type="button"
                                    @click="setFilter('per_page', '{{ $perVal }}')"
                                    :class="filterState.per_page === '{{ $perVal }}' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="px-2.5 py-1.5 rounded-lg text-xs transition-all whitespace-nowrap"
                                >
                                    {{ $perLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Baris 2: Quick Category Filter Pills (1-Klik Langsung Saring) -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                        <div class="flex flex-wrap items-center gap-1.5">
                            @php
                                $pills = [
                                    ['key' => 'all', 'label' => 'Semua Akun', 'count' => $categoryCounts['all']],
                                    ['key' => 'direct_worker', 'label' => 'Worker Langsung', 'count' => $categoryCounts['direct_worker']],
                                    ['key' => 'under_mitra', 'label' => 'Under Mitra / Sub', 'count' => $categoryCounts['under_mitra']],
                                    ['key' => 'mitra', 'label' => 'Mitra & Timnya', 'count' => $categoryCounts['mitra']],
                                    ['key' => 'main_account', 'label' => 'Main Account', 'count' => $categoryCounts['main_account']],
                                    ['key' => 'rekruter', 'label' => 'Rekruter', 'count' => $categoryCounts['rekruter']],
                                ];
                            @endphp

                            @foreach($pills as $pill)
                                <button
                                    type="button"
                                    @click="setFilter('account_type', '{{ $pill['key'] }}')"
                                    :class="filterState.account_type === '{{ $pill['key'] }}'
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                                        : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100 hover:text-slate-900'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-semibold transition-all"
                                >
                                    <span>{{ $pill['label'] }}</span>
                                    <span
                                        :class="filterState.account_type === '{{ $pill['key'] }}' ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-700'"
                                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold tabular-nums"
                                    >
                                        {{ $pill['count'] }}
                                    </span>
                                </button>
                            @endforeach

                            @if($categoryCounts['auto_created'] > 0)
                                <button
                                    type="button"
                                    @click="setFilter('account_type', 'auto_created')"
                                    :class="filterState.account_type === 'auto_created'
                                        ? 'bg-amber-600 text-white border-amber-600 shadow-sm'
                                        : 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-semibold transition-all"
                                >
                                    <span>Akun Baru DB</span>
                                    <span
                                        :class="filterState.account_type === 'auto_created' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900'"
                                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold tabular-nums"
                                    >
                                        {{ $categoryCounts['auto_created'] }}
                                    </span>
                                </button>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                x-show="hasActiveFilters()"
                                style="display: none;"
                                @click="resetFilters()"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

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
                        <form action="{{ route('payments.update-rates', $activeCycle) }}" method="POST" class="inline-flex items-center gap-1.5 bg-slate-800 px-2.5 py-1 rounded-xl border border-slate-700">
                            @csrf
                            <input type="hidden" name="scope" value="entries">
                            <template x-for="id in selectedIds" :key="'r-' + id">
                                <input type="hidden" name="entry_ids[]" :value="id">
                            </template>
                            <span class="text-[11px] text-slate-300 font-semibold">Rate/jam:</span>
                            <input type="number" name="rate_idr" value="{{ $activeCycle->worker_rate_idr }}" min="1000" step="1000" required class="w-24 rounded-lg border-slate-600 bg-slate-900 text-white text-xs py-1 px-2">
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-xs font-bold text-white transition-colors">
                                Ubah Rate
                            </button>
                        </form>
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

                <!-- Container Hasil Filter & Tabel (Diperbarui Real-time Tanpa Reload Halaman) -->
                <div x-ref="resultsContainer" :class="isFiltering ? 'opacity-60 pointer-events-none transition-opacity' : 'transition-opacity'">
                    @include('payments.partials.results')
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

        @if($activeCycle)
            <!-- Modal Atur Revenue / Kurs USD -->
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

            <!-- Modal Atur Rate per Mitra / Akun -->
            <div
                x-show="showRatesModal"
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                @click.self="showRatesModal = false"
            >
                <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" x-text="rateModalScope === 'entries' ? 'Ubah Tarif Akun' : 'Atur Rate Mitra / Tim'"></h3>
                            <p class="text-xs text-slate-500" x-text="rateModalScope === 'entries' ? rateModalLabel : 'Otomatis menghitung ulang Yang Perlu dibayarkan & Total Bayar'"></p>
                        </div>
                        <button type="button" @click="showRatesModal = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('payments.update-rates', $activeCycle) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="scope" :value="rateModalScope">
                        <template x-if="rateModalScope === 'entries' && rateModalEntryId">
                            <input type="hidden" name="entry_ids[]" :value="rateModalEntryId">
                        </template>

                        <div x-show="rateModalScope === 'parent'">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Mitra / Main Account Induk</label>
                            <select
                                name="parent_id"
                                x-model="rateModalParentId"
                                :required="rateModalScope === 'parent'"
                                class="w-full rounded-xl border-slate-300 text-sm"
                            >
                                <option value="">-- Pilih Mitra / Tim --</option>
                                @foreach($parentOptions as $parentOpt)
                                    <option value="{{ $parentOpt['id'] }}">
                                        {{ $parentOpt['name'] }} {{ $parentOpt['mitra_id'] ? "({$parentOpt['mitra_id']})" : '' }} ({{ $parentOpt['count'] }} akun)
                                    </option>
                                @endforeach
                            </select>
                            @if($parentOptions->isEmpty())
                                <p class="mt-1 text-[11px] text-amber-700">Belum ada akun terhubung ke Mitra/Main Account pada cycle ini.</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tarif Baru per Jam (IDR)</label>
                            <input
                                type="number"
                                name="rate_idr"
                                x-model.number="rateModalValue"
                                min="1000"
                                step="1000"
                                required
                                class="w-full rounded-xl border-slate-300 text-sm font-bold"
                            >
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach([40000, 50000, 55000, 60000, 70000, 80000] as $preset)
                                    <button
                                        type="button"
                                        @click="rateModalValue = {{ $preset }}"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border border-slate-200 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 transition-colors"
                                    >
                                        {{ $fmtIdr($preset) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button type="button" @click="showRatesModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 shadow-sm">
                                Terapkan Rate Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
