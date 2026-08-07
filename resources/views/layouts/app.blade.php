<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kamerakita.ai') }}</title>
        <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gradient-to-b from-blue-100 to-white text-slate-800 overflow-x-hidden min-h-screen">
        <div class="min-h-screen flex min-w-0">
            <!-- Sidebar Navigation -->
            <x-sidebar />

            <!-- Page Body Content -->
            <div class="flex-1 min-w-0 md:pl-64 flex flex-col min-h-screen">
                <!-- Top Navbar -->
                <x-navbar :header="$header ?? null" />

                <!-- Main Content Slot -->
                @php
                    $partner = \App\Models\Partner::where('user_id', Auth::id())->first();
                    $isUser = $partner && in_array($partner->partner_role, ['worker', 'mitra'], true);
                @endphp
                <main class="flex-1 min-w-0 px-4 pt-3 sm:p-6 {{ $isUser ? 'pb-28 md:pb-6' : 'pb-6' }}">
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-floating-calculator />
        <x-push-prompt />
        <x-mobile-bottom-nav />
    </body>
</html>
