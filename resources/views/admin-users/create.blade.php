<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Tambah Akun Internal
            </h2>
            <a href="{{ route('admin-users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-xl font-semibold text-xs text-gray-700 uppercase hover:bg-gray-200 transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin-users.store') }}" method="POST" class="space-y-8" x-data="{ selectedRole: @js(old('role', 'accountant_tax')) }">
                @csrf

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-[32px] border border-gray-200 p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <span class="block text-xs font-black tracking-widest text-indigo-600 uppercase font-mono mb-1">AKUN INTERNAL</span>
                        <h3 class="text-lg font-bold text-gray-900">Informasi &amp; Role Akun Internal</h3>
                        <p class="text-xs text-gray-500 mt-1">Pilih role sesuai tugas (Admin, Finance, atau Accountant &amp; Tax Manager). Akun langsung aktif tanpa onboarding worker.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="role" class="block text-sm font-semibold text-gray-700">Role Akses <span class="text-red-500">*</span></label>
                        <select name="role" id="role" x-model="selectedRole" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($roleOptions as $roleValue => $meta)
                                <option value="{{ $roleValue }}">{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                        <div class="rounded-xl bg-slate-50 border border-slate-200/80 px-3.5 py-2.5 text-xs text-slate-600">
                            @foreach($roleOptions as $roleValue => $meta)
                                <p x-show="selectedRole === '{{ $roleValue }}'" x-cloak>{{ $meta['description'] }}</p>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="password" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin-users.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 transition shadow-md shadow-indigo-100">
                        Simpan Akun Internal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
