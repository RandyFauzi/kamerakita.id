<x-app-layout>
    <!-- Spline Viewer Script -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.5/build/spline-viewer.js"></script>

    <!-- Main Container -->
    <div class="relative min-h-[calc(100vh-4rem)] bg-[#050505] text-white overflow-hidden -m-4 sm:-m-6 lg:-m-8">
        
        <!-- Subtle Spotlight Background -->
        <div class="absolute top-0 left-1/4 w-[1000px] h-[500px] bg-white/[0.03] rounded-[100%] blur-[120px] pointer-events-none transform -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[800px] h-[600px] bg-indigo-500/[0.04] rounded-[100%] blur-[100px] pointer-events-none transform translate-y-1/3"></div>

        <div class="max-w-[1400px] mx-auto w-full h-full min-h-[calc(100vh-4rem)] flex flex-col lg:flex-row relative z-10">
            
            <!-- Left Content Section -->
            <div class="w-full lg:w-1/2 p-8 sm:p-12 lg:p-20 flex flex-col justify-center relative z-20">
                
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-white/10 bg-white/5 backdrop-blur-md w-max mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-medium text-slate-300 tracking-wide uppercase">Live Event Active</span>
                </div>

                <!-- Title & Context -->
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-br from-white to-white/50 pb-2">
                    {{ $eventName }}
                </h1>
                <p class="text-lg text-slate-400 font-light max-w-md mb-12 leading-relaxed">
                    Pantau secara langsung pergerakan durasi video yang dilaporkan oleh mitra hingga batas waktu penutupan proyek.
                </p>

                <!-- Data & Progress Section -->
                <div class="space-y-10 max-w-lg">
                    
                    <!-- Volumes -->
                    <div class="flex items-end gap-6">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mb-2">Acquired Volume</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-6xl font-bold tracking-tighter">{{ number_format($totalHours, 1) }}</span>
                                <span class="text-xl text-slate-500 font-medium">HRS</span>
                            </div>
                        </div>
                        <div class="pb-1 text-slate-600 font-light text-2xl">/</div>
                        <div class="pb-1">
                            <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mb-2">Target</p>
                            <span class="text-2xl text-slate-400 font-medium">{{ $targetHours }} HRS</span>
                        </div>
                    </div>

                    <!-- Progress Bar (Sleek Modern) -->
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm font-medium text-slate-300">Progress</span>
                            <span class="text-sm font-bold text-white">{{ number_format($rawPercentage, 1) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-white rounded-full relative transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(255,255,255,0.8)]" 
                                 style="width: {{ $progressPercentage > 2 ? $progressPercentage : 2 }}%">
                            </div>
                        </div>
                    </div>

                    <!-- Countdown Glass Card -->
                    <div class="mt-12 bg-white/[0.02] border border-white/10 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
                        <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mb-6 text-center">Closing In</p>
                        
                        <div id="timer-container" class="flex justify-between items-start w-full px-2 sm:px-6">
                            <!-- Days -->
                            <div class="flex flex-col items-center">
                                <span id="days" class="text-3xl sm:text-4xl font-bold tracking-tighter">00</span>
                                <span class="text-[10px] text-slate-500 mt-2 uppercase tracking-widest">Days</span>
                            </div>
                            <span class="text-2xl text-slate-700 mt-1">:</span>
                            
                            <!-- Hours -->
                            <div class="flex flex-col items-center">
                                <span id="hours" class="text-3xl sm:text-4xl font-bold tracking-tighter">00</span>
                                <span class="text-[10px] text-slate-500 mt-2 uppercase tracking-widest">Hours</span>
                            </div>
                            <span class="text-2xl text-slate-700 mt-1">:</span>
                            
                            <!-- Minutes -->
                            <div class="flex flex-col items-center">
                                <span id="mins" class="text-3xl sm:text-4xl font-bold tracking-tighter">00</span>
                                <span class="text-[10px] text-slate-500 mt-2 uppercase tracking-widest">Mins</span>
                            </div>
                            <span class="text-2xl text-slate-700 mt-1">:</span>
                            
                            <!-- Seconds -->
                            <div class="flex flex-col items-center">
                                <div class="relative overflow-hidden h-[1.2em]">
                                    <span id="secs" class="text-3xl sm:text-4xl font-bold tracking-tighter block transition-transform duration-300">00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 mt-2 uppercase tracking-widest">Secs</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right 3D Spline Section -->
            <div class="w-full lg:w-1/2 h-[50vh] lg:h-auto relative min-h-[400px] z-10 pointer-events-auto">
                <!-- Fallback gradient if Spline doesn't load instantly -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent rounded-full filter blur-[100px] -z-10"></div>
                
                <!-- Spline 3D Scene (Using a beautiful dark modern 3D scene from Spline Community) -->
                <spline-viewer loading-anim-type="spinner-small-light" class="w-full h-full" url="https://prod.spline.design/kZEXS6z8T6VpOb5g/scene.splinecode"></spline-viewer>
                
                <!-- Overlay to blend edges -->
                <div class="absolute inset-0 bg-gradient-to-r from-[#050505] via-transparent to-transparent pointer-events-none hidden lg:block"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-transparent to-transparent pointer-events-none lg:hidden"></div>
            </div>

        </div>
    </div>

    <!-- Script for Live Clock & Countdown -->
    <script>
        // Update the countdown timer every second
        const deadline = new Date("{{ $targetDeadline->format('Y-m-d\TH:i:s') }}").getTime();

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = deadline - now;

            // Waktu habis
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("timer-container").innerHTML = "<div class='text-2xl font-bold text-white tracking-widest uppercase text-center w-full'>Data Locked</div>";
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
    </script>
</x-app-layout>
