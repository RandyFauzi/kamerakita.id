<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900">Tax Center — PPh 21, PPh 23 &amp; PPN</h2>
                <p class="text-xs text-slate-500 mt-0.5">Rekonsiliasi pemotongan pajak payroll, faktur klien, arsip bukti potong, dan ekspor Coretax.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <input type="month" name="month" value="{{ $month }}" class="rounded-xl border-slate-200 text-sm">
                    <button class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Tampilkan</button>
                </form>
                <form action="{{ route('tax.periods.sync') }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition">
                        Hitung / Sync Pajak
                    </button>
                </form>
                <a href="{{ route('tax.export-csv', ['month' => $month]) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                    Ekspor CSV Coretax
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif

        <!-- 4 KPI Pajak Periode -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Bruto Payroll</span>
                <p class="mt-2 text-xl sm:text-2xl font-black text-slate-900 tabular-nums">Rp{{ number_format($period->total_gross_idr, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total PPh 21 (Perorangan)</span>
                <p class="mt-2 text-xl sm:text-2xl font-black text-indigo-700 tabular-nums">Rp{{ number_format($period->total_pph21_idr, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total PPh 23 (Badan)</span>
                <p class="mt-2 text-xl sm:text-2xl font-black text-violet-700 tabular-nums">Rp{{ number_format($period->total_pph23_idr, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">PPN &amp; Pot. PPh23 Klien</span>
                <p class="mt-2 text-lg sm:text-xl font-black text-emerald-700 tabular-nums">PPN Rp{{ number_format($period->total_ppn_idr, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-0.5">PPh23 dipotong klien: Rp{{ number_format($period->total_client_pph23_idr, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Status Pelaporan & NTPN Periode -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono">STATUS SPT MASA {{ $month }}</span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 rounded-full text-xs font-black border {{ $period->status === 'REPORTED' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($period->status === 'PAID' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                            {{ $period->status }}
                        </span>
                        @if($period->ntpn_code)
                            <span class="text-xs text-slate-600 font-mono">NTPN: <strong>{{ $period->ntpn_code }}</strong></span>
                        @endif
                    </div>
                </div>

                <form action="{{ route('tax.periods.status', $period) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-2 w-full lg:w-auto">
                    @csrf
                    <select name="status" class="rounded-xl border-slate-200 text-xs font-semibold">
                        @foreach(\App\Models\TaxPeriod::ALL_STATUSES as $st)
                            <option value="{{ $st }}" @selected($period->status === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="ntpn_code" value="{{ old('ntpn_code', $period->ntpn_code) }}" placeholder="Kode NTPN Setor..." class="rounded-xl border-slate-200 text-xs">
                    <input type="date" name="payment_date" value="{{ old('payment_date', $period->payment_date?->toDateString()) }}" class="rounded-xl border-slate-200 text-xs">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">Simpan Status</button>
                </form>
            </div>
        </div>

        <!-- Tabel Pemotongan PPh 21 / PPh 23 Payroll -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Daftar Pemotongan PPh 21 / PPh 23</h3>
                    <p class="text-xs text-slate-500">Default PMK 168/2023 untuk orang pribadi (DPP 50% × 5%) &amp; 2% / 4% untuk badan usaha.</p>
                </div>
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama / email / NPWP..." class="rounded-xl border-slate-200 text-xs">
                    <select name="tax_type" class="rounded-xl border-slate-200 text-xs">
                        <option value="">Semua Jenis</option>
                        <option value="PPH21" @selected(($filters['tax_type'] ?? '') === 'PPH21')>PPh 21</option>
                        <option value="PPH23" @selected(($filters['tax_type'] ?? '') === 'PPH23')>PPh 23</option>
                    </select>
                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" name="missing_tax_id" value="1" @checked(!empty($filters['missing_tax_id'])) class="rounded border-slate-300">
                        Belum ada NPWP/NIK
                    </label>
                    <button class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Penerima &amp; NPWP/NIK</th>
                            <th class="px-4 py-3 text-left font-semibold">Jenis &amp; Kode</th>
                            <th class="px-4 py-3 text-right font-semibold">Bruto</th>
                            <th class="px-4 py-3 text-right font-semibold">DPP</th>
                            <th class="px-4 py-3 text-right font-semibold">PPh</th>
                            <th class="px-4 py-3 text-right font-semibold">Net Diterima</th>
                            <th class="px-4 py-3 text-left font-semibold">Bukti Potong &amp; Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($records as $rec)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-900">{{ $rec->recipient_name }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $rec->recipient_email }}</p>
                                    <p class="text-[11px] font-mono {{ $rec->recipient_npwp || $rec->recipient_nik ? 'text-slate-600' : 'text-rose-600 font-bold' }}">
                                        NPWP/NIK: {{ $rec->recipient_npwp ?: ($rec->recipient_nik ?: 'BELUM DIISI') }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full font-bold {{ $rec->tax_type === 'PPH23' ? 'bg-violet-50 text-violet-700' : 'bg-indigo-50 text-indigo-700' }}">{{ $rec->tax_type }}</span>
                                    <p class="text-[11px] text-slate-500 font-mono mt-1">{{ $rec->tax_object_code }} ({{ $rec->tax_rate_percent }}%)</p>
                                    <p class="text-[10px] text-slate-400">{{ $rec->notes }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums">Rp{{ number_format($rec->gross_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 tabular-nums">Rp{{ number_format($rec->dpp_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-rose-700 tabular-nums">Rp{{ number_format($rec->tax_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700 tabular-nums">Rp{{ number_format($rec->net_amount_idr, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 space-y-2">
                                    <form action="{{ route('tax.records.update', $rec) }}" method="POST" class="flex flex-wrap items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="tax_type" value="{{ $rec->tax_type }}">
                                        <input type="hidden" name="tax_object_code" value="{{ $rec->tax_object_code }}">
                                        <input type="hidden" name="tax_rate_percent" value="{{ $rec->tax_rate_percent }}">
                                        <input type="text" name="recipient_npwp" value="{{ $rec->recipient_npwp }}" placeholder="NPWP..." class="w-28 rounded-lg border-slate-200 text-[11px] py-1">
                                        <input type="text" name="bupot_number" value="{{ $rec->bupot_number }}" placeholder="No Bupot..." class="w-28 rounded-lg border-slate-200 text-[11px] py-1">
                                        <select name="status" class="rounded-lg border-slate-200 text-[11px] py-1">
                                            @foreach(['CALCULATED', 'WITHHELD', 'REPORTED', 'EXEMPT'] as $rst)
                                                <option value="{{ $rst }}" @selected($rec->status === $rst)>{{ $rst }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-lg bg-slate-800 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-slate-700">Simpan</button>
                                    </form>
                                    <form action="{{ route('tax.records.bupot', $rec) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-1.5">
                                        @csrf
                                        <input type="file" name="bupot_file" accept=".pdf,.jpg,.jpeg,.png" required class="text-[10px] w-40">
                                        <button type="submit" class="rounded-lg bg-indigo-50 px-2 py-1 text-[11px] font-bold text-indigo-700 hover:bg-indigo-100">Upload Bupot</button>
                                        @if($rec->bupot_path)
                                            <a href="{{ route('tax.records.bupot.download', $rec) }}" class="text-[11px] font-bold text-emerald-700 underline">Unduh</a>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                    Belum ada data pajak periode {{ $month }}. Klik <strong>Hitung / Sync Pajak</strong> di atas setelah membuat Siklus Pembayaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
                <div class="p-4 border-t border-slate-100">{{ $records->links() }}</div>
            @endif
        </div>

        <!-- Pajak Invoice Klien & Kurs Valas -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Pajak Invoice Klien &amp; Kurs Valas ({{ $month }})</h3>
                <p class="text-xs text-slate-500">Atur PPN, PPh 23 dipotong klien lokal, nomor faktur pajak, kurs IDR, serta posting otomatis ke Jurnal Keuangan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Invoice</th>
                            <th class="px-4 py-3 text-right font-semibold">Tagihan</th>
                            <th class="px-4 py-3 text-left font-semibold">PPN (%) / PPh23 / Faktur / Kurs IDR</th>
                            <th class="px-4 py-3 text-right font-semibold">Jurnal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-900">{{ $inv->invoice_no }} ({{ $inv->status }})</p>
                                    <p class="text-slate-500">{{ $inv->client_name }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-bold tabular-nums">{{ $inv->currency }} {{ number_format($inv->total_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('tax.invoices.update', $inv) }}" method="POST" class="flex flex-wrap items-center gap-1.5">
                                        @csrf
                                        <label class="inline-flex items-center gap-1 text-[11px]">
                                            <input type="checkbox" name="is_domestic" value="1" @checked($inv->is_domestic) class="rounded border-slate-300">
                                            Lokal
                                        </label>
                                        <input type="number" step="0.01" name="ppn_rate" value="{{ $inv->ppn_rate }}" placeholder="PPN %" class="w-16 rounded-lg border-slate-200 text-[11px] py-1" title="PPN %">
                                        <input type="number" step="0.01" name="pph23_withheld" value="{{ $inv->pph23_withheld }}" placeholder="PPh23" class="w-20 rounded-lg border-slate-200 text-[11px] py-1" title="PPh 23 Dipotong">
                                        <input type="number" step="0.01" name="idr_exchange_rate" value="{{ $inv->idr_exchange_rate }}" placeholder="Kurs IDR" class="w-24 rounded-lg border-slate-200 text-[11px] py-1" title="Kurs IDR">
                                        <input type="text" name="faktur_pajak_no" value="{{ $inv->faktur_pajak_no }}" placeholder="No Faktur Pajak" class="w-28 rounded-lg border-slate-200 text-[11px] py-1">
                                        <button type="submit" class="rounded-lg bg-slate-800 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-slate-700">Update</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($inv->journal_posted_at)
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold">Sudah Posting</span>
                                    @elseif($inv->status === 'PAID')
                                        <form action="{{ route('tax.post-invoice', $inv) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-indigo-700">Posting Jurnal</button>
                                        </form>
                                    @else
                                        <span class="text-slate-400">Menunggu PAID</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Tidak ada invoice klien pada bulan {{ $month }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
