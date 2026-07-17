<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('QC Video Room') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ 
        showVerifyModal: false,
        activeReport: {},
        actionType: 'approve_full',
        approvedMinutes: '',
        notes: '',
        openReview(report) {
            this.activeReport = report;
            this.showVerifyModal = true;
            this.actionType = 'approve_full';
            this.approvedMinutes = report.submitted_duration_minutes;
            this.notes = '';
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

            <!-- Filter & Search Card -->
            <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                <form action="{{ route('video-submissions.qc-room') }}" method="GET" class="flex gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Cari berdasarkan Nama Mitra, ID Mitra, atau ID Laporan..." class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-gray-800 transition-colors duration-200">
                        Cari
                    </button>
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
                            @forelse($pendingReports as $report)
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
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-yellow-50 text-yellow-800 border-yellow-200">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button 
                                            @click="openReview({{ json_encode($report->load('partner')) }})" 
                                            class="inline-flex items-center px-3.5 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-700 hover:bg-indigo-100 text-xs font-bold tracking-wider transition-colors duration-200">
                                            Review
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="font-medium text-sm">Semua video telah selesai diverifikasi! Tidak ada antrean pending.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pendingReports->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $pendingReports->links() }}
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
                                        <template x-if="activeReport.evidence_email_image_url">
                                            <a :href="activeReport.evidence_email_image_url" target="_blank" class="block w-full h-full">
                                                <img :src="activeReport.evidence_email_image_url" class="object-contain w-full h-full bg-white" alt="Bukti gambar email">
                                            </a>
                                        </template>
                                        <template x-if="!activeReport.evidence_email_image_url">
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
                                        <template x-if="activeReport.evidence_app_quality_image_url">
                                            <a :href="activeReport.evidence_app_quality_image_url" target="_blank" class="block w-full h-full">
                                                <img :src="activeReport.evidence_app_quality_image_url" class="object-contain w-full h-full bg-white" alt="Bukti kualitas aplikasi">
                                            </a>
                                        </template>
                                        <template x-if="!activeReport.evidence_app_quality_image_url">
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

                        <!-- Verification Action Form -->
                        <form :action="'/qc-room/' + activeReport.id + '/verify'" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tentukan Aksi Verifikasi</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                           :class="actionType === 'approve_full' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-gray-200 text-gray-600'">
                                        <input type="radio" name="action" value="approve_full" class="sr-only" x-model="actionType" @change="approvedMinutes = activeReport.submitted_duration_minutes">
                                        <span class="block text-sm font-bold">Approve Penuh</span>
                                        <span class="block text-xs text-gray-400 mt-1" x-text="activeReport.submitted_duration_minutes + ' menit' "></span>
                                    </label>

                                    <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                           :class="actionType === 'approve_partial' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-gray-200 text-gray-600'">
                                        <input type="radio" name="action" value="approve_partial" class="sr-only" x-model="actionType">
                                        <span class="block text-sm font-bold">Approve Sebagian</span>
                                        <span class="block text-xs text-gray-400 mt-1">Input durasi manual</span>
                                    </label>

                                    <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer text-center hover:bg-slate-50 transition-colors"
                                           :class="actionType === 'reject' ? 'border-rose-600 bg-rose-50/50 text-rose-700' : 'border-gray-200 text-gray-600'">
                                        <input type="radio" name="action" value="reject" class="sr-only" x-model="actionType">
                                        <span class="block text-sm font-bold">Reject</span>
                                        <span class="block text-xs text-gray-400 mt-1">Video ditolak</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Conditional Fields -->
                            <div x-show="actionType === 'approve_partial'" class="animate-in slide-in-from-top-4 duration-200">
                                <label for="approved_duration_minutes" class="block text-sm font-semibold text-gray-700 mb-1">Durasi yang Disetujui (Menit)</label>
                                <input type="number" name="approved_duration_minutes" id="approved_duration_minutes" x-model="approvedMinutes" :max="activeReport.submitted_duration_minutes" min="0" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-xs text-gray-400 mt-1" x-text="'Maksimal durasi: ' + activeReport.submitted_duration_minutes + ' menit'"></p>
                            </div>

                            <div x-show="actionType === 'reject'" class="space-y-3 animate-in slide-in-from-top-4 duration-200">
                                <div>
                                    <label for="verifier_notes" class="block text-sm font-semibold text-gray-700 mb-1">Catatan Penolakan / Masukan <span class="text-red-500">*</span></label>
                                    <textarea name="verifier_notes" id="verifier_notes" rows="3" x-model="notes" :required="actionType === 'reject'" placeholder="Jelaskan alasan penolakan secara spesifik (misal: gambar kabur, e-mail tidak cocok)..." class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
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
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
