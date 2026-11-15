<?php

namespace App\Services;

use App\Models\{FinanceEntry, FinancePeriod, FinanceSetting};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceJournal
{
    public function setting(): FinanceSetting
    {
        return FinanceSetting::firstOrCreate(['id' => 1], ['reserve_bps' => 2000, 'recipients' => []]);
    }

    public function entries(string $month)
    {
        $start = CarbonImmutable::createFromFormat('!Y-m', $month);
        return FinanceEntry::where('entry_date', '>=', $start->toDateString())
            ->where('entry_date', '<', $start->addMonth()->toDateString());
    }

    public function totals(string $month): array
    {
        $sums = $this->entries($month)->selectRaw('type, SUM(amount) AS total')->groupBy('type')->pluck('total', 'type');
        $income = (int) ($sums['income'] ?? 0);
        $expense = (int) ($sums['expense'] ?? 0);
        return ['income' => $income, 'expense' => $expense, 'profit' => $income - $expense,
            'distributed' => (int) ($sums['distribution'] ?? 0)];
    }

    public function balance(): int
    {
        return (int) FinanceEntry::selectRaw("COALESCE(SUM(CASE WHEN type IN ('income', 'capital') THEN amount ELSE -amount END), 0) AS balance")->value('balance');
    }

    public function allocation(int $profit, FinanceSetting $setting): array
    {
        $positive = max(0, $profit);
        $reserve = intdiv($positive, 10000) * $setting->reserve_bps + intdiv(($positive % 10000) * $setting->reserve_bps, 10000);
        $pool = max(0, $profit) - $reserve;
        $rows = [];
        foreach ($setting->recipients as $recipient) {
            $weighted = ($pool % 10000) * $recipient['bps'];
            $rows[] = $recipient + ['amount' => intdiv($pool, 10000) * $recipient['bps'] + intdiv($weighted, 10000), 'remainder' => $weighted % 10000];
        }
        // Largest remainder allocation keeps every rupiah accounted for.
        if ($rows && array_sum(array_column($rows, 'bps')) === 10000) {
            $order = array_keys($rows);
            usort($order, fn ($a, $b) => ($rows[$b]['remainder'] <=> $rows[$a]['remainder']) ?: ($a <=> $b));
            $left = $pool - array_sum(array_column($rows, 'amount'));
            for ($i = 0; $i < $left; $i++) $rows[$order[$i]]['amount']++;
        }
        return ['profit' => $profit, 'reserve' => $reserve, 'pool' => $pool, 'reserve_bps' => $setting->reserve_bps, 'recipients' => $rows];
    }

    public function openPeriod(string $month): FinancePeriod
    {
        FinancePeriod::firstOrCreate(['month' => $month]);
        $period = FinancePeriod::where('month', $month)->lockForUpdate()->firstOrFail();
        if ($period->closed_at) throw ValidationException::withMessages(['month' => 'Bulan ini sudah dibagikan dan dikunci. Jurnal tidak dapat diubah.']);
        return $period;
    }

    public function distribute(string $month, int $actor): void
    {
        DB::transaction(function () use ($month, $actor) {
            if ($month > now('Asia/Makassar')->format('Y-m')) throw ValidationException::withMessages(['month' => 'Bulan mendatang belum dapat dibagikan.']);
            $period = $this->openPeriod($month);
            $setting = FinanceSetting::whereKey($this->setting()->id)->lockForUpdate()->firstOrFail();
            if (!$setting->recipients || array_sum(array_column($setting->recipients, 'bps')) !== 10000) {
                throw ValidationException::withMessages(['recipients' => 'Atur penerima dengan total persentase 100% terlebih dahulu.']);
            }
            $allocation = $this->allocation($this->totals($month)['profit'], $setting);
            if ($allocation['pool'] <= 0) throw ValidationException::withMessages(['month' => 'Belum ada profit yang dapat dibagikan.']);
            if ($this->balance() < $allocation['pool']) throw ValidationException::withMessages(['month' => 'Saldo kas jurnal tidak cukup untuk pembagian ini.']);
            foreach ($allocation['recipients'] as $recipient) {
                if ($recipient['amount'] === 0) continue;
                $date = $month === now('Asia/Makassar')->format('Y-m') ? now('Asia/Makassar')->toDateString() : CarbonImmutable::createFromFormat('!Y-m', $month)->endOfMonth()->toDateString();
                FinanceEntry::create(['entry_date' => $date,
                    'type' => 'distribution', 'category' => 'Pembagian profit', 'description' => 'Profit '.$month.' · '.$recipient['name'],
                    'amount' => $recipient['amount'], 'finance_period_id' => $period->id, 'created_by' => $actor, 'updated_by' => $actor]);
            }
            $period->update(['closed_at' => now(), 'closed_by' => $actor, 'distribution' => $allocation]);
        }, 3);
    }
}
