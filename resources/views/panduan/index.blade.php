<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Panduan') }}
                </h2>
            </div>

            @if(false)
                <a href="{{ asset('assets/ManualBook_Atlas.pdf') }}" target="_blank" class="text-sm px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh PDF
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                            Video Tutorial Resmi
                        </span>
                        <h3 class="mt-2 text-lg sm:text-xl font-bold text-gray-900">
                            Panduan Pendaftaran Akun KameraKita &amp; Atlas
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Ikuti kedua video tutorial di bawah ini secara berurutan agar proses registrasi akun KameraKita dan aktivasi akun Atlas berjalan lancar.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Video 1: Daftar KameraKita -->
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 sm:p-6 border-b border-gray-100">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                    Langkah 1
                                </span>
                                <h4 class="mt-2 text-lg font-bold text-gray-900">
                                    Video Tutorial Daftar KameraKita
                                </h4>
                                <p class="mt-1 text-sm text-gray-600">
                                    Cara mendaftar akun di portal KameraKita, memasukkan kode aktivasi, menyetujui SOP, dan melengkapi profil rekening.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 bg-gray-50 flex-1 flex flex-col items-center justify-center">
                        <div class="w-full max-w-[320px] aspect-[9/16] bg-slate-950 rounded-2xl overflow-hidden shadow-md border border-slate-800">
                            <video
                                class="w-full h-full object-contain"
                                controls
                                playsinline
                                preload="metadata"
                            >
                                <source src="{{ asset('assets/tutorials/tutorial-daftar-kamerakita.mp4') }}" type="video/mp4">
                                Browser Anda tidak mendukung pemutar video HTML5.
                            </video>
                        </div>
                    </div>

                    <div class="px-5 py-4 bg-white border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                        <span>Pastikan menggunakan kode aktivasi yang valid saat registrasi.</span>
                        <a
                            href="{{ asset('assets/tutorials/tutorial-daftar-kamerakita.mp4') }}"
                            target="_blank"
                            rel="noopener"
                            class="font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1"
                        >
                            Buka di tab baru
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </section>

                <!-- Video 2: Daftar Atlas -->
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 sm:p-6 border-b border-gray-100">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    Langkah 2
                                </span>
                                <h4 class="mt-2 text-lg font-bold text-gray-900">
                                    Video Tutorial Daftar Atlas
                                </h4>
                                <p class="mt-1 text-sm text-gray-600">
                                    Cara memilih Mitra Eksternal &rarr; Perekam di aplikasi Atlas Capture, verifikasi OTP lewat Mailbox KameraKita, hingga cek profil team.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 bg-gray-50 flex-1 flex flex-col items-center justify-center">
                        <div class="w-full max-w-[320px] aspect-[9/16] bg-slate-950 rounded-2xl overflow-hidden shadow-md border border-slate-800">
                            <video
                                class="w-full h-full object-contain"
                                controls
                                playsinline
                                preload="metadata"
                            >
                                <source src="{{ asset('assets/tutorials/tutorial-daftar-atlas.mp4') }}" type="video/mp4">
                                Browser Anda tidak mendukung pemutar video HTML5.
                            </video>
                        </div>
                    </div>

                    <div class="px-5 py-4 bg-white border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                        <span>Kode OTP masuk otomatis ke menu Mailbox pada akun KameraKita Anda.</span>
                        <a
                            href="{{ asset('assets/tutorials/tutorial-daftar-atlas.mp4') }}"
                            target="_blank"
                            rel="noopener"
                            class="font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1"
                        >
                            Buka di tab baru
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @if(false)
        <!-- Container Utama dengan tinggi tetap agar tidak terlipat -->
        <div class="w-full bg-white relative" style="height: calc(100vh - 140px); min-height: 700px;">
            <!-- Menggunakan iframe alih-alih object untuk kompatibilitas dan caching (cache) browser yang lebih baik -->
            <iframe 
                src="{{ asset('assets/ManualBook_Atlas.pdf') }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                class="absolute top-0 left-0 w-full h-full border-none shadow-inner"
                title="Panduan KameraKita AI"
            >
                <p>Browser Anda tidak mendukung iframe PDF.</p>
            </iframe>
        </div>
    @endif
</x-app-layout>
