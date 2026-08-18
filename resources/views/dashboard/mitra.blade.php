<x-app-layout>
    <div class="space-y-4 sm:space-y-6" x-data="{ showBanner: true }">
        
        <!-- Top Banner: YOUR ACCOUNT IS ACTIVE -->
        <template x-if="showBanner">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-150 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex gap-3 sm:gap-4 items-start pr-8 md:pr-0">
                    <div class="p-2.5 sm:p-3 bg-indigo-50 text-indigo-650 rounded-xl sm:rounded-2xl shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <span class="block text-xs font-black tracking-widest text-indigo-650 uppercase">RUANG MANAJEMEN TIM</span>
                        <p class="text-sm text-gray-500 max-w-xl">Anda terdaftar sebagai Vendor. Anda mengawasi statistik rekam kerja, status QC, serta penumpukan pending transfer dari tim Worker Anda.</p>
                        <!-- Progress bar -->
                        <div class="w-full bg-indigo-100 h-2 rounded-full mt-3 overflow-hidden">
                            <div class="bg-indigo-650 h-full rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-emerald-50 text-emerald-800 text-[10px] font-black px-3 py-1.5 rounded-full uppercase border border-emerald-100">Tim Verified</span>
                    <button type="button" aria-label="Tutup pemberitahuan" @click="showBanner = false" class="absolute top-4 right-4 w-9 h-9 inline-flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Referral Code Banner --}}
        @if($partner->referral_code)
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 rounded-2xl sm:rounded-3xl p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white/20 rounded-2xl shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-[10px] font-black tracking-widest text-indigo-200 uppercase">Kode Referral Anda</span>
                    <span class="block text-2xl sm:text-3xl font-black text-white font-mono tracking-widest mt-1">{{ $partner->referral_code }}</span>
                    <span class="block text-xs text-indigo-200 mt-1">Bagikan kode ini ke calon Worker yang ingin bergabung di bawah tim Anda.</span>
                </div>
            </div>
            <button onclick="navigator.clipboard.writeText('{{ $partner->referral_code }}').then(() => { this.textContent = '✓ Tersalin!'; setTimeout(() => this.textContent = 'Salin Kode', 1500) })"
                class="shrink-0 px-5 py-2.5 bg-white text-indigo-700 font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-50 transition shadow">
                Salin Kode
            </button>
        </div>
        @endif

        <!-- Dynamic Holographic Card & Info Balance -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Left: Holographic balance card (2 cols) - KOMISI TIM -->
            <div class="lg:col-span-2">
                <x-glass-orb-card 
                    title="Rp{{ number_format($metrics['commission_paid_earnings'] + $metrics['commission_pending_earnings'], 0, ',', '.') }}"
                    subtitle="KOMISI TIM WORKER (RATE Rp{{ number_format($metrics['commission_hourly_rate'], 0, ',', '.') }}/JAM)"
                    label1="PENDING KOMISI"
                    value1="Rp{{ number_format($metrics['commission_pending_earnings'], 0, ',', '.') }}"
                    label2="TOTAL JAM KERJA"
                    value2="{{ $metrics['total_all_time_hours_formatted'] }}"
                >
                    <x-slot name="actionSlot">
                        <div class="grid grid-cols-2 divide-x divide-slate-200/50 w-full">
                            <a href="{{ route('vendor.reports.index') }}" class="py-4 text-center text-sm font-bold text-blue-700 hover:bg-black/5 transition-colors">
                                QC Tracker Tim
                            </a>
                            <a href="{{ route('profile.edit') }}" class="py-4 text-center text-sm font-bold text-slate-700 hover:bg-black/5 transition-colors">
                                Edit Profil
                            </a>
                        </div>
                    </x-slot>
                </x-glass-orb-card>
            </div>

            <!-- Right: Investment balance card (1 col) - PENDAPATAN PRIBADI -->
            <div class="bg-white rounded-2xl sm:rounded-[32px] p-5 sm:p-8 border border-gray-150 shadow-sm flex flex-col justify-between gap-5 min-h-[180px] sm:min-h-[220px]">
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
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
                <a href="https://wa.me/6287886272647?text=Halo%20Admin%20Kamerakita.id,%20saya%20Vendor%20Koordinator%20ingin%20berkonsultasi%20terkait%20operasional%20tim%20saya." target="_blank" class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:shadow-md transition">
                    <div class="p-2.5 bg-indigo-50 text-indigo-650 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="block text-xs font-bold text-gray-800">Dukungan Pusat</span>
                    <span class="block text-[10px] text-gray-400 font-medium">Bantuan WA Agensi</span>
                </a>

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
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="bg-white rounded-2xl sm:rounded-[32px] p-4 sm:p-6 border border-gray-150 shadow-sm" x-data="{ showAddWorkerModal: {{ $errors->any() ? 'true' : 'false' }} }">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                <div>
                    <span class="block text-sm font-bold text-gray-900">Pencapaian Menit Tim Worker</span>
                    <span class="text-xs text-gray-400">Daftar tim direct</span>
                </div>
                <button @click="showAddWorkerModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Anggota
                </button>
            </div>

            <!-- Modal Tambah Worker -->
            <div x-show="showAddWorkerModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showAddWorkerModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showAddWorkerModal" @click.away="showAddWorkerModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                        <form action="{{ route('vendor.workers.store') }}" method="POST">
                            @csrf
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Daftarkan Worker Baru</h3>
                                        <div class="mt-4 space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                                <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Username <span class="text-xs text-gray-400">(tanpa spasi)</span></label>
                                                <input type="text" name="username" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                                <input type="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                                                <input type="password" name="password_confirmation" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Daftarkan</button>
                                <button type="button" @click="showAddWorkerModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500">
                            <th class="py-3 text-left font-semibold">ID Vendor</th>
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

        <!-- CTA Mailbox -->
        <div class="mt-8 bg-white border border-indigo-100 rounded-3xl p-5 md:p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-5 transition-all hover:shadow-md hover:border-indigo-200">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center shrink-0 border border-indigo-100">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[16px] md:text-lg font-bold text-gray-900 mb-0.5">Kotak Masuk (Mailbox)</h3>
                    <p class="text-gray-500 text-[13px] md:text-sm leading-snug">Cek pesan, pengumuman, dan pembaruan terbaru dari tim KameraKita.</p>
                </div>
            </div>
            
            <a href="{{ route('mailbox.index') }}" class="w-full md:w-auto shrink-0 bg-indigo-600 text-white hover:bg-indigo-700 px-6 py-3 rounded-xl font-bold text-[14px] shadow-sm transition-colors text-center flex items-center justify-center gap-2 group">
                Buka Mailbox
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

    </div>
</x-app-layout>
