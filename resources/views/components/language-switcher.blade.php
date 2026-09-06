<div class="lang-switch-container">
    <span class="lang-label {{ app()->getLocale() === 'id' ? 'active' : '' }}">ID</span>
    <button type="button" class="lang-toggle {{ app()->getLocale() === 'en' ? 'switched' : '' }}" onclick="switchLanguage()" aria-label="Toggle Language">
        <span class="lang-thumb"></span>
    </button>
    <span class="lang-label {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</span>
</div>

<style>
.lang-switch-container { display: inline-flex; align-items: center; gap: 8px; margin-right: 12px; }
.lang-label { font-size: 13px; font-weight: 600; color: #9ca3af; transition: color 0.3s; font-family: sans-serif; }
.lang-label.active { color: #0d7490; } /* matches the screenshot dark blue color */
.lang-toggle { position: relative; width: 44px; height: 24px; background: #e5e7eb; border-radius: 100px; border: none; cursor: pointer; padding: 2px; transition: background 0.3s; display: inline-flex; align-items: center; box-sizing: border-box; }
.lang-toggle.switched { background: #00668f; } /* matches screenshot toggle blue */
.lang-thumb { display: block; width: 20px; height: 20px; background: white; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transform: translateX(0); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); }
.lang-toggle.switched .lang-thumb { transform: translateX(20px); }
</style>

<script>
if (typeof switchLanguage !== 'function') {
    function switchLanguage() {
        const isEn = {{ app()->getLocale() === 'id' ? 'true' : 'false' }};
        const newLocale = isEn ? 'en' : 'id';
        const toggles = document.querySelectorAll('.lang-toggle');
        toggles.forEach(t => t.classList.toggle('switched'));
        
        fetch('{{ route('locale.switch') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ locale: newLocale })
        }).then(() => { window.location.reload(); }).catch(() => { window.location.reload(); });
    }
}
</script>
