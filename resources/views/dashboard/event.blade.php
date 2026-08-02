<x-app-layout>
    <!-- Dark, sleek Apple-activity-style background -->
    <div class="relative min-h-[calc(100vh-4rem)] -m-4 sm:-m-6 lg:-m-8 flex flex-col items-center justify-center bg-[#000000] font-sans overflow-hidden">
        
        <!-- Ambient Background Glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-[#ff2d55]/10 blur-[100px] rounded-full pointer-events-none"></div>

        <!-- Event Name Header -->
        <div class="relative z-10 mb-6 text-center">
            <span class="text-xs font-bold uppercase tracking-[0.3em] text-[#ff2d55] drop-shadow-[0_0_10px_rgba(255,45,85,0.5)]">{{ $eventName }}</span>
        </div>

        <!-- The Widget -->
        <div class="relative z-10 w-full px-4 sm:px-0 mx-auto" style="max-width: 480px;">
            <!-- Outer Black Card (Container) -->
            <div class="w-full bg-[#09090b] rounded-[2.5rem] p-4 flex gap-4 border border-white/5 shadow-2xl relative">
                
                <!-- Left Card (Flame & Percentage) -->
                <div class="w-[45%] bg-[#18181b] rounded-[2rem] flex flex-col items-center justify-center p-6 border border-white/5 relative group">
                    <!-- Glowing Flame -->
                    <div class="relative mb-5 mt-2 flex items-center justify-center">
                        <div class="absolute w-12 h-12 bg-[#ff2d55] blur-[25px] rounded-full opacity-60"></div>
                        <svg class="relative w-16 h-16 text-[#ff2d55] drop-shadow-[0_0_12px_rgba(255,45,85,0.6)]" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 00-1.071-.136 9.742 9.742 0 00-3.539 6.177A7.547 7.547 0 016.648 6.61a.75.75 0 00-1.152-.082A9 9 0 1015.68 4.534a7.46 7.46 0 01-2.717-2.248z" clip-rule="evenodd" />
                        </svg>
                        <!-- Inner white spark -->
                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-4 h-5 bg-white rounded-full blur-[1px] opacity-90" style="clip-path: ellipse(50% 50% at 50% 50%);"></div>
                    </div>
                    
                    <span class="text-3xl font-bold text-white mb-1 tracking-tight">{{ number_format($rawPercentage, 0) }}%</span>
                    <span class="text-[11px] font-medium text-gray-500 whitespace-nowrap">Profit Progress</span>
                </div>

                <!-- Right Area (Stats & Countdown) -->
                <div class="w-[55%] flex flex-col justify-between py-2 pr-2">
                    
                    <!-- Stats Top -->
                    <div class="flex items-baseline gap-1.5 mb-5 mt-1 px-1">
                        <span class="text-4xl font-bold text-white tracking-tighter">{{ number_format($totalHours, 0) }}</span>
                        <span class="text-base font-medium text-gray-500">/ {{ $targetHours }}</span>
                    </div>

                    <!-- Glowing White Progress Bar -->
                    <div class="w-full h-[14px] bg-[#27272a] rounded-full mb-5 relative">
                        <div class="absolute top-0 left-0 h-full bg-white rounded-full transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(255,255,255,0.8)]" 
                             style="width: {{ $progressPercentage > 5 ? $progressPercentage : 5 }}%;"></div>
                    </div>

                    <!-- Countdown Container (Replaces Mon-Sun) -->
                    <div class="w-full bg-[#18181b] rounded-2xl py-3 px-4 border border-white/5 flex justify-between items-center relative">
                        
                        <!-- Days -->
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-7 h-7 rounded-full bg-[#ff2d55] flex items-center justify-center shadow-[0_0_10px_rgba(255,45,85,0.5)]">
                                <span id="days" class="text-white text-[11px] font-bold">00</span>
                            </div>
                            <span class="text-[9px] font-semibold text-gray-400">Days</span>
                        </div>

                        <!-- Hours -->
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-7 h-7 rounded-full bg-[#ff2d55] flex items-center justify-center shadow-[0_0_10px_rgba(255,45,85,0.5)]">
                                <span id="hours" class="text-white text-[11px] font-bold">00</span>
                            </div>
                            <span class="text-[9px] font-semibold text-gray-400">Hrs</span>
                        </div>

                        <!-- Minutes -->
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-7 h-7 rounded-full bg-[#ff2d55] flex items-center justify-center shadow-[0_0_10px_rgba(255,45,85,0.5)]">
                                <span id="mins" class="text-white text-[11px] font-bold">00</span>
                            </div>
                            <span class="text-[9px] font-semibold text-gray-400">Min</span>
                        </div>

                        <!-- Seconds -->
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-7 h-7 rounded-full bg-[#ff2d55] flex items-center justify-center shadow-[0_0_10px_rgba(255,45,85,0.5)] border border-white/10">
                                <span id="secs" class="text-white text-[11px] font-bold">00</span>
                            </div>
                            <span class="text-[9px] font-semibold text-gray-400">Sec</span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <!-- Script for Countdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deadline = new Date("{{ $targetDeadline->format('Y-m-d\TH:i:s') }}").getTime();

            const x = setInterval(function() {
                const now = new Date().getTime();
                const distance = deadline - now;

                if (distance < 0) {
                    clearInterval(x);
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
                if(document.getElementById("secs")) document.getElementById("secs").innerHTML = pad(seconds);
                
            }, 1000);
        });
    </script>
</x-app-layout>
