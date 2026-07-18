<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Riwayat Pembayaran Gaji') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ 
        showProofModal: false,
        proofUrl: '',
        imageFailed: false,
        
        openProofModal(url) {
            this.proofUrl = url;
            this.showProofModal = true;
            this.imageFailed = false;
        }
    }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Back navigation link -->
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Gaji & Payouts</span>
            </div>

            <!-- Page Header Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900 leading-tight">Riwayat Penerimaan Gaji</h3>
                    <p class="text-xs text-gray-400 mt-1">Daftar lengkap bukti transfer dan rincian laporan yang sudah dibayarkan untuk profil Anda.</p>
                </div>
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-2 text-indigo-700 text-xs font-bold">
                    Rate: Rp {{ number_format($partner->base_hourly_rate ?: 54000, 0, ',', '.') }} / Jam
                </div>
            </div>

            <!-- Payments List Accordions -->
            <div class="space-y-4">
                @forelse($payments as $index => $pay)
                    <div x-data="{ open: false }" class="bg-white border border-gray-150 rounded-2xl overflow-hidden shadow-sm transition" :class="open ? 'border-indigo-200 shadow-md' : 'hover:shadow-sm'">
                        <!-- Header -->
                        <div class="p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 cursor-pointer select-none" @click="open = !open">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Transfer</span>
                                    <span class="block text-sm font-black text-slate-800">{{ $pay['paid_at']->translatedFormat('d F Y - H:i') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 w-full sm:w-auto justify-between sm:justify-end">
                                <div class="text-left sm:text-right">
                                    <span class="block text-[9px] font-bold text-gray-450 uppercase tracking-wider">Total Diterima</span>
                                    <span class="block text-base font-black text-slate-900 leading-tight">Rp {{ number_format($pay['total_amount'], 0, ',', '.') }}</span>
                                    <span class="block text-[9px] font-medium text-gray-400" x-text="'Untuk ' + {{ count($pay['reports']) }} + ' Laporan' "></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($pay['proof_url'])
                                        <button type="button" 
                                                @click.stop="openProofModal('{{ $pay['proof_url'] }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Bukti Transfer
                                        </button>
                                    @endif
                                    <div class="p-1 text-gray-450 rounded-lg transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Body (Included Work Reports) -->
                        <div x-show="open" x-collapse x-cloak>
                            <div class="px-5 pb-5 border-t border-gray-100 bg-slate-50/50">
                                <div class="overflow-x-auto mt-3">
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
                    <div class="bg-white rounded-2xl p-12 text-center border border-gray-150 shadow-sm space-y-2">
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
                <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200 flex flex-col my-8">
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
                    <div class="p-6 bg-slate-50 flex items-center justify-center min-h-[300px] max-h-[70vh] overflow-y-auto">
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
