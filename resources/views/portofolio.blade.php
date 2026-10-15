@php
    $locale = app()->getLocale();
    $en = $locale === 'en';
    $number = fn ($value, $decimals = 0) => number_format($value, $decimals, $en ? '.' : ',', $en ? ',' : '.');
    $lines = fn ($key) => explode('\n', __($key));
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('portfolio.title') }}</title>
    <meta name="description" content="{{ __('portfolio.description') }}">
    <meta name="theme-color" content="#0284c7">
    <meta property="og:title" content="{{ __('portfolio.title') }}">
    <meta property="og:description" content="{{ __('portfolio.description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('portfolio.index') }}">
    <meta property="og:image" content="{{ asset('vendor-assets/kamerakita/hero-pov.png') }}">
    <link rel="canonical" href="{{ route('portfolio.index') }}">
    <link rel="icon" href="{{ asset('images/Logo.webp') }}" type="image/webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@1,600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portfolio.css') }}?v={{ filemtime(public_path('css/portfolio.css')) }}">
</head>
<body class="portfolio-page" id="top">
<a class="skip-link" href="#main-content">{{ __('portfolio.skip') }}</a>
<header class="pf-header">
    <div class="pf-container pf-nav">
        <a class="brand" href="{{ url('/') }}" aria-label="KameraKita AI — {{ __('portfolio.nav.home') }}">
            <span class="brand__mark"><img src="{{ asset('images/Logo.webp') }}" alt="" width="40" height="40"></span>
            <span>KameraKita<span class="brand__ai">AI</span></span>
        </a>
        <nav class="pf-desktop-nav" aria-label="{{ __('portfolio.nav.portfolio') }}">
            <a href="#capabilities">{{ __('portfolio.nav.capabilities') }}</a>
            <a href="#production">{{ __('portfolio.nav.production') }}</a>
            <a href="#workflow">{{ __('portfolio.nav.workflow') }}</a>
        </nav>
        <div class="pf-nav-actions">
            <div class="pf-languages" aria-label="Language">
                @foreach(['id' => 'ID', 'en' => 'EN'] as $language => $label)
                    <form method="POST" action="{{ route('locale.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $language }}">
                        <button type="submit" lang="{{ $language }}" aria-label="{{ $language === 'id' ? 'Bahasa Indonesia' : 'English' }}" aria-pressed="{{ $locale === $language ? 'true' : 'false' }}">{{ $label }}</button>
                    </form>
                @endforeach
            </div>
            <a class="button button--blue pf-nav-cta" href="#contact">{{ __('portfolio.nav.contact') }} <span aria-hidden="true">↗</span></a>
        </div>
    </div>
