<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{FinanceEntry, FinancePeriod};
use App\Services\FinanceJournal;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceController extends Controller
{
    public function index(Request $request, FinanceJournal $journal)
    {
        $request->validate(['month' => ['nullable', 'date_format:Y-m'], 'search' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', 'in:income,expense,capital,distribution']]);
        $month = $request->input('month') ?: now('Asia/Makassar')->format('Y-m');
        $totals = $journal->totals($month);
        $previous = $journal->totals(CarbonImmutable::createFromFormat('!Y-m', $month)->subMonth()->format('Y-m'));
        $balance = $journal->balance();
        $setting = $journal->setting();
        $period = FinancePeriod::where('month', $month)->first();
        $allocation = $period?->distribution ?? $journal->allocation($totals['profit'], $setting);
        $largest = $journal->entries($month)->where('type', 'expense')->selectRaw('category, SUM(amount) AS total')->groupBy('category')->orderByDesc('total')->first();
        $entries = $journal->entries($month)->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('description', 'like', '%'.$request->input('search').'%')->orWhere('category', 'like', '%'.$request->input('search').'%')))
            ->orderByDesc('entry_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = CarbonImmutable::createFromFormat('!Y-m', $month)->subMonths($i);
            $trend[] = ['label' => $date->translatedFormat('M'), ...$journal->totals($date->format('Y-m'))];
        }
        return view('admin.finance.index', compact('month', 'totals', 'previous', 'balance', 'setting', 'period', 'allocation', 'largest', 'entries', 'trend'));
    }

    private function entryData(Request $request): array
    {
        return $request->validate(['entry_date' => ['required', 'date_format:Y-m-d'], 'type' => ['required', 'in:income,expense,capital'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'], 'category' => ['required', 'string', 'max:80'], 'description' => ['required', 'string', 'max:255']]);
    }

    public function store(Request $request, FinanceJournal $journal)
    {
        $data = $this->entryData($request);
        DB::transaction(function () use ($data, $journal, $request) {
            $journal->openPeriod(substr($data['entry_date'], 0, 7));
            FinanceEntry::create($data + ['created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        }, 3);
        return redirect()->route('admin.finance.index', ['month' => substr($data['entry_date'], 0, 7)])->with('success', 'Transaksi berhasil dicatat.');
    }

    public function update(Request $request, FinanceEntry $entry, FinanceJournal $journal)
    {
        $data = $this->entryData($request);
        DB::transaction(function () use ($entry, $data, $journal, $request) {
            $months = array_unique([$entry->entry_date->format('Y-m'), substr($data['entry_date'], 0, 7)]);
            sort($months);
            foreach ($months as $month) $journal->openPeriod($month);
            $locked = FinanceEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();
            if ($locked->type === 'distribution' || $locked->entry_date->format('Y-m') !== $entry->entry_date->format('Y-m')) abort(409);
            $locked->update($data + ['updated_by' => $request->user()->id]);
        }, 3);
        return redirect()->route('admin.finance.index', ['month' => substr($data['entry_date'], 0, 7)])->with('success', 'Transaksi diperbarui.');
    }

    public function destroy(Request $request, FinanceEntry $entry, FinanceJournal $journal)
    {
        $month = $entry->entry_date->format('Y-m');
        DB::transaction(function () use ($entry, $month, $journal, $request) {
            $journal->openPeriod($month);
            $locked = FinanceEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();
            if ($locked->type === 'distribution' || $locked->entry_date->format('Y-m') !== $month) abort(409);
            $locked->update(['updated_by' => $request->user()->id]);
            $locked->delete();
        }, 3);
        return back()->with('success', 'Transaksi dihapus dari jurnal.');
    }

    public function settings(Request $request, FinanceJournal $journal)
    {
        $data = $request->validate(['reserve' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'recipients' => ['required', 'array', 'min:1', 'max:10'], 'recipients.*.name' => ['required', 'string', 'max:80', 'distinct:ignore_case'],
            'recipients.*.percent' => ['required', 'numeric', 'gt:0', 'max:100', 'decimal:0,2']]);
        $recipients = array_map(fn ($r) => ['name' => trim($r['name']), 'bps' => (int) round((float) $r['percent'] * 100)], $data['recipients']);
        if (array_sum(array_column($recipients, 'bps')) !== 10000) throw ValidationException::withMessages(['recipients' => 'Total persentase penerima harus tepat 100%.']);
        DB::transaction(fn () => $journal->setting()->update(['reserve_bps' => (int) round((float) $data['reserve'] * 100), 'recipients' => $recipients]));
        return back()->with('success', 'Komposisi pembagian profit disimpan.');
    }

    public function distribute(Request $request, FinanceJournal $journal)
    {
        $data = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $journal->distribute($data['month'], $request->user()->id);
        return redirect()->route('admin.finance.index', ['month' => $data['month']])->with('success', 'Pembagian profit dicatat. Jurnal bulan ini dikunci.');
    }
}
