<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leaderboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ currentTab: 'weekly' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 max-w-lg mx-auto">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">KameraKita <span class="text-sky-600">Top Workers</span></h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto" x-text="currentTab === 'weekly' ? 'Peringkat kontributor berdasarkan total durasi video yang dikirim minggu ini.' : 'Peringkat kontributor terbaik berdasarkan total durasi video disetujui.'"></p>
                </div>

                <!-- Tabs -->
                <div class="flex gap-2 p-1 bg-slate-100 rounded-2xl mb-8">
                    <button @click="currentTab = 'weekly'" :class="{ 'bg-white shadow-sm text-sky-700': currentTab === 'weekly', 'text-slate-500 hover:text-slate-700': currentTab !== 'weekly' }" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm transition-all duration-200">
                        Minggu Ini
                    </button>
                    <button @click="currentTab = 'allTime'" :class="{ 'bg-white shadow-sm text-sky-700': currentTab === 'allTime', 'text-slate-500 hover:text-slate-700': currentTab !== 'allTime' }" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm transition-all duration-200">
                        Sepanjang Waktu
                    </button>
                </div>

                <!-- Leaderboards -->
                <div class="flex flex-col items-center justify-start min-h-[400px]">
                    <template x-if="currentTab === 'weekly'">
                        <div class="w-full flex flex-col items-center">
                            <interactive-leaderboard theme="light" players="{{ $weeklyData }}"></interactive-leaderboard>
                            <p class="mt-8 text-xs text-slate-400 italic text-center max-w-xs leading-relaxed">
                                * Catatan: Angka di atas adalah total durasi terkirim. Durasi akan diverifikasi ulang dengan total disetujui, sehingga skor akhir dapat berubah.
                            </p>
                        </div>
                    </template>
                    <template x-if="currentTab === 'allTime'">
                        <div class="w-full flex justify-center">
                            <interactive-leaderboard theme="light" players="{{ $allTimeData }}"></interactive-leaderboard>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Web Component Definition -->
    <script>
        class InteractiveLeaderboard extends HTMLElement {
            connectedCallback() {
                const theme = this.getAttribute('theme') || 'light';
                let players = [];
                
                try {
                    const playersData = this.getAttribute('players');
                    if (playersData) {
                        players = JSON.parse(playersData);
                    }
                } catch (e) {
                    console.error("Invalid JSON in players attribute");
                }

                if (players.length === 0) {
                    this.innerHTML = `
                        <div class="w-full text-center py-10 text-slate-400 font-medium animate-pulse">
                            Belum ada data skor untuk periode ini.
                        </div>
                    `;
                    return;
                }

                const isLight = theme === 'light';

                this.innerHTML = `
                    <style>
                        .lb-container-${theme} {
                            font-family: 'Inter', sans-serif;
                            width: 100%;
                            max-width: 450px;
                        }

                        /* --- STACK ANIMATION LOGIC --- */
                        .leaderboard-wrapper {
                            position: relative;
                            display: flex;
                            flex-direction: column;
                            gap: 12px;
                            padding: 20px 10px;
                            perspective: 1000px;
                        }

                        .rank-card {
                            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
                            transform-origin: top center;
                            position: relative;
                            will-change: transform, opacity;
                            border-radius: 24px;
                            padding: 16px;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            cursor: pointer;
                        }

                        /* Default Stacked State */
                        .leaderboard-wrapper:not(:hover) .rank-card:nth-child(1) { z-index: 40; }
                        .leaderboard-wrapper:not(:hover) .rank-card:nth-child(2) { z-index: 30; transform: translateY(-35px) scale(0.95); opacity: 0.9; }
                        .leaderboard-wrapper:not(:hover) .rank-card:nth-child(3) { z-index: 20; transform: translateY(-70px) scale(0.90); opacity: 0.7; }
                        .leaderboard-wrapper:not(:hover) .rank-card:nth-child(4) { z-index: 10; transform: translateY(-105px) scale(0.85); opacity: 0.3; filter: blur(1px); }
                        .leaderboard-wrapper:not(:hover) .rank-card:nth-child(n+5) { z-index: 5; transform: translateY(-135px) scale(0.80); opacity: 0; pointer-events: none; }

                        /* Expanded State (On Wrapper Hover) */
                        .leaderboard-wrapper:hover .rank-card {
                            transform: translateY(0) scale(1);
                            opacity: 1;
                            filter: blur(0);
                            pointer-events: auto;
                        }

                        /* Individual Card Hover Effect */
                        .leaderboard-wrapper:hover .rank-card:hover {
                            transform: translateY(-4px) scale(1.02);
                            z-index: 50;
                        }

                        /* --- SPECIFIC CARD STYLES --- */
                        
                        /* Rank 1: The Golden Glow */
                        .rank-1 {
                            background: linear-gradient(135deg, #FFD000 0%, #FF8A00 100%);
                            box-shadow: 
                                0 20px 40px -10px rgba(255, 138, 0, 0.4),
                                inset 0 2px 4px rgba(255, 255, 255, 0.5);
                            border: 1px solid rgba(255,255,255,0.4);
                            overflow: hidden;
                        }

                        @keyframes shimmer {
                            0% { transform: translateX(-150%) skewX(-15deg); }
                            100% { transform: translateX(200%) skewX(-15deg); }
                        }
                        .rank-1::after {
                            content: '';
                            position: absolute;
                            top: 0; left: 0; width: 30%; height: 100%;
                            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
                            animation: shimmer 3s infinite;
                        }

                        /* Light Theme Cards */
                        .theme-light .rank-other {
                            background: #ffffff;
                            box-shadow: 
                                0 15px 35px -10px rgba(0,0,0,0.08),
                                inset 0 2px 4px rgba(255,255,255,1);
                            border: 1px solid rgba(226, 232, 240, 0.8);
                        }
                        .theme-light .rank-other:hover {
                            border: 1px solid rgba(203, 213, 225, 1);
                            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.12), 0 0 20px rgba(255, 215, 0, 0.15);
                        }

                        /* --- INITIAL LOAD ANIMATION --- */
                        @keyframes fadeInUp {
                            from { opacity: 0; transform: translateY(40px) scale(0.9); }
                            to { opacity: 1; transform: translateY(0) scale(1); }
                        }
                        .animate-entrance {
                            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                            opacity: 0;
                        }
                        
                        .delay-100 { animation-delay: 100ms; }
                        .delay-200 { animation-delay: 200ms; }
                        .delay-300 { animation-delay: 300ms; }
                        .delay-400 { animation-delay: 400ms; }
                        .delay-500 { animation-delay: 500ms; }
                        .delay-600 { animation-delay: 600ms; }
                        .delay-700 { animation-delay: 700ms; }
                        .delay-800 { animation-delay: 800ms; }
                        .delay-900 { animation-delay: 900ms; }
                        .delay-1000 { animation-delay: 1000ms; }

                        /* Helper Classes */
                        .flex-center { display: flex; align-items: center; justify-content: center; }
                        .flex-between { display: flex; align-items: center; justify-content: space-between; }
                    </style>

                    <div class="lb-container-${theme} theme-${theme}">
                        <div class="leaderboard-wrapper w-full">
                            ${players.map((player, index) => this.generatePlayerCard(player, index, isLight)).join('')}
                        </div>
                    </div>
                `;
            }

            generatePlayerCard(player, index, isLight) {
                const rank = index + 1;
                const isFirst = rank === 1;
                const delayClass = `delay-${Math.min((index + 1) * 100, 1000)}`;
                
                // Colors for Rank 1
                let textColor = isFirst ? '#111827' : (isLight ? '#1f2937' : '#f3f4f6');
                let scoreColor = isFirst ? '#111827' : (isLight ? '#0369a1' : '#38bdf8');
                let badgeBg = isFirst ? 'rgba(255,255,255,0.2)' : (isLight ? '#f1f5f9' : 'rgba(255,255,255,0.05)');
                let badgeBorder = isFirst ? 'rgba(255,255,255,0.3)' : (isLight ? '#e2e8f0' : 'rgba(255,255,255,0.1)');
                let badgeText = isFirst ? '#ffffff' : (isLight ? '#64748b' : '#9ca3af');
                let scoreBg = isFirst ? 'rgba(255,255,255,0.2)' : 'transparent';
                let scoreBorder = isFirst ? '1px solid rgba(255,255,255,0.3)' : 'none';

                // Decreasing opacity for lower ranks
                let cardOpacity = isFirst ? '1' : (rank === 2 ? '1' : (rank === 3 ? '0.9' : (rank === 4 ? '0.7' : '0.5')));

                return `
                    <div class="rank-card ${isFirst ? 'rank-1' : 'rank-other'} animate-entrance ${delayClass}" style="opacity: ${cardOpacity}">
                        <div class="flex-center" style="gap: 16px; position: relative; z-index: 10;">
                            <!-- Rank Badge -->
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: ${badgeBg}; border: 1px solid ${badgeBorder}; color: ${badgeText}; font-size: 14px; font-weight: bold; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                                ${rank}
                            </div>
                            
                            <!-- Avatar -->
                            <img src="${player.avatar}" alt="${player.name}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid ${isFirst ? 'rgba(255,255,255,0.3)' : (isLight ? '#e2e8f0' : 'rgba(255,255,255,0.1)')}; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            
                            <!-- Name -->
                            <span style="font-weight: 700; font-size: 18px; letter-spacing: -0.02em; color: ${textColor};">${player.name}</span>
                        </div>
                        
                        <!-- Score -->
                        <div class="flex-center" style="gap: 6px; position: relative; z-index: 10; background: ${scoreBg}; border: ${scoreBorder}; padding: 6px 12px; border-radius: 999px; backdrop-filter: blur(4px);">
                            <span style="font-weight: 700; font-size: 14px; color: ${scoreColor};">${player.score}</span>
                            <!-- Sparkle SVG (adapted for video/minutes) -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="color: ${scoreColor}">
                                <path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
                            </svg>
                        </div>
                    </div>
                `;
            }
        }

        if (!customElements.get('interactive-leaderboard')) {
            customElements.define('interactive-leaderboard', InteractiveLeaderboard);
        }
    </script>
</x-app-layout>
