<x-app-layout>
    <div class="space-y-4 sm:space-y-6" x-data="{ showBanner: true }">

        {{-- Top Banner --}}
        <template x-if="showBanner">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-150 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex gap-3 sm:gap-4 items-start pr-8 md:pr-0">
                    <div class="p-2.5 sm:p-3 bg-violet-50 text-violet-600 rounded-xl sm:rounded-2xl shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <span class="block text-xs font-black tracking-widest text-violet-600 uppercase">REKRUTER — PROGRAM KOMISI</span>
                        <p class="text-sm text-gray-500 max-w-xl">Anda terdaftar sebagai Rekruter. Setiap Worker yang Anda rekrut berhasil mencapai <strong>20 jam video approved</strong>, Anda mendapat komisi <strong>Rp 100.000</strong>.</p>
                        <div class="w-full bg-violet-100 h-2 rounded-full mt-3 overflow-hidden">
                            <div class="bg-violet-600 h-full rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-emerald-50 text-emerald-800 text-[10px] font-black px-3 py-1.5 rounded-full uppercase border border-emerald-100">Aktif</span>
                    <button type="button" aria-label="Tutup pemberitahuan" @click="showBanner = false" class="absolute top-4 right-4 w-9 h-9 inline-flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Referral Code — Prominent Banner --}}
        @if($partner->referral_code)
        <div class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl sm:rounded-3xl p-5 sm:p-7 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 shadow-xl">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-white/20 rounded-2xl shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-[10px] font-black tracking-widest text-violet-200 uppercase mb-1">Kode Referral Anda</span>
                    <span class="block text-3xl sm:text-4xl font-black text-white font-mono tracking-widest">{{ $partner->referral_code }}</span>
                    <span class="block text-xs text-violet-200 mt-2">Minta calon Worker mengisi kode ini saat mendaftar → mereka otomatis terhubung ke Anda.</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 w-full sm:w-auto">
                <button
                    onclick="navigator.clipboard.writeText('{{ $partner->referral_code }}').then(() => { this.textContent = '✓ Kode Tersalin!'; setTimeout(() => this.textContent = 'Salin Kode', 2000) })"
                    class="px-6 py-3 bg-white text-violet-700 font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-violet-50 transition shadow text-center">
                    Salin Kode
                </button>
                <a href="https://wa.me/?text=Halo%21+Daftarkan+diri+kamu+sebagai+Worker+di+KameraKita.id+menggunakan+kode+referral+saya%3A+*{{ $partner->referral_code }}*+%F0%9F%8E%A5"
                   target="_blank"
                   class="px-6 py-3 bg-white/20 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-white/30 transition text-center">
                    Bagikan via WA
                </a>
            </div>
        </div>
        @endif

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Recruited --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col justify-between gap-3">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-2xl font-black text-gray-900">{{ $metrics['recruited_workers_count'] }}</span>
                    <span class="block text-xs text-gray-400 font-medium mt-0.5">Worker Direkrut</span>
                </div>
            </div>

            {{-- Milestone Reached --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col justify-between gap-3">
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-2xl font-black text-gray-900">{{ $metrics['paid_commission_count'] + $metrics['pending_commission_count'] }}</span>
                    <span class="block text-xs text-gray-400 font-medium mt-0.5">Milestone Tercapai</span>
                </div>
            </div>

            {{-- Komisi Pending --}}
            <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100 shadow-sm flex flex-col justify-between gap-3">
                <div class="p-2.5 bg-amber-100 text-amber-600 rounded-xl w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-xs text-amber-600 font-bold uppercase tracking-wider">Komisi Pending</span>
                    <span class="block text-xl font-black text-amber-800 mt-1">Rp {{ number_format($metrics['pending_commission_amount'], 0, ',', '.') }}</span>
                    <span class="block text-[10px] text-amber-500 mt-0.5">{{ $metrics['pending_commission_count'] }} milestone belum dibayar</span>
                </div>
            </div>

            {{-- Komisi Lunas --}}
            <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100 shadow-sm flex flex-col justify-between gap-3">
                <div class="p-2.5 bg-emerald-100 text-emerald-600 rounded-xl w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-xs text-emerald-600 font-bold uppercase tracking-wider">Komisi Lunas</span>
                    <span class="block text-xl font-black text-emerald-800 mt-1">Rp {{ number_format($metrics['paid_commission_amount'], 0, ',', '.') }}</span>
                    <span class="block text-[10px] text-emerald-500 mt-0.5">{{ $metrics['paid_commission_count'] }} milestone telah dibayar</span>
                </div>
            </div>
        </div>

        {{-- Cara Kerja --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-gray-150 shadow-sm">
            <span class="block text-xs font-black tracking-widest text-gray-400 uppercase mb-4">Cara Kerja Program Komisi</span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex gap-3 items-start p-4 bg-violet-50 rounded-2xl">
                    <span class="w-8 h-8 rounded-full bg-violet-600 text-white font-black text-sm flex items-center justify-center shrink-0">1</span>
                    <div>
                        <span class="block text-sm font-bold text-gray-800">Bagikan Kode Referral</span>
                        <span class="block text-xs text-gray-500 mt-1">Kirimkan kode <strong class="font-mono text-violet-700">{{ $partner->referral_code }}</strong> ke calon Worker.</span>
                    </div>
                </div>
                <div class="flex gap-3 items-start p-4 bg-blue-50 rounded-2xl">
                    <span class="w-8 h-8 rounded-full bg-blue-600 text-white font-black text-sm flex items-center justify-center shrink-0">2</span>
                    <div>
                        <span class="block text-sm font-bold text-gray-800">Worker Daftar & Berkarya</span>
                        <span class="block text-xs text-gray-500 mt-1">Worker memasukkan kode saat registrasi dan mulai membuat video.</span>
                    </div>
                </div>
                <div class="flex gap-3 items-start p-4 bg-emerald-50 rounded-2xl">
                    <span class="w-8 h-8 rounded-full bg-emerald-600 text-white font-black text-sm flex items-center justify-center shrink-0">3</span>
                    <div>
                        <span class="block text-sm font-bold text-gray-800">20 Jam → Rp 100.000</span>
                        <span class="block text-xs text-gray-500 mt-1">Saat Worker mencapai 20 jam video approved, komisi Anda otomatis dicatat.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Worker Rekrutan --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-150 shadow-sm">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <span class="block text-sm font-bold text-gray-900">Worker Rekrutan Anda</span>
                <span class="text-xs text-gray-400">{{ $metrics['recruited_workers_count'] }} worker</span>
            </div>

            @if(empty($metrics['workers_data']))
                <div class="py-10 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm text-gray-400">Belum ada Worker yang mendaftar dengan kode referral Anda.</p>
                    <p class="text-xs text-gray-300 mt-1">Bagikan kode <strong class="font-mono text-violet-600">{{ $partner->referral_code }}</strong> ke calon Worker sekarang!</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="text-gray-500 text-xs uppercase tracking-wider">
                                <th class="py-3 text-left font-bold">Nama Worker</th>
                                <th class="py-3 text-left font-bold">ID</th>
                                <th class="py-3 text-center font-bold">Total Jam Approved</th>
                                <th class="py-3 text-center font-bold">Progress ke 20 Jam</th>
                                <th class="py-3 text-center font-bold">Status Milestone</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($metrics['workers_data'] as $data)
                                @php
                                    $progress = min(100, ($data['approved_hours'] / 20) * 100);
                                    $progressColor = $data['milestone_reached'] ? 'bg-emerald-500' : ($progress >= 75 ? 'bg-amber-500' : 'bg-indigo-500');
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-4">
                                        <div class="font-semibold text-gray-800">{{ $data['worker']->full_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $data['worker']->email }}</div>
                                    </td>
                                    <td class="py-4 font-mono text-xs text-indigo-600 font-bold">{{ $data['worker']->mitra_id }}</td>
                                    <td class="py-4 text-center">
                                        <span class="font-bold text-gray-800">{{ $data['approved_hours'] }} jam</span>
                                    </td>
                                    <td class="py-4 text-center min-w-[120px]">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                                <div class="{{ $progressColor }} h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-500 w-8 shrink-0">{{ round($progress) }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        @if($data['milestone_reached'])
                                            @if($data['milestone_status'] === 'paid')
                                                <span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-100 px-3 py-1 rounded-lg text-xs font-bold">✓ Komisi Lunas</span>
                                            @else
                                                <span class="inline-block bg-amber-50 text-amber-700 border border-amber-100 px-3 py-1 rounded-lg text-xs font-bold">⏳ Menunggu Dibayar</span>
                                            @endif
                                        @else
                                            <span class="inline-block bg-gray-50 text-gray-400 border border-gray-100 px-3 py-1 rounded-lg text-xs">Belum Tercapai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
