<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Services\ActivityLogger;
use App\Services\PaymentCycleCsvImporter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagePaymentsController extends Controller
{
    public const DEFAULT_WORKER_RATE_IDR = 60000;

    public function index(Request $request)
    {
        $cycles = PaymentCycle::orderByDesc('period_end')->orderByDesc('id')->get();
        $requestedCycleId = $request->integer('cycle_id');
        $activeCycle = $requestedCycleId
            ? ($cycles->firstWhere('id', $requestedCycleId) ?? $cycles->first())
            : $cycles->first();

        $allRows = $activeCycle ? $this->buildCycleRows($activeCycle) : collect();

        $parentOptions = $this->extractParentOptions($allRows);

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'account_type' => (string) $request->input('account_type', 'all'),
            'parent_id' => (string) $request->input('parent_id', ''),
            'pay_status' => (string) $request->input('pay_status', 'all'),
            'sort' => (string) $request->input('sort', 'default'),
            'dir' => strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc',
            'per_page' => (string) $request->input('per_page', '100'),
        ];

        $filteredRows = $this->applyFiltersAndSorting($allRows, $filters);

        $filteredSummary = [
            'count' => $filteredRows->count(),
            'billable_hours' => round((float) $filteredRows->sum('billable_hours'), 2),
            'rejected_hours' => round((float) $filteredRows->sum('rejected_hours'), 2),
            'not_checked_hours' => round((float) $filteredRows->sum('not_checked_hours'), 2),
            'total_hours' => round((float) $filteredRows->sum('total_hours'), 2),
            'recordings_count' => (int) $filteredRows->sum('recordings_count'),
            'amount_to_pay_idr' => (int) $filteredRows->sum('amount_to_pay_idr'),
            'paid_count' => $filteredRows->where('is_paid', true)->count(),
            'paid_amount_idr' => (int) $filteredRows->where('is_paid', true)->sum('amount_to_pay_idr'),
            'auto_created_count' => $filteredRows->where('was_auto_created', true)->count(),
        ];

        $cycleTotals = $this->buildCycleSummary($allRows, $activeCycle);

        $perPage = $filters['per_page'];
        if ($perPage === 'all') {
            $paginatedRows = null;
            $displayRows = $filteredRows;
        } else {
            $size = in_array((int) $perPage, [50, 100, 250, 500], true) ? (int) $perPage : 100;
            $page = max(1, $request->integer('page', 1));
            $paginatedRows = new LengthAwarePaginator(
                $filteredRows->forPage($page, $size)->values(),
                $filteredRows->count(),
                $size,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $displayRows = collect($paginatedRows->items());
        }

        $hasActiveFilter = $filters['search'] !== ''
            || $filters['account_type'] !== 'all'
            || $filters['parent_id'] !== ''
            || $filters['pay_status'] !== 'all';

        return view('payments.manage', compact(
            'cycles',
            'activeCycle',
            'displayRows',
            'paginatedRows',
            'filters',
            'hasActiveFilter',
            'filteredSummary',
            'cycleTotals',
            'parentOptions',
        ));
    }

    public function importCsv(Request $request, PaymentCycleCsvImporter $importer)
    {
        $uploaded = $request->file('excel_file') ?? $request->file('csv_file');
        if ($uploaded) {
            $request->files->set('excel_file', $uploaded);
        }

        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:20480'],
            'cycle_name' => ['nullable', 'string', 'max:120'],
            'sheet_name' => ['nullable', 'string', 'max:120'],
            'worker_rate_idr' => ['nullable', 'integer', 'min:1000', 'max:1000000'],
            'revenue_idr' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
        ]);

        $cycle = $importer->import(
            file: $request->file('excel_file'),
            cycleName: $validated['cycle_name'] ?? null,
            workerRateIdr: (int) ($validated['worker_rate_idr'] ?? self::DEFAULT_WORKER_RATE_IDR),
            overrideRevenueIdr: isset($validated['revenue_idr']) ? (int) $validated['revenue_idr'] : null,
            userId: $request->user()?->id,
            preferredSheet: $validated['sheet_name'] ?? null,
        );

        $totalEntries = $cycle->entries()->count();
        $autoCreated = $cycle->entries()->where('was_auto_created', true)->count();

        ActivityLogger::log(
            'payment_cycle.import',
            "Mengimpor Excel '{$cycle->name}' ({$totalEntries} akun, {$autoCreated} akun baru otomatis dibuat DB, Total Bayar Rp " . number_format($cycle->total_pay_idr, 0, ',', '.') . ').'
        );

        $msg = "Berhasil mengimpor {$cycle->name} dari sheet \"{$cycle->source_label}\" ({$totalEntries} akun).";
        if ($autoCreated > 0) {
            $msg .= " {$autoCreated} akun yang belum ada di database otomatis dibuat dengan tanda Akun Baru (Auto DB).";
        }

        return redirect()
            ->route('payments.manage', ['cycle_id' => $cycle->id])
            ->with('success', $msg);
    }

    public function updateCycleFinancials(Request $request, PaymentCycle $cycle)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'client_billable_hours' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'client_rate_usd' => ['required', 'numeric', 'min:0', 'max:1000'],
            'revenue_idr' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'usd_to_idr_rate' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $clientHours = round((float) $validated['client_billable_hours'], 2);
        $clientRateUsd = round((float) $validated['client_rate_usd'], 2);
        $clientTotalUsd = round($clientHours * $clientRateUsd, 2);

        $revenueIdr = isset($validated['revenue_idr']) ? (int) $validated['revenue_idr'] : 0;
        $kurs = isset($validated['usd_to_idr_rate']) ? (float) $validated['usd_to_idr_rate'] : null;

        if ($revenueIdr <= 0 && $kurs && $clientTotalUsd > 0) {
            $revenueIdr = (int) round($clientTotalUsd * $kurs);
        } elseif ($revenueIdr > 0 && $clientTotalUsd > 0 && ! $kurs) {
            $kurs = round($revenueIdr / $clientTotalUsd, 2);
        }

        $cycle->update([
            'name' => $validated['name'],
            'client_billable_hours' => $clientHours,
            'client_rate_usd' => $clientRateUsd,
            'client_total_usd' => $clientTotalUsd,
            'usd_to_idr_rate' => $kurs,
            'revenue_idr' => $revenueIdr,
        ]);

        return redirect()
            ->route('payments.manage', ['cycle_id' => $cycle->id])
            ->with('success', "Ringkasan finansial {$cycle->name} diperbarui.");
    }

    public function updatePaymentStatus(Request $request)
    {
        $validated = $request->validate([
            'entry_ids' => ['required', 'array', 'min:1'],
            'entry_ids.*' => ['integer', 'exists:payment_cycle_entries,id'],
            'status' => ['required', 'in:paid,unpaid'],
        ]);

        $isPaid = $validated['status'] === 'paid';

        $updated = PaymentCycleEntry::whereIn('id', $validated['entry_ids'])->update([
            'is_paid' => $isPaid,
            'paid_at' => $isPaid ? now() : null,
            'paid_by' => $isPaid ? $request->user()?->id : null,
        ]);

        $label = $isPaid ? 'Sudah Dibayar' : 'Belum Dibayar';

        return back()->with('success', "{$updated} akun ditandai {$label}.");
    }

    public function destroyCycle(PaymentCycle $cycle)
    {
        $name = $cycle->name;
        $cycle->delete();

        ActivityLogger::log('payment_cycle.delete', "Menghapus payment cycle '{$name}'.");

        return redirect()
            ->route('payments.manage')
            ->with('success', "Payment cycle '{$name}' berhasil dihapus.");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $cycles = PaymentCycle::orderByDesc('period_end')->orderByDesc('id')->get();
        $activeCycle = $request->integer('cycle_id')
            ? ($cycles->firstWhere('id', $request->integer('cycle_id')) ?? $cycles->first())
            : $cycles->first();

        $allRows = $activeCycle ? $this->buildCycleRows($activeCycle) : collect();

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'account_type' => (string) $request->input('account_type', 'all'),
            'parent_id' => (string) $request->input('parent_id', ''),
            'pay_status' => (string) $request->input('pay_status', 'all'),
            'sort' => (string) $request->input('sort', 'default'),
            'dir' => strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc',
        ];

        $rows = $this->applyFiltersAndSorting($allRows, $filters);
        $variant = $request->input('variant', 'sheet');
        $slug = $activeCycle ? str()->slug($activeCycle->name) : 'pembayaran';
        $filename = "pembayaran_{$slug}_{$variant}_" . now('Asia/Makassar')->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows, $variant) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            if ($variant === 'bank') {
                fputcsv($out, [
                    'Worker email',
                    'Nama Partner',
                    'ID KMK',
                    'Kategori Akun',
                    'Akun Auto DB',
                    'Mitra / Main Account Induk',
                    'Metode / Bank',
                    'No. Rekening / AirTM',
                    'Pemilik Rekening',
                    'Billable hours',
                    'Yang Perlu dibayarkan (IDR)',
                    'Status Pembayaran',
                ]);

                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r['worker_email'],
                        $r['partner_name'] ?? '-',
                        $r['mitra_id'] ?? '-',
                        $r['account_category_label'],
                        $r['was_auto_created'] ? 'Akun Baru (Auto DB)' : 'Sudah Ada',
                        $r['parent_name'] ? ($r['parent_name'] . ($r['parent_mitra_id'] ? " ({$r['parent_mitra_id']})" : '')) : '-',
                        $r['bank_name'] ?? '-',
                        $r['bank_account_number'] ?? '-',
                        $r['bank_account_owner'] ?? '-',
                        number_format($r['billable_hours'], 2, '.', ''),
                        $r['amount_to_pay_idr'],
                        $r['is_paid'] ? 'Paid' : 'Unpaid',
                    ]);
                }
            } else {
                fputcsv($out, [
                    'Worker email',
                    'Billable hours (to be paid)',
                    'Rejected hours',
                    'Not checked yet (hours)',
                    'Total hours',
                    'Recordings',
                    'Yang Perlu dibayarkan',
                ]);

                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r['worker_email'],
                        number_format($r['billable_hours'], 2, '.', ''),
                        number_format($r['rejected_hours'], 2, '.', ''),
                        number_format($r['not_checked_hours'], 2, '.', ''),
                        number_format($r['total_hours'], 2, '.', ''),
                        $r['recordings_count'],
                        $r['amount_to_pay_idr'],
                    ]);
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildCycleRows(PaymentCycle $cycle): Collection
    {
        $entries = $cycle->entries()
            ->with(['partner.mitraParent', 'partner.recruiter', 'atlasWorker.user.partner.mitraParent', 'atlasWorker.user.partner.recruiter'])
            ->get();

        $unlinkedEmails = $entries
            ->filter(fn (PaymentCycleEntry $e) => ! $e->partner_id)
            ->pluck('worker_email')
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->unique()
            ->values();

        $fallbackPartners = $unlinkedEmails->isNotEmpty()
            ? Partner::with(['mitraParent', 'recruiter'])
                ->whereIn(DB::raw('LOWER(email)'), $unlinkedEmails->all())
                ->get()
                ->keyBy(fn (Partner $p) => strtolower((string) $p->email))
            : collect();

        return $entries->map(function (PaymentCycleEntry $entry) use ($fallbackPartners) {
            $email = strtolower(trim($entry->worker_email));
            $partner = $entry->partner
                ?? $entry->atlasWorker?->user?->partner
                ?? $fallbackPartners->get($email);

            return $this->formatUnifiedRow($entry, $partner);
        });
    }

    private function formatUnifiedRow(PaymentCycleEntry $entry, ?Partner $partner): array
    {
        $role = $partner ? strtolower(trim((string) $partner->partner_role)) : null;
        $parent = $partner?->mitraParent;
        $parentRole = $parent ? strtolower(trim((string) $parent->partner_role)) : null;

        if (! $partner) {
            $accountCategory = 'unmatched';
            $accountCategoryLabel = 'Belum Di-Link DB';
        } elseif ($parent) {
            $accountCategory = $parentRole === Partner::ROLE_MAIN_ACCOUNT ? 'under_main_account' : 'under_mitra';
            $accountCategoryLabel = $parentRole === Partner::ROLE_MAIN_ACCOUNT ? 'Sub Main Account' : 'Under Mitra';
        } elseif ($role === Partner::ROLE_MITRA) {
            $accountCategory = 'mitra';
            $accountCategoryLabel = 'Mitra';
        } elseif ($role === Partner::ROLE_MAIN_ACCOUNT) {
            $accountCategory = 'main_account';
            $accountCategoryLabel = 'Main Account';
        } elseif ($role === Partner::ROLE_REKRUTER) {
            $accountCategory = 'rekruter';
            $accountCategoryLabel = 'Rekruter';
        } else {
            $accountCategory = 'direct_worker';
            $accountCategoryLabel = 'Worker Langsung';
        }

        $useParentBank = $parent && $parent->bypassesWorkerBankOnboarding();
        $bankHolder = $useParentBank ? $parent : $partner;
        $bankName = null;
        $bankNumber = null;
        $bankOwner = null;
        $bankSummary = null;

        if ($bankHolder) {
            $isAirtm = $bankHolder->payment_method === 'airtm';
            $bankName = $isAirtm ? 'AirTM' : ($bankHolder->bank_name ?: null);
            $bankNumber = $isAirtm ? $bankHolder->airtm_username : ($bankHolder->bank_account_number ?: null);
            $bankOwner = $bankHolder->bank_account_owner ?: $bankHolder->full_name;
            if ($bankName || $bankNumber) {
                $prefix = $useParentBank ? 'Rek. Induk: ' : '';
                $bankSummary = trim("{$prefix}{$bankName} {$bankNumber}");
            }
        }

        return [
            'entry_id' => $entry->id,
            'atlas_worker_id' => $entry->atlas_worker_id,
            'worker_email' => $entry->worker_email,
            'billable_hours' => (float) $entry->billable_hours,
            'rejected_hours' => (float) $entry->rejected_hours,
            'not_checked_hours' => (float) $entry->not_checked_hours,
            'total_hours' => (float) $entry->total_hours,
            'approved_share' => (float) $entry->approved_share,
            'recordings_count' => (int) $entry->recordings_count,
            'main_rejection_reason' => $entry->main_rejection_reason,
            'on_list_now' => $entry->on_list_now ?: 'Yes',
            'removed_from_program' => $entry->removed_from_program,
            'amount_to_pay_idr' => (int) $entry->amount_to_pay_idr,
            'was_auto_created' => (bool) $entry->was_auto_created,
            'is_paid' => (bool) $entry->is_paid,
            'paid_at' => $entry->paid_at?->timezone('Asia/Makassar')->format('d M Y H:i'),
            'partner_id' => $partner?->id,
            'mitra_id' => $partner?->mitra_id,
            'partner_name' => $partner?->full_name,
            'partner_role' => $role,
            'recruiter_partner_id' => $partner?->recruiter_partner_id,
            'account_category' => $accountCategory,
            'account_category_label' => $accountCategoryLabel,
            'parent_id' => $parent?->id,
            'parent_name' => $parent?->full_name,
            'parent_mitra_id' => $parent?->mitra_id,
            'parent_role' => $parentRole,
            'bank_name' => $bankName,
            'bank_account_number' => $bankNumber,
            'bank_account_owner' => $bankOwner,
            'bank_summary' => $bankSummary,
            'sort_order' => (int) $entry->sort_order,
        ];
    }

    private function extractParentOptions(Collection $rows): Collection
    {
        $parents = [];

        foreach ($rows as $row) {
            if ($row['parent_id']) {
                $pid = $row['parent_id'];
                $parents[$pid] ??= [
                    'id' => $pid,
                    'name' => $row['parent_name'] ?? 'Tanpa Nama',
                    'mitra_id' => $row['parent_mitra_id'] ?? '',
                    'role' => $row['parent_role'] ?? 'mitra',
                    'count' => 0,
                ];
                $parents[$pid]['count']++;
            } elseif (in_array($row['partner_role'], [Partner::ROLE_MITRA, Partner::ROLE_MAIN_ACCOUNT], true) && $row['partner_id']) {
                $pid = $row['partner_id'];
                $parents[$pid] ??= [
                    'id' => $pid,
                    'name' => $row['partner_name'] ?? 'Tanpa Nama',
                    'mitra_id' => $row['mitra_id'] ?? '',
                    'role' => $row['partner_role'],
                    'count' => 0,
                ];
                $parents[$pid]['count']++;
            }
        }

        return collect($parents)->sortBy('name')->values();
    }

    private function applyFiltersAndSorting(Collection $rows, array $filters): Collection
    {
        $filtered = $rows->filter(function (array $row) use ($filters) {
            if ($filters['search'] !== '') {
                $needle = mb_strtolower($filters['search']);
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $row['worker_email'],
                    $row['partner_name'],
                    $row['mitra_id'],
                    $row['parent_name'],
                    $row['parent_mitra_id'],
                    $row['bank_summary'],
                    $row['main_rejection_reason'],
                ])));
                if (! str_contains($haystack, $needle)) {
                    return false;
                }
            }

            switch ($filters['account_type']) {
                case 'direct_worker':
                    if ($row['account_category'] !== 'direct_worker') {
                        return false;
                    }
                    break;
                case 'under_mitra':
                    if (! in_array($row['account_category'], ['under_mitra', 'under_main_account'], true)) {
                        return false;
                    }
                    break;
                case 'mitra':
                    if ($row['partner_role'] !== Partner::ROLE_MITRA && $row['parent_role'] !== Partner::ROLE_MITRA) {
                        return false;
                    }
                    break;
                case 'main_account':
                    if ($row['partner_role'] !== Partner::ROLE_MAIN_ACCOUNT && $row['parent_role'] !== Partner::ROLE_MAIN_ACCOUNT) {
                        return false;
                    }
                    break;
                case 'rekruter':
                    if ($row['partner_role'] !== Partner::ROLE_REKRUTER && empty($row['recruiter_partner_id'])) {
                        return false;
                    }
                    break;
                case 'auto_created':
                    if (! $row['was_auto_created']) {
                        return false;
                    }
                    break;
            }

            if ($filters['parent_id'] !== '') {
                $pid = $filters['parent_id'];
                if ($row['parent_id'] !== $pid && $row['partner_id'] !== $pid) {
                    return false;
                }
            }

            if ($filters['pay_status'] === 'paid' && ! $row['is_paid']) {
                return false;
            } elseif ($filters['pay_status'] === 'unpaid' && $row['is_paid']) {
                return false;
            }

            return true;
        });

        $sort = $filters['sort'];
        $desc = $filters['dir'] === 'desc';

        $allowedSorts = [
            'worker_email',
            'billable_hours',
            'rejected_hours',
            'not_checked_hours',
            'total_hours',
            'recordings_count',
            'amount_to_pay_idr',
        ];

        if (in_array($sort, $allowedSorts, true)) {
            $filtered = $desc
                ? $filtered->sortByDesc($sort, SORT_NATURAL | SORT_FLAG_CASE)
                : $filtered->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            $filtered = $filtered->sortBy('sort_order');
        }

        return $filtered->values();
    }

    private function buildCycleSummary(Collection $allRows, ?PaymentCycle $cycle): array
    {
        $totalPayIdr = $cycle ? (int) $cycle->total_pay_idr : 0;
        $revenueIdr = $cycle ? (int) $cycle->revenue_idr : 0;
        $omzetIdr = $revenueIdr > 0 ? ($revenueIdr - $totalPayIdr) : 0;
        $bagiEmpatIdr = $revenueIdr > 0 ? ($omzetIdr / 4) : 0;

        return [
            'worker_count' => $allRows->count(),
            'billable_hours' => round((float) $allRows->sum('billable_hours'), 2),
            'client_billable_hours' => $cycle ? (float) $cycle->client_billable_hours : 0.0,
            'client_rate_usd' => $cycle ? (float) $cycle->client_rate_usd : 4.50,
            'client_total_usd' => $cycle ? (float) $cycle->client_total_usd : 0.0,
            'usd_to_idr_rate' => $cycle?->usd_to_idr_rate,
            'worker_rate_idr' => $cycle ? (int) $cycle->worker_rate_idr : self::DEFAULT_WORKER_RATE_IDR,
            'total_pay_idr' => $totalPayIdr,
            'revenue_idr' => $revenueIdr,
            'omzet_idr' => $omzetIdr,
            'bagi_4_idr' => $bagiEmpatIdr,
            'paid_count' => $allRows->where('is_paid', true)->count(),
            'paid_amount_idr' => (int) $allRows->where('is_paid', true)->sum('amount_to_pay_idr'),
            'auto_created_count' => $allRows->where('was_auto_created', true)->count(),
        ];
    }
}
