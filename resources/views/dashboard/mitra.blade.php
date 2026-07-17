<x-app-layout>
    <div class="space-y-6" x-data="{ showBanner: true }">
        
        <!-- Top Banner: YOUR ACCOUNT IS ACTIVE -->
        <template x-if="showBanner">
            <div class="bg-white rounded-3xl p-6 border border-gray-150 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex gap-4 items-start">
                    <div class="p-3 bg-indigo-50 text-indigo-650 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <span class="block text-xs font-black tracking-widest text-indigo-650 uppercase">RUANG MANAJEMEN TIM</span>
                        <p class="text-sm text-gray-500 max-w-xl">Anda terdaftar sebagai Mitra (Koordinator). Anda mengawasi statistik rekam kerja, status QC, serta penumpukan pending transfer dari tim Worker Anda.</p>
                        <!-- Progress bar -->
                        <div class="w-full bg-indigo-100 h-2 rounded-full mt-3 overflow-hidden">
                            <div class="bg-indigo-650 h-full rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-emerald-50 text-emerald-800 text-[10px] font-black px-3 py-1.5 rounded-full uppercase border border-emerald-100">Tim Verified</span>
                    <button @click="showBanner = false" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <!-- Dynamic Holographic Card & Info Balance -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Holographic balance card (2 cols) - KOMISI TIM -->
            <div class="lg:col-span-2 bg-gradient-to-r from-teal-200 via-indigo-100 to-pink-100 rounded-[32px] p-8 border border-white/40 shadow-sm flex flex-col justify-between min-h-[220px] relative overflow-hidden group">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-[1px] pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-550 font-mono">KOMISI TIM WORKER (RATE Rp{{ number_format($metrics['commission_hourly_rate'], 0, ',', '.') }}/JAM)</span>
                    <div class="flex items-baseline gap-2 mt-3">
                        <span class="text-4xl font-black text-slate-900 tracking-tight">Rp{{ number_format($metrics['commission_paid_earnings'] + $metrics['commission_pending_earnings'], 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-550 font-bold uppercase font-mono">Pending: Rp{{ number_format($metrics['commission_pending_earnings'], 0, ',', '.') }}</span>
                    </div>
                    <span class="block text-xs text-slate-450 font-medium mt-1">Dihitung dari total jam kerja approved tim Worker: <strong>{{ $metrics['total_all_time_hours_formatted'] }}</strong></span>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6 relative z-10">
                    <a href="{{ route('partners.index') }}" class="flex items-center justify-center gap-2 py-3.5 bg-white hover:bg-gray-50 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition">
                        Kelola Anggota Tim
                    </a>
                    <button class="flex items-center justify-center gap-2 py-3.5 bg-white/60 hover:bg-white/80 text-gray-800 font-bold text-xs rounded-2xl shadow-sm transition">
                        Log Kerja Kelompok
                    </button>
                </div>
            </div>

            <!-- Right: Investment balance card (1 col) - PENDAPATAN PRIBADI -->
            <div class="bg-white rounded-[32px] p-8 border border-gray-150 shadow-sm flex flex-col justify-between min-h-[220px]">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">PENDAPATAN PRIBADI (RATE Rp{{ number_format($metrics['personal_hourly_rate'], 0, ',', '.') }}/JAM)</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-3">Rp{{ number_format($metrics['personal_paid_earnings'] + $metrics['personal_pending_earnings'], 0, ',', '.') }}</h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-xs text-slate-400 font-medium">Own Record:</span>
                        <span class="bg-amber-50 border border-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ $metrics['personal_all_time_hours_formatted'] }}
                        </span>
                    </div>
                </div>

                <span class="block text-center text-xs font-bold text-gray-400 py-3 bg-gray-50 border border-gray-150 rounded-2xl font-mono">
                    Pending: Rp{{ number_format($metrics['personal_pending_earnings'], 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- OTHER FEATURES Section -->
        <div class="space-y-3">
            <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono">STATISTIK KELOMPOK</span>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Feature 1: Workers managed -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Total Workers</span>
                    <span class="block text-[10px] text-gray-400 font-medium">{{ $metrics['workers_count'] }} Anggota</span>
                </div>

                <!-- Feature 2: Total Paid Tim -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Sudah Dibayar Tim</span>
                    <span class="block text-[10px] text-gray-400 font-medium font-mono">{{ $metrics['total_paid_hours_formatted'] }}</span>
                </div>

                <!-- Feature 3: WhatsApp Support -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition">
                    <div class="p-2.5 bg-indigo-50 text-indigo-650 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Dukungan Pusat</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Bantuan WA Agensi</span>
                </div>

                <!-- Feature 4: Status Pajak / Akun -->
                <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition relative">
                    <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Status Akun</span>
                    <span class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                        ACTIVE
                    </span>
                </div>
            </div>
        </div>

        <!-- Workers List Card -->
        <div class="bg-white rounded-[32px] p-6 border border-gray-150 shadow-sm">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <span class="block text-sm font-bold text-gray-900">Pencapaian Menit Tim Worker</span>
                <span class="text-xs text-gray-400">Daftar tim direct</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">ID Mitra</th>
                            <th class="py-3 text-left font-semibold">Nama Worker</th>
                            <th class="py-3 text-left font-semibold">All Time</th>
                            <th class="py-3 text-left font-semibold">Paid</th>
                            <th class="py-3 text-left font-semibold">Pending</th>
                            <th class="py-3 text-left font-semibold">Estimasi Pending Gaji</th>
                            <th class="py-3 text-left font-semibold">WhatsApp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($metrics['workers_data'] as $data)
                            <tr>
                                <td class="py-3.5 font-bold text-indigo-650">{{ $data['worker']->mitra_id }}</td>
                                <td class="py-3.5 font-medium text-gray-900">{{ $data['worker']->full_name }}</td>
                                <td class="py-3.5 text-gray-600">{{ $data['metrics']['all_time_hours_formatted'] }}</td>
                                <td class="py-3.5 text-emerald-800">{{ $data['metrics']['paid_hours_formatted'] }}</td>
                                <td class="py-3.5 text-amber-800 font-bold">{{ $data['metrics']['pending_hours_formatted'] }}</td>
                                <td class="py-3.5 font-extrabold text-slate-800">Rp{{ number_format($data['metrics']['pending_earnings'], 0, ',', '.') }}</td>
                                <td class="py-3.5 text-indigo-600 font-medium">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $data['worker']->whatsapp_number) }}" target="_blank" class="hover:underline">
                                        {{ $data['worker']->whatsapp_number }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-450 text-xs">Belum ada Worker terdaftar di bawah naungan Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
