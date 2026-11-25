<x-app-layout>
    <x-slot name="metaTitle">Dashboard Accountant &amp; Tax Manager — Kamerakita</x-slot>
    <x-slot name="metaDescription">Pusat akuntansi, jurnal keuangan, rekonsiliasi payroll, dan manajemen pajak KameraKita.</x-slot>

    <div class="space-y-6 pb-10">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <!-- Hero Card: Keuangan & Pajak Bulan Ini (KameraKita Blue Gradient) -->
        <div class="relative overflow-hidden rounded-2xl sm:rounded-[32px] bg-gradient-to-br from-blue-600 via-[#3b82f6] to-[#0ea5e9] p-5 sm:p-8 text-white shadow-lg shadow-blue-500/20 border border-blue-400/30">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-cyan-300/30 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-16 w-64 h-64 rounded-full bg-white/15 blur-3xl pointer-events-none"></div>
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/50 to-transparent pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-blue-100 font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                        ACCOUNTANT &amp; TAX MANAGER WORKSPACE · {{ $month }}
                    </span>
                    <h1 class="mt-3 text-3xl sm:text-4xl font-black tracking-tight tabular-nums drop-shadow-sm">
                        Rp{{ number_format($report['cashBalanceIdr'], 0, ',', '.') }}
                    </h1>
                    <p class="mt-1.5 text-xs sm:text-sm text-blue-50/90">
                        Saldo Kas Jurnal (Input Manual Real) · Profit Bulan Ini:
                        <strong class="text-white">
                            Rp{{ number_format($report['monthlyTotals']['profit'], 0, ',', '.') }}
                        </strong>
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ route('admin.finance.index') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 text-xs font-bold text-blue-700 hover:bg-blue-50 shadow-sm transition">
                        Jurnal Keuangan
                    </a>
                    <a href="{{ route('tax.index') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-xs font-bold text-white hover:bg-emerald-400 shadow-md shadow-emerald-600/20 transition">
                        Tax Center
                    </a>
                    <a href="{{ route('accounting.reports.index') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white/15 border border-white/25 backdrop-blur-sm px-4 py-3 text-xs font-bold text-white hover:bg-white/25 transition">
                        Laporan P&amp;L
                    </a>
                    <a href="{{ route('payments.manage') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white/15 border border-white/25 backdrop-blur-sm px-4 py-3 text-xs font-bold text-white hover:bg-white/25 transition">
                        Pembayaran
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 KPI Utama Akuntansi & Pajak -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Profit Bulan {{ $month }}</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black tabular-nums {{ $report['monthlyTotals']['profit'] < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        Rp{{ number_format($report['monthlyTotals']['profit'], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-500">Masuk Rp{{ number_format($report['monthlyTotals']['income'], 0, ',', '.') }} · Biaya Rp{{ number_format($report['monthlyTotals']['expense'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pajak PPh {{ $month }}</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black tabular-nums text-violet-700">
                        Rp{{ number_format($taxPeriod->totalWithholdingTaxIdr(), 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-500">
                        Status SPT: <strong class="uppercase text-slate-700">{{ $taxPeriod->status }}</strong>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Hutang Payroll Unpaid</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black tabular-nums text-amber-700">
                        Rp{{ number_format($report['obligations']['unpaid_payroll_idr'], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-500">Kewajiban transfer worker &amp; mitra</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Utang Pajak Belum Setor</span>
                <div>
                    <p class="text-xl sm:text-2xl font-black tabular-nums text-indigo-700">
                        Rp{{ number_format($report['obligations']['tax_payable_idr'], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-500">Akumulasi PPh 21 &amp; 23 periode aktif</p>
                </div>
            </div>
        </div>

        @if($missingTaxPartnersCount > 0)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-sm font-bold text-amber-900">{{ number_format($missingTaxPartnersCount) }} Partner/Mitra WNI belum memiliki NIK/NPWP lengkap</p>
                    <p class="text-xs text-amber-700 mt-0.5">Lengkapi NPWP/NIK dan tipe entitas (Orang Pribadi / Badan) agar kalkulasi PPh 21 &amp; PPh 23 di Tax Center akurat.</p>
                </div>
                <a href="{{ route('partners.index') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700 transition shrink-0">
                    Audit Data Mitra
                </a>
            </div>
        @endif

        <!-- Antrean Posting Payroll & Ringkasan Siklus -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="bg-white rounded-2xl sm:rounded-[28px] p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Siklus Payroll Belum Diposting ke Jurnal</h3>
                        <p class="text-xs text-slate-500">Opsional: catat beban jasa worker/mitra ke pengeluaran jurnal secara langsung.</p>
                    </div>
                    <a href="{{ route('payments.manage') }}" class="text-xs font-bold text-indigo-600 hover:underline">Kelola Pembayaran</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($report['unpostedCycles'] as $cycle)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $cycle->name }}</p>
                                <p class="text-xs text-slate-500">Total Pay: Rp{{ number_format($cycle->total_pay_idr, 0, ',', '.') }}</p>
                            </div>
                            <form action="{{ route('tax.post-cycle', $cycle) }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 transition whitespace-nowrap">
                                    Posting Beban
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-6 text-center">Semua siklus pembayaran aktif sudah tercatat atau belum ada siklus baru.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl sm:rounded-[28px] p-5 sm:p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Siklus Payroll Terbaru</h3>
                    <a href="{{ route('tax.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Buka Tax Center</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-slate-100">
                        <thead>
                            <tr class="text-slate-500 text-left">
                                <th class="py-2 font-semibold">Siklus</th>
                                <th class="py-2 font-semibold text-right">Total Bayar</th>
                                <th class="py-2 font-semibold text-right">Jurnal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentCycles as $cycle)
                                <tr>
                                    <td class="py-2.5 font-bold text-slate-900">{{ $cycle->name }}</td>
                                    <td class="py-2.5 text-right font-semibold tabular-nums">Rp{{ number_format($cycle->total_pay_idr, 0, ',', '.') }}</td>
                                    <td class="py-2.5 text-right">
                                        @if($cycle->journal_posted_at)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Posted</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-slate-400">Belum ada siklus pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
