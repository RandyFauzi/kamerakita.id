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
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-[#f8f8f6] text-slate-800 overflow-x-hidden">
        <div class="min-h-screen flex min-w-0">
            <!-- Sidebar Navigation -->
            <x-sidebar />

            <!-- Page Body Content -->
            <div class="flex-1 min-w-0 md:pl-64 flex flex-col min-h-screen">
                <!-- Top Navbar -->
                <x-navbar :header="$header ?? null" />

                <!-- Main Content Slot -->
                <main class="flex-1 min-w-0 px-4 pb-6 pt-3 sm:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
