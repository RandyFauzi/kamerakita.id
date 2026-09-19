<div id="ios-install-prompt" class="fixed bottom-24 left-1/2 -translate-x-1/2 w-11/12 max-w-sm bg-white rounded-2xl shadow-2xl border border-gray-200 p-4 z-50 transform transition-all duration-500 translate-y-32 opacity-0 pointer-events-none" style="filter: drop-shadow(0 10px 25px rgba(0,0,0,0.1));">
    <button onclick="closeIosPrompt()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 transition">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    <div class="flex items-start gap-3">
        <div class="shrink-0 pt-1">
            <img src="{{ asset('images/app-icon.png') }}" alt="Logo" class="w-12 h-12 rounded-xl border border-gray-100 shadow-sm">
        </div>
        <div>
            <h4 class="text-sm font-bold text-gray-900 mb-1">Instal KameraKita</h4>
            <p class="text-xs text-gray-600 leading-relaxed mb-2">
                Instal aplikasi ini ke iPhone Anda untuk akses yang lebih cepat.
            </p>
            <p class="text-[11px] text-gray-500 font-medium">
                Tap tombol <svg class="inline w-4 h-4 text-blue-500 mx-1 align-sub" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> <b>Share</b> di bawah layar Anda, lalu pilih <b>"Add to Home Screen"</b>.
            </p>
        </div>
    </div>
</div>

<script>
    function isIos() {
        const userAgent = window.navigator.userAgent.toLowerCase();
        return /iphone|ipad|ipod/.test(userAgent);
    }

    function isStandalone() {
        return ('standalone' in window.navigator) && window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;
    }

    function closeIosPrompt() {
        const prompt = document.getElementById('ios-install-prompt');
        prompt.classList.add('translate-y-32', 'opacity-0');
        prompt.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
        localStorage.setItem('ios_prompt_dismissed', 'true');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Only show if it is iOS, NOT already installed, and NOT dismissed previously
        if (isIos() && !isStandalone() && !localStorage.getItem('ios_prompt_dismissed')) {
            setTimeout(() => {
                const prompt = document.getElementById('ios-install-prompt');
                prompt.classList.remove('translate-y-32', 'opacity-0', 'pointer-events-none');
                prompt.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
            }, 2000); // Tampil setelah 2 detik
        }
    });
</script>
