<x-app-layout>
    <x-slot name="title">Unduh APK KameraKita</x-slot>

    <section class="max-w-2xl space-y-4" aria-labelledby="apk-title">
        <h1 id="apk-title" class="text-2xl font-bold text-slate-900">Unduh APK KameraKita</h1>

        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <dl class="grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-slate-600">Nama file</dt>
                    <dd class="font-semibold text-slate-900">{{ $fileName }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-600">Jenis build</dt>
                    <dd class="font-semibold text-slate-900">Debug</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-600">Ukuran</dt>
                    <dd class="font-semibold text-slate-900">
                        {{ $size === null ? '—' : number_format($size / 1_000_000, 2, ',', '.') . ' MB' }}
                    </dd>
                </div>
            </dl>

            <p class="text-sm text-slate-600">
                Build debug ini untuk uji coba di HP atau tablet Android dan masih mengarah ke backend lokal.
                Unggah rekaman dan pengiriman batch memerlukan build yang dikonfigurasi untuk backend yang sesuai.
            </p>

            @if ($size !== null)
                <a href="{{ route('admin.apk.download') }}" class="inline-flex min-h-11 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                    Unduh APK
                </a>
            @else
                <p class="rounded-lg bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                    APK belum tersedia di server.
                </p>
            @endif
        </div>
    </section>
</x-app-layout>
