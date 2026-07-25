<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">Portal Onboarding</h3>
        <p class="text-xs text-gray-400 mt-1">Registrasi resmi Mitra Kontributor Data KameraKita AI.</p>
        
        <div class="mt-4 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-left text-[11px] text-slate-500 leading-relaxed">
            Selamat datang di portal operasional <strong>KameraKita AI</strong>. Formulir ini digunakan untuk memvalidasi tipe perangkat Apple (iPhone) Anda guna mendukung proyek pengumpulan data video egocentric (pelatihan Computer Vision).
        </div>
    </div>

    <form method="POST" action="{{ route('onboarding.submit') }}" class="space-y-4">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <label for="full_name" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">Nama Lengkap <span class="text-red-500">*</span></label>
            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Sesuai profil Fastwork Anda" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-300">
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
                <option value="Lainnya (iPhone XS/11/dll)" {{ old('device_type') === 'Lainnya (iPhone XS/11/dll)' ? 'selected' : '' }}>Lainnya (iPhone XS / 11 / Seri Lain)</option>
            </select>
            <x-input-error :messages="$errors->get('device_type')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                <span>Submit Data & Gabung Grup WhatsApp</span>
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
</x-guest-layout>
