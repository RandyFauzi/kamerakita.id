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
        $state = ['today' => now('Asia/Makassar')->toDateString(), 'month' => $month, 'closed' => (bool) $period?->closed_at, 'rows' => $entries->map(fn ($e) => $this->row($e))->values(),
            'totals' => $totals, 'balance' => $balance, 'allocation' => $allocation, 'trend' => $trend,
            'previousProfit' => $previous['profit'], 'largest' => $largest ? ['category' => $largest->category, 'total' => (int) $largest->total] : null,
            'recipients' => array_map(fn ($r) => ['name' => $r['name'], 'percent' => $r['bps'] / 100], $setting->recipients),
            'indexUrl' => route('admin.finance.index'), 'storeUrl' => route('admin.finance.store'),
            'settingsUrl' => route('admin.finance.settings'), 'distributeUrl' => route('admin.finance.distribute')];
        if ($request->expectsJson()) return response()->json($state);
        return view('admin.finance.index', compact('state', 'month', 'entries', 'totals'));
    }

    private function row(FinanceEntry $entry): array
    {
        return ['id' => $entry->id, 'client_key' => $entry->client_key, 'revision' => $entry->revision,
            'entry_date' => $entry->entry_date->toDateString(), 'type' => $entry->type, 'amount' => $entry->amount,
            'category' => $entry->category, 'description' => $entry->description];
    }

    private function entryData(Request $request): array
    {
        return $request->validate(['entry_date' => ['required', 'date_format:Y-m-d'], 'type' => ['required', 'in:income,expense,capital'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'], 'category' => ['required', 'string', 'max:80'], 'description' => ['required', 'string', 'max:255']]);
    }

    public function store(Request $request, FinanceJournal $journal)
    {
        $data = $this->entryData($request);
        $key = $request->expectsJson() ? $request->validate(['client_key' => ['required', 'uuid']])['client_key'] : null;
        $entry = DB::transaction(function () use ($data, $journal, $request, $key) {
            if ($key && ($existing = FinanceEntry::withTrashed()->where('client_key', $key)->first())) {
                abort_if($existing->trashed(), 409, 'Baris ini sudah dihapus. Gunakan baris baru.');
                return $existing;
            }
            $journal->openPeriod(substr($data['entry_date'], 0, 7));
            // A period lock serializes concurrent retries of the same draft.
            if ($key && ($existing = FinanceEntry::withTrashed()->where('client_key', $key)->first())) {
                abort_if($existing->trashed(), 409, 'Baris ini sudah dihapus. Gunakan baris baru.');
                return $existing;
            }
            return FinanceEntry::create($data + ['client_key' => $key, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        }, 3);
        if ($request->expectsJson()) {
            $row = $this->row($entry);
            foreach ($data as $field => $value) {
                if ((string) $row[$field] !== (string) $value) return response()->json(['message' => 'Versi sebelumnya sudah tersimpan. Tekan Coba lagi untuk menyimpan perubahan terbaru.', 'entry' => $row], 409);
            }
            return response()->json(['entry' => $row]);
        }
        return redirect()->route('admin.finance.index', ['month' => substr($data['entry_date'], 0, 7)])->with('success', 'Transaksi berhasil dicatat.');
    }

    public function update(Request $request, FinanceEntry $entry, FinanceJournal $journal)
    {
        $data = $this->entryData($request);
        if ($request->expectsJson()) $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        $entry = DB::transaction(function () use ($entry, $data, $journal, $request) {
            $months = array_unique([$entry->entry_date->format('Y-m'), substr($data['entry_date'], 0, 7)]);
            sort($months);
            foreach ($months as $month) $journal->openPeriod($month);
            $locked = FinanceEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();
            if ($locked->type === 'distribution' || $locked->entry_date->format('Y-m') !== $entry->entry_date->format('Y-m')) abort(409, 'Baris berubah. Muat ulang sebelum melanjutkan.');
            if ($request->expectsJson() && $locked->revision !== (int) $request->input('revision')) abort(409, 'Baris sudah diubah pengguna lain. Salin isian Anda lalu muat ulang.');
            $locked->update($data + ['updated_by' => $request->user()->id, 'revision' => $locked->revision + 1]);
            return $locked;
        }, 3);
        if ($request->expectsJson()) return response()->json(['entry' => $this->row($entry)]);
        return redirect()->route('admin.finance.index', ['month' => substr($data['entry_date'], 0, 7)])->with('success', 'Transaksi diperbarui.');
    }

    public function destroy(Request $request, FinanceEntry $entry, FinanceJournal $journal)
    {
        $month = $entry->entry_date->format('Y-m');
        if ($request->expectsJson()) $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($entry, $month, $journal, $request) {
            $journal->openPeriod($month);
            $locked = FinanceEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();
            if ($locked->type === 'distribution' || $locked->entry_date->format('Y-m') !== $month) abort(409);
            if ($request->expectsJson() && $locked->revision !== (int) $request->input('revision')) abort(409, 'Baris berubah. Muat ulang sebelum menghapus.');
            $locked->update(['updated_by' => $request->user()->id]);
            $locked->delete();
        }, 3);
        if ($request->expectsJson()) return response()->json(['deleted' => true]);
        return back()->with('success', 'Transaksi dihapus dari jurnal.');
    }

    public function settings(Request $request, FinanceJournal $journal)
    {
        $data = $request->validate(['recipients' => ['required', 'array', 'min:1', 'max:10'], 'recipients.*.name' => ['required', 'string', 'max:80', 'distinct:ignore_case'],
            'recipients.*.percent' => ['required', 'numeric', 'gt:0', 'max:100', 'decimal:0,2']]);
        $recipients = array_map(fn ($r) => ['name' => trim($r['name']), 'bps' => (int) round((float) $r['percent'] * 100)], $data['recipients']);
        if (array_sum(array_column($recipients, 'bps')) !== 10000) throw ValidationException::withMessages(['recipients' => 'Total persentase penerima harus tepat 100%.']);
        DB::transaction(fn () => $journal->setting()->update(['reserve_bps' => 0, 'recipients' => $recipients]));
        if ($request->expectsJson()) return response()->json(['saved' => true]);
        return back()->with('success', 'Komposisi pembagian profit disimpan.');
    }

    public function distribute(Request $request, FinanceJournal $journal)
    {
        $data = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $journal->distribute($data['month'], $request->user()->id);
        if ($request->expectsJson()) return response()->json(['saved' => true]);
        return redirect()->route('admin.finance.index', ['month' => $data['month']])->with('success', 'Pembagian profit dicatat. Jurnal bulan ini dikunci.');
    }
}
