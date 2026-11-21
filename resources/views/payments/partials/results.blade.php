@php
    $fmtHours = fn ($v) => number_format((float) $v, 2, ',', '.');
    $fmtIdr = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $isPendingTab = ($filters['tab'] ?? 'all') === 'pending';

    $sortUrl = function (string $column) use ($filters, $activeCycle) {
        $isCurrent = $filters['sort'] === $column;
        $nextDir = ($isCurrent && $filters['dir'] === 'desc') ? 'asc' : 'desc';
        return route('payments.manage', array_merge(request()->except('partial'), [
            'cycle_id' => $activeCycle?->id,
            'sort' => $column,
            'dir' => $nextDir,
            'page' => 1,
        ]));
    };
@endphp

@if($isPendingTab)
    <!-- Ringkasan Khusus Tab Pending (Not Checked Yet) -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3.5 flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm text-amber-950">
            <span class="inline-flex items-center gap-1.5 font-extrabold text-amber-800">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Daftar Pending Cycle Ini:
            </span>
            <span><strong>{{ number_format($filteredSummary['count'], 0, ',', '.') }} akun</strong> memiliki jam pending</span>
            <span>&middot;</span>
            <span>Total Pending: <strong>{{ $fmtHours($filteredSummary['not_checked_hours']) }} jam</strong></span>
        </div>
        <div class="text-xs sm:text-sm font-extrabold text-amber-800 tabular-nums">
            Estimasi Nilai Pending: {{ $fmtIdr($filteredSummary['estimated_pending_idr']) }}
        </div>
    </div>
@elseif($hasActiveFilter)
    <!-- Subtotal Filter -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm mb-4">
        <div class="flex flex-wrap items-center gap-3 text-indigo-950">
            <span class="font-bold">Hasil Filter ({{ number_format($filteredSummary['count'], 0, ',', '.') }} akun):</span>
            <span>Billable: <strong>{{ $fmtHours($filteredSummary['billable_hours']) }} jam</strong></span>
            <span>&middot;</span>
            <span>Rejected: <strong>{{ $fmtHours($filteredSummary['rejected_hours']) }} jam</strong></span>
            <span>&middot;</span>
            <span>Recordings: <strong>{{ number_format($filteredSummary['recordings_count'], 0, ',', '.') }}</strong></span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($filters['parent_id'] !== '')
                <button
                    type="button"
                    @click="openTeamRateModal('{{ $filters['parent_id'] }}')"
                    class="px-3 py-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-colors"
                >
                    Ubah Rate Tim Ini
                </button>
            @endif
            <div class="font-extrabold text-indigo-700 tabular-nums">
                Subtotal Yang Perlu dibayarkan: {{ $fmtIdr($filteredSummary['amount_to_pay_idr']) }}
            </div>
        </div>
    </div>
@endif