</header>
<main id="main-content">
    <section class="pf-hero" aria-labelledby="hero-title">
        <div class="pf-container pf-hero-grid">
            <div class="pf-hero-copy">
                <p class="pf-eyebrow"><span class="pf-status-dot" aria-hidden="true"></span>{{ __('portfolio.hero.eyebrow') }}</p>
                <h1 id="hero-title">{{ __('portfolio.hero.line_one') }}<br><em>{{ __('portfolio.hero.line_two') }}</em></h1>
                <p class="pf-hero-description">{{ __('portfolio.hero.description') }}</p>
                <div class="pf-hero-actions">
                    <a class="button button--yellow" href="#contact">{{ __('portfolio.hero.primary') }} <span aria-hidden="true">↗</span></a>
                    <a class="pf-text-link" href="#capabilities">{{ __('portfolio.hero.secondary') }} <span aria-hidden="true">↓</span></a>
                </div>
                <p class="pf-hero-note">{{ __('portfolio.hero.note') }}</p>
            </div>
            <div class="pf-hero-visual">
                <div class="pf-camera-frame">
                    <img src="{{ asset('vendor-assets/kamerakita/hero-pov.png') }}" alt="{{ __('portfolio.hero.image_alt') }}" width="1024" height="1024" fetchpriority="high">
                    <div class="pf-camera-top"><span><i aria-hidden="true"></i> POV / 01</span><span>{{ __('portfolio.hero.location') }} <span aria-hidden="true">↗</span></span></div>
                    <div class="pf-camera-corners" aria-hidden="true"></div>
                    <div class="pf-camera-bottom"><span>{{ __('portfolio.hero.frame') }}</span><span aria-hidden="true">[ + ]</span></div>
                </div>
                <div class="pf-quality-card">
                    <span class="pf-quality-icon" aria-hidden="true">✓</span>
                    <div><strong>{{ $number($portfolio['approval_percentage'], 1) }}<small>%</small></strong><span>{{ __('portfolio.hero.quality') }}</span></div>
                    <span class="pf-quality-spark" aria-hidden="true">▁▂▃▂▄▅▆▅▇</span>
                </div>
                <div class="pf-visual-caption"><span aria-hidden="true">✳</span>{{ __('portfolio.hero.tag') }}</div>
            </div>
        </div>
    </section>

    <section class="pf-stats-section" aria-label="{{ __('portfolio.stats.source') }}">
        <div class="pf-container">
            <div class="pf-stat-grid">
                @foreach([
                    ['value' => $number(floor($portfolio['atlas']['recorded_minutes'] / 60)).'+', 'key' => 'recorded'],
                    ['value' => $number(floor($portfolio['atlas']['approved_minutes'] / 60)).'+', 'key' => 'approved'],
                    ['value' => $number($portfolio['registered_contributors']), 'key' => 'contributors'],
                    ['value' => '02', 'key' => 'platforms'],
                ] as $stat)
                    <div class="pf-stat"><strong>{{ $stat['value'] }}</strong><h2>{{ __('portfolio.stats.'.$stat['key']) }}</h2><p>{{ __('portfolio.stats.'.$stat['key'].'_note') }}</p></div>
                @endforeach
            </div>
            <div class="pf-platforms"><span>{{ __('portfolio.platforms.note') }}</span><span class="pf-platform-logo"><span class="pf-atlas-mark" aria-hidden="true">A</span>Atlas <b>Capture</b></span><span class="pf-platform-logo"><span class="pf-minute-mark" aria-hidden="true">m</span>Minute <b>Data</b></span><span class="pf-snapshot">{{ __('portfolio.stats.source') }}</span></div>
        </div>
    </section>

    <section class="pf-section" id="capabilities" aria-labelledby="cap-title">
        <div class="pf-container">
            <div class="pf-section-head"><div><p class="pf-eyebrow">{{ __('portfolio.capabilities.eyebrow') }}</p><h2 id="cap-title">@foreach($lines('portfolio.capabilities.title') as $line){{ $line }}@unless($loop->last)<br>@endunless @endforeach</h2></div><p>{{ __('portfolio.capabilities.description') }}</p></div>
            <div class="pf-cap-grid">
                @foreach(__('portfolio.capabilities.items') as $item)
                    <article class="pf-cap-card"><div class="pf-cap-top"><span>{{ $item['number'] }}</span><span aria-hidden="true">{{ ['⌁', '⌘', '✳'][$loop->index] }}</span></div><h3>{{ $item['title'] }}</h3><p>{{ $item['description'] }}</p><span class="pf-chip">{{ $item['tag'] }}</span></article>
                @endforeach
            </div>
            <ul class="pf-cap-chips">@foreach(__('portfolio.capabilities.chips') as $chip)<li><span aria-hidden="true">✓</span>{{ $chip }}</li>@endforeach</ul>
        </div>
    </section>

    <section class="pf-section pf-proof" id="production" aria-labelledby="proof-title">
        <div class="pf-container pf-proof-grid">
            <div class="pf-proof-copy"><p class="pf-eyebrow">{{ __('portfolio.proof.eyebrow') }}</p><h2 id="proof-title">@foreach($lines('portfolio.proof.title') as $line){{ $line }}@unless($loop->last)<br>@endunless @endforeach</h2><p>{{ __('portfolio.proof.description') }}</p>
                <div class="pf-target"><span>{{ __('portfolio.proof.target_label') }}</span><strong>{{ $number($portfolio['monthly_target_hours']) }}<i>+</i> <small>{{ __('portfolio.proof.target_unit') }}</small></strong><p>{{ __('portfolio.proof.target_note') }}</p></div>
                <a class="pf-text-link" href="#contact">{{ __('portfolio.nav.contact') }} <span aria-hidden="true">↗</span></a>
            </div>
            <div class="pf-production-board">
                <div class="pf-board-heading"><div><span class="pf-board-dots" aria-hidden="true">● ● ●</span><h3>{{ __('portfolio.proof.chart_title') }}</h3><p>{{ __('portfolio.proof.chart_subtitle') }}</p></div><span class="pf-board-badge">2026</span></div>
                <div class="pf-chart" role="img" aria-label="{{ __('portfolio.proof.chart_subtitle') }}: @foreach($portfolio['reports']['monthly_hours'] as $month => $hours){{ $month }} {{ $number($hours, 2) }} {{ __('portfolio.proof.chart_unit') }}; @endforeach">
                    @foreach($portfolio['reports']['monthly_hours'] as $month => $hours)
                        <div class="pf-chart-column"><span class="pf-chart-value">{{ $number($hours) }}</span><div class="pf-chart-bar" style="--bar-height: {{ round($hours / max($portfolio['reports']['monthly_hours']) * 100, 1) }}%"><span></span></div><span class="pf-chart-month">{{ __('portfolio.proof.chart_months')[$loop->index] }}</span></div>
                    @endforeach
                </div>
                <p class="pf-chart-note">{{ __('portfolio.proof.chart_note') }}</p>
                <div class="pf-board-bottom"><div><strong>{{ $number($portfolio['reports']['count']) }}</strong><span>{{ __('portfolio.proof.report_label') }}</span></div><div><strong>01 <span aria-hidden="true">↗</span></strong><span>{{ __('portfolio.proof.qa_label') }}</span></div></div>
            </div>
        </div>
    </section>

    <section class="pf-section" id="workflow" aria-labelledby="workflow-title">
        <div class="pf-container">
            <div class="pf-centered-head"><p class="pf-eyebrow">{{ __('portfolio.workflow.eyebrow') }}</p><h2 id="workflow-title">@foreach($lines('portfolio.workflow.title') as $line){{ $line }}@unless($loop->last)<br>@endunless @endforeach</h2><p>{{ __('portfolio.workflow.description') }}</p></div>
            <ol class="pf-workflow">@foreach(__('portfolio.workflow.steps') as $step)<li><div class="pf-step-number">0{{ $loop->iteration }}<span aria-hidden="true">↗</span></div><h3>{{ $step['title'] }}</h3><p>{{ $step['description'] }}</p></li>@endforeach</ol>
            <div class="pf-workflow-note">@foreach(__('portfolio.proof.qa_items') as $item)<span><i aria-hidden="true">✓</i>{{ $item }}</span>@endforeach</div>
        </div>
    </section>

    <section class="pf-section pf-showcase" id="showcase" aria-labelledby="showcase-title">
        <div class="pf-container">
            <div class="pf-section-head"><div><p class="pf-eyebrow">{{ __('portfolio.showcase.eyebrow') }}</p><h2 id="showcase-title">@foreach($lines('portfolio.showcase.title') as $line){{ $line }}@unless($loop->last)<br>@endunless @endforeach</h2></div><p>{{ __('portfolio.showcase.description') }}</p></div>
            <div class="pf-gallery">@foreach(__('portfolio.showcase.cards') as $card)<figure><div class="pf-gallery-photo pf-crop-{{ $card['position'] }}"><img src="{{ asset('vendor-assets/pov-activities.webp') }}" alt="{{ $card['alt'] }}" loading="lazy" width="1254" height="1254"><span class="pf-gallery-tag">POV / 0{{ $loop->iteration }}</span><span class="pf-gallery-cross" aria-hidden="true">+</span></div><figcaption><h3>{{ $card['title'] }}</h3><p>{{ $card['description'] }}</p></figcaption></figure>@endforeach</div>
            <p class="pf-gallery-caption">{{ __('portfolio.showcase.illustration') }}</p>
            <div class="pf-project-strips"><div><span>{{ __('portfolio.showcase.atlas_label') }}</span><strong>{{ $number(floor($portfolio['atlas']['approved_minutes'] / 60)) }}<em>+</em> <small>{{ __('portfolio.proof.chart_unit') }}</small></strong><p>{{ __('portfolio.showcase.atlas_note') }}</p></div><div><span>{{ __('portfolio.showcase.minute_label') }}</span><strong>{{ $number(floor($portfolio['minute_data']['approved_minutes'] / 60)) }}<em>+</em> <small>{{ __('portfolio.proof.chart_unit') }}</small></strong><p>{{ __('portfolio.showcase.minute_note') }}</p></div></div>
            <details class="pf-methodology"><summary>{{ __('portfolio.methodology.title') }} <span aria-hidden="true">+</span></summary><div><p>{{ __('portfolio.methodology.atlas') }}</p><p>{{ __('portfolio.methodology.reports') }}</p><p>{{ __('portfolio.methodology.separation') }}</p><p>{{ __('portfolio.platforms.context') }}</p></div></details>
        </div>
    </section>

    <section class="pf-section pf-contact" id="contact" aria-labelledby="contact-title">
        <div class="pf-container pf-contact-grid">
            <div class="pf-contact-copy"><p class="pf-eyebrow">{{ __('portfolio.contact.eyebrow') }}</p><h2 id="contact-title">@foreach($lines('portfolio.contact.title') as $line)<span>{{ $line }}</span>@unless($loop->last)<br>@endunless @endforeach</h2><p class="pf-contact-lead">{{ __('portfolio.contact.description') }}</p><p>{{ __('portfolio.contact.body') }}</p><ul>@foreach(__('portfolio.contact.tags') as $tag)<li><span aria-hidden="true">✓</span>{{ $tag }}</li>@endforeach</ul><div class="pf-contact-art" aria-hidden="true"><span>POV</span><span>→</span><span>AI</span></div></div>
            <form class="pf-contact-form" method="POST" action="{{ route('portfolio.inquiry') }}">
                @csrf
                <h3>{{ __('portfolio.contact.form_title') }}</h3>
                @foreach(['name' => 'text', 'company' => 'text', 'email' => 'email'] as $field => $type)
                    <div class="pf-field"><label for="inquiry-{{ $field }}">{{ __('portfolio.form.'.$field) }}</label><input id="inquiry-{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" placeholder="{{ __('portfolio.form.'.$field.'_placeholder') }}" required maxlength="{{ $field === 'email' ? 254 : ($field === 'company' ? 150 : 100) }}" autocomplete="{{ ['name' => 'name', 'company' => 'organization', 'email' => 'email'][$field] }}" @error($field) aria-invalid="true" aria-describedby="error-{{ $field }}" @enderror>@error($field)<p class="pf-field-error" id="error-{{ $field }}">{{ $message }}</p>@enderror</div>
                @endforeach
                <div class="pf-field"><label for="inquiry-brief">{{ __('portfolio.form.brief') }}</label><textarea id="inquiry-brief" name="brief" rows="4" required minlength="10" maxlength="2000" placeholder="{{ __('portfolio.form.brief_placeholder') }}" @error('brief') aria-invalid="true" aria-describedby="error-brief" @enderror>{{ old('brief') }}</textarea>@error('brief')<p class="pf-field-error" id="error-brief">{{ $message }}</p>@enderror</div>
                <button class="button button--blue" type="submit">{{ __('portfolio.form.submit') }} <span aria-hidden="true">↗</span></button><p class="pf-form-note">{{ __('portfolio.contact.note') }}</p>
            </form>
        </div>
    </section>
</main>
<footer class="pf-footer"><div class="pf-container"><div class="pf-footer-top"><div><a class="brand" href="{{ url('/') }}"><span class="brand__mark"><img src="{{ asset('images/Logo.webp') }}" alt="" width="40" height="40"></span><span>KameraKita<span class="brand__ai">AI</span></span></a><p>@foreach($lines('portfolio.footer.description') as $line){{ $line }}<br>@endforeach</p></div><nav aria-label="Footer"><a href="{{ url('/') }}">{{ __('portfolio.nav.home') }}</a><a href="{{ route('jadi-vendor') }}">{{ __('portfolio.nav.vendor') }}</a><a href="#contact">{{ __('portfolio.nav.contact') }} ↗</a></nav></div><div class="pf-footer-bottom"><span>© {{ date('Y') }} KameraKita AI. {{ __('portfolio.footer.rights') }}</span><a href="#top">{{ __('portfolio.footer.back') }} ↑</a></div></div></footer>
</body>
</html>
