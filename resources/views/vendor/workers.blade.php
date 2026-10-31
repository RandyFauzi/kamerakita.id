<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
            {{ __('Data Tim Worker') }}
        </h2>
    </x-slot>

    @php
        $workersList = collect($metrics['workers_data'] ?? [])->map(function($data) {
            $worker = $data['worker'];
            $m = $data['metrics'] ?? [];
            $email = $worker->user->email ?? $worker->email ?? '';
            $cleanWa = preg_replace('/[^0-9]/', '', (string)$worker->whatsapp_number);
            if (str_starts_with($cleanWa, '0')) {
                $cleanWa = '62' . substr($cleanWa, 1);
            }

            return [
                'id' => $worker->id,
                'mitra_id' => $worker->mitra_id ?? '-',
                'full_name' => $worker->full_name ?? '-',
                'email' => $email,
                'whatsapp_number' => $worker->whatsapp_number ?? '-',
                'whatsapp_clean' => $cleanWa,
                'bank_name' => $worker->bank_name ?? '',
                'bank_account_number' => $worker->bank_account_number ?? '',
                'bank_account_owner' => $worker->bank_account_owner ?? '',
                'all_time_minutes' => (int)($m['all_time_minutes'] ?? 0),
                'all_time_hours' => $m['all_time_hours_formatted'] ?? '0m',
                'paid_minutes' => (int)($m['paid_minutes'] ?? 0),
                'paid_hours' => $m['paid_hours_formatted'] ?? '0m',
                'pending_minutes' => (int)($m['pending_minutes'] ?? 0),
                'pending_hours' => $m['pending_hours_formatted'] ?? '0m',
                'pending_earnings' => (float)($m['pending_earnings'] ?? 0),
                'pending_earnings_formatted' => number_format((float)($m['pending_earnings'] ?? 0), 0, ',', '.'),
                'edit_url' => route('vendor.workers.update', $worker->id),
            ];
        })->values();
    @endphp

    <div class="py-8" 
         x-data="{
            showAddWorkerModal: {{ $errors->any() ? 'true' : 'false' }},
            showEditModal: false,
            showCopyModal: false,
            copyDropdown: false,
            copyFormat: 'email',
            copyData: '',
            copyFeedback: false,
            searchQuery: '',
            hoursFilter: 'all',
            bankFilter: 'all',
            sortBy: 'default',
            perPage: 25,
            currentPage: 1,
            editingWorker: null,
            editForm: {
                id: null,
                name: '',
                whatsapp_number: '',
                bank_name: '',
                bank_account_number: '',
                bank_account_owner: '',
                edit_url: ''
            },
            workers: @js($workersList),
            get filteredWorkers() {
                let list = this.workers || [];

                // 1. Search Query
                if (this.searchQuery && this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase().trim();
                    list = list.filter(w => {
                        return (w.full_name && w.full_name.toLowerCase().includes(q)) ||
                               (w.email && w.email.toLowerCase().includes(q)) ||
                               (w.mitra_id && w.mitra_id.toLowerCase().includes(q)) ||
                               (w.whatsapp_number && w.whatsapp_number.toLowerCase().includes(q)) ||
                               (w.bank_name && w.bank_name.toLowerCase().includes(q)) ||
                               (w.bank_account_number && w.bank_account_number.toLowerCase().includes(q));
                    });
                }

                // 2. Filter Jam Kerja
                if (this.hoursFilter === 'pending') {
                    list = list.filter(w => (w.pending_minutes || 0) > 0);
                } else if (this.hoursFilter === 'active') {
                    list = list.filter(w => (w.all_time_minutes || 0) > 0);
                } else if (this.hoursFilter === 'zero') {
                    list = list.filter(w => (w.all_time_minutes || 0) === 0);
                }

                // 3. Filter Rekening
                if (this.bankFilter === 'has_bank') {
                    list = list.filter(w => w.bank_account_number && w.bank_account_number.trim() !== '' && w.bank_account_number !== '-');
                } else if (this.bankFilter === 'no_bank') {
                    list = list.filter(w => !w.bank_account_number || w.bank_account_number.trim() === '' || w.bank_account_number === '-');
                }

                // 4. Sorting
                list = [...list];
                if (this.sortBy === 'name_asc') {
                    list.sort((a, b) => (a.full_name || '').localeCompare(b.full_name || ''));
                } else if (this.sortBy === 'name_desc') {
                    list.sort((a, b) => (b.full_name || '').localeCompare(a.full_name || ''));
                } else if (this.sortBy === 'all_time_desc') {
                    list.sort((a, b) => (b.all_time_minutes || 0) - (a.all_time_minutes || 0));
                } else if (this.sortBy === 'pending_desc') {
                    list.sort((a, b) => (b.pending_minutes || 0) - (a.pending_minutes || 0));
                } else if (this.sortBy === 'earnings_desc') {
                    list.sort((a, b) => (b.pending_earnings || 0) - (a.pending_earnings || 0));
                }

                return list;
            },
            get paginatedWorkers() {
                if (this.perPage === 'all') return this.filteredWorkers;
                const limit = parseInt(this.perPage) || 25;
                const start = (this.currentPage - 1) * limit;
                return this.filteredWorkers.slice(start, start + limit);
            },
            get totalPages() {
                if (this.perPage === 'all') return 1;
                const limit = parseInt(this.perPage) || 25;
                return Math.ceil(this.filteredWorkers.length / limit) || 1;
            },
            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },
            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },
            resetFilters() {
                this.searchQuery = '';
                this.hoursFilter = 'all';
                this.bankFilter = 'all';
                this.sortBy = 'default';
                this.currentPage = 1;
            },
            hasActiveFilters() {
                return (this.searchQuery && this.searchQuery.trim() !== '') || this.hoursFilter !== 'all' || this.bankFilter !== 'all' || this.sortBy !== 'default';
            },
            openEditModal(worker) {
                this.editingWorker = worker;
                this.editForm = {
                    id: worker.id,
                    name: worker.full_name || '',
                    whatsapp_number: worker.whatsapp_number && worker.whatsapp_number !== '-' ? worker.whatsapp_number : '',
                    bank_name: worker.bank_name || '',
                    bank_account_number: worker.bank_account_number || '',
                    bank_account_owner: worker.bank_account_owner || '',
                    edit_url: worker.edit_url
                };
                this.showEditModal = true;
            },
            openCopyModal(format = 'email') {
                this.copyFormat = format;
                this.copyFeedback = false;
                this.copyDropdown = false;
                this.renderCopyData();
                this.showCopyModal = true;
            },
            setCopyFormat(format) {
                this.copyFormat = format;
                this.copyFeedback = false;
                this.renderCopyData();
            },
            renderCopyData() {
                const currentList = this.filteredWorkers;
                if (!currentList || currentList.length === 0) {
                    this.copyData = 'Tidak ada data worker pada hasil filter ini.';
                    return;
                }

                if (this.copyFormat === 'email') {
                    const emails = currentList
                        .map(w => w.email ? w.email.trim() : '')
                        .filter(e => e && e !== '-' && !e.toLowerCase().includes('belum'));
                    const uniqueEmails = [...new Set(emails)];
                    this.copyData = uniqueEmails.length > 0 ? uniqueEmails.join(', ') : 'Tidak ada email valid.';
                } else if (this.copyFormat === 'whatsapp') {
                    const phones = currentList
                        .map(w => w.whatsapp_number ? w.whatsapp_number.trim() : '')
                        .filter(p => p && p !== '-');
                    this.copyData = phones.length > 0 ? phones.join('\n') : 'Tidak ada nomor WhatsApp.';
                } else if (this.copyFormat === 'bank_account') {
                    const accounts = currentList
                        .map(w => w.bank_account_number ? w.bank_account_number.trim() : '')
                        .filter(a => a && a !== '-' && a.toLowerCase() !== 'n/a');
                    this.copyData = accounts.length > 0 ? accounts.join('\n') : 'Tidak ada nomor rekening.';
                } else if (this.copyFormat === 'name') {
                    const names = currentList
                        .map(w => w.full_name ? w.full_name.trim() : '')
                        .filter(n => n && n !== '-');
                    this.copyData = names.length > 0 ? names.join('\n') : 'Tidak ada nama.';
                } else if (this.copyFormat === 'full') {
                    const full = currentList
                        .map(w => `${w.full_name} (${w.email || 'Tanpa email'})`);
                    this.copyData = full.join('\n');
                }
            },
            getCopyCount() {
                const currentList = this.filteredWorkers;
                if (!currentList || currentList.length === 0) return 0;
                if (this.copyFormat === 'email') {
                    return [...new Set(currentList.map(w => w.email ? w.email.trim() : '').filter(e => e && e !== '-' && !e.toLowerCase().includes('belum')))].length;
                } else if (this.copyFormat === 'whatsapp') {
                    return currentList.map(w => w.whatsapp_number ? w.whatsapp_number.trim() : '').filter(p => p && p !== '-').length;
                } else if (this.copyFormat === 'bank_account') {
                    return currentList.map(w => w.bank_account_number ? w.bank_account_number.trim() : '').filter(a => a && a !== '-' && a.toLowerCase() !== 'n/a').length;
                } else if (this.copyFormat === 'name') {
                    return currentList.map(w => w.full_name ? w.full_name.trim() : '').filter(n => n && n !== '-').length;
                }
                return currentList.length;
            },
            getCopyButtonLabel() {
                if (this.copyFormat === 'email') return 'Salin Email';
                if (this.copyFormat === 'whatsapp') return 'Salin No WA';
                if (this.copyFormat === 'bank_account') return 'Salin No Rek';
                if (this.copyFormat === 'name') return 'Salin Nama';
                return 'Salin Data';
            },
            copyToClipboard() {
                if (!this.copyData || this.copyData.startsWith('Tidak ada')) return;
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(this.copyData).then(() => {
                        this.copyFeedback = true;
                        setTimeout(() => { this.copyFeedback = false; }, 2000);
                    }).catch(() => {
                        this.fallbackCopy();
                    });
                } else {
                    this.fallbackCopy();
                }
            },
            fallbackCopy() {
                const textarea = document.createElement('textarea');
                textarea.value = this.copyData;
                textarea.style.position = 'fixed';
                textarea.style.left = '-999999px';
                textarea.style.top = '-999999px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    document.execCommand('copy');
                    this.copyFeedback = true;
                    setTimeout(() => { this.copyFeedback = false; }, 2000);
                } catch (err) {
                    console.error('Fallback copy failed', err);
                }
                document.body.removeChild(textarea);
            }
         }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-xs sm:text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <div class="font-bold text-xs sm:text-sm mb-1">Terjadi kesalahan input:</div>
                    <ul class="list-disc pl-5 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl sm:rounded-[32px] p-4 sm:p-6 border border-gray-150 shadow-sm">
                <!-- Header Title & Add Manual Button -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 pb-4 border-b border-gray-100 mb-4">
                    <div>
                        <span class="block text-sm sm:text-base font-bold text-gray-900">Manajemen Anggota Tim</span>
                        <span class="text-xs text-gray-400">Daftar worker di bawah naungan Anda</span>
                    </div>
                    <button @click="showAddWorkerModal = true" class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Anggota Manual
                    </button>
                </div>

                <!-- Referral Info Box -->
                <div class="mb-6 bg-gradient-to-br from-indigo-50/80 to-blue-50/80 border border-indigo-100 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-indigo-900 text-sm">Undang Worker secara Mandiri</h4>
                        <p class="text-xs text-indigo-700 mt-1">Bagikan link ini agar worker bisa mendaftar sendiri dan <b>langsung masuk</b> ke tim Anda.</p>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="text" readonly value="{{ route('register', ['ref' => $partner->referral_code]) }}" id="refLink" class="text-xs border-indigo-200 bg-white shadow-sm rounded-lg px-3 py-2 w-full sm:w-64 text-gray-600 focus:ring-0 focus:border-indigo-200" title="Link Pendaftaran Worker">
                        <button type="button" onclick="
                            const copyText = document.getElementById('refLink');
                            copyText.select();
                            copyText.setSelectionRange(0, 99999);
                            navigator.clipboard.writeText(copyText.value);
                            const btn = this;
                            const originalText = btn.innerText;
                            btn.innerText = 'Tersalin!';
                            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                            setTimeout(() => { 
                                btn.innerText = originalText; 
                                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                                btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                            }, 2000);
                        " class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition-colors shadow-sm whitespace-nowrap">
                            Salin Link
                        </button>
                    </div>
                </div>

                <!-- Toolbar Pencarian, Filter & Tombol Salin Kontak -->
                <div class="mb-5 space-y-3">
                    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                        <!-- Input Search -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   @input="currentPage = 1"
                                   placeholder="Cari nama, email, ID KMK, nomor WhatsApp, rekening..." 
                                   class="w-full pl-10 pr-9 py-2.5 border border-gray-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition-all shadow-xs">
                            <button type="button" 
                                    x-show="searchQuery.trim() !== ''" 
                                    @click="searchQuery = ''; currentPage = 1" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition"
                                    style="display: none;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Filter Dropdowns & Salin Button -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filter Jam Kerja -->
                            <div class="relative flex-1 sm:flex-none">
                                <select x-model="hoursFilter" 
                                        @change="currentPage = 1"
                                        class="w-full sm:w-auto py-2.5 pl-3 pr-8 border border-gray-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-xs font-medium text-gray-700">
                                    <option value="all">Semua Jam Kerja</option>
                                    <option value="pending">Ada Jam Pending ( > 0m )</option>
                                    <option value="active">Pernah Kerja ( All Time > 0 )</option>
                                    <option value="zero">Belum Ada Jam ( 0m )</option>
                                </select>
                            </div>

                            <!-- Filter Rekening Bank -->
                            <div class="relative flex-1 sm:flex-none">
                                <select x-model="bankFilter" 
                                        @change="currentPage = 1"
                                        class="w-full sm:w-auto py-2.5 pl-3 pr-8 border border-gray-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-xs font-medium text-gray-700">
                                    <option value="all">Semua Rekening</option>
                                    <option value="has_bank">Sudah Isi Rekening</option>
                                    <option value="no_bank">Belum Isi Rekening</option>
                                </select>
                            </div>

                            <!-- Urutkan / Sort -->
                            <div class="relative flex-1 sm:flex-none">
                                <select x-model="sortBy" 
                                        @change="currentPage = 1"
                                        class="w-full sm:w-auto py-2.5 pl-3 pr-8 border border-gray-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-xs font-medium text-gray-700">
                                    <option value="default">Urutan Default</option>
                                    <option value="name_asc">Nama (A - Z)</option>
                                    <option value="name_desc">Nama (Z - A)</option>
                                    <option value="all_time_desc">Jam All Time Tertinggi</option>
                                    <option value="pending_desc">Jam Pending Tertinggi</option>
                                    <option value="earnings_desc">Estimasi Pending Gaji Tertinggi</option>
                                </select>
                            </div>

                            <!-- Dropdown Salin Data Kontak (Split Button persis seperti di Admin) -->
                            <div class="relative flex-none" @click.away="copyDropdown = false">
                                <div class="inline-flex rounded-xl shadow-xs">
                                    <button type="button" 
                                            @click="openCopyModal('email')" 
                                            class="justify-center inline-flex items-center px-3.5 py-2.5 bg-white border border-gray-200 rounded-l-xl font-semibold text-xs sm:text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition gap-1.5" 
                                            title="Salin Kontak dari Hasil Filter">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Salin Kontak</span>
                                    </button>
                                    <button type="button" 
                                            @click="copyDropdown = !copyDropdown" 
                                            class="inline-flex items-center px-2.5 py-2.5 bg-white border border-l-0 border-gray-200 rounded-r-xl text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition" 
                                            title="Pilihan Format Salin">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                </div>

                                <!-- Dropdown Menu Pilihan Format -->
                                <div x-show="copyDropdown" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-56 rounded-xl shadow-xl bg-white border border-gray-100 z-30 py-1.5 divide-y divide-gray-100 text-xs"
                                     style="display: none;">
                                    <div class="py-1">
                                        <button type="button" @click="openCopyModal('email')" class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 hover:text-indigo-700 flex items-center gap-2 font-medium transition">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span>1. Salin Email (Dipisah Koma)</span>
                                        </button>
                                        <button type="button" @click="openCopyModal('whatsapp')" class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 hover:text-emerald-700 flex items-center gap-2 font-medium transition">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>2. Salin No WA (Berurutan)</span>
                                        </button>
                                        <button type="button" @click="openCopyModal('bank_account')" class="w-full text-left px-3.5 py-2 hover:bg-amber-50 hover:text-amber-700 flex items-center gap-2 font-medium transition">
                                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            <span>3. Salin No Rek (Hanya Norek)</span>
                                        </button>
                                        <button type="button" @click="openCopyModal('name')" class="w-full text-left px-3.5 py-2 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2 font-medium transition">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>4. Salin Nama (Berurutan)</span>
                                        </button>
                                    </div>
                                    <div class="py-1">
                                        <button type="button" @click="openCopyModal('full')" class="w-full text-left px-3.5 py-2 hover:bg-gray-50 text-gray-600 flex items-center gap-2 transition">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Format Lengkap (Nama + Email)</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Reset Filter -->
                            <button type="button" 
                                    x-show="hasActiveFilters()" 
                                    @click="resetFilters()" 
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition border border-rose-200 shadow-xs"
                                    style="display: none;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Reset Filter</span>
                            </button>
                        </div>
                    </div>

                    <!-- Summary Info Bar -->
                    <div class="flex items-center justify-between text-xs text-gray-500 pt-1 px-0.5">
                        <div class="flex items-center gap-2">
                            <span>
                                Menampilkan <span class="font-bold text-gray-900" x-text="filteredWorkers.length"></span> dari <span class="font-bold text-gray-900" x-text="workers.length"></span> worker
                            </span>
                            <span x-show="hasActiveFilters()" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100" style="display: none;">
                                Filter Aktif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Table Worker -->
                <div class="overflow-x-auto pb-4">
                    <table class="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr class="text-gray-500 border-b border-gray-100">
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">ID Worker</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Nama Worker</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">All Time</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Paid</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Pending</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Estimasi Pending Gaji</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">WhatsApp</th>
                                <th class="py-3 text-center font-semibold whitespace-nowrap px-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="item in paginatedWorkers" :key="item.id">
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3.5 px-2 font-bold text-indigo-600 whitespace-nowrap" x-text="item.mitra_id"></td>
                                    <td class="py-3.5 px-2 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900" x-text="item.full_name"></div>
                                        <div class="text-xs text-gray-500 flex items-center gap-1.5">
                                            <span x-text="item.email || '-'"></span>
                                            <template x-if="item.bank_account_number">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200" :title="'Rek: ' + item.bank_name + ' ' + item.bank_account_number">
                                                    Bank Terisi
                                                </span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-2 text-gray-600 whitespace-nowrap" x-text="item.all_time_hours"></td>
                                    <td class="py-3.5 px-2 text-emerald-800 whitespace-nowrap font-medium" x-text="item.paid_hours"></td>
                                    <td class="py-3.5 px-2 text-amber-800 font-bold whitespace-nowrap" x-text="item.pending_hours"></td>
                                    <td class="py-3.5 px-2 font-extrabold text-slate-800 whitespace-nowrap" x-text="'Rp' + item.pending_earnings_formatted"></td>
                                    <td class="py-3.5 px-2 text-indigo-600 font-medium whitespace-nowrap">
                                        <a :href="'https://wa.me/' + item.whatsapp_clean" target="_blank" class="hover:underline inline-flex items-center gap-1">
                                            <span x-text="item.whatsapp_number"></span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-2 text-center whitespace-nowrap">
                                        <button type="button" @click="openEditModal(item)" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State saat filter tidak menemukan data -->
                            <tr x-show="workers.length > 0 && filteredWorkers.length === 0" style="display: none;">
                                <td colspan="8" class="py-12 text-center text-gray-500 text-xs">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="p-3 bg-gray-50 text-gray-400 rounded-full">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-gray-700">Tidak ada worker yang sesuai dengan pencarian atau filter yang dipilih.</p>
                                        <button type="button" @click="resetFilters()" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold hover:underline">
                                            Reset Filter & Pencarian
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State jika memang belum punya worker -->
                            <tr x-show="workers.length === 0" style="display: none;">
                                <td colspan="8" class="py-10 text-center text-gray-400 text-xs">
                                    Belum ada Worker terdaftar di bawah naungan Anda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 text-xs text-gray-500" x-show="filteredWorkers.length > 0">
                    <div class="flex items-center gap-2">
                        <span>Tampilkan:</span>
                        <select x-model="perPage" @change="currentPage = 1" class="text-xs border border-gray-200 rounded-lg py-1 px-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="all">Semua</option>
                        </select>
                        <span>per halaman</span>
                    </div>
                    
                    <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                        <button type="button" 
                                @click="prevPage()" 
                                :disabled="currentPage === 1" 
                                class="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                            Sebelumnya
                        </button>
                        <span class="px-2 font-medium text-gray-700">
                            Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>
                        </span>
                        <button type="button" 
                                @click="nextPage()" 
                                :disabled="currentPage === totalPages" 
                                class="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                            Selanjutnya
                        </button>
                    </div>
                </div>

                <!-- Modal Tambah Worker Manual -->
                <div x-show="showAddWorkerModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showAddWorkerModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showAddWorkerModal = false" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="showAddWorkerModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl sm:rounded-[28px] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                            <form action="{{ route('vendor.workers.store') }}" method="POST">
                                @csrf
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                                                <h3 class="text-base sm:text-lg font-bold text-gray-900" id="modal-title">Daftarkan Worker Baru</h3>
                                                <button type="button" @click="showAddWorkerModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="space-y-4 text-xs sm:text-sm">
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                                    <input type="text" name="name" required class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Username Email <span class="text-red-500">*</span></label>
                                                    <div class="flex rounded-xl shadow-xs overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-blue-500">
                                                        <input type="text" name="username" required class="flex-1 block w-full min-w-0 border-0 text-xs sm:text-sm focus:ring-0" placeholder="namapekerja">
                                                        <span class="inline-flex items-center px-3 bg-gray-50 text-gray-500 border-l border-gray-200 text-xs sm:text-sm font-medium">
                                                            @kamerakitaid.site
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                                    <input type="text" name="whatsapp_number" required class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm" placeholder="08123456789">
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                                    <input type="password" name="password" required class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                                                    <input type="password" name="password_confirmation" required class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-xl shadow-xs px-4 py-2.5 bg-blue-600 text-xs sm:text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none transition">
                                        Daftarkan
                                    </button>
                                    <button type="button" @click="showAddWorkerModal = false" class="mt-2 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-xs px-4 py-2.5 bg-white text-xs sm:text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Worker Dinamis -->
                <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-edit-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showEditModal = false" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl sm:rounded-[28px] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                            <form :action="editForm.edit_url" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                                                <h3 class="text-base sm:text-lg font-bold text-gray-900" id="modal-edit-title">
                                                    Edit Worker: <span class="text-indigo-600" x-text="editForm.name"></span>
                                                </h3>
                                                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="space-y-4 text-xs sm:text-sm">
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                                    <input type="text" name="name" x-model="editForm.name" required class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                                    <input type="text" name="whatsapp_number" x-model="editForm.whatsapp_number" required class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>
                                                
                                                <hr class="my-3 border-gray-100">
                                                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider text-indigo-700">Informasi Pembayaran (Opsional)</h4>
                                                
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Nama Bank / e-Wallet</label>
                                                    <input type="text" name="bank_name" x-model="editForm.bank_name" class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm" placeholder="Contoh: BCA, Mandiri, DANA, dll">
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Nomor Rekening</label>
                                                    <input type="text" name="bank_account_number" x-model="editForm.bank_account_number" class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Atas Nama Rekening</label>
                                                    <input type="text" name="bank_account_owner" x-model="editForm.bank_account_owner" class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>

                                                <hr class="my-3 border-gray-100">
                                                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider text-indigo-700">Keamanan (Opsional)</h4>
                                                
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Password Baru (Opsional)</label>
                                                    <input type="password" name="password" class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                </div>
                                                <div>
                                                    <label class="block font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                                                    <input type="password" name="password_confirmation" class="block w-full border-gray-200 rounded-xl shadow-xs focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-xl shadow-xs px-4 py-2.5 bg-blue-600 text-xs sm:text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none transition">
                                        Simpan Perubahan
                                    </button>
                                    <button type="button" @click="showEditModal = false" class="mt-2 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-xs px-4 py-2.5 bg-white text-xs sm:text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Salin Data Kontak (Persis seperti di Admin) -->
                <div x-show="showCopyModal" 
                     style="display: none" 
                     class="fixed inset-0 z-[100] overflow-y-auto" 
                     aria-labelledby="copy-modal-title" 
                     role="dialog" 
                     aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showCopyModal" 
                             x-transition:enter="ease-out duration-300" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100" 
                             x-transition:leave="ease-in duration-200" 
                             x-transition:leave-start="opacity-100" 
                             x-transition:leave-end="opacity-0" 
                             class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" 
                             @click="showCopyModal = false" 
                             aria-hidden="true"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div x-show="showCopyModal" 
                             x-transition:enter="ease-out duration-300" 
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                             x-transition:leave="ease-in duration-200" 
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                             class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl sm:rounded-[28px] shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6 w-full relative border border-gray-100">
                             
                            <div class="absolute top-0 right-0 pt-5 pr-5">
                                <button @click="showCopyModal = false" type="button" class="text-gray-400 bg-white rounded-lg hover:text-gray-600 focus:outline-none p-1 hover:bg-gray-100 transition">
                                    <span class="sr-only">Tutup</span>
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 leading-tight">
                                                Salin Data Kontak Tim Worker
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                Data diambil otomatis sesuai hasil pencarian dan filter saat ini.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- 4 Pilihan Format Utama Sesuai Request -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 my-3.5">
                                        <!-- 1. Salin Email -->
                                        <button type="button" 
                                                @click="setCopyFormat('email')" 
                                                :class="copyFormat === 'email' ? 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-500 shadow-xs' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 font-medium border border-gray-200'"
                                                class="px-2.5 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all text-center">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span>1. Salin Email</span>
                                        </button>

                                        <!-- 2. Salin No WA -->
                                        <button type="button" 
                                                @click="setCopyFormat('whatsapp')" 
                                                :class="copyFormat === 'whatsapp' ? 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-500 shadow-xs' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 font-medium border border-gray-200'"
                                                class="px-2.5 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all text-center">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>2. Salin No WA</span>
                                        </button>

                                        <!-- 3. Salin No Rek -->
                                        <button type="button" 
                                                @click="setCopyFormat('bank_account')" 
                                                :class="copyFormat === 'bank_account' ? 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-500 shadow-xs' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 font-medium border border-gray-200'"
                                                class="px-2.5 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all text-center">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            <span>3. Salin No Rek</span>
                                        </button>

                                        <!-- 4. Salin Nama -->
                                        <button type="button" 
                                                @click="setCopyFormat('name')" 
                                                :class="copyFormat === 'name' ? 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-500 shadow-xs' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 font-medium border border-gray-200'"
                                                class="px-2.5 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all text-center">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>4. Salin Nama</span>
                                        </button>
                                    </div>

                                    <!-- Keterangan format aktif & counter -->
                                    <div class="flex items-center justify-between text-xs px-3 py-2 rounded-xl mb-2.5 font-medium border"
                                         :class="{
                                            'bg-indigo-50/80 border-indigo-100 text-indigo-900': copyFormat === 'email',
                                            'bg-emerald-50/80 border-emerald-100 text-emerald-900': copyFormat === 'whatsapp',
                                            'bg-amber-50/80 border-amber-100 text-amber-900': copyFormat === 'bank_account',
                                            'bg-blue-50/80 border-blue-100 text-blue-900': copyFormat === 'name',
                                            'bg-gray-50 border-gray-200 text-gray-800': copyFormat === 'full'
                                         }">
                                        <div class="flex items-center gap-2 overflow-hidden text-ellipsis whitespace-nowrap">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0" 
                                                  :class="{
                                                    'bg-indigo-500': copyFormat === 'email',
                                                    'bg-emerald-500': copyFormat === 'whatsapp',
                                                    'bg-amber-500': copyFormat === 'bank_account',
                                                    'bg-blue-500': copyFormat === 'name',
                                                    'bg-gray-500': copyFormat === 'full'
                                                  }"></span>
                                            <span x-show="copyFormat === 'email'">Format: <strong>Email dipisah koma</strong> (<code>randy@.., riswan@..</code>)</span>
                                            <span x-show="copyFormat === 'whatsapp'">Format: <strong>Nomor WhatsApp berurutan</strong> (1 nomor per baris)</span>
                                            <span x-show="copyFormat === 'bank_account'">Format: <strong>Hanya nomor rekening berurutan</strong> (1 nomor per baris)</span>
                                            <span x-show="copyFormat === 'name'">Format: <strong>Hanya nama pekerja berurutan</strong> (1 nama per baris)</span>
                                            <span x-show="copyFormat === 'full'">Format: <strong>Nama (Email)</strong> per baris</span>
                                        </div>
                                        <div class="font-bold flex items-center gap-1 flex-shrink-0 pl-2">
                                            <span x-text="getCopyCount()"></span> data
                                        </div>
                                    </div>

                                    <!-- Textarea Preview -->
                                    <div class="relative">
                                        <textarea x-model="copyData" 
                                                  class="w-full h-56 p-3.5 border-gray-200 rounded-xl shadow-xs text-xs sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono bg-gray-50/60 leading-relaxed" 
                                                  placeholder="Memuat data..." 
                                                  readonly></textarea>
                                        
                                        <div class="mt-1.5 flex justify-between items-center text-[11px] text-gray-400">
                                            <span>*Teks di atas dapat Anda blok & salin secara manual jika dibutuhkan.</span>
                                            <button type="button" 
                                                    @click="setCopyFormat(copyFormat === 'full' ? 'email' : 'full')" 
                                                    class="text-indigo-600 hover:text-indigo-800 underline font-medium">
                                                <span x-show="copyFormat !== 'full'">Format Lengkap (Nama + Email)</span>
                                                <span x-show="copyFormat === 'full'">Kembali ke Pilihan Format</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Modal -->
                            <div class="mt-5 pt-3 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                                <button type="button" 
                                        @click="showCopyModal = false" 
                                        class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-300 shadow-xs px-4 py-2.5 bg-white text-xs sm:text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none transition">
                                    Tutup
                                </button>
                                <button type="button" 
                                        @click="copyToClipboard()" 
                                        :disabled="!copyData || copyData.startsWith('Tidak ada')" 
                                        :class="copyFeedback ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white'"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl shadow-sm px-5 py-2.5 text-xs sm:text-sm font-bold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-50">
                                    <template x-if="!copyFeedback">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 002-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                            <span x-text="getCopyButtonLabel()">Salin</span>
                                        </span>
                                    </template>
                                    <template x-if="copyFeedback">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Tersalin ke Clipboard!</span>
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
