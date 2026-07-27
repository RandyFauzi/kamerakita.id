<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Manajemen Data Kemitraan (Mitra & Worker)') }}
            </h2>
            <a href="{{ route('partners.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-650 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:from-blue-700 hover:to-indigo-700 transition shadow-md shadow-indigo-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Registrasi Mitra / Worker Baru
            </a>
        </div>
    </x-slot>

    <div
        class="py-8"
        x-data="{
            showDeleteModal: false,
            deleteTarget: {},
            deleting: false,
            showCopyModal: false,
            copyData: '',
            fetchingContacts: false,
            openDeleteModal(target) {
                this.deleteTarget = target;
                this.deleting = false;
                this.showDeleteModal = true;
            },
            closeDeleteModal() {
                this.showDeleteModal = false;
                this.deleteTarget = {};
                this.deleting = false;
            },
            openCopyModal() {
                this.showCopyModal = true;
                this.fetchingContacts = true;
                this.copyData = 'Sedang memuat data kontak...';
                
                const search = document.getElementById('search').value || '';
                const role = document.getElementById('role').value || '';
                const status = document.getElementById('status').value || '';
                const group = document.getElementById('group').value || '';
                
                const params = new URLSearchParams({ search, role, status, group });
                
                fetch('{{ route('partners.export-contacts') }}?' + params.toString())
                    .then(res => res.text())
                    .then(text => {
                        this.copyData = text || 'Tidak ada data.';
                        this.fetchingContacts = false;
                    })
                    .catch(err => {
                        this.copyData = 'Gagal memuat data kontak.';
                        this.fetchingContacts = false;
                    });
            },
            closeCopyModal() {
                this.showCopyModal = false;
            },
            copyToClipboard() {
                navigator.clipboard.writeText(this.copyData).then(() => {
                    alert('Data berhasil disalin!');
                }).catch(err => {
                    alert('Gagal menyalin, silakan block text dan copy manual.');
                });
            }
        }"
        @keydown.escape.window="closeDeleteModal(); closeCopyModal()"
    >
        <div class="w-full max-w-none mx-auto space-y-6">
            
            @if(session('success'))
                <div class="p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-100 flex items-center gap-2 animate-bounce" role="alert">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <section class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6 lg:gap-4" aria-label="Ringkasan akun kemitraan">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Total Pengguna</span>
                            <strong class="mt-2 block text-2xl font-black text-slate-900 sm:text-3xl">{{ number_format($summary['total_users'], 0, ',', '.') }}</strong>
                            <span class="mt-1 block text-xs text-gray-400">Seluruh akun kemitraan</span>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H2v-2a4 4 0 014-4h3m4 6v-2a4 4 0 00-4-4m4 6h4m-8-8a4 4 0 100-8 4 4 0 000 8zm8 0a3 3 0 100-6"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Worker/Kontributor</span>
                            <strong class="mt-2 block text-2xl font-black text-blue-700 sm:text-3xl">{{ number_format($summary['total_workers'], 0, ',', '.') }}</strong>
                            <span class="mt-1 block text-xs text-gray-400">Akun pelaksana pekerjaan</span>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3 3L22 4M16 21H5a2 2 0 01-2-2V8a2 2 0 012-2h8"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Total Mitra</span>
                            <strong class="mt-2 block text-2xl font-black text-emerald-700 sm:text-3xl">{{ number_format($summary['total_mitra'], 0, ',', '.') }}</strong>
                            <span class="mt-1 block text-xs text-gray-400">Akun mitra koordinator</span>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a4 4 0 018 0v2m-9 0h10a2 2 0 012 2v9a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2zm-2 5h14"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <a href="{{ route('partners.index', array_merge(request()->except('page'), ['status' => 'active'])) }}" class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/30 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Akun Aktif</span>
                            <strong class="mt-2 block text-2xl font-black text-emerald-700 sm:text-3xl">{{ number_format($summary['total_active'], 0, ',', '.') }}</strong>
                            <span class="mt-1 block text-xs text-gray-400">Mengirim laporan rutin</span>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>
                </a>

                <a href="{{ route('partners.index', array_merge(request()->except('page'), ['status' => 'inactive'])) }}" class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm transition hover:border-amber-200 hover:bg-amber-50/30 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Akun Inactive</span>
                            <strong class="mt-2 block text-2xl font-black text-amber-700 sm:text-3xl">{{ number_format($summary['total_inactive'], 0, ',', '.') }}</strong>
                            <span class="mt-1 block text-xs text-gray-400">Tidak laporan 2 hari</span>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>
                </a>

                <div class="rounded-2xl border border-rose-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Akun Suspended</span>
                            <strong class="mt-2 block text-2xl font-black text-rose-700 sm:text-3xl">{{ number_format($summary['total_suspended'], 0, ',', '.') }}</strong>
                            <span class="mt-1 block text-xs text-gray-400">Akses sedang dinonaktifkan</span>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 11-12.728 0m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </section>

            <!-- Form Filter & Table Wrapper -->
            <form action="{{ route('partners.index') }}" method="GET" class="space-y-6">
                
                <!-- Search & Action Card (Header above table) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150 p-6">
                    <div class="flex flex-col md:flex-row gap-4 items-end justify-between">
                        
                        <!-- Search Input -->
                        <div class="flex-grow w-full md:w-auto">
                            <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 font-mono">Cari Nama / ID / WA</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama, ID, atau WA..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2 w-full md:w-auto flex-none">
                            <button type="submit" class="flex-1 md:flex-none justify-center inline-flex items-center px-6 py-2.5 bg-gray-900 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-gray-800 transition shadow-sm">
                                Cari
                            </button>
                            <button type="button" @click="openCopyModal()" class="flex-none justify-center inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition shadow-sm" title="Salin Kontak dari Hasil Filter">
                                <svg class="w-4 h-4 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span class="hidden md:inline">Salin</span>
                            </button>
                            @if($search || $role || $status || (isset($group) && $group))
                                <a href="{{ route('partners.index') }}" class="flex-none justify-center inline-flex items-center px-4 py-2.5 bg-gray-55 border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-100 transition shadow-sm" title="Reset Filter">
                                    <svg class="w-4 h-4 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span class="hidden md:inline">Reset</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="w-16 px-4 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">No.</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID Mitra</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Peran (Role)</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Grup</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal/Jam</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Headstrap</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">WhatsApp</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mitra Atasan</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Registrasi Klien</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                                <tr class="bg-slate-100/50 border-t border-slate-205">
                                    <th class="px-4 py-2"></th>
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2">
                                        <select name="role" id="role" onchange="this.form.submit()" class="block w-full py-1.5 px-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-medium text-gray-700">
                                            <option value="">Semua Peran</option>
                                            <option value="worker" {{ $role == 'worker' ? 'selected' : '' }}>Worker</option>
                                            <option value="mitra" {{ $role == 'mitra' ? 'selected' : '' }}>Mitra</option>
                                        </select>
                                    </th>
                                    <th class="px-6 py-2">
                                        <select name="group" id="group" onchange="this.form.submit()" class="block w-full py-1.5 px-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-medium text-gray-700">
                                            <option value="">Semua Grup</option>
                                            <option value="Group A" {{ (isset($group) && $group == 'Group A') ? 'selected' : '' }}>Group A</option>
                                            <option value="Group B" {{ (isset($group) && $group == 'Group B') ? 'selected' : '' }}>Group B</option>
                                        </select>
                                    </th>
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2">
                                        <select name="status" id="status" onchange="this.form.submit()" class="block w-full py-1.5 px-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-medium text-gray-700">
                                            <option value="">Semua Status</option>
                                            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="suspended" {{ $status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        </select>
                                    </th>
                                    <th class="px-6 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($partners as $partner)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                    <td class="w-16 px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-500">
                                        {{ $partners->firstItem() + $loop->index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-650">
                                        {{ $partner->mitra_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <div class="flex flex-col">
                                            <span>{{ $partner->full_name }}</span>
                                            <span class="text-xs text-gray-400 font-normal">{{ $partner->user?->email ?? 'Belum punya akun login' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 uppercase font-mono text-xs">
                                        {{ $partner->partner_role === 'mitra' ? 'Mitra' : 'Worker' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($partner->group_name == 'Group A')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                A
                                            </span>
                                        @elseif($partner->group_name == 'Group B')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                B
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200">
                                                -
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                        Rp{{ number_format($partner->base_hourly_rate ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $partner->has_headstrap ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-gray-50 text-gray-500 border-gray-200' }}">
                                            {{ $partner->has_headstrap ? 'Sudah' : 'Belum' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-650">
                                        {{ $partner->whatsapp_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $partner->mitraParent->full_name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $partner->is_client_registered ? 'bg-emerald-50 text-emerald-700 border-emerald-250' : 'bg-rose-50 text-rose-700 border-rose-250' }}">
                                            {{ $partner->is_client_registered ? 'Registered' : 'Unregistered' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $partner->statusBadgeClasses() }}">
                                            {{ $partner->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('partners.edit', $partner) }}" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button
                                                type="button"
                                                @click="openDeleteModal(@js([
                                                    'name' => $partner->full_name,
                                                    'mitraId' => $partner->mitra_id,
                                                    'role' => $partner->partner_role === 'mitra' ? 'Mitra' : 'Worker',
                                                    'url' => route('partners.destroy', $partner),
                                                ]))"
                                                class="p-1.5 text-gray-500 hover:text-red-650 hover:bg-red-50 rounded-lg transition"
                                                title="Hapus"
                                                aria-label="Hapus akun {{ $partner->full_name }}"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                                        <span class="text-sm">Tidak ada data mitra atau worker ditemukan.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($partners->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $partners->links() }}
                    </div>
                @endif
            </div>
        </form>

        </div>

        <template x-if="showDeleteModal">
            <div
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
                @click.self="closeDeleteModal()"
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-partner-title"
            >
                <div class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-5 shadow-2xl sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.3 4.5L2.7 18a2 2 0 001.75 3h15.1a2 2 0 001.75-3L13.7 4.5a2 2 0 00-3.4 0z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 id="delete-partner-title" class="text-lg font-black leading-tight text-slate-900">Hapus Akun Pengguna?</h3>
                            <p class="mt-1 text-xs font-medium text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl border border-gray-100 bg-slate-50 p-4">
                        <span class="block truncate text-sm font-bold text-slate-900" x-text="deleteTarget.name"></span>
                        <span class="mt-1 block text-xs text-gray-500">
                            <span x-text="deleteTarget.mitraId"></span>
                            <span aria-hidden="true">&middot;</span>
                            <span x-text="deleteTarget.role"></span>
                        </span>
                    </div>
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showDeleteModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0" 
                         x-transition:enter-end="opacity-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100" 
                         x-transition:leave-end="opacity-0" 
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                         @click="closeDeleteModal()"
                         aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showDeleteModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                        Hapus Akun Mitra/Worker
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Apakah Anda yakin ingin menghapus <strong x-text="deleteTarget.name" class="text-gray-900"></strong> (<span x-text="deleteTarget.role"></span>) dari sistem? 
                                        </p>
                                        <div class="bg-red-50 p-4 rounded-xl border border-red-100">
                                            <p class="text-xs text-red-800 font-medium">
                                                <strong>⚠️ Perhatian:</strong> Tindakan ini akan menghapus akun login dan seluruh data histori kerja video yang pernah dikirim oleh pengguna ini secara permanen.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                            <form :action="deleteTarget.url" method="POST" @submit="deleting = true">
                                @csrf
                                @method('DELETE')
                                <button type="submit" :disabled="deleting" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition disabled:opacity-50">
                                    <span x-show="!deleting">Ya, Hapus Permanen</span>
                                    <span x-show="deleting">Menghapus...</span>
                                </button>
                            </form>
                            <button type="button" @click="closeDeleteModal()" :disabled="deleting" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Copy Contacts Modal -->
        <template x-if="showCopyModal">
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showCopyModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0" 
                         x-transition:enter-end="opacity-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100" 
                         x-transition:leave-end="opacity-0" 
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                         @click="closeCopyModal()"
                         aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showCopyModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                        Salin Data Kontak
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Berikut adalah daftar kontak hasil filter. Klik tombol Salin untuk mengcopy seluruh teks ini.
                                        </p>
                                        <textarea x-model="copyData" class="w-full h-64 p-3 border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono" readonly></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                            <button type="button" @click="copyToClipboard()" :disabled="fetchingContacts" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition disabled:opacity-50">
                                <span x-show="!fetchingContacts">Salin (Copy)</span>
                                <span x-show="fetchingContacts">Memuat...</span>
                            </button>
                            <button type="button" @click="closeCopyModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
