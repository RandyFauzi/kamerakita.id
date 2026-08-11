<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
            {{ __('Riwayat Pembayaran Gaji') }}
        </h2>
    </x-slot>

    <div class="py-2 sm:py-8" x-data="{
        showProofModal: false,
        proofUrl: '',
        imageFailed: false,
        
        openProofModal(url) {
            this.proofUrl = url;
            this.showProofModal = true;
            this.imageFailed = false;
        }
    }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Back navigation link -->
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <span class="text-[10px] sm:text-xs text-gray-400 font-semibold uppercase tracking-wider">Riwayat Gaji</span>
            </div>

            <!-- Page Header Card -->
            <div class="overflow-hidden shadow-sm rounded-2xl sm:rounded-3xl p-4 sm:p-6 text-white relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);">
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="space-y-1">
                        <span class="bg-indigo-500/20 text-indigo-300 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Riwayat Transfer</span>
                        <h3 class="text-xl font-black tracking-tight">Riwayat Penerimaan Gaji</h3>
                        <p class="text-xs text-slate-350 max-w-xl leading-normal">Daftar lengkap bukti transfer bank dan rincian laporan kerja harian Anda yang telah dibayarkan oleh Finance.</p>
                    </div>
                    <div class="w-full md:w-auto bg-white/10 backdrop-blur-md border border-white/10 rounded-xl sm:rounded-2xl px-4 py-2.5 text-xs text-indigo-200 font-bold shrink-0">
                        Rate Anda: <span class="text-white font-black">Rp {{ number_format($partner->base_hourly_rate ?: 54000, 0, ',', '.') }}</span> / Jam
                    </div>
                </div>
                <!-- Premium subtle background glows -->
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>
            </div>

            <!-- Payments List Accordions -->
            <div class="space-y-4">
                @forelse($payments as $index => $pay)
                    <div x-data="{ open: false }" class="border rounded-2xl overflow-hidden shadow-sm transition {{ $pay['has_custom_rate'] ? 'bg-amber-50/20 border-amber-200' : 'bg-white border-gray-150' }}" :class="open ? '{{ $pay['has_custom_rate'] ? 'border-amber-400 shadow-md' : 'border-indigo-200 shadow-md' }}' : 'hover:shadow-sm'">
                        <!-- Header -->
                        <div class="p-4 sm:p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 cursor-pointer select-none" @click="open = !open">
                            <!-- Left: Date info & Icon -->
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-11 h-11 sm:w-12 sm:h-12 {{ $pay['has_custom_rate'] ? 'bg-amber-100 border-amber-200 text-amber-600' : 'bg-emerald-50 border-emerald-100 text-emerald-600' }} border rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="space-y-0.5">
                                    <span class="block text-[10px] font-bold {{ $pay['has_custom_rate'] ? 'text-amber-600' : 'text-emerald-600' }} uppercase tracking-wider">Transfer Selesai</span>
                                    <span class="block text-sm font-black text-slate-800">{{ $pay['paid_at']->translatedFormat('d F Y - H:i') }}</span>
                                </div>
                            </div>

                            <!-- Right: Nominal, Proof button and expand indicator -->
                            <div class="flex items-end gap-3 w-full md:w-auto justify-between md:justify-end pt-3 md:pt-0 border-t border-gray-100 md:border-0">
                                <div class="text-left md:text-right">
                                    <span class="block text-[9px] font-bold text-gray-455 uppercase tracking-wider">Total Gaji Diterima</span>
                                    <div class="flex items-center gap-2 justify-start md:justify-end">
                                        @if($pay['has_custom_rate'])
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded border border-amber-200">Rate Khusus</span>
                                        @endif
                                        <span class="block text-lg font-black {{ $pay['has_custom_rate'] ? 'text-amber-600' : 'text-slate-900' }} leading-tight">Rp {{ number_format($pay['total_amount'], 0, ',', '.') }}</span>
                                    </div>
                                    <span class="block text-[9px] font-semibold text-gray-400 mt-0.5" x-text="'Untuk ' + {{ count($pay['reports']) }} + ' Laporan' "></span>
                                </div>

                                <div class="flex items-center gap-3">
                                    @if($pay['proof_url'])
                                        <button type="button" 
                                                @click.stop="openProofModal('{{ $pay['proof_url'] }}')"
                                                class="min-h-10 inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 text-indigo-750 rounded-xl text-xs font-bold transition duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Bukti Transfer
                                        </button>
                                    @endif
                                    <div class="p-1.5 text-gray-450 hover:bg-slate-50 rounded-lg transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Body (Included Work Reports) -->
                        <div x-show="open" x-collapse x-cloak>
                            <div class="px-4 sm:px-5 pb-4 sm:pb-5 border-t border-gray-100 bg-slate-50/50">
                                <div class="space-y-2 pt-3 sm:hidden">
                                    @foreach($pay['reports'] as $report)
                                        <div class="bg-white rounded-xl border border-gray-150 p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <span class="block text-xs font-bold text-slate-800">{{ $report->submission_date->translatedFormat('d F Y') }}</span>
                                                    <span class="block font-mono text-[10px] text-gray-400 mt-0.5">{{ substr($report->id, 0, 8) }}...</span>
                                                </div>
                                                <span class="inline-flex px-2 py-1 rounded-full text-[9px] font-black uppercase border bg-emerald-50 border-emerald-150 text-emerald-800">Dibayar</span>
                                            </div>
                                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 text-xs">
                                                <span class="text-gray-400">Durasi disetujui</span>
                                                <strong class="text-slate-800">{{ $report->approved_duration_formatted }}</strong>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="hidden sm:block overflow-x-auto mt-3">
                                    <table class="min-w-full divide-y divide-gray-200/60 text-xs">
                                        <thead>
                                            <tr class="text-gray-450 font-bold text-left uppercase tracking-wider">
                                                <th class="pb-2">ID Laporan</th>
                                                <th class="pb-2">Tanggal Kerja</th>
                                                <th class="pb-2">Durasi Kerja</th>
                                                <th class="pb-2 text-right">Status Bayar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 font-medium text-slate-700">
                                            @foreach($pay['reports'] as $report)
                                                <tr>
                                                    <td class="py-2.5 font-mono text-gray-450">{{ substr($report->id, 0, 8) }}...</td>
                                                    <td class="py-2.5">{{ $report->submission_date->translatedFormat('d F Y') }}</td>
                                                    <td class="py-2.5 font-bold">{{ $report->approved_duration_formatted }}</td>
                                                    <td class="py-2.5 text-right">
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border bg-emerald-50 border-emerald-150 text-emerald-800">
                                                            Paid
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
                    <div class="bg-white rounded-2xl p-8 sm:p-12 text-center border border-gray-150 shadow-sm space-y-2">
                        <div class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-center mx-auto text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">Belum Ada Riwayat Pembayaran</h4>
                        <p class="text-xs text-gray-450 max-w-xs mx-auto leading-relaxed">
                            Semua laporan Anda yang telah disetujui (Approved) saat ini masih berada dalam antrean pembayaran admin.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Transfer Proof Viewer Modal -->
        <template x-if="showProofModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200 flex flex-col my-4 sm:my-8">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-950 text-white flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-black tracking-tight">Bukti Transfer Pembayaran</h3>
                        </div>
                        <button @click="showProofModal = false" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body (Image Container) -->
                    <div class="p-3 sm:p-6 bg-slate-50 flex items-center justify-center min-h-[240px] sm:min-h-[300px] max-h-[70vh] overflow-y-auto">
                        <template x-if="proofUrl && !imageFailed">
                            <a :href="proofUrl" target="_blank" class="block max-w-full">
                                <img :src="proofUrl" x-on:error="imageFailed = true" class="object-contain max-h-[60vh] rounded-2xl border border-gray-250 bg-white" alt="Bukti Transfer">
                            </a>
                        </template>
                        <template x-if="!proofUrl || imageFailed">
                            <div class="text-center text-gray-400 text-xs font-semibold">
                                Gambar bukti transfer tidak dapat dimuat atau telah kedaluwarsa.
                            </div>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="showProofModal = false" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
