<x-app-layout>
    <x-slot name="header">
        <div class="w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                {{ __('Kelola Kode Referal & Grup') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-xs font-bold shadow-sm transition">
                ← Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-4 space-y-6">
        @if(session('success'))
            <div class="p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-100 flex items-center gap-2 animate-in fade-in duration-300" role="alert">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Add New Code Form -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-6 border border-gray-150 shadow-sm space-y-5 h-fit">
                <div>
                    <h3 class="font-extrabold text-base text-gray-900">Buat Kode Referal Baru</h3>
                    <p class="text-xs text-gray-400 mt-1">Gunakan kode ini untuk mengelompokkan kontributor saat pendaftaran.</p>
                </div>

                <form method="POST" action="{{ route('referral-codes.store') }}" class="space-y-4">
                    @csrf

                    <div class="bg-gray-50 border border-gray-150 rounded-xl p-3.5 space-y-1">
                        <span class="block text-[10px] font-bold text-gray-405 uppercase tracking-wider font-mono">FORMAT KODE OTOMATIS</span>
                        <span class="block text-xs text-gray-700 font-bold font-mono">KMK-[NO][HURUF ACAK]</span>
                        <p class="text-[10px] text-gray-400">Sistem akan men-generate kode unik secara otomatis (contoh: KMK-03ASQW).</p>
                    </div>

                    <div>
                        <label for="group_name" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Kelompok / Grup Baru</label>
                        <input id="group_name" type="text" name="group_name" value="{{ old('group_name') }}" required 
                               class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-300" 
                               placeholder="Contoh: Group D, Group Mytron">
                        <x-input-error :messages="$errors->get('group_name')" class="mt-1" />
                    </div>

                    <button type="submit" class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-sm">
                        Simpan Kode Referal
                    </button>
                </form>
            </div>

            <!-- Right Column: Codes List & Group Stats (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Group Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100/40 shadow-sm">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">TOTAL MITRA GROUP A</span>
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-3xl font-black text-slate-900">{{ $groupCounts['Group A'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 font-bold uppercase font-mono">Kontributor</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl p-5 border border-emerald-100/40 shadow-sm">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">TOTAL MITRA GROUP B</span>
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-3xl font-black text-slate-900">{{ $groupCounts['Group B'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 font-bold uppercase font-mono">Kontributor</span>
                        </div>
                    </div>
                </div>

                <!-- Codes Table -->
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-extrabold text-base text-gray-900">Daftar Kode Referal</h3>
                        <p class="text-xs text-gray-400 mt-1">Daftar kode aktif yang digunakan untuk pendaftaran.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500">
                            <thead class="text-xs text-gray-450 uppercase bg-gray-50/50 border-b border-gray-100 font-bold">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Kode Referal</th>
                                    <th scope="col" class="px-6 py-4">Grup Tujuan</th>
                                    <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($codes as $code)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 font-mono font-bold text-gray-900">
                                            {{ $code->code }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $code->group_name === 'Group B' ? 'bg-emerald-50 text-emerald-700 border border-emerald-250' : 'bg-blue-50 text-blue-700 border border-blue-250' }}">
                                                {{ $code->group_name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('referral-codes.destroy', $code->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kode ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-900 font-extrabold transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-10 text-center text-gray-400">
                                            Belum ada kode referal yang terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
