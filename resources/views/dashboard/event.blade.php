<x-app-layout>
    <!-- Pastikan Spline script dimuat -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.5/build/spline-viewer.js"></script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Splite 3D Card Design -->
            <div class="w-full bg-slate-950 relative overflow-hidden rounded-3xl border border-white/10 shadow-2xl flex flex-col lg:flex-row min-h-[600px]">
                
                <!-- Spotlight Effect (Simulated via radial gradient) -->
                <div class="absolute -top-40 left-0 md:left-60 md:-top-20 w-[600px] h-[600px] bg-white/[0.07] rounded-full filter blur-[100px] pointer-events-none"></div>

                <!-- Left Content -->
                <div class="flex-1 p-8 sm:p-12 relative z-10 flex flex-col justify-center border-b lg:border-b-0 lg:border-r border-white/10">
                    
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-white/20 bg-white/5 backdrop-blur-md w-max mb-6">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-medium text-slate-300 tracking-wide uppercase">Live Event Active</span>
                    </div>

                    <h1 class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-b from-neutral-50 to-neutral-400 tracking-tight mb-4">
                        {{ $eventName }}
                    </h1>
                    <p class="text-neutral-300 max-w-lg mb-10 text-sm leading-relaxed">
                        Pantau secara langsung pergerakan durasi video yang dilaporkan oleh mitra hingga batas waktu penutupan proyek.
                    </p>

                    <!-- Focus 1: Progress Bar -->
                    <div class="mb-10 w-full max-w-md bg-white/5 p-6 rounded-2xl border border-white/10 backdrop-blur-sm">
                        <div class="flex items-end gap-4 mb-6">
                            <div>
                                <p class="text-[10px] text-neutral-400 uppercase tracking-widest font-semibold mb-1">Acquired Volume</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-bold text-white tracking-tighter">{{ number_format($totalHours, 1) }}</span>
                                    <span class="text-sm text-neutral-500 font-medium">HRS</span>
                                </div>
                            </div>
                            <div class="pb-1 text-neutral-600 font-light text-2xl">/</div>
                            <div class="pb-1">
                                <p class="text-[10px] text-neutral-400 uppercase tracking-widest font-semibold mb-1">Target</p>
                                <span class="text-xl text-neutral-400 font-medium">{{ $targetHours }} HRS</span>
                            </div>
                        </div>

                        <div class="w-full">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-medium text-neutral-400 uppercase tracking-wider">Progress</span>
                                <span class="text-xs font-bold text-emerald-400">{{ number_format($rawPercentage, 1) }}%</span>
                            </div>
                            <!-- Track -->
                            <div class="h-2 w-full bg-neutral-900 rounded-full overflow-hidden shadow-inner border border-white/5">
                                <!-- Fill -->
                                <div class="h-full bg-gradient-to-r from-emerald-600 to-emerald-400 rounded-full relative transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(52,211,153,0.5)]" 
                                     style="width: {{ $progressPercentage > 5 ? $progressPercentage : 5 }}%">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Focus 2: Countdown Timer -->
                    <div class="w-full max-w-md bg-white/5 p-6 rounded-2xl border border-white/10 backdrop-blur-sm">
                        <p class="text-[10px] text-neutral-400 uppercase tracking-widest font-semibold mb-4 text-center">T-Minus Countdown</p>
                        
                        <div id="timer-container" class="flex justify-between items-start w-full px-2 sm:px-4">
                            <!-- Days -->
                            <div class="flex flex-col items-center">
                                <span id="days" class="text-3xl sm:text-4xl font-bold text-white tracking-tighter">00</span>
                                <span class="text-[9px] text-neutral-500 mt-1 uppercase tracking-widest">Days</span>
                            </div>
                            <span class="text-2xl text-neutral-700 mt-1">:</span>
                            
                            <!-- Hours -->
                            <div class="flex flex-col items-center">
                                <span id="hours" class="text-3xl sm:text-4xl font-bold text-white tracking-tighter">00</span>
                                <span class="text-[9px] text-neutral-500 mt-1 uppercase tracking-widest">Hours</span>
                            </div>
                            <span class="text-2xl text-neutral-700 mt-1">:</span>
                            
                            <!-- Minutes -->
                            <div class="flex flex-col items-center">
                                <span id="mins" class="text-3xl sm:text-4xl font-bold text-white tracking-tighter">00</span>
                                <span class="text-[9px] text-neutral-500 mt-1 uppercase tracking-widest">Mins</span>
                            </div>
                            <span class="text-2xl text-neutral-700 mt-1">:</span>
                            
                            <!-- Seconds -->
                            <div class="flex flex-col items-center">
                                <div class="relative overflow-hidden h-[1.2em]">
                                    <span id="secs" class="text-3xl sm:text-4xl font-bold text-white tracking-tighter block transition-transform duration-300">00</span>
                                </div>
                                <span class="text-[9px] text-neutral-500 mt-1 uppercase tracking-widest">Secs</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Content (Interactive 3D Spline) -->
                <div class="flex-1 relative min-h-[400px] lg:min-h-full pointer-events-auto bg-black/40">
                    <spline-viewer 
                        loading-anim-type="spinner-small-light" 
                        class="w-full h-full absolute inset-0" 
                        url="https://prod.spline.design/kZDDjO5HuC9GJUM2/scene.splinecode">
                    </spline-viewer>
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
                    document.getElementById("timer-container").innerHTML = "<div class='text-2xl font-bold text-red-500 tracking-widest uppercase text-center w-full animate-pulse'>Data Locked</div>";
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
