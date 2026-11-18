@props(['variant' => 'bar'])

@php
    $povService = app(\App\Services\AccountPovService::class);
    $canUsePov = $povService->canCurrentSessionUsePov();
@endphp

@if($canUsePov)
    @php
        $isImpersonating = $povService->isImpersonating();
        $activeUser = Auth::user();
        $activePartner = $activeUser?->partner;
        $displayName = $activePartner?->full_name ?: ($activeUser?->name ?? '-');
        $roleLabel = $activeUser ? $povService->formatRoleLabel($activeUser, $activePartner) : '-';
        $isUnverified = $activeUser && $activeUser->email_verified_at === null;
        $needsOnboarding = $activeUser
            && in_array($activeUser->role, ['worker', 'partner', 'vendor'], true)
            && ! $activeUser->has_completed_onboarding;
    @endphp

    @if($isImpersonating)
        @if($variant === 'floating')
            <div class="fixed bottom-3 left-1/2 -translate-x-1/2 z-[90] w-[calc(100%-1.5rem)] max-w-2xl">
                <div class="bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-amber-400/40 px-3.5 py-2.5 flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] font-extrabold tracking-wider uppercase px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300 border border-amber-400/30">POV</span>
                                <span class="text-xs sm:text-sm font-bold truncate">{{ $displayName }}</span>
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-white/10 text-slate-200">{{ $roleLabel }}</span>
                            </div>
                            <p class="text-[11px] text-slate-300 truncate">{{ $activeUser->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-account-pov-modal'))" class="px-2.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition">
                            Ganti Akun
                        </button>
                        <form method="POST" action="{{ route('account-pov.leave') }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-extrabold transition shadow-sm">
                                Keluar POV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-slate-900 text-white border-b border-amber-400/35 px-3 sm:px-6 py-2">
                <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                        </span>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0 text-xs">
                            <span class="font-extrabold tracking-wider uppercase px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300 border border-amber-400/30">Mode POV</span>
                            <span class="font-bold text-white truncate max-w-[160px] sm:max-w-xs">{{ $displayName }}</span>
                            <span class="hidden sm:inline text-slate-400">•</span>
                            <span class="text-slate-300 truncate max-w-[190px] sm:max-w-xs">{{ $activeUser->email }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">{{ $roleLabel }}</span>
                            @if($activePartner?->mitra_id)
                                <span class="hidden md:inline px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white/10 text-slate-200">{{ $activePartner->mitra_id }}</span>
                            @endif
                            @if($isUnverified)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/25 text-rose-200 border border-rose-400/30">Belum Verifikasi Email</span>
                            @endif
                            @if($needsOnboarding)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/25 text-amber-200 border border-amber-400/30">Belum Onboarding</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-account-pov-modal'))" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span>Ganti Akun</span>
                        </button>
                        <form method="POST" action="{{ route('account-pov.leave') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-extrabold transition shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Keluar POV</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Quick Account POV Switcher Modal (Rendered once per page for authorized superadmin) -->
    <div id="account-pov-modal" class="hidden fixed inset-0 z-[110] flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="account-pov-modal-title">
        <div id="account-pov-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative z-10 w-full sm:max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]">
            <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 bg-slate-50/70">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </span>
                        <h3 id="account-pov-modal-title" class="text-base font-extrabold text-slate-900">Buka Mode POV Akun</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Lihat tampilan persis seperti akun Worker, Mitra, Main Account, atau Admin lain tanpa password.</p>
                </div>
                <button type="button" id="account-pov-close" aria-label="Tutup modal POV" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/70 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-4 border-b border-slate-100">
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        id="account-pov-search-input"
                        type="text"
                        autocomplete="off"
                        placeholder="Cari nama, email, ID KMK, no. WA, atau role..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>
            </div>

            <div id="account-pov-results" class="flex-1 overflow-y-auto divide-y divide-slate-100 p-2 min-h-[220px]">
                <div class="py-10 text-center text-xs text-slate-400">Ketik nama, email, atau ID KMK untuk mencari akun...</div>
            </div>

            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Shortcut: <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-mono text-[10px]">Ctrl+Shift+P</kbd></span>
                @if($isImpersonating)
                    <form method="POST" action="{{ route('account-pov.leave') }}">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-700">Keluar POV sekarang</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @once
        <script>
            (function () {
                const modal = document.getElementById('account-pov-modal');
                if (!modal) return;

                const backdrop = document.getElementById('account-pov-backdrop');
                const closeBtn = document.getElementById('account-pov-close');
                const searchInput = document.getElementById('account-pov-search-input');
                const resultsEl = document.getElementById('account-pov-results');
                const searchUrl = @json(route('account-pov.search'));
                const csrfToken = @json(csrf_token());
                let debounceTimer = null;
                let activeController = null;

                function openModal() {
                    modal.classList.remove('hidden');
                    setTimeout(() => searchInput && searchInput.focus(), 40);
                    fetchAccounts(searchInput ? searchInput.value : '');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                }

                function setMessage(text, isError) {
                    resultsEl.replaceChildren();
                    const box = document.createElement('div');
                    box.className = 'py-10 text-center text-xs ' + (isError ? 'text-rose-600 font-medium' : 'text-slate-400');
                    box.textContent = text;
                    resultsEl.appendChild(box);
                }

                function createBadge(text, className) {
                    const span = document.createElement('span');
                    span.className = className;
                    span.textContent = text;
                    return span;
                }

                function renderAccounts(accounts) {
                    resultsEl.replaceChildren();

                    if (!Array.isArray(accounts) || accounts.length === 0) {
                        setMessage('Tidak ada akun yang cocok dengan pencarian Anda.', false);
                        return;
                    }

                    const returnUrl = window.location.pathname + window.location.search;

                    accounts.forEach((acc) => {
                        const row = document.createElement('div');
                        row.className = 'flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition';

                        const left = document.createElement('div');
                        left.className = 'flex items-center gap-3 min-w-0 flex-1';

                        const avatar = document.createElement('div');
                        avatar.className = 'w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0 uppercase';
                        avatar.textContent = (acc.name || acc.email || '?').charAt(0);

                        const info = document.createElement('div');
                        info.className = 'min-w-0 flex-1';

                        const titleLine = document.createElement('div');
                        titleLine.className = 'flex items-center gap-1.5 flex-wrap';

                        const nameEl = document.createElement('span');
                        nameEl.className = 'text-sm font-bold text-slate-900 truncate max-w-[180px]';
                        nameEl.textContent = acc.name || acc.email;
                        titleLine.appendChild(nameEl);

                        if (acc.mitra_id) {
                            titleLine.appendChild(createBadge(acc.mitra_id, 'px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold'));
                        }

                        titleLine.appendChild(createBadge(acc.role_label || '-', 'px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold'));

                        if (!acc.is_verified) {
                            titleLine.appendChild(createBadge('Unverified', 'px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 text-[10px] font-semibold'));
                        }

                        const emailEl = document.createElement('p');
                        emailEl.className = 'text-xs text-slate-500 truncate mt-0.5';
                        emailEl.textContent = acc.email || '';

                        info.appendChild(titleLine);
                        info.appendChild(emailEl);
                        left.appendChild(avatar);
                        left.appendChild(info);
                        row.appendChild(left);

                        const right = document.createElement('div');
                        right.className = 'shrink-0';

                        if (acc.is_current) {
                            right.appendChild(createBadge('Aktif', 'inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200'));
                        } else {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = acc.enter_url;

                            const tokenInput = document.createElement('input');
                            tokenInput.type = 'hidden';
                            tokenInput.name = '_token';
                            tokenInput.value = csrfToken;

                            const returnInput = document.createElement('input');
                            returnInput.type = 'hidden';
                            returnInput.name = 'return_url';
                            returnInput.value = returnUrl;

                            const btn = document.createElement('button');
                            btn.type = 'submit';
                            btn.className = 'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition';
                            btn.textContent = 'Masuk POV';

                            form.appendChild(tokenInput);
                            form.appendChild(returnInput);
                            form.appendChild(btn);
                            right.appendChild(form);
                        }

                        row.appendChild(right);
                        resultsEl.appendChild(row);
                    });
                }

                async function fetchAccounts(query) {
                    if (activeController) {
                        activeController.abort();
                    }
                    activeController = new AbortController();

                    setMessage('Memuat daftar akun...', false);

                    try {
                        const url = new URL(searchUrl, window.location.origin);
                        if (query && query.trim() !== '') {
                            url.searchParams.set('q', query.trim());
                        }

                        const response = await fetch(url.toString(), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            signal: activeController.signal,
                        });

                        if (!response.ok) {
                            throw new Error('Gagal memuat akun');
                        }

                        const data = await response.json();
                        renderAccounts(data.accounts || []);
                    } catch (err) {
                        if (err.name === 'AbortError') return;
                        setMessage('Gagal memuat daftar akun. Coba lagi.', true);
                    }
                }

                window.addEventListener('open-account-pov-modal', openModal);
                backdrop && backdrop.addEventListener('click', closeModal);
                closeBtn && closeBtn.addEventListener('click', closeModal);

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'p') {
                        e.preventDefault();
                        if (modal.classList.contains('hidden')) {
                            openModal();
                        } else {
                            closeModal();
                        }
                    }
                });

                searchInput && searchInput.addEventListener('input', (e) => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        fetchAccounts(e.target.value);
                    }, 220);
                });
            })();
        </script>
    @endonce
@endif
