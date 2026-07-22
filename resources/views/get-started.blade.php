<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get Started - KAMERAKITA AI</title>
    <meta name="description" content="Panduan interaktif sebelum daftar sebagai kontributor KAMERAKITA AI.">
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
                        panel: '0 28px 90px rgba(15, 23, 42, 0.12)',
                        glow: '0 24px 70px rgba(37, 99, 235, 0.22)',
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

        .stage-grid {
            background-image:
                linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#f4f7ff] text-slate-950">
    @php
        $manualBookUrl = asset('images/Assest/Manual Book/ManualBook_KameraKitaAI_22072026.pdf');
        $whatsappUrl = 'https://wa.me/6287886272647?text='.rawurlencode('Halo Admin KameraKita AI, saya ingin bergabung dan membutuhkan referral/invite code untuk registrasi aplikasi Minute.');
        $slides = [
            [
                'label' => 'Perangkat',
                'eyebrow' => 'Setup 01',
                'title' => 'Pastikan perangkat siap untuk rekaman QC',
                'body' => 'Gunakan perangkat yang didukung agar video bisa direkam stabil dan sesuai standar KameraKita AI.',
                'accent' => '#2563eb',
                'soft' => '#dbeafe',
                'icon' => 'phone',
                'items' => ['iPhone 12 ke atas', 'Google Pixel 6 ke atas', 'Samsung Galaxy S21 ke atas'],
                'stat' => 'Device check',
                'cta' => 'Lanjut',
            ],
            [
                'label' => 'Email',
                'eyebrow' => 'Setup 02',
                'title' => 'Buat email kerja dan simpan aksesnya',
                'body' => 'Email dari mail.tm dipakai untuk daftar dashboard dan aplikasi Minute. Jangan sampai email atau password hilang.',
                'accent' => '#7c3aed',
                'soft' => '#ede9fe',
                'icon' => 'mail',
                'items' => ['Buka mail.tm', 'Create account', 'Simpan email dan password'],
                'stat' => 'Identity ready',
                'cta' => 'Lanjut',
            ],
            [
                'label' => 'Dashboard',
                'eyebrow' => 'Setup 03',
                'title' => 'Aktifkan akun dashboard KameraKita AI',
                'body' => 'Dashboard dipakai untuk mengirim laporan, melihat hasil QC, dan memantau status pembayaran.',
                'accent' => '#0891b2',
                'soft' => '#cffafe',
                'icon' => 'dashboard',
                'items' => ['Daftar akun', 'Masuk dashboard', 'Lengkapi data diri'],
                'stat' => 'Account ready',
                'cta' => 'Lanjut',
            ],
            [
                'label' => 'Minute',
                'eyebrow' => 'Setup 04',
                'title' => 'Install aplikasi Minute dan mulai onboarding',
                'body' => 'Download aplikasi Minute, buka aplikasinya, lalu baca ToU dan policy sebelum menyetujui.',
                'accent' => '#f97316',
                'soft' => '#ffedd5',
                'icon' => 'download',
                'items' => ['Download aplikasi', 'Klik Get Started', 'Baca ToU dan policy'],
                'stat' => 'App installed',
                'cta' => 'Lanjut',
            ],
            [
                'label' => 'Referral',
                'eyebrow' => 'Setup 05',
                'title' => 'Minta referral code lewat WhatsApp admin',
                'body' => 'Invite code diberikan oleh tim KameraKita AI. Japri admin dulu sebelum membuat akun di aplikasi Minute.',
                'accent' => '#059669',
                'soft' => '#d1fae5',
                'icon' => 'code',
                'items' => ['Japri admin', 'Minta invite code', 'Gunakan saat daftar Minute'],
                'stat' => 'Invite code',
                'cta' => 'Minta Code',
            ],
            [
                'label' => 'Laporan',
                'eyebrow' => 'Setup 06',
                'title' => 'Rekam sesuai SOP dan kirim laporan harian',
                'body' => 'Gunakan headstrap, rekam landscape, pastikan tangan terlihat, lalu upload screenshot durasi dan kualitas aplikasi.',
                'accent' => '#111827',
                'soft' => '#e5e7eb',
                'icon' => 'check',
                'items' => ['Pakai headstrap', 'Video landscape', 'Upload 2 screenshot laporan'],
                'stat' => 'Ready to work',
                'cta' => 'Daftar',
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
            progress() {
                return ((this.active + 1) / this.slides.length) * 100;
            },
            isLast() {
                return this.active === this.slides.length - 1;
            },
            primaryAction() {
                if (this.slides[this.active].label === 'Referral') {
                    window.open('{{ $whatsappUrl }}', '_blank');
                    return;
                }

                this.next();
            }
        }"
        class="relative min-h-screen"
    >
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-28 top-16 h-80 w-80 rounded-full bg-cyan-200/55 blur-3xl"></div>
            <div class="absolute right-0 top-0 h-96 w-96 rounded-full bg-indigo-200/70 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-96 w-96 rounded-full bg-emerald-100/70 blur-3xl"></div>
        </div>

        <header class="relative z-10 mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-sky-700 shadow-sm">
                    <img src="{{ asset('images/Logo.webp') }}" alt="KAMERAKITA AI" class="h-full w-full object-contain">
                </span>
                <span class="text-sm font-black tracking-tight sm:text-base">KAMERAKITA<span class="text-indigo-600">.AI</span></span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="rounded-full bg-white/85 px-4 py-2 text-xs font-black text-slate-700 shadow-sm ring-1 ring-white transition hover:bg-white">Masuk</a>
                <a href="{{ $manualBookUrl }}" target="_blank" class="hidden rounded-full bg-white/70 px-4 py-2 text-xs font-bold text-slate-500 ring-1 ring-white transition hover:bg-white sm:inline-flex">Manual Book</a>
            </div>
        </header>

        <main class="relative z-10 mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl items-center gap-6 px-4 pb-8 sm:px-6 lg:grid-cols-[280px_1fr] lg:px-8">
            <aside class="hidden lg:block">
                <div class="rounded-[28px] border border-white/80 bg-white/72 p-4 shadow-panel backdrop-blur">
                    <div class="px-2 pb-4">
                        <span class="text-[10px] font-black uppercase tracking-[0.24em] text-indigo-600">KameraKita AI</span>
                        <h1 class="mt-2 text-2xl font-black leading-tight text-slate-950">Setup sebelum mulai kerja</h1>
                        <p class="mt-2 text-xs leading-5 text-slate-500">Selesaikan urutan ini sebelum daftar dan mulai membuat laporan.</p>
                    </div>

                    <nav class="space-y-2" aria-label="Step tutorial">
                        <template x-for="(slide, index) in slides" :key="slide.label">
                            <button
                                type="button"
                                @click="go(index)"
                                class="group flex w-full items-center gap-3 rounded-2xl p-3 text-left transition"
                                :class="active === index ? 'bg-slate-950 text-white shadow-lg shadow-slate-300/40' : 'text-slate-500 hover:bg-white'"
                            >
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black"
                                    :class="active === index ? 'bg-white text-slate-950' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-50'"
                                    x-text="String(index + 1).padStart(2, '0')"
                                ></span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-black" x-text="slide.label"></span>
                                    <span class="block truncate text-[11px]" :class="active === index ? 'text-slate-300' : 'text-slate-400'" x-text="slide.title"></span>
                                </span>
                            </button>
                        </template>
                    </nav>
                </div>
            </aside>

            <section class="rounded-[32px] border border-white/80 bg-white/68 p-3 shadow-panel backdrop-blur sm:p-4 lg:p-5">
                <div class="grid min-h-[720px] overflow-hidden rounded-[26px] bg-white lg:grid-cols-[1fr_380px]">
                    <div class="relative flex flex-col bg-slate-950 text-white">
                        <div class="stage-grid absolute inset-0 opacity-45"></div>
                        <div class="absolute inset-0" :style="`background: radial-gradient(circle at 22% 18%, ${slides[active].accent}55, transparent 32%), radial-gradient(circle at 72% 62%, ${slides[active].soft}, transparent 34%)`"></div>

                        <div class="relative z-10 flex items-center justify-between gap-4 border-b border-white/10 px-5 py-4 sm:px-7">
                            <div>
                                <span class="block text-[10px] font-black uppercase tracking-[0.26em] text-white/50">Onboarding</span>
                                <span class="mt-1 block text-sm font-black" x-text="slides[active].eyebrow"></span>
                            </div>
                            <div class="min-w-28 rounded-full bg-white/10 p-1">
                                <div class="h-2 rounded-full bg-white transition-all duration-500" :style="`width: ${progress()}%`"></div>
                            </div>
                        </div>

                        <div class="relative z-10 flex flex-1 flex-col justify-between p-5 sm:p-7 lg:p-9">
                            <div class="max-w-2xl">
                                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-xs font-black text-white ring-1 ring-white/10">
                                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                    <span x-text="slides[active].stat"></span>
                                </div>

                                <template x-for="(slide, index) in slides" :key="slide.title">
                                    <article
                                        x-cloak
                                        x-show="active === index"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 -translate-y-2"
                                    >
                                        <h2 class="mt-7 max-w-3xl text-3xl font-black leading-tight tracking-tight sm:text-5xl" x-text="slide.title"></h2>
                                        <p class="mt-5 max-w-2xl text-sm leading-7 text-white/72 sm:text-base" x-text="slide.body"></p>
                                    </article>
                                </template>
                            </div>

                            <div class="mt-8 grid gap-4 lg:grid-cols-[1fr_220px]">
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <template x-for="item in slides[active].items" :key="item">
                                        <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-slate-950">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            <span class="mt-3 block text-xs font-bold leading-5 text-white/88" x-text="item"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="rounded-2xl bg-white p-4 text-slate-950">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Current Step</span>
                                    <strong class="mt-2 block text-4xl font-black" x-text="String(active + 1).padStart(2, '0')"></strong>
                                    <span class="mt-1 block text-xs font-bold text-slate-500">dari 06 langkah</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col border-t border-slate-100 bg-white lg:border-l lg:border-t-0">
                        <div class="p-5 sm:p-7">
                            <div class="relative mx-auto aspect-[9/16] max-w-[260px] overflow-hidden rounded-[34px] border-[10px] border-slate-950 bg-slate-100 shadow-glow">
                                <div class="absolute left-1/2 top-2 z-20 h-5 w-24 -translate-x-1/2 rounded-full bg-slate-950"></div>
                                <div class="flex h-full flex-col pt-9">
                                    <div class="px-5">
                                        <div class="rounded-[26px] p-5 text-white" :style="`background: linear-gradient(145deg, ${slides[active].accent}, #111827)`">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-black uppercase tracking-widest opacity-70">Preview</span>
                                                <span class="rounded-full bg-white/20 px-2 py-1 text-[10px] font-black" x-text="slides[active].label"></span>
                                            </div>
                                            <div class="mt-10 flex h-24 items-center justify-center rounded-3xl bg-white/16 ring-1 ring-white/20">
                                                <template x-if="slides[active].icon === 'phone'">
                                                    <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 2h8a2 2 0 012 2v16a2 2 0 01-2 2H8a2 2 0 01-2-2V4a2 2 0 012-2zm4 17h.01M9 5h6"/></svg>
                                                </template>
                                                <template x-if="slides[active].icon === 'mail'">
                                                    <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4zM4 7l8 6 8-6"/></svg>
                                                </template>
                                                <template x-if="slides[active].icon === 'dashboard'">
                                                    <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4zM8 9h3m-3 4h8m-8 4h5m4-8h1"/></svg>
                                                </template>
                                                <template x-if="slides[active].icon === 'download'">
                                                    <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v11m0 0l-4-4m4 4l4-4M5 18h14v3H5z"/></svg>
                                                </template>
                                                <template x-if="slides[active].icon === 'code'">
                                                    <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9l-4 3 4 3m8-6l4 3-4 3M14 5l-4 14"/></svg>
                                                </template>
                                                <template x-if="slides[active].icon === 'check'">
                                                    <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 5-6M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7z"/></svg>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 flex-col justify-center px-6 text-center">
                                        <span class="text-[10px] font-black uppercase tracking-[0.24em] text-indigo-600" x-text="slides[active].eyebrow"></span>
                                        <h3 class="mt-3 text-xl font-black leading-tight text-slate-950" x-text="slides[active].label"></h3>
                                        <p class="mt-2 text-xs leading-5 text-slate-500" x-text="slides[active].body"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-center gap-2">
                                <template x-for="(slide, index) in slides" :key="index">
                                    <button
                                        type="button"
                                        @click="go(index)"
                                        class="h-2 rounded-full transition-all"
                                        :style="active === index ? `background: ${slide.accent}` : ''"
                                        :class="active === index ? 'w-8' : 'w-2 bg-slate-200'"
                                        :aria-label="'Buka step ' + (index + 1)"
                                    ></button>
                                </template>
                            </div>
                        </div>

                        <div class="mt-auto border-t border-slate-100 p-5 sm:p-7">
                            <div class="grid grid-cols-[48px_1fr_48px] gap-3">
                                <button type="button" @click="prev()" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 transition hover:bg-slate-200" :class="active === 0 ? 'invisible' : ''" aria-label="Sebelumnya">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M15 19l-7-7 7-7"/></svg>
                                </button>

                                <button type="button" @click="primaryAction()" class="flex h-12 items-center justify-center rounded-2xl px-5 text-xs font-black uppercase tracking-widest text-white shadow-lg transition hover:opacity-95" :style="`background: ${slides[active].accent}`">
                                    <span x-text="slides[active].cta"></span>
                                </button>

                                <button type="button" @click="go(slides.length - 1)" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 transition hover:bg-slate-200" aria-label="Lewati">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                </button>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <a href="{{ $whatsappUrl }}" target="_blank" class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-700 transition hover:bg-emerald-100">Referral WA</a>
                                <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-slate-950 px-4 py-2 text-xs font-black text-white transition hover:bg-slate-800">Daftar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
