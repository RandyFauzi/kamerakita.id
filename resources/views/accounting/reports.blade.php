<x-app-layout>
    <x-slot name="metaTitle">Laporan Akuntansi — Kamerakita</x-slot>
    <x-slot name="metaDescription">Laporan Laba Rugi (P&amp;L), rincian kategori pemasukan &amp; beban, serta kewajiban pajak dan payroll.</x-slot>

    <div class="space-y-6 pb-12">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ $errors->first() }}</div>
        @endif

        <!-- Hero Control Banner (KameraKita Blue Gradient) -->
        <div class="relative overflow-hidden rounded-2xl sm:rounded-[32px] bg-gradient-to-br from-blue-600 via-[#3b82f6] to-[#0ea5e9] p-5 sm:p-8 text-white shadow-lg shadow-blue-500/20 border border-blue-400/30">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-cyan-300/30 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-16 w-64 h-64 rounded-full bg-white/15 blur-3xl pointer-events-none"></div>
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/50 to-transparent pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-blue-100 font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                        LAPORAN AKUNTANSI
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        Laba Rugi · {{ \Carbon\Carbon::createFromFormat('!Y-m', $report['month'])->translatedFormat('F Y') }}
                    </h1>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                    <form method="GET" class="flex items-center gap-2 bg-white border border-white/50 rounded-2xl p-1.5 shadow-sm">
                        <input type="month" name="month" value="{{ $report['month'] }}" class="bg-transparent border-0 text-slate-800 text-xs sm:text-sm font-semibold focus:ring-0 px-2.5 py-1.5">
                        <input type="number" name="year" value="{{ $report['year'] }}" min="2020" max="2100" class="w-20 bg-slate-100 border-0 rounded-lg text-slate-800 text-xs font-semibold text-center focus:ring-0 py-1.5">
                        <button type="submit" class="rounded-xl bg-blue-50 hover:bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-700 transition">Buka</button>
                    </form>

                    <a href="{{ route('accounting.reports.export-csv', ['month' => $report['month']]) }}" class="flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 hover:bg-emerald-400 px-4 py-3 text-xs font-bold text-white shadow-md shadow-emerald-600/20 transition whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Ekspor Buku Besar CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Neraca Operasional Singkat -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kas Jurnal Total</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black text-slate-900 tabular-nums">Rp{{ number_format($report['cashBalanceIdr'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Saldo kumulatif jurnal</p>
                </div>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Laba Bersih ({{ $report['month'] }})</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black tabular-nums {{ $report['monthlyTotals']['profit'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                        Rp{{ number_format($report['monthlyTotals']['profit'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pendapatan dikurangi beban</p>
                </div>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Hutang Payroll Unpaid</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black text-amber-700 tabular-nums">Rp{{ number_format($report['obligations']['unpaid_payroll_idr'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Belum ditransfer ke tim</p>
                </div>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Utang Pajak Belum Setor</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black text-violet-700 tabular-nums">Rp{{ number_format($report['obligations']['tax_payable_idr'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">PPh 21 &amp; 23 periode berjalan</p>
                </div>
            </div>
        </div>

        <!-- Rincian P&L Kategori Bulan Dipilih -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="bg-white rounded-2xl sm:rounded-[28px] border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-4">Rincian Pendapatan ({{ $report['month'] }})</h3>
                <div class="space-y-2.5 text-sm">
                    @forelse($report['monthlyIncomeByCategory'] as $row)
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-600">{{ $row->category }}</span>
                            <span class="font-bold text-blue-700 tabular-nums">Rp{{ number_format($row->total, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada pemasukan pada bulan ini.</p>
                    @endforelse
                    <div class="flex justify-between pt-2 font-black text-slate-900">
                        <span>Total Pendapatan</span>
                        <span class="tabular-nums">Rp{{ number_format($report['monthlyTotals']['income'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl sm:rounded-[28px] border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-4">Rincian Beban Operasional ({{ $report['month'] }})</h3>
                <div class="space-y-2.5 text-sm">
                    @forelse($report['monthlyExpenseByCategory'] as $row)
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-600">{{ $row->category }}</span>
                            <span class="font-bold text-rose-700 tabular-nums">Rp{{ number_format($row->total, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada beban pada bulan ini.</p>
                    @endforelse
                    <div class="flex justify-between pt-2 font-black text-slate-900">
                        <span>Total Beban</span>
                        <span class="tabular-nums">Rp{{ number_format($report['monthlyTotals']['expense'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Laba Rugi Tahunan (12 Bulan) -->
        <div class="bg-white rounded-2xl sm:rounded-[28px] border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Laporan Laba Rugi Tahun {{ $report['year'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Akumulasi: Pemasukan <strong>Rp{{ number_format($report['annualSummary']['income'], 0, ',', '.') }}</strong> ·
                        Biaya <strong>Rp{{ number_format($report['annualSummary']['expense'], 0, ',', '.') }}</strong> ·
                        Laba <strong>Rp{{ number_format($report['annualSummary']['profit'], 0, ',', '.') }}</strong>
                    </p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Bulan</th>
                            <th class="px-5 py-3 text-right font-semibold">Pemasukan</th>
                            <th class="px-5 py-3 text-right font-semibold">Beban</th>
                            <th class="px-5 py-3 text-right font-semibold">Laba Bersih</th>
                            <th class="px-5 py-3 text-right font-semibold">Bagi Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($report['annualRows'] as $row)
                            <tr class="{{ $row['month'] === $report['month'] ? 'bg-indigo-50/40 font-semibold' : '' }}">
                                <td class="px-5 py-3 text-slate-900">{{ $row['label'] }}</td>
                                <td class="px-5 py-3 text-right text-blue-700 tabular-nums">Rp{{ number_format($row['income'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-rose-700 tabular-nums">Rp{{ number_format($row['expense'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-bold tabular-nums {{ $row['profit'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">Rp{{ number_format($row['profit'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-slate-600 tabular-nums">Rp{{ number_format($row['distributed'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
