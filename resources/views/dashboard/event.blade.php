<x-app-layout>
    <!-- Light, clean background with subtle grid pattern -->
    <div class="relative min-h-[calc(100vh-4rem)] -m-4 sm:-m-6 lg:-m-8 flex flex-col items-center justify-center bg-[#fafafa] font-sans"
         style="background-image: linear-gradient(#f0f0f0 1px, transparent 1px), linear-gradient(90deg, #f0f0f0 1px, transparent 1px); background-size: 40px 40px;">
        
        <!-- Top Nav Badges (Aesthetic detail) -->
        <div class="absolute top-6 left-6 hidden sm:block">
            <span class="px-4 py-2 bg-white border border-gray-200 rounded-full text-xs font-semibold text-gray-600 flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg> 
                K-01
            </span>
        </div>
        <div class="absolute top-6 right-6 hidden sm:block">
            <span class="text-gray-400 text-sm font-medium">Dashboard progress</span>
        </div>

        <!-- The Central Widget -->
        <div class="w-full px-4 sm:px-0 relative z-10 mx-auto" style="max-width: 480px;">
            <div class="w-full rounded-[2.5rem] bg-white shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1),0_0_0_1px_rgba(0,0,0,0.02)] flex flex-col relative overflow-hidden">
                
                <!-- Top Section (White) -->
                <div class="p-7 sm:p-8 pb-14 relative z-10 bg-white rounded-t-[2.5rem]">
                    <div class="flex items-start gap-5">
                        <!-- Thumbnail -->
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex-shrink-0 flex items-center justify-center border border-indigo-100 shadow-inner">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        
                        <!-- Info -->
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-2">
                                <span class="px-2.5 py-1 bg-green-50 text-green-600 text-[10px] font-bold uppercase tracking-wider rounded-md border border-green-100">Active</span>
                                <div class="text-right flex items-center gap-2">
                                    <span class="text-[11px] text-gray-400 font-medium">Target HRS</span>
                                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path></svg>
                                    </div>
                                </div>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight mb-1 truncate" title="{{ $eventName }}">{{ $eventName }}</h2>
                            <div class="flex items-center gap-1.5">
                                <span class="text-lg font-bold text-indigo-600">{{ number_format($totalHours, 1) }}</span>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Acquired / {{ $targetHours }} HRS</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section (Dark Gradient) -->
                <div class="p-7 sm:p-8 rounded-[2.5rem] -mt-8 relative z-20 overflow-hidden"
                     style="background: linear-gradient(150deg, #181926 0%, #100b16 100%); box-shadow: 0 -10px 20px -10px rgba(0,0,0,0.2);">
                    
                    <!-- Inner warm glow at the bottom right -->
                    <div class="absolute right-[-20%] bottom-[-50%] w-64 h-64 bg-orange-500/20 blur-[50px] pointer-events-none rounded-full"></div>
                    <div class="absolute left-[-20%] top-[-20%] w-40 h-40 bg-blue-500/10 blur-[40px] pointer-events-none rounded-full"></div>

                    <!-- Header -->
                    <div class="text-center mb-6">
                        <h3 class="text-gray-400 text-[10px] font-bold uppercase tracking-[0.25em]">Profit Progress</h3>
                    </div>

                    <!-- Stats -->
                    <div class="flex justify-between items-end mb-4 relative z-10">
                        <span class="text-4xl sm:text-5xl font-bold text-white tracking-tighter">{{ number_format($rawPercentage, 0) }}%</span>
                        <div class="flex items-center gap-2 pb-1">
                            <span class="text-gray-400 text-xs font-medium">Live monitoring</span>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>

                    <!-- Semi-3D Thick Progress Bar (First Design) -->
                    <div class="w-full h-12 lg:h-14 rounded-full relative flex items-center pr-4 mb-2 mt-4"
                         style="background: rgba(0,0,0,0.5); box-shadow: inset 0 4px 12px rgba(0,0,0,0.8), inset 0 1px 1px rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.03);">
                        
                        <!-- Glowing Fill with 3D Volume -->
                        <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out z-10" 
                             style="width: {{ $progressPercentage > 15 ? $progressPercentage : 15 }}%; 
                                    background: linear-gradient(90deg, #a3ff47 0%, #47ffde 100%); 
                                    box-shadow: inset 0 4px 8px rgba(255,255,255,0.5), inset 0 -4px 8px rgba(0,0,0,0.15), 0 0 35px rgba(71,255,222,0.4);">
                        </div>
                        
                        <!-- Text inside right side of the bar -->
                        <div class="relative w-full flex justify-end z-20 pointer-events-none">
                            <span class="text-xs font-bold text-white/80 drop-shadow-md tracking-wide">Target: {{ $targetHours }} HRS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Minimalist Countdown -->
        <div class="mt-10 relative z-10 w-full px-4 sm:px-0 flex flex-col items-center mx-auto" style="max-width: 480px;">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.25em] mb-4">Closing Deadline</span>
            
            <div id="timer-container" class="flex items-center justify-center gap-3 sm:gap-5 w-full">
                <!-- Days -->
                <div class="flex flex-col items-center w-[60px] sm:w-[70px]">
                    <div class="bg-white border border-gray-100 rounded-xl p-3 w-full shadow-sm">
                        <span id="days" class="text-2xl sm:text-3xl text-gray-800 font-bold block text-center">00</span>
                    </div>
                    <span class="text-[9px] text-gray-400 mt-2 uppercase tracking-widest font-semibold">Days</span>
                </div>
                
                <div class="text-xl text-gray-300 font-light mb-5">:</div>
                
                <!-- Hours -->
                <div class="flex flex-col items-center w-[60px] sm:w-[70px]">
                    <div class="bg-white border border-gray-100 rounded-xl p-3 w-full shadow-sm">
                        <span id="hours" class="text-2xl sm:text-3xl text-gray-800 font-bold block text-center">00</span>
                    </div>
                    <span class="text-[9px] text-gray-400 mt-2 uppercase tracking-widest font-semibold">Hours</span>
                </div>

                <div class="text-xl text-gray-300 font-light mb-5">:</div>

                <!-- Minutes -->
                <div class="flex flex-col items-center w-[60px] sm:w-[70px]">
                    <div class="bg-white border border-gray-100 rounded-xl p-3 w-full shadow-sm">
                        <span id="mins" class="text-2xl sm:text-3xl text-gray-800 font-bold block text-center">00</span>
                    </div>
                    <span class="text-[9px] text-gray-400 mt-2 uppercase tracking-widest font-semibold">Minutes</span>
                </div>

                <div class="text-xl text-gray-300 font-light mb-5">:</div>

                <!-- Seconds -->
                <div class="flex flex-col items-center w-[60px] sm:w-[70px]">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 w-full shadow-sm relative overflow-hidden">
                        <span id="secs" class="text-2xl sm:text-3xl text-indigo-600 font-bold block text-center relative z-10">00</span>
                    </div>
                    <span class="text-[9px] text-indigo-400 mt-2 uppercase tracking-widest font-semibold">Seconds</span>
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
                    document.getElementById("timer-container").innerHTML = "<div class='text-sm font-bold text-gray-500 tracking-widest uppercase bg-white px-6 py-3 rounded-xl border border-gray-100 shadow-sm'>Data Locked</div>";
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
