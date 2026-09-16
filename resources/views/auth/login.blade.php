<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <h3 class="text-xl font-bold text-gray-900">{{ __('auth_view.login_title') }}</h3>
        <p class="text-xs text-gray-400 mt-1">Masuk menggunakan Username Anda.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Username -->
        <div>
            <label for="username" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('auth_view.username_label') }}</label>
            <div class="flex items-stretch rounded-xl shadow-sm">
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" class="block w-full px-3.5 py-2.5 border border-r-0 border-gray-200 rounded-l-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="contoh: budi123">
                <span class="inline-flex items-center px-4 bg-gray-50 border border-l-0 border-gray-200 rounded-r-xl text-gray-500 text-sm font-medium">
                    @kamerakitaid.site
                </span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1.5 leading-tight">Gunakan username akun Anda.</p>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
            <x-input-error :messages="$errors->get('username')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="block text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('auth_view.password_label') }}</label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-indigo-600 hover:underline font-semibold" href="{{ route('password.request') }}">
                        {{ __('auth_view.forgot_password') }}
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-gray-200 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
            <label for="remember_me" class="ms-2 text-xs font-semibold text-gray-500">{{ __('auth_view.remember_me') }}</label>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-sm">
                {{ __('auth_view.login_button') }}
            </button>
        </div>

        <div class="text-center pt-2">
            <span class="text-xs text-gray-400">{{ __('auth_view.no_account') }} </span>
            <a href="{{ route('register') }}" class="text-xs text-indigo-600 hover:underline font-bold">
                {{ __('auth_view.register_link') }}
            </a>
        </div>
    </form>
</x-guest-layout>
