<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KAMERAKITA AI — Platform Rekam Video & Datasets Terpercaya</title>
    
    <!-- Meta Description -->
    <meta name="description" content="Gabung jadi mitra kontributor di KAMERAKITA AI! Dapatkan penghasilan tambahan rutin lewat rekam video. Tanpa modal, rekap transparan, transfer mingguan!">
    <link rel="icon" href="{{ asset('vendor-assets/kamerakita/logo-mark.svg') }}" type="image/webp">

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

    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}?v={{ time() }}">

    <style>
    /* Strict Scroll-Reveal Animation Engine (Guaranteed Execution & Anti-Cache) */
    .animate-fade-up, .animate-fade-down, .animate-fade-left, .animate-fade-right, 
    .animate-zoom-in, .animate-zoom-out, .animate-flip-up, .animate-scale-up {
        opacity: 0 !important;
        pointer-events: none !important;
        transition: opacity 850ms cubic-bezier(0.16, 1, 0.3, 1), 
                    transform 850ms cubic-bezier(0.16, 1, 0.3, 1), 
                    filter 850ms ease !important;
        will-change: opacity, transform;
    }

    .animate-fade-up { transform: translate3d(0, 65px, 0) !important; }
    .animate-fade-down { transform: translate3d(0, -65px, 0) !important; }
    .animate-fade-left { transform: translate3d(65px, 0, 0) !important; }
    .animate-fade-right { transform: translate3d(-65px, 0, 0) !important; }
    .animate-zoom-in { transform: scale(0.82) translate3d(0, 30px, 0) !important; filter: blur(8px) !important; }
    .animate-zoom-out { transform: scale(1.15) translate3d(0, -30px, 0) !important; filter: blur(8px) !important; }
    .animate-scale-up { transform: scale(0.88) !important; filter: blur(6px) !important; }
    .animate-flip-up { transform: perspective(1000px) rotateX(-50deg) translate3d(0, 40px, 0) !important; transform-origin: bottom center; }

    .is-visible {
        opacity: 1 !important;
        pointer-events: auto !important;
        filter: blur(0) !important;
    }
    .is-visible:not(.hero-chart):not(.pov-card):not(.hero-wallet):not(.hero-headstrap) {
        transform: translate3d(0, 0, 0) scale(1) rotateX(0) !important;
    }

    /* Floating keyframes control */
    .hero-chart, .pov-card, .hero-wallet, .hero-headstrap {
        animation-play-state: paused !important;
    }
    .is-visible.hero-chart,
    .is-visible.pov-card,
    .is-visible.hero-wallet,
    .is-visible.hero-headstrap {
        animation-play-state: running !important;
    }
    </style>
