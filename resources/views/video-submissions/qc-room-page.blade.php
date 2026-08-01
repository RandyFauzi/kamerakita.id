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
            <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                <form action="{{ route('video-submissions.qc-room') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    
                    <!-- Dropdown Periode (Manis & Rapi) -->
                    <div class="w-full md:w-80">
                        <label for="period" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Pilih Periode Mingguan (Sabtu-Kamis)</label>
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
                        <a href="{{ route('video-submissions.export-pdf', ['period' => $selectedPeriodKey, 'search' => $search, 'group' => $selectedGroup]) }}" 
                           target="_blank" 
                           class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-indigo-55 border border-indigo-200 rounded-xl font-semibold text-sm text-indigo-700 hover:bg-indigo-100 transition-all gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak PDF
                        </a>
                    </div>
                </form>
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
                                            <div class="lg:col-span-7 flex flex-col">
                                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-3 font-mono">Daftar Laporan Kerja Harian</h4>
                                                
                                                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                                                    <table class="min-w-full divide-y divide-gray-100">
                                                        <thead class="bg-slate-50">
                                                            <tr>
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
                                                                    <td class="px-4 py-2 whitespace-nowrap text-xs font-mono text-gray-500">
                                                                        {{ substr($report->id, 0, 8) }}...
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
                                                                                        'date' => $report->submission_date->translatedFormat('d F Y'),
                                                                                        'duration' => $report->submitted_duration_formatted,
                                                                                        'status' => $partner->approval_status === 'paid' ? 'Paid (Lunas)' : ($partner->approval_status === 'approved' ? 'Rilis (Approved)' : ($partner->approval_status === 'draft' ? 'Draf (Admin)' : 'Belum Diperiksa')),
                                                                                        'approved_min' => $report->approved_duration_minutes,
                                                                                        'email_img' => $report->evidence_email_image_url,
                                                                                        'quality_img' => $report->evidence_app_quality_image_url,
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
                                                                                    <form action="{{ route('video-submissions.restore-report', $report->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status revisi dan mengembalikan laporan ini ke antrean review?')">
                                                                                        @csrf
                                                                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg text-[9px] font-bold border border-emerald-200 transition">
                                                                                            Batal Tolak
                                                                                        </button>
                                                                                    </form>
                                                                                @else
                                                                                    <!-- Reject/Revision Action -->
                                                                                    <form action="{{ route('video-submissions.reject-report', $report->id) }}" method="POST" class="inline" onsubmit="return confirmRejection(this)">
                                                                                        @csrf
                                                                                        <input type="hidden" name="reason" class="reject-reason-input">
                                                                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-amber-55 hover:bg-amber-100 text-amber-855 rounded-lg text-[9px] font-bold border border-amber-200 transition">
                                                                                            Revisi
                                                                                        </button>
                                                                                    </form>
                                                                                @endif

                                                                                <!-- Delete Action -->
                                                                                <form action="{{ route('video-submissions.destroy', $report->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan harian ini secara permanen?')">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-800 rounded-lg text-[9px] font-bold border border-rose-200 transition">
                                                                                        Hapus
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
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
                                <a :href="activeDailyReport.email_img" target="_blank" class="relative group block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img :src="activeDailyReport.email_img" class="w-full h-24 object-cover group-hover:scale-105 transition duration-150">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Quality image -->
                            <div class="space-y-1.5" x-show="activeDailyReport.quality_img">
                                <span class="block text-[9px] text-gray-400 uppercase font-mono">Bukti Kualitas Video</span>
                                <a :href="activeDailyReport.quality_img" target="_blank" class="relative group block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img :src="activeDailyReport.quality_img" class="w-full h-24 object-cover group-hover:scale-105 transition duration-150">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </div>
                                </a>
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
        <script>
            function confirmRejection(form) {
                let reason = prompt("Masukkan alasan penolakan / instruksi revisi untuk mitra:");
                if (reason === null) return false; // Dibatalkan oleh admin
                if (reason.trim() === "") {
                    alert("Alasan penolakan wajib diisi!");
                    return false;
                }
                form.querySelector('.reject-reason-input').value = reason;
                return true;
            }
        </script>
    </div>
</x-app-layout>
