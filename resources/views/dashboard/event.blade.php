<x-app-layout>
    <!-- Full Screen Wrapper for Video Background -->
    <div class="relative min-h-[calc(100vh-4rem)] -m-4 sm:-m-6 lg:-m-8 overflow-hidden flex flex-col items-center justify-center bg-[#07090b]">
        
        <!-- Video Background -->
        <div class="absolute inset-0 w-full h-full z-0 overflow-hidden pointer-events-none">
            <!-- Cipher Digital Video (Webm & MP4 Fallback) -->
            <video autoplay loop muted playsinline class="absolute top-1/2 left-1/2 min-w-full min-h-full w-auto h-auto object-cover transform -translate-x-1/2 -translate-y-1/2 opacity-75 mix-blend-screen filter contrast-125">
                <source src="https://cipherdigital.com/wp-content/uploads/2026/04/Cipher_Hero_60fps.webm" type="video/webm">
                <source src="https://cipherdigital.com/wp-content/uploads/2026/04/Cipher_Hero_60fps.mp4" type="video/mp4">
            </video>
            
            <!-- Dark Vignette & Gradient Overlays for Readability -->
            <div class="absolute inset-0 bg-gradient-to-b from-[#07090b]/80 via-transparent to-[#07090b]/90"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-transparent via-[#07090b]/40 to-[#07090b]/95"></div>
        </div>

        <!-- Main Content Grid -->
        <div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 sm:px-12 py-16 flex flex-col items-center justify-center">
            
            <!-- Header Section -->
            <div class="text-center mb-12 lg:mb-20 max-w-4xl mx-auto">
                <div class="inline-block mb-4 px-4 py-1.5 rounded-full border border-white/10 bg-white/5 backdrop-blur-md">
                    <span class="text-xs font-semibold tracking-[0.2em] text-[#a3ff47] uppercase">Live Monitoring Dashboard</span>
                </div>
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-400 tracking-tighter drop-shadow-2xl mb-6">
                    {{ $eventName }}
                </h1>
                <p class="text-neutral-400 text-lg md:text-xl font-light tracking-wide max-w-2xl mx-auto">
                    Pantau secara langsung pergerakan durasi video yang dilaporkan oleh mitra hingga batas waktu penutupan proyek.
                </p>
            </div>

            <!-- Cards Container (Side by side on Large Screens) -->
            <div class="flex flex-col lg:flex-row items-stretch justify-center gap-8 lg:gap-10 w-full">
                
                <!-- Progress Card (Glassmorphism & Semi-3D) -->
                <div class="w-full lg:w-1/2 max-w-xl rounded-[2.5rem] p-8 sm:p-10 relative overflow-hidden backdrop-blur-2xl transition-all duration-500 hover:-translate-y-2 group"
                     style="background: rgba(15, 17, 21, 0.6); box-shadow: inset 0 1px 1px rgba(255,255,255,0.1), 0 30px 60px -15px rgba(0,0,0,0.9); border: 1px solid rgba(255,255,255,0.08);">
                    
                    <!-- Ambient Glow -->
                    <div class="absolute top-[-20%] left-[-10%] w-[300px] h-[300px] bg-white/[0.03] rounded-full blur-[60px] pointer-events-none z-0 transition-opacity group-hover:opacity-100"></div>

                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-[#a3ff47] animate-spin-slow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="text-xl font-medium text-white tracking-wide">Project Progress</span>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wider text-[#a3ff47] bg-[#a3ff47]/10 border border-[#a3ff47]/20 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#a3ff47] animate-pulse"></span>
                                    LIVE
                                </span>
                            </div>

                            <div class="flex items-baseline gap-1 mb-6">
                                <span class="text-7xl lg:text-8xl font-bold text-white tracking-tighter">{{ number_format($rawPercentage, 0) }}</span>
                                <span class="text-4xl text-neutral-500 font-bold">%</span>
                            </div>

                            <!-- Semi-3D Thick Progress Bar -->
                            <div class="w-full h-12 lg:h-14 rounded-full relative flex items-center mb-10"
                                 style="background: rgba(0,0,0,0.6); box-shadow: inset 0 4px 12px rgba(0,0,0,0.9), inset 0 1px 1px rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.03);">
                                
                                <!-- Glowing Fill -->
                                <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out z-10" 
                                     style="width: {{ $progressPercentage > 15 ? $progressPercentage : 15 }}%; 
                                            background: linear-gradient(90deg, #a3ff47 0%, #47ffde 100%); 
                                            box-shadow: inset 0 4px 8px rgba(255,255,255,0.5), inset 0 -4px 8px rgba(0,0,0,0.2), 0 0 40px rgba(71,255,222,0.4);">
                                </div>
                                
                                <div class="absolute right-5 z-20 pointer-events-none">
                                    <span class="text-xs sm:text-sm font-bold text-white/90 drop-shadow-md">Target: {{ $targetHours }} HRS</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-end mt-4 pt-6 border-t border-white/10">
                            <div>
                                <p class="text-xs text-neutral-400 font-semibold uppercase tracking-[0.15em] mb-2">Total Acquired</p>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-3xl sm:text-4xl font-bold text-white tracking-tight">{{ number_format($totalHours, 1) }}</span>
                                    <span class="text-sm sm:text-base text-neutral-500 font-bold">HRS</span>
                                </div>
                            </div>
                            
                            <button class="w-12 h-12 rounded-full bg-white/5 hover:bg-white/15 border border-white/10 text-white flex items-center justify-center transition-all hover:scale-110 shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Countdown Timer Card (Matching Aesthetic) -->
                <div class="w-full lg:w-1/2 max-w-xl rounded-[2.5rem] p-8 sm:p-10 relative overflow-hidden backdrop-blur-2xl transition-all duration-500 hover:-translate-y-2 flex flex-col justify-center items-center group"
                     style="background: rgba(15, 17, 21, 0.6); box-shadow: inset 0 1px 1px rgba(255,255,255,0.1), 0 30px 60px -15px rgba(0,0,0,0.9); border: 1px solid rgba(255,255,255,0.08);">
                    
                    <!-- Ambient Glow -->
                    <div class="absolute bottom-[-20%] right-[-10%] w-[300px] h-[300px] bg-[#47ffde]/[0.05] rounded-full blur-[60px] pointer-events-none z-0 transition-opacity group-hover:opacity-100"></div>

                    <div class="relative z-10 w-full flex flex-col items-center justify-center h-full">
                        <div class="flex items-center gap-3 mb-12 w-full justify-center">
                            <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="text-neutral-300 text-sm uppercase tracking-[0.2em] font-semibold text-center">Closing Time Remaining</h3>
                        </div>

                        <div id="timer-container" class="flex gap-3 sm:gap-4 md:gap-6 items-center justify-center w-full">
                            
                            <!-- Days -->
                            <div class="flex flex-col items-center">
                                <div class="bg-black/60 border border-white/10 rounded-2xl p-4 sm:p-6 shadow-inner backdrop-blur-md relative overflow-hidden group/time min-w-[70px] sm:min-w-[90px]">
                                    <div class="absolute inset-0 bg-white/5 opacity-0 group-hover/time:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative overflow-hidden h-[1.1em] flex justify-center">
                                        <span id="days" class="text-4xl sm:text-5xl lg:text-6xl text-white font-light tracking-tighter block transition-transform duration-300 text-center w-full">00</span>
                                    </div>
                                </div>
                                <span class="text-[9px] sm:text-[11px] text-neutral-500 mt-4 uppercase tracking-[0.2em] font-semibold">Days</span>
                            </div>
                            
                            <div class="text-2xl sm:text-3xl text-neutral-600 font-light mb-6">:</div>
                            
                            <!-- Hours -->
                            <div class="flex flex-col items-center">
                                <div class="bg-black/60 border border-white/10 rounded-2xl p-4 sm:p-6 shadow-inner backdrop-blur-md relative overflow-hidden group/time min-w-[70px] sm:min-w-[90px]">
                                    <div class="absolute inset-0 bg-white/5 opacity-0 group-hover/time:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative overflow-hidden h-[1.1em] flex justify-center">
                                        <span id="hours" class="text-4xl sm:text-5xl lg:text-6xl text-white font-light tracking-tighter block transition-transform duration-300 text-center w-full">00</span>
                                    </div>
                                </div>
                                <span class="text-[9px] sm:text-[11px] text-neutral-500 mt-4 uppercase tracking-[0.2em] font-semibold">Hours</span>
                            </div>

                            <div class="text-2xl sm:text-3xl text-neutral-600 font-light mb-6">:</div>

                            <!-- Minutes -->
                            <div class="flex flex-col items-center">
                                <div class="bg-black/60 border border-white/10 rounded-2xl p-4 sm:p-6 shadow-inner backdrop-blur-md relative overflow-hidden group/time min-w-[70px] sm:min-w-[90px]">
                                    <div class="absolute inset-0 bg-white/5 opacity-0 group-hover/time:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative overflow-hidden h-[1.1em] flex justify-center">
                                        <span id="mins" class="text-4xl sm:text-5xl lg:text-6xl text-white font-light tracking-tighter block transition-transform duration-300 text-center w-full">00</span>
                                    </div>
                                </div>
                                <span class="text-[9px] sm:text-[11px] text-neutral-500 mt-4 uppercase tracking-[0.2em] font-semibold">Minutes</span>
                            </div>

                            <div class="text-2xl sm:text-3xl text-neutral-600 font-light mb-6">:</div>

                            <!-- Seconds -->
                            <div class="flex flex-col items-center">
                                <div class="bg-[#1a202c]/80 border border-white/10 rounded-2xl p-4 sm:p-6 shadow-[inset_0_2px_15px_rgba(71,255,222,0.15)] backdrop-blur-md relative overflow-hidden min-w-[70px] sm:min-w-[90px]">
                                    <div class="relative overflow-hidden h-[1.1em] flex justify-center">
                                        <span id="secs" class="text-4xl sm:text-5xl lg:text-6xl text-[#47ffde] font-medium tracking-tighter block transition-transform duration-300 text-center w-full" style="text-shadow: 0 0 20px rgba(71,255,222,0.5);">00</span>
                                    </div>
                                </div>
                                <span class="text-[9px] sm:text-[11px] text-[#47ffde]/70 mt-4 uppercase tracking-[0.2em] font-semibold">Seconds</span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script for Countdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update the countdown timer every second
            const deadline = new Date("{{ $targetDeadline->format('Y-m-d\TH:i:s') }}").getTime();

            const x = setInterval(function() {
                const now = new Date().getTime();
                const distance = deadline - now;

                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("timer-container").innerHTML = "<div class='text-2xl font-light text-white tracking-widest uppercase'>Data Locked</div>";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                const pad = (num) => num.toString().padStart(2, '0');

                if(document.getElementById("days")) document.getElementById("days").innerHTML = pad(days);
                if(document.getElementById("hours")) document.getElementById("hours").innerHTML = pad(hours);
                if(document.getElementById("mins")) document.getElementById("mins").innerHTML = pad(minutes);
                
                // Smooth sliding animation for seconds
                const secElement = document.getElementById("secs");
                if(secElement) {
                    if(secElement.innerHTML !== pad(seconds)) {
                        secElement.style.transform = 'translateY(-100%)';
                        setTimeout(() => {
                            secElement.style.transition = 'none';
                            secElement.style.transform = 'translateY(100%)';
                            secElement.innerHTML = pad(seconds);
                            
                            setTimeout(() => {
                                secElement.style.transition = 'transform 300ms cubic-bezier(0.4, 0, 0.2, 1)';
                                secElement.style.transform = 'translateY(0)';
                            }, 20);
                        }, 300);
                    }
                }
            }, 1000);
        });
    </script>
</x-app-layout>
