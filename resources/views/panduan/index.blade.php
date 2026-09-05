<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panduan Book') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="mb-4 text-center">
                        <p class="text-sm text-gray-500">Anda dapat membaca panduan di bawah ini atau <a href="{{ asset('assets/ManualBook_Atlas.pdf') }}" target="_blank" class="text-indigo-600 font-semibold hover:underline">mengunduhnya di sini</a>.</p>
                    </div>

                    <!-- Metode Native Object dengan Fallback Button -->
                    <div class="w-full mx-auto h-[70vh] min-h-[600px] border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-gray-50">
                        <!-- Object untuk Browser Desktop Modern -->
                        <object 
                            data="{{ asset('assets/ManualBook_Atlas.pdf') }}#toolbar=1&navpanes=1&scrollbar=0&view=FitH" 
                            type="application/pdf" 
                            class="w-full h-full"
                        >
                            <!-- Teks fallback jika browser pengguna (misal: mobile) tidak mendukung PDF inline -->
                            <div class="flex flex-col items-center justify-center h-full p-6 text-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p class="text-gray-600 mb-6 max-w-md">Browser Anda tidak mendukung pratinjau PDF langsung (biasanya pada perangkat mobile). Silakan buka atau unduh file secara manual.</p>
                                <a href="{{ asset('assets/ManualBook_Atlas.pdf') }}" target="_blank" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Buka / Unduh Panduan PDF
                                </a>
                            </div>
                        </object>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
