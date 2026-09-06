<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KameraKita AI - Kerja Rumah Jadi Cuan</title>
    <link rel="icon" href="{{ asset('images/Logo.webp') }}" type="image/webp">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=JetBrains+Mono:wght@400;700&family=Playfair+Display:ital,wght@1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/landing.css?v=' . time()) }}">
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>
    
    <!-- Navbar -->
    <header class="navbar" id="top">
      <div class="nav-shell">
        <a class="brand" href="#top" aria-label="KameraKita AI — kembali ke atas">
          <span class="brand__mark"><img src="{{ asset('vendor-assets/kamerakita/logo-mark.svg') }}" alt=""></span>
          <span>KameraKita<span class="brand__ai">AI</span></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Buka menu navigasi" id="menu-toggle-btn">
          <span></span><span></span><span></span>
        </button>
        <div class="nav-panel" id="nav-panel">
          <nav aria-label="Navigasi utama">
            <a href="#keunggulan" class="nav-link">{{ __('landing.nav.benefits') }}<span class="nav-chevron" aria-hidden="true">⌄</span></a>
            <a href="#kalkulator" class="nav-link">{{ __('landing.nav.earnings') }}<span class="nav-chevron" aria-hidden="true">⌄</span></a>
            <a href="#cara-kerja" class="nav-link">{{ __('landing.nav.how_it_works') }}</a>
            <a href="#mitra" class="nav-link">{{ __('landing.nav.faq') }}</a>
            <a href="#faq" class="nav-link">FAQ</a>
          </nav>
          <div class="nav-actions">
            <!-- Language Toggle -->
            <form method="POST" action="{{ route('locale.switch') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="locale" value="{{ app()->getLocale() === 'id' ? 'en' : 'id' }}">
                <button type="submit" class="button button--outline button--small" style="padding: 0 12px; font-weight: bold; border: 1px solid #d1d5db; color: #4b5563; background: white; cursor: pointer;">
                    {{ app()->getLocale() === 'id' ? '🇬🇧 EN' : '🇮🇩 ID' }}
                </button>
            </form>
            <a class="button button--outline button--small" href="{{ route('jadi-vendor') }}">Jadi Mitra Vendor</a>
            <a class="button button--blue button--small" href="{{ route('login') }}">{{ __('landing.nav.login') }}</a>
          </div>
        </div>
      </div>
    </header>

    <main id="main-content">
        <!-- Hero -->
        <section class="hero">
          <div class="hero-inner">
            <div class="hero-copy" data-reveal>
              <h1>{!! __('landing.hero.title') !!}</h1>
              <p>{{ __('landing.hero.subtitle') }}</p>
              <div class="hero-actions">
                <a class="button button--yellow" href="{{ route('onboarding.form') }}">{{ __('landing.hero.cta_primary') }}</a>
                <a class="button button--ghost" href="#cara-kerja">{{ __('landing.hero.cta_secondary') }}</a>
              </div>
              <div class="contributors">
                <div class="avatar-stack" aria-hidden="true">
                    <img src="{{ asset('assets/figma/hero-01.png') }}" alt="">
                    <img src="{{ asset('assets/figma/hero-02.png') }}" alt="">
                    <img src="{{ asset('assets/figma/hero-03.png') }}" alt="">
                    <img src="{{ asset('assets/figma/hero-04.png') }}" alt="">
                    <img src="{{ asset('assets/figma/hero-08.png') }}" alt="">
                    <img src="{{ asset('assets/figma/hero-15.png') }}" alt="">
                </div>
                <span>1000+ contributor bersama kami</span>
              </div>
            </div>
            <div class="hero-visual" data-reveal style="--delay: 140ms;" aria-label="Kontributor KameraKita menggunakan ponsel">
              <img class="hero-woman" src="{{ asset('assets/figma/hero-07.png') }}" alt="Perempuan tersenyum melihat ponsel">
              <img class="coin coin--back float-two" src="{{ asset('assets/figma/hero-13.png') }}" alt="">
              <img class="coin coin--one float-one" src="{{ asset('assets/figma/hero-13.png') }}" alt="">
              <img class="coin coin--two float-two" src="{{ asset('assets/figma/hero-16.png') }}" alt="">
              <img class="coin coin--three float-three" src="{{ asset('assets/figma/hero-13.png') }}" alt="">
              <img class="coin coin--four float-two" src="{{ asset('assets/figma/hero-16.png') }}" alt="">
              <div class="money-labels" aria-hidden="true"><span>+IDR 10.000</span><span>+IDR 50.000</span><strong>+IDR 100.000</strong></div>
            </div>
          </div>
        </section>

        <!-- Benefits -->
        <section class="benefits section" id="keunggulan">
          <div class="section-heading" data-reveal>
            <span class="eyebrow">KENAPA HARUS GABUNG?</span>
            <h2>Kerja simpel, hasil maksimal!</h2>
            <p>Kamu cukup rekam aktivitas harian dari rumah. Laporan yang lolos approved akan masuk rekap pendapatan secara transparan.</p>
          </div>
          <div class="benefit-grid">
              <article class="benefit-card" data-reveal style="--delay: 90ms;">
                <div class="benefit-art"><img class="float-1" src="{{ asset('assets/figma/benefits-ai-figma.png') }}" alt="Ilustrasi Bantu AI Biar Pintar"></div>
                <h3>Bantu AI Biar Pintar</h3><p>KameraKita AI ngajarin teknologi pintar (AI) biar bisa paham cara manusia beraktivitas di dalam rumah.</p>
              </article>
              <article class="benefit-card" data-reveal style="--delay: 180ms;">
                <div class="benefit-art"><img class="float-2" src="{{ asset('assets/figma/benefits-record-figma.png') }}" alt="Ilustrasi Rekam Kegiatan Rumah"></div>
                <h3>Rekam Kegiatan Rumah</h3><p>Tugas kamu cuma pakai alat di kepala, lalu rekam aktivitas harian kayak ngepel, nyuci piring, atau beres-beres.</p>
              </article>
              <article class="benefit-card" data-reveal style="--delay: 270ms;">
                <div class="benefit-art"><img class="float-3" src="{{ asset('assets/figma/benefits-wallet-figma.png') }}" alt="Ilustrasi Kirim Video & Terima Cuan"></div>
                <h3>Kirim Video & Terima Cuan</h3><p>Kerjaan rumah beres, dompet tetep tebel. Gak perlu keahlian khusus, semua orang semua kalangan pasti bisa!</p>
              </article>
          </div>
        </section>

        <!-- Income Calculator -->
        <section class="calculator-section section" id="kalkulator">
          <div class="calculator-shell" data-reveal>
            <span class="ambient ambient--blue" aria-hidden="true"></span>
            <span class="ambient ambient--yellow" aria-hidden="true"></span>
            <div class="section-heading" data-reveal style="--delay: 80ms;">
              <span class="eyebrow">SIMULASI CUAN MINGGUAN</span>
              <h2>Pilih ritme kerja yang paling cocok</h2>
              <p>Rate dasar Rp60.000 per jam rekaman bersih. Simulasi ini membantu kamu membayangkan potensi cuan mingguan sebelum mulai.</p>
            </div>
            
            <div class="calculator-grid">
              <div class="calculator-input" data-reveal style="--delay: 150ms;">
                <div class="rate-card">
                  <span>RATE PER JAM</span>
                  <strong>Rp 60.000</strong>
                  <small>Dikalikan jam kerja harian yang laporan videonya lolos QC.</small>
                </div>
                <fieldset>
                  <legend>Pilih simulasi kerja</legend>
                  <div class="mode-list" id="calc-modes">
                      <!-- Filled by JS -->
                  </div>
                </fieldset>
                <div class="calc-facts">
                  <div><span>DASAR HITUNG</span><strong>Jam approved</strong></div>
                  <div><span>VERIFIKASI</span><strong>Lewat QC</strong></div>
                </div>
              </div>
              <div class="earnings-card" data-reveal style="--delay: 230ms;">
                <div class="earnings-copy" id="calc-monthly-container">
                    <div id="calc-monthly">
                      <span>ESTIMASI TOTAL BULANAN</span>
                      <strong>Rp 3.360.000</strong>
                      <p>Dengan 2 jam/hari dan rate dasar Rp60.000/jam.</p>
                    </div>
                </div>
                <div class="weekly" id="calc-weekly-container">
                    <div id="calc-weekly">
                      <span>POTENSI MINGGUAN</span>
                      <strong>~ Rp 840.000</strong>
                    </div>
                </div>
                <a class="button button--blue calculator-cta" href="{{ route('onboarding.form') }}">MULAI DAFTAR SEKARANG</a>
                <p class="disclaimer">Simulasi bukan jaminan pendapatan. Nominal mengikuti laporan approved dan ketentuan operasional.</p>
                <img class="wallet-art float-slow" src="{{ asset('assets/figma/calculator-03.png') }}" alt="Ilustrasi dompet dengan uang rupiah">
              </div>
            </div>
          </div>
        </section>

        <!-- How It Works -->
        <section class="steps section" id="cara-kerja">
          <span class="steps-glow steps-glow--blue" aria-hidden="true"></span><span class="steps-glow steps-glow--yellow" aria-hidden="true"></span>
          <div class="section-heading" data-reveal>
            <span class="eyebrow">CARA KERJA</span>
            <h2>Cuma 3 langkah buat mulai dapet cuan</h2>
            <p>Dari daftar sampai pembayaran, semuanya dibuat simpel dan bakal dipandu tim KameraKita.</p>
          </div>
          <div class="step-grid">
              <article class="step-card" data-reveal style="--delay: 100ms;">
                <img class="step-art step-float" style="--float-delay: 0ms;" src="{{ asset('assets/figma/steps-03.png') }}" alt="Ilustrasi langkah 1">
                <strong class="step-number">1</strong>
                <h3>Gabung & Ikuti Briefing</h3><p>Daftar lewat WhatsApp, lalu tim kami bakal jelasin cara kerja, tugas, dan kebutuhan alat.</p>
              </article>
              <article class="step-card" data-reveal style="--delay: 200ms;">
                <img class="step-art step-float" style="--float-delay: 160ms;" src="{{ asset('assets/figma/steps-01.png') }}" alt="Ilustrasi langkah 2">
                <strong class="step-number">2</strong>
                <h3>Rekam Aktivitasmu</h3><p>Pilih tugas yang tersedia, pasang HP sesuai panduan, lalu rekam aktivitas sehari-hari seperti biasa.</p>
              </article>
              <article class="step-card" data-reveal style="--delay: 300ms;">
                <img class="step-art step-float" style="--float-delay: 320ms;" src="{{ asset('assets/figma/steps-09.png') }}" alt="Ilustrasi langkah 3">
                <strong class="step-number">3</strong>
                <h3>Upload & Terima Bayaran</h3><p>Kirim hasil rekaman untuk dicek. Setelah lolos QC, durasi approved masuk ke pembayaran bulanan.</p>
              </article>
          </div>
        </section>

        <!-- Testimonials -->
        <section class="testimonials section" id="mitra">
          <span class="testimonial-glow testimonial-glow--one" aria-hidden="true"></span><span class="testimonial-glow testimonial-glow--two" aria-hidden="true"></span>
          <div class="section-heading" data-reveal>
            <span class="eyebrow">TESTIMONY</span>
            <h2>Cerita kontributor yang mulai punya<br>penghasilan tambahan</h2>
            <p>Beberapa pengalaman yang menggambarkan bagaimana alur kerja, QC, dan pembayaran dijalankan dengan lebih rapi.</p>
          </div>
          <div class="testimonial-grid">
              <article class="testimonial-card" data-reveal style="--delay: 90ms;">
                <div class="quote-pair float-1" aria-hidden="true"><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""></div>
                <blockquote>Laporan rapi, pembayaran jadi lebih tenang</blockquote>
                <div class="person"><img src="{{ asset('assets/figma/testimonials-02.png') }}" alt=""><cite>Asha Aulia</cite></div>
              </article>
              <article class="testimonial-card" data-reveal style="--delay: 170ms;">
                <div class="quote-pair float-2" aria-hidden="true"><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""></div>
                <blockquote>Awalnya ragu, setelah rutin submit mulai terasa hasilnya</blockquote>
                <div class="person"><img src="{{ asset('assets/figma/testimonials-01.png') }}" alt=""><cite>Irfan Tamami</cite></div>
              </article>
              <article class="testimonial-card" data-reveal style="--delay: 250ms;">
                <div class="quote-pair float-3" aria-hidden="true"><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""></div>
                <blockquote>QC Jelas, jadi tahu laporan mana yang perlu diperbaiki</blockquote>
                <div class="person"><img src="{{ asset('assets/figma/testimonials-03.png') }}" alt=""><cite>Jessica Halim</cite></div>
              </article>
              <article class="testimonial-card" data-reveal style="--delay: 330ms;">
                <div class="quote-pair float-1" aria-hidden="true"><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""><img src="{{ asset('assets/figma/testimonials-09.png') }}" alt=""></div>
                <blockquote>Modal HP dan waktu luang, bisa mulai dari rumah</blockquote>
                <div class="person"><img src="{{ asset('assets/figma/testimonials-06.png') }}" alt=""><cite>Nadya Salsabila</cite></div>
              </article>
          </div>
        </section>

        <!-- FAQ -->
        <section class="faq section" id="faq">
          <div class="section-heading" data-reveal>
            <span class="eyebrow">FAQ</span>
            <h2>Pertanyaan yang Sering Diajukan</h2>
          </div>
          <div class="faq-list" data-reveal style="--delay: 100ms;" id="faq-list">
             <article class="faq-item">
               <h3>
                 <button type="button" aria-expanded="false" class="faq-btn">
                   <span>Apakah pendaftaran mitra dipungut biaya?</span><span class="faq-plus" aria-hidden="true">+</span>
                 </button>
               </h3>
               <div class="faq-answer" aria-hidden="true">
                 <div><p>Sama sekali tidak. Pendaftaran Mitra Kontributor di KAMERAKITA AI 100% GRATIS tanpa modal awal apa pun.</p></div>
               </div>
             </article>
             <article class="faq-item">
               <h3>
                 <button type="button" aria-expanded="false" class="faq-btn">
                   <span>Kapan komisi hasil rekap durasi akan dicairkan?</span><span class="faq-plus" aria-hidden="true">+</span>
                 </button>
               </h3>
               <div class="faq-answer" aria-hidden="true">
                 <div><p>Pencairan komisi diproses manual oleh admin sesuai jadwal operasional berdasarkan rekap durasi yang sudah approved.</p></div>
               </div>
             </article>
          </div>
        </section>

        <!-- Bottom CTA -->
        <section class="bottom-cta">
          <img class="cta-banner" src="{{ asset('assets/figma/cta-banner.png') }}" alt="Kontributor KameraKita menggunakan ponsel di rumah">
          <div class="cta-copy">
            <div data-reveal>
              <h2>{!! __('landing.cta.title') !!}</h2>
              <p>{{ __('landing.cta.subtitle') }}</p>
              <a class="button button--blue button--large" href="{{ route('onboarding.form') }}">{{ __('landing.hero.cta_primary') }}</a>
            </div>
          </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-grid">
        <div>
          <a class="brand brand--footer" href="#top" aria-label="KameraKita AI — kembali ke atas">
            <span class="brand__mark"><img src="{{ asset('vendor-assets/kamerakita/logo-mark.svg') }}" alt=""></span>
            <span>KameraKita<span class="brand__ai">AI</span></span>
          </a>
          <p>Platform Rekam Video & Datasets Terpercaya No. 1 di Indonesia untuk Mitra & Enterprise Computer Vision.</p>
        </div>
        <div><h2>LAYANAN MITRA</h2><a href="#keunggulan">Keunggulan Mitra</a><a href="#kalkulator">Kalkulator Komisi</a><a href="#cara-kerja">{{ __('landing.nav.how_it_works') }}</a></div>
        <div><h2>AKSES AKUN</h2><a href="{{ route('login') }}">{{ __('landing.nav.login') }}</a><a href="{{ route('onboarding.form') }}">Daftar Kontributor Baru</a><a href="{{ route('dashboard') }}">Dashboard Admin QC</a></div>
        <div><h2>PERUSAHAAN</h2><p>Domain Resmi: <a href="{{ url('/') }}">{{ parse_url(url('/'), PHP_URL_HOST) ?? 'kamerakitaid.site' }}</a></p><p>PT KAMERAKITA AI Indonesia</p></div>
      </div>
      <div class="footer-bottom"><span>© {{ date('Y') }} KAMERAKITA AI. All rights reserved.</span><div><span>Kebijakan Privasi</span><span>Syarat & Ketentuan</span></div></div>
    </footer>

    <!-- Scripts -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
          // 1. Navbar Mobile Toggle
          const menuBtn = document.getElementById('menu-toggle-btn');
          const navPanel = document.getElementById('nav-panel');
          const navLinks = document.querySelectorAll('.nav-link');
          
          if(menuBtn && navPanel) {
              menuBtn.addEventListener('click', () => {
                  const isOpen = navPanel.classList.toggle('is-open');
                  menuBtn.setAttribute('aria-expanded', isOpen);
              });
              
              navLinks.forEach(link => {
                  link.addEventListener('click', () => {
                      navPanel.classList.remove('is-open');
                      menuBtn.setAttribute('aria-expanded', 'false');
                  });
              });
              
              window.addEventListener('hashchange', () => {
                  navPanel.classList.remove('is-open');
                  menuBtn.setAttribute('aria-expanded', 'false');
              });
          }

          // 2. Scroll Reveal (IntersectionObserver)
          const revealElements = [...document.querySelectorAll('[data-reveal]')];
          if (!('IntersectionObserver' in window)) {
            revealElements.forEach(el => el.classList.add('is-visible'));
          } else {
            const observer = new IntersectionObserver((entries) => {
              entries.forEach((entry) => {
                if (entry.isIntersecting || entry.boundingClientRect.top < window.innerHeight) {
                  entry.target.classList.add('is-visible');
                  observer.unobserve(entry.target);
                }
              });
            }, { rootMargin: '0px 0px -4% 0px', threshold: 0.01 });
            
            revealElements.forEach(el => observer.observe(el));
          }

          // 3. Calculator Logic
          const RATE = 60000;
          const DAYS_PER_WEEK = 7;
          const WEEKS_PER_MONTH = 4;
          const MODES = [
            { name: 'Santai', copy: 'Mulai pelan, tetap produktif', hours: 2 },
            { name: 'Fokus', copy: 'Lebih rutin, hasil lebih terasa', hours: 4 },
            { name: 'Gacor Ketua!!!', copy: 'Mode serius cari cuan', hours: 6 },
          ];
          
          const formatRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num).replace('Rp', 'Rp ');
          
          const calcModesContainer = document.getElementById('calc-modes');
          const calcMonthlyContainer = document.getElementById('calc-monthly-container');
          const calcWeeklyContainer = document.getElementById('calc-weekly-container');
          
          let selectedMode = 0;
          
          function renderCalculator() {
              if(!calcModesContainer) return;
              calcModesContainer.innerHTML = '';
              MODES.forEach((mode, index) => {
                  const btn = document.createElement('button');
                  btn.type = 'button';
                  btn.className = `mode-card ${selectedMode === index ? 'is-selected' : ''}`;
                  btn.setAttribute('aria-pressed', selectedMode === index);
                  btn.innerHTML = `<span><strong>${mode.name}</strong><small>${mode.copy}</small></span><b>${mode.hours} jam/hari</b>`;
                  btn.onclick = () => {
                      selectedMode = index;
                      renderCalculator();
                  };
                  calcModesContainer.appendChild(btn);
              });
              
              const mode = MODES[selectedMode];
              const weekly = RATE * mode.hours * DAYS_PER_WEEK;
              const monthly = weekly * WEEKS_PER_MONTH;
              
              // Animate number change by cloning and replacing
              const oldMonthly = calcMonthlyContainer.querySelector('div');
              const newMonthly = oldMonthly.cloneNode(false);
              newMonthly.innerHTML = `<span>ESTIMASI TOTAL BULANAN</span><strong>${formatRp(monthly)}</strong><p>Dengan ${mode.hours} jam/hari dan rate dasar Rp60.000/jam.</p>`;
              calcMonthlyContainer.replaceChild(newMonthly, oldMonthly);
              
              const oldWeekly = calcWeeklyContainer.querySelector('div');
              const newWeekly = oldWeekly.cloneNode(false);
              newWeekly.innerHTML = `<span>POTENSI MINGGUAN</span><strong>~ ${formatRp(weekly)}</strong>`;
              calcWeeklyContainer.replaceChild(newWeekly, oldWeekly);
          }
          
          renderCalculator();

          // 4. FAQ Accordion
          const faqItems = document.querySelectorAll('.faq-item');
          faqItems.forEach(item => {
              const btn = item.querySelector('.faq-btn');
              const answer = item.querySelector('.faq-answer');
              if(btn && answer) {
                  btn.addEventListener('click', () => {
                      const isOpen = item.classList.contains('is-open');
                      
                      // Close all other
                      faqItems.forEach(otherItem => {
                          otherItem.classList.remove('is-open');
                          otherItem.querySelector('.faq-btn').setAttribute('aria-expanded', 'false');
                          otherItem.querySelector('.faq-answer').setAttribute('aria-hidden', 'true');
                      });
                      
                      if (!isOpen) {
                          item.classList.add('is-open');
                          btn.setAttribute('aria-expanded', 'true');
                          answer.setAttribute('aria-hidden', 'false');
                      }
                  });
              }
          });
      });
    </script>
</body>
</html>
