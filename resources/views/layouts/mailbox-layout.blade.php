<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Mailbox - {{ config('app.name', 'KameraKita AI') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|outfit:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- DOMPurify -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.9/purify.min.js"></script>
    
    <style>
        /* Sembunyikan elemen AlpineJS yang belum ter-load (Mencegah FOUC) */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-white text-slate-900 h-screen w-screen overflow-hidden flex selection:bg-indigo-200 selection:text-indigo-900">
    <div class="w-full h-full flex overflow-hidden">
        {{ $slot }}
    </div>
</body>
</html>
