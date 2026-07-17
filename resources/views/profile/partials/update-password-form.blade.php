<section>
    <header class="border-b border-gray-100 pb-4">
        <span class="block text-xs font-black tracking-widest text-indigo-650 uppercase font-mono mb-1">KEAMANAN</span>
        <h3 class="text-lg font-bold text-gray-900">Ubah Password</h3>
        <p class="text-xs text-gray-400">Gunakan password yang kuat dan berbeda dari layanan lain.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-semibold text-gray-700 mb-1">Password Saat Ini <span class="text-red-500">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-semibold text-gray-700 mb-1">Password Baru <span class="text-red-500">*</span></label>
            <input id="update_password_password" name="password" type="password" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-gray-900 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-gray-800 transition shadow-md shadow-gray-100">
                Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-green-700"
                >Password tersimpan.</p>
            @endif
        </div>
    </form>
</section>
