<x-app-layout>
    <!-- Pastikan Spline script dimuat -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.5/build/spline-viewer.js"></script>

    <!-- Full Black Canvas with Negative Margins to override layout padding -->
    <div class="relative bg-black min-h-[calc(100vh-4rem)] -m-4 sm:-m-6 lg:-m-8 overflow-hidden text-white font-sans flex flex-col lg:flex-row items-center">
        
        <!-- Subtle Spotlight Effect -->
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-white/[0.03] rounded-full filter blur-[120px] pointer-events-none transform translate-x-1/3 -translate-y-1/3"></div>

        <!-- Left Content (Text, Progress, Countdown) -->
        <div class="w-full lg:w-1/2 px-8 sm:px-16 lg:px-24 py-12 flex flex-col justify-center relative z-10">
            
            <h1 class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-b from-neutral-50 to-neutral-400 tracking-tight mb-4">
                {{ $eventName }}
            </h1>
            <p class="text-neutral-400 max-w-lg mb-12 text-sm leading-relaxed">
                Pantau secara langsung pergerakan durasi video yang dilaporkan oleh mitra hingga batas waktu penutupan proyek.
            </p>

            <!-- Progress Bar Section (Clean, no boxes) -->
            <div class="mb-16 max-w-lg">
                <div class="flex items-end gap-4 mb-4">
                    <div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-5xl font-light text-white tracking-tighter">{{ number_format($totalHours, 1) }}</span>
                            <span class="text-sm text-neutral-500 font-medium">HRS</span>
                        </div>
                    </div>
                    <div class="pb-1 text-neutral-600 font-light text-3xl">/</div>
                    <div class="pb-1">
                        <span class="text-2xl text-neutral-500 font-light">{{ $targetHours }} HRS</span>
                    </div>
                </div>

                <div class="w-full">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Progress</span>
                        <span class="text-sm font-light text-white">{{ number_format($rawPercentage, 1) }}%</span>
                    </div>
                    <div class="h-1 w-full bg-neutral-900 rounded-full overflow-hidden">
                        <div class="h-full bg-white rounded-full relative transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(255,255,255,0.5)]" 
                             style="width: {{ $progressPercentage > 2 ? $progressPercentage : 2 }}%">
                        </div>
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

        <!-- Right Content (Interactive 3D Spline) -->
        <div class="w-full lg:w-1/2 h-[50vh] lg:h-screen absolute lg:relative top-0 right-0 opacity-30 lg:opacity-100 pointer-events-none lg:pointer-events-auto">
            <spline-viewer 
                loading-anim-type="spinner-small-light" 
                class="w-full h-full" 
                url="https://prod.spline.design/kZDDjO5HuC9GJUM2/scene.splinecode">
            </spline-viewer>
            <!-- Overlay to fade out spline into background on small screens -->
            <div class="absolute inset-0 bg-gradient-to-r from-black via-transparent to-transparent hidden lg:block"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent lg:hidden"></div>
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