</head>
<body class="antialiased selection:bg-sky-500 selection:text-white">
    @php
        $whatsappJoinUrl = 'https://wa.me/6287886272647?text='.rawurlencode('Halo Admin KameraKita AI, saya ingin gabung grup WA pendaftaran kontributor.');
        $whatsappVendorUrl = 'https://wa.me/6287886272647?text='.rawurlencode('Halo Admin KameraKita AI, saya tertarik mendaftar program Vendor/Agensi Partner. Boleh minta info lebih lanjut?');
    @endphp

    <!-- 1. Top Navbar (Clean White background, exact Evermos layout) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl  flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('vendor-assets/kamerakita/logo-mark.svg') }}" alt="KAMERAKITA AI Logo" class="w-full h-full object-contain rounded-lg">
                </div>
                <span class="text-xl font-black tracking-[-0.03em] text-slate-900">
                    KameraKita<span class="ml-0.5 text-[#0284c7]">AI</span>
                </span>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-700">
                <a href="/#keunggulan" class="hover:text-sky-600 transition flex items-center gap-1">
                    Keunggulan <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                <a href="/#kalkulator" class="hover:text-sky-600 transition flex items-center gap-1">
                    Kalkulator <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                <a href="/#cara-kerja" class="hover:text-sky-600 transition">Cara Kerja</a>
                <a href="/#mitra" class="hover:text-sky-600 transition">Testimony</a>
                <a href="/#faq" class="hover:text-sky-600 transition">FAQ</a>
            </nav>

            <!-- Action Button -->
            <div class="flex items-center gap-3">
                <x-language-switcher />
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
      <div class="hero-copy">
        <p class="chip animate-fade-down"><span class="chip-dot"></span> KameraKita AI Partner Program</p>
        <h1 id="hero-title" class="animate-fade-up" data-delay="100">{!! __('vendor_landing.hero.title') !!}</h1>
        <p class="hero-description animate-fade-up" data-delay="200">{!! __('vendor_landing.hero.subtitle') !!}</p>
        <div class="hero-actions animate-fade-up" data-delay="300">
          <a class="button button-primary" href="{{ $whatsappVendorUrl }}" target="_blank">Gabung Jadi Partner</a>
          <a class="button button-outline" href="#features">Lihat Cara Kerjanya</a>
        </div>
      </div>

      <div class="hero-visual" aria-label="Visual pendapatan partner dan proyek perekaman aktivitas">
        <img class="hero-glow animate-fade-down" src="{{ asset('vendor-assets/') }}/kamerakita/hero-glow.svg" alt="">
        <img class="hero-chart animate-zoom-in" data-delay="150" src="{{ asset('vendor-assets/') }}/kamerakita/hero-chart.png" alt="Grafik tren profit bulanan">
        
        <div class="pov-card animate-fade-left" data-delay="250">
          <div class="pov-image">
            <img src="{{ asset('vendor-assets/') }}/kamerakita/hero-pov.png" alt="Perekaman aktivitas melipat pakaian dari sudut pandang orang pertama">
            <div class="pov-scanner"></div>
            <div class="rec-badge"><span class="rec-dot"></span>REC • LIVE</div>
            <span class="feed-label">CAMERA_FEED_03 // SPATIAL</span>
            <span class="depth-label"><small>DEPTH MAP</small><b>LIDAR_POINTCLOUD_ALIGN</b></span>
          </div>
        </div>
        
        <img class="hero-wallet animate-fade-down" data-delay="350" src="{{ asset('vendor-assets/') }}/kamerakita/hero-wallet.png" alt="Dompet berisi uang rupiah">
        <img class="hero-headstrap animate-fade-up" data-delay="450" src="{{ asset('vendor-assets/') }}/kamerakita/hero-headstrap.png" alt="Kamera headstrap untuk merekam aktivitas">
      </div>
    </section>

    <section class="project-section surface-section" id="features" aria-labelledby="project-title">
      <div class="project-heading">
        <div class="animate-fade-right">
          <p class="chip"><span class="chip-dot"></span> PROYEK DATA AI</p>
          <h2 id="project-title">Membantu AI Memahami Dunia Nyata</h2>
        </div>
        <p class="animate-fade-left" data-delay="150">Tim Anda membantu mengumpulkan video aktivitas<br class="desktop-break"> sehari-hari dari sudut pandang orang pertama untuk<br class="desktop-break"> kebutuhan pengembangan AI.</p>
      </div>

      <div class="project-grid">
        <article class="project-card animate-fade-up" data-delay="100">
          <img src="{{ asset('vendor-assets/') }}/kamerakita/project-recording.png" alt="Ilustrasi perekaman aktivitas harian">
          <div class="project-copy">
            <h3>Rekam Aktivitas Harian</h3>
            <p>Worker memakai smartphone dan headstrap untuk merekam kegiatan seperti beres-beres, memasak, atau aktivitas rumah lainnya.</p>
          </div>
        </article>
        <article class="project-card animate-fade-up" data-delay="250">
          <img src="{{ asset('vendor-assets/') }}/kamerakita/project-guideline.png" alt="Ilustrasi alur panduan proyek">
          <div class="project-copy">
            <h3>Ikuti Panduan Proyek</h3>
            <p>Setiap project punya arahan aktivitas, durasi, dan standar rekaman yang harus diikuti.</p>
          </div>
        </article>
        <article class="project-card project-card-last animate-fade-up" data-delay="400">
          <img src="{{ asset('vendor-assets/') }}/kamerakita/project-verification.png" alt="Ilustrasi pengiriman data untuk verifikasi">
          <div class="project-copy">
            <h3>Kirim untuk Diverifikasi</h3>
            <p>Video yang selesai dikirim untuk dicek. Jam yang lolos validasi kemudian dihitung untuk pembayaran.</p>
          </div>
        </article>
      </div>
    </section>

    <section class="profit-section" id="pricing" aria-labelledby="profit-title">
      <div class="profit-heading animate-fade-down">
        <p class="chip"><span class="chip-dot"></span> SIMULASI PERTUMBUHAN</p>
        <h2 id="profit-title">Simulasikan Potensi Profit dari Skala Operasional Anda</h2>
      </div>

      <div class="profit-grid">
        <article class="tier-card animate-fade-right" data-delay="100">
          <div class="tier-name"><img src="{{ asset('vendor-assets/') }}/kamerakita/icon-tier-1.svg" alt=""><span>Tier 1: Minimal</span></div>
          <p class="worker-count"><strong>9</strong><span>orang worker</span></p>
          <div class="tier-profit">
            <span>Estimasi profit/bulan</span>
            <strong data-counter="7500000" data-prefix="Rp ">Rp 7.500.000</strong>
            <small>Volume: 500 Jam</small>
          </div>
        </article>

        <article class="tier-card animate-fade-left" data-delay="250">
          <div class="tier-name"><img src="{{ asset('vendor-assets/') }}/kamerakita/icon-tier-2.svg" alt=""><span>Tier 2: Menengah</span></div>
          <p class="worker-count"><strong>20</strong><span>orang worker</span></p>
          <div class="tier-profit">
            <span>Estimasi profit/bulan</span>
            <strong data-counter="18000000" data-prefix="Rp ">Rp 18.000.000</strong>
            <small>Volume: 1.200 Jam</small>
          </div>
        </article>

        <article class="tier-card tier-large animate-zoom-in" data-delay="400">
          <img class="profit-art" src="{{ asset('vendor-assets/') }}/kamerakita/profit-wallet.png" alt="Dompet besar berisi uang rupiah">
          <div class="tier-name"><img src="{{ asset('vendor-assets/') }}/kamerakita/icon-tier-3.svg" alt=""><span>Tier 3: Agensi Skala Besar</span></div>
          <p class="worker-count"><strong>50+</strong><span>orang worker</span></p>
          <div class="tier-profit">
            <span>Estimasi profit/bulan</span>
            <strong class="blue-profit" data-counter="45000000" data-prefix="Rp " data-suffix="+">Rp 45.000.000+</strong>
            <small>Volume: 3.000+ jam</small>
          </div>
          <a class="button tier-button" href="{{ $whatsappVendorUrl }}" target="_blank">Gabung Jadi Partner</a>
        </article>
      </div>
    </section>

    <section class="how-section" id="faq" aria-labelledby="how-title">
      <div class="how-heading">
        <div class="animate-fade-right">
          <p class="chip"><span class="chip-dot"></span> CARA KERJA</p>
          <h2 id="how-title">Mulai Jadi Partner dalam 3 Langkah</h2>
        </div>
        <a class="button how-button animate-fade-left" data-delay="150" href="{{ $whatsappVendorUrl }}" target="_blank">Gabung Jadi Partner</a>
      </div>

      <div class="steps-grid">
        <article class="step animate-fade-up" data-delay="100">
          <span class="step-number">1</span>
          <h3>JOIN</h3>
          <p>Daftarkan agency Anda. Siapkan worker, smartphone, dan headstrap.</p>
        </article>
        <article class="step animate-fade-up" data-delay="250">
          <span class="step-number">2</span>
          <h3>OPERATE</h3>
          <p>Jalankan project sesuai guideline KameraKita</p>
        </article>
        <article class="step animate-fade-up" data-delay="400">
          <span class="step-number">3</span>
          <h3>EARN</h3>
          <p>Terima pembayaran dari jam data yang berhasil divalidasi.</p>
        </article>
      </div>
    </section>

    <section class="final-cta" id="contact" aria-labelledby="cta-title">
      <img class="cta-art animate-fade-right" src="{{ asset('vendor-assets/') }}/kamerakita/final-cta-art.png" alt="Ilustrasi AI dan aliran koin">
      <div class="cta-copy animate-fade-left" data-delay="150">
        <h2 id="cta-title">Siap buka peluang bisnis baru dari proyek<br class="desktop-break"> AI global?</h2>
        <p>Masuk lebih awal, bangun operasional, dan nikmati<br class="desktop-break"> potensi margin dari setiap jam yang tervalidasi.</p>
        <a class="button button-yellow" href="{{ $whatsappVendorUrl }}" target="_blank">Gabung Jadi Partner</a>
      </div>
    </section>
  </main>
