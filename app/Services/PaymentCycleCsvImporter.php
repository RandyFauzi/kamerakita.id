<?php

namespace App\Services;

use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Throwable;

class PaymentCycleCsvImporter
{
    public function import(
        UploadedFile $file,
        ?string $cycleName = null,
        int $workerRateIdr = 60000,
        ?int $overrideRevenueIdr = null,
        ?int $userId = null,
        ?string $preferredSheet = null,
    ): PaymentCycle {
        [$rows, $detectedSheetName] = $this->readSpreadsheetRows($file, $preferredSheet);

        if ($rows === []) {
            throw ValidationException::withMessages(['excel_file' => 'File Excel/CSV kosong atau tidak dapat dibaca.']);
        }

        $metadata = $this->extractHeaderMetadata($rows);
        [$headerIndex, $columns] = $this->locateWorkerHeader($rows);

        if ($headerIndex === null) {
            throw ValidationException::withMessages([
                'excel_file' => 'Header kolom "Worker email" dan "Billable hours" tidak ditemukan di sheet ' . $detectedSheetName . '.',
            ]);
        }

        $parsedRows = [];
        $summaryRevenue = null;

        for ($i = $headerIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $joined = strtolower(trim(implode(' ', array_map(fn ($v) => (string) ($v ?? ''), $row))));
            if ($joined === '') {
                continue;
            }

            // Capture bottom summary Revenue row on the kamerakita-ai sheet
            $rowRevenue = $this->extractLabeledSummaryIdr($row, 'revenue');
            if ($rowRevenue !== null) {
                $summaryRevenue = $rowRevenue;
                continue;
            }

            if ($this->isSummaryOrStopRow($row, $columns['worker_email'])) {
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
            $amountToPay = ($rawPay !== null && trim((string) $rawPay) !== '' && ! str_starts_with(trim((string) $rawPay), '#'))
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
                'main_rejection_reason' => ($reason === '' || $reason === '0' || str_starts_with($reason, '#')) ? null : $reason,
                'on_list_now' => $onList !== '' ? $onList : 'Yes',
                'removed_from_program' => ($removed === '' || $removed === '0') ? null : $removed,
                'amount_to_pay_idr' => max(0, $amountToPay),
                'sort_order' => count($parsedRows),
            ];
        }

        if ($parsedRows === []) {
            throw ValidationException::withMessages(['excel_file' => 'Tidak ada baris worker yang valid di file Excel/CSV.']);
        }

        $emails = array_keys($parsedRows);
        $partnersByEmail = Partner::whereIn(DB::raw('LOWER(email)'), $emails)
            ->get()
            ->keyBy(fn (Partner $p) => strtolower((string) $p->email));

        $usersByEmail = User::with('partner')
            ->whereIn(DB::raw('LOWER(email)'), $emails)
            ->get()
            ->keyBy(fn (User $u) => strtolower((string) $u->email));

        $atlasWorkersByEmail = AtlasWorker::with('user.partner')
            ->whereIn(DB::raw('LOWER(atlas_email)'), $emails)
            ->get()
            ->keyBy(fn (AtlasWorker $w) => strtolower((string) $w->atlas_email));

        $revenueIdr = $overrideRevenueIdr ?? $summaryRevenue ?? 0;
        $totalUsd = $metadata['client_total_usd'] ?: round($metadata['client_billable_hours'] * $metadata['client_rate_usd'], 2);
        $usdToIdr = ($revenueIdr > 0 && $totalUsd > 0) ? round($revenueIdr / $totalUsd, 2) : null;

        return DB::transaction(function () use (
            $cycleName,
            $detectedSheetName,
            $metadata,
            $workerRateIdr,
            $revenueIdr,
            $totalUsd,
            $usdToIdr,
            $userId,
            $parsedRows,
            $partnersByEmail,
            $usersByEmail,
            $atlasWorkersByEmail
        ) {
            $resolvedName = trim((string) $cycleName);
            if ($resolvedName === '') {
                $resolvedName = $metadata['suggested_name'] ?: ('Payment Cycle ' . now('Asia/Makassar')->format('d M Y H:i'));
            }

            $cycle = PaymentCycle::create([
                'name' => $resolvedName,
                'source_label' => $detectedSheetName ?: 'kamerakita-ai',
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

            $nextMitraNum = $this->extractHighestMitraNumber() + 1;
            $now = now();
            $insertRows = [];

            foreach ($parsedRows as $email => $item) {
                $atlasWorker = $atlasWorkersByEmail->get($email);
                $user = $usersByEmail->get($email) ?? $atlasWorker?->user;
                $partner = $partnersByEmail->get($email) ?? $user?->partner;
                $wasAutoCreated = false;

                if (! $partner) {
                    $wasAutoCreated = true;

                    if (! $user) {
                        $user = User::create([
                            'name' => $this->deriveDisplayName($email),
                            'email' => $email,
                            'password' => Hash::make(Str::random(24)),
                            'role' => 'worker',
                            'email_verified_at' => $now,
                        ]);
                    }

                    [$mitraId, $nextMitraNum] = $this->allocateNextMitraId($nextMitraNum);

                    $isRemoved = ! empty($item['removed_from_program']) || strtolower($item['on_list_now']) !== 'yes';

                    $partner = Partner::create([
                        'partner_role' => Partner::ROLE_WORKER,
                        'mitra_id' => $mitraId,
                        'full_name' => $user->name ?: $this->deriveDisplayName($email),
                        'email' => $email,
                        'whatsapp_number' => '',
                        'registration_date' => $now->toDateString(),
                        'status' => $isRemoved ? Partner::STATUS_INACTIVE : Partner::STATUS_ACTIVE,
                        'base_hourly_rate' => $workerRateIdr,
                        'user_id' => $user->id,
                    ]);
                }

                if (! $atlasWorker) {
                    $atlasWorker = AtlasWorker::create([
                        'user_id' => $partner->user_id ?? $user?->id,
                        'atlas_email' => $email,
                        'group' => 'ASTRO',
                        'active' => strtolower($item['on_list_now']) === 'yes',
                    ]);
                } elseif (! $atlasWorker->user_id && ($partner->user_id ?? $user?->id)) {
                    $atlasWorker->update(['user_id' => $partner->user_id ?? $user?->id]);
                }

                $insertRows[] = array_merge($item, [
                    'payment_cycle_id' => $cycle->id,
                    'partner_id' => $partner->id,
                    'atlas_worker_id' => $atlasWorker->id,
                    'was_auto_created' => $wasAutoCreated,
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

    /**
     * Read Excel (.xlsx/.xls) or CSV (.csv/.txt) and auto-select the kamerakita-ai sheet.
     *
     * @return array{0: array<int, array<int, mixed>>, 1: string}
     */
    private function readSpreadsheetRows(UploadedFile $file, ?string $preferredSheet): array
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if (in_array($ext, ['xlsx', 'xls'], true)) {
            try {
                $spreadsheet = IOFactory::load($file->getRealPath());
            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    'excel_file' => 'Gagal membaca file Excel: ' . $e->getMessage(),
                ]);
            }

            $sheet = $this->pickTargetWorksheet($spreadsheet, $preferredSheet);
            $rows = $this->extractWorksheetRowsSafely($sheet);

            return [$rows, $sheet->getTitle() ?: 'kamerakita-ai'];
        }

        $raw = file_get_contents($file->getRealPath());
        if ($raw === false || trim($raw) === '') {
            return [[], 'kamerakita-ai'];
        }

        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $delimiter = $this->detectDelimiter($lines);
        $rows = array_map(fn (string $line) => str_getcsv($line, $delimiter), $lines);

        $sheetLabel = 'kamerakita-ai';
        foreach (array_slice($rows, 0, 3) as $r) {
            $first = trim((string) ($r[0] ?? ''));
            if (stripos($first, 'kamerakita-ai') !== false) {
                $sheetLabel = 'kamerakita-ai';
                break;
            }
        }

        return [$rows, $sheetLabel];
    }

    private function pickTargetWorksheet(Spreadsheet $spreadsheet, ?string $preferredSheet)
    {
        $sheets = $spreadsheet->getAllSheets();
        $preferred = trim((string) $preferredSheet);

        if ($preferred !== '') {
            foreach ($sheets as $sheet) {
                if (strcasecmp(trim($sheet->getTitle()), $preferred) === 0) {
                    return $sheet;
                }
            }

            if (! in_array(strtolower($preferred), ['kamerakita-id', 'kamerakita-ai'], true)) {
                $available = implode(', ', array_map(fn ($s) => $s->getTitle(), $sheets));
                throw ValidationException::withMessages([
                    'excel_file' => "Sheet \"{$preferred}\" tidak ditemukan di file Excel. Sheet tersedia: {$available}.",
                ]);
            }
        }

        // Only read 1 exact target sheet: prioritize "kamerakita-id", then "kamerakita-ai"
        foreach (['kamerakita-id', 'kamerakita-ai'] as $targetName) {
            foreach ($sheets as $sheet) {
                if (strtolower(trim($sheet->getTitle())) === $targetName) {
                    return $sheet;
                }
            }
        }

        $available = implode(', ', array_map(fn ($s) => $s->getTitle(), $sheets));
        throw ValidationException::withMessages([
            'excel_file' => "Sheet \"kamerakita-id\" tidak ditemukan di file Excel (hanya membaca 1 sheet tersebut). Sheet tersedia: {$available}.",
        ]);
    }

    private function extractWorksheetRowsSafely(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $rows = [];

        for ($r = 1; $r <= $highestRow; $r++) {
            $row = [];
            for ($c = 1; $c <= $highestColIndex; $c++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $cell = $sheet->getCell($colLetter . $r);
                $val = null;

                if ($cell->isFormula()) {
                    $cached = $cell->getOldCalculatedValue();
                    if ($cached !== null && $cached !== '' && ! is_array($cached)) {
                        $val = $cached;
                    } else {
                        try {
                            $val = $cell->getCalculatedValue();
                        } catch (Throwable) {
                            $val = $cached;
                        }
                    }
                } else {
                    $val = $cell->getValue();
                }

                $row[] = $this->scalarCell($val);
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function normalizeRows(array $rows): array
    {
        return array_map(
            fn ($row) => is_array($row) ? array_map(fn ($cell) => $this->scalarCell($cell), $row) : [$this->scalarCell($row)],
            $rows
        );
    }

    private function scalarCell(mixed $value): string|int|float|null
    {
        while (is_array($value)) {
            $value = $value === [] ? null : reset($value);
        }

        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            return $value->getPlainText();
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value === null || is_string($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }

    private function extractHighestMitraNumber(): int
    {
        $highest = 0;
        foreach (Partner::query()->where('mitra_id', 'like', 'KMK-%')->pluck('mitra_id') as $mitraId) {
            if (preg_match('/^KMK-(\d+)$/D', (string) $mitraId, $matches)) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return $highest;
    }

    private function allocateNextMitraId(int $nextNum): array
    {
        do {
            $candidate = 'KMK-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
            $nextNum++;
        } while (Partner::where('mitra_id', $candidate)->exists());

        return [$candidate, $nextNum];
    }

    private function deriveDisplayName(string $email): string
    {
        $local = strstr($email, '@', true) ?: $email;
        $cleaned = trim((string) preg_replace('/[._\-]+/', ' ', $local));

        return $cleaned !== '' ? Str::title($cleaned) : $email;
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

        foreach (array_slice($rows, 0, 8) as $row) {
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
                    if ($candidate !== '' && ! str_starts_with($candidate, '#')) {
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
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $str = trim((string) $value);
        if ($str === '' || str_starts_with($str, '#')) {
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
        if (is_int($value) || is_float($value)) {
            $num = (float) $value;
            return $num > 1.0001 ? ($num / 100) : $num;
        }

        $raw = trim((string) $value);
        if ($raw === '' || str_starts_with($raw, '#')) {
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
        if (is_int($value) || is_float($value)) {
            return (int) round((float) $value);
        }

        $str = trim((string) $value);
        if ($str === '' || str_starts_with($str, '#')) {
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
