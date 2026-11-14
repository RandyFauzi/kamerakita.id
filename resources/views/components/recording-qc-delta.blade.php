@props(['record', 'category'])
@php
    $delta = $record->qc_delta[$category] ?? 0;
    $previous = $category === 'approved' ? $record->prev_approved_minutes : $record->prev_rejected_minutes;
    $current = $category === 'approved' ? $record->approved_minutes : $record->rejected_minutes;
    $positive = $category === 'approved' ? $delta > 0 : $delta < 0;
    $changedAt = $record->qc_changed_at?->timezone('Asia/Makassar')->format('d M Y H:i').' WITA';
@endphp
@if(abs($delta) >= 0.01)
    <span class="block mt-1 text-xs font-bold {{ $positive ? 'text-emerald-700' : ($category === 'rejected' ? 'text-rose-700' : 'text-amber-700') }}"
          title="QC {{ $changedAt }}: {{ number_format($previous, 2) }} → {{ number_format($current, 2) }} menit">
        <span aria-hidden="true">{{ $delta > 0 ? '▲' : '▼' }}</span>
        <span class="sr-only">{{ $delta > 0 ? 'Naik' : 'Turun' }}</span>
        {{ $delta > 0 ? '+' : '−' }}{{ number_format(abs($delta), 2) }} m
    </span>
@endif
