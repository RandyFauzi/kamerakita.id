<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Mailbox - {{ config('app.name', 'KameraKita AI') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|outfit:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F3F4F6] text-slate-900 h-screen overflow-hidden flex items-center justify-center selection:bg-indigo-200 selection:text-indigo-900">
    <div class="w-full h-full max-w-[1600px] bg-white flex overflow-hidden shadow-2xl sm:rounded-[32px] sm:m-4 sm:h-[calc(100vh-2rem)] border border-slate-100">
        {{ $slot }}
    </div>
</body>
</html>
