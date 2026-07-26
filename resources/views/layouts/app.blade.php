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
        @if(auth()->check() && in_array(auth()->user()->role, ['superadmin', 'admin', 'finance']))
            <!-- Floating Hour-to-Minute Calculator Widget -->
            <div x-data="{ 
                open: false, 
                hoursInput: '', 
                get totalMinutes() {
                    let h = parseFloat(this.hoursInput);
                    return isNaN(h) ? 0 : Math.round(h * 60);
                },
                get hourPart() {
                    let h = parseFloat(this.hoursInput);
                    return isNaN(h) ? 0 : Math.floor(h);
                },
                get minutePart() {
                    let h = parseFloat(this.hoursInput);
                    if (isNaN(h)) return 0;
                    let decimals = h - Math.floor(h);
                    return Math.round(decimals * 60);
                }
            }" class="fixed right-4 top-1/2 -translate-y-1/2 z-50 flex items-center justify-end" @click.away="open = false">
                <!-- Floating Action Button (FAB) -->
                <button type="button" @click="open = !open" 
                        class="w-12 h-12 bg-slate-950 hover:bg-slate-900 text-white rounded-full flex items-center justify-center shadow-lg transition duration-200 focus:outline-none ring-2 ring-white/10 hover:scale-105"
                        title="Kalkulator Menit Kerja">
                    <svg class="w-6 h-6 transform transition-transform duration-300" :class="open ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Calculator Icon when closed, Close Icon when open -->
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" x-cloak/>
                    </svg>
                </button>

                <!-- Floating Calculator Panel -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-4 scale-95"
                     class="absolute right-14 w-80 bg-slate-900 text-white rounded-3xl shadow-2xl p-5 border border-slate-800 space-y-4"
                     x-cloak>
                    <div>
                        <span class="text-[9px] uppercase font-bold text-indigo-400 tracking-wider font-mono">Helper Tool</span>
                        <h4 class="text-sm font-black tracking-tight">Kalkulator Jam ➜ Menit</h4>
                    </div>

                    <!-- Input Box -->
                    <div class="space-y-1">
                        <label for="calc-hours" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide">Jam Desimal</label>
                        <div class="relative">
                            <input type="number" id="calc-hours" step="any" x-model="hoursInput" placeholder="Contoh: 3.63" 
                                   class="block w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white font-mono text-lg font-bold">
                            <span class="absolute right-3 top-3 text-xs text-slate-500 font-bold font-mono">jam</span>
                        </div>
                    </div>

                    <!-- Calculations Display -->
                    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850 space-y-3">
                        <div>
                            <span class="block text-[9px] text-slate-500 uppercase font-mono">Total Hasil Konversi</span>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-2xl font-black text-emerald-400 font-mono" x-text="totalMinutes"></span>
                                <span class="text-xs text-slate-450 font-bold">menit</span>
                            </div>
                        </div>
                        <div class="border-t border-slate-850 pt-2.5 flex items-center justify-between text-xs">
                            <span class="text-slate-400 font-medium">Format Waktu:</span>
                            <span class="font-bold text-slate-200 font-mono" x-text="hourPart + ' jam ' + minutePart + ' menit'"></span>
                        </div>
                    </div>

                    <!-- Informational Helper -->
                    <div class="text-[9px] text-slate-500 leading-relaxed bg-slate-950/40 p-2.5 rounded-xl border border-slate-850/50">
                        Rumus: <code class="font-mono text-slate-400 font-bold">Jam Desimal × 60 = Total Menit</code>.<br>
                        Gunakan tanda titik (.) untuk nilai desimal/koma.
                    </div>
                </div>
            </div>
        @endif
    </body>
</html>
