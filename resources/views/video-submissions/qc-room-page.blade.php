<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('QC Video Room') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ 
        showVerifyModal: false,
        showDeleteModal: false,
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
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-100 flex items-center gap-2 animate-bounce" role="alert">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">{{ session('success') }}</span>
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
                        <div>
                            <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Review Laporan Kerja Video</span>
                            <h3 class="text-lg font-black" x-text="'Laporan: ' + activeReport.id.substring(0, 8) + '...'"></h3>
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
                                        <span class="text-sm font-bold text-slate-800">1. Bukti Gambar Email</span>
                                        <span class="bg-indigo-50 text-indigo-700 text-xs px-2.5 py-0.5 rounded-full font-bold">Email Evidence</span>
                                    </div>
                                    <div class="mt-2 aspect-video bg-slate-200 rounded-xl overflow-hidden relative flex items-center justify-center border border-gray-300">
                                        <template x-if="activeReport.evidence_email_image_url && !emailImageFailed">
                                            <a :href="activeReport.evidence_email_image_url" target="_blank" class="block w-full h-full">
                                                <img :src="activeReport.evidence_email_image_url" x-on:error="emailImageFailed = true" class="object-contain w-full h-full bg-white" alt="Bukti gambar email">
                                            </a>
                                        </template>
                                        <template x-if="!activeReport.evidence_email_image_url || emailImageFailed">
                                            <span class="text-xs font-semibold text-gray-400">File bukti email tidak ditemukan</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Diupload untuk memvalidasi data pengiriman dari e-mail resmi Minutes.</p>
                                </div>

                                <div class="bg-slate-50 border border-gray-200 rounded-2xl p-4 space-y-2 flex flex-col justify-between">
                                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                        <span class="text-sm font-bold text-slate-800">2. Bukti Kualitas Aplikasi</span>
                                        <span class="bg-indigo-50 text-indigo-700 text-xs px-2.5 py-0.5 rounded-full font-bold">App Quality</span>
                                    </div>
                                    <div class="mt-2 aspect-video bg-slate-200 rounded-xl overflow-hidden relative flex items-center justify-center border border-gray-300">
                                        <template x-if="activeReport.evidence_app_quality_image_url && !qualityImageFailed">
                                            <a :href="activeReport.evidence_app_quality_image_url" target="_blank" class="block w-full h-full">
                                                <img :src="activeReport.evidence_app_quality_image_url" x-on:error="qualityImageFailed = true" class="object-contain w-full h-full bg-white" alt="Bukti kualitas aplikasi">
                                            </a>
                                        </template>
                                        <template x-if="!activeReport.evidence_app_quality_image_url || qualityImageFailed">
                                            <span class="text-xs font-semibold text-gray-400">File bukti kualitas aplikasi tidak ditemukan</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Tangkap layar dashboard Minutes Data Apps untuk memvalidasi durasi log.</p>
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
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tentukan Aksi Verifikasi</label>
                                    
                                    <!-- Conditional Action Options depending on current status -->
                                    <div class="grid gap-3" :class="activeReport.qc_status === 'pending' ? 'grid-cols-2' : 'grid-cols-3'">
                                        <!-- If Pending: Can Reject directly or Move to On Review -->
                                        <template x-if="activeReport.qc_status === 'pending'">
                                            <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                                   :class="actionType === 'start_review' ? 'border-blue-600 bg-blue-50/50 text-blue-700 font-bold' : 'border-gray-200 text-gray-600'">
                                                <input type="radio" name="action" value="start_review" class="sr-only" x-model="actionType">
                                                <span class="block text-sm font-bold">Mulai Review</span>
                                                <span class="block text-xs text-gray-400 mt-1">Pindahkan ke ON REVIEW</span>
                                            </label>
                                        </template>

                                        <template x-if="activeReport.qc_status === 'pending'">
                                            <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                                   :class="actionType === 'reject' ? 'border-red-600 bg-red-50/50 text-red-750 font-bold' : 'border-gray-200 text-gray-600'">
                                                <input type="radio" name="action" value="reject" class="sr-only" x-model="actionType">
                                                <span class="block text-sm font-bold">Reject Langsung</span>
                                                <span class="block text-xs text-gray-400 mt-1">Tolak Laporan langsung</span>
                                            </label>
                                        </template>

                                        <!-- If On Review: Can Approve Full, Approve Partial, or Reject -->
                                        <template x-if="activeReport.qc_status === 'on_review'">
                                            <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                                   :class="actionType === 'approve_full' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-gray-200 text-gray-600'">
                                                <input type="radio" name="action" value="approve_full" class="sr-only" x-model="actionType" @change="approvedMinutes = activeReport.submitted_duration_minutes">
                                                <span class="block text-sm font-bold">Approve Penuh</span>
                                                <span class="block text-xs text-gray-400 mt-1" x-text="activeReport.submitted_duration_minutes + ' menit' "></span>
                                            </label>
                                        </template>

                                        <template x-if="activeReport.qc_status === 'on_review'">
                                            <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                                   :class="actionType === 'approve_partial' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-gray-200 text-gray-600'">
                                                <input type="radio" name="action" value="approve_partial" class="sr-only" x-model="actionType">
                                                <span class="block text-sm font-bold">Approve Sebagian</span>
                                                <span class="block text-xs text-gray-400 mt-1">Input durasi manual</span>
                                            </label>
                                        </template>

                                        <template x-if="activeReport.qc_status === 'on_review'">
                                            <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                                   :class="actionType === 'reject' ? 'border-red-600 bg-red-50/50 text-red-750' : 'border-gray-200 text-gray-600'">
                                                <input type="radio" name="action" value="reject" class="sr-only" x-model="actionType">
                                                <span class="block text-sm font-bold">Reject</span>
                                                <span class="block text-xs text-gray-400 mt-1">Video ditolak</span>
                                            </label>
                                        </template>
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
                                <div class="flex justify-end pt-4 border-t border-gray-100">
                                    <button type="button" @click="showVerifyModal = false" class="inline-flex items-center px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-semibold text-sm transition">
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
    </div>
</x-app-layout>
