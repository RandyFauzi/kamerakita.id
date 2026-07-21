<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KAMERAKITA AI — Powering Computer Vision & Multimodal AI Models</title>
    
    <!-- Meta Description & SEO -->
    <meta name="description" content="KAMERAKITA AI menyediakan dataset video berkualitas tinggi yang dikumpulkan oleh ribuan mitra terverifikasi, divalidasi dengan sistem QC presisi untuk AI Labs & Enterprise Computer Vision.">
    <link rel="icon" href="{{ asset('Logo.webp') }}" type="image/webp">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-mono-tech {
            font-family: 'JetBrains Mono', monospace;
        }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(99, 102, 241, 0.15) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .glow-indigo {
            box-shadow: 0 0 50px -10px rgba(99, 102, 241, 0.35);
        }
        .glow-cyan {
            box-shadow: 0 0 50px -10px rgba(6, 182, 212, 0.35);
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.2);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white overflow-x-hidden">

    <!-- 1. Top Announcement Bar (News Bar) -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border-b border-indigo-900/40 text-xs py-2.5 px-4 text-center relative z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 flex-wrap">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse mr-1.5"></span>
                KAMERAKITA AI V2.0
            </span>
            <span class="text-slate-300 font-medium">Powering Next-Gen Vision AI Datasets Across Indonesia & Southeast Asia</span>
            <a href="#solutions" class="text-indigo-400 font-bold hover:text-indigo-300 inline-flex items-center gap-1 transition-colors ml-1">
                Explore Enterprise Solutions 
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>

    <!-- 2. Glassmorphic Navigation Header -->
    <header x-data="{ mobileMenuOpen: false, scrolled: false }" 
            @scroll.window="scrolled = (window.pageYOffset > 20)"
            :class="scrolled ? 'bg-slate-950/90 backdrop-blur-xl border-b border-slate-800/80 shadow-2xl py-3.5' : 'bg-transparent py-5'"
            class="sticky top-0 z-40 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-cyan-400 p-0.5 shadow-lg shadow-indigo-500/30 transition-transform group-hover:scale-105">
                    <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('Logo.webp') }}" alt="KAMERAKITA AI Logo" class="w-6 h-6 object-contain">
                    </div>
                </div>
                <div>
                    <span class="text-lg font-black tracking-wider text-white flex items-center gap-1 font-mono-tech">
                        KAMERAKITA <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-cyan-400 bg-clip-text text-transparent">AI</span>
                    </span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest -mt-1 font-mono-tech">Dataset Engine</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-300">
                <a href="#about" class="hover:text-indigo-400 transition-colors">Platform</a>
                <a href="#capabilities" class="hover:text-indigo-400 transition-colors">Capabilities</a>
                <a href="#qc-engine" class="hover:text-indigo-400 transition-colors">QC Engine</a>
                <a href="#contributors" class="hover:text-indigo-400 transition-colors">Contributor Network</a>
                <a href="#use-cases" class="hover:text-indigo-400 transition-colors">Use Cases</a>
            </nav>

            <!-- Action CTAs -->
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition shadow-sm">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl font-bold text-xs text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-indigo-600 via-violet-600 to-cyan-500 hover:from-indigo-500 hover:to-cyan-400 text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-500/50 transition-all hover:scale-102">
                        Join as Contributor
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Toggle Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenuOpen" x-collapse class="md:hidden bg-slate-900 border-b border-slate-800 px-4 py-4 space-y-3">
            <a href="#about" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-300 py-1">Platform</a>
            <a href="#capabilities" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-300 py-1">Capabilities</a>
            <a href="#qc-engine" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-300 py-1">QC Engine</a>
            <a href="#contributors" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-300 py-1">Contributor Network</a>
            <a href="#use-cases" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-300 py-1">Use Cases</a>
            <div class="pt-3 border-t border-slate-800 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center px-4 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 text-white">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center px-4 py-2 rounded-xl font-bold text-xs bg-slate-800 text-white">Sign In</a>
                    <a href="{{ route('register') }}" class="w-full text-center px-4 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 text-white">Join as Contributor</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 3. Hero Section (Centific Blueprint Format) -->
    <section id="about" class="relative pt-12 pb-24 lg:pt-20 lg:pb-32 bg-grid-pattern overflow-hidden">
        <!-- Glow Orbs in Background -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-indigo-600/20 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-1/3 right-10 w-[400px] h-[300px] bg-cyan-500/15 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Hero Text Column -->
                <div class="lg:col-span-7 space-y-8 text-center lg:text-left">
                    
                    <!-- Eyebrow Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900/90 border border-indigo-500/30 text-indigo-300 text-xs font-bold font-mono-tech shadow-inner">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                        POWERING TOMORROW'S AI WITH REAL-WORLD VISION DATASETS
                    </div>

                    <!-- Main H1 Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1]">
                        Built for Companies Building the Future of <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-cyan-400 bg-clip-text text-transparent">Vision AI</span>
                    </h1>

                    <!-- Subheadline -->
                    <p class="text-base sm:text-lg text-slate-400 font-normal leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        KAMERAKITA AI is the hidden infrastructure behind world-class Computer Vision models. We generate, refine, and operationalize real-world video datasets across Indonesia through a distributed contributor network and multi-stage human QC.
                    </p>

                    <!-- Dual CTAs -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                        <a href="#solutions" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-black text-sm bg-gradient-to-r from-indigo-600 via-violet-600 to-cyan-500 hover:from-indigo-500 hover:to-cyan-400 text-white shadow-xl shadow-indigo-600/30 hover:shadow-indigo-500/50 transition-all transform hover:-translate-y-0.5 text-center">
                            Minta Akses Dataset / Book Demo
                        </a>
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-7 py-4 rounded-2xl font-bold text-sm bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 hover:border-indigo-500/50 transition text-center">
                            Gabung Jadi Mitra Kontributor →
                        </a>
                    </div>

                    <!-- Key Highlights Bullet List -->
                    <div class="pt-6 border-t border-slate-800/80 grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                        <div class="flex items-center gap-2.5 text-xs font-semibold text-slate-300">
                            <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Global data pipelines for training at scale
                        </div>
                        <div class="flex items-center gap-2.5 text-xs font-semibold text-slate-300">
                            <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Automated labeling, curation & QC evidence
                        </div>
                        <div class="flex items-center gap-2.5 text-xs font-semibold text-slate-300">
                            <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Human feedback for model alignment & safety
                        </div>
                        <div class="flex items-center gap-2.5 text-xs font-semibold text-slate-300">
                            <span class="p-1 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Continuous data loops for production AI
                        </div>
                    </div>

                </div>

                <!-- Right Hero Visual Showcase (QC Command Center Mockup) -->
                <div class="lg:col-span-5 relative">
                    <div class="glass-card rounded-3xl p-6 shadow-2xl relative glow-indigo border border-indigo-500/30 space-y-6">
                        
                        <!-- Top Card Header -->
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="text-xs font-black uppercase tracking-wider text-slate-300 font-mono-tech">QC COMMAND CENTER LIVE</span>
                            </div>
                            <span class="text-[10px] font-mono-tech px-2 py-1 bg-indigo-950 border border-indigo-800 rounded-lg text-indigo-300">SYSTEM ACTIVE</span>
                        </div>

                        <!-- Live Metrics Bar inside Card -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-900/90 rounded-2xl p-3.5 border border-slate-800">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block font-mono-tech">TOTAL VERIFIED</span>
                                <span class="text-xl font-black text-white font-mono-tech">48j 37m</span>
                            </div>
                            <div class="bg-slate-900/90 rounded-2xl p-3.5 border border-slate-800">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block font-mono-tech">QC ACCURACY</span>
                                <span class="text-xl font-black text-emerald-400 font-mono-tech">99.8%</span>
                            </div>
                        </div>

                        <!-- Mockup Evidence Inspection Box -->
                        <div class="bg-slate-950 rounded-2xl p-4 border border-slate-800 space-y-3">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-indigo-400">Mitra: KMK-018 (Muhammad)</span>
                                <span class="bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded text-[10px]">VERIFIED QC</span>
                            </div>
                            <div class="aspect-video bg-slate-900 rounded-xl border border-slate-800 flex items-center justify-center relative overflow-hidden group">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-80 z-10"></div>
                                <img src="{{ asset('Logo.webp') }}" alt="Evidence Sample" class="w-12 h-12 opacity-30 group-hover:scale-110 transition-transform">
                                <div class="absolute bottom-3 left-3 z-20 text-[11px] font-mono-tech text-slate-300">
                                    <span>Duration: 61 Menit</span> • <span class="text-cyan-400">Database Cumulative: 183 Mins</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer indicator -->
                        <div class="text-center">
                            <span class="text-[11px] text-slate-400 font-mono-tech">
                                Real-Time Evidence Verification & Multi-Layer Audit Trail
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 4. Interactive Capability Cards (Solutions & Products) -->
    <section id="capabilities" class="py-24 bg-slate-900/50 border-y border-slate-800/80 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="max-w-3xl mx-auto text-center space-y-4 mb-16">
                <span class="text-xs font-bold font-mono-tech uppercase tracking-widest text-indigo-400 bg-indigo-950/80 border border-indigo-800/80 px-3.5 py-1.5 rounded-full inline-block">
                    ENTERPRISE CAPABILITIES
                </span>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    Tomorrow's AI Requires Data That Reflects the Real World
                </h2>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                    AI doesn't fail because of models; it fails because of data that doesn't capture real-world variance. Explore our suite of dataset acquisition, QC validation, and contributor management engines.
                </p>
            </div>

            <!-- 4 Grid Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1 -->
                <div class="glass-card rounded-3xl p-6 flex flex-col justify-between space-y-6 transition-all">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-white">Custom Video Dataset Acquisition</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            We orchestrate structured video dataset collection across 34 provinces in Indonesia covering vision, action recognition, speech, and human interaction.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-indigo-400 font-mono-tech inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Explore Collection →
                    </span>
                </div>

                <!-- Card 2 -->
                <div class="glass-card rounded-3xl p-6 flex flex-col justify-between space-y-6 transition-all">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-white">Human-in-the-Loop QC Validation</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Every video submission is verified side-by-side against app screenshots, duration logs, and quality factors by our dedicated verifier team.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-cyan-400 font-mono-tech inline-flex items-center gap-1">
                        View QC Room →
                    </span>
                </div>

                <!-- Card 3 -->
                <div class="glass-card rounded-3xl p-6 flex flex-col justify-between space-y-6 transition-all">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/30 flex items-center justify-center text-violet-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-white">Dataset Marketplace & Bulk Export</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Access production-ready datasets with instant CSV exports, metadata annotations, and bank bulk payout integration.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-violet-400 font-mono-tech inline-flex items-center gap-1">
                        Browse Marketplace →
                    </span>
                </div>

                <!-- Card 4 -->
                <div class="glass-card rounded-3xl p-6 flex flex-col justify-between space-y-6 transition-all">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-white">Distributed Contributor Network</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Thousands of verified partner creators logged into our mobile ecosystem, ready to deliver targeted multi-scenario video footage.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-emerald-400 font-mono-tech inline-flex items-center gap-1">
                        Join Network →
                    </span>
                </div>

            </div>
        </div>
    </section>

    <!-- 5. Real-Time Stats Bar (Trust Indicators) -->
    <section class="py-16 bg-slate-950 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-white font-mono-tech">50,000+</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono-tech">Verified Video Hours</span>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-indigo-400 font-mono-tech">1,200+</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono-tech">Active Contributor Partners</span>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-emerald-400 font-mono-tech">99.8%</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono-tech">QC Verification Accuracy</span>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-black text-cyan-400 font-mono-tech">34</span>
                    <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider font-mono-tech">Provinces Covered</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Enterprise Use-Cases Showcase (Computer Vision & AI Labs) -->
    <section id="use-cases" class="py-24 bg-slate-900/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div class="space-y-3">
                    <span class="text-xs font-bold font-mono-tech uppercase tracking-widest text-cyan-400 bg-cyan-950/80 border border-cyan-800/80 px-3.5 py-1.5 rounded-full inline-block">
                        AI MODEL APPLICATIONS
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                        Powering Frontier Models Across Domains
                    </h2>
                </div>
                <p class="text-slate-400 text-sm max-w-md">
                    From autonomous perception to gesture analytics, our video datasets feed continuous training loops for enterprise AI teams.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Use Case 1 -->
                <div class="glass-card rounded-3xl p-8 space-y-4 relative overflow-hidden group">
                    <span class="text-xs font-bold font-mono-tech text-indigo-400 uppercase">COMPUTER VISION</span>
                    <h3 class="text-xl font-black text-white">Action Recognition & Gesture Analytics</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Multi-angle videos capturing human movement, gesture sequences, and activity classification under real-world lighting conditions.
                    </p>
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-300 font-mono-tech">
                        <span>Dataset Size: 12,000+ Hrs</span>
                        <span class="text-indigo-400 group-hover:translate-x-1 transition-transform">Details →</span>
                    </div>
                </div>

                <!-- Use Case 2 -->
                <div class="glass-card rounded-3xl p-8 space-y-4 relative overflow-hidden group">
                    <span class="text-xs font-bold font-mono-tech text-cyan-400 uppercase">MULTIMODAL AI</span>
                    <h3 class="text-xl font-black text-white">Facial Liveness & Security Verification</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        High-resolution facial video captures across diverse demographic profiles to train anti-spoofing and bio-authentication models.
                    </p>
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-300 font-mono-tech">
                        <span>Dataset Size: 8,500+ Hrs</span>
                        <span class="text-cyan-400 group-hover:translate-x-1 transition-transform">Details →</span>
                    </div>
                </div>

                <!-- Use Case 3 -->
                <div class="glass-card rounded-3xl p-8 space-y-4 relative overflow-hidden group">
                    <span class="text-xs font-bold font-mono-tech text-emerald-400 uppercase">ROBOTICS & PERCEPTION</span>
                    <h3 class="text-xl font-black text-white">Spatial Environment & Object Interaction</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        First-person (egocentric) and third-person video recordings of everyday object handling for embodied AI and robotics.
                    </p>
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-300 font-mono-tech">
                        <span>Dataset Size: 15,000+ Hrs</span>
                        <span class="text-emerald-400 group-hover:translate-x-1 transition-transform">Details →</span>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- 7. High-Impact Final CTA Banner -->
    <section id="solutions" class="py-20 relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="glass-card rounded-3xl p-10 sm:p-14 text-center space-y-8 relative overflow-hidden border border-indigo-500/40 glow-indigo">
                
                <span class="text-xs font-bold font-mono-tech uppercase tracking-widest text-indigo-300 bg-indigo-950/90 border border-indigo-800 px-4 py-1.5 rounded-full inline-block">
                    READY TO SCALE YOUR AI DATA?
                </span>

                <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                    Turn Raw Video Into Production-Grade AI Datasets Today
                </h2>

                <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                    Connect with our data operations team to request custom video acquisition, inspect dataset samples, or join our growing network of contributors.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                    <a href="mailto:contact@kamerakitaid.site" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-black text-sm bg-gradient-to-r from-indigo-600 via-violet-600 to-cyan-500 hover:from-indigo-500 hover:to-cyan-400 text-white shadow-xl shadow-indigo-600/40 transition">
                        Hubungi Tim Enterprise / Minta Demo
                    </a>
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-sm bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700 transition">
                        Daftar Sebagai Kontributor
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- 8. Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/80 py-12 text-slate-400 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('Logo.webp') }}" alt="Logo" class="w-6 h-6">
                    <span class="text-sm font-black text-white font-mono-tech">KAMERAKITA AI</span>
                </div>
                <p class="text-slate-500 text-xs leading-relaxed">
                    The Enterprise Platform for Vision AI Datasets, QC Operations, & Contributor Network in Indonesia.
                </p>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px] font-mono-tech">Solutions</span>
                <ul class="space-y-1.5">
                    <li><a href="#capabilities" class="hover:text-indigo-400 transition">Video Data Collection</a></li>
                    <li><a href="#capabilities" class="hover:text-indigo-400 transition">Human QC Validation</a></li>
                    <li><a href="#capabilities" class="hover:text-indigo-400 transition">Dataset Marketplace</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px] font-mono-tech">Platform</span>
                <ul class="space-y-1.5">
                    <li><a href="{{ route('login') }}" class="hover:text-indigo-400 transition">QC Room Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-indigo-400 transition">Contributor Register</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-indigo-400 transition">Dashboard Admin</a></li>
                </ul>
            </div>

            <div class="space-y-2">
                <span class="block text-white font-bold uppercase tracking-wider text-[11px] font-mono-tech">Company</span>
                <p class="text-slate-500">Official Domain: <span class="text-indigo-400 font-mono-tech">kamerakitaid.site</span></p>
                <p class="text-slate-500">PT Kamerakita AI Indonesia</p>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-900 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-[11px] font-mono-tech text-slate-500">
            <p>© 2026 KAMERAKITA AI. All rights reserved.</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-slate-300">Privacy Policy</a>
                <a href="#" class="hover:text-slate-300">Terms of Use</a>
                <a href="#" class="hover:text-slate-300">Security & QC Standards</a>
            </div>
        </div>
    </footer>

</body>
</html>
