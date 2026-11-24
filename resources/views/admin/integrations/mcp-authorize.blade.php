<x-guest-layout>
    <div class="mx-auto max-w-[500px] py-2">
        <section class="overflow-hidden rounded-[16px] border border-slate-200 bg-white shadow-sm" aria-labelledby="mcp-consent-heading">
            {{-- Top Banner --}}
            <div class="border-b border-slate-200 bg-gradient-to-b from-indigo-50/70 to-white px-6 py-6 text-center">
                <div class="mx-auto mb-4 flex items-center justify-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-[14px] border border-indigo-200 bg-white text-indigo-600 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </span>
                    <span class="flex h-12 w-12 items-center justify-center rounded-[14px] bg-indigo-600 text-white shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                </div>

                <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-white px-3 py-1 text-[11px] font-semibold text-indigo-700">
                    <span>OAuth 2.1 &middot; Kamerakita MCP</span>
                </span>

                <h1 id="mcp-consent-heading" class="mt-3 text-[20px] font-bold tracking-tight text-slate-900">
                    Hubungkan {{ $client->name }} ke Kamerakita
                </h1>
                <p class="mt-1 text-[13px] text-slate-600">
                    <strong>{{ $client->name }}</strong> meminta izin mengakses data Kamerakita atas nama
                    <span class="font-semibold text-slate-900">{{ $user->name }}</span>.
                </p>
            </div>

            {{-- Body --}}
            <div class="space-y-4 p-6">
                <div class="space-y-2.5 rounded-[12px] border border-indigo-200/80 bg-indigo-50/50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Izin yang diberikan</p>
                    <ul class="space-y-2 text-[13px] text-slate-800">
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Membaca ringkasan rekaman Atlas, payroll, QC video, keuangan, dan invoice sesuai peran <strong>{{ $user->role }}</strong>.</span>
                        </li>
                        @if (in_array($user->role, ['superadmin', 'admin'], true))
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Menjalankan aksi operasional admin (dengan audit log &amp; dukungan simulasi).</span>
                            </li>
                        @else
                            <li class="flex items-start gap-2.5 text-slate-600">
                                <svg class="mt-0.5 w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span><strong>Hanya baca (Read-only):</strong> aplikasi tidak dapat mengubah atau menghapus data.</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="flex items-start gap-2.5 rounded-[10px] border border-slate-200 bg-slate-50 px-3.5 py-3 text-[12px] text-slate-600">
                    <svg class="mt-0.5 w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Masuk sebagai <strong class="text-slate-900">{{ $user->email }}</strong>. Anda dapat mencabut koneksi ini kapan saja dari halaman <strong>Integrasi MCP</strong>.</span>
                </div>

                <div class="flex flex-col-reverse gap-2.5 pt-2 sm:flex-row sm:items-center sm:justify-end">
                    <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="inline-flex h-[42px] w-full cursor-pointer items-center justify-center rounded-[10px] border border-slate-200 bg-white px-5 text-[13px] font-semibold text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 sm:w-auto">
                            Tolak
                        </button>
                    </form>
                    <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="inline-flex h-[42px] w-full cursor-pointer items-center justify-center rounded-[10px] bg-indigo-600 px-5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-indigo-700 sm:w-auto">
                            Izinkan akses
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
