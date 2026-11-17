<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider mb-1.5" style="background-color: #e0f2fe; color: #0369a1;">
                    Program Khusus Kontributor Multi-Akun
                </span>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                    Pengajuan Akun Induk (Main Account)
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Kelola seluruh email pengerjaan Atlas Anda dalam satu dashboard terpusat tanpa ribet login-logout.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 pb-24 md:pb-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
            @include('main_account._content')
        </div>
    </div>
</x-app-layout>
