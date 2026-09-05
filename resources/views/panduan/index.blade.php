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

</x-app-layout>
