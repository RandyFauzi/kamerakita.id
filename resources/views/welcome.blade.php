<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KAMERAKITA AI — Platform Kerja Mitra & Rekap Laporan Video Terpercaya</title>
    
    <!-- Meta Description -->
    <meta name="description" content="Gabung jadi Mitra Kontributor di KAMERAKITA AI! Dapatkan penghasilan tambahan rutin setiap minggu dengan merekam video & rekap laporan. Daftar Gratis, Tanpa Modal, Transfer Tiap Rabu!">
    <link rel="icon" href="{{ asset('images/Logo.webp') }}" type="image/webp">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        evermos: {
                            green: '#03ac0e',
                            greenDark: '#028a0b',
                            greenLight: '#e6f9e8',
                            greenBorder: '#bdf2c3',
                            slateBg: '#fafafa',
                            cardBg: '#ffffff',
                            darkText: '#1a1d1f',
                            mutedText: '#6f767e',
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
            color: #1a1d1f;
            -webkit-font-smoothing: antialiased;
        }

        .btn-evermos-primary {
            background-color: #03ac0e;
            color: #ffffff;
            font-weight: 800;
            border-radius: 16px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(3, 172, 14, 0.25);
        }
        .btn-evermos-primary:hover {
            background-color: #028a0b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(3, 172, 14, 0.35);
        }

        .btn-evermos-secondary {
            background-color: #f4f5f6;
            color: #1a1d1f;
            font-weight: 700;
            border-radius: 16px;
            border: 1px solid #e6e8ec;
            transition: all 0.2s ease;
        }
        .btn-evermos-secondary:hover {
            background-color: #e6e8ec;
            color: #000000;
        }

        .evermos-card {
            background: #ffffff;
            border: 1px solid #f0f2f5;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease;
        }
        .evermos-card:hover {
            border-color: #bdf2c3;
            box-shadow: 0 12px 32px rgba(3, 172, 14, 0.08);
            transform: translateY(-3px);
        }

        .badge-evermos-green {
            background-color: #e6f9e8;
            color: #03ac0e;
            border: 1px solid #bdf2c3;
        }

        .gradient-hero-bg {
            background: linear-gradient(180deg, #f4fbf5 0%, #ffffff 100%);
        }

        .gradient-accent-bg {
            background: linear-gradient(135deg, #03ac0e 0%, #027a0a 100%);
        }
    </style>
</head>
<body class="antialiased selection:bg-emerald-500 selection:text-white">

    <!-- 1. Top Announcement Bar (Evermos Style) -->
    <div class="bg-[#028a0b] text-white text-xs py-2.5 px-4 text-center font-medium">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 flex-wrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white text-[#028a0b]">
                🎉 PENDAFTARAN GRATIS
            </span>
            <span>Daftar Mitra Kontributor KAMERAKITA AI Sekarang • Tanpa Modal • Transfer Mingguan Tiap Hari Rabu!</span>
            <a href="{{ route('register') }}" class="font-bold underline underline-offset-4 hover:text-emerald-100 transition-colors ml-1 inline-flex items-center gap-1">
                Daftar Sekarang →
            </a>
        </div>
    </div>

    <!-- 2. Navbar (Clean, Friendly, & Humanistic) -->
    <header x-data="{ mobileOpen: false, scrolled: false }"
            @scroll.window="scrolled = (window.pageYOffset > 15)"
            :class="scrolled ? 'bg-white/95 backdrop-blur-md border-b border-slate-150 shadow-sm py-3.5' : 'bg-transparent py-5'"
            class="sticky top-0 z-50 transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white border border-emerald-200 p-1 flex items-center justify-center shadow-xs overflow-hidden">
                    <img src="{{ asset('images/Logo.webp') }}" alt="KAMERAKITA AI Logo" class="w-full h-full object-contain rounded-xl">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-tight text-slate-900 leading-none">
                        KAMERAKITA <span class="text-emerald-600">AI</span>
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Platform Mitra Kontributor</span>
                </div>
            </a>

            <!-- Menu Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#keunggulan" class="hover:text-emerald-600 transition-colors">Keunggulan</a>
                <a href="#kalkulator" class="hover:text-emerald-600 transition-colors">Kalkulator Penghasilan</a>
                <a href="#cara-kerja" class="hover:text-emerald-600 transition-colors">Cara Kerja</a>
                <a href="#mitra" class="hover:text-emerald-600 transition-colors">Cerita Mitra</a>
                <a href="#faq" class="hover:text-emerald-600 transition-colors">FAQ</a>
            </nav>

            <!-- Action CTAs -->
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-evermos-primary px-6 py-3 text-xs uppercase tracking-wider inline-flex items-center gap-2">
                        Masuk Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 text-xs font-bold text-slate-700 hover:text-emerald-600 transition">
                        Masuk / Login
                    </a>
                    <a href="{{ route('register') }}" class="btn-evermos-primary px-6 py-3 text-xs uppercase tracking-wider inline-flex items-center gap-2">
                        Daftar Kontributor Gratis
                    </a>
                @endauth
            </div>

            <!-- Mobile Toggle -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-slate-700 hover:text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileOpen" x-collapse class="md:hidden bg-white border-b border-slate-200 px-4 py-4 space-y-3">
            <a href="#keunggulan" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Keunggulan</a>
            <a href="#kalkulator" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Kalkulator Penghasilan</a>
            <a href="#cara-kerja" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Cara Kerja</a>
            <a href="#mitra" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Cerita Mitra</a>
            <a href="#faq" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">FAQ</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center btn-evermos-primary py-3 text-xs uppercase">Masuk Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 text-xs font-bold text-slate-700 border border-slate-200 rounded-xl">Masuk / Login</a>
                    <a href="{{ route('register') }}" class="w-full text-center btn-evermos-primary py-3 text-xs uppercase">Daftar Kontributor Gratis</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 3. Hero Section (Warm, Empathetic, & Humanistic) -->
    <section class="gradient-hero-bg pt-8 pb-16 lg:pt-16 lg:pb-24 border-b border-emerald-100/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Hero Text -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    
                    <!-- Friendly Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full badge-evermos-green text-xs font-bold tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        SAATNYA TAMBAH PENGHASILAN TANPA MODAL DARI RUMAH
                    </div>

                    <!-- Warm Main Headline (H1) -->
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-[1.2]">
                        Saatnya Jadi <span class="text-emerald-600 underline decoration-emerald-300 decoration-wavy decoration-2">Andalan Keluarga</span> Lewat Penghasilan Tambahan
                    </h1>

                    <!-- Subheadline -->
                    <p class="text-sm sm:text-base text-slate-600 font-normal leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Bantu laboratorium AI mengembangkan teknologi Computer Vision sambil menghasilkan uang tambahan dari rekam video. **Daftar Gratis**, tanpa perlu modal, rekap durasi transparan, dan **pencairan langsung dikirim setiap hari Rabu!**
                    </p>

                    <!-- Dual CTAs -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                        <a href="{{ route('register') }}" class="btn-evermos-primary w-full sm:w-auto px-8 py-4 text-sm font-extrabold text-center inline-flex items-center justify-center gap-2">
                            <span>Daftar Kontributor Gratis</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="#kalkulator" class="btn-evermos-secondary w-full sm:w-auto px-7 py-4 text-sm font-bold text-center">
                            Hitung Target Penghasilan ibu/Bapak →
                        </a>
                    </div>

                    <!-- Trust Signals Bar -->
                    <div class="pt-6 border-t border-slate-200/80 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs font-semibold text-slate-600">
                        <div class="flex items-center gap-1.5 text-amber-500">
                            <span class="font-bold">★ 4.9/5</span>
                            <span class="text-slate-500">(1,200+ Mitra Aktif)</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Pencairan Mingguan Tiap Rabu</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>100% Bebas Biaya</span>
                        </div>
                    </div>

                </div>

                <!-- Right Hero Card (Realistic Partner Snapshot & Payout Card) -->
                <div class="lg:col-span-5">
                    <div class="evermos-card p-6 relative space-y-6">
                        
                        <!-- Top Partner Card Profile Header -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 font-extrabold flex items-center justify-center text-lg border border-emerald-300">
                                    MH
                                </div>
                                <div>
                                    <span class="text-sm font-extrabold text-slate-900 block">Muhammad (Mitra Aktif)</span>
                                    <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ID Mitra: KMK-018
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold font-mono px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                TERVERIFIKASI
                            </span>
                        </div>

                        <!-- Live Payout Badge Display -->
                        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl p-5 text-white shadow-md space-y-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-100 block">REKAP KOMISI MINGGUAN DISENTUJUI</span>
                            <div class="flex items-baseline justify-between">
                                <span class="text-3xl font-black font-mono">Rp 1.850.000</span>
                                <span class="text-xs font-bold bg-white/20 px-2.5 py-1 rounded-lg">Status: PAID</span>
                            </div>
                            <p class="text-[11px] text-emerald-100 pt-1 border-t border-white/20">
                                Otomatis ditransfer ke Rekening Bank setiap hari Rabu
                            </p>
                        </div>

                        <!-- Dynamic QC Verification Log Preview -->
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-2 text-xs">
                            <div class="flex justify-between items-center text-slate-700">
                                <span class="font-bold">Laporan Kerja Video Terakhir:</span>
                                <span class="text-emerald-700 font-bold font-mono">Approved (61m)</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-500 pt-1">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Bukti Screenshot Durasi Aplikasi Divalidasi Presisi</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 4. Keunggulan Section ("Kenapa Harus Jadi Reseller/Mitra Evermos Style") -->
    <section id="keunggulan" class="py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700 bg-emerald-100 px-3.5 py-1 rounded-full inline-block">
                    KEUNTUNGAN MITRA
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Kenapa Sih Kamu Harus Bergabung Jadi Mitra KAMERAKITA AI?
                </h2>
                <p class="text-slate-600 text-sm sm:text-base">
                    Kemudahan menghasilkan uang tambahan dari rumah dengan dukungan penuh dari tim verifikator dan sistem pembayaran transparan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1 -->
                <div class="evermos-card p-6 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl">
                        💰
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Tanpa Modal & Bebas Biaya</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pendaftaran 100% gratis tanpa modal sepeser pun. Cukup gunakan HP Anda dan mulai rekam video sesuai instruksi panduan.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="evermos-card p-6 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
                        📅
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Transfer Rutin Tiap Rabu</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Rekap durasi hasil QC diselesaikan secara akurat, dan komisi pendapatan langsung ditransfer ke bank Anda setiap minggunya.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="evermos-card p-6 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xl">
                        🔍
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Sistem QC Transparan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengecekan bukti durasi aplikasi dilakukan side-by-side. Anda dapat melihat status laporan secara real-time di dashboard.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="evermos-card p-6 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xl">
                        🏠
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Kerja dari Rumah & Fleksibel</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Atur jam kerja Anda sendiri tanpa terikat kantor. Cocok untuk ibu rumah tangga, mahasiswa, maupun pekerja lepas.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- 5. Interactive Income Calculator ("Hitung Target Komisi Ibu/Bapak") -->
    <section id="kalkulator" class="py-20 bg-white" x-data="{
        hoursPerDay: 2,
        daysPerWeek: 5,
        ratePerHour: 45000,
        get weeklyEarnings() {
            return this.hoursPerDay * this.daysPerWeek * this.ratePerHour;
        },
        get monthlyEarnings() {
            return this.weeklyEarnings * 4;
        },
        formatRupiah(number) {
            return 'Rp ' + number.toLocaleString('id-ID');
        }
    }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="evermos-card p-8 sm:p-12 border border-emerald-200 shadow-xl relative overflow-hidden">
                
                <div class="text-center space-y-3 mb-10">
                    <span class="text-xs font-bold uppercase tracking-widest text-emerald-700 bg-emerald-100 px-3.5 py-1 rounded-full inline-block">
                        KALKULATOR ESTIMASI PENGHASILAN
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Hitung Target Komisi Ibu/Bapak Biar Makin Yakin Berikhtiar
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm">
                        Geser simulasi jam kerja di bawah untuk melihat potensi komisi mingguan dan bulanan yang bisa Anda dapatkan!
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center pt-4">
                    
                    <!-- Sliders Input -->
                    <div class="space-y-6 bg-slate-50 p-6 rounded-2xl border border-slate-200">
                        
                        <!-- Slider 1: Jam per hari -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-slate-800">
                                <span>Jam Kerja per Hari:</span>
                                <span class="text-emerald-700 font-mono font-extrabold text-sm" x-text="hoursPerDay + ' Jam'"></span>
                            </div>
                            <input type="range" min="1" max="8" x-model="hoursPerDay" class="w-full accent-emerald-600 cursor-pointer">
                            <div class="flex justify-between text-[10px] text-slate-400 font-bold">
                                <span>1 Jam</span>
                                <span>4 Jam</span>
                                <span>8 Jam</span>
                            </div>
                        </div>

                        <!-- Slider 2: Hari per minggu -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-slate-800">
                                <span>Hari Kerja per Minggu:</span>
                                <span class="text-emerald-700 font-mono font-extrabold text-sm" x-text="daysPerWeek + ' Hari'"></span>
                            </div>
                            <input type="range" min="1" max="7" x-model="daysPerWeek" class="w-full accent-emerald-600 cursor-pointer">
                            <div class="flex justify-between text-[10px] text-slate-400 font-bold">
                                <span>1 Hari</span>
                                <span>5 Hari</span>
                                <span>7 Hari</span>
                            </div>
                        </div>

                    </div>

                    <!-- Earnings Output Box -->
                    <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-8 rounded-2xl shadow-lg space-y-6 text-center lg:text-left">
                        <div>
                            <span class="text-xs uppercase font-semibold text-emerald-100 block">ESTIMASI PENGHASILAN MINGGUAN (DITRANSFER TIAP RABU)</span>
                            <span class="text-3xl sm:text-4xl font-black font-mono mt-1 block" x-text="formatRupiah(weeklyEarnings)"></span>
                        </div>
                        <div class="pt-4 border-t border-white/20">
                            <span class="text-xs uppercase font-semibold text-emerald-100 block">ESTIMASI TOTAL BULANAN</span>
                            <span class="text-2xl font-extrabold font-mono mt-1 block text-emerald-200" x-text="formatRupiah(monthlyEarnings)"></span>
                        </div>
                        <a href="{{ route('register') }}" class="btn-evermos-primary w-full block py-3 text-xs uppercase text-center bg-white text-emerald-800 hover:bg-slate-100 shadow-md">
                            Daftar Sekarang & Mulai Hasilkan Komisi →
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- 6. How It Works ("4 Langkah Mudah") -->
    <section id="cara-kerja" class="py-20 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700 bg-emerald-100 px-3.5 py-1 rounded-full inline-block">
                    CARA KERJA
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Gimana Cara Kerjanya? 4 Langkah Mudah
                </h2>
                <p class="text-slate-600 text-xs sm:text-sm">
                    Proses pendaftaran yang praktis dan transparan dari awal hingga komisi masuk ke rekening Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                <div class="evermos-card p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        1
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Daftar Akun Gratis</h4>
                    <p class="text-xs text-slate-500">Isi data pendaftaran mitra tanpa biaya sepeser pun.</p>
                </div>

                <div class="evermos-card p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        2
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Rekam Video</h4>
                    <p class="text-xs text-slate-500">Ikuti panduan mudah rekam video sesuai tugas yang diberikan.</p>
                </div>

                <div class="evermos-card p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        3
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Kirim Laporan & Bukti</h4>
                    <p class="text-xs text-slate-500">Upload screenshot durasi kerja aplikasi ke sistem QC Room.</p>
                </div>

                <div class="evermos-card p-6 text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-extrabold flex items-center justify-center mx-auto text-sm">
                        4
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Terima Transferan</h4>
                    <p class="text-xs text-slate-500">Hasil verifikasi disetujui dan komisi ditransfer otomatis tiap Rabu.</p>
                </div>

            </div>

        </div>
    </section>

    <!-- 7. Cerita Mitra (Testimonials & Human Proof) -->
    <section id="mitra" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700 bg-emerald-100 px-3.5 py-1 rounded-full inline-block">
                    CERITA MITRA SUKSES
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Ini Dia Cerita Mitra yang Sukses Tambah Penghasilan
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="evermos-card p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs">
                            SR
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block">Siti Rahmawati</span>
                            <span class="text-xs text-slate-500">Ibu Rumah Tangga - Bandung</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Alhamdulillah sejak join jadi mitra kontributor KAMERAKITA AI, saya bisa nambah uang belanja tanpa mengganggu waktu urus anak di rumah. Tiap hari Rabu selalu tepat waktu transferannya!"
                    </p>
                </div>

                <div class="evermos-card p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                            AP
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block">Agus Prasetyo</span>
                            <span class="text-xs text-slate-500">Mahasiswa - Surabaya</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Sistem rekap durasinya sangat transparan di QC Room. Saya bisa pantau mana laporan yang approved dan berapa jam yang cair. Mantap banget buat nambah jajan!"
                    </p>
                </div>

                <div class="evermos-card p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs">
                            DR
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block">Dedi Rahmat</span>
                            <span class="text-xs text-slate-500">Pekerja Freelance - Jakarta</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Proses verifikasinya adil dan jelas. Begitu bukti disetujui, pembayarannya langsung masuk rekap mingguan. Sangat merekomendasikan buat teman-teman!"
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- 8. FAQ Accordion (Pertanyaan Sering Diajukan) -->
    <section id="faq" class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center space-y-3 mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700 bg-emerald-100 px-3.5 py-1 rounded-full inline-block">
                    FAQ
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Pertanyaan yang Sering Diajukan
                </h2>
            </div>

            <div class="space-y-4" x-data="{ activeFaq: null }">
                
                <div class="evermos-card p-5 cursor-pointer" @click="activeFaq = (activeFaq === 1 ? null : 1)">
                    <div class="flex justify-between items-center text-sm font-extrabold text-slate-900">
                        <span>Apakah pendaftaran mitra dipungut biaya?</span>
                        <span x-text="activeFaq === 1 ? '−' : '+'" class="text-lg font-bold text-emerald-600"></span>
                    </div>
                    <div x-show="activeFaq === 1" x-collapse class="pt-3 text-xs text-slate-600 leading-relaxed">
                        Sama sekali tidak. Pendaftaran Mitra Kontributor di KAMERAKITA AI 100% GRATIS dan tanpa biaya modal awal apa pun.
                    </div>
                </div>

                <div class="evermos-card p-5 cursor-pointer" @click="activeFaq = (activeFaq === 2 ? null : 2)">
                    <div class="flex justify-between items-center text-sm font-extrabold text-slate-900">
                        <span>Kapan komisi hasil rekap durasi akan dicairkan?</span>
                        <span x-text="activeFaq === 2 ? '−' : '+'" class="text-lg font-bold text-emerald-600"></span>
                    </div>
                    <div x-show="activeFaq === 2" x-collapse class="pt-3 text-xs text-slate-600 leading-relaxed">
                        Pencairan komisi dilakukan secara berkala setiap minggunya pada **hari Rabu** secara otomatis langsung ke rekening bank yang terdaftar.
                    </div>
                </div>

                <div class="evermos-card p-5 cursor-pointer" @click="activeFaq = (activeFaq === 3 ? null : 3)">
                    <div class="flex justify-between items-center text-sm font-extrabold text-slate-900">
                        <span>Bagaimana sistem QC memverifikasi laporan saya?</span>
                        <span x-text="activeFaq === 3 ? '−' : '+'" class="text-lg font-bold text-emerald-600"></span>
                    </div>
                    <div x-show="activeFaq === 3" x-collapse class="pt-3 text-xs text-slate-600 leading-relaxed">
                        Tim verifikator mengecek screenshot bukti durasi dan kualitas video yang Anda unggah side-by-side. Durasi disetujui (*Approved*) akan langsung diakumulasikan ke saldo gaji mingguan Anda.
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- 9. Final Action Banner (Evermos Style) -->
    <section class="py-16 gradient-accent-bg text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                Gimana, Siap Wujudkan Mimpi Buat Jadi Andalan Keluarga?
            </h2>

            <p class="text-emerald-100 text-sm sm:text-base max-w-2xl mx-auto">
                Bergabunglah bersama ribuan mitra kontributor terverifikasi di seluruh Indonesia sekarang juga.
            </p>

            <div class="pt-2">
                <a href="{{ route('register') }}" class="btn-evermos-primary px-9 py-4 text-xs font-black uppercase tracking-wider bg-white text-[#028a0b] hover:bg-slate-100 shadow-xl inline-block">
                    Daftar Kontributor Gratis Sekarang →
                </a>
            </div>

        </div>
    </section>

    <!-- 10. Footer (Evermos Warm Footer Style) -->
    <footer class="bg-slate-900 text-slate-400 py-12 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/Logo.webp') }}" alt="Logo" class="w-7 h-7 rounded-lg object-contain">
                    <span class="text-sm font-black text-white">KAMERAKITA AI</span>
                </div>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Platform Rekam Video & Datasets Terpercaya No. 1 di Indonesia untuk Mitra & Enterprise Computer Vision.
                </p>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px]">Layanan Mitra</span>
                <ul class="space-y-2">
                    <li><a href="#keunggulan" class="hover:text-emerald-400 transition">Keunggulan Mitra</a></li>
                    <li><a href="#kalkulator" class="hover:text-emerald-400 transition">Kalkulator Komisi</a></li>
                    <li><a href="#cara-kerja" class="hover:text-emerald-400 transition">Panduan Cara Kerja</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px]">Akses Akun</span>
                <ul class="space-y-2">
                    <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition">Masuk / Login Mitra</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-emerald-400 transition">Daftar Kontributor Baru</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-emerald-400 transition">Dashboard Admin QC</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px]">Perusahaan</span>
                <p class="text-slate-500">Domain Resmi: <span class="text-emerald-400 font-bold font-mono">kamerakitaid.site</span></p>
                <p class="text-slate-500">PT KAMERAKITA AI Indonesia</p>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-800 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-[11px] text-slate-500">
            <p>© 2026 KAMERAKITA AI. All rights reserved.</p>
            <div class="flex items-center gap-6 font-medium">
                <a href="#" class="hover:text-slate-300">Kebijakan Privasi</a>
                <a href="#" class="hover:text-slate-300">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-slate-300">Pusat Bantuan</a>
            </div>
        </div>
    </footer>

</body>
</html>
