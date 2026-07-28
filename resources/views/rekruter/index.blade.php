<x-app-layout>
    <x-slot name="header">
        <div class="w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                Manajemen Rekruter
            </h2>
            <span class="text-xs text-gray-400 font-medium">Daftar Rekruter & Status Komisi</span>
        </div>
    </x-slot>

    <div class="py-1 sm:py-4 space-y-4 sm:space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-2xl p-4 text-sm font-medium">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 text-sm font-medium">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Search --}}
        <div class="bg-white rounded-2xl border border-gray-150 shadow-sm p-4 sm:p-6">
            <form method="GET" action="{{ route('rekruter.index') }}" class="flex gap-3">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama atau ID rekruter..."
                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
                <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-gray-700 transition">
                    Cari
                </button>
                @if($search)
                    <a href="{{ route('rekruter.index') }}" class="px-4 py-2.5 bg-gray-100 text-gray-600 text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-gray-200 transition">Reset</a>
                @endif
            </form>
        </div>

        {{-- Rekruter Table --}}
        <div class="bg-white rounded-2xl border border-gray-150 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 text-sm">Daftar Rekruter</h3>
                <span class="text-xs text-gray-400">Total: {{ $rekruterList->total() }} rekruter</span>
            </div>

            @if($rekruterList->isEmpty())
                <div class="p-12 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-400">Belum ada rekruter terdaftar.</p>
                    <p class="text-xs text-gray-300 mt-1">Rekruter dapat didaftarkan melalui halaman Data Mitra dengan memilih role "Rekruter".</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <tr>
                                <th class="px-5 py-3 text-left">Rekruter</th>
                                <th class="px-5 py-3 text-center">Kode Referral</th>
                                <th class="px-5 py-3 text-center">Worker Rekrutan</th>
                                <th class="px-5 py-3 text-center">Komisi Pending</th>
                                <th class="px-5 py-3 text-center">Komisi Lunas</th>
                                <th class="px-5 py-3 text-center">Total Komisi</th>
                                <th class="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($rekruterList as $rekruter)
                                @php
                                    $pendingCommissions = $rekruter->recruiterCommissions->where('status', 'pending');
                                    $paidCommissions = $rekruter->recruiterCommissions->where('status', 'paid');
                                    $pendingAmount = $pendingCommissions->sum('commission_amount');
                                    $paidAmount = $paidCommissions->sum('commission_amount');
                                    $totalAmount = $pendingAmount + $paidAmount;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800">{{ $rekruter->full_name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $rekruter->mitra_id }} · {{ $rekruter->email }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($rekruter->referral_code)
                                            <span class="inline-block font-mono text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-3 py-1 rounded-lg font-bold tracking-wider">
                                                {{ $rekruter->referral_code }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-700 font-bold text-sm">
                                            {{ $rekruter->recruited_workers_count }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($pendingAmount > 0)
                                            <span class="inline-block bg-amber-50 text-amber-700 border border-amber-100 px-3 py-1 rounded-lg text-xs font-bold">
                                                Rp {{ number_format($pendingAmount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($paidAmount > 0)
                                            <span class="inline-block bg-green-50 text-green-700 border border-green-100 px-3 py-1 rounded-lg text-xs font-bold">
                                                Rp {{ number_format($paidAmount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($totalAmount > 0)
                                            <span class="font-bold text-gray-700 text-sm">
                                                Rp {{ number_format($totalAmount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">Rp 0</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <a href="{{ route('rekruter.show', $rekruter) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-gray-700 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($rekruterList->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $rekruterList->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-app-layout>