<!-- Tabel Utama -->
<div class="bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto max-h-[75vh]">
        <table class="min-w-full border-collapse text-xs">
            <thead class="bg-[#2b2d30] text-white sticky top-0 z-10 select-none">
                <tr class="divide-x divide-slate-600">
                    <th class="px-2 py-2.5 text-center w-9 font-semibold">#</th>
                    @if(!$isPendingTab)
                        <th class="px-2 py-2.5 text-center w-9">
                            <input
                                type="checkbox"
                                @change="toggleAll($event.target.checked)"
                                :checked="allPageIds.length > 0 && selectedIds.length === allPageIds.length"
                                class="rounded border-slate-500 bg-slate-700 text-indigo-500 focus:ring-0"
                                title="Pilih semua baris di halaman ini"
                            >
                        </th>
                    @endif
                    <th class="px-3 py-2.5 text-left font-bold">
                        <a href="{{ $sortUrl('worker_email') }}" @click.prevent="toggleSort('worker_email')" class="inline-flex items-center gap-1 hover:text-indigo-300">
                            Worker email
                            @if($filters['sort'] === 'worker_email')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                        </a>
                    </th>
                    <th class="px-3 py-2.5 text-left font-bold whitespace-nowrap">Rekening</th>
                    @if($isPendingTab)
                        <th class="px-3 py-2.5 text-right font-bold bg-amber-900/40 text-amber-200 whitespace-nowrap">
                            <a href="{{ $sortUrl('not_checked_hours') }}" @click.prevent="toggleSort('not_checked_hours')" class="inline-flex items-center justify-end gap-1 hover:text-white">
                                Pending (Not checked yet)
                                @if($filters['sort'] === 'not_checked_hours' || $filters['sort'] === 'default')<span>{{ ($filters['sort'] === 'not_checked_hours' && $filters['dir'] === 'asc') ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-3 py-2.5 text-right font-bold whitespace-nowrap">
                            <a href="{{ $sortUrl('estimated_pending_idr') }}" @click.prevent="toggleSort('estimated_pending_idr')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Estimasi Pending (IDR)
                                @if($filters['sort'] === 'estimated_pending_idr')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('billable_hours') }}" @click.prevent="toggleSort('billable_hours')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Billable hours
                                @if($filters['sort'] === 'billable_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('rejected_hours') }}" @click.prevent="toggleSort('rejected_hours')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Rejected hours
                                @if($filters['sort'] === 'rejected_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('total_hours') }}" @click.prevent="toggleSort('total_hours')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Total hours
                                @if($filters['sort'] === 'total_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('recordings_count') }}" @click.prevent="toggleSort('recordings_count')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Recordings
                                @if($filters['sort'] === 'recordings_count')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                    @else
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('billable_hours') }}" @click.prevent="toggleSort('billable_hours')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Billable hours (to be paid)
                                @if($filters['sort'] === 'billable_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('rejected_hours') }}" @click.prevent="toggleSort('rejected_hours')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Rejected hours
                                @if($filters['sort'] === 'rejected_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('not_checked_hours') }}" @click.prevent="toggleSort('not_checked_hours')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Not checked yet (hours)
                                @if($filters['sort'] === 'not_checked_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('total_hours') }}" @click.prevent="toggleSort('total_hours')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Total hours
                                @if($filters['sort'] === 'total_hours')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-right font-bold">
                            <a href="{{ $sortUrl('recordings_count') }}" @click.prevent="toggleSort('recordings_count')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Recordings
                                @if($filters['sort'] === 'recordings_count')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-3 py-2.5 text-right font-bold whitespace-nowrap">
                            <a href="{{ $sortUrl('amount_to_pay_idr') }}" @click.prevent="toggleSort('amount_to_pay_idr')" class="inline-flex items-center justify-end gap-1 hover:text-indigo-300">
                                Yang Perlu dibayarkan
                                @if($filters['sort'] === 'amount_to_pay_idr')<span>{{ $filters['dir'] === 'asc' ? '▲' : '▼' }}</span>@endif
                            </a>
                        </th>
                        <th class="px-2.5 py-2.5 text-center font-bold whitespace-nowrap">Status</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($displayRows as $idx => $row)
                    @php
                        $rowNum = $paginatedRows ? ($paginatedRows->firstItem() + $idx) : ($idx + 1);
                        $roleBadgeClass = match($row['account_category']) {
                            'direct_worker' => 'bg-sky-50 text-sky-700 border-sky-200',
                            'under_mitra' => 'bg-violet-50 text-violet-700 border-violet-200',
                            'under_main_account' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'mitra' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'main_account' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'rekruter' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <tr class="divide-x divide-slate-200 hover:bg-blue-50/50 transition-colors {{ $row['is_paid'] ? 'bg-emerald-50/30' : ($row['was_auto_created'] ? 'bg-amber-50/40' : ($idx % 2 === 1 ? 'bg-slate-50/60' : '')) }}">
                        <td class="px-2 py-2 text-center text-slate-400 font-mono text-[11px] tabular-nums">
                            {{ $rowNum }}
                        </td>
                        @if(!$isPendingTab)
                            <td class="px-2 py-2 text-center">
                                <input
                                    type="checkbox"
                                    value="{{ $row['entry_id'] }}"
                                    x-model.number="selectedIds"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                >
                            </td>
                        @endif
                        <td class="px-3 py-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="font-medium text-slate-900 select-all">{{ $row['worker_email'] }}</span>
                                @if($row['was_auto_created'])
                                    <span
                                        title="Akun belum ada di database saat upload Excel, otomatis dibuatkan sistem sebagai Worker baru."
                                        class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold"
                                    >
                                        Akun Baru (Auto DB)
                                    </span>
                                @endif
                            </div>
                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px]">
                                <span class="inline-flex items-center px-1.5 rounded border font-semibold {{ $roleBadgeClass }}">
                                    {{ $row['account_category_label'] }}
                                </span>
                                @if($row['partner_name'])
                                    <span class="text-slate-600 font-medium">
                                        {{ $row['partner_name'] }}
                                        @if($row['mitra_id'])({{ $row['mitra_id'] }})@endif
                                    </span>
                                @endif
                                @if($row['parent_name'])
                                    <span class="text-slate-400">
                                        &rarr; Induk: <strong class="text-slate-600">{{ $row['parent_name'] }}</strong>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            @if($row['bank_name'] || $row['bank_account_number'])
                                <div class="font-mono text-[11px] font-semibold text-slate-800 whitespace-nowrap select-all">
                                    {{ $row['bank_name'] ?? '' }} {{ $row['bank_account_number'] ?? '' }}
                                </div>
                                <div class="text-[10px] text-slate-500 flex flex-wrap items-center gap-1 mt-0.5">
                                    @if($row['parent_name'] && str_starts_with((string) $row['bank_summary'], 'Rek. Induk:'))
                                        <span class="px-1 rounded bg-violet-50 text-violet-700 font-semibold border border-violet-200">Rek. Induk</span>
                                    @endif
                                    @if($row['bank_account_owner'])
                                        <span class="truncate max-w-[160px]" title="{{ $row['bank_account_owner'] }}">a.n. {{ $row['bank_account_owner'] }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400 italic">Belum ada rekening</span>
                            @endif
                        </td>

                        @if($isPendingTab)
                            <td class="px-3 py-2 text-right bg-amber-50/60 tabular-nums whitespace-nowrap">
                                <span class="font-extrabold text-amber-800">{{ $fmtHours($row['not_checked_hours']) }} jam</span>
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $fmtIdr($row['estimated_pending_idr']) }}</div>
                                <div class="text-[10px] text-slate-400">{{ '@' . $fmtIdr($row['hourly_rate_idr']) }}/jam</div>
                            </td>
                            <td class="px-2.5 py-2 text-right font-semibold text-slate-800 tabular-nums whitespace-nowrap">
                                {{ $fmtHours($row['billable_hours']) }}
                            </td>
                            <td class="px-2.5 py-2 text-right text-slate-700 tabular-nums whitespace-nowrap">
                                {{ $fmtHours($row['rejected_hours']) }}
                            </td>
                            <td class="px-2.5 py-2 text-right font-medium text-slate-800 tabular-nums whitespace-nowrap">
                                {{ $fmtHours($row['total_hours']) }}
                            </td>
                            <td class="px-2.5 py-2 text-right text-slate-800 tabular-nums whitespace-nowrap">
                                {{ number_format($row['recordings_count'], 0, ',', '.') }}
                            </td>
                        @else
                            <td class="px-2.5 py-2 text-right font-semibold text-slate-900 tabular-nums whitespace-nowrap">
                                {{ $fmtHours($row['billable_hours']) }}
                            </td>
                            <td class="px-2.5 py-2 text-right text-slate-700 tabular-nums whitespace-nowrap">
                                {{ $fmtHours($row['rejected_hours']) }}
                            </td>
                            <td class="px-2.5 py-2 text-right text-slate-600 tabular-nums whitespace-nowrap">
                                {{ $fmtHours($row['not_checked_hours']) }}
                            </td>
                            <td class="px-2.5 py-2 text-right font-medium text-slate-800 tabular-nums whitespace-nowrap">
                                {{ $fmtHours($row['total_hours']) }}
                            </td>
                            <td class="px-2.5 py-2 text-right text-slate-800 tabular-nums whitespace-nowrap">
                                {{ number_format($row['recordings_count'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $fmtIdr($row['amount_to_pay_idr']) }}</div>
                                @php $isCustomRate = $row['hourly_rate_idr'] !== (int) $activeCycle->worker_rate_idr; @endphp
                                <button
                                    type="button"
                                    @click="openSingleRateModal({{ $row['entry_id'] }}, @js($row['worker_email']), {{ $row['hourly_rate_idr'] }})"
                                    title="Klik untuk ubah tarif per jam akun ini"
                                    class="mt-0.5 inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold transition-colors {{ $isCustomRate ? 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200' : 'text-slate-400 hover:text-indigo-700 hover:bg-slate-100' }}"
                                >
                                    {{ '@' . $fmtIdr($row['hourly_rate_idr']) }}/jam
                                </button>
                            </td>
                            <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                @if($row['is_paid'])
                                    <button
                                        type="button"
                                        @click="submitSingleStatus({{ $row['entry_id'] }}, 'unpaid')"
                                        title="Klik untuk kembalikan ke Unpaid"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition-colors"
                                    >
                                        Paid
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        @click="submitSingleStatus({{ $row['entry_id'] }}, 'paid')"
                                        title="Klik untuk tandai Sudah Dibayar"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors"
                                    >
                                        Unpaid
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isPendingTab ? 9 : 11 }}" class="px-6 py-10 text-center text-slate-500">
                            {{ $isPendingTab ? 'Tidak ada jam pending (Not checked yet) pada cycle/filter ini.' : 'Tidak ada baris akun yang cocok dengan filter saat ini.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($displayRows->isNotEmpty())
                <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-slate-900">
                    <tr class="divide-x divide-slate-200">
                        @if($isPendingTab)
                            <td colspan="3" class="px-3 py-2.5 text-right uppercase text-[11px] tracking-wider text-slate-600">
                                Total Pending ({{ number_format($filteredSummary['count'], 0, ',', '.') }} akun)
                            </td>
                            <td class="px-3 py-2.5 text-right text-amber-800 tabular-nums whitespace-nowrap">{{ $fmtHours($filteredSummary['not_checked_hours']) }} jam</td>
                            <td class="px-3 py-2.5 text-right text-indigo-700 tabular-nums whitespace-nowrap">{{ $fmtIdr($filteredSummary['estimated_pending_idr']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['billable_hours']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['rejected_hours']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['total_hours']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ number_format($filteredSummary['recordings_count'], 0, ',', '.') }}</td>
                        @else
                            <td colspan="4" class="px-3 py-2.5 text-right uppercase text-[11px] tracking-wider text-slate-600">
                                Total ({{ number_format($filteredSummary['count'], 0, ',', '.') }} akun)
                            </td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['billable_hours']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['rejected_hours']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['not_checked_hours']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ $fmtHours($filteredSummary['total_hours']) }}</td>
                            <td class="px-2.5 py-2.5 text-right tabular-nums">{{ number_format($filteredSummary['recordings_count'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right text-indigo-700 tabular-nums whitespace-nowrap">{{ $fmtIdr($filteredSummary['amount_to_pay_idr']) }}</td>
                            <td class="px-2.5 py-2.5 text-center text-[11px] text-emerald-700 whitespace-nowrap">
                                {{ $filteredSummary['paid_count'] }}/{{ $filteredSummary['count'] }} Paid
                            </td>
                        @endif
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($paginatedRows && $paginatedRows->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50" @click="handlePaginationClick($event)">
            {{ $paginatedRows->links() }}
        </div>
    @endif
</div>
