<?php

namespace App\Services;

use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentCycleCsvImporter
{
    public function import(
        UploadedFile $file,
        ?string $cycleName = null,
        int $workerRateIdr = 60000,
        ?int $overrideRevenueIdr = null,
        ?int $userId = null,
    ): PaymentCycle {
        $raw = file_get_contents($file->getRealPath());
        if ($raw === false || trim($raw) === '') {
            throw ValidationException::withMessages(['csv_file' => 'File CSV kosong atau tidak dapat dibaca.']);
        }

        // Strip UTF-8 BOM
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $delimiter = $this->detectDelimiter($lines);

        $rows = array_map(fn (string $line) => str_getcsv($line, $delimiter), $lines);

        $metadata = $this->extractHeaderMetadata($rows);
        [$headerIndex, $columns] = $this->locateWorkerHeader($rows);

        if ($headerIndex === null) {
            throw ValidationException::withMessages([
                'csv_file' => 'Header kolom "Worker email" dan "Billable hours" tidak ditemukan di file CSV.',
            ]);
        }

        $parsedRows = [];
        $summaryRevenue = null;

        for ($i = $headerIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $joined = strtolower(trim(implode(' ', $row)));
            if ($joined === '') {
                continue;
            }

            // Capture bottom summary values for the kamerakita-ai sheet
            $rowRevenue = $this->extractLabeledSummaryIdr($row, 'revenue');
            if ($rowRevenue !== null) {
                $summaryRevenue = $rowRevenue;
                continue;
            }

            if ($this->isSummaryOrStopRow($row, $columns['worker_email'])) {
                // Stop immediately if a second table header starts below the main sheet
                $firstCell = strtolower(trim((string) ($row[$columns['worker_email']] ?? '')));
                if (str_starts_with($firstCell, 'worker email')) {
                    break;
                }
                continue;
            }

            $email = strtolower(trim((string) ($row[$columns['worker_email']] ?? '')));
            if ($email === '' || ! str_contains($email, '@')) {
                continue;
            }

            $billable = $this->parseDecimal($row[$columns['billable']] ?? 0);
            $rejected = $columns['rejected'] !== null ? $this->parseDecimal($row[$columns['rejected']] ?? 0) : 0.0;
            $notChecked = $columns['not_checked'] !== null ? $this->parseDecimal($row[$columns['not_checked']] ?? 0) : 0.0;
            $total = $columns['total'] !== null ? $this->parseDecimal($row[$columns['total']] ?? 0) : round($billable + $rejected + $notChecked, 2);

            $share = $columns['approved_share'] !== null
                ? $this->parseShare($row[$columns['approved_share']] ?? 0)
                : (($billable + $rejected) > 0 ? $billable / ($billable + $rejected) : 0.0);

            $recordings = $columns['recordings'] !== null ? (int) $this->parseDecimal($row[$columns['recordings']] ?? 0) : 0;
            $reason = $columns['reason'] !== null ? trim((string) ($row[$columns['reason']] ?? '')) : '';
            $onList = $columns['on_list'] !== null ? trim((string) ($row[$columns['on_list']] ?? '')) : 'Yes';
            $removed = $columns['removed'] !== null ? trim((string) ($row[$columns['removed']] ?? '')) : '';

            $rawPay = $columns['amount_to_pay'] !== null ? ($row[$columns['amount_to_pay']] ?? null) : null;
            $amountToPay = ($rawPay !== null && trim((string) $rawPay) !== '')
                ? $this->parseIdr($rawPay)
                : (int) round($billable * $workerRateIdr);

            $parsedRows[$email] = [
                'worker_email' => $email,
                'billable_hours' => round($billable, 2),
                'rejected_hours' => round($rejected, 2),
                'not_checked_hours' => round($notChecked, 2),
                'total_hours' => round($total, 2),
                'approved_share' => round($share, 6),
                'recordings_count' => max(0, $recordings),
                'main_rejection_reason' => ($reason === '' || $reason === '0') ? null : $reason,
                'on_list_now' => $onList !== '' ? $onList : 'Yes',
                'removed_from_program' => ($removed === '' || $removed === '0') ? null : $removed,
                'amount_to_pay_idr' => max(0, $amountToPay),
                'sort_order' => count($parsedRows),
            ];
        }

        if ($parsedRows === []) {
            throw ValidationException::withMessages(['csv_file' => 'Tidak ada baris worker yang valid di file CSV.']);
        }

        $emails = array_keys($parsedRows);
        $partnersByEmail = Partner::whereIn(DB::raw('LOWER(email)'), $emails)
            ->get()
            ->keyBy(fn (Partner $p) => strtolower((string) $p->email));

        $atlasWorkersByEmail = AtlasWorker::with('user.partner')
            ->whereIn(DB::raw('LOWER(atlas_email)'), $emails)
            ->get()
            ->keyBy(fn (AtlasWorker $w) => strtolower((string) $w->atlas_email));

        $revenueIdr = $overrideRevenueIdr ?? $summaryRevenue ?? 0;
        $totalUsd = $metadata['client_total_usd'] ?: round($metadata['client_billable_hours'] * $metadata['client_rate_usd'], 2);
        $usdToIdr = ($revenueIdr > 0 && $totalUsd > 0) ? round($revenueIdr / $totalUsd, 2) : null;

        return DB::transaction(function () use ($cycleName, $metadata, $workerRateIdr, $revenueIdr, $totalUsd, $usdToIdr, $userId, $parsedRows, $partnersByEmail, $atlasWorkersByEmail) {
            $resolvedName = trim((string) $cycleName);
            if ($resolvedName === '') {
                $resolvedName = $metadata['suggested_name'] ?: ('Payment Cycle ' . now('Asia/Makassar')->format('d M Y H:i'));
            }

            $cycle = PaymentCycle::create([
                'name' => $resolvedName,
                'source_label' => 'kamerakita-ai',
                'period_start' => $metadata['period_start'],
                'period_end' => $metadata['period_end'],
                'frozen_on' => $metadata['frozen_on'],
                'client_billable_hours' => $metadata['client_billable_hours'] ?: collect($parsedRows)->sum('billable_hours'),
                'client_rate_usd' => $metadata['client_rate_usd'],
                'client_total_usd' => $totalUsd ?: round(collect($parsedRows)->sum('billable_hours') * $metadata['client_rate_usd'], 2),
                'usd_to_idr_rate' => $usdToIdr,
                'revenue_idr' => $revenueIdr,
                'worker_rate_idr' => $workerRateIdr,
                'total_pay_idr' => collect($parsedRows)->sum('amount_to_pay_idr'),
                'created_by' => $userId,
            ]);

            $now = now();
            $insertRows = [];
            foreach ($parsedRows as $email => $item) {
                $atlasWorker = $atlasWorkersByEmail->get($email);
                $partner = $partnersByEmail->get($email) ?? $atlasWorker?->user?->partner;

                $insertRows[] = array_merge($item, [
                    'payment_cycle_id' => $cycle->id,
                    'partner_id' => $partner?->id,
                    'atlas_worker_id' => $atlasWorker?->id,
                    'is_paid' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach (array_chunk($insertRows, 250) as $chunk) {
                PaymentCycleEntry::insert($chunk);
            }

            return $cycle;
        });
    }

    private function detectDelimiter(array $lines): string
    {
        $sample = implode("\n", array_slice($lines, 0, 10));
        $candidates = [',' => substr_count($sample, ','), ';' => substr_count($sample, ';'), "\t" => substr_count($sample, "\t")];
        arsort($candidates);

        return (string) array_key_first($candidates);
    }

    private function extractHeaderMetadata(array $rows): array
    {
        $meta = [
            'suggested_name' => null,
            'period_start' => null,
            'period_end' => null,
            'frozen_on' => null,
            'client_billable_hours' => 0.0,
            'client_rate_usd' => 4.50,
            'client_total_usd' => 0.0,
        ];

        foreach (array_slice($rows, 0, 6) as $row) {
            $text = trim(implode(' ', array_filter($row, fn ($c) => trim((string) $c) !== '')));
            if ($text === '') {
                continue;
            }

            if (preg_match('/Payment\s+cycle\s+(\d+)\s*=\s*(\d{4}-\d{2}-\d{2})\s+to\s+(\d{4}-\d{2}-\d{2})/i', $text, $m)) {
                $meta['suggested_name'] = "Payment Cycle {$m[1]} ({$m[2]} s/d {$m[3]})";
                $meta['period_start'] = $m[2];
                $meta['period_end'] = $m[3];
            }

            if (preg_match('/frozen\s+on\s+(\d{4}-\d{2}-\d{2})/i', $text, $m)) {
                $meta['frozen_on'] = $m[1];
            }

            if (preg_match('/([\d.,]+)\s+billable\s+hours\s*[x×]\s*US\$\s*([\d.,]+)\s*=\s*US\$\s*([\d.,]+)/i', $text, $m)) {
                $meta['client_billable_hours'] = $this->parseDecimal($m[1]);
                $meta['client_rate_usd'] = $this->parseDecimal($m[2]);
                $meta['client_total_usd'] = $this->parseDecimal($m[3]);
            }
        }

        return $meta;
    }

    private function locateWorkerHeader(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $normalized = array_map(fn ($cell) => strtolower(trim((string) $cell)), $row);
            $emailCol = null;
            $billableCol = null;

            foreach ($normalized as $colIdx => $label) {
                if (str_starts_with($label, 'worker email')) {
                    $emailCol = $colIdx;
                } elseif (str_starts_with($label, 'billable hours')) {
                    $billableCol = $colIdx;
                }
            }

            if ($emailCol !== null && $billableCol !== null) {
                $columns = [
                    'worker_email' => $emailCol,
                    'billable' => $billableCol,
                    'rejected' => $this->findColumn($normalized, ['rejected hours']),
                    'not_checked' => $this->findColumn($normalized, ['not checked yet']),
                    'total' => $this->findColumn($normalized, ['total hours']),
                    'approved_share' => $this->findColumn($normalized, ['approved share']),
                    'recordings' => $this->findColumn($normalized, ['recordings', 'recordin']),
                    'reason' => $this->findColumn($normalized, ['main rejection reason']),
                    'on_list' => $this->findColumn($normalized, ['on the list']),
                    'removed' => $this->findColumn($normalized, ['removed from']),
                    'amount_to_pay' => $this->findColumn($normalized, ['yang perlu dibayarkan']),
                ];

                return [$index, $columns];
            }
        }

        return [null, []];
    }

    private function findColumn(array $normalizedHeaders, array $prefixes): ?int
    {
        foreach ($normalizedHeaders as $idx => $header) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($header, $prefix)) {
                    return $idx;
                }
            }
        }

        return null;
    }

    private function extractLabeledSummaryIdr(array $row, string $label): ?int
    {
        foreach ($row as $idx => $cell) {
            if (strtolower(trim((string) $cell)) === $label) {
                for ($j = $idx + 1; $j < count($row); $j++) {
                    $candidate = trim((string) ($row[$j] ?? ''));
                    if ($candidate !== '') {
                        return $this->parseIdr($candidate);
                    }
                }
            }
        }

        return null;
    }

    private function isSummaryOrStopRow(array $row, int $emailCol): bool
    {
        $first = strtolower(trim((string) ($row[$emailCol] ?? '')));
        if (str_starts_with($first, 'worker email')) {
            return true;
        }

        foreach ($row as $cell) {
            $clean = strtolower(trim((string) $cell));
            if (in_array($clean, ['total bayar', 'omzet', 'bagi 4', 'total'], true)) {
                return true;
            }
        }

        return false;
    }

    private function parseDecimal(mixed $value): float
    {
        $str = trim((string) $value);
        if ($str === '') {
            return 0.0;
        }

        $str = preg_replace('/[^0-9,.\-]/', '', $str) ?? '0';
        if ($str === '' || $str === '-') {
            return 0.0;
        }

        $hasComma = str_contains($str, ',');
        $hasDot = str_contains($str, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($str, ',');
            $lastDot = strrpos($str, '.');
            if ($lastComma > $lastDot) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif ($hasComma) {
            if (preg_match('/^-?\d+,\d{1,6}$/', $str)) {
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        }

        return (float) $str;
    }

    private function parseShare(mixed $value): float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        $isPercent = str_contains($raw, '%');
        $num = $this->parseDecimal($raw);
        if ($isPercent || $num > 1.0001) {
            return $num / 100;
        }

        return $num;
    }

    private function parseIdr(mixed $value): int
    {
        $str = trim((string) $value);
        if ($str === '') {
            return 0;
        }

        $str = preg_replace('/[^0-9,.\-]/', '', $str) ?? '0';
        if ($str === '' || $str === '-') {
            return 0;
        }

        if (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $str)) {
            $str = str_replace('.', '', $str);
        } elseif (str_contains($str, ',') && str_contains($str, '.')) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (str_contains($str, ',') && preg_match('/^-?\d{1,3}(,\d{3})+$/', $str)) {
            $str = str_replace(',', '', $str);
        } elseif (str_contains($str, ',')) {
            $str = str_replace(',', '.', $str);
        }

        return (int) round((float) $str);
    }
}
