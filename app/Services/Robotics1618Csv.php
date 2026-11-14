<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Generator;
use RuntimeException;

class Robotics1618Csv
{
    private const REQUIRED = ['recording_id', 'email', 'task', 'created_utc', 'duration_s', 'status',
        'billable_h', 'rejected_h', 'pending_h', 'deferred_h', 'submitted_utc', 'reviewed_utc',
        'reconciliation', 'rejection_reasons', 'pay_period_start'];

    public static function date(string $value): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}T.+(?:Z|[+-]\d{2}:\d{2})$/D', $value)) {
            throw new RuntimeException('Timestamp sumber harus memiliki zona waktu.');
        }

        return CarbonImmutable::parse($value)->utc();
    }

    private function minutes(string $value, float $factor): string
    {
        if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0 || (float) $value * $factor > 10000000) {
            throw new RuntimeException('Durasi CSV tidak valid.');
        }

        return number_format((float) $value * $factor, 6, '.', '');
    }

    public function records(string $path, array $metadata): Generator
    {
        $export = $metadata['recordings_csv'];
        foreach (['from', 'to'] as $key) {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $export[$key] ?? '') ||
                ! CarbonImmutable::hasFormatWithModifiers($export[$key], 'Y-m-d')) {
                throw new RuntimeException('Rentang ekspor CSV tidak valid.');
            }
        }
        if ($export['to'] < $export['from']) {
            throw new RuntimeException('Rentang ekspor CSV terbalik.');
        }
        $handle = fopen($path, 'rb');
        try {
            $header = fgetcsv($handle, null, ',', '"', '');
            if ($header) {
                $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
            }
            if (! $header || array_diff(self::REQUIRED, $header) || count($header) !== count(array_unique($header))) {
                throw new RuntimeException('Kolom CSV 1618 berubah atau tidak lengkap.');
            }
            $seen = [];
            $raw = 0;
            while (($values = fgetcsv($handle, 262144, ',', '"', '')) !== false) {
                $raw++;
                if (count($values) !== count($header)) {
                    throw new RuntimeException('Baris CSV tidak lengkap pada baris '.($raw + 1).'.');
                }
                $row = array_combine($header, $values);
                if (isset($row['latest_row'])) {
                    $latest = strtolower(trim($row['latest_row']));
                    if (! in_array($latest, ['yes', 'no'], true)) {
                        throw new RuntimeException('Penanda latest_row tidak valid.');
                    }
                    if ($latest === 'no') {
                        continue;
                    }
                }
                $id = strtolower(trim($row['recording_id']));
                $email = strtolower(trim($row['email']));
                if (! preg_match('/^[a-f0-9]{24}$/D', $id) || isset($seen[$id]) ||
                    ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 ||
                    ! trim($row['task']) || mb_strlen($row['task']) > 255 || mb_strlen($row['status']) > 255) {
                    throw new RuntimeException('Identitas recording CSV tidak valid/duplikat pada baris '.($raw + 1).'.');
                }
                $date = self::date($row['created_utc']);
                if ($date->toDateString() < $export['from'] || $date->toDateString() > $export['to']) {
                    throw new RuntimeException('Recording berada di luar rentang ekspor.');
                }
                $record = ['record_id' => $id, 'participant' => $email, 'task' => $row['task'],
                    'recorded_utc' => $date->toIso8601String(), 'status' => $row['status'],
                    'duration_min' => $this->minutes($row['duration_s'], 1 / 60)];
                foreach (['billable', 'rejected', 'pending', 'deferred'] as $category) {
                    $record[$category.'_min'] = $this->minutes($row[$category.'_h'], 60);
                }
                foreach (['submitted_utc', 'reviewed_utc'] as $key) {
                    $record[$key] = $row[$key] ? self::date($row[$key])->toIso8601String() : null;
                }
                foreach (['reconciliation', 'rejection_reasons'] as $key) {
                    if (mb_strlen($row[$key]) > 20000) {
                        throw new RuntimeException('Catatan CSV terlalu panjang.');
                    }
                    $record[$key] = $row[$key] ?: null;
                }
                if ($row['pay_period_start']) {
                    CarbonImmutable::parse($row['pay_period_start']);
                }
                $record['pay_period_start'] = $row['pay_period_start'] ?: null;
                $seen[$id] = true;
                yield $record;
            }
            if ($raw !== $export['rows'] || (isset($export['recordings']) && count($seen) !== $export['recordings'])) {
                throw new RuntimeException('Jumlah baris/recording berbeda dari metadata 1618. Tidak ada data diimpor.');
            }
        } finally {
            fclose($handle);
        }
    }
}
