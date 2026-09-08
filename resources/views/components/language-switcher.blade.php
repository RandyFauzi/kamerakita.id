<div class="lang-dropdown-wrapper" style="position: relative; display: inline-block;">
    <button type="button" class="lang-dropdown-btn" onclick="toggleLangDropdown(event)">
        @if(app()->getLocale() === 'id')
            <img src="{{ asset('images/flags/id.svg') }}" alt="ID" class="lang-flag-icon" onerror="this.outerHTML='<span class=\'lang-emoji\'>🇮🇩</span>'">
            <span class="lang-text">ID</span>
        @else
            <img src="{{ asset('images/flags/en.svg') }}" alt="EN" class="lang-flag-icon" onerror="this.outerHTML='<span class=\'lang-emoji\'>🇺🇸</span>'">
            <span class="lang-text">EN</span>
        @endif
        <svg class="lang-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <div class="lang-dropdown-menu" style="display: none; opacity: 0;">
        <button type="button" onclick="setLanguage('id')" class="lang-dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}">
            <img src="{{ asset('images/flags/id.svg') }}" alt="Indonesia" class="lang-flag-icon" onerror="this.outerHTML='<span class=\'lang-emoji\'>🇮🇩</span>'">
            <span class="lang-name">Indonesia</span>
        </button>
        <button type="button" onclick="setLanguage('en')" class="lang-dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
            <img src="{{ asset('images/flags/en.svg') }}" alt="English" class="lang-flag-icon" onerror="this.outerHTML='<span class=\'lang-emoji\'>🇺🇸</span>'">
            <span class="lang-name">English</span>
        </button>
    </div>
</div>

<style>
.lang-dropdown-wrapper {
    font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
    margin-right: 12px;
}
.lang-dropdown-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 9999px;
    cursor: pointer;
    transition: all 0.2s;
    color: #1e293b;
    font-weight: 600;
    font-size: 14px;
}
.lang-dropdown-btn:hover {
    background: #e2e8f0;
}
.lang-flag-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.lang-emoji {
    font-size: 16px;
    line-height: 1;
}
.lang-chevron {
    transition: transform 0.2s;
    color: #64748b;
}
.lang-chevron.rotate-180 {
    transform: rotate(180deg);
}
.lang-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 8px;
    min-width: 160px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 50;
    transition: opacity 0.2s;
}
.lang-dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 10px 12px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
    transition: background 0.2s;
    color: #475569;
    font-weight: 500;
    font-size: 14px;
}
.lang-dropdown-item:hover {
    background: #f8fafc;
}
.lang-dropdown-item.active {
    background: #f1f5f9;
    color: #0f172a;
    font-weight: 600;
}
</style>

<script>
if (typeof setLanguage !== 'function') {
    function setLanguage(newLocale) {
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

    function toggleLangDropdown(event) {
        event.stopPropagation();
        const wrapper = event.currentTarget.closest('.lang-dropdown-wrapper');
        const menu = wrapper.querySelector('.lang-dropdown-menu');
        const chevron = wrapper.querySelector('.lang-chevron');
        
        const isHidden = menu.style.display === 'none';
        
        // Close all other dropdowns
        document.querySelectorAll('.lang-dropdown-menu').forEach(m => {
            m.style.display = 'none';
            m.style.opacity = '0';
        });
        document.querySelectorAll('.lang-chevron').forEach(c => {
            c.classList.remove('rotate-180');
        });

        if (isHidden) {
            menu.style.display = 'block';
            // slight delay to allow display block to apply before opacity transition
            setTimeout(() => { menu.style.opacity = '1'; }, 10);
            chevron.classList.add('rotate-180');
        }
    }

    // Close when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.lang-dropdown-wrapper')) {
            document.querySelectorAll('.lang-dropdown-menu').forEach(m => {
                m.style.display = 'none';
                m.style.opacity = '0';
            });
            document.querySelectorAll('.lang-chevron').forEach(c => {
                c.classList.remove('rotate-180');
            });
        }
    });
}
</script>
