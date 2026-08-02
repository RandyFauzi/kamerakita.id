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
                    <div class="inline-flex items-center justify-center p-3 bg-white/10 rounded-2xl mb-6 backdrop-blur-sm">
                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-300 uppercase tracking-widest mb-1">Batas Waktu Target</h3>
                    <p class="text-sm text-gray-400 mb-8">{{ $targetDeadline->translatedFormat('l, d F Y - H:i') }}</p>

                    <div class="flex justify-center gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/10 shadow-xl">
                                <span id="cd-days" class="text-2xl sm:text-4xl font-black tabular-nums">00</span>
                            </div>
                            <span class="text-xs font-bold text-gray-400 mt-2 uppercase">Hari</span>
                        </div>
                        <div class="text-2xl font-black text-white/30 pt-4">:</div>
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/10 shadow-xl">
                                <span id="cd-hours" class="text-2xl sm:text-4xl font-black tabular-nums">00</span>
                            </div>
                            <span class="text-xs font-bold text-gray-400 mt-2 uppercase">Jam</span>
                        </div>
                        <div class="text-2xl font-black text-white/30 pt-4">:</div>
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/10 shadow-xl">
                                <span id="cd-minutes" class="text-2xl sm:text-4xl font-black tabular-nums">00</span>
                            </div>
                            <span class="text-xs font-bold text-gray-400 mt-2 uppercase">Menit</span>
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

            // Countdown Timer
            const targetDate = new Date("{{ $targetDeadline->format('Y-m-d H:i:s') }}").getTime();

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    document.getElementById('cd-days').innerText = "00";
                    document.getElementById('cd-hours').innerText = "00";
                    document.getElementById('cd-minutes').innerText = "00";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

                document.getElementById('cd-days').innerText = days.toString().padStart(2, '0');
                document.getElementById('cd-hours').innerText = hours.toString().padStart(2, '0');
                document.getElementById('cd-minutes').innerText = minutes.toString().padStart(2, '0');
            }
            
            setInterval(updateCountdown, 1000);
            updateCountdown();
        });
    </script>
</x-app-layout>
