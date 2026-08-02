<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    🏆 Event: {{ $eventName }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Dasbor pemantauan kemajuan untuk event aktif.
                </p>
            </div>
            
            <!-- Live Date Badge -->
            <div class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2 rounded-xl border border-amber-200 shadow-sm">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-bold tracking-wide" id="live-clock">--:--:--</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Target Card -->
            <div class="lg:col-span-2 bg-white rounded-[32px] p-6 sm:p-10 border border-gray-150 shadow-sm relative overflow-hidden">
                <!-- Decorative Background -->
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-purple-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-2">
                        <span class="bg-indigo-100 text-indigo-800 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Pencapaian Target
                        </span>
                        <span class="text-sm font-bold text-gray-400">
                            {{ $periodTarget ? $periodTarget['start']->translatedFormat('d M') . ' - ' . $periodTarget['end']->translatedFormat('d M Y') : 'Semua Waktu' }}
                        </span>
                    </div>

                    <div class="mt-8 flex items-end justify-between">
                        <div>
                            <h2 class="text-5xl sm:text-7xl font-black text-slate-900 tracking-tighter">
                                {{ number_format($totalHours, 1) }} <span class="text-2xl text-gray-400 font-bold">/ {{ $targetHours }} Jam</span>
                            </h2>
                            <p class="text-gray-500 font-medium mt-2">Total durasi video yang terkumpul untuk event ini</p>
                        </div>
                    </div>

                    <!-- Progress Bar (Neon Neumorphic Style) -->
                    <div class="mt-8 bg-slate-900 rounded-[32px] p-4 sm:p-6 shadow-2xl border border-slate-800 relative overflow-hidden">
                        <!-- Subtle dot pattern background -->
                        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#475569 1px, transparent 1px); background-size: 12px 12px;"></div>
                        
                        <div class="relative z-10">
                            <div class="flex justify-between text-sm font-bold text-slate-300 mb-4 tracking-wider">
                                <span>PROGRESS PENCAPAIAN</span>
                                <span class="text-lime-400">{{ number_format($rawPercentage, 1) }}%</span>
                            </div>
                            
                            <!-- The Trench (Inner shadow dark track) -->
                            <div class="w-full bg-slate-950 rounded-full h-10 sm:h-12 p-1.5 shadow-[inset_0_4px_10px_rgba(0,0,0,0.6)] border border-slate-800/50 flex items-center relative">
                                
                                <!-- The Neon Pill (Filled progress) -->
                                <div class="bg-gradient-to-r from-lime-300 via-lime-400 to-green-500 h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_20px_rgba(163,230,53,0.5)] flex items-center justify-end px-3 relative group cursor-pointer" 
                                     style="width: {{ $progressPercentage > 5 ? $progressPercentage : 5 }}%">
                                     
                                     <!-- The Icon (Rocket instead of airplane) -->
                                     <svg class="w-5 h-5 text-green-950 transform rotate-45 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                     </svg>
                                </div>
                                
                                <!-- Remaining text on the right of the trench if space allows -->
                                <div class="absolute right-4 text-xs font-bold text-slate-600">
                                    {{ $targetHours - $totalHours > 0 ? '-'.number_format($targetHours - $totalHours, 1).' Jam' : 'SELESAI' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Countdown Timer Card -->
            <div class="bg-slate-900 rounded-[32px] p-6 sm:p-10 shadow-lg text-white relative overflow-hidden flex flex-col justify-center text-center">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 to-slate-900/90 z-0"></div>
            <div class="relative z-10">
                <!-- Countdown Timer (Cinematic Style) -->
                <div class="bg-black text-white rounded-3xl p-6 sm:p-10 shadow-2xl border border-gray-800 flex flex-col items-center justify-center relative overflow-hidden group">
                    <!-- Subtle cinematic glow -->
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/50 to-transparent pointer-events-none"></div>
                    
                    <h3 class="text-xs sm:text-sm font-bold text-gray-500 uppercase tracking-[0.2em] mb-6 relative z-10">
                        WAKTU TERSISA SEBELUM PENUTUPAN
                    </h3>

                    <div id="timer-container" class="flex items-start justify-center gap-2 sm:gap-4 font-mono font-bold text-4xl sm:text-6xl md:text-7xl lg:text-8xl tracking-tighter relative z-10">
                        <!-- Months -->
                        <div class="flex flex-col items-center">
                            <span id="mths" class="text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]">00</span>
                            <span class="text-[10px] sm:text-xs text-gray-400 mt-2 font-sans font-bold tracking-widest uppercase">Mths</span>
                        </div>
                        <span class="text-gray-600 -mt-1 sm:-mt-2 animate-pulse">:</span>
                        
                        <!-- Days -->
                        <div class="flex flex-col items-center">
                            <span id="days" class="text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]">00</span>
                            <span class="text-[10px] sm:text-xs text-gray-400 mt-2 font-sans font-bold tracking-widest uppercase">Days</span>
                        </div>
                        <span class="text-gray-600 -mt-1 sm:-mt-2 animate-pulse">:</span>
                        
                        <!-- Hours -->
                        <div class="flex flex-col items-center">
                            <span id="hours" class="text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]">00</span>
                            <span class="text-[10px] sm:text-xs text-gray-400 mt-2 font-sans font-bold tracking-widest uppercase">Hrs</span>
                        </div>
                        <span class="text-gray-600 -mt-1 sm:-mt-2 animate-pulse">:</span>
                        
                        <!-- Minutes -->
                        <div class="flex flex-col items-center">
                            <span id="mins" class="text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]">00</span>
                            <span class="text-[10px] sm:text-xs text-gray-400 mt-2 font-sans font-bold tracking-widest uppercase">Mins</span>
                        </div>
                        <span class="text-gray-600 -mt-1 sm:-mt-2 animate-pulse">:</span>
                        
                        <!-- Seconds -->
                        <div class="flex flex-col items-center overflow-hidden h-[1.2em]">
                            <span id="secs" class="text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)] transition-all duration-150 block">00</span>
                            <span class="text-[10px] sm:text-xs text-gray-400 mt-2 font-sans font-bold tracking-widest uppercase block -translate-y-[1.2em]">Secs</span>
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
                document.getElementById('live-clock').innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
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
                document.getElementById("timer-container").innerHTML = "<div class='text-4xl font-black text-red-500 tracking-widest uppercase animate-pulse'>Waktu Habis!</div>";
                return;
            }

            // Kalkulasi MTHS, DAYS, HRS, MINS, SECS
            // Asumsi 1 bulan = 30 hari untuk penyederhanaan cinematic display
            const mths = Math.floor(distance / (1000 * 60 * 60 * 24 * 30));
            const days = Math.floor((distance % (1000 * 60 * 60 * 24 * 30)) / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Fungsi untuk menambahkan angka 0 di depan jika di bawah 10
            const pad = (num) => num.toString().padStart(2, '0');

            // Menampilkan hasil
            if(document.getElementById("mths")) document.getElementById("mths").innerHTML = pad(mths);
            if(document.getElementById("days")) document.getElementById("days").innerHTML = pad(days);
            if(document.getElementById("hours")) document.getElementById("hours").innerHTML = pad(hours);
            if(document.getElementById("mins")) document.getElementById("mins").innerHTML = pad(minutes);
            
            // Animasi flip sederhana untuk detik
            const secElement = document.getElementById("secs");
            if(secElement) {
                if(secElement.innerHTML !== pad(seconds)) {
                    secElement.style.transform = 'translateY(-10px)';
                    secElement.style.opacity = '0';
                    setTimeout(() => {
                        secElement.innerHTML = pad(seconds);
                        secElement.style.transform = 'translateY(0)';
                        secElement.style.opacity = '1';
                    }, 150);
                }
            }

        }, 1000);
    </script>
</x-app-layout>
