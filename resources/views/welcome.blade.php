<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Powering Model Labs & Enterprise AI | KAMERAKITA AI</title>
    
    <!-- Meta Description -->
    <meta name="description" content="KAMERAKITA AI helps enterprises build and scale AI with production-ready video datasets, secure data operations, and human-in-the-loop QC verification.">
    <link rel="icon" href="{{ asset('Logo.webp') }}" type="image/webp">

    <!-- Google Fonts: Inter & Geist Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN for 100% Guaranteed Production Reliability -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        centific: {
                            green: '#1a7954',
                            greenLight: '#eef8f3',
                            greenBorder: '#d1e9dc',
                            black: '#121212',
                            darkCard: '#1c1c1c',
                            grayBg: '#f8fafc',
                            border: '#e2e8f0',
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
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #121212;
            -webkit-font-smoothing: antialiased;
        }
        .bg-grid-subtle {
            background-image: radial-gradient(rgba(0, 0, 0, 0.04) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .centific-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            transition: all 0.25s ease;
        }
        .centific-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }
        .badge-green {
            background-color: rgba(26, 121, 84, 0.08);
            color: #1a7954;
            border: 1px solid rgba(26, 121, 84, 0.2);
        }
        .pill-btn-black {
            background-color: #121212;
            color: #ffffff;
            border-radius: 9999px;
            transition: all 0.2s ease;
        }
        .pill-btn-black:hover {
            background-color: #2a2a2a;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.25);
        }
        .pill-btn-ghost {
            background-color: #f1f5f9;
            color: #0f172a;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .pill-btn-ghost:hover {
            background-color: #e2e8f0;
            color: #000000;
        }
    </style>
