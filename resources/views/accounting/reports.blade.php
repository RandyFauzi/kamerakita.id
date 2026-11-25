<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900">Laporan Akuntansi (Laba Rugi &amp; Kewajiban)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ringkasan pendapatan, beban operasional/payroll, posisi kas, piutang klien, dan kewajiban pajak.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <input type="month" name="month" value="{{ $report['month'] }}" class="rounded-xl border-slate-200 text-sm">
                    <input type="number" name="year" value="{{ $report['year'] }}" min="2020" max="2100" class="w-24 rounded-xl border-slate-200 text-sm">
                    <button class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Tampilkan</button>
                </form>
                <a href="{{ route('accounting.reports.export-csv', ['month' => $report['month']]) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                    Ekspor Buku Besar CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif

        <!-- Neraca Operasional Singkat -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-2xl bg-slate-900 text-white p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Kas Jurnal Total</span>
                <p class="mt-2 text-xl sm:text-2xl font-black tabular-nums">Rp{{ number_format($report['cashBalanceIdr'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Laba Bersih ({{ $report['month'] }})</span>
                <p class="mt-2 text-xl sm:text-2xl font-black tabular-nums {{ $report['monthlyTotals']['profit'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                    Rp{{ number_format($report['monthlyTotals']['profit'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Hutang Payroll Unpaid</span>
                <p class="mt-2 text-xl sm:text-2xl font-black text-amber-700 tabular-nums">Rp{{ number_format($report['obligations']['unpaid_payroll_idr'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Utang Pajak Belum Disetor</span>
                <p class="mt-2 text-xl sm:text-2xl font-black text-violet-700 tabular-nums">Rp{{ number_format($report['obligations']['tax_payable_idr'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Rincian P&L Kategori Bulan Dipilih -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Rincian Pendapatan ({{ $report['month'] }})</h3>
                <div class="space-y-2 text-sm">
                    @forelse($report['monthlyIncomeByCategory'] as $row)
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-600">{{ $row->category }}</span>
                            <span class="font-bold text-blue-700 tabular-nums">Rp{{ number_format($row->total, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada pemasukan pada bulan ini.</p>
                    @endforelse
                    <div class="flex justify-between pt-2 font-black text-slate-900">
                        <span>Total Pendapatan</span>
                        <span class="tabular-nums">Rp{{ number_format($report['monthlyTotals']['income'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Rincian Beban Operasional ({{ $report['month'] }})</h3>
                <div class="space-y-2 text-sm">
                    @forelse($report['monthlyExpenseByCategory'] as $row)
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-600">{{ $row->category }}</span>
                            <span class="font-bold text-rose-700 tabular-nums">Rp{{ number_format($row->total, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada beban pada bulan ini.</p>
                    @endforelse
                    <div class="flex justify-between pt-2 font-black text-slate-900">
                        <span>Total Beban</span>
                        <span class="tabular-nums">Rp{{ number_format($report['monthlyTotals']['expense'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Laba Rugi Tahunan (12 Bulan) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Laporan Laba Rugi Tahun {{ $report['year'] }}</h3>
                    <p class="text-xs text-slate-500">Akumulasi tahunan: Pemasukan Rp{{ number_format($report['annualSummary']['income'], 0, ',', '.') }} · Biaya Rp{{ number_format($report['annualSummary']['expense'], 0, ',', '.') }} · Laba Rp{{ number_format($report['annualSummary']['profit'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Bulan</th>
                            <th class="px-4 py-3 text-right font-semibold">Pemasukan</th>
                            <th class="px-4 py-3 text-right font-semibold">Beban</th>
                            <th class="px-4 py-3 text-right font-semibold">Laba Bersih</th>
                            <th class="px-4 py-3 text-right font-semibold">Bagi Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($report['annualRows'] as $row)
                            <tr class="{{ $row['month'] === $report['month'] ? 'bg-indigo-50/40 font-semibold' : '' }}">
                                <td class="px-4 py-2.5 text-slate-900">{{ $row['label'] }}</td>
                                <td class="px-4 py-2.5 text-right text-blue-700 tabular-nums">Rp{{ number_format($row['income'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-right text-rose-700 tabular-nums">Rp{{ number_format($row['expense'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-right font-bold tabular-nums {{ $row['profit'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">Rp{{ number_format($row['profit'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-right text-slate-600 tabular-nums">Rp{{ number_format($row['distributed'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
