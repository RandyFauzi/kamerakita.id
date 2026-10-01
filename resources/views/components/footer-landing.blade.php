<style>
  .k-footer { min-height: 284px; padding: 48px max(32px, calc((100% - 1280px) / 2)) 32px; color: #64748b; background: #0f172a; font-size: 12px; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
  .k-footer-grid { display: grid; grid-template-columns: 1.05fr 1fr 1fr 1.1fr; gap: 64px; }
  .k-footer-grid a.k-brand { display: inline-flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #fff; text-decoration: none; }
  .k-footer .k-brand__mark { flex: 0 0 29px; width: 29px; max-width: 29px; height: 29px; padding: 3px; border-radius: 8px; }
  .k-footer .k-brand__mark img { width: 100%; height: 100%; object-fit: contain; }
  .k-footer .k-brand__ai { color: #fff; }
  .k-footer-grid h2 { margin: 0 0 13px; color: #fff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
  .k-footer-grid p { max-width: 280px; margin: 10px 0 0; line-height: 1.65; }
  .k-footer-grid a { display: block; margin-top: 8px; transition: color 180ms ease; color: #64748b; text-decoration: none; }
  .k-footer-grid a:hover { color: #fff; }
  .k-footer-grid p a { display: inline; color: #38bdf8; font-family: 'JetBrains Mono', monospace; }
  .k-footer-bottom { margin-top: 64px; padding-top: 32px; border-top: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center; color: #64748b; font-size: 11px; }
  .k-footer-bottom div { display: flex; gap: 24px; }
  @media (max-width: 800px) {
      .k-footer-grid { grid-template-columns: 1fr; gap: 48px; }
      .k-footer-bottom { flex-direction: column; gap: 24px; text-align: center; }
  }
</style>

<footer class="k-footer">
    <div class="k-footer-grid">
        <div>
        <a class="k-brand" href="/" aria-label="KameraKita AI">
            <span class="k-brand__mark"><img src="{{ asset('images/Logo.webp') }}" alt=""></span>
            <span>KameraKita<span class="k-brand__ai">AI</span></span>
        </a>
        <p>{{ __('landing.footer_desc') }}</p>
        </div>
        <div>
            <h2>{{ __('landing.footer_services') }}</h2>
            <a href="/#keunggulan">Keunggulan Mitra</a>
            <a href="/#kalkulator">Kalkulator Komisi</a>
            <a href="/#cara-kerja">{{ __('landing.nav.how_it_works') }}</a>
        </div>
        <div>
            <h2>{{ __('landing.footer_acc') }}</h2>
            <a href="{{ route('login') }}">{{ __('landing.nav.login') }}</a>
            <a href="{{ route('onboarding.form') }}">Daftar Kontributor Baru</a>
            <a href="{{ route('dashboard') }}">Dashboard Admin QC</a>
        </div>
        <div>
            <h2>{{ __('landing.footer_corp') }}</h2>
            <p>Domain Resmi: <a href="{{ url('/') }}">{{ parse_url(url('/'), PHP_URL_HOST) ?? 'kamerakitaid.site' }}</a></p>
            <p>{{ __('landing.footer_pt') }}</p>
        </div>
    </div>
    <div class="k-footer-bottom">
        <span>&copy; {{ date('Y') }} KAMERAKITA AI. All rights reserved.</span>
        <div>
            <span>Kebijakan Privasi</span>
            <span>Syarat & Ketentuan</span>
        </div>
    </div>
</footer>
