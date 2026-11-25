<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Models\TaxPeriod;
use App\Models\TaxRecord;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaxCalculationService
{
    /**
     * Generate or refresh withholding tax records and monthly invoice tax totals for YYYY-MM.
     */
    public function syncPeriod(string $month, int $actorId): TaxPeriod
    {
        $start = CarbonImmutable::createFromFormat('!Y-m', $month);
        $end = $start->addMonth();

        return DB::transaction(function () use ($month, $start, $end, $actorId): TaxPeriod {
            $period = TaxPeriod::firstOrCreate(
                ['period_month' => $month],
                ['status' => TaxPeriod::STATUS_DRAFT, 'created_by' => $actorId, 'updated_by' => $actorId]
            );

            $lockedPeriod = TaxPeriod::whereKey($period->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedPeriod->status, [TaxPeriod::STATUS_PAID, TaxPeriod::STATUS_REPORTED], true)) {
                throw ValidationException::withMessages([
                    'period_month' => 'Periode pajak ini sudah berstatus PAID/REPORTED dan dikunci.',
                ]);
            }

            $cycleIds = PaymentCycle::query()
                ->where(function ($q) use ($start, $end): void {
                    $q->where(function ($sub) use ($start, $end): void {
                        $sub->whereNotNull('period_end')
                            ->where('period_end', '>=', $start->toDateString())
                            ->where('period_end', '<', $end->toDateString());
                    })->orWhere(function ($sub) use ($start, $end): void {
                        $sub->whereNull('period_end')
                            ->whereNotNull('frozen_on')
                            ->where('frozen_on', '>=', $start->toDateString())
                            ->where('frozen_on', '<', $end->toDateString());
                    })->orWhere(function ($sub) use ($start, $end): void {
                        $sub->whereNull('period_end')
                            ->whereNull('frozen_on')
                            ->where('created_at', '>=', $start->startOfDay())
                            ->where('created_at', '<', $end->startOfDay());
                    });
                })
                ->pluck('id');

            $entries = PaymentCycleEntry::query()
                ->with('partner')
                ->whereIn('payment_cycle_id', $cycleIds)
                ->where('amount_to_pay_idr', '>', 0)
                ->get();

            foreach ($entries as $entry) {
                $existing = TaxRecord::query()
                    ->where('tax_period_id', $lockedPeriod->id)
                    ->where('payment_cycle_entry_id', $entry->id)
                    ->first();

                if ($existing && in_array($existing->status, [TaxRecord::STATUS_WITHHELD, TaxRecord::STATUS_REPORTED], true)) {
                    continue;
                }

                $computed = $this->calculateEntryTax($entry, $entry->partner);

                TaxRecord::updateOrCreate(
                    [
                        'tax_period_id' => $lockedPeriod->id,
                        'payment_cycle_entry_id' => $entry->id,
                    ],
                    [
                        'payment_cycle_id' => $entry->payment_cycle_id,
                        'partner_id' => $entry->partner_id,
                        'recipient_name' => $entry->partner?->full_name ?: $entry->worker_email,
                        'recipient_email' => $entry->worker_email,
                        'recipient_npwp' => $entry->partner?->npwp ?: $entry->partner?->nik,
                        'recipient_nik' => $entry->partner?->nik,
                        ...$computed,
                    ]
                );
            }

            $lockedPeriod->status = $lockedPeriod->status === TaxPeriod::STATUS_DRAFT && $entries->isNotEmpty()
                ? TaxPeriod::STATUS_CALCULATED
                : $lockedPeriod->status;
            $lockedPeriod->updated_by = $actorId;

            return $this->recalculatePeriodTotals($lockedPeriod, $start, $end);
        }, 3);
    }

    /**
     * Compute PPh 21 (PMK 168/2023) or PPh 23 withholding values for a single payroll entry.
     *
     * @return array<string, int|float|string|null>
     */
    public function calculateEntryTax(PaymentCycleEntry $entry, ?Partner $partner): array
    {
        $gross = max(0, (int) $entry->amount_to_pay_idr);
        $countryCode = strtoupper((string) ($partner?->country_code ?? 'ID'));

        if ($partner?->is_tax_exempt || ($countryCode !== '' && $countryCode !== 'ID')) {
            return [
                'tax_type' => TaxRecord::TYPE_PPH21,
                'tax_object_code' => 'NON-SUBJEK',
                'gross_amount_idr' => $gross,
                'dpp_amount_idr' => 0,
                'tax_rate_percent' => 0.0,
                'tax_amount_idr' => 0,
                'net_amount_idr' => $gross,
                'status' => TaxRecord::STATUS_EXEMPT,
                'notes' => $partner?->is_tax_exempt
                    ? 'Dikecualikan pajak manual'
                    : "Luar negeri ({$countryCode})",
            ];
        }

        if (($partner?->tax_entity_type ?? 'orang_pribadi') === 'badan') {
            $hasTaxId = ! empty($partner?->npwp);
            $rate = $hasTaxId ? 2.0 : 4.0;
            $dpp = $gross;
            $tax = (int) round($dpp * ($rate / 100));

            return [
                'tax_type' => TaxRecord::TYPE_PPH23,
                'tax_object_code' => '24-104-01',
                'gross_amount_idr' => $gross,
                'dpp_amount_idr' => $dpp,
                'tax_rate_percent' => $rate,
                'tax_amount_idr' => $tax,
                'net_amount_idr' => max(0, $gross - $tax),
                'status' => TaxRecord::STATUS_CALCULATED,
                'notes' => $hasTaxId ? 'Badan usaha ber-NPWP (2%)' : 'Badan usaha non-NPWP (4%)',
            ];
        }

        // Individual (Bukan Pegawai - PMK 168/2023): DPP = 50% Bruto, Tarif Ps. 17 Lapis 1 = 5% (efektif 2.5%).
        $dpp = (int) round($gross * 0.5);
        $rate = 5.0;
        $tax = (int) round($dpp * ($rate / 100));
        $hasNikOrNpwp = ! empty($partner?->npwp) || ! empty($partner?->nik);

        return [
            'tax_type' => TaxRecord::TYPE_PPH21,
            'tax_object_code' => '21-100-09',
            'gross_amount_idr' => $gross,
            'dpp_amount_idr' => $dpp,
            'tax_rate_percent' => $rate,
            'tax_amount_idr' => $tax,
            'net_amount_idr' => max(0, $gross - $tax),
            'status' => TaxRecord::STATUS_CALCULATED,
            'notes' => $hasNikOrNpwp ? 'Bukan pegawai (DPP 50% × 5%)' : 'NIK/NPWP belum dilengkapi',
        ];
    }

    public function recalculatePeriodTotals(
        TaxPeriod $period,
        ?CarbonImmutable $start = null,
        ?CarbonImmutable $end = null
    ): TaxPeriod {
        $start ??= CarbonImmutable::createFromFormat('!Y-m', $period->period_month);
        $end ??= $start->addMonth();

        $totals = TaxRecord::query()
            ->where('tax_period_id', $period->id)
            ->selectRaw("
                COALESCE(SUM(gross_amount_idr), 0) AS total_gross,
                COALESCE(SUM(CASE WHEN tax_type = 'PPH21' THEN tax_amount_idr ELSE 0 END), 0) AS total_pph21,
                COALESCE(SUM(CASE WHEN tax_type = 'PPH23' THEN tax_amount_idr ELSE 0 END), 0) AS total_pph23
            ")
            ->first();

        $period->update([
            'total_gross_idr' => (int) ($totals->total_gross ?? 0),
            'total_pph21_idr' => (int) ($totals->total_pph21 ?? 0),
            'total_pph23_idr' => (int) ($totals->total_pph23 ?? 0),
            'total_ppn_idr' => 0,
            'total_client_pph23_idr' => 0,
        ]);

        return $period->fresh();
    }

    /**
     * Build Coretax / e-Bupot compatible CSV rows for the given tax period.
     *
     * @return array{headers: list<string>, rows: list<list<string|int|float>>}
     */
    public function buildCoretaxCsv(TaxPeriod $period): array
    {
        $headers = [
            'Periode Pajak',
            'Jenis Pajak',
            'Kode Objek Pajak',
            'Nama Penerima',
            'Email',
            'NPWP / NITKU',
            'NIK',
            'Penghasilan Bruto (IDR)',
            'DPP (IDR)',
            'Tarif (%)',
            'PPh Terpotong (IDR)',
            'Net Diterima (IDR)',
            'No Bukti Potong',
            'Status',
            'Catatan',
        ];

        $rows = $period->records()
            ->get()
            ->map(function (TaxRecord $record) use ($period): array {
                return [
                    $period->period_month,
                    $record->tax_type,
                    $record->tax_object_code,
                    $record->recipient_name,
                    $record->recipient_email ?? '',
                    $record->recipient_npwp ?? '',
                    $record->recipient_nik ?? '',
                    $record->gross_amount_idr,
                    $record->dpp_amount_idr,
                    $record->tax_rate_percent,
                    $record->tax_amount_idr,
                    $record->net_amount_idr,
                    $record->bupot_number ?? '',
                    $record->status,
                    $record->notes ?? '',
                ];
            })
            ->all();

        return compact('headers', 'rows');
    }
}
