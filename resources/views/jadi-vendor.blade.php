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

    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="antialiased selection:bg-sky-500 selection:text-white">
    @php
        $whatsappJoinUrl = 'https://wa.me/6287886272647?text='.rawurlencode('Halo Admin KameraKita AI, saya ingin gabung grup WA pendaftaran kontributor.');
    @endphp

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
                <a href="#mitra" class="hover:text-sky-600 transition">Testimony</a>
                <a href="#faq" class="hover:text-sky-600 transition">FAQ</a>
            </nav>

            <!-- Action Button -->
            <div class="flex items-center gap-3">
                <a href="{{ route('jadi-vendor') }}" class="hidden sm:inline-flex px-5 py-2 border-2 border-sky-500 text-sky-600 hover:bg-sky-50 hover:text-sky-700 font-bold rounded-xl text-sm transition shadow-sm">
                    Jadi Vendor
                </a>
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
<main id="main" class="main-stack">
    <section class="hero" id="top" aria-labelledby="hero-title">
      <div class="hero-copy" data-aos="fade-right">
        <p class="chip">KameraKita AI Partner Program</p>
        <h1 id="hero-title">Bangun Agensi AI Anda<br><span>Profit Tanpa Batas</span></h1>
        <p class="hero-description">Jadilah Official Vendor KameraKita. Kami siapkan kontrak AI global,<br class="desktop-break"> Anda cukup kelola tim dan nikmati marginnya</p>
        <div class="hero-actions">
          <a class="button button-primary" href="#contact">Gabung Jadi Partner</a>
          <a class="button button-outline" href="#features">Lihat Cara Kerjanya</a>
        </div>
      </div>

      <div class="hero-visual" data-aos="fade-left" data-aos-delay="200" aria-label="Visual pendapatan partner dan proyek perekaman aktivitas">
        <img class="hero-glow" src="{{ asset('vendor-assets/') }}/kamerakita/hero-glow.svg" alt="">
        <img class="hero-chart" src="{{ asset('vendor-assets/') }}/kamerakita/hero-chart.png" alt="Grafik tren profit bulanan">
        <div class="pov-card">
          <div class="pov-image">
            <img src="{{ asset('vendor-assets/') }}/kamerakita/hero-pov.png" alt="Perekaman aktivitas melipat pakaian dari sudut pandang orang pertama">
          </div>
          <span class="feed-label">CAMERA_FEED_03 // SPATIAL</span>
          <span class="depth-label"><small>DEPTH MAP</small><b>LIDAR_POINTCLOUD_ALIGN</b></span>
        </div>
        <img class="hero-wallet" src="{{ asset('vendor-assets/') }}/kamerakita/hero-wallet.png" alt="Dompet berisi uang rupiah">
        <img class="hero-headstrap" src="{{ asset('vendor-assets/') }}/kamerakita/hero-headstrap.png" alt="Kamera headstrap untuk merekam aktivitas">
      </div>
    </section>

    <section class="project-section surface-section" id="features" aria-labelledby="project-title">
      <div class="project-heading" data-aos="fade-up">
        <div>
          <p class="chip">PROYEK DATA AI</p>
          <h2 id="project-title">Membantu AI Memahami Dunia Nyata</h2>
        </div>
        <p>Tim Anda membantu mengumpulkan video aktivitas<br class="desktop-break"> sehari-hari dari sudut pandang orang pertama untuk<br class="desktop-break"> kebutuhan pengembangan AI.</p>
      </div>

      <div class="project-grid">
        <article class="project-card" data-aos="fade-up" data-aos-delay="100">
          <img src="{{ asset('vendor-assets/') }}/kamerakita/project-recording.png" alt="Ilustrasi perekaman aktivitas harian">
          <div class="project-copy">
            <h3>Rekam Aktivitas Harian</h3>
            <p>Worker memakai smartphone dan headstrap untuk merekam kegiatan seperti beres-beres, memasak, atau aktivitas rumah lainnya.</p>
          </div>
        </article>
        <article class="project-card" data-aos="fade-up" data-aos-delay="200">
          <img src="{{ asset('vendor-assets/') }}/kamerakita/project-guideline.png" alt="Ilustrasi alur panduan proyek">
          <div class="project-copy">
            <h3>Ikuti Panduan Proyek</h3>
            <p>Setiap project punya arahan aktivitas, durasi, dan standar rekaman yang harus diikuti.</p>
          </div>
        </article>
        <article class="project-card project-card-last" data-aos="fade-up" data-aos-delay="300">
          <img src="{{ asset('vendor-assets/') }}/kamerakita/project-verification.png" alt="Ilustrasi pengiriman data untuk verifikasi">
          <div class="project-copy">
            <h3>Kirim untuk Diverifikasi</h3>
            <p>Video yang selesai dikirim untuk dicek. Jam yang lolos validasi kemudian dihitung untuk pembayaran.</p>
          </div>
        </article>
      </div>
    </section>

    <section class="profit-section" id="pricing" aria-labelledby="profit-title">
      <div class="profit-heading" data-aos="fade-up">
        <p class="chip">SIMULASI PERTUMBUHAN</p>
        <h2 id="profit-title">Simulasikan Potensi Profit dari Skala Operasional Anda</h2>
      </div>

      <div class="profit-grid">
        <article class="tier-card" data-aos="zoom-in-up" data-aos-delay="100">
          <div class="tier-name"><img src="{{ asset('vendor-assets/') }}/kamerakita/icon-tier-1.svg" alt=""><span>Tier 1: Minimal</span></div>
          <p class="worker-count"><strong>9</strong><span>orang worker</span></p>
          <div class="tier-profit">
            <span>Estimasi profit/bulan</span>
            <strong>Rp 7.500.000</strong>
            <small>Volume: 500 Jam</small>
          </div>
        </article>

        <article class="tier-card" data-aos="zoom-in-up" data-aos-delay="200">
          <div class="tier-name"><img src="{{ asset('vendor-assets/') }}/kamerakita/icon-tier-2.svg" alt=""><span>Tier 2: Menengah</span></div>
          <p class="worker-count"><strong>20</strong><span>orang worker</span></p>
          <div class="tier-profit">
            <span>Estimasi profit/bulan</span>
            <strong>Rp 18.000.000</strong>
            <small>Volume: 1.200 Jam</small>
          </div>
        </article>

        <article class="tier-card tier-large" data-aos="zoom-in-up" data-aos-delay="300">
          <img class="profit-art" src="{{ asset('vendor-assets/') }}/kamerakita/profit-wallet.png" alt="Dompet besar berisi uang rupiah">
          <div class="tier-name"><img src="{{ asset('vendor-assets/') }}/kamerakita/icon-tier-3.svg" alt=""><span>Tier 3: Agensi Skala Besar</span></div>
          <p class="worker-count"><strong>50+</strong><span>orang worker</span></p>
          <div class="tier-profit">
            <span>Estimasi profit/bulan</span>
            <strong class="blue-profit">Rp 45.000.000+</strong>
            <small>Volume: 3.000+ jam</small>
          </div>
          <a class="button tier-button" href="#contact">Gabung Jadi Partner</a>
        </article>
      </div>
    </section>

    <section class="how-section" id="faq" aria-labelledby="how-title">
      <div class="how-heading" data-aos="fade-right">
        <div>
          <p class="chip">CARA KERJA</p>
          <h2 id="how-title">Mulai Jadi Partner dalam 3 Langkah</h2>
        </div>
        <a class="button how-button" href="#contact">Gabung Jadi Partner</a>
      </div>

      <div class="steps-grid">
        <article class="step" data-aos="fade-up" data-aos-delay="100">
          <span class="step-number">1</span>
          <h3>JOIN</h3>
          <p>Daftarkan agency Anda. Siapkan worker, smartphone, dan headstrap.</p>
        </article>
        <article class="step" data-aos="fade-up" data-aos-delay="200">
          <span class="step-number">2</span>
          <h3>OPERATE</h3>
          <p>Jalankan project sesuai guideline KameraKita</p>
        </article>
        <article class="step" data-aos="fade-up" data-aos-delay="300">
          <span class="step-number">3</span>
          <h3>EARN</h3>
          <p>Terima pembayaran dari jam data yang berhasil divalidasi.</p>
        </article>
      </div>
    </section>

    <section class="final-cta" id="contact" aria-labelledby="cta-title">
      <img class="cta-art" data-aos="fade-right" src="{{ asset('vendor-assets/') }}/kamerakita/final-cta-art.png" alt="Ilustrasi AI dan aliran koin">
      <div class="cta-copy" data-aos="fade-left" data-aos-delay="200">
        <h2 id="cta-title">Siap buka peluang bisnis baru dari proyek<br class="desktop-break"> AI global?</h2>
        <p>Masuk lebih awal, bangun operasional, dan nikmati<br class="desktop-break"> potensi margin dari setiap jam yang tervalidasi.</p>
        <a class="button button-yellow" href="mailto:partnership@kamerakita.com">Gabung Jadi Partner</a>
      </div>
    </section>
  </main>
<script src="{{ asset('js/vendor.js') }}"></script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 1000,
    easing: 'ease-out-cubic',
    once: true,
    offset: 50
  });
</script>
</body>
</html>