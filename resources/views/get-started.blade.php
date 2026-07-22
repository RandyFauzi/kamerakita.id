<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get Started - KAMERAKITA AI</title>
    <meta name="description" content="Panduan singkat sebelum daftar sebagai kontributor KAMERAKITA AI.">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    boxShadow: {
                        phone: '0 34px 80px rgba(37, 99, 235, 0.18)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .slide-art {
            background:
                radial-gradient(circle at 22% 18%, rgba(255,255,255,.92) 0 12%, transparent 13%),
                radial-gradient(circle at 78% 16%, rgba(255,255,255,.72) 0 10%, transparent 11%),
                linear-gradient(145deg, var(--from), var(--to));
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#eef3ff] text-slate-950">
    @php
        $manualBookUrl = asset('images/Assest/Manual Book/ManualBook_KameraKitaAI_22072026.pdf');
        $whatsappUrl = 'https://wa.me/6287886272647?text='.rawurlencode('Halo Admin KameraKita AI, saya ingin bergabung dan membutuhkan referral/invite code untuk registrasi aplikasi Minute.');
        $slides = [
            [
                'eyebrow' => 'Step 01',
                'title' => 'Siapkan Perangkat yang Didukung',
                'body' => 'Gunakan iPhone 12 ke atas, Google Pixel 6 ke atas, atau Samsung Galaxy S21 ke atas agar rekaman layak QC.',
                'tone' => 'from:#0ea5e9;--to:#2563eb',
                'icon' => 'phone',
            ],
            [
                'eyebrow' => 'Step 02',
                'title' => 'Buat Email Khusus Kerja',
                'body' => 'Buat email di mail.tm, lalu simpan email dan password. Email ini dipakai untuk KameraKita AI dan aplikasi Minute.',
                'tone' => 'from:#6366f1;--to:#8b5cf6',
                'icon' => 'mail',
            ],
            [
                'eyebrow' => 'Step 03',
                'title' => 'Daftar Akun KameraKita AI',
                'body' => 'Akun dashboard wajib dibuat agar laporan harian dan pembayaran bisa diproses dengan benar.',
                'tone' => 'from:#14b8a6;--to:#0284c7',
                'icon' => 'dashboard',
            ],
            [
                'eyebrow' => 'Step 04',
                'title' => 'Install Aplikasi Minute',
                'body' => 'Download Minute, buka aplikasi, klik Get Started, lalu baca ToU dan policy sebelum setuju.',
                'tone' => 'from:#f97316;--to:#ef4444',
                'icon' => 'download',
            ],
            [
                'eyebrow' => 'Step 05',
                'title' => 'Minta Referral Code ke Admin',
                'body' => 'Referral atau invite code diberikan lewat japri WhatsApp. Minta dulu ke admin sebelum membuat akun Minute.',
                'tone' => 'from:#22c55e;--to:#059669',
                'icon' => 'code',
            ],
            [
                'eyebrow' => 'Step 06',
                'title' => 'Rekam Sesuai SOP dan Kirim Laporan',
                'body' => 'Pakai headstrap, video landscape, tangan terlihat, lalu upload screenshot total durasi dan kualitas aplikasi setiap hari.',
                'tone' => 'from:#1d4ed8;--to:#111827',
                'icon' => 'check',
            ],
        ];
    @endphp

    <div
        x-data="{
            active: 0,
            slides: @js($slides),
            next() {
                if (this.active < this.slides.length - 1) {
                    this.active++;
                    return;
                }

                window.location.href = '{{ route('register') }}';
            },
            prev() {
                if (this.active > 0) this.active--;
            },
            go(index) {
                this.active = index;
            },
            isLast() {
                return this.active === this.slides.length - 1;
            }
        }"
        class="relative min-h-screen"
    >
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-24 top-10 h-72 w-72 rounded-full bg-sky-300/30 blur-3xl"></div>
            <div class="absolute right-0 top-1/4 h-96 w-96 rounded-full bg-indigo-300/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-emerald-200/30 blur-3xl"></div>
        </div>

        <header class="relative z-10 mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-sky-700 shadow-sm">
                    <img src="{{ asset('images/Logo.webp') }}" alt="KAMERAKITA AI" class="h-full w-full object-contain">
                </span>
                <span class="text-sm font-black tracking-tight sm:text-base">KAMERAKITA<span class="text-indigo-600">.AI</span></span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="rounded-full bg-white/80 px-4 py-2 text-xs font-black text-slate-700 shadow-sm ring-1 ring-white/80 backdrop-blur transition hover:bg-white">Masuk</a>
                <a href="{{ $manualBookUrl }}" target="_blank" class="hidden rounded-full bg-white/70 px-4 py-2 text-xs font-bold text-slate-500 ring-1 ring-white/80 backdrop-blur transition hover:bg-white sm:inline-flex">Manual Book</a>
            </div>
        </header>

        <main class="relative z-10 mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl items-center gap-8 px-4 pb-8 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
            <section class="hidden lg:block">
                <span class="inline-flex rounded-full bg-white/80 px-4 py-2 text-xs font-black uppercase tracking-widest text-indigo-700 shadow-sm ring-1 ring-white/80">Tutorial Sebelum Daftar</span>
                <h1 class="mt-5 max-w-xl text-5xl font-black leading-tight tracking-tight text-slate-950">
                    Mulai dengan alur yang jelas, lalu daftar tanpa bingung.
                </h1>
                <p class="mt-4 max-w-lg text-base leading-8 text-slate-600">
                    Geser step seperti onboarding aplikasi. Setelah paham, minta referral code ke admin dan lanjut daftar akun KameraKita AI.
                </p>

                <div class="mt-8 grid max-w-xl grid-cols-3 gap-3">
                    <template x-for="(slide, index) in slides.slice(0, 3)" :key="slide.title">
                        <button
                            type="button"
                            @click="go(index)"
                            class="rounded-3xl bg-white/75 p-4 text-left shadow-sm ring-1 ring-white/80 transition hover:bg-white"
                            :class="active === index ? 'scale-[1.03] ring-2 ring-indigo-300' : ''"
                        >
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-500" x-text="slide.eyebrow"></span>
                            <span class="mt-2 block text-sm font-black leading-5 text-slate-950" x-text="slide.title"></span>
                        </button>
                    </template>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ $whatsappUrl }}" target="_blank" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                        Minta Referral Code
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-900 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                        Langsung Daftar
                    </a>
                </div>
            </section>

            <section class="mx-auto w-full max-w-sm lg:max-w-[430px]" aria-label="Onboarding tutorial">
                <div class="relative mx-auto aspect-[9/18.5] w-full rounded-[42px] bg-white/55 p-2 shadow-phone ring-1 ring-white/70 backdrop-blur">
                    <div class="absolute left-1/2 top-4 z-20 h-6 w-28 -translate-x-1/2 rounded-full bg-slate-950"></div>
                    <div class="relative flex h-full flex-col overflow-hidden rounded-[34px] bg-white">
                        <div class="flex items-center justify-between px-6 pb-3 pt-8 text-[11px] font-black text-slate-900">
                            <span>9:41</span>
                            <span class="tracking-widest">KAMERAKITA</span>
                        </div>

                        <div class="relative flex-1 px-6 pb-6">
                            <template x-for="(slide, index) in slides" :key="slide.title">
                                <article
                                    x-cloak
                                    x-show="active === index"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-x-8"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-x-0"
                                    x-transition:leave-end="opacity-0 -translate-x-8"
                                    class="absolute inset-x-6 top-0 flex h-full flex-col"
                                >
                                    <div class="slide-art mt-3 flex h-[44%] items-center justify-center rounded-[30px] text-white" :style="'--' + slide.tone">
                                        <div class="relative flex h-32 w-32 items-center justify-center rounded-[32px] bg-white/20 shadow-lg ring-1 ring-white/30">
                                            <template x-if="slide.icon === 'phone'">
                                                <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 2h8a2 2 0 012 2v16a2 2 0 01-2 2H8a2 2 0 01-2-2V4a2 2 0 012-2zm4 17h.01M9 5h6"/></svg>
                                            </template>
                                            <template x-if="slide.icon === 'mail'">
                                                <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7l8 6 8-6"/></svg>
                                            </template>
                                            <template x-if="slide.icon === 'dashboard'">
                                                <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4zM8 9h3m-3 4h8m-8 4h5m4-8h1"/></svg>
                                            </template>
                                            <template x-if="slide.icon === 'download'">
                                                <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v11m0 0l-4-4m4 4l4-4M5 18h14v3H5z"/></svg>
                                            </template>
                                            <template x-if="slide.icon === 'code'">
                                                <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9l-4 3 4 3m8-6l4 3-4 3M14 5l-4 14"/></svg>
                                            </template>
                                            <template x-if="slide.icon === 'check'">
                                                <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 5-6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7z"/></svg>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 flex-col justify-center text-center">
                                        <span class="text-xs font-black uppercase tracking-[0.28em] text-indigo-500" x-text="slide.eyebrow"></span>
                                        <h2 class="mt-3 text-2xl font-black leading-tight tracking-tight text-slate-950" x-text="slide.title"></h2>
                                        <p class="mx-auto mt-3 max-w-[290px] text-sm leading-6 text-slate-500" x-text="slide.body"></p>
                                    </div>
                                </article>
                            </template>
                        </div>

                        <div class="space-y-5 px-6 pb-7">
                            <div class="flex items-center justify-center gap-2">
                                <template x-for="(slide, index) in slides" :key="index">
                                    <button
                                        type="button"
                                        @click="go(index)"
                                        class="h-2 rounded-full transition-all"
                                        :class="active === index ? 'w-8 bg-indigo-600' : 'w-2 bg-slate-200'"
                                        :aria-label="'Buka step ' + (index + 1)"
                                    ></button>
                                </template>
                            </div>

                            <div class="grid grid-cols-[48px_1fr_48px] items-center gap-3">
                                <button type="button" @click="prev()" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200" :class="active === 0 ? 'invisible' : ''" aria-label="Sebelumnya">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M15 19l-7-7 7-7"/></svg>
                                </button>

                                <button type="button" @click="next()" class="flex h-12 items-center justify-center rounded-full bg-indigo-600 px-6 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
                                    <span x-text="isLast() ? 'Daftar' : 'Next'"></span>
                                </button>

                                <a x-show="isLast()" x-cloak href="{{ $whatsappUrl }}" target="_blank" class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white transition hover:bg-emerald-600" aria-label="Minta referral code via WhatsApp">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 10h8M8 14h5m8-2a9 9 0 11-16.5-5L3 21l4.2-1.4A9 9 0 1021 12z"/></svg>
                                </a>
                                <button x-show="!isLast()" x-cloak type="button" @click="go(slides.length - 1)" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200" aria-label="Lewati">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 text-center lg:hidden">
                    <a href="{{ $whatsappUrl }}" target="_blank" class="text-sm font-black text-emerald-700">Butuh referral code? Japri Admin WA</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
