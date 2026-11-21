<?php

namespace App\Http\Controllers;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\PaymentCycle;
use App\Models\PaymentCycleEntry;
use App\Services\ActivityLogger;
use App\Services\PaymentCycleCsvImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagePaymentsController extends Controller
{
    public const DEFAULT_WORKER_RATE_IDR = 60000;

    public function index(Request $request)
    {
        $cycles = PaymentCycle::orderByDesc('period_end')->orderByDesc('id')->get();
        $mode = $request->input('source');
        $activeCycle = null;

        if ($mode !== 'live') {
            $requestedCycleId = $request->integer('cycle_id');
            $activeCycle = $requestedCycleId
                ? $cycles->firstWhere('id', $requestedCycleId)
                : $cycles->first();
        }

        $isLiveMode = $activeCycle === null;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $liveRateIdr = max(1000, (int) $request->input('worker_rate_idr', self::DEFAULT_WORKER_RATE_IDR));

        $allRows = $isLiveMode
            ? $this->buildLiveRows($startDate, $endDate, $liveRateIdr)
            : $this->buildCycleRows($activeCycle);

        $parentOptions = $this->extractParentOptions($allRows);
        $rejectionReasons = $allRows
            ->pluck('main_rejection_reason')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'account_type' => (string) $request->input('account_type', 'all'),
            'parent_id' => (string) $request->input('parent_id', ''),
            'pay_status' => (string) $request->input('pay_status', 'all'),
            'list_status' => (string) $request->input('list_status', 'all'),
            'rejection_reason' => (string) $request->input('rejection_reason', ''),
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
            'unpaid_count' => $filteredRows->where('is_paid', false)->count(),
            'unpaid_amount_idr' => (int) $filteredRows->where('is_paid', false)->sum('amount_to_pay_idr'),
        ];

        $cycleTotals = $this->buildCycleSummary($allRows, $activeCycle, $liveRateIdr);

        $perPage = $filters['per_page'];
        if ($perPage === 'all') {
            $paginatedRows = null;
            $displayRows = $filteredRows;
        } else {
            $size = in_array((int) $perPage, [50, 100, 250, 500], true) ? (int) $perPage : 100;
            $page = max(1, $request->integer('page', 1));
            $paginatedRows = new \Illuminate\Pagination\LengthAwarePaginator(
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
            || $filters['pay_status'] !== 'all'
            || $filters['list_status'] !== 'all'
            || $filters['rejection_reason'] !== '';

        return view('payments.manage', compact(
            'cycles',
            'activeCycle',
            'isLiveMode',
            'startDate',
            'endDate',
            'liveRateIdr',
            'displayRows',
            'paginatedRows',
            'filters',
            'hasActiveFilter',
            'filteredSummary',
            'cycleTotals',
            'parentOptions',
            'rejectionReasons',
        ));
    }

    public function importCsv(Request $request, PaymentCycleCsvImporter $importer)
    {
        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'cycle_name' => ['nullable', 'string', 'max:120'],
            'worker_rate_idr' => ['nullable', 'integer', 'min:1000', 'max:1000000'],
            'revenue_idr' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
        ]);

        $cycle = $importer->import(
            file: $request->file('csv_file'),
            cycleName: $validated['cycle_name'] ?? null,
            workerRateIdr: (int) ($validated['worker_rate_idr'] ?? self::DEFAULT_WORKER_RATE_IDR),
            overrideRevenueIdr: isset($validated['revenue_idr']) ? (int) $validated['revenue_idr'] : null,
            userId: $request->user()?->id,
        );

        ActivityLogger::log(
            'payment_cycle.import',
            "Mengimpor payment cycle '{$cycle->name}' ({$cycle->entries()->count()} akun, Total Bayar Rp " . number_format($cycle->total_pay_idr, 0, ',', '.') . ').'
        );

        return redirect()
            ->route('payments.manage', ['cycle_id' => $cycle->id])
            ->with('success', "Berhasil mengimpor {$cycle->name} ({$cycle->entries()->count()} akun).");
    }

    public function snapshotLive(Request $request)
    {
        $validated = $request->validate([
            'cycle_name' => ['required', 'string', 'max:120'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'worker_rate_idr' => ['required', 'integer', 'min:1000', 'max:1000000'],
            'client_rate_usd' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'revenue_idr' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
        ]);

        $rows = $this->buildLiveRows(
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            (int) $validated['worker_rate_idr']
        );

        if ($rows->isEmpty()) {
            return back()->with('error', 'Tidak ada data rekaman Atlas untuk rentang tanggal yang dipilih.');
        }

        $clientRateUsd = (float) ($validated['client_rate_usd'] ?? 4.50);
        $totalBillable = round((float) $rows->sum('billable_hours'), 2);
        $totalUsd = round($totalBillable * $clientRateUsd, 2);
        $revenueIdr = (int) ($validated['revenue_idr'] ?? 0);

        $cycle = DB::transaction(function () use ($validated, $rows, $clientRateUsd, $totalBillable, $totalUsd, $revenueIdr, $request) {
            $cycle = PaymentCycle::create([
                'name' => $validated['cycle_name'],
                'source_label' => 'atlas-db-snapshot',
                'period_start' => $validated['start_date'] ?? null,
                'period_end' => $validated['end_date'] ?? null,
                'frozen_on' => now('Asia/Makassar')->toDateString(),
                'client_billable_hours' => $totalBillable,
                'client_rate_usd' => $clientRateUsd,
                'client_total_usd' => $totalUsd,
                'usd_to_idr_rate' => ($revenueIdr > 0 && $totalUsd > 0) ? round($revenueIdr / $totalUsd, 2) : null,
                'revenue_idr' => $revenueIdr,
                'worker_rate_idr' => (int) $validated['worker_rate_idr'],
                'total_pay_idr' => (int) $rows->sum('amount_to_pay_idr'),
                'created_by' => $request->user()?->id,
            ]);

            $now = now();
            $insertRows = $rows->values()->map(fn (array $row, int $idx) => [
                'payment_cycle_id' => $cycle->id,
                'partner_id' => $row['partner_id'],
                'atlas_worker_id' => $row['atlas_worker_id'],
                'worker_email' => $row['worker_email'],
                'billable_hours' => $row['billable_hours'],
                'rejected_hours' => $row['rejected_hours'],
                'not_checked_hours' => $row['not_checked_hours'],
                'total_hours' => $row['total_hours'],
                'approved_share' => $row['approved_share'],
                'recordings_count' => $row['recordings_count'],
                'main_rejection_reason' => $row['main_rejection_reason'],
                'on_list_now' => $row['on_list_now'],
                'removed_from_program' => $row['removed_from_program'],
                'amount_to_pay_idr' => $row['amount_to_pay_idr'],
                'is_paid' => false,
                'sort_order' => $idx,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            foreach (array_chunk($insertRows, 250) as $chunk) {
                PaymentCycleEntry::insert($chunk);
            }

            return $cycle;
        });

        ActivityLogger::log('payment_cycle.snapshot', "Menyimpan snapshot payment cycle '{$cycle->name}' dari data Atlas DB ({$rows->count()} akun).");

        return redirect()
            ->route('payments.manage', ['cycle_id' => $cycle->id])
            ->with('success', "Snapshot '{$cycle->name}' berhasil disimpan ({$rows->count()} akun).");
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
        $mode = $request->input('source');
        $activeCycle = $mode === 'live'
            ? null
            : ($request->integer('cycle_id') ? $cycles->firstWhere('id', $request->integer('cycle_id')) : $cycles->first());

        $liveRateIdr = max(1000, (int) $request->input('worker_rate_idr', self::DEFAULT_WORKER_RATE_IDR));
        $allRows = $activeCycle
            ? $this->buildCycleRows($activeCycle)
            : $this->buildLiveRows($request->input('start_date'), $request->input('end_date'), $liveRateIdr);

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'account_type' => (string) $request->input('account_type', 'all'),
            'parent_id' => (string) $request->input('parent_id', ''),
            'pay_status' => (string) $request->input('pay_status', 'all'),
            'list_status' => (string) $request->input('list_status', 'all'),
            'rejection_reason' => (string) $request->input('rejection_reason', ''),
            'sort' => (string) $request->input('sort', 'default'),
            'dir' => strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc',
        ];

        $rows = $this->applyFiltersAndSorting($allRows, $filters);
        $variant = $request->input('variant', 'sheet');
        $slug = $activeCycle ? str()->slug($activeCycle->name) : 'live-atlas';
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
                    'Approved share',
                    'Recordings',
                    'Main rejection reason',
                    'On the list now',
                    'Removed from the program',
                    'Yang Perlu dibayarkan',
                ]);

                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r['worker_email'],
                        number_format($r['billable_hours'], 2, '.', ''),
                        number_format($r['rejected_hours'], 2, '.', ''),
                        number_format($r['not_checked_hours'], 2, '.', ''),
                        number_format($r['total_hours'], 2, '.', ''),
                        round($r['approved_share'] * 100) . '%',
                        $r['recordings_count'],
                        $r['main_rejection_reason'] ?? '',
                        $r['on_list_now'],
                        $r['removed_from_program'] ?? '',
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

        // Fallback match by email for any entry that didn't link at import time
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

            return $this->formatUnifiedRow(
                entryId: $entry->id,
                atlasWorkerId: $entry->atlas_worker_id,
                workerEmail: $entry->worker_email,
                billableHours: (float) $entry->billable_hours,
                rejectedHours: (float) $entry->rejected_hours,
                notCheckedHours: (float) $entry->not_checked_hours,
                totalHours: (float) $entry->total_hours,
                approvedShare: (float) $entry->approved_share,
                recordingsCount: (int) $entry->recordings_count,
                mainRejectionReason: $entry->main_rejection_reason,
                onListNow: $entry->on_list_now ?: 'Yes',
                removedFromProgram: $entry->removed_from_program,
                amountToPayIdr: (int) $entry->amount_to_pay_idr,
                isPaid: (bool) $entry->is_paid,
                paidAt: $entry->paid_at?->timezone('Asia/Makassar')->format('d M Y H:i'),
                partner: $partner,
                sortOrder: (int) $entry->sort_order,
            );
        });
    }

    private function buildLiveRows(?string $startDate, ?string $endDate, int $workerRateIdr): Collection
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';
        $utcDateExpr = $isMysql
            ? 'COALESCE(DATE(recorded_at), task_date)'
            : 'COALESCE(DATE(recorded_at), task_date)';

        $query = AtlasTask::query()->select([
            'atlas_worker_id',
            'billable_minutes',
            'approved_minutes',
            'rejected_minutes',
            'review_minutes',
            'deferred_minutes',
            'source_details',
            'notes',
        ]);

        if ($startDate) {
            $query->whereRaw("{$utcDateExpr} >= ?", [$startDate]);
        }
        if ($endDate) {
            $query->whereRaw("{$utcDateExpr} <= ?", [$endDate]);
        }

        $tasksByWorker = $query->get()->groupBy('atlas_worker_id');
        if ($tasksByWorker->isEmpty()) {
            return collect();
        }

        $workers = AtlasWorker::with(['user.partner.mitraParent', 'user.partner.recruiter'])
            ->whereIn('id', $tasksByWorker->keys()->all())
            ->get()
            ->keyBy('id');

        $emails = $workers->pluck('atlas_email')->map(fn ($e) => strtolower(trim((string) $e)))->unique()->all();
        $partnersByEmail = $emails !== []
            ? Partner::with(['mitraParent', 'recruiter'])
                ->whereIn(DB::raw('LOWER(email)'), $emails)
                ->get()
                ->keyBy(fn (Partner $p) => strtolower((string) $p->email))
            : collect();

        $rows = collect();
        foreach ($tasksByWorker as $workerId => $tasks) {
            $worker = $workers->get($workerId);
            if (! $worker) {
                continue;
            }

            $billableMinutes = $tasks->sum(fn ($t) => (float) ($t->billable_minutes > 0 ? $t->billable_minutes : $t->approved_minutes));
            $rejectedMinutes = $tasks->sum(fn ($t) => (float) $t->rejected_minutes);
            $pendingMinutes = $tasks->sum(fn ($t) => (float) $t->review_minutes + (float) ($t->deferred_minutes ?? 0));

            $billableHours = round($billableMinutes / 60, 2);
            $rejectedHours = round($rejectedMinutes / 60, 2);
            $notCheckedHours = round($pendingMinutes / 60, 2);
            $totalHours = round($billableHours + $rejectedHours + $notCheckedHours, 2);
            $approvedShare = ($billableHours + $rejectedHours) > 0
                ? ($billableHours / ($billableHours + $rejectedHours))
                : 0.0;

            $reasonCounts = [];
            foreach ($tasks as $t) {
                if ((float) $t->rejected_minutes <= 0) {
                    continue;
                }
                $rawReason = trim((string) ($t->source_details['rejection_reasons'] ?? $t->notes ?? ''));
                if ($rawReason === '') {
                    continue;
                }
                foreach (preg_split('/[;,]+/', $rawReason) ?: [] as $part) {
                    $clean = trim($part);
                    if ($clean !== '') {
                        $reasonCounts[$clean] = ($reasonCounts[$clean] ?? 0) + 1;
                    }
                }
            }
            arsort($reasonCounts);
            $mainReason = $reasonCounts !== [] ? (string) array_key_first($reasonCounts) : null;

            $email = strtolower(trim((string) $worker->atlas_email));
            $partner = $worker->user?->partner ?? $partnersByEmail->get($email);
            $rate = $workerRateIdr;

            $isRemoved = ! $worker->active || ($partner && $partner->status === Partner::STATUS_SUSPENDED);

            $rows->push($this->formatUnifiedRow(
                entryId: null,
                atlasWorkerId: $worker->id,
                workerEmail: $worker->atlas_email,
                billableHours: $billableHours,
                rejectedHours: $rejectedHours,
                notCheckedHours: $notCheckedHours,
                totalHours: $totalHours,
                approvedShare: round($approvedShare, 6),
                recordingsCount: $tasks->count(),
                mainRejectionReason: $mainReason,
                onListNow: $worker->active ? 'Yes' : 'No',
                removedFromProgram: $isRemoved ? 'Yes' : null,
                amountToPayIdr: (int) round($billableHours * $rate),
                isPaid: false,
                paidAt: null,
                partner: $partner,
                sortOrder: 0,
            ));
        }

        return $rows->sortByDesc('billable_hours')->values()->map(function (array $row, int $index) {
            $row['sort_order'] = $index;

            return $row;
        });
    }

    private function formatUnifiedRow(
        ?int $entryId,
        ?int $atlasWorkerId,
        string $workerEmail,
        float $billableHours,
        float $rejectedHours,
        float $notCheckedHours,
        float $totalHours,
        float $approvedShare,
        int $recordingsCount,
        ?string $mainRejectionReason,
        string $onListNow,
        ?string $removedFromProgram,
        int $amountToPayIdr,
        bool $isPaid,
        ?string $paidAt,
        ?Partner $partner,
        int $sortOrder,
    ): array {
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
            'entry_id' => $entryId,
            'atlas_worker_id' => $atlasWorkerId,
            'worker_email' => $workerEmail,
            'billable_hours' => $billableHours,
            'rejected_hours' => $rejectedHours,
            'not_checked_hours' => $notCheckedHours,
            'total_hours' => $totalHours,
            'approved_share' => $approvedShare,
            'recordings_count' => $recordingsCount,
            'main_rejection_reason' => $mainRejectionReason,
            'on_list_now' => $onListNow,
            'removed_from_program' => $removedFromProgram,
            'amount_to_pay_idr' => $amountToPayIdr,
            'is_paid' => $isPaid,
            'paid_at' => $paidAt,
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
            'sort_order' => $sortOrder,
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
                case 'unmatched':
                    if ($row['account_category'] !== 'unmatched') {
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

            if ($filters['list_status'] === 'on_list' && strtolower($row['on_list_now']) !== 'yes') {
                return false;
            } elseif ($filters['list_status'] === 'removed' && empty($row['removed_from_program']) && strtolower($row['on_list_now']) === 'yes') {
                return false;
            }

            if ($filters['rejection_reason'] !== '' && $row['main_rejection_reason'] !== $filters['rejection_reason']) {
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
            'approved_share',
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

    private function buildCycleSummary(Collection $allRows, ?PaymentCycle $cycle, int $liveRateIdr): array
    {
        $totalPayIdr = $cycle ? (int) $cycle->total_pay_idr : (int) $allRows->sum('amount_to_pay_idr');
        $revenueIdr = $cycle ? (int) $cycle->revenue_idr : 0;
        $omzetIdr = $revenueIdr > 0 ? ($revenueIdr - $totalPayIdr) : 0;
        $bagiEmpatIdr = $revenueIdr > 0 ? ($omzetIdr / 4) : 0;

        return [
            'worker_count' => $allRows->count(),
            'billable_hours' => round((float) $allRows->sum('billable_hours'), 2),
            'client_billable_hours' => $cycle ? (float) $cycle->client_billable_hours : round((float) $allRows->sum('billable_hours'), 2),
            'client_rate_usd' => $cycle ? (float) $cycle->client_rate_usd : 4.50,
            'client_total_usd' => $cycle ? (float) $cycle->client_total_usd : round((float) $allRows->sum('billable_hours') * 4.50, 2),
            'usd_to_idr_rate' => $cycle?->usd_to_idr_rate,
            'worker_rate_idr' => $cycle ? (int) $cycle->worker_rate_idr : $liveRateIdr,
            'total_pay_idr' => $totalPayIdr,
            'revenue_idr' => $revenueIdr,
            'omzet_idr' => $omzetIdr,
            'bagi_4_idr' => $bagiEmpatIdr,
            'paid_count' => $allRows->where('is_paid', true)->count(),
            'paid_amount_idr' => (int) $allRows->where('is_paid', true)->sum('amount_to_pay_idr'),
        ];
    }
}
