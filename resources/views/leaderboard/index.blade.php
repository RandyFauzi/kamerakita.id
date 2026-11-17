<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leaderboard') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10" x-data="{ currentCategory: '{{ $defaultCategory ?? 'worker' }}', currentTab: 'weekly' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl p-5 sm:p-8 max-w-2xl mx-auto border border-slate-200/80">
                <!-- Dynamic Title & Subtitle -->
                <div class="text-center mb-6">
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">
                        KameraKita <span style="color: #0284c7;" x-text="currentCategory === 'mitra' ? 'Top Mitra' : 'Top Workers'"></span>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1.5 leading-relaxed"
                       x-text="currentCategory === 'mitra'
                            ? (currentTab === 'weekly' ? 'Akumulasi durasi kerja seluruh anggota Mitra minggu ini.' : 'Akumulasi durasi kerja seluruh anggota Mitra sepanjang waktu.')
                            : (currentTab === 'weekly' ? 'Peringkat kontributor individu & akumulasi Main Account minggu ini.' : 'Peringkat kontributor individu & akumulasi Main Account sepanjang waktu.')">
                    </p>
                </div>

                <!-- Category Switcher: Worker vs Mitra -->
                <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-100 rounded-2xl mb-3">
                    <button type="button"
                            @click="currentCategory = 'worker'"
                            :class="currentCategory === 'worker' ? 'bg-white shadow-sm text-sky-700 ring-1 ring-slate-200/70' : 'text-slate-500 hover:text-slate-700'"
                            class="py-2.5 px-3 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 flex flex-col items-center justify-center leading-tight">
                        <span>Kategori Worker</span>
                        <span class="text-[10px] font-medium opacity-75 mt-0.5">Individu &amp; Main Account</span>
                    </button>
                    <button type="button"
                            @click="currentCategory = 'mitra'"
                            :class="currentCategory === 'mitra' ? 'bg-white shadow-sm text-sky-700 ring-1 ring-slate-200/70' : 'text-slate-500 hover:text-slate-700'"
                            class="py-2.5 px-3 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 flex flex-col items-center justify-center leading-tight">
                        <span>Kategori Mitra</span>
                        <span class="text-[10px] font-medium opacity-75 mt-0.5">Vendor &amp; Studio</span>
                    </button>
                </div>

                <!-- Time Period Tabs -->
                <div class="flex gap-2 p-1 bg-slate-100/80 rounded-xl mb-6">
                    <button type="button"
                            @click="currentTab = 'weekly'"
                            :class="{ 'bg-white shadow-sm text-sky-700': currentTab === 'weekly', 'text-slate-500 hover:text-slate-700': currentTab !== 'weekly' }"
                            class="flex-1 py-2 px-4 rounded-lg font-bold text-xs transition-all duration-200">
                        {{ __('dashboard.leaderboard.this_week') }}
                    </button>
                    <button type="button"
                            @click="currentTab = 'allTime'"
                            :class="{ 'bg-white shadow-sm text-sky-700': currentTab === 'allTime', 'text-slate-500 hover:text-slate-700': currentTab !== 'allTime' }"
                            class="flex-1 py-2 px-4 rounded-lg font-bold text-xs transition-all duration-200">
                        {{ __('dashboard.leaderboard.all_time') }}
                    </button>
                </div>

                <!-- Leaderboards -->
                <div class="flex flex-col items-center justify-start min-h-[400px] w-full">
                    <!-- Worker: Weekly -->
                    <template x-if="currentCategory === 'worker' && currentTab === 'weekly'">
                        <div class="w-full flex flex-col items-center">
                            <interactive-leaderboard theme="light" players="{{ $weeklyWorkerData }}"></interactive-leaderboard>
                            <p class="mt-6 text-xs text-slate-400 italic text-center max-w-sm leading-relaxed">
                                {{ __('dashboard.leaderboard.note') }}
                            </p>
                        </div>
                    </template>

                    <!-- Worker: All Time -->
                    <template x-if="currentCategory === 'worker' && currentTab === 'allTime'">
                        <div class="w-full flex flex-col items-center">
                            <interactive-leaderboard theme="light" players="{{ $allTimeWorkerData }}"></interactive-leaderboard>
                            <p class="mt-6 text-xs text-slate-400 italic text-center max-w-sm leading-relaxed">
                                {{ __('dashboard.leaderboard.note') }}
                            </p>
                        </div>
                    </template>

                    <!-- Mitra: Weekly -->
                    <template x-if="currentCategory === 'mitra' && currentTab === 'weekly'">
                        <div class="w-full flex flex-col items-center">
                            <interactive-leaderboard theme="light" players="{{ $weeklyMitraData }}"></interactive-leaderboard>
                            <p class="mt-6 text-xs text-slate-400 italic text-center max-w-sm leading-relaxed">
                                {{ __('dashboard.leaderboard.note') }}
                            </p>
                        </div>
                    </template>

                    <!-- Mitra: All Time -->
                    <template x-if="currentCategory === 'mitra' && currentTab === 'allTime'">
                        <div class="w-full flex flex-col items-center">
                            <interactive-leaderboard theme="light" players="{{ $allTimeMitraData }}"></interactive-leaderboard>
                            <p class="mt-6 text-xs text-slate-400 italic text-center max-w-sm leading-relaxed">
                                {{ __('dashboard.leaderboard.note') }}
                            </p>
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
                        <div class="w-full text-center py-12 text-slate-400 text-sm font-medium">
                            {{ __('dashboard.leaderboard.empty_state') }}
                        </div>
                    `;
                    return;
                }

                const isLight = theme === 'light';

                const top3 = players.slice(0, 3);
                const others = players.slice(3);

                const formatDuration = (val) => {
                    let raw = 0;
                    if (typeof val === 'number') {
                        raw = Math.round(val);
                    } else {
                        raw = parseInt(String(val).replace(/[^0-9]/g, '')) || 0;
                    }
                    const h = Math.floor(raw / 60);
                    const m = raw % 60;
                    if (h > 0 && m > 0) return `${h} Jam ${m} Menit`;
                    if (h > 0) return `${h} Jam`;
                    return `${m} Menit`;
                };

                const formatMetaLabel = (p) => {
                    if (!p) return '';
                    const count = parseInt(p.members_count || 1, 10);
                    if (p.entity_type === 'main_account') {
                        return count > 1 ? `Main Account · ${count} Akun` : 'Main Account';
                    }
                    if (p.entity_type === 'mitra' && count > 1) {
                        return `${count} Anggota`;
                    }
                    return '';
                };

                let podiumHtml = '';
                if (top3.length > 0) {
                    const p1 = top3[0];
                    const p2 = top3[1];
                    const p3 = top3[2];

                    const p1Meta = formatMetaLabel(p1);
                    const p2Meta = formatMetaLabel(p2);
                    const p3Meta = formatMetaLabel(p3);

                    podiumHtml = `
                    <div class="lb-podium-grid">
                        <!-- Rank 2 -->
                        ${p2 ? `
                        <div class="animate-entrance delay-200 lb-podium-col">
                            <img src="${p2.avatar}" class="lb-avatar-sm" style="border-color: #cbd5e1;">
                            <span class="lb-podium-name" style="color: ${isLight ? '#334155' : '#e2e8f0'};" title="${p2.name}">${p2.name}</span>
                            ${p2Meta ? `<span class="lb-meta-pill">${p2Meta}</span>` : ''}
                            <div class="lb-podium-scores">
                                <span class="lb-podium-worked" style="color: ${isLight ? '#0369a1' : '#38bdf8'};">${formatDuration(p2.worked_minutes ?? p2.score)}</span>
                                <span class="lb-acc-pill">
                                    <span class="lb-acc-dot"></span>
                                    Acc: ${formatDuration(p2.approved_minutes ?? 0)}
                                </span>
                            </div>
                            <div class="lb-pedestal" style="height: 86px; background: linear-gradient(to bottom, #94a3b8, #cbd5e1);">
                                <div class="lb-pedestal-num">2</div>
                            </div>
                        </div>
                        ` : `<div class="lb-podium-col"></div>`}

                        <!-- Rank 1 -->
                        ${p1 ? `
                        <div class="animate-entrance delay-100 lb-podium-col lb-podium-col--first">
                            <div style="position: relative;">
                                <div style="position: absolute; top: -22px; left: 50%; transform: translateX(-50%); font-size: 26px; filter: drop-shadow(0 2px 4px rgba(245,158,11,0.4)); z-index: 20;">👑</div>
                                <img src="${p1.avatar}" class="lb-avatar-lg" style="border-color: #fbbf24;">
                            </div>
                            <span class="lb-podium-name lb-podium-name--first" style="color: ${isLight ? '#0f172a' : '#f8fafc'};" title="${p1.name}">${p1.name}</span>
                            ${p1Meta ? `<span class="lb-meta-pill">${p1Meta}</span>` : ''}
                            <div class="lb-podium-scores">
                                <span class="lb-podium-worked lb-podium-worked--first">${formatDuration(p1.worked_minutes ?? p1.score)}</span>
                                <span class="lb-acc-pill">
                                    <span class="lb-acc-dot"></span>
                                    Acc: ${formatDuration(p1.approved_minutes ?? 0)}
                                </span>
                            </div>
                            <div class="lb-pedestal lb-pedestal--first" style="height: 122px; background: linear-gradient(to bottom, #f59e0b, #fcd34d);">
                                <div class="lb-pedestal-num lb-pedestal-num--first">1</div>
                            </div>
                        </div>
                        ` : `<div class="lb-podium-col lb-podium-col--first"></div>`}

                        <!-- Rank 3 -->
                        ${p3 ? `
                        <div class="animate-entrance delay-300 lb-podium-col">
                            <img src="${p3.avatar}" class="lb-avatar-sm" style="border-color: #fcd34d;">
                            <span class="lb-podium-name" style="color: ${isLight ? '#334155' : '#e2e8f0'};" title="${p3.name}">${p3.name}</span>
                            ${p3Meta ? `<span class="lb-meta-pill">${p3Meta}</span>` : ''}
                            <div class="lb-podium-scores">
                                <span class="lb-podium-worked" style="color: ${isLight ? '#0369a1' : '#38bdf8'};">${formatDuration(p3.worked_minutes ?? p3.score)}</span>
                                <span class="lb-acc-pill">
                                    <span class="lb-acc-dot"></span>
                                    Acc: ${formatDuration(p3.approved_minutes ?? 0)}
                                </span>
                            </div>
                            <div class="lb-pedestal" style="height: 70px; background: linear-gradient(to bottom, #d97706, #fde68a);">
                                <div class="lb-pedestal-num">3</div>
                            </div>
                        </div>
                        ` : `<div class="lb-podium-col"></div>`}
                    </div>
                    ${others.length > 0 ? `<div class="lb-section-divider"><span>Other Participants</span></div>` : ''}
                    `;
                }

                this.innerHTML = `
                    <style>
                        interactive-leaderboard {
                            display: block;
                            width: 100%;
                        }
                        .lb-container-${theme} {
                            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
                            width: 100%;
                            max-width: 100%;
                            box-sizing: border-box;
                        }

                        /* Podium Grid */
                        .lb-podium-grid {
                            display: grid;
                            grid-template-columns: minmax(0, 1fr) minmax(0, 1.12fr) minmax(0, 1fr);
                            align-items: end;
                            gap: 10px;
                            margin: 34px 0 28px;
                            width: 100%;
                        }
                        .lb-podium-col {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            min-width: 0;
                        }
                        .lb-podium-col--first {
                            z-index: 10;
                        }
                        .lb-avatar-sm {
                            width: 56px;
                            height: 56px;
                            border-radius: 50%;
                            border: 3px solid #cbd5e1;
                            object-fit: cover;
                            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
                        }
                        .lb-avatar-lg {
                            width: 72px;
                            height: 72px;
                            border-radius: 50%;
                            border: 4px solid #fbbf24;
                            object-fit: cover;
                            box-shadow: 0 8px 16px rgba(245, 158, 11, 0.28);
                        }
                        .lb-podium-name {
                            font-weight: 700;
                            margin-top: 8px;
                            font-size: 13px;
                            text-align: center;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            width: 100%;
                            padding: 0 4px;
                        }
                        .lb-podium-name--first {
                            font-weight: 800;
                            font-size: 15px;
                        }
                        .lb-meta-pill {
                            font-size: 10px;
                            font-weight: 600;
                            color: #0284c7;
                            margin-top: 2px;
                            white-space: nowrap;
                        }
                        .lb-podium-scores {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 4px;
                            margin: 6px 0 10px;
                            width: 100%;
                        }
                        .lb-podium-worked {
                            font-size: 12px;
                            font-weight: 700;
                            white-space: nowrap;
                        }
                        .lb-podium-worked--first {
                            font-size: 13px;
                            font-weight: 800;
                            color: #d97706;
                        }
                        .lb-acc-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            font-size: 10px;
                            color: #475569;
                            font-weight: 600;
                            background: #f1f5f9;
                            border: 1px solid #e2e8f0;
                            padding: 2px 7px;
                            border-radius: 999px;
                            white-space: nowrap;
                            max-width: 100%;
                        }
                        .lb-acc-dot {
                            width: 5px;
                            height: 5px;
                            border-radius: 50%;
                            background: #0284c7;
                            flex-shrink: 0;
                        }
                        .lb-pedestal {
                            width: 100%;
                            max-width: 104px;
                            border-top-left-radius: 14px;
                            border-top-right-radius: 14px;
                            display: flex;
                            justify-content: center;
                            align-items: flex-start;
                            padding-top: 12px;
                            box-shadow: inset 0 2px 5px rgba(255, 255, 255, 0.5);
                        }
                        .lb-pedestal--first {
                            max-width: 120px;
                            box-shadow: 0 -4px 14px rgba(245, 158, 11, 0.25), inset 0 2px 5px rgba(255, 255, 255, 0.5);
                        }
                        .lb-pedestal-num {
                            background: rgba(255, 255, 255, 0.42);
                            border-radius: 50%;
                            width: 28px;
                            height: 28px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-weight: 800;
                            color: #ffffff;
                            font-size: 13px;
                        }
                        .lb-pedestal-num--first {
                            width: 34px;
                            height: 34px;
                            font-size: 16px;
                        }

                        /* Section Divider */
                        .lb-section-divider {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin-bottom: 14px;
                            color: #64748b;
                            font-size: 11px;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        }

                        /* Participant Cards List */
                        .leaderboard-wrapper {
                            display: flex;
                            flex-direction: column;
                            gap: 10px;
                            width: 100%;
                            box-sizing: border-box;
                        }

                        .rank-card {
                            transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 220ms ease, border-color 220ms ease;
                            border-radius: 18px;
                            padding: 12px 16px;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            width: 100%;
                            box-sizing: border-box;
                            background: #ffffff;
                            border: 1px solid #e2e8f0;
                            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
                        }

                        .rank-card:hover {
                            transform: translateY(-2px);
                            border-color: #7dd3fc;
                            box-shadow: 0 10px 24px rgba(2, 132, 199, 0.1);
                        }

                        .lb-card-left {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            flex: 1 1 0%;
                            min-width: 0;
                        }

                        .lb-rank-badge {
                            width: 30px;
                            height: 30px;
                            border-radius: 50%;
                            background: #f1f5f9;
                            border: 1px solid #e2e8f0;
                            color: #64748b;
                            font-size: 12px;
                            font-weight: 800;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }

                        .lb-card-avatar {
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            object-fit: cover;
                            border: 2px solid #e2e8f0;
                            flex-shrink: 0;
                        }

                        .lb-card-info {
                            display: flex;
                            flex-direction: column;
                            min-width: 0;
                            flex: 1;
                        }

                        .lb-card-name {
                            font-weight: 700;
                            font-size: 14px;
                            color: #0f172a;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }

                        .lb-card-meta {
                            font-size: 11px;
                            font-weight: 600;
                            color: #0284c7;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }

                        .lb-card-right {
                            display: flex;
                            flex-direction: column;
                            align-items: flex-end;
                            gap: 3px;
                            flex-shrink: 0;
                            white-space: nowrap;
                        }

                        .lb-card-worked {
                            font-weight: 800;
                            font-size: 13px;
                            color: #0369a1;
                            white-space: nowrap;
                        }

                        @media (max-width: 480px) {
                            .lb-podium-grid { gap: 6px; }
                            .lb-podium-name { font-size: 11px; }
                            .lb-podium-worked { font-size: 10px; }
                            .lb-acc-pill { font-size: 9px; padding: 1.5px 5px; }
                            .rank-card { padding: 10px 12px; gap: 8px; }
                            .lb-card-left { gap: 8px; }
                            .lb-card-avatar { width: 34px; height: 34px; }
                            .lb-rank-badge { width: 26px; height: 26px; font-size: 11px; }
                            .lb-card-name { font-size: 13px; }
                            .lb-card-worked { font-size: 12px; }
                        }

                        /* Entrance Animation */
                        @keyframes fadeInUp {
                            from { opacity: 0; transform: translateY(18px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                        .animate-entrance {
                            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                            opacity: 0;
                        }
                        .delay-100 { animation-delay: 80ms; }
                        .delay-200 { animation-delay: 160ms; }
                        .delay-300 { animation-delay: 240ms; }
                        .delay-400 { animation-delay: 300ms; }
                        .delay-500 { animation-delay: 350ms; }
                        .delay-600 { animation-delay: 400ms; }
                        .delay-700 { animation-delay: 450ms; }
                        .delay-800 { animation-delay: 500ms; }
                        .delay-900 { animation-delay: 550ms; }
                        .delay-1000 { animation-delay: 600ms; }
                    </style>

                    <div class="lb-container-${theme} theme-${theme}">
                        ${podiumHtml}
                        <div class="leaderboard-wrapper">
                            ${others.map((player, index) => this.generatePlayerCard(player, index + 3, formatDuration, formatMetaLabel)).join('')}
                        </div>
                    </div>
                `;
            }

            generatePlayerCard(player, index, formatDuration, formatMetaLabel) {
                const rank = index + 1;
                const delayClass = `delay-${Math.min((index + 1) * 50, 1000)}`;
                const metaLabel = formatMetaLabel(player);

                return `
                    <div class="rank-card animate-entrance ${delayClass}">
                        <div class="lb-card-left">
                            <div class="lb-rank-badge">${rank}</div>
                            <img src="${player.avatar}" alt="${player.name}" class="lb-card-avatar">
                            <div class="lb-card-info">
                                <span class="lb-card-name" title="${player.name}">${player.name}</span>
                                ${metaLabel ? `<span class="lb-card-meta">${metaLabel}</span>` : ''}
                            </div>
                        </div>
                        <div class="lb-card-right">
                            <span class="lb-card-worked">${formatDuration(player.worked_minutes ?? player.score)}</span>
                            <span class="lb-acc-pill">
                                <span class="lb-acc-dot"></span>
                                Acc: ${formatDuration(player.approved_minutes ?? 0)}
                            </span>
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