</head>
<body class="antialiased selection:bg-slate-900 selection:text-white">

    <!-- 1. Top News / Announcement Bar (Centific Style) -->
    <div class="bg-[#1c1c1c] text-white text-xs py-2.5 px-4 text-center font-medium border-b border-zinc-800">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-3 flex-wrap">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-950/80 text-emerald-400 border border-emerald-800/60">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-2"></span>
                KAMERAKITA AI V2.0
            </span>
            <span class="text-zinc-300">Connect with KAMERAKITA AI to discover what's next in Vision AI Datasets.</span>
            <a href="#solutions" class="text-white font-semibold underline underline-offset-4 hover:text-emerald-400 transition-colors inline-flex items-center gap-1">
                See where to meet us →
            </a>
        </div>
    </div>

    <!-- 2. Header & Floating Glass Navigation -->
    <header x-data="{ mobileOpen: false, scrolled: false }"
            @scroll.window="scrolled = (window.pageYOffset > 15)"
            :class="scrolled ? 'bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs py-3.5' : 'bg-white py-5'"
            class="sticky top-0 z-50 transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            
            <!-- Centific Style Logo -->
            <a href="/" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-950 p-1.5 flex items-center justify-center shrink-0">
                    <img src="{{ asset('Logo.webp') }}" alt="KAMERAKITA AI Logo" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-extrabold tracking-tight text-slate-950 uppercase leading-none font-mono">
                        KAMERAKITA <span class="text-emerald-600">AI</span>
                    </span>
                    <span class="text-[9px] font-semibold text-slate-500 uppercase tracking-widest mt-0.5">Enterprise Vision Data</span>
                </div>
            </a>

            <!-- Desktop Menu Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-700 uppercase tracking-wider">
                <a href="#platform" class="hover:text-black transition-colors">Platform</a>
                <a href="#solutions" class="hover:text-black transition-colors">Solutions</a>
                <a href="#qc-engine" class="hover:text-black transition-colors">QC Engine</a>
                <a href="#contributors" class="hover:text-black transition-colors">Contributor Network</a>
                <a href="#use-cases" class="hover:text-black transition-colors">Use Cases</a>
            </nav>

            <!-- Action CTAs -->
            <div class="hidden md:flex items-center gap-3">
                <a href="#solutions" class="px-4 py-2 text-xs font-semibold text-slate-700 hover:text-black border border-slate-200 rounded-full hover:bg-slate-50 transition">
                    Multilingual AI
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="pill-btn-black px-5 py-2.5 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2">
                        Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 hover:text-black transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="pill-btn-black px-5 py-2.5 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2">
                        Book a demo
                    </a>
                @endauth
            </div>

            <!-- Mobile Toggle -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-slate-700 hover:text-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileOpen" x-collapse class="md:hidden bg-white border-b border-slate-200 px-4 py-4 space-y-3">
            <a href="#platform" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Platform</a>
            <a href="#solutions" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Solutions</a>
            <a href="#qc-engine" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">QC Engine</a>
            <a href="#contributors" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Contributor Network</a>
            <a href="#use-cases" @click="mobileOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Use Cases</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center pill-btn-black py-2.5 text-xs font-bold uppercase">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2 text-xs font-semibold text-slate-700 border border-slate-200 rounded-full">Sign In</a>
                    <a href="{{ route('register') }}" class="w-full text-center pill-btn-black py-2.5 text-xs font-bold uppercase">Book a demo</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 3. Centific Hero Section Blueprint -->
    <section id="platform" class="relative pt-16 pb-20 lg:pt-24 lg:pb-32 bg-grid-subtle overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Hero Content -->
                <div class="lg:col-span-7 space-y-8 text-left">
                    
                    <!-- Centific Eyebrow Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full badge-green text-xs font-bold font-mono tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        POWERING TOMORROW'S AI WITH HUMAN-TRAINED EXPERTISE
                    </div>

                    <!-- Centific Massive Headline H1 -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-950 tracking-tight leading-[1.08]">
                        Built for Companies Building the Future of <span class="text-emerald-700 underline decoration-emerald-300 decoration-wavy decoration-2">Vision AI</span>
                    </h1>

                    <!-- Centific Body Paragraph -->
                    <p class="text-base sm:text-lg text-slate-600 font-normal leading-relaxed max-w-2xl">
                        KAMERAKITA AI builds the data engines behind frontier models. We generate, refine, and operationalize real-world video signals across Indonesia through production-ready datasets, human feedback, and automated QC verification.
                    </p>

                    <!-- Centific Dual Pill Buttons -->
                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-2">
                        <a href="#solutions" class="pill-btn-black w-full sm:w-auto px-8 py-4 text-xs font-bold uppercase tracking-wider text-center">
                            Book a demo
                        </a>
                        <a href="{{ route('register') }}" class="pill-btn-ghost w-full sm:w-auto px-7 py-4 text-xs font-bold uppercase tracking-wider text-center">
                            Join our Expert Network →
                        </a>
                    </div>

                    <!-- Centific Checkmarks List -->
                    <div class="pt-6 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="flex items-center gap-3 text-xs font-semibold text-slate-800">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Global data pipelines for training at scale
                        </div>
                        <div class="flex items-center gap-3 text-xs font-semibold text-slate-800">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Automated labeling, curation & QC evidence
                        </div>
                        <div class="flex items-center gap-3 text-xs font-semibold text-slate-800">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Human feedback for model alignment & safety
                        </div>
                        <div class="flex items-center gap-3 text-xs font-semibold text-slate-800">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Continuous data loops for production AI
                        </div>
                    </div>

                </div>

                <!-- Right Hero Feature Display (QC Command Center Preview) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-2xl shadow-slate-200/80 space-y-6">
                        
                        <!-- Top Header inside Card -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-900 font-mono">QC COMMAND CENTER</span>
                            </div>
                            <span class="text-[10px] font-mono px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200">SYSTEM ACTIVE</span>
                        </div>

                        <!-- Live Metrics Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-150">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block font-mono">TOTAL VERIFIED</span>
                                <span class="text-2xl font-black text-slate-950 font-mono mt-1 block">48j 37m</span>
                            </div>
                            <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-150">
                                <span class="text-[10px] uppercase font-bold text-emerald-800 block font-mono">QC ACCURACY</span>
                                <span class="text-2xl font-black text-emerald-700 font-mono mt-1 block">99.8%</span>
                            </div>
                        </div>

                        <!-- Evidence Review Mockup Box -->
                        <div class="bg-slate-950 rounded-2xl p-4 text-white space-y-3 shadow-md">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-indigo-300">Mitra: KMK-018 (Muhammad)</span>
                                <span class="bg-emerald-500/20 text-emerald-400 font-bold px-2.5 py-0.5 rounded-full text-[10px] border border-emerald-500/30">VERIFIED</span>
                            </div>
                            <div class="aspect-video bg-slate-900 rounded-xl border border-slate-800 flex items-center justify-center relative overflow-hidden">
                                <img src="{{ asset('Logo.webp') }}" alt="Evidence Sample" class="w-10 h-10 opacity-40">
                                <div class="absolute bottom-3 left-3 text-[11px] font-mono text-slate-300 bg-slate-950/80 backdrop-blur-xs px-2.5 py-1 rounded-md border border-slate-800">
                                    <span>Submitted: 61 Menit</span> • <span class="text-emerald-400 font-bold">Cumulative: 183 Mins</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="text-center text-xs font-semibold text-slate-500">
                            Side-by-side evidence inspection & automated bulk payroll audit
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 4. Capability Cards Grid Section ("Tomorrow's AI Requires Data That Is...") -->
    <section id="solutions" class="py-24 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="max-w-3xl space-y-4 mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full badge-green text-xs font-bold font-mono">
                    EXPERT SOLUTIONS
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-950 tracking-tight">
                    Tomorrow's AI Requires Data That Is Production-Ready
                </h2>
                <p class="text-slate-600 text-base leading-relaxed">
                    AI doesn't fail because of models; it fails because of data that doesn't reflect the real world. KAMERAKITA AI's data products and human feedback services bridge the gap between raw video and reliable AI.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Solution Card 1 -->
                <div class="centific-card p-6 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold text-lg">
                            01
                        </div>
                        <h3 class="text-lg font-bold text-slate-950">Custom Video Collection</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            We generate and orchestrate multi-angle video datasets across vision, action recognition, speech, and human interaction across 34 provinces.
                        </p>
                    </div>
                    <a href="#contact" class="text-xs font-bold text-slate-950 font-mono inline-flex items-center gap-1 hover:text-emerald-700">
                        Learn more →
                    </a>
                </div>

                <!-- Solution Card 2 -->
                <div class="centific-card p-6 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center font-bold text-lg">
                            02
                        </div>
                        <h3 class="text-lg font-bold text-slate-950">Human-in-the-Loop QC</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Turn raw video into trusted AI data. We power QC pipelines at scale combining expert verifiers, screenshot alignment, and safety frameworks.
                        </p>
                    </div>
                    <a href="#qc-engine" class="text-xs font-bold text-slate-950 font-mono inline-flex items-center gap-1 hover:text-blue-700">
                        Learn more →
                    </a>
                </div>

                <!-- Solution Card 3 -->
                <div class="centific-card p-6 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center font-bold text-lg">
                            03
                        </div>
                        <h3 class="text-lg font-bold text-slate-950">AI Data Marketplace</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Your gateway to enterprise-grade video datasets. Instant CSV exports, duration summaries, and automated bank bulk transfer integration.
                        </p>
                    </div>
                    <a href="#contact" class="text-xs font-bold text-slate-950 font-mono inline-flex items-center gap-1 hover:text-indigo-700">
                        Learn more →
                    </a>
                </div>

                <!-- Solution Card 4 -->
                <div class="centific-card p-6 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-700 border border-violet-200 flex items-center justify-center font-bold text-lg">
                            04
                        </div>
                        <h3 class="text-lg font-bold text-slate-950">Multimodal Localization</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Train AI that understands regional context, culture, and gesture nuance across Southeast Asia with specialized contributor pools.
                        </p>
                    </div>
                    <a href="#contact" class="text-xs font-bold text-slate-950 font-mono inline-flex items-center gap-1 hover:text-violet-700">
                        Learn more →
                    </a>
                </div>

            </div>

        </div>
    </section>

    <!-- 5. Real-Time Stats Row (Centific Dark Accent) -->
    <section class="py-16 bg-[#121212] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-white font-mono">50,000+</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono">Verified Video Hours</span>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-emerald-400 font-mono">1,200+</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono">Active Contributors</span>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-blue-400 font-mono">99.8%</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono">QC Precision Rate</span>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-amber-400 font-mono">34</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono">Provinces Covered</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Expert Network Showcase (Centific Style) -->
    <section id="contributors" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-5 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full badge-green text-xs font-bold font-mono">
                        GLOBAL EXPERT NETWORK
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-950 tracking-tight leading-tight">
                        Help Shape the Next Generation of Intelligence
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                        Join our network of verified contributor partners, domain raters, and data collectors across Indonesia. Work flexibly, record quality evidence, and get paid weekly.
                    </p>
                    <a href="{{ route('register') }}" class="pill-btn-black inline-flex items-center gap-2 px-7 py-3.5 text-xs font-bold uppercase tracking-wider">
                        Join our network →
                    </a>
                </div>

                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 font-mono">VISION SPECIALIST</span>
                        <h4 class="text-base font-bold text-slate-950">Action & Motion Collector</h4>
                        <p class="text-xs text-slate-500">Record structured human motion datasets under natural environments.</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 font-mono">EVALUATION RATER</span>
                        <h4 class="text-base font-bold text-slate-950">Quality & Duration Verifier</h4>
                        <p class="text-xs text-slate-500">Audit submitted app screenshots and validate cumulative duration logs.</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 font-mono">MULTIMODAL ANNOTATOR</span>
                        <h4 class="text-base font-bold text-slate-950">Facial & Liveness Specialist</h4>
                        <p class="text-xs text-slate-500">Collect demographic-diverse facial samples for anti-spoofing models.</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-violet-700 font-mono">FIELD OPERATOR</span>
                        <h4 class="text-base font-bold text-slate-950">Regional Scenario Lead</h4>
                        <p class="text-xs text-slate-500">Manage local contributor pools across major cities and provinces.</p>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- 7. Bottom High-Impact CTA Banner (Centific Style) -->
    <section id="contact" class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#121212] text-white rounded-3xl p-10 sm:p-14 text-center space-y-8 shadow-2xl">
                
                <span class="text-xs font-bold font-mono uppercase tracking-widest text-emerald-400 bg-emerald-950/80 border border-emerald-800 px-4 py-1.5 rounded-full inline-block">
                    GET STARTED TODAY
                </span>

                <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                    Turn Data Into AI That Works
                </h2>

                <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                    Connect with our team to explore custom dataset acquisitions, get a live walkthrough of the QC Command Center, or join our contributor network.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                    <a href="mailto:contact@kamerakitaid.site" class="bg-white text-slate-950 hover:bg-slate-100 px-8 py-4 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg transition">
                        Get a live walkthrough
                    </a>
                    <a href="{{ route('register') }}" class="bg-zinc-800 text-white hover:bg-zinc-700 px-8 py-4 rounded-full text-xs font-bold uppercase tracking-wider border border-zinc-700 transition">
                        Talk to our team →
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- 8. Footer (Centific Style) -->
    <footer class="bg-white border-t border-slate-200 py-12 text-slate-600 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('Logo.webp') }}" alt="Logo" class="w-6 h-6">
                    <span class="text-sm font-black text-slate-950 font-mono">KAMERAKITA AI</span>
                </div>
                <p class="text-slate-500 text-xs leading-relaxed">
                    The Enterprise Platform for Vision AI Datasets, QC Operations, & Contributor Network in Indonesia.
                </p>
            </div>

            <div class="space-y-2">
                <span class="block text-slate-950 font-bold uppercase tracking-wider text-[11px] font-mono">Products</span>
                <ul class="space-y-2">
                    <li><a href="#solutions" class="hover:text-black transition">Custom Video Collection</a></li>
                    <li><a href="#solutions" class="hover:text-black transition">Human QC Command Center</a></li>
                    <li><a href="#solutions" class="hover:text-black transition">AI Data Marketplace</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-slate-950 font-bold uppercase tracking-wider text-[11px] font-mono">Quick Links</span>
                <ul class="space-y-2">
                    <li><a href="{{ route('login') }}" class="hover:text-black transition">QC Room Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-black transition">Contributor Register</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-black transition">Dashboard Admin</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-slate-950 font-bold uppercase tracking-wider text-[11px] font-mono">Company</span>
                <p class="text-slate-500">Official Site: <span class="text-slate-950 font-semibold font-mono">kamerakitaid.site</span></p>
                <p class="text-slate-500">PT KAMERAKITA AI Indonesia</p>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-100 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-[11px] text-slate-500">
            <p>© 2026 KAMERAKITA AI. All rights reserved.</p>
            <div class="flex items-center gap-6 font-medium">
                <a href="#" class="hover:text-slate-900">Privacy Policy</a>
                <a href="#" class="hover:text-slate-900">Terms of Use</a>
                <a href="#" class="hover:text-slate-900">Cookie Policy</a>
            </div>
        </div>
    </footer>

</body>
</html>