<script src="{{ asset('js/vendor.js') }}"></script>

<script>
/**
 * Premium Scroll Animation & Micro-Interactions Library
 */
(function() {
    // 1. Header glassmorphic shadow on scroll
    const header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                header.classList.add('shadow-md', 'bg-white/98');
                header.classList.remove('bg-white/95');
            } else {
                header.classList.remove('shadow-md', 'bg-white/98');
                header.classList.add('bg-white/95');
            }
        });
    }

    // 2. IntersectionObserver for Reveal Animations & Counter (Bulletproof 1-by-1 Reveal)
    const initAnimations = () => {
        const animationClasses = [
            '.animate-fade-up', '.animate-fade-down', '.animate-fade-left', 
            '.animate-fade-right', '.animate-zoom-in', '.animate-zoom-out', '.animate-flip-up', '.animate-scale-up'
        ];

        const animatedElements = document.querySelectorAll(animationClasses.join(', '));

        // Fallback for reduced motion or missing IntersectionObserver
        const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        if (reducedMotion || !('IntersectionObserver' in window)) {
            animatedElements.forEach(el => el.classList.add('is-visible'));
            return;
        }

        const observerOptions = {
            root: null,
            rootMargin: "0px 0px -80px 0px", 
            threshold: 0.1 
        };

        const observerCallback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    
                    // Trigger number counters if present inside entry
                    const counters = entry.target.querySelectorAll('[data-counter]');
                    counters.forEach(counterEl => animateCounter(counterEl));

                    if (!entry.target.hasAttribute('data-animate-repeat')) {
                        observer.unobserve(entry.target);
                    }
                }
            });
        };

        const observer = new IntersectionObserver(observerCallback, observerOptions);

        animatedElements.forEach((el) => {
            const delay = parseInt(el.getAttribute('data-delay') || '0', 10);
            if (delay > 0) {
                el.style.transitionDelay = `${delay}ms`;
            }

            // Hero section elements animate on load; all other sections strictly wait for user scroll
            if (el.closest('.hero')) {
                setTimeout(() => {
                    el.classList.add('is-visible');
                }, delay + 80);
            } else {
                observer.observe(el);
            }
        });
    };

    // 3. Number Counter Animation Function
    function animateCounter(el) {
        if (el.dataset.animated) return;
        el.dataset.animated = "true";

        const target = parseInt(el.getAttribute('data-counter'), 10);
        const prefix = el.getAttribute('data-prefix') || '';
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 1800; // ms
        const startTime = performance.now();

        function updateCount(currentTime) {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);
            // Ease out expo formula
            const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            const currentVal = Math.floor(easeProgress * target);

            el.textContent = prefix + currentVal.toLocaleString('id-ID') + suffix;

            if (progress < 1) {
                requestAnimationFrame(updateCount);
            } else {
                el.textContent = prefix + target.toLocaleString('id-ID') + suffix;
            }
        }

        requestAnimationFrame(updateCount);
    }

    // 4. Subtle 3D Tilt Effect on Hover for Cards
    const tiltCards = document.querySelectorAll('.project-card, .tier-card, .pov-card');
    tiltCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -6; // max 6deg
            const rotateY = ((x - centerX) / centerX) * 6;  // max 6deg

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnimations);
    } else {
        initAnimations();
    }
})();
</script>
</body>
</html>
