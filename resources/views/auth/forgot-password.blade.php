<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="text-xl font-bold text-gray-900">Pemulihan Kata Sandi</h3>
        <p class="text-xs text-gray-500 mt-1">Lupa password akun KameraKita Anda?</p>
    </div>

    @if(session('recovery_submitted'))
        <div class="space-y-4">
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-center">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-emerald-900 mb-1">Permintaan Berhasil Diajukan!</h4>
                <p class="text-xs text-emerald-700 leading-relaxed">
                    Pengajuan reset untuk akun <span class="font-bold font-mono">{{ session('submitted_email') }}</span> telah tercatat di sistem.
                </p>
                <div class="mt-3 pt-3 border-t border-emerald-100 text-[11px] text-emerald-800 leading-tight">
                    Untuk keamanan akun, silakan <strong>konfirmasi ke Admin via WhatsApp</strong> di bawah ini agar permohonan Anda segera disetujui:
                </div>
            </div>

            <!-- WhatsApp Action Button -->
            <a href="{{ session('admin_wa_link') }}" target="_blank"
               class="w-full inline-flex items-center justify-center gap-2.5 px-4 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-sm rounded-xl transition shadow-sm text-center"
               style="background-color: #059669;">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Chat Admin WhatsApp (0895-3665-83095)</span>
            </a>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs text-indigo-600 hover:underline font-bold">
                    &larr; Kembali ke Halaman Login
                </a>
            </div>
        </div>
    @else
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="mb-4 text-xs text-gray-500 leading-relaxed bg-gray-50 p-3 rounded-xl border border-gray-100">
            Masukkan <strong>Username</strong> atau <strong>Email KameraKita</strong> Anda. Setelah mengajukan, Anda akan diarahkan untuk konfirmasi langsung ke WhatsApp Admin demi keamanan akun.
        </div>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address or Username -->
            <div>
                <label for="email" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Username / Email Akun</label>
                <input id="email" type="text" name="email" :value="old('email')" required autofocus
                       class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                       placeholder="contoh: budi123 atau budi123@kamerakitaid.site">
                <p class="text-[10px] text-gray-400 mt-1.5">Bisa ketik nama username saja (tanpa @).</p>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-sm">
                    Ajukan Reset Password
                </button>
            </div>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs text-gray-500 hover:text-gray-800 font-semibold">
                    &larr; Kembali ke Login
                </a>
            </div>
        </form>
    @endif
</x-guest-layout>
