<x-app-layout>
    <!-- Pastikan Spline script dimuat -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.5/build/spline-viewer.js"></script>

    <!-- Full Black Canvas with Negative Margins to override layout padding -->
    <!-- Custom style tag to forcefully override background just in case tailwind classes conflict -->
    <style>
        .event-dark-bg {
            background: linear-gradient(135deg, #1f2937 0%, #030712 100%) !important;
            color: #ffffff !important;
        }
    </style>

    <div class="relative min-h-[calc(100vh-4rem)] -m-4 sm:-m-6 lg:-m-8 overflow-hidden flex flex-col lg:flex-row items-center event-dark-bg">
        
        <!-- Subtle Spotlight Effect -->
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-white/[0.05] rounded-full filter blur-[120px] pointer-events-none transform translate-x-1/3 -translate-y-1/3 z-0"></div>

        <!-- Left Content (Text, Progress, Countdown) -->
        <div class="w-full lg:w-1/2 px-8 sm:px-16 lg:px-24 py-12 flex flex-col justify-center relative z-20 pointer-events-none">
            <!-- Let left content text be pointer events none so it doesn't block Spline interactions -->
            <div class="pointer-events-auto">
                <h1 class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-b from-white to-gray-400 tracking-tight mb-8 drop-shadow-md">
                    {{ $eventName }}
                </h1>
            </div>

            <!-- Progress Bar Card (Semi-3D) -->
            <div class="mb-16 max-w-lg w-full rounded-[2rem] p-6 sm:p-8 relative overflow-hidden"
                 style="background: linear-gradient(180deg, #262626 0%, #171717 100%); box-shadow: inset 0 1px 2px rgba(255,255,255,0.1), 0 25px 50px -12px rgba(0,0,0,0.8); border: 1px solid rgba(255,255,255,0.05);">
                
                <!-- Subtle inner glow on top-left -->
                <div class="absolute top-[-50px] left-[-50px] w-[200px] h-[200px] bg-white/[0.04] rounded-full blur-[40px] pointer-events-none z-0"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-1">
                        <svg class="w-6 h-6 text-neutral-300 animate-spin-slow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-xl font-medium text-white tracking-wide drop-shadow-sm">Project Progress</span>
                        <span class="px-3 py-1 rounded-full text-xs text-neutral-400 ml-1" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">{{ $eventName }}</span>
                    </div>

                    <div class="flex items-baseline gap-1 mb-5">
                        <span class="text-7xl font-bold text-white tracking-tighter drop-shadow-lg">{{ number_format($rawPercentage, 0) }}</span>
                        <span class="text-3xl text-neutral-500 font-bold drop-shadow-sm">%</span>
                    </div>

                    <!-- Semi-3D Thick Progress Bar -->
                    <div class="w-full h-14 rounded-full relative flex items-center pr-5 mb-8"
                         style="background: rgba(0,0,0,0.5); box-shadow: inset 0 4px 12px rgba(0,0,0,0.8), inset 0 1px 1px rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.03);">
                        
                        <!-- Glowing Fill with 3D Volume -->
                        <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out z-10" 
                             style="width: {{ $progressPercentage > 15 ? $progressPercentage : 15 }}%; 
                                    background: linear-gradient(90deg, #a3ff47 0%, #47ffde 100%); 
                                    box-shadow: inset 0 4px 8px rgba(255,255,255,0.5), inset 0 -4px 8px rgba(0,0,0,0.15), 0 0 35px rgba(71,255,222,0.4);">
                        </div>
                        
                        <!-- Text inside right side of the bar -->
                        <div class="relative w-full flex justify-end z-20 pointer-events-none">
                            <span class="text-sm font-medium text-white/80 drop-shadow-md">Target: {{ $targetHours }} HRS</span>
                        </div>
                    </div>
                    
                    <!-- Bottom section: Acquired & Detail -->
                    <div class="flex justify-between items-end mt-6">
                        <div>
                            <p class="text-sm text-neutral-400 font-medium mb-1">Total Acquired</p>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-2xl font-bold text-white tracking-tight">{{ number_format($totalHours, 1) }}</span>
                                <span class="text-sm text-neutral-500 font-bold">HRS</span>
                            </div>
                        </div>
                        
                        <button class="px-5 py-2.5 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-white font-medium transition-colors flex items-center gap-2">
                            More details 
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Countdown Timer (Clean, thin font) -->
            <div class="max-w-lg">
                <div id="timer-container" class="flex justify-start items-start gap-4 sm:gap-6 font-light">
                    <!-- Days -->
                    <div class="flex flex-col items-center">
                        <span id="days" class="text-5xl sm:text-6xl text-white tracking-tight">00</span>
                        <span class="text-[10px] text-neutral-500 mt-2 uppercase tracking-widest">Days</span>
                    </div>
                    <span class="text-4xl text-neutral-600 mt-1">:</span>
                    
                    <!-- Hours -->
                    <div class="flex flex-col items-center">
                        <span id="hours" class="text-5xl sm:text-6xl text-white tracking-tight">00</span>
                        <span class="text-[10px] text-neutral-500 mt-2 uppercase tracking-widest">Hours</span>
                    </div>
                    <span class="text-4xl text-neutral-600 mt-1">:</span>
                    
                    <!-- Minutes -->
                    <div class="flex flex-col items-center">
                        <span id="mins" class="text-5xl sm:text-6xl text-white tracking-tight">00</span>
                        <span class="text-[10px] text-neutral-500 mt-2 uppercase tracking-widest">Minutes</span>
                    </div>
                    <span class="text-4xl text-neutral-600 mt-1">:</span>
                    
                    <!-- Seconds -->
                    <div class="flex flex-col items-center">
                        <div class="relative overflow-hidden h-[1.1em]">
                            <span id="secs" class="text-5xl sm:text-6xl text-white tracking-tight block transition-transform duration-300">00</span>
                        </div>
                        <span class="text-[10px] text-neutral-500 mt-2 uppercase tracking-widest">Seconds</span>
                    </div>
                </div>
            </div>
            
            </div>
        </div>

        <!-- Right Content (Interactive 3D Spline) -->
        <div class="w-full lg:w-1/2 h-[50vh] lg:h-screen absolute top-0 right-0 opacity-40 lg:opacity-100 z-0 overflow-visible pointer-events-auto flex justify-end items-center">
            <!-- Geser Spline drastis ke kanan agar tidak menutupi teks -->
            <spline-viewer 
                loading-anim-type="spinner-small-light" 
                class="w-full h-full lg:w-[140%] lg:h-[140%] lg:absolute lg:top-[-20%] lg:-right-[40%]" 
                url="https://prod.spline.design/kZDDjO5HuC9GJUM2/scene.splinecode">
            </spline-viewer>
            <!-- Overlay to fade out spline into background on small screens -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#111] via-transparent to-transparent hidden lg:block pointer-events-none"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#111] via-[#111]/50 to-transparent lg:hidden pointer-events-none"></div>
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
