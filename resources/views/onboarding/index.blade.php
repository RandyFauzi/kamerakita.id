<x-guest-layout>
    <div class="text-center space-y-6 py-4">
        <div class="mb-8">
            <h3 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">Portal Pendaftar Baru</h3>
            <p class="text-xs text-gray-400 mt-1">Gabung menjadi Mitra Kontributor Data KameraKita AI.</p>
            
            <div class="mt-4 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-left text-[11px] text-slate-500 leading-relaxed">
                Selamat datang di portal pendaftaran <strong>KameraKita AI</strong>. Silakan pilih opsi di bawah ini untuk mendaftar atau bertanya langsung kepada tim kami.
            </div>
        </div>

        <div class="space-y-4">
            <a href="{{ route('onboarding.register') }}" class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm uppercase tracking-widest rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                <span>Daftar Sekarang</span>
            </a>
            
            <a href="https://wa.me/6285389933194?text=Halo%20Leader!%20Saya%20tertarik%20bergabung%20dengan%20KameraKita%20AI.%20Boleh%20bantu%20jelaskan%20bagaimana%20sistem%20kerja%20dan%20persyaratannya%3F" target="_blank" class="w-full py-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold text-sm uppercase tracking-widest rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                <span>Tanya dengan Leader</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </a>
        </div>
    </div>
</x-guest-layout>
