<x-app-layout>
    <x-slot name="title">Recording Tim</x-slot>
    <x-slot name="header"><h2 class="text-xl font-bold text-gray-900">Recording Tim</h2></x-slot>
    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-indigo-600">Vendor Operasional</p>
                    <h1 class="mt-1 text-2xl font-bold text-gray-900">Semua recording, satu tampilan.</h1>
                    <p class="mt-2 text-sm text-gray-600">Data recording Atlas seluruh worker di bawah mitra Anda. Ringkasan mengikuti filter yang dipilih.</p>
                </div>
                <span class="self-start shrink-0 rounded-full border border-indigo-100 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">{{ number_format($stats->recordings) }} recording · {{ number_format($stats->workers) }} akun</span>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                @foreach(['worked' => 'Total durasi', 'approved' => 'Approved', 'pending' => 'Pending review', 'rejected' => 'Rejected', 'deferred' => 'Deferred'] as $key => $label)
                    <div class="rounded-2xl border {{ $key === 'worked' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-200 bg-white text-gray-900' }} p-4 sm:p-5 shadow-sm">
                        <p class="text-sm font-medium {{ $key === 'worked' ? 'text-indigo-100' : 'text-gray-600' }}">{{ $label }}</p>
                        <p class="mt-3 text-2xl font-bold tabular-nums">{{ number_format($stats->$key / 60, 2) }} <span class="text-sm font-medium">jam</span></p>
                        <p class="mt-1 text-xs {{ $key === 'worked' ? 'text-indigo-100' : 'text-gray-500' }}">{{ number_format($stats->$key, 2) }} menit</p>
                    </div>
                @endforeach
            </div>

            <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm" aria-label="Filter recording">
                <form method="GET" action="{{ route('vendor.recordings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div><label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Cari recording</label><input id="search" name="search" value="{{ request('search') }}" placeholder="Email, nama, tugas, atau ID" class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    <div><label for="worker_id" class="block text-sm font-semibold text-gray-700 mb-2">Akun worker</label><select id="worker_id" name="worker_id" class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Semua akun tim</option>@foreach($workers as $worker)<option value="{{ $worker->id }}" @selected(request('worker_id') == $worker->id)>{{ $worker->atlas_email }}</option>@endforeach</select></div>
                    <div><label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">Dari tanggal (UTC)</label><input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    <div><label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">Sampai tanggal (UTC)</label><input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    @if($errors->any())<div role="alert" class="sm:col-span-2 lg:col-span-4 text-sm text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                    <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-3"><button class="min-h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 px-5 py-2 text-sm font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Terapkan filter</button><a href="{{ route('vendor.recordings.index') }}" class="inline-flex min-h-11 items-center px-3 text-sm font-semibold text-gray-600 hover:text-indigo-600">Reset filter</a><p class="text-xs text-gray-500 sm:ml-auto">Waktu rekam memakai UTC, sama dengan Atlas.</p></div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden" aria-label="Daftar recording tim">
                <div class="px-4 sm:px-6 py-5 border-b border-gray-100"><h2 class="font-bold text-gray-900">Daftar recording</h2><p class="mt-1 text-sm text-gray-500">Deferred ditampilkan terpisah dari pending dan rejected. Seluruh durasi tabel dalam menit.</p></div>
                <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel recording, geser untuk melihat semua kolom">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600"><tr>@foreach(['Waktu rekam (UTC)', 'Akun worker', 'Recording / tugas', 'Total', 'Approved', 'Rejected', 'Pending', 'Deferred'] as $heading)<th scope="col" class="px-4 py-4 whitespace-nowrap font-semibold">{{ $heading }}</th>@endforeach</tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($records as $record)
                                <tr class="hover:bg-indigo-50/30">
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-600">{{ $record->recorded_at ? \Carbon\CarbonImmutable::parse($record->recorded_at, 'UTC')->format('d M Y H:i:s') : $record->task_date }}</td>
                                    <td class="px-4 py-4"><p class="font-semibold text-gray-900">{{ $record->atlasWorker->user?->name ?? $record->atlasWorker->atlas_email }}</p><p class="mt-1 text-xs text-gray-500">{{ $record->atlasWorker->atlas_email }}</p></td>
                                    <td class="px-4 py-4 min-w-[16rem]"><p class="font-semibold text-gray-900">{{ $record->task_name }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $record->atlas_task_id }}</p>@if($record->source_status)<span class="mt-2 inline-block rounded-lg bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">Atlas: {{ $record->source_status }}</span>@endif</td>
                                    @foreach(['worked_minutes', 'approved_minutes', 'rejected_minutes', 'review_minutes', 'deferred_minutes'] as $field)<td class="px-4 py-4 tabular-nums whitespace-nowrap {{ $field === 'worked_minutes' ? 'font-semibold text-gray-900' : 'text-gray-600' }}">{{ number_format($record->$field, 2) }}</td>@endforeach
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-16 text-center"><h3 class="font-semibold text-gray-900">Belum ada recording yang sesuai</h3><p class="mt-2 text-sm text-gray-500">Coba ubah filter. Recording akan tampil setelah akun Atlas worker terhubung ke tim dan data tersinkron.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($records->hasPages())<div class="border-t border-gray-100 px-4 sm:px-6 py-4">{{ $records->links() }}</div>@endif
            </section>
        </div>
    </div>
</x-app-layout>
