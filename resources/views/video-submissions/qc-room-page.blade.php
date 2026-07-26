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
                            @foreach($periods as $p)
                                <option value="{{ $p['start']->format('Y-m-d') . '|' . $p['end']->format('Y-m-d') }}" 
                                    {{ $selectedPeriodKey === ($p['start']->format('Y-m-d') . '|' . $p['end']->format('Y-m-d')) ? 'selected' : '' }}>
                                    📅 {{ $p['label'] }}
                                </option>
                            @endforeach
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
                        @if($search)
                            <a href="{{ route('video-submissions.qc-room', ['period' => $selectedPeriodKey]) }}" class="flex-1 md:flex-none justify-center inline-flex items-center px-5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-200 transition-all">
                                Reset
                            </a>
                        @endif
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
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col" class="w-10 px-4 py-4"></th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mitra (Worker)</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Dilaporkan</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Durasi Disetujui (Menit)</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Catatan Masukan / Alasan</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-64">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($partners as $partner)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                    <!-- Toggle Accordion Button -->
                                    <td class="px-4 py-4 text-center">
                                        <button type="button" @click="togglePartner('{{ $partner->id }}')" class="p-1 text-gray-400 hover:text-indigo-650 hover:bg-slate-50 rounded-lg transition duration-200">
                                            <svg class="w-5 h-5 transform transition-transform duration-200" :class="isExpanded('{{ $partner->id }}') ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                    </td>
                                    
                                    <!-- Partner Demographics -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900">{{ $partner->full_name }}</span>
                                            <span class="text-xs text-gray-400">ID: {{ $partner->mitra_id }}</span>
                                            <span class="text-xs text-slate-400">{{ $partner->email ?: ($partner->user?->email ?: '-') }}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Total Reported Duration -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-slate-700 font-mono">
                                        {{ floor($partner->total_reported_minutes / 60) }}j {{ $partner->total_reported_minutes % 60 }}m
                                    </td>
                                    
                                    <!-- Inline Approval & Draft Input Form -->
                                    <td class="px-6 py-4" colspan="2">
                                        <form id="form-{{ $partner->id }}" method="POST" class="flex items-center gap-4">
                                            @csrf
                                            <input type="hidden" name="partner_id" value="{{ $partner->id }}">
                                            <input type="hidden" name="period_start_date" value="{{ $startDate->format('Y-m-d') }}">
                                            <input type="hidden" name="period_end_date" value="{{ $endDate->format('Y-m-d') }}">
                                            
                                            <!-- Approved Minutes Input -->
                                            <div class="w-32">
                                                <input type="number" name="approved_minutes" value="{{ old('approved_minutes', $partner->input_approved_minutes) }}" required min="0" 
                                                       {{ $partner->approval_status === 'paid' ? 'disabled' : '' }}
                                                       class="block w-full px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-center">
                                            </div>
                                            
                                            <!-- Verifier Notes Input -->
                                            <div class="flex-1">
                                                <input type="text" name="verifier_notes" value="{{ old('verifier_notes', $partner->input_verifier_notes) }}" placeholder="Catatan/Alasan (opsional)..."
                                                       {{ $partner->approval_status === 'paid' ? 'disabled' : '' }}
                                                       class="block w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                        </form>
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

                                    <!-- Action Buttons -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        @if($partner->approval_status !== 'paid')
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- Save Draft Button -->
                                                <button type="submit" form="form-{{ $partner->id }}" 
                                                        x-on:click.prevent="$el.form.action='{{ route('video-submissions.save-draft') }}'; $el.form.submit()"
                                                        class="px-3.5 py-1.5 bg-white hover:bg-slate-50 border border-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider rounded-lg transition duration-150">
                                                    Simpan Draf
                                                </button>
                                                
                                                <!-- Approve & Release Button -->
                                                <button type="submit" form="form-{{ $partner->id }}"
                                                        x-on:click.prevent="$el.form.action='{{ route('video-submissions.finalize') }}'; $el.form.submit()"
                                                        class="px-3.5 py-1.5 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-lg transition duration-150 shadow-sm">
                                                    Rilis & Approve
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Verifikasi Terkunci</span>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Expanded Daily Reports Accordion Detail -->
                                <tr x-show="isExpanded('{{ $partner->id }}')" x-cloak class="bg-slate-50/40">
                                    <td colspan="7" class="px-6 py-4">
                                        <div class="border border-slate-100 bg-white rounded-2xl p-4 shadow-sm">
                                            <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3 font-mono">Daftar Laporan Kerja Harian</h4>
                                            
                                            <div class="overflow-x-auto mt-2">
                                                <table class="min-w-full divide-y divide-gray-100">
                                                    <thead class="bg-slate-50">
                                                        <tr>
                                                            <th scope="col" class="px-4 py-2 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">ID Laporan</th>
                                                            <th scope="col" class="px-4 py-2 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Tanggal Kerja</th>
                                                            <th scope="col" class="px-4 py-2 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Durasi Kirim</th>
                                                            <th scope="col" class="px-4 py-2 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Status QC</th>
                                                            <th scope="col" class="px-4 py-2 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Aksi</th>
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
                                                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Approved ({{ $report->approved_duration_minutes }}m)</span>
                                                                    @elseif($report->qc_status === 'rejected')
                                                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">Rejected</span>
                                                                    @else
                                                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Review (Pending)</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 whitespace-nowrap text-center">
                                                                    <button type="button" 
                                                                            @click="activeDailyReport = {{ json_encode([
                                                                                'id' => $report->id,
                                                                                'date' => $report->submission_date->translatedFormat('d F Y'),
                                                                                'duration' => $report->submitted_duration_formatted,
                                                                                'status' => $report->qc_status === 'approved' ? 'Approved' : ($report->qc_status === 'rejected' ? 'Rejected' : 'Review (Pending)'),
                                                                                'approved_min' => $report->approved_duration_minutes,
                                                                                'email_img' => $report->evidence_email_image_url,
                                                                                'quality_img' => $report->evidence_app_quality_image_url,
                                                                                'device' => $report->device_type ?: '-',
                                                                                'headstrap' => $report->has_headstrap ? 'Ya' : 'Tidak',
                                                                                'notes' => $report->verifier_notes ?: 'Tidak ada catatan'
                                                                            ]) }}; showDetailModal = true" 
                                                                            class="inline-flex items-center px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[10px] font-bold transition shadow-xs">
                                                                        Detail
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
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

        <!-- Alpine Image Preview Modal Overlay -->
        <div x-show="showImageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm animate-fade-in" x-cloak style="display: none;">
            <div class="relative max-w-4xl w-full bg-white rounded-3xl overflow-hidden shadow-2xl p-2 border border-slate-100">
                <button type="button" @click="showImageModal = false" class="absolute top-4 right-4 z-10 p-2 bg-slate-900/60 hover:bg-slate-900 text-white rounded-full transition duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <img :src="previewImageUrl" class="w-full max-h-[80vh] object-contain rounded-2xl">
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
                            <span class="block font-bold text-slate-800 font-mono" x-text="activeDailyReport.id ? activeDailyReport.id.substring(0, 8) + '...' : ''"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Status QC</span>
                            <span class="block font-bold text-slate-800" x-text="activeDailyReport.status"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Durasi Kirim</span>
                            <span class="block font-bold text-slate-800" x-text="activeDailyReport.duration"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Durasi Disetujui</span>
                            <span class="block font-bold text-slate-800" x-text="activeDailyReport.approved_min + ' menit'"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Tipe Perangkat</span>
                            <span class="block font-bold text-slate-800" x-text="activeDailyReport.device"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Gunakan Headstrap</span>
                            <span class="block font-bold text-slate-800" x-text="activeDailyReport.headstrap"></span>
                        </div>
                        <div class="col-span-2 border-t border-gray-200 pt-2 mt-2">
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Catatan Masukan / Alasan</span>
                            <p class="block text-slate-800 mt-1 leading-relaxed font-medium" x-text="activeDailyReport.notes"></p>
                        </div>
                    </div>

                    <!-- Proof/Evidence Image Grid -->
                    <div class="space-y-3">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider font-mono">Foto Bukti (SOP)</span>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Email image -->
                            <div class="space-y-1.5" x-show="activeDailyReport.email_img">
                                <span class="block text-[9px] text-gray-400 uppercase font-mono">Bukti Email Register</span>
                                <button type="button" @click="previewImageUrl = activeDailyReport.email_img; showImageModal = true" class="relative group block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img :src="activeDailyReport.email_img" class="w-full h-24 object-cover group-hover:scale-105 transition duration-150">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                            
                            <!-- Quality image -->
                            <div class="space-y-1.5" x-show="activeDailyReport.quality_img">
                                <span class="block text-[9px] text-gray-400 uppercase font-mono">Bukti Kualitas Video</span>
                                <button type="button" @click="previewImageUrl = activeDailyReport.quality_img; showImageModal = true" class="relative group block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img :src="activeDailyReport.quality_img" class="w-full h-24 object-cover group-hover:scale-105 transition duration-150">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </div>
                                </button>
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
    </div>
</x-app-layout>
