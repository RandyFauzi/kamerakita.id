<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Powering Tomorrow's AI with World-Class Data | KAMERAKITA AI</title>
    
    <!-- Meta Description -->
    <meta name="description" content="We help model labs and enterprises build, train, deploy, and govern intelligent systems through high-quality video datasets, human expertise, and end-to-end QC platforms.">
    <link rel="icon" href="{{ asset('images/Logo.webp') }}" type="image/webp">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
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
            background-color: #0b0d14;
            color: #ffffff;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* 3D Canvas Background & Mesh Blur Overlay */
        #hero-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            filter: blur(50px);
            opacity: 0.95;
            transform: scale(1.1);
        }

        .hero-content-layer {
            position: relative;
            z-index: 10;
        }

        .centific-white-btn {
            background-color: #ffffff;
            color: #000000;
            font-weight: 700;
            border-radius: 9999px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .centific-white-btn:hover {
            background-color: #f1f5f9;
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(255, 255, 255, 0.3);
        }

        .centific-ghost-btn {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
            transition: all 0.2s ease;
        }
        .centific-ghost-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .centific-card-dark {
            background: rgba(18, 20, 29, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            transition: all 0.25s ease;
        }
        .centific-card-dark:hover {
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }

        .logo-ticker img {
            filter: brightness(0) invert(1);
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }
        .logo-ticker img:hover {
            opacity: 1;
        }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500 selection:text-white">

    <!-- HERO SECTION WITH 3D ANIMATED MESH GRADIENT -->
    <div class="relative min-h-screen flex flex-col justify-between overflow-hidden">
        
        <!-- Interactive 3D Canvas Background -->
        <canvas id="hero-canvas"></canvas>
        
        <!-- Dark Gradient Overlay for Readability -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-[#0b0d14] z-2 pointer-events-none"></div>

        <!-- 1. Top Glassmorphic Navigation Bar (Exact Centific Header Layout) -->
        <header x-data="{ mobileOpen: false }" class="hero-content-layer w-full pt-4 pb-2 px-4 sm:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-2xl bg-slate-900/90 border border-slate-700/80 p-1.5 flex items-center justify-center overflow-hidden shadow-lg shadow-black/50">
                        <img src="{{ asset('images/Logo.webp') }}" alt="KAMERAKITA AI Logo" class="w-full h-full object-contain rounded-xl">
                    </div>
                    <span class="text-xl font-black tracking-tight text-white flex items-center gap-1 font-mono">
                        KAMERAKITA<span class="text-emerald-400 font-extrabold">AI</span>
                    </span>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-200">
                    <a href="#solutions" class="hover:text-white transition-colors flex items-center gap-1">Product <span class="text-slate-400">+</span></a>
                    <a href="#qc-engine" class="hover:text-white transition-colors flex items-center gap-1">Research <span class="text-slate-400">+</span></a>
                    <a href="#use-cases" class="hover:text-white transition-colors flex items-center gap-1">Leaderboards <span class="text-slate-400">+</span></a>
                    <a href="#contributors" class="hover:text-white transition-colors flex items-center gap-1">Resources <span class="text-slate-400">+</span></a>
                    <a href="#company" class="hover:text-white transition-colors flex items-center gap-1">Company <span class="text-slate-400">+</span></a>
                </nav>

                <!-- Action CTAs -->
                <div class="hidden md:flex items-center gap-3">
                    <a href="#solutions" class="centific-ghost-btn px-5 py-2.5 text-xs font-semibold tracking-wide inline-flex items-center gap-1.5">
                        Multilingual AI 
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="centific-white-btn px-6 py-2.5 text-xs uppercase tracking-wider inline-flex items-center">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="centific-white-btn px-6 py-2.5 text-xs uppercase tracking-wider inline-flex items-center">
                            Book a demo
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Toggle -->
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                </button>
            </div>

            <!-- Mobile Drawer -->
            <div x-show="mobileOpen" x-collapse class="lg:hidden bg-slate-950/95 border-b border-slate-800 px-4 py-4 space-y-3 mt-3 rounded-2xl">
                <a href="#solutions" @click="mobileOpen = false" class="block text-sm font-medium text-slate-200">Product</a>
                <a href="#qc-engine" @click="mobileOpen = false" class="block text-sm font-medium text-slate-200">Research</a>
                <a href="#use-cases" @click="mobileOpen = false" class="block text-sm font-medium text-slate-200">Leaderboards</a>
                <a href="#contributors" @click="mobileOpen = false" class="block text-sm font-medium text-slate-200">Resources</a>
                <div class="pt-3 border-t border-slate-800 flex flex-col gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full text-center centific-white-btn py-2.5 text-xs uppercase">Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="w-full text-center centific-white-btn py-2.5 text-xs uppercase">Book a demo</a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- 2. Centific News Bar (Directly below header) -->
        <div class="hero-content-layer w-full my-4 px-4">
            <div class="max-w-fit mx-auto bg-black/60 backdrop-blur-md border border-white/10 rounded-full px-5 py-1.5 text-xs flex items-center gap-2 text-slate-300">
                <span class="w-2 h-2 rounded-full bg-pink-500 animate-pulse"></span>
                <span>Connect with KAMERAKITA AI to discover what's next in Vision AI Datasets.</span>
                <a href="#solutions" class="text-white font-bold hover:underline inline-flex items-center gap-1">
                    See where to meet us →
                </a>
            </div>
        </div>

        <!-- 3. Centific Hero Content (Identical Layout to Screenshot) -->
        <main class="hero-content-layer max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-left py-12 lg:py-20 my-auto space-y-8">
            
            <!-- Headline H1 -->
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-semibold text-white tracking-tight leading-[1.06] max-w-4xl">
                Powering Tomorrow's AI with <br class="hidden sm:block"/>
                <span class="font-normal text-slate-200">World-Class Vision Data</span>
            </h1>

            <!-- Subheadline Paragraph -->
            <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed max-w-2xl">
                We help model labs and enterprises build, train, deploy, and govern intelligent vision systems through high-quality video datasets, human expertise, and end-to-end platforms that turn complexity into scalable, real-world impact.
            </p>

            <!-- Hero Action Button -->
            <div class="pt-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="centific-white-btn inline-block px-8 py-4 text-sm font-bold shadow-2xl">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="centific-white-btn inline-block px-8 py-4 text-sm font-bold shadow-2xl">
                        Book a Demo
                    </a>
                @endauth
            </div>

        </main>

        <!-- 4. Client / Partner Logo Ticker (As shown in screenshot) -->
        <div class="hero-content-layer w-full border-t border-white/10 bg-black/40 backdrop-blur-md py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                    The hidden infrastructure behind world-class AI models
                </p>
                <div class="logo-ticker flex items-center justify-center flex-wrap gap-8 sm:gap-14">
                    <span class="text-lg font-black tracking-widest text-white/80 font-mono">NVIDIA</span>
                    <span class="text-lg font-black tracking-widest text-white/80 font-mono">xAI</span>
                    <span class="text-lg font-black tracking-widest text-white/80 font-mono">AWS</span>
                    <span class="text-lg font-black tracking-widest text-white/80 font-mono">ANTHROPIC</span>
                    <span class="text-lg font-black tracking-widest text-white/80 font-mono">COHERE</span>
                    <span class="text-lg font-black tracking-widest text-white/80 font-mono">DELL</span>
                    <span class="text-lg font-black tracking-widest text-white/80 font-mono">GOOGLE</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 5. Centific Capabilities Grid Section -->
    <section id="solutions" class="py-28 bg-[#0e1017] border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="max-w-3xl space-y-4 mb-20">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950/80 border border-emerald-800 px-3.5 py-1.5 rounded-full inline-block">
                    EXPERT SOLUTIONS
                </span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Tomorrow's AI Requires Data That Reflects the Real World
                </h2>
                <p class="text-slate-400 text-base leading-relaxed">
                    AI doesn't fail because of models; it fails because of data that doesn't capture real-world variance. KAMERAKITA AI's data products and human feedback services bridge the gap between raw video and reliable AI.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1 -->
                <div class="centific-card-dark p-8 flex flex-col justify-between space-y-8">
                    <div class="space-y-4">
                        <span class="text-xs font-bold text-emerald-400 font-mono uppercase">01 / DATASET ENGINE</span>
                        <h3 class="text-xl font-bold text-white">Custom Video Collection</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            We generate and orchestrate multi-angle video datasets across vision, action recognition, speech, and human interaction across 34 provinces.
                        </p>
                    </div>
                    <a href="#contact" class="text-xs font-bold text-white hover:text-emerald-400 transition inline-flex items-center gap-1">
                        Learn more →
                    </a>
                </div>

                <!-- Card 2 -->
                <div class="centific-card-dark p-8 flex flex-col justify-between space-y-8">
                    <div class="space-y-4">
                        <span class="text-xs font-bold text-indigo-400 font-mono uppercase">02 / QC COMMAND CENTER</span>
                        <h3 class="text-xl font-bold text-white">Human-in-the-Loop QC</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Turn raw video into trusted AI data. We power QC pipelines at scale combining expert verifiers, screenshot alignment, and safety frameworks.
                        </p>
                    </div>
                    <a href="#qc-engine" class="text-xs font-bold text-white hover:text-indigo-400 transition inline-flex items-center gap-1">
                        Learn more →
                    </a>
                </div>

                <!-- Card 3 -->
                <div class="centific-card-dark p-8 flex flex-col justify-between space-y-8">
                    <div class="space-y-4">
                        <span class="text-xs font-bold text-cyan-400 font-mono uppercase">03 / BULK MARKETPLACE</span>
                        <h3 class="text-xl font-bold text-white">AI Data Marketplace</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Your gateway to enterprise-grade video datasets. Instant CSV exports, duration summaries, and automated bank bulk transfer integration.
                        </p>
                    </div>
                    <a href="#contact" class="text-xs font-bold text-white hover:text-cyan-400 transition inline-flex items-center gap-1">
                        Learn more →
                    </a>
                </div>

                <!-- Card 4 -->
                <div class="centific-card-dark p-8 flex flex-col justify-between space-y-8">
                    <div class="space-y-4">
                        <span class="text-xs font-bold text-violet-400 font-mono uppercase">04 / MULTIMODAL AI</span>
                        <h3 class="text-xl font-bold text-white">Multimodal Localization</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Train AI that understands regional context, culture, and gesture nuance across Southeast Asia with specialized contributor pools.
                        </p>
                    </div>
                    <a href="#contact" class="text-xs font-bold text-white hover:text-violet-400 transition inline-flex items-center gap-1">
                        Learn more →
                    </a>
                </div>

            </div>

        </div>
    </section>

    <!-- 6. Real-Time QC Command Center Showcase -->
    <section id="qc-engine" class="py-24 bg-[#0b0d14] border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-6 space-y-6">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-400 bg-indigo-950/80 border border-indigo-800 px-3.5 py-1.5 rounded-full inline-block">
                        AUTOMATED VERIFICATION
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Real-Time Side-by-Side Evidence Inspection & Cumulative Audit
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                        Our verifiers validate app screenshots, duration logs, and partner submissions in real-time. Automatically cross-reference cumulative dataset minutes in our database with contributor evidence.
                    </p>
                    <div class="pt-4 grid grid-cols-2 gap-4">
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4">
                            <span class="block text-2xl font-black text-white font-mono">50,000+</span>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Verified Hours</span>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4">
                            <span class="block text-2xl font-black text-emerald-400 font-mono">99.8%</span>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Precision Rate</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <div class="centific-card-dark p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <span class="text-xs font-bold text-slate-300 font-mono">QC ROOM REVIEW MODAL</span>
                            <span class="bg-emerald-500/20 text-emerald-400 text-[10px] font-bold px-2.5 py-1 rounded-full border border-emerald-500/30">DATABASE CUMULATIVE: 183 MINS</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 text-xs">
                                <span class="text-slate-500 block font-mono text-[10px]">EVIDENCE 1</span>
                                <span class="font-bold text-white">Screenshot Total Durasi</span>
                            </div>
                            <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 text-xs">
                                <span class="text-slate-500 block font-mono text-[10px]">EVIDENCE 2</span>
                                <span class="font-bold text-white">Screenshot Kualitas Video</span>
                            </div>
                        </div>
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs flex justify-between items-center text-slate-300">
                            <span>Mitra: KMK-018 (Muhammad)</span>
                            <span class="text-emerald-400 font-mono font-bold">Durasi: 61 Menit</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 7. High-Impact Bottom CTA Banner -->
    <section id="company" class="py-24 bg-[#0e1017] border-t border-slate-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-b from-slate-900 to-black rounded-3xl p-10 sm:p-16 text-center space-y-8 border border-white/10 shadow-2xl">
                
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950/80 border border-emerald-800 px-4 py-1.5 rounded-full inline-block">
                    GET STARTED TODAY
                </span>

                <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Turn Data Into AI That Works
                </h2>

                <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                    Connect with our team to explore custom dataset acquisitions, get a live walkthrough of the QC Command Center, or join our contributor network.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                    <a href="mailto:contact@kamerakitaid.site" class="centific-white-btn px-8 py-4 text-xs uppercase tracking-wider">
                        Get a live walkthrough
                    </a>
                    <a href="{{ route('register') }}" class="centific-ghost-btn px-8 py-4 text-xs uppercase tracking-wider">
                        Talk to our team →
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- 8. Centific Footer -->
    <footer class="bg-[#0b0d14] border-t border-slate-800/80 py-12 text-slate-400 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            
            <div class="space-y-3">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/Logo.webp') }}" alt="Logo" class="w-7 h-7 rounded-lg object-contain">
                    <span class="text-sm font-black text-white font-mono">KAMERAKITA AI</span>
                </div>
                <p class="text-slate-500 text-xs leading-relaxed">
                    The Enterprise Platform for Vision AI Datasets, QC Operations, & Contributor Network in Indonesia.
                </p>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px] font-mono">Products</span>
                <ul class="space-y-2">
                    <li><a href="#solutions" class="hover:text-white transition">Custom Video Collection</a></li>
                    <li><a href="#qc-engine" class="hover:text-white transition">Human QC Command Center</a></li>
                    <li><a href="#solutions" class="hover:text-white transition">AI Data Marketplace</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px] font-mono">Quick Links</span>
                <ul class="space-y-2">
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">QC Room Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Contributor Register</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">Dashboard Admin</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px] font-mono">Company</span>
                <p class="text-slate-500">Official Site: <span class="text-white font-mono font-semibold">kamerakitaid.site</span></p>
                <p class="text-slate-500">PT KAMERAKITA AI Indonesia</p>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-900 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-[11px] text-slate-500">
            <p>© 2026 KAMERAKITA AI. All rights reserved.</p>
            <div class="flex items-center gap-6 font-medium">
                <a href="#" class="hover:text-slate-300">Privacy Policy</a>
                <a href="#" class="hover:text-slate-300">Terms of Use</a>
                <a href="#" class="hover:text-slate-300">Cookie Policy</a>
            </div>
        </div>
    </footer>

    <!-- Interactive HTML5 3D Animated Silk Mesh Gradient Canvas Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('hero-canvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            let width, height;

            function resize() {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            }
            window.addEventListener('resize', resize);
            resize();

            let mouseX = width / 2;
            let mouseY = height / 2;

            window.addEventListener('mousemove', (e) => {
                mouseX += (e.clientX - mouseX) * 0.05;
                mouseY += (e.clientY - mouseY) * 0.05;
            });

            let time = 0;

            function drawMesh() {
                time += 0.0025;
                ctx.clearRect(0, 0, width, height);

                // Deep Obsidian Base
                ctx.fillStyle = '#0b0d14';
                ctx.fillRect(0, 0, width, height);

                // 1. Violet Indigo Silk Wave
                const x1 = width * 0.35 + Math.sin(time * 0.7) * 220 + (mouseX - width/2) * 0.15;
                const y1 = height * 0.45 + Math.cos(time * 0.9) * 160 + (mouseY - height/2) * 0.15;
                const r1 = Math.max(width, height) * 0.55;
                const g1 = ctx.createRadialGradient(x1, y1, 0, x1, y1, r1);
                g1.addColorStop(0, 'rgba(99, 102, 241, 0.55)');
                g1.addColorStop(0.4, 'rgba(76, 29, 149, 0.35)');
                g1.addColorStop(1, 'transparent');
                ctx.fillStyle = g1;
                ctx.fillRect(0, 0, width, height);

                // 2. Silk Magenta / Deep Pink Blob (As in Centific Screenshot)
                const x2 = width * 0.65 + Math.cos(time * 0.6) * 250 - (mouseX - width/2) * 0.1;
                const y2 = height * 0.35 + Math.sin(time * 1.1) * 180 - (mouseY - height/2) * 0.1;
                const r2 = Math.max(width, height) * 0.5;
                const g2 = ctx.createRadialGradient(x2, y2, 0, x2, y2, r2);
                g2.addColorStop(0, 'rgba(219, 39, 119, 0.45)');
                g2.addColorStop(0.5, 'rgba(131, 24, 67, 0.25)');
                g2.addColorStop(1, 'transparent');
                ctx.fillStyle = g2;
                ctx.fillRect(0, 0, width, height);

                // 3. Emerald Silk Wave
                const x3 = width * 0.5 + Math.sin(time * 1.1) * 260;
                const y3 = height * 0.75 + Math.cos(time * 0.8) * 190;
                const r3 = Math.max(width, height) * 0.6;
                const g3 = ctx.createRadialGradient(x3, y3, 0, x3, y3, r3);
                g3.addColorStop(0, 'rgba(16, 185, 129, 0.38)');
                g3.addColorStop(0.5, 'rgba(5, 150, 105, 0.18)');
                g3.addColorStop(1, 'transparent');
                ctx.fillStyle = g3;
                ctx.fillRect(0, 0, width, height);

                // 4. Cyan Glow Accent
                const x4 = width * 0.2 + Math.cos(time * 1.3) * 180;
                const y4 = height * 0.7 + Math.sin(time * 0.5) * 150;
                const r4 = Math.max(width, height) * 0.4;
                const g4 = ctx.createRadialGradient(x4, y4, 0, x4, y4, r4);
                g4.addColorStop(0, 'rgba(6, 182, 212, 0.35)');
                g4.addColorStop(1, 'transparent');
                ctx.fillStyle = g4;
                ctx.fillRect(0, 0, width, height);

                requestAnimationFrame(drawMesh);
            }

            drawMesh();
        });
    </script>
</body>
</html>
