<x-app-layout>
    <x-slot name="metaTitle">Dashboard Accountant &amp; Tax Manager — Kamerakita</x-slot>
    <x-slot name="metaDescription">Pusat akuntansi, rekonsiliasi payroll, invoice klien, dan manajemen pajak KameraKita.</x-slot>

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

        <!-- Hero Card: Keuangan & Pajak Bulan Ini -->
        <div class="relative overflow-hidden rounded-2xl sm:rounded-[32px] bg-gradient-to-br from-slate-900 via-[#162033] to-indigo-950 p-5 sm:p-8 text-white shadow-lg border border-slate-800/80">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-16 w-64 h-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-indigo-200/80 font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        ACCOUNTANT &amp; TAX MANAGER WORKSPACE · {{ $month }}
                    </span>
                    <h1 class="mt-3 text-3xl sm:text-4xl font-black tracking-tight tabular-nums">
                        Rp{{ number_format($report['cashBalanceIdr'], 0, ',', '.') }}
                    </h1>
                    <p class="mt-1.5 text-xs sm:text-sm text-slate-300">
                        Saldo Kas Jurnal Keseluruhan · Profit Bulan Ini:
                        <strong class="{{ $report['monthlyTotals']['profit'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                            Rp{{ number_format($report['monthlyTotals']['profit'], 0, ',', '.') }}
                        </strong>
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ route('admin.finance.index') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 text-xs font-bold text-slate-900 hover:bg-slate-100 transition">
                        Jurnal Keuangan
                    </a>
                    <a href="{{ route('tax.index') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-xs font-bold text-slate-950 hover:bg-emerald-400 transition">
                        Tax Center
                    </a>
                    <a href="{{ route('accounting.reports.index') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white/10 border border-white/15 px-4 py-3 text-xs font-bold text-white hover:bg-white/20 transition">
                        Laporan P&amp;L
                    </a>
                    <a href="{{ route('invoices.index') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white/10 border border-white/15 px-4 py-3 text-xs font-bold text-white hover:bg-white/20 transition">
                        Invoice Klien
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 KPI Utama Akuntansi & Pajak -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Profit Bulan {{ $month }}</span>
                <p class="mt-2 text-xl sm:text-2xl font-black tabular-nums {{ $report['monthlyTotals']['profit'] < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    Rp{{ number_format($report['monthlyTotals']['profit'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Masuk Rp{{ number_format($report['monthlyTotals']['income'], 0, ',', '.') }} · Biaya Rp{{ number_format($report['monthlyTotals']['expense'], 0, ',', '.') }}</p>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pajak Periode {{ $month }}</span>
                <p class="mt-2 text-xl sm:text-2xl font-black tabular-nums text-violet-700">
                    Rp{{ number_format($taxPeriod->totalWithholdingTaxIdr() + $taxPeriod->total_ppn_idr, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Status: <strong class="uppercase text-slate-700">{{ $taxPeriod->status }}</strong> · PPh21/23 &amp; PPN
                </p>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Hutang Payroll Unpaid</span>
                <p class="mt-2 text-xl sm:text-2xl font-black tabular-nums text-amber-700">
                    Rp{{ number_format($report['obligations']['unpaid_payroll_idr'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Kewajiban transfer worker &amp; mitra</p>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Piutang Klien Aktif</span>
                <p class="mt-2 text-xl sm:text-2xl font-black tabular-nums text-blue-700">
                    ${{ number_format($report['obligations']['receivables_usd'], 2) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">{{ $report['obligations']['receivables_count'] }} invoice berstatus ISSUED/SENT</p>
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

        <!-- Antrean Posting Jurnal Otomatis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="bg-white rounded-2xl sm:rounded-[28px] p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Invoice PAID Belum Diposting ke Jurnal</h3>
                        <p class="text-xs text-slate-500">Konversi ke IDR &amp; catat pemasukan ke buku besar</p>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Semua Invoice</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($report['unpostedInvoices'] as $inv)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $inv->invoice_no }} · {{ $inv->client_name }}</p>
                                <p class="text-xs text-slate-500">{{ $inv->currency }} {{ number_format($inv->total_amount, 2) }} · {{ $inv->invoice_date?->translatedFormat('d M Y') }}</p>
                            </div>
                            <form action="{{ route('tax.post-invoice', $inv) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @if(strtoupper($inv->currency) !== 'IDR')
                                    <input type="number" step="1" min="1" name="idr_exchange_rate" value="{{ $inv->idr_exchange_rate ?? 16000 }}" required class="w-28 rounded-lg border-slate-200 text-xs font-semibold" title="Kurs IDR">
                                @endif
                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition whitespace-nowrap">
                                    Posting Jurnal
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Semua invoice PAID sudah tercatat di Jurnal Keuangan.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl sm:rounded-[28px] p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Siklus Payroll Belum Diposting ke Jurnal</h3>
                        <p class="text-xs text-slate-500">Catat beban jasa worker/mitra ke pengeluaran bulanan</p>
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
                                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800 transition whitespace-nowrap">
                                    Posting Beban
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Semua siklus pembayaran aktif sudah tercatat di Jurnal Keuangan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Ringkasan Terbaru -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="bg-white rounded-2xl sm:rounded-[28px] p-5 sm:p-6 border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Invoice Klien Terbaru</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-slate-100">
                        <thead>
                            <tr class="text-slate-500 text-left">
                                <th class="py-2 font-semibold">No Invoice</th>
                                <th class="py-2 font-semibold">Klien</th>
                                <th class="py-2 font-semibold text-right">Nilai</th>
                                <th class="py-2 font-semibold text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentInvoices as $inv)
                                <tr>
                                    <td class="py-2.5 font-bold text-slate-900">{{ $inv->invoice_no }}</td>
                                    <td class="py-2.5 text-slate-600">{{ $inv->client_name }}</td>
                                    <td class="py-2.5 text-right font-semibold tabular-nums">{{ $inv->currency }} {{ number_format($inv->total_amount, 2) }}</td>
                                    <td class="py-2.5 text-right"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ $inv->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-slate-400">Belum ada invoice.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl sm:rounded-[28px] p-5 sm:p-6 border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Siklus Payroll Terbaru</h3>
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
