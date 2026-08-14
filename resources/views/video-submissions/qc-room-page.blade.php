<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('QC Video Room') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ 
        expandedPartners: [],
        showImageModal: false,
        showDetailModal: false,
        showCreateModal: false,
        previewImageUrl: '',
        activeDailyReport: {},
        togglePartner(partnerId) {
            if (this.expandedPartners.includes(partnerId)) {
                this.expandedPartners = this.expandedPartners.filter(id => id !== partnerId);
            } else {
                this.expandedPartners.push(partnerId);
            }
        },
        isExpanded(partnerId) {
            return this.expandedPartners.includes(partnerId);
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div x-data="{ showToast: true }" 
                     x-show="showToast" 
                     x-init="setTimeout(() => showToast = false, 3000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2 md:translate-y-0 md:translate-x-4"
                     x-transition:enter-end="opacity-100 translate-y-0 md:translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 md:translate-x-0"
                     x-transition:leave-end="opacity-0 translate-y-2 md:translate-y-0 md:translate-x-4"
                     class="fixed bottom-5 right-5 z-50 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-2xl shadow-xl border border-emerald-100 max-w-sm">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white mr-3">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <div class="text-sm font-bold text-emerald-800">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-2xl text-sm text-rose-800 font-semibold shadow-sm animate-in fade-in duration-200">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Period Filter & Search Header Card -->
            <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 space-y-5">
                <form action="{{ route('video-submissions.qc-room') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    
                    <!-- Dropdown Periode -->
                    <div class="w-full md:w-80">
                        <label for="period" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Pilih Periode (Rabu–Selasa)</label>
                        <select name="period" id="period" onchange="this.form.submit()" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-gray-700 font-medium">
                            <option value="all" {{ $selectedPeriodKey === 'all' ? 'selected' : '' }}>Semua Periode (All-Time)</option>
                            @foreach($periods as $p)
                                <option value="{{ $p['start']->format('Y-m-d') . '|' . $p['end']->format('Y-m-d') }}" 
                                    {{ $selectedPeriodKey === ($p['start']->format('Y-m-d') . '|' . $p['end']->format('Y-m-d')) ? 'selected' : '' }}>
                                    {{ $p['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Group Filter -->
                    <div class="w-full md:w-44">
                        <label for="group" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Pilih Grup</label>
                        <select name="group" id="group" onchange="this.form.submit()" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-gray-700 font-medium">
                            <option value="">Semua Grup</option>
                            <option value="Group A" {{ $selectedGroup === 'Group A' ? 'selected' : '' }}>Group A</option>
                            <option value="Group B" {{ $selectedGroup === 'Group B' ? 'selected' : '' }}>Group B</option>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="flex-1 w-full">
                        <label for="search" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Cari Nama/ID Mitra</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Cari berdasarkan Nama Mitra, ID Mitra, atau Email..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                        </div>
                    </div>
                    
                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="submit" class="flex-1 md:flex-none justify-center inline-flex items-center px-6 py-2.5 bg-gray-900 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-gray-800 transition-colors duration-200">
                            Cari
                        </button>
                        @if($search || $selectedGroup)
                            <a href="{{ route('video-submissions.qc-room', ['period' => $selectedPeriodKey]) }}" class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-200 transition-all">
                                Reset
                            </a>
                        @endif
                        <button type="button" @click.prevent="showCreateModal = true" class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-indigo-700 transition-all shadow-sm gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat Laporan untuk Mitra
                        </button>
                        <a href="{{ route('video-submissions.export-pdf', ['period' => $selectedPeriodKey, 'search' => $search, 'group' => $selectedGroup]) }}" 
                           target="_blank" 
                           class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-indigo-50 border border-indigo-200 rounded-xl font-semibold text-sm text-indigo-700 hover:bg-indigo-100 transition-all gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak PDF
                        </a>
                    </div>
                </form>

                <!-- Day Strip (hanya tampil kalau ada periode spesifik yang dipilih) -->
                @if(!empty($periodDays))
                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">Filter Hari (Opsional)</span>
                        </div>

                        <div class="flex gap-2 overflow-x-auto pb-1" style="scrollbar-width: none;">
                            @foreach($periodDays as $day)
                                @php
                                    $dayKey   = $day->format('Y-m-d');
                                    $isActive = $selectedDate === $dayKey;
                                    $isToday  = $day->isToday();
                                    
                                    // Jika hari sudah aktif, klik lagi akan menghapus filter tanggal (toggle off)
                                    $routeParams = array_merge(request()->except('date'), [
                                        'period' => $selectedPeriodKey, 
                                        'search' => $search, 
                                        'group' => $selectedGroup
                                    ]);
                                    
                                    if (!$isActive) {
                                        $routeParams['date'] = $dayKey;
                                    }
                                @endphp
                                <a href="{{ route('video-submissions.qc-room', $routeParams) }}"
                                   class="flex-shrink-0 flex flex-col items-center px-3 py-2 rounded-2xl border transition-all duration-150 select-none cursor-pointer
                                   @if($isActive) bg-slate-900 border-slate-900 shadow-sm
                                   @elseif($isToday) bg-indigo-50 border-indigo-300
                                   @else bg-white border-gray-200 hover:border-indigo-200 hover:bg-indigo-50
                                   @endif"
                                   style="min-width: 52px;">
                                    <span class="text-[9px] font-bold uppercase tracking-wider font-mono
                                    @if($isActive) text-slate-400
                                    @elseif($isToday) text-indigo-500
                                    @else text-gray-400
                                    @endif">
                                        {{ $day->translatedFormat('D') }}
                                    </span>
                                    <span class="text-[15px] font-black leading-tight mt-0.5
                                    @if($isActive) text-white
                                    @elseif($isToday) text-indigo-700
                                    @else text-gray-800
                                    @endif">
                                        {{ $day->format('j') }}
                                    </span>
                                    @if($isToday)
                                        <span class="w-1.5 h-1.5 rounded-full mt-1 {{ $isActive ? 'bg-slate-400' : 'bg-indigo-500' }}"></span>
                                    @else
                                        <span class="w-1.5 h-1.5 mt-1"></span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>




            <!-- Dynamic Stats Summary -->
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Total Dikirim Periode Ini:</span>
                    <span class="text-sm font-black text-slate-800 font-mono">{{ $filteredSubmittedDuration }}</span>
                </div>
                <div class="bg-emerald-50 border border-emerald-250 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 font-mono">Total Rilis Disetujui:</span>
                    <span class="text-sm font-black text-emerald-700 font-mono">{{ $filteredApprovedDuration }}</span>
                </div>
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-800 font-mono">Mitra Pengirim:</span>
                    <span class="text-sm font-black text-indigo-700 font-mono">{{ $partners->total() }}</span>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-150">
                        <thead class="bg-slate-50/70">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Mitra (Worker)</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Total Dilaporkan</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Durasi Verifikasi</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Status</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($partners as $partner)
                                <tr class="hover:bg-slate-50/60 transition-colors duration-150 cursor-pointer select-none" @click="togglePartner('{{ $partner->id }}')">
                                    <!-- Partner Demographics -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="text-gray-400">
                                                <svg class="w-4 h-4 transform transition-transform duration-200" :class="isExpanded('{{ $partner->id }}') ? 'rotate-90 text-indigo-650' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-900">{{ $partner->full_name }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">ID: {{ $partner->mitra_id }}</span>
                                                 <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-400" x-data="{ copied: false }" @click.stop>
                                                     <span class="select-all">{{ $partner->email ?: ($partner->user?->email ?: '-') }}</span>
                                                     @if($partner->email || ($partner->user?->email))
                                                         <button type="button" 
                                                                 @click="navigator.clipboard.writeText('{{ $partner->email ?: $partner->user?->email }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                                                 class="text-gray-400 hover:text-indigo-650 transition p-0.5 rounded"
                                                                 title="Salin Email">
                                                             <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                                             </svg>
                                                             <span x-show="copied" class="text-[9px] font-bold text-emerald-600 font-mono" x-cloak>Tersalin!</span>
                                                         </button>
                                                     @endif
                                                 </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Total Reported Duration -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-slate-700 font-mono">
                                        {{ floor($partner->total_reported_minutes / 60) }}j {{ $partner->total_reported_minutes % 60 }}m
                                    </td>
                                    
                                    <!-- Verification Duration -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 font-mono">
                                        @if($partner->approval_status !== 'none' && $partner->period_approval)
                                            <span class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg px-2.5 py-1 text-xs">
                                                {{ $partner->period_approval->approved_minutes }} menit
                                            </span>
                                            @if($partner->period_approval->verifier_notes)
                                                <span class="block text-[10px] text-gray-500 truncate max-w-xs mt-1.5 font-sans" title="{{ $partner->period_approval->verifier_notes }}">Catatan: {{ $partner->period_approval->verifier_notes }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic text-xs">Belum diperiksa</span>
                                        @endif
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($partner->approval_status === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-blue-50 text-blue-700 border-blue-200">
                                                Paid (Lunas)
                                            </span>
                                        @elseif($partner->approval_status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">
                                                Rilis (Approved)
                                            </span>
                                        @elseif($partner->approval_status === 'draft')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-amber-50 text-amber-700 border-amber-250">
                                                Draf (Admin)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-gray-50 text-gray-500 border-gray-200">
                                                Belum Diperiksa
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium" @click.stop>
                                        <button type="button" @click="togglePartner('{{ $partner->id }}')" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                            <span x-text="isExpanded('{{ $partner->id }}') ? 'Tutup' : 'Periksa Laporan'"></span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Expanded Daily Reports Accordion Detail -->
                                <tr x-show="isExpanded('{{ $partner->id }}')" x-cloak class="bg-slate-50/40">
                                    <td colspan="5" class="px-6 py-4">
                                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 bg-white rounded-3xl p-5 border border-slate-100 shadow-xs">
                                            
                                            <!-- Left Panel (5 columns): Form Verifikasi -->
                                            <div class="lg:col-span-5 space-y-4 pr-0 lg:pr-6 border-0 lg:border-r border-slate-100">
                                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest font-mono">Panel Verifikasi Periode</h4>
                                                
                                                @if($selectedPeriodKey === 'all')
                                                    <div class="w-full bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-center text-xs font-medium text-indigo-700 leading-relaxed">
                                                        ℹ️ Panel persetujuan dinonaktifkan pada tampilan <strong>Semua Periode</strong>. Silakan pilih periode spesifik di bagian atas untuk melakukan approval.
                                                    </div>
                                                @else
                                                    <form id="form-{{ $partner->id }}" method="POST" class="space-y-4">
                                                        @csrf
                                                        <input type="hidden" name="partner_id" value="{{ $partner->id }}">
                                                        <input type="hidden" name="period_start_date" value="{{ $startDate->format('Y-m-d') }}">
                                                        <input type="hidden" name="period_end_date" value="{{ $endDate->format('Y-m-d') }}">
                                                    
                                                    <!-- Input Durasi Disetujui -->
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Durasi yang Disetujui (Menit)</label>
                                                        <div class="relative">
                                                            <input type="number" name="approved_minutes" value="{{ old('approved_minutes', $partner->input_approved_minutes) }}" required min="0" 
                                                                   {{ $partner->approval_status === 'paid' ? 'disabled' : '' }}
                                                                   class="block w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-lg font-bold">
                                                            <span class="absolute right-3 top-2.5 text-xs text-gray-400 font-medium">menit</span>
                                                        </div>
                                                        <p class="text-[10px] text-gray-400 mt-1.5 leading-relaxed">
                                                            Maksimal durasi dilaporkan: <strong class="text-slate-700 font-bold font-mono">{{ $partner->total_reported_minutes }} menit</strong> 
                                                            ({{ floor($partner->total_reported_minutes / 60) }}j {{ $partner->total_reported_minutes % 60 }}m).
                                                        </p>
                                                    </div>
                                                    
                                                    <!-- Input Catatan Verifikator -->
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 font-mono">Catatan Masukan / Alasan</label>
                                                        <textarea name="verifier_notes" rows="3" placeholder="Catatan opsional atau penyesuaian SOP..."
                                                                  {{ $partner->approval_status === 'paid' ? 'disabled' : '' }}
                                                                  class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 leading-relaxed">{{ old('verifier_notes', $partner->input_verifier_notes) }}</textarea>
                                                    </div>

                                                    <!-- Form Actions -->
                                                    <div class="pt-2 border-t border-slate-100 space-y-2">
                                                        <div class="flex items-center gap-3">
                                                            @if($partner->approval_status !== 'paid')
                                                                <button type="submit" form="form-{{ $partner->id }}" 
                                                                        x-on:click.prevent="$el.form.action='{{ route('video-submissions.save-draft') }}'; $el.form.submit()"
                                                                        class="flex-1 justify-center inline-flex items-center px-4 py-2.5 bg-white hover:bg-slate-50 border border-gray-205 text-gray-750 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 shadow-xs">
                                                                    Simpan Draf
                                                                </button>
                                                                <button type="submit" form="form-{{ $partner->id }}"
                                                                        x-on:click.prevent="$el.form.action='{{ route('video-submissions.finalize') }}'; $el.form.submit()"
                                                                        class="flex-1 justify-center inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 shadow-md shadow-indigo-50 font-bold">
                                                                    Rilis & Approve
                                                                </button>
                                                            @endif
                                                        </div>
                                                        @if($partner->approval_status !== 'paid' && in_array($partner->approval_status, ['approved', 'draft']))
                                                            <button type="submit" form="form-{{ $partner->id }}"
                                                                    x-on:click.prevent="if(confirm('Apakah Anda yakin ingin membatalkan status persetujuan periode ini? Seluruh laporan kerja harian mitra akan kembali ke status review.')) { $el.form.action='{{ route('video-submissions.revert-period') }}'; $el.form.submit() }"
                                                                    class="w-full justify-center inline-flex items-center px-4 py-2 bg-rose-50 hover:bg-rose-100 border border-rose-250 text-rose-800 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 shadow-xs">
                                                                Batalkan & Reset Persetujuan
                                                            </button>
                                                        @elseif($partner->approval_status === 'paid')
                                                            <div class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-center text-xs font-bold text-slate-500 font-mono">
                                                                🔒 Status Terkunci (Sudah Dibayar)
                                                            </div>
                                                        @endif
                                                    </div>
                                                </form>
                                                @endif
                                            </div>

                                            <!-- Right Panel (7 columns): Daftar Laporan Kerja Harian -->
                                            <div class="lg:col-span-7 flex flex-col" x-data="{
                                                partnerSelectedReports: [],
                                                get totalSelectedMinutes() {
                                                    let total = 0;
                                                    this.partnerSelectedReports.forEach(id => {
                                                        const el = document.getElementById('report-minutes-' + id);
                                                        if (el) total += parseInt(el.value) || 0;
                                                    });
                                                    return total;
                                                },
                                                toggleAll() {
                                                    const checkboxes = document.querySelectorAll('.report-checkbox-{{ $partner->id }}');
                                                    if (this.partnerSelectedReports.length === checkboxes.length) {
                                                        this.partnerSelectedReports = [];
                                                    } else {
                                                        this.partnerSelectedReports = Array.from(checkboxes).map(cb => cb.value);
                                                    }
                                                }
                                            }">
                                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-3 font-mono flex items-center justify-between">
                                                    <span>Daftar Laporan Kerja Harian</span>
                                                </h4>
                                                
                                                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                                                    <table class="min-w-full divide-y divide-gray-100">
                                                        <thead class="bg-slate-50">
                                                            <tr>
                                                                <th class="px-4 py-2 text-left text-[10px] font-bold text-slate-500 uppercase font-mono w-8">
                                                                    <input type="checkbox" @click="toggleAll" :checked="partnerSelectedReports.length > 0 && partnerSelectedReports.length === document.querySelectorAll('.report-checkbox-{{ $partner->id }}').length" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
                                                                </th>
                                                                <th class="px-4 py-2 text-left text-[10px] font-bold text-slate-500 uppercase font-mono">ID Laporan</th>
                                                                <th class="px-4 py-2 text-left text-[10px] font-bold text-slate-500 uppercase font-mono">Tanggal Kerja</th>
                                                                <th class="px-4 py-2 text-center text-[10px] font-bold text-slate-500 uppercase font-mono">Durasi Kirim</th>
                                                                <th class="px-4 py-2 text-center text-[10px] font-bold text-slate-500 uppercase font-mono">Status Harian</th>
                                                                <th class="px-4 py-2 text-center text-[10px] font-bold text-slate-500 uppercase font-mono">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100 bg-white">
                                                            @foreach($partner->period_reports as $report)
                                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                                    <td class="px-4 py-2 whitespace-nowrap text-center" @click.stop>
                                                                        @if($report->qc_status !== 'approved' && $partner->approval_status !== 'paid')
                                                                            <input type="checkbox" x-model="partnerSelectedReports" value="{{ $report->id }}" class="report-checkbox-{{ $partner->id }} rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
                                                                            <input type="hidden" id="report-minutes-{{ $report->id }}" value="{{ $report->submitted_duration_minutes }}">
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-xs font-mono text-gray-500">
                                                                        <div class="flex flex-col gap-0.5">
                                                                            <span>{{ substr($report->id, 0, 8) }}...</span>
                                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold {{ $report->project_name === 'atlas' ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-600' }} uppercase w-fit">
                                                                                {{ str_replace('_', ' ', $report->project_name ?? 'minutes data') }}
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-xs font-semibold text-slate-700">
                                                                        {{ $report->submission_date->translatedFormat('d F Y') }}
                                                                    </td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-center font-bold text-indigo-650 font-mono">
                                                                        {{ $report->submitted_duration_formatted }}
                                                                    </td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-center">
                                                                        @if($report->qc_status === 'approved')
                                                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Approved ({{ $report->approved_duration_minutes }}m)</span>
                                                                        @elseif($report->qc_status === 'rejected')
                                                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-100" title="{{ $report->verifier_notes }}">Revisi (Ditolak)</span>
                                                                        @else
                                                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Review</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-center">
                                                                        <div class="flex items-center justify-center gap-1.5" @click.stop>
                                                                            <!-- Detail button -->
                                                                            <button type="button" 
                                                                                    @click="activeDailyReport = {{ json_encode([
                                                                                        'id' => $report->id,
                                                                                        'project_name' => $report->project_name,
                                                                                        'date' => $report->submission_date->translatedFormat('d F Y'),
                                                                                        'duration' => $report->submitted_duration_formatted,
                                                                                        'status' => $partner->approval_status === 'paid' ? 'Paid (Lunas)' : ($partner->approval_status === 'approved' ? 'Rilis (Approved)' : ($partner->approval_status === 'draft' ? 'Draf (Admin)' : 'Belum Diperiksa')),
                                                                                        'approved_min' => $report->approved_duration_minutes,
                                                                                        'email_img' => $report->evidence_email_image_url,
                                                                                        'quality_img' => $report->evidence_app_quality_image_url,
                                                                                        'submitted_imgs' => $report->evidence_submitted_image_urls,
                                                                                        'device' => $report->device_type ?: '-',
                                                                                        'headstrap' => $report->has_headstrap ? 'Ya' : 'Tidak',
                                                                                        'notes' => $report->verifier_notes ?: 'Tidak ada catatan'
                                                                                    ]) }}; showDetailModal = true" 
                                                                                    class="inline-flex items-center px-2 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[9px] font-bold transition">
                                                                                Detail
                                                                            </button>

                                                                            @if($partner->approval_status !== 'paid')
                                                                                @if($report->qc_status === 'rejected')
                                                                                    <!-- Restore/Undo Rejection Action -->
                                                                                    <form action="{{ route('video-submissions.restore-report', $report->id) }}" method="POST" class="inline" onsubmit="event.preventDefault(); confirmRestore(this)">
                                                                                        @csrf
                                                                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg text-[9px] font-bold border border-emerald-200 transition">
                                                                                            Batal Tolak
                                                                                        </button>
                                                                                    </form>
                                                                                @elseif($report->qc_status === 'approved')
                                                                                    <!-- Undo Approval Action -->
                                                                                    <form action="{{ route('video-submissions.restore-report', $report->id) }}" method="POST" class="inline" onsubmit="event.preventDefault(); confirmCancelApproval(this)">
                                                                                        @csrf
                                                                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-[9px] font-bold border border-slate-200 transition">
                                                                                            Batal
                                                                                        </button>
                                                                                    </form>
                                                                                @else
                                                                                    <!-- Approve Action -->
                                                                                    <form action="{{ route('video-submissions.approve-report', $report->id) }}" method="POST" class="inline" onsubmit="event.preventDefault(); confirmApproveReport(this, {{ $report->submitted_duration_minutes }})">
                                                                                        @csrf
                                                                                        <input type="hidden" name="adjusted_minutes" class="adjusted-minutes-input">
                                                                                        <input type="hidden" name="admin_note" class="admin-note-input">
                                                                                        <input type="hidden" name="custom_rate" class="custom-rate-input">
                                                                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg text-[9px] font-bold border border-emerald-200 transition">
                                                                                            Setujui
                                                                                        </button>
                                                                                    </form>

                                                                                    <!-- Reject/Revision Action -->
                                                                                    <form action="{{ route('video-submissions.reject-report', $report->id) }}" method="POST" class="inline" onsubmit="event.preventDefault(); confirmRejection(this)">
                                                                                        @csrf
                                                                                        <input type="hidden" name="reason" class="reject-reason-input">
                                                                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-[9px] font-bold border border-amber-200 transition">
                                                                                            Revisi
                                                                                        </button>
                                                                                    </form>
                                                                                @endif

                                                                                @if($report->qc_status !== 'approved')
                                                                                    <!-- Delete Action -->
                                                                                    <form action="{{ route('video-submissions.destroy', $report->id) }}" method="POST" class="inline" onsubmit="event.preventDefault(); confirmDelete(this)">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-800 rounded-lg text-[9px] font-bold border border-rose-200 transition">
                                                                                            Hapus
                                                                                        </button>
                                                                                    </form>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Floating Action Bar for Batch Approve -->
                                                <div x-show="partnerSelectedReports.length > 0" x-transition x-cloak class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between shadow-sm">
                                                    <div class="text-xs font-bold text-indigo-800">
                                                        <span x-text="partnerSelectedReports.length"></span> Laporan Dipilih
                                                        <span class="opacity-50 mx-2">|</span>
                                                        Total Kirim: <span x-text="totalSelectedMinutes"></span> menit
                                                    </div>
                                                    <button type="button" @click="confirmBatchApprove('{{ $partner->id }}', partnerSelectedReports, totalSelectedMinutes)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                                        Setujui Terpilih
                                                    </button>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <span class="text-sm">Tidak ada data pengumpulan laporan mitra untuk periode ini.</span>
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
        </div>


        <!-- Alpine Daily Report Detail Pop-up Modal -->
        <div x-show="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak style="display: none;">
            <div class="bg-white rounded-[32px] max-w-lg w-full shadow-2xl overflow-hidden border border-gray-150 animate-in fade-in zoom-in-95 duration-200 flex flex-col my-8">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-950 text-white flex justify-between items-center">
                    <div>
                        <span class="text-[9px] uppercase font-bold text-indigo-400 tracking-widest font-mono">Detail Laporan Harian</span>
                        <h3 class="text-base font-black leading-tight" x-text="activeDailyReport.date"></h3>
                    </div>
                    <button @click="showDetailModal = false" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 overflow-y-auto max-h-[65vh]">
                    <!-- Report Fields Grid -->
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl text-xs font-semibold text-slate-700">
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">ID Laporan</span>
                            <span class="block font-bold text-slate-800 font-mono" x-text="activeDailyReport.id"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Tanggal Kerja</span>
                            <span class="block font-bold text-slate-800" x-text="activeDailyReport.date"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Total Durasi</span>
                            <span class="block font-bold text-slate-800 font-mono" x-text="activeDailyReport.duration"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Status</span>
                            <span class="block font-bold text-slate-800" x-text="activeDailyReport.status"></span>
                        </div>
                    </div>

                    <!-- Proof/Evidence Image Grid -->
                    <div class="space-y-3">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider font-mono">Foto Bukti (SOP)</span>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Email image -->
                            <div class="space-y-1.5" x-show="activeDailyReport.email_img">
                                <span class="block text-[9px] text-gray-400 uppercase font-mono">Bukti Email Register</span>
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider font-mono block mb-1" x-text="activeDailyReport.project_name === 'atlas' ? 'Durasi & Kualitas' : 'Durasi App'">Durasi & Kualitas</span>
                                <a :href="activeDailyReport.email_img" target="_blank" class="relative group block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img :src="activeDailyReport.email_img" class="w-full h-24 object-cover group-hover:scale-105 transition duration-150">
                                    <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/10 transition flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Quality image (Minutes Data only) -->
                            <div class="space-y-1.5" x-show="activeDailyReport.project_name !== 'atlas' && activeDailyReport.quality_img">
                                <span class="block text-[9px] text-gray-400 uppercase font-mono">Bukti Kualitas Video</span>
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider font-mono block mb-1">Kualitas App</span>
                                <a :href="activeDailyReport.quality_img" target="_blank" class="relative group block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img :src="activeDailyReport.quality_img" class="w-full h-24 object-cover group-hover:scale-105 transition duration-150">
                                    <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/10 transition flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </div>
                                </a>
                            </div>

                            <!-- Submitted images (Atlas only) -->
                            <div class="space-y-1.5 col-span-2 mt-2" x-show="activeDailyReport.project_name === 'atlas' && activeDailyReport.submitted_imgs && activeDailyReport.submitted_imgs.length > 0">
                                <span class="block text-[9px] text-gray-400 uppercase font-mono">Bukti Unggahan/Submitted</span>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-for="(img, idx) in activeDailyReport.submitted_imgs" :key="idx">
                                        <a :href="img" target="_blank" class="relative group block w-full overflow-hidden rounded-xl border border-slate-200">
                                            <img :src="img" class="w-full h-24 object-cover group-hover:scale-105 transition duration-150">
                                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-150 flex justify-end">
                    <button type="button" @click="showDetailModal = false" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const swalOptions = {
                confirmButtonColor: '#0f766e',
                cancelButtonColor: '#bae6fd',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-gray-100',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold shadow-md hover:shadow-lg transition-all',
                    cancelButton: 'rounded-xl px-6 py-2.5 font-bold text-slate-700 shadow-sm hover:bg-sky-200 transition-all ml-3'
                },
                buttonsStyling: true
            };

            function confirmDelete(form) {
                Swal.fire({
                    ...swalOptions,
                    title: 'Hapus Laporan?',
                    text: "Apakah Anda yakin ingin menghapus laporan harian ini secara permanen?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }

            function confirmRestore(form) {
                Swal.fire({
                    ...swalOptions,
                    title: 'Batal Tolak?',
                    text: "Apakah Anda yakin ingin membatalkan status revisi dan mengembalikan laporan ini ke antrean review?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Kembalikan',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }

            function confirmCancelApproval(form) {
                Swal.fire({
                    ...swalOptions,
                    title: 'Batalkan Persetujuan?',
                    text: "Apakah Anda yakin ingin membatalkan status approved dan mengembalikan laporan ini ke antrean review?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Tutup',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }

            function confirmRejection(form) {
                Swal.fire({
                    ...swalOptions,
                    title: 'Revisi Laporan',
                    text: 'Masukkan alasan penolakan / instruksi revisi untuk mitra:',
                    input: 'textarea',
                    inputPlaceholder: 'Ketik alasan di sini...',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Tolak & Revisi',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value || value.trim() === '') {
                            return 'Alasan penolakan wajib diisi!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.querySelector('.reject-reason-input').value = result.value;
                        form.submit();
                    }
                });
            }

            function confirmApproveReport(form, defaultMinutes) {
                Swal.fire({
                    ...swalOptions,
                    title: 'Setujui Laporan & Durasi',
                    html: `
                        <div class="space-y-4 mt-4 text-left">
                            <p class="text-sm text-gray-500">Durasi yang diajukan oleh mitra adalah <strong>${defaultMinutes} menit</strong>. Sesuaikan jika bukti tidak mendukung penuh.</p>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Durasi Disetujui (Menit)</label>
                                <input type="number" id="swal-adjusted-minutes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-bold" value="${defaultMinutes}" min="0">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Catatan Penyesuaian (Opsional)</label>
                                <textarea id="swal-admin-note" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 text-sm" rows="2" placeholder="Catatan jika durasi dikurangi..."></textarea>
                            </div>
                            <div class="pt-2 border-t border-gray-100">
                                <label class="flex items-center gap-2 cursor-pointer mb-2">
                                    <input type="checkbox" id="swal-toggle-custom-rate" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" onchange="document.getElementById('swal-custom-rate-container').classList.toggle('hidden', !this.checked)">
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Gunakan Rate Khusus (Custom Rate)</span>
                                </label>
                                <div id="swal-custom-rate-container" class="hidden">
                                    <input type="number" id="swal-custom-rate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 text-sm" placeholder="Contoh: 55000" min="0">
                                </div>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan & Setujui',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#059669', // emerald-600
                    preConfirm: () => {
                        const minutes = document.getElementById('swal-adjusted-minutes').value;
                        if (!minutes || minutes < 0) {
                            Swal.showValidationMessage('Durasi tidak valid!');
                        }
                        return {
                            minutes: minutes,
                            note: document.getElementById('swal-admin-note').value,
                            custom_rate: document.getElementById('swal-toggle-custom-rate').checked ? document.getElementById('swal-custom-rate').value : ''
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.querySelector('.adjusted-minutes-input').value = result.value.minutes;
                        form.querySelector('.admin-note-input').value = result.value.note;
                        form.querySelector('.custom-rate-input').value = result.value.custom_rate;
                        form.submit();
                    }
                });
            }
            function confirmBatchApprove(partnerId, reportIds, totalReportedMinutes) {
                Swal.fire({
                    ...swalOptions,
                    title: 'Persetujuan Kolektif (Batch Approve)',
                    html: `
                        <div class="space-y-4 mt-4 text-left">
                            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 text-xs text-indigo-800">
                                Mengapprove <strong>${reportIds.length} laporan</strong> secara bersamaan.<br>
                                Total durasi diajukan: <strong>${totalReportedMinutes} menit</strong>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Total Durasi Disetujui (Menit)</label>
                                <input type="number" id="swal-batch-minutes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-bold text-lg text-indigo-700" value="${totalReportedMinutes}" min="0">
                            </div>
                            <div class="pt-2 border-t border-gray-100">
                                <label class="flex items-center gap-2 cursor-pointer mb-2">
                                    <input type="checkbox" id="swal-batch-toggle-custom-rate" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="document.getElementById('swal-batch-custom-rate-container').classList.toggle('hidden', !this.checked)">
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Gunakan Rate Khusus (Custom Rate)</span>
                                </label>
                                <div id="swal-batch-custom-rate-container" class="hidden">
                                    <input type="number" id="swal-batch-custom-rate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Contoh: 55000" min="0">
                                </div>
                            </div>
                        </div>
                        <form id="batch-approve-form-${partnerId}" method="POST" action="{{ route('video-submissions.batch-approve') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="report_ids" id="batch-report-ids-${partnerId}">
                            <input type="hidden" name="total_approved_minutes" id="batch-total-minutes-${partnerId}">
                            <input type="hidden" name="custom_rate" id="batch-custom-rate-${partnerId}">
                        </form>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan & Approve Semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#4f46e5', // indigo-600
                    preConfirm: () => {
                        const minutes = document.getElementById('swal-batch-minutes').value;
                        if (!minutes || minutes < 0) {
                            Swal.showValidationMessage('Durasi tidak valid!');
                        }
                        return {
                            minutes: minutes,
                            custom_rate: document.getElementById('swal-batch-toggle-custom-rate').checked ? document.getElementById('swal-batch-custom-rate').value : ''
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(`batch-approve-form-${partnerId}`);
                        document.getElementById(`batch-report-ids-${partnerId}`).value = reportIds.join(',');
                        document.getElementById(`batch-total-minutes-${partnerId}`).value = result.value.minutes;
                        document.getElementById(`batch-custom-rate-${partnerId}`).value = result.value.custom_rate;
                        form.submit();
                    }
                });
            }
        </script>
        </script>

        <!-- Create Report by Admin Modal -->
        <template x-teleport="body">
            <div x-show="showCreateModal" style="display: none" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm" 
                     @click="showCreateModal = false"
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showCreateModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full sm:p-6 w-full relative">
                     
                    <div class="absolute top-0 right-0 pt-5 pr-5">
                        <button @click="showCreateModal = false" type="button" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="sm:flex sm:items-start w-full">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Buat Laporan untuk Mitra</h3>
                            <div class="mt-4 w-full">
                                <form x-data="{ project_name: 'atlas' }" action="{{ route('video-submissions.create-by-admin') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-left">
                                    @csrf

                                    <div>
                                        <label for="partner_id" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Akun Mitra <span class="text-red-500">*</span></label>
                                        <select name="partner_id" id="partner_id" required class="block w-full min-h-11 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">-- Pilih Mitra --</option>
                                            @foreach($allWorkers as $worker)
                                                <option value="{{ $worker->id }}">{{ $worker->full_name }} ({{ $worker->email ?: ($worker->user?->email ?: '-') }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="project_name_admin" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Aplikasi <span class="text-red-500">*</span></label>
                                        <select name="project_name" id="project_name_admin" x-model="project_name" required class="block w-full min-h-11 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="atlas">Atlas</option>
                                            <option value="minutes_data">Minutes Data</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                        <div>
                                            <label for="submission_date_admin" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengambilan Data <span class="text-red-500">*</span></label>
                                            <input type="date" name="submission_date" id="submission_date_admin" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="block w-full min-h-11 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <div x-data="{ hours: '', minutes: '' }">
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Total Durasi Kerja <span class="text-red-500">*</span></label>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="relative">
                                                    <input type="number" inputmode="numeric" x-model="hours" min="0" max="24" placeholder="0" class="block w-full min-h-11 pr-12 pl-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none">Jam</span>
                                                </div>
                                                <div class="relative">
                                                    <input type="number" inputmode="numeric" x-model="minutes" min="0" max="59" placeholder="0" class="block w-full min-h-11 pr-14 pl-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none">Menit</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="submitted_duration_minutes" :value="(parseInt(hours) || 0) * 60 + (parseInt(minutes) || 0)">
                                        </div>
                                    </div>

                                    <div class="bg-slate-50 border border-gray-100 rounded-xl p-4 space-y-3">
                                        <div class="flex justify-between items-center border-b border-gray-200/50 pb-2">
                                            <span class="text-sm font-bold text-slate-800">
                                                1. <span x-show="project_name === 'atlas'">Screenshot Total Durasi & Kualitas</span>
                                                   <span x-show="project_name === 'minutes_data'" style="display: none;">Screenshot Total Durasi</span>
                                                 <span class="text-red-500">*</span>
                                            </span>
                                        </div>
                                        <input type="file" accept="image/jpeg,image/png,image/webp" name="evidence_email_image_path" required class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                                    </div>

                                    <div class="bg-slate-50 border border-gray-100 rounded-xl p-4 space-y-3" x-cloak x-show="project_name === 'minutes_data'">
                                        <div class="flex justify-between items-center border-b border-gray-200/50 pb-2">
                                            <span class="text-sm font-bold text-slate-800">2. Screenshot Bagian Kualitas <span class="text-red-500">*</span></span>
                                        </div>
                                        <input type="file" accept="image/jpeg,image/png,image/webp" name="evidence_app_quality_image_path" :required="project_name === 'minutes_data'" class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                                    </div>

                                    <div class="bg-slate-50 border border-gray-100 rounded-xl p-4 space-y-3" x-show="project_name === 'atlas'">
                                        <div class="flex justify-between items-center border-b border-gray-200/50 pb-2">
                                            <span class="text-sm font-bold text-slate-800">2. Screenshot Bagian Unggahan/Submitted <span class="text-red-500">*</span></span>
                                        </div>
                                        <input type="file" accept="image/jpeg,image/png,image/webp" name="evidence_submitted_image_paths[]" multiple :required="project_name === 'atlas'" class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                                    </div>

                                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-gray-100">
                                        <button type="button" @click="showCreateModal = false" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition duration-150">
                                            Batal
                                        </button>
                                        <button type="submit" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-300">
                                            Buat Laporan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </template>
    </div>
</x-app-layout>
