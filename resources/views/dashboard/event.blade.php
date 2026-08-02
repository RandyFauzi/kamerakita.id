<x-app-layout>
    <!-- Sci-Fi / Robotic Container -->
    <div class="relative min-h-[calc(100vh-4rem)] bg-slate-950 text-cyan-50 font-sans overflow-hidden -m-4 sm:-m-6 lg:-m-8 p-4 sm:p-6 lg:p-8" style="background-image: linear-gradient(rgba(6, 182, 212, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(6, 182, 212, 0.05) 1px, transparent 1px); background-size: 30px 30px; background-position: center center;">
        
        <!-- Animated scanline overlay -->
        <div class="absolute inset-0 pointer-events-none z-50 opacity-10 bg-[linear-gradient(to_bottom,transparent_50%,rgba(0,0,0,0.5)_51%)]" style="background-size: 100% 4px;"></div>
        
        <!-- Animated glowing orbs -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-lime-500 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-pulse" style="animation-delay: 2s;"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <!-- Header Section -->
            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-cyan-900/50 pb-6">
                <div class="relative">
                    <!-- Glitch effect pseudo-element (handled via CSS classes or simple layout) -->
                    <h1 class="text-3xl sm:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-emerald-400 tracking-widest uppercase relative inline-block">
                        <span class="absolute -left-[2px] -top-[2px] text-red-500 opacity-50 mix-blend-screen animate-pulse">SYS.EVENT // {{ $eventName }}</span>
                        SYS.EVENT // {{ $eventName }}
                    </h1>
                    <div class="flex items-center gap-3 mt-3">
                        <div class="h-px w-12 bg-cyan-500 shadow-[0_0_10px_rgba(6,182,212,0.8)]"></div>
                        <p class="text-xs text-cyan-500 font-mono tracking-widest uppercase">
                            STATUS: <span class="text-lime-400 animate-pulse">ACTIVE_MONITORING</span>
                        </p>
                    </div>
                </div>
                
                <!-- Live Date Badge -->
                <div class="flex flex-col items-end">
                    <div class="flex items-center gap-3 bg-slate-900/80 backdrop-blur-md px-4 py-2 rounded border border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.15)]">
                        <svg class="w-4 h-4 text-cyan-400 animate-spin" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="text-sm font-mono font-bold text-cyan-100 tracking-wider" id="live-clock">--:--:--</span>
                    </div>
                    <span class="text-[10px] text-cyan-700 font-mono mt-1 uppercase tracking-widest">LOCAL_SYS_TIME</span>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 lg:gap-8">
                
                <!-- Main Target Data Console (Left side - spans 3 cols) -->
                <div class="xl:col-span-3 relative group">
                    <!-- Robotic Corner Accents -->
                    <div class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-cyan-400 group-hover:scale-125 transition-transform duration-300"></div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-cyan-400 group-hover:scale-125 transition-transform duration-300"></div>
                    <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-cyan-400 group-hover:scale-125 transition-transform duration-300"></div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-cyan-400 group-hover:scale-125 transition-transform duration-300"></div>

                    <div class="bg-slate-900/60 backdrop-blur-md rounded border border-slate-700/50 p-6 sm:p-10 h-full flex flex-col justify-between shadow-[inset_0_0_30px_rgba(0,0,0,0.8)] relative overflow-hidden">
                        
                        <!-- Internal scanline -->
                        <div class="absolute left-0 right-0 h-1 bg-cyan-400/20 top-0 animate-[scan_3s_ease-in-out_infinite]"></div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3 bg-slate-950/80 px-4 py-2 border-l-4 border-lime-400 rounded-r">
                                <span class="text-[10px] sm:text-xs font-mono font-bold text-lime-400 uppercase tracking-widest">
                                    DATA_RANGE // {{ $periodTarget ? $periodTarget['start']->translatedFormat('d M') . ' - ' . $periodTarget['end']->translatedFormat('d M Y') : 'ALL' }}
                                </span>
                            </div>
                            <div class="px-3 py-1 bg-cyan-950/50 border border-cyan-800 text-cyan-400 text-xs font-mono rounded">
                                TARGET_VOL: {{ $targetHours }} HRS
                            </div>
                        </div>

                        <!-- Data Display -->
                        <div class="mb-12">
                            <h2 class="font-mono text-7xl sm:text-8xl md:text-9xl font-black text-transparent bg-clip-text bg-gradient-to-b from-white to-slate-500 tracking-tighter drop-shadow-[0_0_20px_rgba(255,255,255,0.1)] flex items-baseline gap-2">
                                {{ number_format($totalHours, 1) }} 
                                <span class="text-2xl sm:text-4xl text-cyan-600 font-bold tracking-normal">HRS</span>
                            </h2>
                            <p class="text-sm font-mono text-slate-400 mt-4 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-2 h-2 bg-lime-400 rounded-full animate-ping"></span> 
                                VOL_ACQUIRED_FROM_DATASET
                            </p>
                        </div>

                        <!-- The Robotic Progress Bar -->
                        <div class="relative mt-auto">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">UPLOAD_PROGRESS</span>
                                <span class="text-lg font-mono font-black text-lime-400 drop-shadow-[0_0_10px_rgba(163,230,53,0.8)]">{{ number_format($rawPercentage, 1) }}%</span>
                            </div>
                            
                            <!-- Track -->
                            <div class="h-8 bg-slate-950 border border-slate-800 rounded flex items-center p-1 relative overflow-hidden">
                                <!-- Grid lines inside track -->
                                <div class="absolute inset-0 opacity-20" style="background-image: repeating-linear-gradient(90deg, transparent, transparent 10px, #334155 10px, #334155 11px);"></div>
                                
                                <!-- Fill -->
                                <div class="h-full bg-gradient-to-r from-cyan-600 via-emerald-500 to-lime-400 rounded-sm relative shadow-[0_0_15px_rgba(163,230,53,0.6)] transition-all duration-1000 ease-out" 
                                     style="width: {{ $progressPercentage > 5 ? $progressPercentage : 5 }}%">
                                     <!-- Leading Edge Glow -->
                                     <div class="absolute right-0 top-0 bottom-0 w-2 bg-white blur-[2px]"></div>
                                </div>
                            </div>
                            
                            <!-- Target Indicator -->
                            <div class="absolute top-0 bottom-0 right-0 w-px bg-red-500/50 border-r border-dashed border-red-500"></div>
                            <span class="absolute -bottom-6 right-0 text-[10px] text-red-400 font-mono uppercase">TARGET_LOCK</span>
                        </div>
                    </div>
                </div>

                <!-- Cinematic Countdown Console (Right side - spans 2 cols) -->
                <div class="xl:col-span-2 relative group flex flex-col">
                    <!-- Robotic Corner Accents -->
                    <div class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-red-500 group-hover:scale-125 transition-transform duration-300"></div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-red-500 group-hover:scale-125 transition-transform duration-300"></div>
                    <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-red-500 group-hover:scale-125 transition-transform duration-300"></div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-red-500 group-hover:scale-125 transition-transform duration-300"></div>

                    <div class="bg-black border border-red-900/30 p-6 sm:p-10 h-full flex flex-col items-center justify-center relative overflow-hidden shadow-[inset_0_0_50px_rgba(220,38,38,0.1)]">
                        <!-- Warning stripes overlay -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-[repeating-linear-gradient(45deg,#dc2626,#dc2626_10px,transparent_10px,transparent_20px)] opacity-50"></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-[repeating-linear-gradient(45deg,#dc2626,#dc2626_10px,transparent_10px,transparent_20px)] opacity-50"></div>

                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(220,38,38,0.15)_0%,transparent_70%)] pointer-events-none"></div>
                        
                        <div class="flex items-center gap-2 mb-8 relative z-10">
                            <svg class="w-5 h-5 text-red-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <h3 class="text-xs sm:text-sm font-bold text-red-500 uppercase tracking-[0.3em] font-mono">
                                T-MINUS_COUNTDOWN
                            </h3>
                        </div>

                        <div id="timer-container" class="flex items-start justify-center gap-1 sm:gap-2 lg:gap-3 font-mono font-bold text-3xl sm:text-5xl md:text-6xl tracking-tighter relative z-10 w-full">
                            <!-- Months -->
                            <div class="flex flex-col items-center flex-1">
                                <span id="mths" class="text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.5)] bg-slate-900 w-full text-center py-2 rounded-t border-b border-slate-700">00</span>
                                <span class="text-[9px] sm:text-[10px] text-red-400 mt-2 font-mono uppercase tracking-widest">Mths</span>
                            </div>
                            <span class="text-red-600 animate-pulse mt-2">:</span>
                            
                            <!-- Days -->
                            <div class="flex flex-col items-center flex-1">
                                <span id="days" class="text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.5)] bg-slate-900 w-full text-center py-2 rounded-t border-b border-slate-700">00</span>
                                <span class="text-[9px] sm:text-[10px] text-red-400 mt-2 font-mono uppercase tracking-widest">Days</span>
                            </div>
                            <span class="text-red-600 animate-pulse mt-2">:</span>
                            
                            <!-- Hours -->
                            <div class="flex flex-col items-center flex-1">
                                <span id="hours" class="text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.5)] bg-slate-900 w-full text-center py-2 rounded-t border-b border-slate-700">00</span>
                                <span class="text-[9px] sm:text-[10px] text-red-400 mt-2 font-mono uppercase tracking-widest">Hrs</span>
                            </div>
                            <span class="text-red-600 animate-pulse mt-2">:</span>
                            
                            <!-- Minutes -->
                            <div class="flex flex-col items-center flex-1">
                                <span id="mins" class="text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.5)] bg-slate-900 w-full text-center py-2 rounded-t border-b border-slate-700">00</span>
                                <span class="text-[9px] sm:text-[10px] text-red-400 mt-2 font-mono uppercase tracking-widest">Mins</span>
                            </div>
                            <span class="text-red-600 animate-pulse mt-2">:</span>
                            
                            <!-- Seconds -->
                            <div class="flex flex-col items-center flex-1 overflow-hidden">
                                <div class="bg-slate-900 w-full text-center py-2 rounded-t border-b border-slate-700 relative overflow-hidden h-[1.3em]">
                                    <span id="secs" class="text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.5)] absolute inset-0 flex items-center justify-center transition-all duration-150">00</span>
                                </div>
                                <span class="text-[9px] sm:text-[10px] text-red-400 mt-2 font-mono uppercase tracking-widest">Secs</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script for Live Clock & Countdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Live Clock
            function updateClock() {
                const now = new Date();
                document.getElementById('live-clock').innerText = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
            setInterval(updateClock, 1000);
            updateClock();
        });

        // Update the countdown timer every second
        const deadline = new Date("{{ $targetDeadline->format('Y-m-d\TH:i:s') }}").getTime();

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = deadline - now;

            // Waktu habis
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("timer-container").innerHTML = "<div class='text-3xl sm:text-5xl font-mono font-black text-red-500 tracking-widest uppercase animate-pulse shadow-red-500 drop-shadow-[0_0_20px_rgba(220,38,38,0.8)]'>SYS_OFFLINE</div>";
                return;
            }

            const mths = Math.floor(distance / (1000 * 60 * 60 * 24 * 30));
            const days = Math.floor((distance % (1000 * 60 * 60 * 24 * 30)) / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const pad = (num) => num.toString().padStart(2, '0');

            if(document.getElementById("mths")) document.getElementById("mths").innerHTML = pad(mths);
            if(document.getElementById("days")) document.getElementById("days").innerHTML = pad(days);
            if(document.getElementById("hours")) document.getElementById("hours").innerHTML = pad(hours);
            if(document.getElementById("mins")) document.getElementById("mins").innerHTML = pad(minutes);
            
            // Animasi flip / drop untuk detik
            const secElement = document.getElementById("secs");
            if(secElement) {
                if(secElement.innerHTML !== pad(seconds)) {
                    secElement.style.transform = 'translateY(-100%)';
                    secElement.style.opacity = '0';
                    setTimeout(() => {
                        secElement.innerHTML = pad(seconds);
                        secElement.style.transform = 'translateY(100%)';
                        
                        setTimeout(() => {
                            secElement.style.transform = 'translateY(0)';
                            secElement.style.opacity = '1';
                        }, 50);
                    }, 100);
                }
            }

        }, 1000);
    </script>

    <style>
        /* Custom Scanline Animation injected directly for Tailwind */
        @keyframes scan {
            0% { top: 0; opacity: 1; }
            50% { opacity: 0; }
            100% { top: 100%; opacity: 1; }
        }
    </style>
</x-app-layout>
