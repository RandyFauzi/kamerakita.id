@if(auth()->check() && in_array(auth()->user()->role, ['superadmin', 'admin']))

    <!-- Pembungkus Utama (Fixed di kanan tengah layar) -->
    <div 
        x-data="{ 
            isOpen: false, 
            decimalHours: '', 
            get minutes() { 
                return this.decimalHours ? Math.round(parseFloat(this.decimalHours) * 60) : 0 
            } 
        }" 
        class="fixed z-[999] right-0 top-1/2 -translate-y-1/2 flex items-center"
        @click.away="isOpen = false"
    >
        
        <!-- Panel Kalkulator (Muncul saat icon diklik) -->
        <div 
            x-show="isOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-10"
            class="mr-3 bg-white p-5 rounded-2xl shadow-2xl border border-gray-200 w-64" 
            style="display: none;"
            x-cloak
        >
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-bold text-gray-800 text-sm">Konversi Jam Klien</h4>
                <button @click="isOpen = false" class="text-gray-400 hover:text-red-500 font-bold">&times;</button>
            </div>
            
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1 font-medium">Jam Desimal (Contoh: 3.63)</label>
                <input 
                    type="number" 
                    step="0.01" 
                    x-model="decimalHours" 
                    placeholder="Contoh: 3.63"
                    class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow"
                >
            </div>
            
            <div class="bg-indigo-50 p-4 rounded-xl text-center border border-indigo-100">
                <span class="block text-xs text-indigo-600 mb-1 font-semibold">Total Menit Proporsional</span>
                <span class="text-3xl font-extrabold text-indigo-900" x-text="minutes"></span>
                <span class="text-sm font-medium text-indigo-700 block mt-1">Menit</span>
            </div>
        </div>

        <!-- Tombol Icon (Selalu terlihat) -->
        <button 
            @click="isOpen = !isOpen" 
            class="bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-l-xl shadow-lg transition-colors flex items-center justify-center border-y border-l border-indigo-800"
            title="Kalkulator Menit"
        >
            <!-- SVG Icon Kalkulator -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </button>
        
    </div>

@endif
