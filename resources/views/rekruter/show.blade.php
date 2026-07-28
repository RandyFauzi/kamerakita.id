<x-app-layout>
    <x-slot name="header">
        <div class="w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <div>
                <a href="{{ route('rekruter.index') }}" class="text-xs text-gray-400 hover:text-gray-600 transition mb-1 inline-flex items-center gap-1">
                    ← Kembali ke Daftar Rekruter
                </a>
                <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                    {{ $rekruter->full_name }}
                </h2>
                <p class="text-sm text-gray-400 mt-0.5">{{ $rekruter->mitra_id }} · Role: Rekruter</p>
            </div>
        </div>
    </x-slot>

    <div class="py-1 sm:py-4 space-y-4 sm:space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-2xl p-4 text-sm font-medium">
                ✅ {{ session('success') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Referral Code --}}
            <div class="bg-white rounded-2xl border border-gray-150 shadow-sm p-5">
                <div class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-2">Kode Referral</div>
                <div class="font-mono text-lg font-black text-indigo-600 tracking-wider">
                    {{ $rekruter->referral_code ?? '—' }}
                </div>
                <div class="text-xs text-gray-400 mt-1">Bagikan kode ini ke calon worker</div>
            </div>

            {{-- Total Recruited --}}
            <div class="bg-white rounded-2xl border border-gray-150 shadow-sm p-5">
                <div class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-2">Worker Direkrut</div>
                <div class="text-3xl font-black text-blue-600">{{ $rekruter->recruitedWorkers->count() }}</div>
                <div class="text-xs text-gray-400 mt-1">Total worker yang mendaftar</div>
            </div>

            {{-- Pending Commission --}}
            @php
                $pendingCommissions = $rekruter->recruiterCommissions->where('status', 'pending');
                $paidCommissions = $rekruter->recruiterCommissions->where('status', 'paid');
                $pendingAmount = $pendingCommissions->sum('commission_amount');
                $paidAmount = $paidCommissions->sum('commission_amount');
            @endphp
            <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-5">
                <div class="text-xs text-amber-600 uppercase tracking-widest font-bold mb-2">Komisi Belum Dibayar</div>
                <div class="text-2xl font-black text-amber-700">Rp {{ number_format($pendingAmount, 0, ',', '.') }}</div>
                <div class="text-xs text-amber-500 mt-1">{{ $pendingCommissions->count() }} milestone menunggu pembayaran</div>
            </div>

            {{-- Paid Commission --}}
            <div class="bg-green-50 rounded-2xl border border-green-100 shadow-sm p-5">
                <div class="text-xs text-green-600 uppercase tracking-widest font-bold mb-2">Komisi Telah Dibayar</div>
                <div class="text-2xl font-black text-green-700">Rp {{ number_format($paidAmount, 0, ',', '.') }}</div>
                <div class="text-xs text-green-500 mt-1">{{ $paidCommissions->count() }} milestone lunas</div>
            </div>
        </div>

        {{-- Commission Records --}}
        <div class="bg-white rounded-2xl border border-gray-150 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-sm">Riwayat Komisi Milestone (20 Jam)</h3>
                <p class="text-xs text-gray-400 mt-1">Setiap worker yang direkrut mencapai 20 jam video approved = Rp 100.000 komisi</p>
            </div>

            @if($rekruter->recruiterCommissions->isEmpty())
                <div class="p-10 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm text-gray-400">Belum ada worker rekrutan yang mencapai 20 jam.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <tr>
                                <th class="px-5 py-3 text-left">Worker</th>
                                <th class="px-5 py-3 text-center">Jam saat Milestone</th>
                                <th class="px-5 py-3 text-center">Komisi</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-center">Tanggal Lunas</th>
                                <th class="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($rekruter->recruiterCommissions as $commission)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800">{{ $commission->worker?->full_name ?? '—' }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $commission->worker?->mitra_id }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="font-bold text-gray-700">{{ number_format($commission->approved_hours_at_milestone, 1) }} jam</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="font-bold text-gray-800">Rp {{ number_format($commission->commission_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($commission->status === 'paid')
                                            <span class="inline-block bg-green-50 text-green-700 border border-green-100 px-3 py-1 rounded-lg text-xs font-bold">✓ Lunas</span>
                                        @else
                                            <span class="inline-block bg-amber-50 text-amber-700 border border-amber-100 px-3 py-1 rounded-lg text-xs font-bold">⏳ Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-xs text-gray-500">
                                            {{ $commission->paid_at ? $commission->paid_at->format('d M Y') : '—' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($commission->status === 'pending')
                                            @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                                                <form method="POST" action="{{ route('rekruter.commission.pay', $commission) }}"
                                                      onsubmit="return confirm('Tandai komisi ini sebagai lunas?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-green-700 transition">
                                                        ✓ Tandai Lunas
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="text-gray-300 text-xs">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Recruited Workers List --}}
        <div class="bg-white rounded-2xl border border-gray-150 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-sm">Worker Rekrutan</h3>
                <p class="text-xs text-gray-400 mt-1">Semua worker yang mendaftar menggunakan kode referral ini</p>
            </div>
            @if($rekruter->recruitedWorkers->isEmpty())
                <div class="p-8 text-center text-gray-400 text-sm">Belum ada worker yang mendaftar dengan kode referral ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <tr>
                                <th class="px-5 py-3 text-left">Nama Worker</th>
                                <th class="px-5 py-3 text-center">ID</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-center">Milestone 20 Jam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($rekruter->recruitedWorkers as $worker)
                                @php
                                    $workerCommission = $rekruter->recruiterCommissions->firstWhere('worker_partner_id', $worker->id);
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800">{{ $worker->full_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $worker->email }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-center font-mono text-xs text-gray-500">{{ $worker->mitra_id }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-block px-2 py-1 rounded-lg text-xs font-bold
                                            {{ $worker->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-500' }}">
                                            {{ ucfirst($worker->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($workerCommission)
                                            <span class="inline-block bg-green-50 text-green-700 border border-green-100 px-3 py-1 rounded-lg text-xs font-bold">✓ Tercapai</span>
                                        @else
                                            <span class="inline-block bg-gray-50 text-gray-400 border border-gray-100 px-3 py-1 rounded-lg text-xs">Belum tercapai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
