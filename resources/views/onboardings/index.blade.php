<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Pendaftar Baru
                </h2>
                <p class="text-xs text-gray-400 mt-1">Daftar calon kontributor/mitra yang baru mendaftar.</p>
            </div>
            <div class="flex items-center gap-2 bg-white px-4 py-2 border border-gray-150 rounded-2xl shadow-sm text-xs font-mono text-gray-500">
                <span>Total Registrasi:</span>
                <strong class="text-indigo-650 font-bold">{{ \App\Models\FastworkOnboarding::count() }}</strong>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ deleteModalOpen: false, deleteActionUrl: '', deleteItemName: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-xl text-sm text-emerald-800 font-semibold shadow-sm animate-in fade-in duration-200">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter & Search Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150 p-6">
                <form action="{{ route('admin.onboardings.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1 w-full">
                        <label for="search" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 font-mono">Cari Nama / WhatsApp / Perangkat / Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   class="block w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white placeholder-gray-300"
                                   placeholder="Masukkan kata kunci pencarian...">
                        </div>
                    </div>
                    
                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="submit" class="flex-1 md:flex-none px-6 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-sm">
                            Cari Data
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.onboardings.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-widest rounded-xl transition text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col" class="w-16 px-4 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">No.</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Username Fastwork</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. WhatsApp</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe Perangkat Apple</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Headstrap</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($onboardings as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                    <td class="w-16 px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-500">
                                        {{ $onboardings->firstItem() + $loop->index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $item->full_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-555 font-mono text-xs">
                                        {{ $item->fastwork_username ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->whatsapp_number) }}" target="_blank" class="text-blue-650 hover:underline flex items-center gap-1">
                                            <span>{{ $item->whatsapp_number }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-indigo-50 text-indigo-700 border-indigo-200">
                                            {{ $item->device_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($item->has_headstrap)
                                            <span class="inline-flex items-center justify-center w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-6 h-6 bg-rose-100 text-rose-600 rounded-full">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ $item->created_at->translatedFormat('d F Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            type="button"
                                            @click="deleteModalOpen = true; deleteActionUrl = '{{ route('admin.onboardings.destroy', $item) }}'; deleteItemName = '{{ $item->full_name }}'"
                                            class="p-1.5 text-gray-500 hover:text-red-650 hover:bg-red-50 rounded-lg transition"
                                            title="Hapus"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <span class="text-sm">Belum ada pendaftar baru.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($onboardings->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $onboardings->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Delete Confirmation Modal (Alpine.js) -->
        <div 
            x-show="deleteModalOpen" 
            class="fixed inset-0 z-50 overflow-y-auto"
            x-cloak
            style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div 
                    x-show="deleteModalOpen"
                    x-transition:enter="ease-out duration-350"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
                    @click="deleteModalOpen = false"
                ></div>

                <!-- Spacer -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div 
                    x-show="deleteModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-middle bg-white rounded-[32px] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 p-8"
                >
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Konfirmasi Hapus Data</h3>
                            <p class="text-sm text-gray-400 mt-2">
                                Apakah Anda yakin ingin menghapus data pelamar <strong class="text-gray-700 font-semibold" x-text="deleteItemName"></strong> dari daftar onboarding? Tindakan ini bersifat permanen.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button 
                            type="button" 
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-250 text-gray-700 font-bold text-xs uppercase tracking-widest rounded-xl transition"
                            @click="deleteModalOpen = false"
                        >
                            Batal
                        </button>
                        <form :action="deleteActionUrl" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-md shadow-rose-100"
                            >
                                Hapus Permanen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
