<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('QC Video Room') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ 
        showVerifyModal: false,
        showDeleteModal: false,
        showRevertModal: false,
        activeReport: {},
        actionType: 'approve_full',
        approvedMinutes: '',
        notes: '',
        emailImageFailed: false,
        qualityImageFailed: false,
        openReview(report) {
            this.activeReport = report;
            this.showVerifyModal = true;
            this.actionType = report.qc_status === 'pending' ? 'start_review' : 'approve_full';
            this.approvedMinutes = report.submitted_duration_minutes;
            this.notes = report.verifier_notes || '';
            this.emailImageFailed = false;
            this.qualityImageFailed = false;
        },
        openDelete(report) {
            this.activeReport = report;
            this.showDeleteModal = true;
        },
        openRevert(report) {
            this.activeReport = report;
            this.showRevertModal = true;
            this.showVerifyModal = false;
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
                <div x-data="{ showToast: true }" 
                     x-show="showToast" 
                     x-init="setTimeout(() => showToast = false, 5000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2 md:translate-y-0 md:translate-x-4"
                     x-transition:enter-end="opacity-100 translate-y-0 md:translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed top-6 right-6 z-50 max-w-sm w-full bg-rose-50 border border-rose-200 rounded-2xl shadow-xl p-4 flex items-start gap-3 animate-in slide-in-from-right duration-300" 
                     role="alert">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-rose-650" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-rose-950 leading-tight">Gagal</p>
                        <p class="text-xs text-rose-800 mt-1 font-medium leading-relaxed">{{ session('error') }}</p>
                    </div>
                    <button @click="showToast = false" class="flex-shrink-0 text-rose-450 hover:text-rose-650 transition rounded-lg hover:bg-rose-100 p-0.5 -mt-1 -mr-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Stat Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-all duration-300">
                    <div class="space-y-2">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Laporan Menunggu QC (Pending)</span>
                        <span class="block text-3xl font-black text-slate-800 tracking-tight">{{ $totalPendingCount }}</span>
                        <span class="block text-xs font-semibold text-emerald-600">Menunggu antrean verifikasi</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-yellow-50 border border-yellow-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-all duration-300">
                    <div class="space-y-2">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Disetujui Hari Ini</span>
                        <span class="block text-3xl font-black text-slate-800 tracking-tight">{{ $totalApprovedCountToday }}</span>
                        <span class="block text-xs font-semibold text-emerald-600">Telah diverifikasi hari ini</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-all duration-300">
                    <div class="space-y-2">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Ditolak Hari Ini</span>
                        <span class="block text-3xl font-black text-slate-800 tracking-tight">{{ $totalRejectedCountToday }}</span>
                        <span class="block text-xs font-semibold text-rose-600">Laporan ditolak / butuh perbaikan</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tab switchers for Status -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('video-submissions.qc-room', array_merge(request()->query(), ['status' => 'all'])) }}" 
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition {{ $status === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    Semua Laporan
                </a>
                <a href="{{ route('video-submissions.qc-room', array_merge(request()->query(), ['status' => 'pending'])) }}" 
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-800 border border-yellow-250 shadow-sm' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    Pending ({{ $totalPendingCount }})
                </a>
                <a href="{{ route('video-submissions.qc-room', array_merge(request()->query(), ['status' => 'on_review'])) }}" 
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition {{ $status === 'on_review' ? 'bg-blue-100 text-blue-800 border border-blue-250 shadow-sm' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    On Review ({{ $totalOnReviewCount }})
                </a>
                <a href="{{ route('video-submissions.qc-room', array_merge(request()->query(), ['status' => 'approved'])) }}" 
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition {{ $status === 'approved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-250 shadow-sm' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    Approved
                </a>
                <a href="{{ route('video-submissions.qc-room', array_merge(request()->query(), ['status' => 'rejected'])) }}" 
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition {{ $status === 'rejected' ? 'bg-rose-100 text-rose-800 border border-rose-250 shadow-sm' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    Rejected
                </a>
            </div>

            <!-- Filter & Search Card -->
            <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                <form action="{{ route('video-submissions.qc-room') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="flex-1 w-full">
                        <label for="search" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Cari Nama/ID</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Cari berdasarkan Nama Mitra, ID Mitra, atau ID Laporan..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                        </div>
                    </div>
                    
                    <div class="w-full md:w-48">
                        <label for="start_date" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="block w-full py-2.5 px-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div class="w-full md:w-48">
                        <label for="end_date" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="block w-full py-2.5 px-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="submit" class="flex-1 md:flex-none justify-center inline-flex items-center px-6 py-2.5 bg-gray-900 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-gray-800 transition-colors duration-200">
                            Filter
                        </button>
                        @if($search || $startDate || $endDate || $status !== 'pending')
                            <a href="{{ route('video-submissions.qc-room') }}" class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-200 transition-all">
                                Reset
                            </a>
                        @endif
                        <a href="{{ route('video-submissions.export-pdf', request()->query()) }}" target="_blank" class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-indigo-50 border border-indigo-100 rounded-xl font-semibold text-sm text-indigo-700 hover:bg-indigo-100 transition-all gap-1.5" title="Ekspor PDF A4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Ekspor PDF
                        </a>
                    </div>
                </form>
            </div>

            <!-- Filtered Stats Summary (Context-Aware Dynamic Badges) -->
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <!-- Badge 1: Filtered Reports Count -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Filtered Reports:</span>
                    <span class="text-sm font-black text-slate-800 font-mono">{{ $reports->total() }}</span>
                </div>

                @if($status === 'all')
                    <!-- All Tab: Show Overview -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 font-mono">Total Submitted:</span>
                        <span class="text-sm font-black text-amber-700 font-mono">{{ $filteredSubmittedDuration }}</span>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-250 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 font-mono">Total Approved:</span>
                        <span class="text-sm font-black text-emerald-700 font-mono">{{ $filteredApprovedDuration }}</span>
                    </div>

                    <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-800 font-mono">Total Rejected:</span>
                        <span class="text-sm font-black text-rose-700 font-mono">{{ $filteredRejectedDuration }}</span>
                    </div>

                @elseif($status === 'pending')
                    <!-- Pending Tab -->
                    <div class="bg-yellow-50 border border-yellow-250 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-600"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-yellow-800 font-mono">Total Pending Duration:</span>
                        <span class="text-sm font-black text-yellow-800 font-mono">{{ $filteredSubmittedDuration }}</span>
                    </div>

                @elseif($status === 'on_review')
                    <!-- On Review Tab -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-800 font-mono">Total On Review Duration:</span>
                        <span class="text-sm font-black text-blue-800 font-mono">{{ $filteredSubmittedDuration }}</span>
                    </div>

                @elseif($status === 'approved')
                    <!-- Approved Tab -->
                    <div class="bg-emerald-50 border border-emerald-250 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 font-mono">Total Approved Duration:</span>
                        <span class="text-sm font-black text-emerald-700 font-mono">{{ $filteredApprovedDuration }}</span>
                    </div>

                @elseif($status === 'rejected')
                    <!-- Rejected Tab -->
                    <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-800 font-mono">Total Rejected Duration:</span>
                        <span class="text-sm font-black text-rose-700 font-mono">{{ $filteredRejectedDuration }}</span>
                    </div>
                @endif
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID Laporan</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mitra (Worker)</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Kerja</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durasi Kirim</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status QC</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($reports as $report)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600">
                                        {{ substr($report->id, 0, 8) }}...
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900">{{ $report->partner->full_name }}</span>
                                            <span class="text-xs text-gray-400">ID: {{ $report->partner->mitra_id }}</span>
                                            <span class="text-xs text-slate-400">{{ $report->partner->email ?: ($report->partner->user?->email ?: '-') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $report->submission_date->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ $report->submitted_duration_formatted }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->qc_status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-yellow-50 text-yellow-800 border-yellow-250">
                                                Pending
                                            </span>
                                        @elseif($report->qc_status === 'on_review')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-blue-50 text-blue-800 border-blue-200">
                                                On Review
                                            </span>
                                        @elseif($report->qc_status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-800 border-emerald-200">
                                                Approved ({{ $report->approved_duration_minutes }}m)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-rose-50 text-rose-800 border-rose-200">
                                                Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                @click="openReview(@js($report->load('partner')))"
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-700 hover:bg-indigo-100 text-xs font-bold tracking-wider transition">
                                                {{ in_array($report->qc_status, ['pending', 'on_review']) ? 'Review' : 'Detail' }}
                                            </button>

                                            @if(Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin')
                                                <button type="button" @click="openDelete(@js($report))" class="p-1.5 text-gray-400 hover:text-red-650 hover:bg-red-50 rounded-lg transition" title="Hapus Laporan">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="font-medium text-sm">Tidak ada data laporan video ditemukan untuk filter ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- Verification Modal -->
        <template x-if="showVerifyModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
                <div class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200 flex flex-col my-8">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-950 text-white flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div>
                                <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Review Laporan Kerja Video</span>
                                <h3 class="text-lg font-black" x-text="'Laporan: ' + (activeReport.id ? activeReport.id.substring(0, 8) + '...' : '')"></h3>
                            </div>
                            <!-- All-Time Cumulative Minutes Badge -->
                            <div class="bg-indigo-900 border border-indigo-800 px-3 py-1 rounded-xl flex flex-col items-center">
                                <span class="text-[8px] font-black uppercase text-indigo-300 font-mono tracking-wider">Database Cumulative</span>
                                <span class="text-xs font-black text-white font-mono mt-0.5" x-text="(activeReport.partner_total_submitted_minutes || 0) + ' Mins'"></span>
                            </div>
                        </div>
                        <button @click="showVerifyModal = false" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body / Content -->
                    <div class="p-6 space-y-6 flex-1 overflow-y-auto max-h-[70vh]">
                        <!-- Side-by-Side Evidence Images -->
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Evidence Submissions (Side-by-Side)</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-slate-50 border border-gray-200 rounded-2xl p-4 space-y-2 flex flex-col justify-between">
                                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                        <span class="text-sm font-bold text-slate-800">1. Screenshot total durasi di aplikasi</span>
                                        <span class="bg-indigo-50 text-indigo-700 text-xs px-2.5 py-0.5 rounded-full font-bold">Total Durasi</span>
                                    </div>
                                    <div class="mt-2 aspect-video bg-slate-200 rounded-xl overflow-hidden relative flex items-center justify-center border border-gray-300">
                                        <template x-if="activeReport.evidence_email_image_url && !emailImageFailed">
                                            <a :href="activeReport.evidence_email_image_url" target="_blank" class="block w-full h-full">
                                                <img :src="activeReport.evidence_email_image_url" x-on:error="emailImageFailed = true" class="object-contain w-full h-full bg-white" alt="Screenshot total durasi di aplikasi">
                                            </a>
                                        </template>
                                        <template x-if="!activeReport.evidence_email_image_url || emailImageFailed">
                                            <span class="text-xs font-semibold text-gray-400">File screenshot total durasi tidak ditemukan</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Diupload untuk memvalidasi total durasi kerja yang tampil di aplikasi.</p>
                                </div>

                                <div class="bg-slate-50 border border-gray-200 rounded-2xl p-4 space-y-2 flex flex-col justify-between">
                                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                        <span class="text-sm font-bold text-slate-800">2. Screenshot Bagian Kualitas di Aplikasi</span>
                                        <span class="bg-indigo-50 text-indigo-700 text-xs px-2.5 py-0.5 rounded-full font-bold">Kualitas</span>
                                    </div>
                                    <div class="mt-2 aspect-video bg-slate-200 rounded-xl overflow-hidden relative flex items-center justify-center border border-gray-300">
                                        <template x-if="activeReport.evidence_app_quality_image_url && !qualityImageFailed">
                                            <a :href="activeReport.evidence_app_quality_image_url" target="_blank" class="block w-full h-full">
                                                <img :src="activeReport.evidence_app_quality_image_url" x-on:error="qualityImageFailed = true" class="object-contain w-full h-full bg-white" alt="Screenshot bagian kualitas di aplikasi">
                                            </a>
                                        </template>
                                        <template x-if="!activeReport.evidence_app_quality_image_url || qualityImageFailed">
                                            <span class="text-xs font-semibold text-gray-400">File screenshot kualitas aplikasi tidak ditemukan</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Diupload untuk memvalidasi bagian kualitas rekaman yang tampil di aplikasi.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Brief info -->
                        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="block text-xs font-semibold text-indigo-400 uppercase">Nama Worker</span>
                                <span class="block font-bold text-slate-800" x-text="activeReport.partner.full_name"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-indigo-400 uppercase">ID Mitra</span>
                                <span class="block font-bold text-slate-800" x-text="activeReport.partner.mitra_id"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-indigo-400 uppercase">Tanggal Kerja</span>
                                <span class="block font-bold text-slate-800" x-text="activeReport.submission_date"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-indigo-400 uppercase">Durasi Dilaporkan</span>
                                <span class="block font-bold text-slate-800" x-text="activeReport.submitted_duration_minutes + ' Menit'"></span>
                            </div>
                        </div>

                        <!-- Verification Action Form (Show only if status is pending or on_review) -->
                        <template x-if="['pending', 'on_review'].includes(activeReport.qc_status)">
                            <form :action="'/qc-room/' + activeReport.id + '/verify'" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="action" :value="actionType">
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tentukan Aksi Verifikasi</label>
                                    
                                    <!-- Conditional Action Options depending on current status -->
                                    <div class="grid gap-3" :class="activeReport.qc_status === 'pending' ? 'grid-cols-2' : 'grid-cols-3'">
                                        <!-- If Pending: Can Reject directly or Move to On Review -->
                                        <button type="button" x-show="activeReport.qc_status === 'pending'"
                                                class="flex flex-col items-center justify-center p-3 border rounded-xl text-center transition-all duration-200 focus:outline-none"
                                                :class="actionType === 'start_review' ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                                @click="actionType = 'start_review'">
                                            <span class="block text-sm font-bold">Mulai Review</span>
                                            <span class="block text-xs text-gray-400 mt-0.5">Pindahkan ke ON REVIEW</span>
                                        </button>

                                        <button type="button" x-show="activeReport.qc_status === 'pending'"
                                                class="flex flex-col items-center justify-center p-3 border rounded-xl text-center transition-all duration-200 focus:outline-none"
                                                :class="actionType === 'reject' ? 'border-red-600 bg-red-50 text-red-750 font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                                @click="actionType = 'reject'">
                                            <span class="block text-sm font-bold">Reject Langsung</span>
                                            <span class="block text-xs text-gray-400 mt-0.5">Tolak Laporan langsung</span>
                                        </button>

                                        <!-- If On Review: Can Approve Full, Approve Partial, or Reject -->
                                        <button type="button" x-show="activeReport.qc_status === 'on_review'"
                                                class="flex flex-col items-center justify-center p-3 border rounded-xl text-center transition-all duration-200 focus:outline-none"
                                                :class="actionType === 'approve_full' ? 'border-indigo-650 bg-indigo-50 text-indigo-700 font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                                @click="actionType = 'approve_full'; approvedMinutes = activeReport.submitted_duration_minutes; if (notes === 'Penyesuaian durasi verifikasi. Total waktu disetujui telah disesuaikan dengan hasil validasi kualitas dan pembacaan durasi akhir pada sistem Minute Data.') { notes = '' }">
                                            <span class="block text-sm font-bold">Approve Penuh</span>
                                            <span class="block text-xs text-gray-400 mt-0.5" x-text="activeReport.submitted_duration_minutes + ' menit'"></span>
                                        </button>

                                        <button type="button" x-show="activeReport.qc_status === 'on_review'"
                                                class="flex flex-col items-center justify-center p-3 border rounded-xl text-center transition-all duration-200 focus:outline-none"
                                                :class="actionType === 'approve_partial' ? 'border-indigo-650 bg-indigo-50 text-indigo-700 font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                                @click="actionType = 'approve_partial'; if (!notes || notes === '') { notes = 'Penyesuaian durasi verifikasi. Total waktu disetujui telah disesuaikan dengan hasil validasi kualitas dan pembacaan durasi akhir pada sistem Minute Data.' }">
                                            <span class="block text-sm font-bold">Approve Sebagian</span>
                                            <span class="block text-xs text-gray-400 mt-0.5">Input durasi manual</span>
                                        </button>

                                        <button type="button" x-show="activeReport.qc_status === 'on_review'"
                                                class="flex flex-col items-center justify-center p-3 border rounded-xl text-center transition-all duration-200 focus:outline-none"
                                                :class="actionType === 'reject' ? 'border-red-600 bg-red-50 text-red-750 font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                                @click="actionType = 'reject'; if (notes === 'Penyesuaian durasi verifikasi. Total waktu disetujui telah disesuaikan dengan hasil validasi kualitas dan pembacaan durasi akhir pada sistem Minute Data.') { notes = '' }">
                                            <span class="block text-sm font-bold">Reject</span>
                                            <span class="block text-xs text-gray-400 mt-0.5">Video ditolak</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Conditional Fields -->
                                <div x-show="actionType === 'approve_partial'" class="animate-in slide-in-from-top-4 duration-200">
                                    <label for="approved_duration_minutes" class="block text-sm font-semibold text-gray-700 mb-1">Durasi yang Disetujui (Menit)</label>
                                    <input type="number" name="approved_duration_minutes" id="approved_duration_minutes" x-model="approvedMinutes" :max="activeReport.submitted_duration_minutes" min="0" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="text-xs text-gray-400 mt-1" x-text="'Maksimal durasi: ' + activeReport.submitted_duration_minutes + ' menit'"></p>
                                </div>

                                <div x-show="actionType === 'reject' || actionType === 'approve_partial'" class="space-y-3 animate-in slide-in-from-top-4 duration-200">
                                    <div>
                                        <label for="verifier_notes" class="block text-sm font-semibold text-gray-700 mb-1">Catatan Masukan / Alasan <span class="text-red-500">*</span></label>
                                        <textarea name="verifier_notes" id="verifier_notes" rows="3" x-model="notes" :required="actionType === 'reject' || actionType === 'approve_partial'" placeholder="Jelaskan alasan penolakan atau alasan hanya disetujui sebagian..." class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                    </div>
                                </div>

                                <!-- Modal Actions -->
                                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                                    <button type="button" @click="showVerifyModal = false" class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none transition">
                                        Batalkan
                                    </button>
                                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-750 text-white border border-transparent rounded-xl font-semibold text-sm focus:outline-none transition shadow-md shadow-indigo-100">
                                        Kirim Hasil QC
                                    </button>
                                </div>
                            </form>
                        </template>

                        <!-- Show notes if already verified (Approved/Rejected) -->
                        <template x-if="!['pending', 'on_review'].includes(activeReport.qc_status)">
                            <div class="space-y-4">
                                <div class="p-4 rounded-2xl border text-sm" :class="activeReport.qc_status === 'approved' ? 'bg-emerald-50 border-emerald-150 text-emerald-800' : 'bg-rose-50 border-rose-150 text-rose-800'">
                                    <span class="block font-bold text-xs uppercase tracking-wider mb-1">Hasil Verifikasi</span>
                                    <p class="font-medium">
                                        Status: <span class="font-black uppercase" x-text="activeReport.qc_status"></span>
                                    </p>
                                    <p class="mt-1" x-text="'Durasi Approved: ' + activeReport.approved_duration_minutes + ' menit'"></p>
                                    <template x-if="activeReport.verifier_notes">
                                        <p class="mt-2 pt-2 border-t border-black/5" x-text="'Catatan Verifikator: ' + activeReport.verifier_notes"></p>
                                    </template>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 pt-4 border-t border-gray-100">
                                    <div>
                                        <template x-if="activeReport.payment_status === 'paid'">
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-50 border border-rose-150 text-rose-800">
                                                Sudah Terbayar (Batal Bayar Dahulu)
                                            </span>
                                        </template>
                                        <template x-if="activeReport.payment_status !== 'paid'">
                                            <button type="button" @click="openRevert(activeReport)" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-150 text-amber-800 rounded-xl text-xs font-bold transition duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 6H16"/>
                                                </svg>
                                                Kembalikan ke Pending
                                            </button>
                                        </template>
                                    </div>
                                    <button type="button" @click="showVerifyModal = false" class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-semibold text-sm transition">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Custom Premium Delete Confirmation Modal -->
        <template x-if="showDeleteModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-[32px] max-w-md w-full shadow-2xl p-6 border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center gap-4 text-red-600 mb-4">
                        <div class="p-3 bg-red-50 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 leading-tight">Hapus Laporan Video?</h3>
                            <p class="text-xs text-gray-400 font-medium">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-550 leading-relaxed mb-6">
                        Apakah Anda yakin ingin menghapus laporan video dengan ID <strong class="text-slate-800 font-bold" x-text="activeReport.id ? activeReport.id.substring(0, 8) + '...' : ''"></strong> secara permanen dari sistem?
                    </p>

                    <!-- Modal Actions -->
                    <form :action="'/qc-room/' + activeReport.id" method="POST" class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold text-xs rounded-2xl hover:bg-gray-50 transition">
                            Batalkan
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-2xl shadow-sm transition">
                            Hapus Permanen
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Custom Premium Revert Confirmation Modal -->
        <template x-if="showRevertModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-[32px] max-w-md w-full shadow-2xl p-6 border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center gap-4 text-amber-600 mb-4">
                        <div class="p-3 bg-amber-50 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 6H16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 leading-tight">Batalkan Verifikasi?</h3>
                            <p class="text-xs text-gray-400 font-medium">Kembalikan ke antrean Pending</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-550 leading-relaxed mb-6">
                        Apakah Anda yakin ingin membatalkan status verifikasi laporan video dengan ID <strong class="text-slate-800 font-bold" x-text="activeReport.id ? activeReport.id.substring(0, 8) + '...' : ''"></strong> dan mengembalikannya ke status antrean **Pending**?
                    </p>

                    <!-- Modal Actions -->
                    <form :action="'/qc-room/' + activeReport.id + '/verify'" method="POST" class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                        @csrf
                        <input type="hidden" name="action" value="revert">
                        <button type="button" @click="showRevertModal = false" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold text-xs rounded-2xl hover:bg-gray-50 transition">
                            Batalkan
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-655 text-white font-bold text-xs rounded-2xl shadow-sm transition">
                            Ya, Kembalikan ke Pending
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
