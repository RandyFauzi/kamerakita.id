<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Edit Anggota: {{ $partner->full_name }} ({{ $partner->mitra_id }})
            </h2>
            <a href="{{ route('partners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-150 border border-gray-200 rounded-xl font-semibold text-xs text-gray-700 uppercase hover:bg-gray-200 transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ role: '{{ $partner->partner_role }}', activeMenu: 'identitas' }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                
                <!-- Kiri: Info Profil (Desktop: Sidebar) -->
                <div class="lg:w-1/3 space-y-6">
                    <div class="bg-white rounded-[32px] shadow-sm border border-gray-150 p-6 flex flex-col items-center relative overflow-hidden">
                        <!-- Avatar Foto -->
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-4 border-[3px] border-white shadow-lg bg-gray-50 flex items-center justify-center relative z-10">
                            <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        
                        <!-- Nama & Kontak -->
                        <h2 class="text-[20px] font-bold text-gray-900 mb-1 text-center relative z-10">{{ $partner->full_name }}</h2>
                        <p class="text-[11px] text-gray-500 mb-4 font-medium text-center relative z-10">{{ $partner->email ?? $partner->user?->email }} | {{ $partner->whatsapp_number }}</p>
                        
                        <!-- Badge Status -->
                        <div class="flex items-center gap-1.5 text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100 relative z-10">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span class="text-[10px] font-bold tracking-wide uppercase" x-text="role"></span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Form Pengaturan (Desktop: Main Content) -->
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-[32px] shadow-sm border border-gray-150 p-4 sm:p-6 lg:p-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 hidden lg:block">Edit Data Anggota</h3>
                        
                        <form action="{{ route('partners.update', $partner) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="flex flex-col gap-4">
                                
                                <!-- Accordion 1: Informasi Identitas -->
                                <div class="border border-gray-150 rounded-2xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)] bg-white transition-all duration-300" :class="{'ring-2 ring-blue-100 border-blue-200': activeMenu === 'identitas'}">
                                    <div @click="activeMenu = activeMenu === 'identitas' ? null : 'identitas'" class="px-4 py-4 lg:py-5 flex items-center justify-between cursor-pointer group hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                                                <svg width="18" height="18" class="lg:w-5 lg:h-5" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-[13px] lg:text-[14px] font-bold text-gray-800 mb-0.5">Identitas & Kontak</h4>
                                                <p class="text-[11px] lg:text-xs text-gray-400 font-medium">Data profil utama dan kontak anggota.</p>
                                            </div>
                                        </div>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300" :class="{'rotate-90': activeMenu === 'identitas'}">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </div>
                                    
                                    <div x-show="activeMenu === 'identitas'" x-collapse class="px-5 lg:px-8 pb-6 lg:pb-8 pt-2 border-t border-gray-100 bg-gray-50/30">
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Peran -->
                                            <div>
                                                <label for="partner_role" class="block text-sm font-semibold text-gray-700 mb-1">Peran Kemitraan <span class="text-red-500">*</span></label>
                                                <select name="partner_role" id="partner_role" x-model="role" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="worker" {{ $partner->partner_role === 'worker' ? 'selected' : '' }}>Worker (Perekam Video)</option>
                                                    <option value="mitra" {{ $partner->partner_role === 'mitra' ? 'selected' : '' }}>Mitra (Koordinator/Fasilitator)</option>
                                                    <option value="rekruter" {{ $partner->partner_role === 'rekruter' ? 'selected' : '' }}>Rekruter (Perekrut Worker)</option>
                                                </select>
                                                @error('partner_role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- ID Mitra -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">ID Mitra (Tidak dapat diubah)</label>
                                                <input type="text" value="{{ $partner->mitra_id }}" readonly class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-400 font-semibold focus:outline-none">
                                            </div>


                                            <!-- NIK -->
                                            <div>
                                                <label for="nik" class="block text-sm font-semibold text-gray-700 mb-1">NIK (Nomor Induk Kependudukan)</label>
                                                <input type="text" name="nik" id="nik" value="{{ old('nik', $partner->nik) }}" placeholder="Contoh: 327301XXXXXXXXXX" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Nama Lengkap -->
                                            <div>
                                                <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                                <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $partner->full_name) }}" required placeholder="Sesuai KTP" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Tanggal Daftar (Opsional) -->
                                            <div>
                                                <label for="registration_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Daftar</label>
                                                <input type="date" name="registration_date" id="registration_date" value="{{ old('registration_date', $partner->registration_date ? $partner->registration_date->format('Y-m-d') : '') }}" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                                                @error('registration_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- WhatsApp -->
                                            <div>
                                                <label for="whatsapp_number" class="block text-sm font-semibold text-gray-700 mb-1">No. WhatsApp <span class="text-red-500">*</span></label>
                                                <input type="text" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $partner->whatsapp_number) }}" required placeholder="Contoh: 6281234567890" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('whatsapp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Email Login -->
                                            <div>
                                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                                                <input type="email" name="email" id="email" value="{{ old('email', $partner->email ?? $partner->user?->email) }}" required placeholder="Contoh: worker@kamerakita.id" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Password -->
                                            <div>
                                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                                                    {{ $partner->user_id ? 'Password Baru' : 'Password Login' }}
                                                    @unless($partner->user_id)<span class="text-red-500">*</span>@endunless
                                                </label>
                                                <input type="password" name="password" id="password" {{ $partner->user_id ? '' : 'required' }} class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <p class="text-[10px] text-gray-400 mt-1">{{ $partner->user_id ? 'Kosongkan jika tidak mengganti password.' : 'Wajib diisi untuk akun login.' }}</p>
                                                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Password Confirmation -->
                                            <div>
                                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">
                                                    Konfirmasi Password
                                                    @unless($partner->user_id)<span class="text-red-500">*</span>@endunless
                                                </label>
                                                <input type="password" name="password_confirmation" id="password_confirmation" {{ $partner->user_id ? '' : 'required' }} class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            
                                            <!-- Alamat Lengkap -->
                                            <div class="md:col-span-2">
                                                <label for="full_address" class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
                                                <textarea name="full_address" id="full_address" rows="2" placeholder="Jalan, RT/RW, Kecamatan, Kota, Kode Pos" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('full_address', $partner->full_address) }}</textarea>
                                                @error('full_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accordion 2: Informasi Finansial -->
                                <div class="border border-gray-150 rounded-2xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)] bg-white transition-all duration-300" :class="{'ring-2 ring-emerald-100 border-emerald-200': activeMenu === 'finansial'}">
                                    <div @click="activeMenu = activeMenu === 'finansial' ? null : 'finansial'" class="px-4 py-4 lg:py-5 flex items-center justify-between cursor-pointer group hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                                                <svg width="18" height="18" class="lg:w-5 lg:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-[13px] lg:text-[14px] font-bold text-gray-800 mb-0.5">Informasi Finansial</h4>
                                                <p class="text-[11px] lg:text-xs text-gray-400 font-medium">Detail perbankan untuk pencairan payroll.</p>
                                            </div>
                                        </div>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300" :class="{'rotate-90': activeMenu === 'finansial'}">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </div>
                                    
                                    <div x-show="activeMenu === 'finansial'" x-collapse class="px-5 lg:px-8 pb-6 lg:pb-8 pt-2 border-t border-gray-100 bg-gray-50/30">
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Nama Bank -->
                                            <div>
                                                <label for="bank_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Bank</label>
                                                <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $partner->bank_name) }}" placeholder="Contoh: BCA, Mandiri, BRI" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Nomor Rekening -->
                                            <div>
                                                <label for="bank_account_number" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Rekening</label>
                                                <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $partner->bank_account_number ?? $partner->account_number) }}" placeholder="Contoh: 7012345678" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('bank_account_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Nama Pemilik Rekening -->
                                            <div class="md:col-span-2">
                                                <label for="bank_account_owner" class="block text-sm font-semibold text-gray-700 mb-1">Nama Pemilik Rekening</label>
                                                <input type="text" name="bank_account_owner" id="bank_account_owner" value="{{ old('bank_account_owner', $partner->bank_account_owner ?? $partner->account_owner_name) }}" placeholder="Contoh: Randy Fauzi" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('bank_account_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accordion 3: Operasional & Status -->
                                <div class="border border-gray-150 rounded-2xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)] bg-white transition-all duration-300" :class="{'ring-2 ring-purple-100 border-purple-200': activeMenu === 'operasional'}">
                                    <div @click="activeMenu = activeMenu === 'operasional' ? null : 'operasional'" class="px-4 py-4 lg:py-5 flex items-center justify-between cursor-pointer group hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                                                <svg width="18" height="18" class="lg:w-5 lg:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-[13px] lg:text-[14px] font-bold text-gray-800 mb-0.5">Operasional & Status</h4>
                                                <p class="text-[11px] lg:text-xs text-gray-400 font-medium">Relasi mitra, rate, dan status akun.</p>
                                            </div>
                                        </div>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300" :class="{'rotate-90': activeMenu === 'operasional'}">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </div>
                                    
                                    <div x-show="activeMenu === 'operasional'" x-collapse class="px-5 lg:px-8 pb-6 lg:pb-8 pt-2 border-t border-gray-100 bg-gray-50/30">
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Pilih Mitra Atasan (Shown only if role is worker) -->
                                            <div x-show="role === 'worker'" class="animate-in fade-in duration-200">
                                                <label for="mitra_parent_id" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Mitra Atasan (Koordinator)</label>
                                                <select name="mitra_parent_id" id="mitra_parent_id" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">Pilih Mitra...</option>
                                                    @foreach($mitraList as $mitra)
                                                        <option value="{{ $mitra->id }}" {{ old('mitra_parent_id', $partner->mitra_parent_id) == $mitra->id ? 'selected' : '' }}>
                                                            {{ $mitra->full_name }} ({{ $mitra->mitra_id }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('mitra_parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Tipe Smartphone -->
                                            <div>
                                                <label for="smartphone_type" class="block text-sm font-semibold text-gray-700 mb-1">Tipe Smartphone</label>
                                                <input type="text" name="smartphone_type" id="smartphone_type" value="{{ old('smartphone_type', $partner->smartphone_type) }}" placeholder="Contoh: iPhone 15 Pro, Samsung S24" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('smartphone_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Headstrap -->
                                            <div>
                                                <label for="has_headstrap" class="block text-sm font-semibold text-gray-700 mb-1">Memiliki Headstrap <span class="text-red-500">*</span></label>
                                                <select name="has_headstrap" id="has_headstrap" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="0" {{ old('has_headstrap', $partner->has_headstrap ? '1' : '0') === '0' ? 'selected' : '' }}>Belum</option>
                                                    <option value="1" {{ old('has_headstrap', $partner->has_headstrap ? '1' : '0') === '1' ? 'selected' : '' }}>Sudah</option>
                                                </select>
                                                @error('has_headstrap') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Rate Pendapatan per Jam -->
                                            <div>
                                                <label for="base_hourly_rate" class="block text-sm font-semibold text-gray-700 mb-1">Rate per Jam (Rupiah) <span class="text-red-500">*</span></label>
                                                <input type="number" step="1000" min="0" name="base_hourly_rate" id="base_hourly_rate" value="{{ old('base_hourly_rate', $partner->base_hourly_rate) }}" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @error('base_hourly_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Status -->
                                            <div>
                                                <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status Kemitraan <span class="text-red-500">*</span></label>
                                                <select name="status" id="status" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="active" {{ old('status', $partner->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status', $partner->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="suspended" {{ old('status', $partner->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                </select>
                                                <p class="text-[10px] text-gray-400 mt-1">Inactive otomatis jika tidak ada laporan 2 hari. Suspended manual admin.</p>
                                                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Kelompok / Grup -->
                                            <div>
                                                <label for="group_name" class="block text-sm font-semibold text-gray-700 mb-1">Kelompok / Grup</label>
                                                <select name="group_name" id="group_name" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                                    <option value="" {{ is_null($partner->group_name) ? 'selected' : '' }}>-- Tanpa Grup --</option>
                                                    @foreach(\App\Models\ActivationCode::select('group_name')->groupBy('group_name')->get() as $code)
                                                        <option value="{{ $code->group_name }}" {{ old('group_name', $partner->group_name) === $code->group_name ? 'selected' : '' }}>{{ $code->group_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('group_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Registrasi Klien Resmi -->
                                            <div class="md:col-span-2">
                                                <label for="is_client_registered" class="block text-sm font-semibold text-gray-700 mb-1">Registrasi Aplikasi Klien <span class="text-red-500">*</span></label>
                                                <select name="is_client_registered" id="is_client_registered" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                                    <option value="0" {{ old('is_client_registered', $partner->is_client_registered ? '1' : '0') === '0' ? 'selected' : '' }}>Belum Terdaftar (Unregistered)</option>
                                                    <option value="1" {{ old('is_client_registered', $partner->is_client_registered ? '1' : '0') === '1' ? 'selected' : '' }}>Sudah Terdaftar Resmi (Registered)</option>
                                                </select>
                                                @error('is_client_registered') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Actions -->
                                <div class="mt-6 flex justify-end gap-3 pt-2">
                                    <a href="{{ route('partners.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition shadow-sm">
                                        Batal
                                    </a>
                                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 transition shadow-md shadow-blue-100">
                                        Simpan Perubahan
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>
