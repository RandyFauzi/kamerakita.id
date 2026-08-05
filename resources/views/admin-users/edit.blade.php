<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Edit Admin: {{ $adminUser->name }}
            </h2>
            <a href="{{ route('admin-users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-150 border border-gray-200 rounded-xl font-semibold text-xs text-gray-700 uppercase hover:bg-gray-200 transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                
                <!-- Kiri: Info Admin (Desktop: Sidebar) -->
                <div class="lg:w-1/3 space-y-6">
                    <div class="bg-white rounded-[32px] shadow-sm border border-gray-150 p-6 flex flex-col items-center relative overflow-hidden">
                        <!-- Avatar Foto -->
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-4 border-[3px] border-white shadow-lg bg-gray-50 flex items-center justify-center relative z-10">
                            <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        
                        <!-- Nama & Kontak -->
                        <h2 class="text-[20px] font-bold text-gray-900 mb-1 text-center relative z-10">{{ $adminUser->name }}</h2>
                        <p class="text-[11px] text-gray-500 mb-4 font-medium text-center relative z-10">{{ $adminUser->email }}</p>
                        
                        <!-- Badge Status -->
                        <div class="flex items-center gap-1.5 text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100 relative z-10">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span class="text-[10px] font-bold tracking-wide uppercase">
                                {{ $adminUser->role ?? 'ADMIN' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Form Pengaturan (Desktop: Main Content) -->
                <div class="lg:w-2/3">
                    <form action="{{ route('admin-users.update', $adminUser) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="bg-white rounded-[32px] shadow-sm border border-gray-150 p-6 lg:p-8 space-y-6">
                            <div class="border-b border-gray-100 pb-4">
                                <span class="block text-xs font-black tracking-widest text-indigo-650 uppercase font-mono mb-1">PENGATURAN AKUN</span>
                                <h3 class="text-lg font-bold text-gray-900">Ubah Data & Password</h3>
                                <p class="text-xs text-gray-400">Kosongkan password jika tidak ingin menggantinya.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Admin <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $adminUser->name) }}" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $adminUser->email) }}" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                                    <input type="password" name="password" id="password" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            
                            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                                <a href="{{ route('admin-users.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
                                    Batal
                                </a>
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-amber-600 hover:to-orange-700 transition shadow-md shadow-orange-100">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>
