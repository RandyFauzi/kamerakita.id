<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Panduan Book') }}
            </h2>
            <a href="{{ asset('assets/ManualBook_Atlas.pdf') }}" target="_blank" class="text-sm px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </a>
        </div>
    </x-slot>

    <!-- Container Utama: Menghilangkan padding berlebih agar PDF mengambil porsi layar maksimal -->
    <div class="h-[calc(100vh-120px)] w-full flex flex-col bg-white">
        <!-- Metode Native Object (Performa Tercepat, Tanpa JS Ekstra) -->
        <object 
            data="{{ asset('assets/ManualBook_Atlas.pdf') }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
            type="application/pdf" 
            class="w-full h-full border-none shadow-inner"
        >
            <!-- Fallback elegan untuk layar Mobile / Browser Tanpa Ekstensi PDF -->
            <div class="flex flex-col items-center justify-center h-full p-6 text-center bg-gray-50/50">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 max-w-md w-full">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Buka Panduan</h2>
                    <p class="text-gray-500 mb-6 text-sm">Browser pada perangkat ini tidak mendukung fitur pratinjau PDF sebaris secara langsung. Jangan khawatir, Anda tetap bisa membacanya.</p>
                    <a href="{{ asset('assets/ManualBook_Atlas.pdf') }}" target="_blank" class="w-full inline-flex justify-center items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition-all gap-2">
                        Buka Dokumen Secara Penuh
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </object>
    </div>
</x-app-layout>
