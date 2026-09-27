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

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.push-notifications.send') }}" method="POST" class="p-6">
            @csrf

            <!-- Target Audience -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Penerima (Target) <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                        <input type="radio" name="target" value="all" class="peer sr-only" checked>
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-blue-500" aria-hidden="true"></span>
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">Semua Pengguna</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">Admin & Pekerja</span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-blue-600 opacity-0 peer-checked:opacity-100" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                        <input type="radio" name="target" value="workers" class="peer sr-only">
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-blue-500" aria-hidden="true"></span>
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">Pekerja Saja</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">Notifikasi massal</span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-blue-600 opacity-0 peer-checked:opacity-100" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                        <input type="radio" name="target" value="admins" class="peer sr-only">
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-blue-500" aria-hidden="true"></span>
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">Admin Saja</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">Internal info</span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 text-blue-600 opacity-0 peer-checked:opacity-100" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </label>
                </div>
            </div>

            <hr class="my-6 border-gray-100">

            <!-- Title -->
            <div class="mb-5">
                <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Judul Notifikasi <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" required placeholder="Cth: Pengumuman Penting, Gaji Sudah Cair!"
                       class="w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white text-sm" value="{{ old('title') }}">
                @error('title') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Body -->
            <div class="mb-5">
                <label for="body" class="block text-sm font-semibold text-gray-700 mb-2">Isi Pesan <span class="text-red-500">*</span></label>
                <textarea name="body" id="body" rows="3" required placeholder="Cth: Halo semuanya, target minggu ini sudah tercapai. Silakan cek dashboard masing-masing."
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
                    Kirim Broadcast Notifikasi
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
