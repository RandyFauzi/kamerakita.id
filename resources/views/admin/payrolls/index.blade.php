@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen Payroll / Tagihan</h2>
            <p class="text-gray-500 text-sm mt-1">Manual approval untuk tagihan worker berdasarkan task yang sudah di-approve.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Buat Payroll Baru (Unpaid) -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-900">Buat Payroll Baru</h3>
                <p class="text-sm text-gray-500">Worker yang punya task "Approved" tapi belum ditagihkan.</p>
            </div>
            <div class="p-5">
                <form action="{{ route('payrolls.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Worker</label>
                        <select name="atlas_worker_id" required class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Pilih Worker Siap Cair --</option>
                            @foreach($unpaidWorkers as $worker)
                                @if($worker->atlasWorker)
                                    <option value="{{ $worker->atlas_worker_id }}">
                                        {{ $worker->atlasWorker->atlas_email }} ({{ round($worker->total_approved_minutes / 60, 1) }} Jam - {{ $worker->total_tasks }} Tasks)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai (Cutoff)</label>
                            <input type="date" name="period_start" required class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Akhir (Cutoff)</label>
                            <input type="date" name="period_end" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Rate Pekerja (Rp / Jam)</label>
                        <!-- Defaulting to 60000 for MVP testing -->
                        <input type="number" name="rate_per_hour" required value="60000" class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-colors">
                        Hitung & Buat Invoice Payroll
                    </button>
                    <p class="text-xs text-center text-gray-400 mt-2">Sistem akan mengunci task sesuai periode tanpa menghapus data aslinya.</p>
                </form>
            </div>
        </div>

        <!-- Histori Payrolls -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-[500px]">
            <div class="p-5 border-b border-gray-100 bg-gray-50 shrink-0">
                <h3 class="text-lg font-bold text-gray-900">Histori Tagihan Payroll</h3>
            </div>
            <div class="flex-1 overflow-y-auto p-0">
                <ul class="divide-y divide-gray-100">
                    @forelse($payrolls as $pr)
                        <li class="p-5 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $pr->atlasWorker->atlas_email ?? 'Unknown' }}</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Periode: {{ $pr->period_start->format('d M Y') }} - {{ $pr->period_end->format('d M Y') }}</p>
                                    <p class="text-xs font-semibold text-indigo-600 mt-1">{{ round($pr->total_approved_minutes / 60, 1) }} Jam Approved</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-gray-900">Rp {{ number_format($pr->amount_rupiah, 0, ',', '.') }}</p>
                                    @if($pr->status === 'PAID')
                                        <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">LUNAS</span>
                                    @else
                                        <div class="flex flex-col items-end gap-2 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800 uppercase tracking-wider">BELUM DIBAYAR</span>
                                            <form action="{{ route('payrolls.mark_paid', $pr) }}" method="POST">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Tandai sebagai Lunas?')" class="text-[10px] bg-gray-900 text-white px-3 py-1 rounded-md hover:bg-gray-800">TANDAI LUNAS</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="p-10 text-center text-gray-500 text-sm">Belum ada histori payroll yang digenerate.</li>
                    @endforelse
                </ul>
            </div>
            @if($payrolls->hasPages())
                <div class="p-3 border-t border-gray-100">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
