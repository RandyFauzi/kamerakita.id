<x-app-layout>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Broadcast Notifikasi</h1>
            <p class="mt-1 text-sm text-gray-500">Kirim notifikasi langsung ke layar HP/PC pengguna (Web Push).</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 rounded-xl border border-green-100 flex items-start gap-3">
            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm font-medium text-green-800">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 rounded-xl border border-red-100 flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm font-medium text-red-800">{{ session('error') }}</div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ tab: 'direct', target: 'all' }">
        
        <!-- Tabs -->
        <div class="flex border-b border-gray-100 bg-gray-50/50">
            <button type="button" @click="tab = 'direct'" :class="tab === 'direct' ? 'border-blue-600 text-blue-700 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'" class="px-6 py-4 border-b-2 font-semibold text-sm transition-all">
                Kirim Sekarang
            </button>
            <button type="button" @click="tab = 'scheduled'" :class="tab === 'scheduled' ? 'border-blue-600 text-blue-700 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'" class="px-6 py-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2">
                Terjadwal
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-600">BETA</span>
            </button>
        </div>

        <form action="{{ route('admin.push-notifications.send') }}" method="POST" class="p-6">
            @csrf

            <!-- Target Audience -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Penerima (Target) <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none transition-all"
                           :class="target === 'all' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 bg-white hover:border-blue-300'">
                        <input type="radio" name="target" value="all" class="sr-only" x-model="target">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium" :class="target === 'all' ? 'text-blue-900' : 'text-gray-900'">Semua Pengguna</span>
                                <span class="mt-1 flex items-center text-sm" :class="target === 'all' ? 'text-blue-700' : 'text-gray-500'">Admin & Pekerja</span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-blue-600 transition-opacity" :class="target === 'all' ? 'opacity-100' : 'opacity-0'" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none transition-all"
                           :class="target === 'workers' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 bg-white hover:border-blue-300'">
                        <input type="radio" name="target" value="workers" class="sr-only" x-model="target">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium" :class="target === 'workers' ? 'text-blue-900' : 'text-gray-900'">Pekerja Saja</span>
                                <span class="mt-1 flex items-center text-sm" :class="target === 'workers' ? 'text-blue-700' : 'text-gray-500'">Notifikasi massal</span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-blue-600 transition-opacity" :class="target === 'workers' ? 'opacity-100' : 'opacity-0'" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none transition-all"
                           :class="target === 'admins' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 bg-white hover:border-blue-300'">
                        <input type="radio" name="target" value="admins" class="sr-only" x-model="target">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium" :class="target === 'admins' ? 'text-blue-900' : 'text-gray-900'">Admin Saja</span>
                                <span class="mt-1 flex items-center text-sm" :class="target === 'admins' ? 'text-blue-700' : 'text-gray-500'">Internal info</span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-blue-600 transition-opacity" :class="target === 'admins' ? 'opacity-100' : 'opacity-0'" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </label>
                </div>
            </div>

            <hr class="my-6 border-gray-100">

            <!-- Scheduled Datetime -->
            <div x-show="tab === 'scheduled'" class="mb-6 p-5 bg-blue-50/50 rounded-xl border border-blue-100" style="display: none;" x-transition>
                <label for="scheduled_at" class="block text-sm font-semibold text-blue-900 mb-2">Pilih Waktu Pengiriman (WIB) <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at" 
                       :required="tab === 'scheduled'"
                       :disabled="tab !== 'scheduled'"
                       class="w-full md:w-1/2 rounded-xl border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-white text-sm" value="{{ old('scheduled_at') }}">
                <p class="text-xs text-blue-600 mt-1.5">Sistem akan menahan notifikasi ini dan otomatis mengirimkannya pada waktu yang Anda pilih.</p>
                @error('scheduled_at') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Title -->
            <div class="mb-5">
                <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Judul Notifikasi <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" required placeholder="Ketik judul notifikasi..."
                       class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white text-sm" value="{{ old('title') }}">
                @error('title') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Body -->
            <div class="mb-5">
                <label for="body" class="block text-sm font-semibold text-gray-700 mb-2">Isi Pesan <span class="text-red-500">*</span></label>
                <textarea name="body" id="body" rows="3" required placeholder="Ketik isi pesan notifikasi..."
                          class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white text-sm">{{ old('body') }}</textarea>
                @error('body') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- URL Target -->
            <div class="mb-6">
                <label for="url" class="block text-sm font-semibold text-gray-700 mb-2">Link Tujuan Saat Diklik <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <input type="url" name="url" id="url" placeholder="Cth: https://kamerakitaid.site/panduan"
                       class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white text-sm" value="{{ old('url') }}">
                <p class="text-xs text-gray-500 mt-1.5">Jika dikosongkan, notifikasi akan mengarahkan pengguna ke halaman utama.</p>
                @error('url') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Action -->
            <div class="flex justify-end mt-8">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span x-text="tab === 'scheduled' ? 'Simpan Jadwal Notifikasi' : 'Kirim Broadcast Notifikasi'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
