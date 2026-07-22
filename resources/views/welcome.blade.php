<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KAMERAKITA AI — Platform Rekam Video & Datasets Terpercaya</title>
    
    <!-- Meta Description -->
    <meta name="description" content="Gabung jadi mitra kontributor di KAMERAKITA AI! Dapatkan penghasilan tambahan rutin lewat rekam video. Tanpa modal, rekap transparan, transfer mingguan!">
    <link rel="icon" href="{{ asset('images/Logo.webp') }}" type="image/webp">

    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display / Instrument Serif -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@1,600;1,700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            deep: '#082545',
                            dark: '#05182e',
                            navy: '#0b3560',
                            accent: '#38bdf8',
                            light: '#e0f2fe',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .bg-hero-solid {
            background-color: #0284c7;
        }

        .btn-evermos-white {
            background-color: #ffffff;
            color: #0284c7;
            font-weight: 800;
            border-radius: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        }
        .btn-evermos-white:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .btn-brand-navy {
            background-color: #0284c7;
            color: #ffffff;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .btn-brand-navy:hover {
            background-color: #0369a1;
        }

        /* Floating blob animation for right hero image */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }
        .animate-float {
            animation: floatSlow 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="antialiased selection:bg-sky-500 selection:text-white">

    <!-- 1. Top Navbar (Clean White background, exact Evermos layout) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-700 p-1 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/Logo.webp') }}" alt="KAMERAKITA AI Logo" class="w-full h-full object-contain rounded-lg">
                </div>
                <span class="text-xl font-black tracking-[-0.03em] text-slate-900">
                    KameraKita<span class="ml-0.5 bg-gradient-to-r from-sky-500 to-indigo-600 bg-clip-text text-transparent">AI</span>
                </span>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-700">
                <a href="#keunggulan" class="hover:text-sky-600 transition flex items-center gap-1">
                    Keunggulan <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                <a href="#kalkulator" class="hover:text-sky-600 transition flex items-center gap-1">
                    Kalkulator <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                <a href="#cara-kerja" class="hover:text-sky-600 transition">Cara Kerja</a>
                <a href="#mitra" class="hover:text-sky-600 transition">Cerita Mitra</a>
                <a href="#faq" class="hover:text-sky-600 transition">FAQ</a>
            </nav>

            <!-- Action Button -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-brand-navy px-6 py-2.5 text-sm">
                        Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-brand-navy px-6 py-2.5 text-sm">
                        Masuk / Daftar
                    </a>
                @endauth
            </div>

        </div>
    </header>

    <!-- 2. Hero Section (Clean, Neat & Perfectly Proportioned Evermos Layout) -->
    <section class="bg-hero-solid text-white py-12 lg:py-16 overflow-hidden relative">
        <div class="max-w-7xl mx-auto pl-4 sm:pl-6 lg:pl-8 pr-0 overflow-visible">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <!-- Left Hero Content Column -->
                <div class="lg:col-span-7 space-y-6 text-left z-10 py-4">
                    
                    <!-- Headline H1 (Larger & Clean Line Breaks) -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.14]">
                        Saatnya Anda jadi <br/>
                        <span class="text-[#FDE047] italic font-serif font-normal">andalan keluarga</span> <br/>
                        lewat penghasilan tambahan
                    </h1>

                    <!-- Hero Call to Action Button -->
                    <div class="pt-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-evermos-white inline-block px-8 py-3.5 text-sm sm:text-base font-extrabold shadow-lg">
                                Gabung sekarang →
                            </a>
                        @else
                            <a href="{{ route('get-started') }}" class="btn-evermos-white inline-block px-8 py-3.5 text-sm sm:text-base font-extrabold shadow-lg">
                                Gabung sekarang →
                            </a>
                        @endauth
                    </div>

                </div>

                <!-- Right Hero Image Column (Flush to Absolute Right Screen Edge) -->
                <div class="lg:col-span-5 relative flex justify-end items-end self-end h-full pt-4 pr-0 overflow-visible">
                    
                    <!-- SVG Organic Blob Backdrop (Evermos Style Cloud Shape) -->
                    <div class="absolute inset-0 flex items-center justify-end pointer-events-none translate-y-6 translate-x-12">
                        <svg class="w-[135%] h-[135%] text-[#02649b] opacity-85 animate-float" viewBox="0 0 500 500" fill="currentColor">
                            <path d="M410,310Q370,370,305,405Q240,440,175,410Q110,380,85,315Q60,250,90,185Q120,120,185,85Q250,50,315,85Q380,120,415,185Q450,250,410,310Z" />
                        </svg>
                    </div>

                    <!-- Hero Main Image Container (Mentok Rapat Ke Kanan & Bawah) -->
                    <div class="relative z-10 w-full max-w-lg ml-auto mr-0 pr-0 flex justify-end items-end">
                        
                        <!-- Real Hero PNG WebP Image (Flush to Right & Bottom Edge) -->
                        <img src="{{ asset('images/Assest/Hero png.webp') }}?v={{ filemtime(public_path('images/Assest/Hero png.webp')) }}" 
                             alt="Mitra KAMERAKITA AI" 
                             class="w-full h-auto max-h-[440px] sm:max-h-[480px] lg:max-h-[540px] object-contain object-bottom object-right block -mb-12 lg:-mb-16 translate-x-6 sm:translate-x-10 lg:translate-x-16 xl:translate-x-20 drop-shadow-xl">

                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- Highlight Value Banner Section (Exact Evermos Style directly below Hero) -->
    <section class="bg-[#dcfce7] sm:bg-[#e0f2fe] py-10 sm:py-14 px-4 sm:px-6 relative overflow-hidden border-b border-sky-200/60">
        <!-- Background Organic Swirl Illustrations -->
        <div class="absolute -left-12 -bottom-12 w-48 h-48 rounded-full border-[24px] border-sky-300/30 pointer-events-none"></div>
        <div class="absolute -right-12 -top-12 w-56 h-56 rounded-full border-[28px] border-sky-300/30 pointer-events-none"></div>

        <div class="max-w-5xl mx-auto text-center relative z-10">
            <p class="text-slate-800 text-lg sm:text-2xl lg:text-3xl font-medium leading-relaxed sm:leading-snug tracking-tight">
                Dengan jadi <span class="text-pink-600 font-extrabold">mitra kontributor</span> KAMERAKITA AI, Anda bisa punya <span class="font-extrabold text-slate-900">penghasilan tambahan</span> lewat <span class="font-extrabold text-slate-900">rekam <span class="text-pink-600 font-extrabold">video</span> tugas harian tanpa modal!</span> Di sini Anda bisa dapat komisi mingguan rutin, <span class="font-extrabold text-slate-900">sistem QC transparan</span>, & bimbingan langsung dari <span class="font-extrabold text-slate-900">tim verifikator berpengalaman</span>.
            </p>
        </div>
    </section>

    <!-- Visual Benefit Mosaic Section -->
    <section class="bg-white py-16 sm:py-20 px-4 sm:px-6 border-b border-slate-200">
        <div class="max-w-6xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-sky-700 bg-sky-100 px-3.5 py-1 rounded-full inline-block">
                    ALASAN BERGABUNG
                </span>
                <h2 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Kenapa kamu cocok jadi Kontributor KameraKita AI?
                </h2>
                <p class="mt-3 text-sm sm:text-base text-slate-600 leading-relaxed">
                    Pekerjaan lebih jelas, laporan tercatat, dan pendapatan bisa dipantau dari dashboard yang transparan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-5">
                <article class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-sky-50 to-cyan-100 p-7 min-h-[260px] md:col-span-3 lg:col-span-4 shadow-sm border border-sky-100">
                    <div class="relative z-10 max-w-[230px]">
                        <h3 class="text-2xl font-extrabold leading-tight text-slate-900">
                            Tidak perlu stok, packing, atau kirim barang
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Fokus kerjakan tugas rekam video sesuai panduan dari HP pribadi.
                        </p>
                    </div>
                    <div class="absolute bottom-0 right-3 h-36 w-32 rounded-t-[48px] bg-white/55 border border-white/70"></div>
                    <div class="absolute bottom-6 right-12 h-20 w-20 rounded-full bg-sky-500/85"></div>
                    <div class="absolute bottom-0 right-16 h-24 w-28 rounded-t-full bg-slate-900/90"></div>
                </article>

                <article class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-50 to-lime-100 p-7 min-h-[260px] md:col-span-3 lg:col-span-4 shadow-sm border border-emerald-100">
                    <div class="relative z-10 max-w-[220px]">
                        <h3 class="text-2xl font-extrabold leading-tight text-slate-900">
                            Komisi dihitung dari durasi yang disetujui
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Setiap menit approved masuk rekap dan bisa dilihat transparan.
                        </p>
                    </div>
                    <div class="absolute right-7 top-8 flex h-24 w-24 items-center justify-center rounded-full bg-white/70 text-3xl font-black text-emerald-600">
                        Rp
                    </div>
                    <div class="absolute -bottom-8 right-8 h-40 w-40 rounded-full bg-emerald-300/35"></div>
                </article>

                <article class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-indigo-50 to-violet-100 p-7 min-h-[260px] md:col-span-6 lg:col-span-4 lg:row-span-2 shadow-sm border border-indigo-100">
                    <div class="relative z-10 max-w-[260px]">
                        <h3 class="text-2xl font-extrabold leading-tight text-slate-900">
                            Dibantu tim admin dan verifikator
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Ada arahan untuk laporan, catatan QC, dan perbaikan bila data belum sesuai.
                        </p>
                    </div>
                    <div class="absolute bottom-0 left-8 right-8 h-52 rounded-t-[90px] bg-white/60 border border-white/70"></div>
                    <div class="absolute bottom-16 left-12 h-24 w-24 rounded-full bg-indigo-300/80"></div>
                    <div class="absolute bottom-12 left-28 h-28 w-28 rounded-full bg-sky-400/75"></div>
                    <div class="absolute bottom-14 right-14 h-24 w-24 rounded-full bg-violet-400/75"></div>
                    <div class="absolute bottom-0 left-10 h-28 w-28 rounded-t-full bg-slate-900/90"></div>
                    <div class="absolute bottom-0 left-32 h-32 w-32 rounded-t-full bg-indigo-700/85"></div>
                    <div class="absolute bottom-0 right-12 h-28 w-28 rounded-t-full bg-slate-800/85"></div>
                </article>

                <article class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-amber-50 to-orange-100 p-7 min-h-[230px] md:col-span-3 lg:col-span-4 shadow-sm border border-orange-100">
                    <div class="relative z-10 max-w-[210px]">
                        <h3 class="text-2xl font-extrabold leading-tight text-slate-900">
                            Cukup ikuti panduan kerja harian
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Tutorial dan manual book membantu worker baru mulai lebih tenang.
                        </p>
                    </div>
                    <div class="absolute bottom-7 right-8 h-24 w-24 rounded-3xl bg-white/70 rotate-6"></div>
                    <div class="absolute bottom-12 right-14 h-10 w-14 rounded-full bg-orange-400/80"></div>
                    <div class="absolute bottom-6 right-20 h-16 w-20 rounded-t-full bg-slate-900/90"></div>
                </article>

                <article class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-rose-50 to-pink-100 p-7 min-h-[230px] md:col-span-3 lg:col-span-4 shadow-sm border border-rose-100">
                    <div class="relative z-10 max-w-[220px]">
                        <h3 class="text-2xl font-extrabold leading-tight text-slate-900">
                            Pembayaran manual lebih aman
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Admin memproses transfer berdasarkan rekap approved yang sudah diverifikasi.
                        </p>
                    </div>
                    <div class="absolute bottom-6 right-8 rounded-2xl bg-white/75 px-5 py-4 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Rekap</div>
                        <div class="mt-1 text-2xl font-black text-rose-600">Paid</div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- 3. Brand / Partner Logo Section -->
    <section id="keunggulan" class="py-16 sm:py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#064e5f] tracking-tight leading-tight">
                    Dibangun untuk alur kerja yang lebih rapi
                </h2>
                <p class="mt-5 text-base sm:text-lg leading-8 text-slate-500">
                    Dari aplikasi, perangkat, sampai proses validasi, semuanya dibuat agar kontributor bisa bekerja dengan alur yang lebih jelas.
                </p>
            </div>

            @php
                $brandLogos = [
                    'Minute',
                    'KameraKita AI',
                    'Mail.tm',
                    'Headstrap',
                    'Dashboard QC',
                    'Payroll',
                    'Mobile Device',
                    'Video Data',
                    'Quality Check',
                    'Transfer Bank',
                ];
            @endphp

            <div class="mt-12 grid grid-cols-2 gap-x-8 gap-y-8 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($brandLogos as $brandLogo)
                    <div class="group flex h-16 items-center justify-center">
                        <div class="flex h-14 min-w-[132px] items-center justify-center rounded-2xl border border-slate-200/70 bg-white px-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-sky-200 hover:shadow-md">
                            <span class="text-center text-sm font-black tracking-tight text-slate-400 transition group-hover:text-sky-700">
                                {{ $brandLogo }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 text-center text-xs font-semibold text-slate-400">
                Tampilan ini akan dilengkapi bertahap mengikuti kebutuhan operasional KameraKita AI.
            </p>
        </div>
    </section>

    <!-- 3. Keunggulan Section (Legacy, hidden) -->
    <section class="hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-sky-700 bg-sky-100 px-3.5 py-1 rounded-full inline-block">
                    KEUNTUNGAN MITRA
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Kenapa Sih Kamu Harus Bergabung Jadi Mitra KAMERAKITA AI?
                </h2>
                <p class="text-slate-600 text-sm sm:text-base">
                    Platform rekam video dengan sistem verifikasi transparan dan pencairan pendapatan rutin setiap minggu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1 -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xl">
                        💰
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Tanpa Modal & Gratis</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pendaftaran 100% gratis tanpa biaya sepeser pun. Rekam video sesuai instruksi panduan langsung dari HP Anda.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl">
                        📅
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Transfer Rutin Tiap Rabu</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Rekap durasi hasil QC diselesaikan secara akurat, dan komisi pendapatan langsung ditransfer ke bank Anda setiap minggunya.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xl">
                        🔍
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">QC Room Transparan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengecekan bukti durasi aplikasi dilakukan side-by-side secara presisi. Anda dapat melihat status laporan di dashboard.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xl">
                        🏠
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Kerja dari Rumah</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Atur jam kerja Anda sendiri dari rumah tanpa terikat kantor. Bebas kapan saja sesuai waktu luang Anda.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- 4. Interactive Income Calculator (Evermos Slider Style) -->
    <section id="kalkulator" class="py-20 bg-white" x-data="{
        daysPerWeek: 5,
        dailyRate: 50000,
        get weeklyEarnings() {
            return this.daysPerWeek * this.dailyRate;
        },
        get monthlyEarnings() {
            return this.weeklyEarnings * 4;
        },
        formatRupiah(number) {
            return 'Rp ' + number.toLocaleString('id-ID');
        }
    }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="relative overflow-hidden bg-white border border-slate-200 rounded-[2rem] p-6 sm:p-10 lg:p-12 shadow-[0_24px_70px_rgba(15,23,42,0.10)]">
                <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-sky-100 blur-3xl"></div>
                <div class="absolute -left-20 bottom-0 h-56 w-56 rounded-full bg-emerald-100 blur-3xl"></div>
                
                <div class="relative text-center space-y-3 mb-10">
                    <span class="text-xs font-bold uppercase tracking-widest text-sky-700 bg-sky-100 px-3.5 py-1 rounded-full inline-block">
                        SIMULASI PENDAPATAN
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Lihat gambaran penghasilan dengan target harian
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base max-w-2xl mx-auto leading-7">
                        Estimasi dibuat sederhana dengan acuan Rp50.000 per hari aktif. Hasil akhir tetap mengikuti laporan yang lolos QC.
                    </p>
                </div>

                <div class="relative grid grid-cols-1 lg:grid-cols-[1fr_1.05fr] gap-8 items-stretch pt-2">
                    
                    <!-- Input Panel -->
                    <div class="flex flex-col justify-between gap-6 rounded-3xl border border-slate-200 bg-slate-50/80 p-6 sm:p-7">
                        
                        <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-400">Acuan harian</span>
                            <div class="mt-2 flex items-end justify-between gap-4">
                                <div>
                                    <div class="text-3xl font-black tracking-tight text-slate-950" x-text="formatRupiah(dailyRate)"></div>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Per hari aktif dengan laporan yang sesuai panduan.</p>
                                </div>
                                <div class="hidden h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 sm:flex">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-10v12"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm font-extrabold text-slate-900">Hari aktif per minggu</span>
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-sm font-black text-sky-700" x-text="daysPerWeek + ' hari'"></span>
                            </div>
                            <input type="range" min="1" max="7" x-model.number="daysPerWeek" class="w-full accent-sky-600 cursor-pointer">
                            <div class="flex justify-between text-[11px] text-slate-400 font-bold">
                                <span>1 Hari</span>
                                <span>4 Hari</span>
                                <span>7 Hari</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Mingguan</span>
                                <div class="mt-1 text-lg font-black text-slate-950" x-text="formatRupiah(weeklyEarnings)"></div>
                            </div>
                            <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Bulanan</span>
                                <div class="mt-1 text-lg font-black text-slate-950" x-text="formatRupiah(monthlyEarnings)"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings Output Box -->
                    <div class="relative overflow-hidden rounded-3xl bg-hero-solid p-7 sm:p-8 text-white shadow-xl space-y-6 text-center lg:text-left">
                        <div>
                            <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest text-sky-200">Estimasi utama</span>
                            <span class="mt-5 text-sm font-semibold uppercase tracking-widest text-slate-400 block">Potensi mingguan</span>
                            <span class="text-4xl sm:text-5xl font-black mt-2 block text-white" x-text="formatRupiah(weeklyEarnings)"></span>
                            <p class="mt-3 text-sm leading-6 text-slate-300">
                                Dengan <span class="font-bold text-white" x-text="daysPerWeek + ' hari aktif'"></span> dan acuan <span class="font-bold text-white">Rp50.000/hari</span>.
                            </p>
                        </div>
                        <div class="pt-4 border-t border-white/20">
                            <span class="text-xs uppercase font-semibold text-slate-400 block">ESTIMASI TOTAL BULANAN</span>
                            <span class="text-2xl font-extrabold mt-1 block text-sky-300" x-text="formatRupiah(monthlyEarnings)"></span>
                        </div>
                        <a href="{{ route('get-started') }}" class="btn-evermos-white w-full block py-3.5 text-xs uppercase text-center shadow-md">
                            Mulai dari tutorial singkat ->
                        </a>
                        <p class="text-center text-[11px] leading-5 text-slate-400">
                            Simulasi bukan jaminan pendapatan. Nominal mengikuti laporan approved dan ketentuan operasional.
                        </p>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- 5. Cara Kerja Section -->
    <section id="cara-kerja" class="py-20 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-sky-700 bg-sky-100 px-3.5 py-1 rounded-full inline-block">
                    CARA KERJA
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Gimana Cara Kerjanya? 4 Langkah Mudah
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-sky-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        1
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Daftar Akun Gratis</h4>
                    <p class="text-xs text-slate-500">Isi pendaftaran mitra tanpa biaya sepeser pun.</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-sky-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        2
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Rekam Video</h4>
                    <p class="text-xs text-slate-500">Ikuti panduan mudah rekam video sesuai instruksi.</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-sky-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        3
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Kirim Laporan & Bukti</h4>
                    <p class="text-xs text-slate-500">Upload screenshot durasi kerja ke QC Room.</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-sky-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        4
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Terima Transferan</h4>
                    <p class="text-xs text-slate-500">Hasil verifikasi disetujui & cair otomatis tiap Rabu.</p>
                </div>

            </div>

        </div>
    </section>

    <!-- 6. Cerita Mitra Testimonials -->
    <section id="mitra" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-sky-700 bg-sky-100 px-3.5 py-1 rounded-full inline-block">
                    CERITA MITRA SUKSES
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Ini Dia Cerita Mitra yang Sukses Tambah Penghasilan
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-sky-600 text-white font-bold flex items-center justify-center text-xs">
                            SR
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block">Siti Rahmawati</span>
                            <span class="text-xs text-slate-500">Ibu Rumah Tangga - Bandung</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Alhamdulillah sejak join jadi mitra kontributor KAMERAKITA AI, saya bisa nambah uang belanja tanpa mengganggu waktu urus anak. Tiap hari Rabu selalu tepat waktu transferannya!"
                    </p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs">
                            AP
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block">Agus Prasetyo</span>
                            <span class="text-xs text-slate-500">Mahasiswa - Surabaya</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Sistem rekap durasinya sangat transparan di QC Room. Saya bisa pantau mana laporan yang approved dan berapa jam yang cair. Mantap banget!"
                    </p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xs">
                            DR
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block">Dedi Rahmat</span>
                            <span class="text-xs text-slate-500">Pekerja Freelance - Jakarta</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Proses verifikasinya adil dan jelas. Begitu bukti disetujui, pembayarannya langsung masuk rekap mingguan. Sangat merekomendasikan!"
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- 7. FAQ Section -->
    <section id="faq" class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center space-y-3 mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-sky-700 bg-sky-100 px-3.5 py-1 rounded-full inline-block">
                    FAQ
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Pertanyaan yang Sering Diajukan
                </h2>
            </div>

            <div class="space-y-4" x-data="{ activeFaq: null }">
                
                <div class="bg-white border border-slate-200 rounded-2xl p-5 cursor-pointer" @click="activeFaq = (activeFaq === 1 ? null : 1)">
                    <div class="flex justify-between items-center text-sm font-extrabold text-slate-900">
                        <span>Apakah pendaftaran mitra dipungut biaya?</span>
                        <span x-text="activeFaq === 1 ? '−' : '+'" class="text-lg font-bold text-sky-600"></span>
                    </div>
                    <div x-show="activeFaq === 1" x-collapse class="pt-3 text-xs text-slate-600 leading-relaxed">
                        Sama sekali tidak. Pendaftaran Mitra Kontributor di KAMERAKITA AI 100% GRATIS tanpa modal awal apa pun.
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5 cursor-pointer" @click="activeFaq = (activeFaq === 2 ? null : 2)">
                    <div class="flex justify-between items-center text-sm font-extrabold text-slate-900">
                        <span>Kapan komisi hasil rekap durasi akan dicairkan?</span>
                        <span x-text="activeFaq === 2 ? '−' : '+'" class="text-lg font-bold text-sky-600"></span>
                    </div>
                    <div x-show="activeFaq === 2" x-collapse class="pt-3 text-xs text-slate-600 leading-relaxed">
                        Pencairan komisi dilakukan secara berkala setiap minggunya pada **hari Rabu** secara otomatis ke rekening bank yang terdaftar.
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- 8. Final Banner -->
    <section class="py-16 bg-hero-solid text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                Gimana, Siap Wujudkan Mimpi Buat Jadi Andalan Keluarga?
            </h2>

            <p class="text-sky-200 text-sm sm:text-base max-w-2xl mx-auto">
                Bergabunglah bersama ribuan mitra kontributor terverifikasi di seluruh Indonesia sekarang juga.
            </p>

            <div class="pt-2">
                <a href="{{ route('get-started') }}" class="btn-evermos-white px-9 py-4 text-xs font-black uppercase tracking-wider shadow-xl inline-block">
                    Gabung Sekarang →
                </a>
            </div>

        </div>
    </section>

    <!-- 9. Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/Logo.webp') }}" alt="Logo" class="w-7 h-7 rounded-lg object-contain">
                    <span class="text-sm font-black text-white font-mono">KAMERAKITA AI</span>
                </div>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Platform Rekam Video & Datasets Terpercaya No. 1 di Indonesia untuk Mitra & Enterprise Computer Vision.
                </p>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px]">Layanan Mitra</span>
                <ul class="space-y-2">
                    <li><a href="#keunggulan" class="hover:text-sky-400 transition">Keunggulan Mitra</a></li>
                    <li><a href="#kalkulator" class="hover:text-sky-400 transition">Kalkulator Komisi</a></li>
                    <li><a href="#cara-kerja" class="hover:text-sky-400 transition">Cara Kerja</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px]">Akses Akun</span>
                <ul class="space-y-2">
                    <li><a href="{{ route('login') }}" class="hover:text-sky-400 transition">Masuk / Daftar</a></li>
                    <li><a href="{{ route('get-started') }}" class="hover:text-sky-400 transition">Daftar Kontributor Baru</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-sky-400 transition">Dashboard Admin QC</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px]">Perusahaan</span>
                <p class="text-slate-500">Domain Resmi: <span class="text-sky-400 font-bold font-mono">kamerakitaid.site</span></p>
                <p class="text-slate-500">PT KAMERAKITA AI Indonesia</p>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-800 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-[11px] text-slate-500">
            <p>© 2026 KAMERAKITA AI. All rights reserved.</p>
            <div class="flex items-center gap-6 font-medium">
                <a href="#" class="hover:text-slate-300">Kebijakan Privasi</a>
                <a href="#" class="hover:text-slate-300">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

</body>
</html>
