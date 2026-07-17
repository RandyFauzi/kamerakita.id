<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kamerakita.ai') }}</title>

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
    <body class="text-gray-900 antialiased bg-[#f8f8f6]">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Modern Logo Header -->
            <div class="mb-4">
                <a href="/" class="flex items-center justify-center">
                    <img src="{{ asset('images/Logo.webp') }}" alt="Kamerakita.ai" class="h-14 w-auto object-contain">
                </a>
            </div>

            <!-- Authentic Minimalist Card container -->
            <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white border border-gray-200/60 shadow-sm overflow-hidden rounded-[32px]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
