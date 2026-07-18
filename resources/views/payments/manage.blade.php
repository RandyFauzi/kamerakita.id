<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Manajemen Pembayaran Gaji') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ 
        showPayModal: false,
        activeWorker: {},
        evidenceImage: null,
        notes: '',
        copySuccess: false,
        
        openPayModal(worker) {
            this.activeWorker = worker;
            this.showPayModal = true;
            this.copySuccess = false;
        },
        
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.copySuccess = true;
                setTimeout(() => this.copySuccess = false, 2000);
            } catch (err) {
                console.error('Gagal menyalin rekening: ', err);
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success / Error Notification Toast -->
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

            <!-- Page Header Card -->
            <div class="overflow-hidden shadow-sm sm:rounded-3xl p-8 text-white relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);">
                <div class="relative z-10 space-y-2 max-w-2xl">
                    <span class="bg-indigo-500/20 text-indigo-300 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Payroll Desk</span>
                    <h3 class="text-2xl font-black tracking-tight">Antrean Pembayaran Gaji Mitra (Worker)</h3>
                    <p class="text-xs text-slate-350 leading-relaxed">
                        Halaman ini mengelompokkan seluruh laporan kerja harian harian yang telah disetujui (Approved) dan menunggu transfer pembayaran. Selesaikan pembayaran secara kolektif per worker, salin rekening, dan unggah bukti transfer.
                    </p>
                </div>
                <!-- Premium subtle background glows -->
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>
            </div>

            <!-- Workers Payout Accordion Queue -->
            <div class="space-y-4">
                @forelse($workers as $index => $w)
                    <div x-data="{ expanded: false }" class="bg-white rounded-2xl border border-gray-150 shadow-sm overflow-hidden transition-all duration-300" :class="expanded ? 'shadow-md border-indigo-200' : 'hover:shadow-sm'">
                        <!-- Accordion Header -->
                        <div class="p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 cursor-pointer select-none" @click="expanded = !expanded">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 border border-gray-200 rounded-xl flex items-center justify-center font-black text-lg text-slate-700">
                                    {{ strtoupper(substr($w['partner']->full_name, 0, 2)) }}
                                </div>
                                <div class="space-y-0.5">
                                    <h4 class="text-base font-black text-slate-900">{{ $w['partner']->full_name }}</h4>
                                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-400 font-medium">
                                        <span>ID: {{ $w['partner']->mitra_id }}</span>
                                        <span>•</span>
                                        <span>Rek: {{ $w['partner']->bank_name ?? 'BCA' }} ({{ $w['partner']->bank_account_number ?? $w['partner']->account_number ?? '-' }})</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                                <div class="text-left md:text-right">
                                    <span class="block text-[10px] font-bold text-gray-450 uppercase tracking-wider">Total Tagihan</span>
                                    <span class="block text-lg font-black text-indigo-600 leading-tight">Rp {{ number_format($w['total_amount'], 0, ',', '.') }}</span>
                                    <span class="block text-[10px] font-medium text-gray-400" x-text="'Untuk ' + {{ count($w['reports']) }} + ' Laporan' "></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click.stop="openPayModal(@js($w))"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-750 text-white rounded-xl text-xs font-bold tracking-wider transition shadow-sm shadow-indigo-100">
                                        Proses Bayar
                                    </button>
                                    <div class="p-1 text-gray-400 hover:bg-gray-50 rounded-lg transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Body (Approved Reports List) -->
                        <div x-show="expanded" x-collapse x-cloak>
                            <div class="px-6 pb-6 border-t border-gray-100 bg-slate-50/50">
                                <div class="overflow-x-auto mt-4">
                                    <table class="min-w-full divide-y divide-gray-200/60 text-xs">
                                        <thead>
                                            <tr class="text-gray-450 font-bold text-left uppercase tracking-wider">
                                                <th class="pb-3 pt-2">ID Laporan</th>
                                                <th class="pb-3 pt-2">Tanggal Kerja</th>
                                                <th class="pb-3 pt-2">Durasi Disetujui</th>
                                                <th class="pb-3 pt-2">Est. Gaji (Rp)</th>
                                                <th class="pb-3 pt-2 text-right">Status QC</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 font-medium text-slate-700">
                                            @foreach($w['reports'] as $report)
                                                <tr>
                                                    <td class="py-3 font-mono text-gray-500">{{ substr($report->id, 0, 8) }}...</td>
                                                    <td class="py-3">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                                    <td class="py-3 font-bold">{{ $report->approved_duration_formatted }}</td>
                                                    <td class="py-3">
                                                        Rp {{ number_format(round(($report->approved_duration_minutes / 60) * $w['rate']), 0, ',', '.') }}
                                                    </td>
                                                    <td class="py-3 text-right">
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border bg-emerald-50 border-emerald-150 text-emerald-800">
                                                            Approved
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl p-16 text-center border border-gray-150 shadow-sm space-y-4 max-w-lg mx-auto mt-6">
                        <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center justify-center mx-auto text-emerald-500 shadow-sm shadow-emerald-50">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-base font-black text-slate-800">Antrean Pembayaran Bersih</h4>
                            <p class="text-xs text-gray-400 leading-relaxed">
                                Tidak ada antrean pembayaran saat ini. Semua laporan harian video yang disetujui (Approved) telah diselesaikan pembayarannya.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Payment Process Modal -->
        <template x-if="showPayModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
                <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200 flex flex-col my-8">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-950 text-white flex justify-between items-center">
                        <div>
                            <span class="text-[10px] uppercase font-black text-indigo-400 tracking-widest">Kirim Gaji Kolektif</span>
                            <h3 class="text-base font-black leading-tight" x-text="activeWorker.partner.full_name"></h3>
                        </div>
                        <button @click="showPayModal = false" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form :action="'/payments/manage/' + activeWorker.partner.id + '/pay'" method="POST" enctype="multipart/form-data" class="flex-1 p-6 space-y-5">
                        @csrf
                        
                        <!-- Earnings Summary Card -->
                        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 text-white rounded-2xl p-5 shadow-inner flex justify-between items-center">
                            <div>
                                <span class="block text-[9px] uppercase tracking-wider text-indigo-200 font-bold">Total Nilai Transfer</span>
                                <span class="block text-2xl font-black tracking-tight" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(activeWorker.total_amount)"></span>
                                <span class="block text-[10px] text-indigo-100 mt-1" x-text="'Total Menit: ' + activeWorker.total_minutes + ' m (' + Number(activeWorker.hours).toFixed(2) + ' jam)'"></span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[9px] uppercase tracking-wider text-indigo-200 font-bold">Rate / Jam</span>
                                <span class="block text-sm font-black" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(activeWorker.rate)"></span>
                            </div>
                        </div>

                        <!-- Bank Detail Copy Box -->
                        <div class="bg-slate-50 border border-gray-200 rounded-2xl p-4 space-y-3">
                            <span class="block text-[10px] font-bold text-gray-450 uppercase tracking-wider">Rekening Tujuan</span>
                            
                            <div class="grid grid-cols-2 gap-4 text-xs font-semibold text-slate-700">
                                <div>
                                    <span class="block text-[9px] text-gray-400 font-normal uppercase">Nama Bank</span>
                                    <span class="block font-bold text-slate-800" x-text="activeWorker.partner.bank_name || 'BCA'"></span>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-gray-400 font-normal uppercase">Pemilik Rekening</span>
                                    <span class="block font-bold text-slate-800" x-text="activeWorker.partner.bank_account_owner || activeWorker.partner.account_owner_name || activeWorker.partner.full_name"></span>
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl p-3 flex justify-between items-center shadow-sm">
                                <div class="space-y-0.5">
                                    <span class="block text-[9px] text-gray-400 uppercase leading-none">Nomor Rekening</span>
                                    <span class="font-mono text-sm font-black tracking-wider text-indigo-650" x-text="activeWorker.partner.bank_account_number || activeWorker.partner.account_number || '-'"></span>
                                </div>
                                <button type="button" 
                                        @click="copyToClipboard(activeWorker.partner.bank_account_number || activeWorker.partner.account_number)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-indigo-100 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-bold hover:bg-indigo-100 transition duration-150">
                                    <span x-text="copySuccess ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Upload Proof Input -->
                        <div class="space-y-1.5">
                            <label for="evidence_payment_proof" class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Unggah Bukti Transfer <span class="text-red-500">*</span></label>
                            <input type="file" name="evidence_payment_proof" id="evidence_payment_proof" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition file:cursor-pointer">
                            <p class="text-[10px] text-gray-400">Pastikan bukti transfer mencantumkan nominal transfer yang tepat dan nama rekening tujuan yang sesuai.</p>
                        </div>

                        <!-- Admin Fee Note -->
                        <div class="bg-orange-50/50 border border-orange-100/50 rounded-xl p-3 flex gap-2">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-orange-550" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-[10px] text-orange-800 leading-normal font-medium">
                                Biaya admin transfer antar bank (LLG / RTGS / BI-Fast) sebesar <strong>Rp 2.500</strong> sepenuhnya dibebankan kepada penerima (Mitra).
                            </p>
                        </div>

                        <!-- Modal Action Buttons -->
                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" @click="showPayModal = false" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 transition">
                                Batalkan
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-750 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-100 transition">
                                Konfirmasi & Bayar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
