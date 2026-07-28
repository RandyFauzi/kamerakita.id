<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log Aktivitas Audit Trail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filter Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150 p-6">
                <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search Query -->
                    <div>
                        <label for="search" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cari Detail / IP</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                            placeholder="Kata kunci..." 
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label for="user_id" class="block text-xs font-bold text-gray-700 uppercase mb-1">Pengguna</label>
                        <select name="user_id" id="user_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Pengguna</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Activity Filter -->
                    <div>
                        <label for="activity" class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipe Aktivitas</label>
                        <select name="activity" id="activity" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Aktivitas</option>
                            @foreach($activities as $act)
                                <option value="{{ $act }}" {{ request('activity') == $act ? 'selected' : '' }}>
                                    {{ $act }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 px-4 rounded-xl transition duration-150 shadow-sm flex items-center justify-center">
                            Filter
                        </button>
                        <a href="{{ route('activity-logs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold py-2.5 px-4 rounded-xl transition duration-150 flex items-center justify-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-150">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs font-black uppercase text-gray-400 tracking-wider">
                                    <th class="pb-3 font-semibold">Waktu</th>
                                    <th class="pb-3 font-semibold">Pelaku</th>
                                    <th class="pb-3 font-semibold">Aktivitas</th>
                                    <th class="pb-3 font-semibold">Deskripsi</th>
                                    <th class="pb-3 font-semibold">Metode / IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                            {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}
                                            <span class="block text-[10px] text-gray-400">({{ $log->created_at->diffForHumans() }})</span>
                                        </td>
                                        <td class="py-4 whitespace-nowrap">
                                            @if($log->user)
                                                <span class="block font-bold text-gray-800">{{ $log->user->name }}</span>
                                                <span class="block text-[10px] text-gray-400 font-mono">{{ $log->user->email }}</span>
                                                <span class="inline-block mt-0.5 text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded {{ $log->user->role === 'superadmin' ? 'bg-red-50 text-red-700' : ($log->user->role === 'admin' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                                    {{ $log->user->role }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 font-mono italic">Sistem / Guest</span>
                                            @endif
                                        </td>
                                        <td class="py-4 whitespace-nowrap">
                                            @php
                                                $badgeClass = match(true) {
                                                    str_starts_with($log->activity, 'auth.') => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                    str_starts_with($log->activity, 'report.reject') => 'bg-red-50 text-red-700 border-red-100',
                                                    str_starts_with($log->activity, 'report.approve') => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                                    str_starts_with($log->activity, 'payment.') => 'bg-amber-50 text-amber-700 border-amber-100',
                                                    default => 'bg-slate-50 text-slate-700 border-slate-100',
                                                };
                                            @endphp
                                            <span class="inline-block text-[10px] font-black font-mono border px-2 py-0.5 rounded-md {{ $badgeClass }}">
                                                {{ $log->activity }}
                                            </span>
                                        </td>
                                        <td class="py-4 max-w-xs md:max-w-md">
                                            <span class="text-gray-700 leading-relaxed block">{{ $log->description }}</span>
                                        </td>
                                        <td class="py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                            <span class="block text-gray-700 font-bold">{{ $log->ip_address ?? 'N/A' }}</span>
                                            <span class="block text-[10px] text-gray-400 max-w-[150px] truncate" title="{{ $log->user_agent }}">
                                                {{ $log->user_agent }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400 italic">
                                            Belum ada log aktivitas yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
