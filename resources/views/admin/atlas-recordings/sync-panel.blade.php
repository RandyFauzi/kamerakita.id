@php
    $active = in_array($syncRun?->status, ['queued', 'running'], true);
    $enabled = config('services.robotics1618.enabled');
    $failed = $syncRun?->status === 'failed';
    $lastSuccessful = $syncRun?->status === 'success' ? $syncRun->finished_at
        : \App\Models\RoboticsSyncRun::where('status', 'success')->latest('id')->value('finished_at');
    $updated = $lastSuccessful ? \Carbon\CarbonImmutable::parse($lastSuccessful, 'UTC')->timezone('Asia/Makassar') : null;
    $next = now('Asia/Makassar')->startOfHour();
    $next = $next->addHours(2 - ($next->hour % 2));
    $labels = ['queued' => 'Mengantre', 'running' => 'Memperbarui', 'success' => 'Selesai', 'failed' => 'Gagal'];
@endphp
<div class="relative flex flex-wrap items-center gap-2" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-gray-600">
        <span class="h-2 w-2 rounded-full {{ $failed ? 'bg-rose-500' : 'bg-emerald-500' }}" aria-hidden="true"></span>
        <div class="text-xs leading-tight">
            <div class="text-gray-500">Terakhir diperbarui</div>
            <div class="font-semibold text-gray-800">{{ $updated ? ($updated->isToday() ? 'Hari ini, ' : $updated->format('d M, ')).$updated->format('H:i').' WITA' : 'Belum ada' }}</div>
        </div>
    </div>
    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="bot-1618-menu"
        class="inline-flex min-h-[44px] items-center gap-3 rounded-xl bg-gray-900 px-4 py-3 text-sm font-bold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
        <svg class="h-4 w-4 text-emerald-400" :class="{{ $active ? 'true' : 'false' }} && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7v5h-5M4 17v-5h5m-3-4a7 7 0 0112-2l2 3M4 15l2 3a7 7 0 0012-2"/></svg>
        Bot 1618
        <span class="h-2 w-2 rounded-full {{ $failed ? 'bg-rose-400' : ($enabled ? 'bg-emerald-400' : 'bg-gray-400') }}" aria-hidden="true"></span>
        <svg class="h-3 w-3 text-gray-400" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
        <span class="sr-only">Buka pengaturan pembaruan</span>
    </button>
    <div id="bot-1618-menu" x-show="open" x-cloak x-transition.opacity
        class="absolute right-0 top-full z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-2xl border border-gray-200 bg-white p-5 shadow-xl text-sm text-gray-700">
        <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h3 class="font-bold text-gray-900">Jadwal Auto-Sync</h3>
                <p class="mt-1 text-xs text-gray-500">Pembaruan recording 1618</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $enabled ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $enabled ? 'Aktif' : 'Nonaktif' }}</span>
        </div>
        <dl class="my-4 space-y-3 text-xs">
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Interval waktu</dt><dd class="font-bold text-emerald-700">Setiap 2 jam</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Sinkron berikutnya</dt><dd class="font-semibold">{{ $enabled ? $next->format('H:i').' WITA' : '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Status terakhir</dt><dd role="status" aria-live="polite" id="robotics-sync-status" class="font-semibold {{ $failed ? 'text-rose-700' : 'text-gray-800' }}">{{ $labels[$syncRun?->status] ?? 'Belum ada' }}{{ $active && $syncRun?->total ? ' · '.$syncRun->processed.'/'.$syncRun->total : '' }}</dd></div>
            @if($syncRun?->total && !$active)
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Recording</dt><dd class="font-semibold">{{ number_format($syncRun->total, 0, ',', '.') }} rekaman</dd></div>
            @endif
        </dl>
        @if($failed)
            <p role="alert" class="mb-3 rounded-lg bg-rose-50 p-3 text-xs text-rose-700">{{ $syncRun->message }}</p>
        @endif
        @if(session('sync_message'))
            <p role="status" class="mb-3 text-xs text-blue-700">Permintaan pembaruan disimpan.</p>
        @endif
        <form method="POST" action="{{ route('admin.recordings.refresh1618') }}" x-data="{ submitted: false }" @submit="submitted = true">
            @csrf
            <button type="submit" @disabled(!$enabled || $active) :disabled="submitted || {{ (!$enabled || $active) ? 'true' : 'false' }}"
                class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                {{ $active ? 'Sedang memperbarui…' : 'Perbarui Sekarang' }}
            </button>
        </form>
        @if($syncRun?->result['source_observed_at'] ?? null)
            <p class="mt-3 text-center text-xs text-gray-500">Data sumber: {{ \Carbon\CarbonImmutable::parse($syncRun->result['source_observed_at'])->timezone('Asia/Makassar')->format('d M, H:i') }} WITA</p>
        @endif
    </div>
</div>
@if($active)
<script>
(() => {
    let attempts = 0;
    const timer = setInterval(async () => {
        if (++attempts > 90) { clearInterval(timer); return; }
        if (document.hidden) return;
        try {
            const response = await fetch(@json(route('admin.recordings.sync-status')), { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) { clearInterval(timer); return; }
            const run = await response.json();
            if (!run) return;
            if (!['queued', 'running'].includes(run.status)) { clearInterval(timer); location.reload(); return; }
            document.getElementById('robotics-sync-status').textContent = `${run.phase || 'Mengantre'} · ${run.processed}/${run.total} rekaman`;
        } catch (_) { /* Keep the last known status; retry at the normal interval. */ }
    }, 10000);
})();
</script>
@endif
