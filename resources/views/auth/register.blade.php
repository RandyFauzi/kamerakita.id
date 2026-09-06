<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="text-xl font-bold text-gray-900">{{ __('auth_view.register_title') }}</h3>
        <p class="text-xs text-gray-400 mt-1">{{ __('auth_view.register_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('auth_view.name_label') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Username -->
        <div>
            <label for="username" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('auth_view.username_label') }}</label>
            <div class="flex items-stretch rounded-xl shadow-sm">
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username" class="block w-full px-3.5 py-2.5 border border-r-0 border-gray-200 rounded-l-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="contoh: budi123">
                <span class="inline-flex items-center px-4 bg-gray-50 border border-l-0 border-gray-200 rounded-r-xl text-gray-500 text-sm font-medium">
                    @kamerakitaid.site
                </span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1.5 leading-tight">Gunakan huruf kecil dan angka tanpa spasi. Ini akan menjadi email internal Anda untuk menerima informasi tugas.</p>
            <x-input-error :messages="$errors->get('username')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('auth_view.password_label') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Activation Code -->
        <div>
            <label for="activation_code" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kode Aktivasi (Activation Code)</label>
            <input id="activation_code" type="text" name="activation_code" value="{{ old('activation_code') }}" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-300 font-mono" placeholder="Contoh: KMK-01ASQW">
            <x-input-error :messages="$errors->get('activation_code')" class="mt-1" />
        </div>

        <!-- Referral Code (Opsional) -->
        <div>
            <label for="referral_code" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                Kode Referral
                <span class="normal-case font-normal text-gray-300 ml-1">(Opsional — dari Vendor/Rekruter Anda)</span>
            </label>
            <input id="referral_code" type="text" name="referral_code" value="{{ old('referral_code') }}" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-300 font-mono" placeholder="Contoh: REF-ABCDEF">
            <x-input-error :messages="$errors->get('referral_code')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a class="text-xs text-indigo-650 hover:underline font-semibold" href="{{ route('login') }}">
                Sudah punya akun?
            </a>

            <button type="submit" class="px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-sm">
                Daftar Akun
            </button>
        </div>
    </form>
</x-guest-layout>
