<div id="ios-install-prompt" class="fixed bottom-24 left-1/2 -translate-x-1/2 w-11/12 max-w-sm bg-white rounded-2xl shadow-2xl border border-gray-200 p-4 z-50 transform transition-all duration-500 translate-y-32 opacity-0 pointer-events-none" style="position: fixed; bottom: 6rem; left: 50%; transform: translateX(-50%) translateY(8rem); width: 91.666667%; max-width: 24rem; background-color: #ffffff; border-radius: 1rem; border: 1px solid #e5e7eb; padding: 1rem; z-index: 50; filter: drop-shadow(0 10px 25px rgba(0,0,0,0.1)); opacity: 0; pointer-events: none; transition: all 0.5s;">
    <button onclick="closeIosPrompt()" style="position: absolute; top: 0.5rem; right: 0.5rem; color: #9ca3af; background: transparent; border: none; cursor: pointer;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
        <div style="flex-shrink: 0; padding-top: 0.25rem;">
            <img src="{{ asset('images/app-icon.png') }}" alt="Logo" width="48" height="48" style="width: 3rem; height: 3rem; border-radius: 0.75rem; border: 1px solid #f3f4f6;">
        </div>
        <div>
            <h4 style="font-size: 0.875rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem 0; font-family: sans-serif;">Instal KameraKita</h4>
            <p style="font-size: 0.75rem; color: #4b5563; line-height: 1.625; margin: 0 0 0.5rem 0; font-family: sans-serif;">
                Instal aplikasi ini ke iPhone Anda untuk akses yang lebih cepat.
            </p>
            <p style="font-size: 11px; color: #6b7280; font-weight: 500; margin: 0; font-family: sans-serif;">
                Tap tombol <svg width="16" height="16" style="display: inline-block; width: 1rem; height: 1rem; color: #3b82f6; margin: 0 0.25rem; vertical-align: sub;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> <b>Share</b> di bawah layar Anda, lalu pilih <b>"Add to Home Screen"</b>.
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
        prompt.style.transform = 'translateX(-50%) translateY(8rem)';
        prompt.style.opacity = '0';
        prompt.style.pointerEvents = 'none';
        localStorage.setItem('ios_prompt_dismissed', 'true');
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (isIos() && !isStandalone() && !localStorage.getItem('ios_prompt_dismissed')) {
            setTimeout(() => {
                const prompt = document.getElementById('ios-install-prompt');
                prompt.style.transform = 'translateX(-50%) translateY(0)';
                prompt.style.opacity = '1';
                prompt.style.pointerEvents = 'auto';
            }, 2000);
        }
    });
</script>
