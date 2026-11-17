@php
    $isAuthenticated = Auth::check();
    $layout = $isAuthenticated ? 'layouts.app' : 'layouts.guest';
@endphp

@if($isAuthenticated)
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Program Khusus Worker Multi-Akun
                </div>
                <h2 class="font-black text-2xl text-gray-900 tracking-tight leading-tight">
                    Pengajuan Akun Induk (Main Account)
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola seluruh email pengerjaan Atlas Anda dalam satu dashboard terpusat tanpa ribet login-logout.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @include('main_account._content')
        </div>
    </div>
</x-app-layout>
@else
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengajuan Akun Induk (Main Account) | KameraKita.ai</title>
    <link rel="icon" href="{{ asset('images/Logo.webp') }}" type="image/webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8f8f6] text-gray-900 antialiased min-h-screen py-10 px-4 sm:px-6">
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Logo & Header -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-gray-200/80 pb-6">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/Logo.webp') }}" alt="Logo" class="h-9 w-auto object-contain">
                <span class="font-black text-xl text-slate-800 tracking-tight">KameraKita<span class="text-indigo-600">.ai</span></span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-xs font-bold text-gray-600 hover:text-indigo-600 transition">
                    Sudah punya akun? Masuk
                </a>
            </div>
        </div>

        @include('main_account._content')
    </div>
</body>
</html>
@endif
