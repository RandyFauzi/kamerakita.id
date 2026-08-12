<x-guest-layout>
    <div x-data="{ showForm: false }">
        <!-- Landing View -->
        <div x-show="!showForm" class="text-center space-y-6 py-4" x-cloak>
            <div class="mb-8">
                <h3 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">Portal Pendaftar Baru</h3>
                <p class="text-xs text-gray-400 mt-1">Gabung menjadi Mitra Kontributor Data KameraKita AI.</p>
                
                <div class="mt-4 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-left text-[11px] text-slate-500 leading-relaxed">
                    Selamat datang di portal pendaftaran <strong>KameraKita AI</strong>. Silakan pilih opsi di bawah ini untuk mendaftar atau bertanya langsung kepada tim kami.
                </div>
            </div>

            <div class="space-y-4">
                <button @click="showForm = true" class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm uppercase tracking-widest rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                    <span>Daftar Sekarang</span>
                </button>
                
                <a href="https://wa.me/6285389933194?text=Halo%20Leader!%20Saya%20tertarik%20bergabung%20dengan%20KameraKita%20AI.%20Boleh%20bantu%20jelaskan%20bagaimana%20sistem%20kerja%20dan%20persyaratannya%3F" target="_blank" class="w-full py-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold text-sm uppercase tracking-widest rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                    <span>Tanya dengan Leader</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Registration Form -->
        <div x-show="showForm" x-cloak>
            <div class="mb-6 text-center">
                <h3 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">Formulir Pendaftaran</h3>
                <p class="text-xs text-gray-400 mt-1">Lengkapi data diri Anda di bawah ini.</p>
                <div class="mt-4 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-left text-[11px] text-slate-500 leading-relaxed">
                    Formulir ini digunakan untuk memvalidasi tipe perangkat Apple (iPhone) Anda guna mendukung proyek pengumpulan data video egocentric (pelatihan Computer Vision).
                </div>
            </div>

            <form method="POST" action="{{ route('onboarding.submit') }}" class="space-y-4">
                @csrf
                
                <button type="button" @click="showForm = false" class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali
                </button>
            <!-- Nama Lengkap -->
            <div>
                <label for="full_name" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Nama Lengkap <span class="text-red-500">*</span></label>
                <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Masukkan nama lengkap Anda" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-300">
                <x-input-error :messages="$errors->get('full_name')" class="mt-1" />
            </div>

            <!-- Nomor WhatsApp -->
            <div>
                <label for="whatsapp_number" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Nomor WhatsApp Aktif <span class="text-red-500">*</span></label>
                <input id="whatsapp_number" type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required placeholder="Contoh: 081234567890" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-300">
                <p class="text-[10px] text-gray-400 mt-1 font-mono">Untuk koordinasi QC & pembayaran.</p>
                <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-1" />
            </div>

            <!-- Username Fastwork -->
            <div>
                <label for="fastwork_username" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Username Fastwork <span class="text-gray-400">(Opsional)</span></label>
                <input id="fastwork_username" type="text" name="fastwork_username" value="{{ old('fastwork_username') }}" placeholder="Contoh: randy_fauzi" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-300">
                <x-input-error :messages="$errors->get('fastwork_username')" class="mt-1" />
            </div>

            <!-- Tipe Perangkat -->
            <div>
                <label for="device_type" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Tipe Perangkat Apple Anda <span class="text-red-500">*</span></label>
                <select id="device_type" name="device_type" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white text-gray-700">
                    <option value="" disabled {{ old('device_type') === null ? 'selected' : '' }}>Pilih Tipe iPhone...</option>
                    <option value="iPhone 12" {{ old('device_type') === 'iPhone 12' ? 'selected' : '' }}>iPhone 12</option>
                    <option value="iPhone 12 Pro / Max" {{ old('device_type') === 'iPhone 12 Pro / Max' ? 'selected' : '' }}>iPhone 12 Pro / Max</option>
                    <option value="iPhone 13" {{ old('device_type') === 'iPhone 13' ? 'selected' : '' }}>iPhone 13</option>
                    <option value="iPhone 13 Pro / Max" {{ old('device_type') === 'iPhone 13 Pro / Max' ? 'selected' : '' }}>iPhone 13 Pro / Max</option>
                    <option value="iPhone 14" {{ old('device_type') === 'iPhone 14' ? 'selected' : '' }}>iPhone 14</option>
                    <option value="iPhone 14 Pro / Max" {{ old('device_type') === 'iPhone 14 Pro / Max' ? 'selected' : '' }}>iPhone 14 Pro / Max</option>
                    <option value="iPhone 15" {{ old('device_type') === 'iPhone 15' ? 'selected' : '' }}>iPhone 15</option>
                    <option value="iPhone 15 Pro / Max" {{ old('device_type') === 'iPhone 15 Pro / Max' ? 'selected' : '' }}>iPhone 15 Pro / Max</option>
                    <option value="Di bawah iPhone 12" {{ old('device_type') === 'Di bawah iPhone 12' ? 'selected' : '' }}>Di bawah iPhone 12</option>
                </select>
                <x-input-error :messages="$errors->get('device_type')" class="mt-1" />
            </div>

            <!-- Headstrap -->
            <div>
                <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    <input type="checkbox" name="has_headstrap" value="1" {{ old('has_headstrap') ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300">
                    <span class="text-sm text-gray-700 font-medium">Saya sudah memiliki aksesoris Headstrap / Chest Strap</span>
                </label>
                <x-input-error :messages="$errors->get('has_headstrap')" class="mt-1" />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                    <span>Daftar Sekarang</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>

            <!-- Disclaimer -->
            <div class="text-[10px] text-gray-400 text-center leading-relaxed pt-2 border-t border-gray-100">
                🔒 Data Anda terenkripsi aman. Setelah dikirim, Anda otomatis dialihkan ke WhatsApp Grup resmi tim.
            </div>
        </form>
        </div>
    </div>
</x-guest-layout>
