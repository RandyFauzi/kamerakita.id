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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        card: '0 24px 70px rgba(15, 23, 42, 0.10)',
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

        @keyframes cardReveal {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes softFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes fadeRise {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reveal-card {
            animation: cardReveal .55s cubic-bezier(.16, 1, .3, 1) both;
        }

        .float-art {
            animation: softFloat 4.5s ease-in-out infinite;
        }

        .fade-rise {
            animation: fadeRise .5s cubic-bezier(.16, 1, .3, 1) both;
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#f7f7f6] text-slate-950">
    @php
        $manualBookUrl = asset('images/Assest/Manual Book/ManualBook_KameraKitaAI_22072026.pdf');
        $whatsappUrl = 'https://wa.me/6287886272647?text='.rawurlencode('Halo Admin KameraKita AI, saya ingin bergabung dan membutuhkan referral/invite code untuk registrasi aplikasi Minute.');
        $slides = [
            [
                'kicker' => 'Step 01',
                'title' => 'Perangkat sudah siap',
                'body' => 'Gunakan iPhone 12 ke atas, Pixel 6 ke atas, atau Samsung S21 ke atas agar rekaman layak QC.',
                'art' => 'phone',
                'accent' => '#2563eb',
            ],
            [
                'kicker' => 'Step 02',
                'title' => 'Buat email kerja',
                'body' => 'Daftar email di mail.tm, lalu simpan email dan password. Ini dipakai untuk KameraKita AI dan Minute.',
                'art' => 'mail',
                'accent' => '#06b6d4',
            ],
            [
                'kicker' => 'Step 03',
                'title' => 'Daftar dashboard',
                'body' => 'Buat akun KameraKita AI supaya laporan, QC, dan pembayaran bisa dipantau dari satu tempat.',
                'art' => 'dashboard',
                'accent' => '#8b5cf6',
            ],
            [
                'kicker' => 'Step 04',
                'title' => 'Install aplikasi Minute',
                'body' => 'Download aplikasi Minute, klik Get Started, lalu baca ToU dan policy sebelum menyetujui.',
                'art' => 'minute',
                'accent' => '#f97316',
            ],
            [
                'kicker' => 'Step 05',
                'title' => 'Minta referral code',
                'body' => 'Invite code diberikan oleh tim. Japri admin WhatsApp dulu sebelum daftar di aplikasi Minute.',
                'art' => 'code',
                'accent' => '#10b981',
            ],
            [
                'kicker' => 'Step 06',
                'title' => 'Rekam dan kirim laporan',
                'body' => 'Pakai headstrap, rekam landscape, lalu upload screenshot total durasi dan kualitas aplikasi.',
                'art' => 'report',
                'accent' => '#111827',
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
            cardAt(offset) {
                return (this.active + offset + this.slides.length) % this.slides.length;
            },
            isLast() {
                return this.active === this.slides.length - 1;
            }
        }"
        class="relative min-h-screen"
    >
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute left-[8%] top-[8%] h-56 w-56 rounded-full bg-sky-100 blur-3xl"></div>
            <div class="absolute right-[10%] top-[18%] h-72 w-72 rounded-full bg-indigo-100 blur-3xl"></div>
            <div class="absolute bottom-[6%] left-[34%] h-64 w-64 rounded-full bg-emerald-100 blur-3xl"></div>
        </div>

        <header class="relative z-10 mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-sky-700 shadow-sm">
                    <img src="{{ asset('images/Logo.webp') }}" alt="KAMERAKITA AI" class="h-full w-full object-contain">
                </span>
                <span class="text-sm font-black tracking-tight sm:text-base">KAMERAKITA<span class="text-indigo-600">.AI</span></span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="rounded-full bg-white px-4 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:bg-slate-50">Masuk</a>
                <a href="{{ $manualBookUrl }}" target="_blank" class="hidden rounded-full bg-white px-4 py-2 text-xs font-bold text-slate-500 shadow-sm transition hover:bg-slate-50 sm:inline-flex">Manual Book</a>
            </div>
        </header>

        <main class="relative z-10 mx-auto flex min-h-[calc(100vh-4rem)] max-w-7xl flex-col justify-center px-4 pb-8 sm:px-6 lg:px-8">
            <section class="mx-auto w-full max-w-6xl">
                <div class="mb-7 text-center">
                    <span class="inline-flex rounded-full bg-white px-4 py-2 text-[11px] font-black uppercase tracking-[0.24em] text-indigo-600 shadow-sm">Tutorial sebelum daftar</span>
                    <h1 class="mx-auto mt-4 max-w-2xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-4xl">
                        Pahami alurnya dulu, lalu mulai dengan tenang.
                    </h1>
                </div>

                <div class="hidden items-center justify-center gap-7 lg:flex">
                    <template x-for="offset in [-1, 0, 1]" :key="offset">
                        <article
                            class="reveal-card flex h-[540px] w-[300px] flex-col rounded-[34px] bg-white p-6 shadow-card transition-all duration-500 ease-out"
                            :class="offset === 0 ? 'z-10 scale-105 opacity-100 blur-0 hover:-translate-y-2' : 'translate-y-8 scale-[.92] opacity-45 blur-[1.5px]'"
                        >
                            <div class="flex flex-1 flex-col">
                                <div class="h-56">
                                    <template x-if="slides[cardAt(offset)].art === 'phone'">
                                        <div class="float-art flex h-full items-center justify-center">
                                            <svg viewBox="0 0 220 180" class="h-full w-full" fill="none">
                                                <path d="M68 66c-18 3-31 17-28 34 3 21 28 28 45 21l34-13c17-7 21-27 9-40-14-15-37-6-60-2z" fill="#DDF7F1"/>
                                                <rect x="72" y="33" width="70" height="116" rx="18" fill="#2563EB"/>
                                                <rect x="84" y="47" width="46" height="77" rx="10" fill="#DBEAFE"/>
                                                <path d="M52 117c16-18 32-22 47-8l20 18c8 7 6 20-4 25-22 11-53 1-71-16-8-8-1-9 8-19z" fill="#1D4ED8"/>
                                                <path d="M151 45l13 13m0-13l-13 13M46 47l9 9m0-9l-9 9" stroke="#111827" stroke-width="3" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="slides[cardAt(offset)].art === 'mail'">
                                        <div class="float-art flex h-full items-center justify-center">
                                            <svg viewBox="0 0 220 180" class="h-full w-full" fill="none">
                                                <circle cx="150" cy="52" r="32" fill="#99F6E4"/>
                                                <path d="M62 71h98v68H62z" fill="#06B6D4"/>
                                                <path d="M62 75l49 35 49-35" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M46 127c20-26 43-31 63-16l19 15c9 7 7 22-4 27-30 14-70 2-86-17-5-7 2-8 8-9z" fill="#2563EB"/>
                                                <path d="M154 52l11 11 21-24" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="slides[cardAt(offset)].art === 'dashboard'">
                                        <div class="float-art flex h-full items-center justify-center">
                                            <svg viewBox="0 0 220 180" class="h-full w-full" fill="none">
                                                <rect x="46" y="36" width="128" height="96" rx="22" fill="#EDE9FE"/>
                                                <rect x="62" y="54" width="96" height="60" rx="12" fill="#8B5CF6"/>
                                                <rect x="75" y="68" width="28" height="10" rx="5" fill="white"/>
                                                <rect x="75" y="86" width="70" height="8" rx="4" fill="#DDD6FE"/>
                                                <rect x="75" y="101" width="52" height="8" rx="4" fill="#DDD6FE"/>
                                                <path d="M58 135c20-23 44-25 63-6l9 9c8 8 4 21-7 24-29 8-67-7-79-27-3-6 7-2 14 0z" fill="#2563EB"/>
                                                <circle cx="164" cy="50" r="18" fill="#5EEAD4"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="slides[cardAt(offset)].art === 'minute'">
                                        <div class="float-art flex h-full items-center justify-center">
                                            <svg viewBox="0 0 220 180" class="h-full w-full" fill="none">
                                                <rect x="98" y="32" width="58" height="110" rx="16" fill="#111827"/>
                                                <rect x="106" y="45" width="42" height="78" rx="8" fill="#FFEDD5"/>
                                                <rect x="113" y="57" width="28" height="8" rx="4" fill="#F97316"/>
                                                <rect x="113" y="75" width="28" height="8" rx="4" fill="#FDBA74"/>
                                                <rect x="113" y="93" width="28" height="8" rx="4" fill="#FDBA74"/>
                                                <path d="M53 120c17-20 36-28 58-10l20 16c9 7 7 21-5 27-31 14-70 0-84-20-5-7 3-6 11-13z" fill="#2563EB"/>
                                                <circle cx="67" cy="57" r="21" fill="#FED7AA"/>
                                                <path d="M61 57h12m-6-6v12" stroke="#F97316" stroke-width="5" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="slides[cardAt(offset)].art === 'code'">
                                        <div class="float-art flex h-full items-center justify-center">
                                            <svg viewBox="0 0 220 180" class="h-full w-full" fill="none">
                                                <circle cx="132" cy="62" r="34" fill="#A7F3D0"/>
                                                <path d="M119 62l10 10 22-27" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <rect x="62" y="86" width="98" height="48" rx="16" fill="#111827"/>
                                                <path d="M84 110l-12 9 12 9m54-18l12 9-12 9m-21-30l-12 42" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M46 133c20-27 43-30 64-12l9 8c9 8 6 22-6 26-30 10-69-4-80-24-3-5 7 0 13 2z" fill="#2563EB"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="slides[cardAt(offset)].art === 'report'">
                                        <div class="float-art flex h-full items-center justify-center">
                                            <svg viewBox="0 0 220 180" class="h-full w-full" fill="none">
                                                <rect x="75" y="34" width="84" height="112" rx="22" fill="#111827"/>
                                                <rect x="88" y="52" width="58" height="76" rx="10" fill="#F8FAFC"/>
                                                <path d="M99 75h36M99 92h28M99 109h36" stroke="#CBD5E1" stroke-width="6" stroke-linecap="round"/>
                                                <circle cx="156" cy="52" r="28" fill="#5EEAD4"/>
                                                <path d="M145 52l8 8 17-22" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M48 128c18-25 41-29 61-12l18 15c9 8 6 22-6 27-31 12-70-2-84-22-5-7 3-5 11-8z" fill="#2563EB"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                <div class="fade-rise mt-auto text-center">
                                    <span class="text-[10px] font-black uppercase tracking-[0.24em]" :style="`color: ${slides[cardAt(offset)].accent}`" x-text="slides[cardAt(offset)].kicker"></span>
                                    <h2 class="mx-auto mt-3 max-w-[230px] text-2xl font-black leading-tight text-slate-950" x-text="slides[cardAt(offset)].title"></h2>
                                    <p class="mx-auto mt-3 max-w-[230px] text-xs leading-5 text-slate-500" x-text="slides[cardAt(offset)].body"></p>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-center gap-1.5" x-show="offset === 0">
                                <template x-for="(slide, index) in slides" :key="index">
                                    <button type="button" @click="go(index)" class="h-1.5 rounded-full transition-all" :class="active === index ? 'w-7 bg-slate-950' : 'w-1.5 bg-slate-200'" :aria-label="'Buka step ' + (index + 1)"></button>
                                </template>
                            </div>

                            <div class="mt-7 grid gap-3" :class="isLast() ? 'grid-cols-[44px_1fr_1fr]' : 'grid-cols-[44px_1fr_44px]'" x-show="offset === 0">
                                <button type="button" @click="prev()" class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-700 transition hover:bg-slate-50" :class="active === 0 ? 'invisible' : ''" aria-label="Sebelumnya">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button type="button" @click="next()" class="rounded-full bg-slate-950 px-5 py-3 text-xs font-black text-white transition hover:bg-slate-800" x-text="isLast() ? 'Daftar' : 'Next'"></button>
                                <a x-show="isLast()" x-cloak href="{{ $whatsappUrl }}" target="_blank" class="rounded-full border border-slate-200 px-5 py-3 text-center text-xs font-black text-slate-950 transition hover:bg-slate-50">WA Code</a>
                                <span x-show="!isLast()" aria-hidden="true"></span>
                            </div>
                        </article>
                    </template>
                </div>

                <div class="mx-auto max-w-sm lg:hidden">
                    <article class="reveal-card flex min-h-[590px] flex-col rounded-[34px] bg-white p-6 shadow-card">
                        <div
                            class="h-64"
                            :key="'mobile-art-' + active"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        >
                            <template x-if="slides[active].art === 'phone'">
                                <svg viewBox="0 0 220 180" class="h-full w-full" fill="none"><path d="M68 66c-18 3-31 17-28 34 3 21 28 28 45 21l34-13c17-7 21-27 9-40-14-15-37-6-60-2z" fill="#DDF7F1"/><rect x="72" y="33" width="70" height="116" rx="18" fill="#2563EB"/><rect x="84" y="47" width="46" height="77" rx="10" fill="#DBEAFE"/><path d="M52 117c16-18 32-22 47-8l20 18c8 7 6 20-4 25-22 11-53 1-71-16-8-8-1-9 8-19z" fill="#1D4ED8"/><path d="M151 45l13 13m0-13l-13 13M46 47l9 9m0-9l-9 9" stroke="#111827" stroke-width="3" stroke-linecap="round"/></svg>
                            </template>
                            <template x-if="slides[active].art === 'mail'">
                                <svg viewBox="0 0 220 180" class="h-full w-full" fill="none"><circle cx="150" cy="52" r="32" fill="#99F6E4"/><path d="M62 71h98v68H62z" fill="#06B6D4"/><path d="M62 75l49 35 49-35" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/><path d="M46 127c20-26 43-31 63-16l19 15c9 7 7 22-4 27-30 14-70 2-86-17-5-7 2-8 8-9z" fill="#2563EB"/><path d="M154 52l11 11 21-24" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </template>
                            <template x-if="slides[active].art === 'dashboard'">
                                <svg viewBox="0 0 220 180" class="h-full w-full" fill="none"><rect x="46" y="36" width="128" height="96" rx="22" fill="#EDE9FE"/><rect x="62" y="54" width="96" height="60" rx="12" fill="#8B5CF6"/><rect x="75" y="68" width="28" height="10" rx="5" fill="white"/><rect x="75" y="86" width="70" height="8" rx="4" fill="#DDD6FE"/><rect x="75" y="101" width="52" height="8" rx="4" fill="#DDD6FE"/><path d="M58 135c20-23 44-25 63-6l9 9c8 8 4 21-7 24-29 8-67-7-79-27-3-6 7-2 14 0z" fill="#2563EB"/><circle cx="164" cy="50" r="18" fill="#5EEAD4"/></svg>
                            </template>
                            <template x-if="slides[active].art === 'minute'">
                                <svg viewBox="0 0 220 180" class="h-full w-full" fill="none"><rect x="98" y="32" width="58" height="110" rx="16" fill="#111827"/><rect x="106" y="45" width="42" height="78" rx="8" fill="#FFEDD5"/><rect x="113" y="57" width="28" height="8" rx="4" fill="#F97316"/><rect x="113" y="75" width="28" height="8" rx="4" fill="#FDBA74"/><rect x="113" y="93" width="28" height="8" rx="4" fill="#FDBA74"/><path d="M53 120c17-20 36-28 58-10l20 16c9 7 7 21-5 27-31 14-70 0-84-20-5-7 3-6 11-13z" fill="#2563EB"/><circle cx="67" cy="57" r="21" fill="#FED7AA"/><path d="M61 57h12m-6-6v12" stroke="#F97316" stroke-width="5" stroke-linecap="round"/></svg>
                            </template>
                            <template x-if="slides[active].art === 'code'">
                                <svg viewBox="0 0 220 180" class="h-full w-full" fill="none"><circle cx="132" cy="62" r="34" fill="#A7F3D0"/><path d="M119 62l10 10 22-27" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/><rect x="62" y="86" width="98" height="48" rx="16" fill="#111827"/><path d="M84 110l-12 9 12 9m54-18l12 9-12 9m-21-30l-12 42" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/><path d="M46 133c20-27 43-30 64-12l9 8c9 8 6 22-6 26-30 10-69-4-80-24-3-5 7 0 13 2z" fill="#2563EB"/></svg>
                            </template>
                            <template x-if="slides[active].art === 'report'">
                                <svg viewBox="0 0 220 180" class="h-full w-full" fill="none"><rect x="75" y="34" width="84" height="112" rx="22" fill="#111827"/><rect x="88" y="52" width="58" height="76" rx="10" fill="#F8FAFC"/><path d="M99 75h36M99 92h28M99 109h36" stroke="#CBD5E1" stroke-width="6" stroke-linecap="round"/><circle cx="156" cy="52" r="28" fill="#5EEAD4"/><path d="M145 52l8 8 17-22" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/><path d="M48 128c18-25 41-29 61-12l18 15c9 8 6 22-6 27-31 12-70-2-84-22-5-7 3-5 11-8z" fill="#2563EB"/></svg>
                            </template>
                        </div>

                        <div
                            class="fade-rise mt-auto text-center"
                            :key="'mobile-copy-' + active"
                        >
                            <span class="text-[10px] font-black uppercase tracking-[0.24em]" :style="`color: ${slides[active].accent}`" x-text="slides[active].kicker"></span>
                            <h2 class="mx-auto mt-3 max-w-[270px] text-2xl font-black leading-tight text-slate-950" x-text="slides[active].title"></h2>
                            <p class="mx-auto mt-3 max-w-[270px] text-xs leading-5 text-slate-500" x-text="slides[active].body"></p>
                        </div>

                        <div class="mt-8 flex justify-center gap-1.5">
                            <template x-for="(slide, index) in slides" :key="index">
                                <button type="button" @click="go(index)" class="h-1.5 rounded-full transition-all" :class="active === index ? 'w-7 bg-slate-950' : 'w-1.5 bg-slate-200'" :aria-label="'Buka step ' + (index + 1)"></button>
                            </template>
                        </div>

                        <div class="mt-7 grid grid-cols-[44px_1fr_44px] gap-3">
                            <button type="button" @click="prev()" class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-700" :class="active === 0 ? 'invisible' : ''" aria-label="Sebelumnya">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="next()" class="rounded-full bg-slate-950 px-5 py-3 text-xs font-black text-white transition hover:bg-slate-800" x-text="isLast() ? 'Daftar' : 'Next'"></button>
                            <span aria-hidden="true"></span>
                        </div>

                        <a href="{{ $whatsappUrl }}" target="_blank" class="mt-4 text-center text-xs font-black text-emerald-700">Minta referral code via WhatsApp</a>
                    </article>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
