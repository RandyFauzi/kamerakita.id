@php
    $partner = \App\Models\Partner::where('user_id', Auth::id())->first();
    $isUser = $partner && in_array($partner->partner_role, ['worker', 'mitra'], true);
@endphp

@if($isUser)
<style>
    @keyframes slideUpFade {
        0% { transform: translateY(100px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    .bottom-nav-container {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 90px;
        filter: drop-shadow(0 -10px 25px rgba(0,0,0,0.05));
        animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        font-family: 'Inter', sans-serif;
        z-index: 40;
    }

    .nav-bg {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .nav-content {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 70px;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 15px;
    }

    /* --- NAV ITEM ANIMATIONS --- */
    .nav-item {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 65px;
        height: 100%;
        color: #94a3b8; 
        text-decoration: none;
        position: relative;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        transition: color 0.4s ease;
    }

    .icon-wrapper {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 44px;
        height: 44px;
        border-radius: 16px;
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.4s ease;
    }

    .icon-wrapper svg {
        stroke-width: 2.2;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .nav-item.active {
        color: #4f46e5;
    }

    .nav-item.active .icon-wrapper {
        transform: translateY(-8px);
    }

    .nav-item:not(.active):hover .icon-wrapper {
        color: #64748b;
        transform: translateY(-4px) scale(1.05);
    }

    /* Magic Indicator */
    .magic-indicator {
        position: absolute;
        top: 5px;
        left: 0;
        width: 44px;
        height: 44px;
        background: #e0e7ff;
        border-radius: 16px;
        z-index: 1;
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        opacity: 0; 
    }

    /* --- CENTER FAB ANIMATIONS --- */
    .center-fab-wrapper {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: 38px;
        z-index: 20;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .fab-glow {
        position: absolute;
        top: 15px;
        width: 65px;
        height: 65px;
        border-radius: 50%;
        background: linear-gradient(90deg, rgba(255, 77, 109, 0.7) 0%, rgba(59, 130, 246, 0.8) 100%);
        filter: blur(18px);
        z-index: -1;
        opacity: 0.8;
        transition: all 0.3s ease;
        transform: translateY(10px);
    }

    .fab-button {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1d4ed8, #3b82f6, #4f46e5);
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        position: relative;
        cursor: pointer;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: inset 0 2px 4px rgba(255,255,255,0.4);
    }

    .fab-button svg {
        stroke-width: 2.5;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .fab-button:hover {
        transform: translateY(-4px);
    }
    
    .fab-button:hover + .fab-glow {
        filter: blur(24px);
        opacity: 1;
        transform: translateY(14px) scale(1.1);
    }

    .fab-button:active {
        transform: scale(0.9);
    }
</style>

<div class="bottom-nav-container md:hidden block" id="bottom-nav-container">
    
    <!-- SVG Shape Curve (Notch) -->
    <svg class="nav-bg" viewBox="0 0 390 90" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0,24 C0,10.7 8.7,0 20,0 H135 C148,0 154,8 158,18 C165,36 178,50 195,50 C212,50 225,36 232,18 C236,8 242,0 255,0 H370 C381.3,0 390,10.7 390,24 V90 H0 V24 Z" fill="#ffffff"/>
    </svg>

    <!-- Tombol Tengah Mengambang (Kirim Laporan) -->
    <a href="{{ route('video-submissions.submit-report.create') }}" class="center-fab-wrapper">
        <div class="fab-glow"></div>
        <div class="fab-button" id="fab-btn">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                <path d="M12 3L4 9v12h5v-7h6v7h5V9z"></path>
            </svg>
        </div>
    </a>

    <!-- Item Navigasi -->
    <div class="nav-content" id="nav-menu">
        
        <!-- Magic Pill Indicator -->
        <div class="magic-indicator" id="magic-indicator"></div>

        <!-- 1. Ringkasan/Home -->
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <div class="icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
        </a>

        <!-- 2. Riwayat Laporan -->
        <a href="{{ route('video-submissions.report-history') }}" class="nav-item {{ request()->routeIs('video-submissions.report-history') ? 'active' : '' }}" style="margin-right: 35px;">
            <div class="icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
        </a>

        <!-- 3. Riwayat Gaji (Wallet) -->
        <a href="{{ route('video-submissions.payment-history') }}" class="nav-item {{ request()->routeIs('video-submissions.payment-history') ? 'active' : '' }}" style="margin-left: 35px;">
            <div class="icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
            </div>
        </a>

        <!-- 4. Profile -->
        <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <div class="icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </a>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('nav-menu');
        const indicator = document.getElementById('magic-indicator');
        const activeItem = container.querySelector('.nav-item.active');

        const updateIndicator = (item, animate = true) => {
            if(!item) return;
            const iconWrapper = item.querySelector('.icon-wrapper');
            const containerRect = container.getBoundingClientRect();
            const iconRect = iconWrapper.getBoundingClientRect();
            
            // Menghitung posisi tengah secara akurat
            const centerX = iconRect.left - containerRect.left + (iconRect.width / 2);
            const indicatorX = centerX - (44 / 2); 
            
            if(!animate) {
                indicator.style.transition = 'none';
            } else {
                indicator.style.transition = 'transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
            }
            
            indicator.style.transform = `translate(${indicatorX}px, 0px)`;
            indicator.style.opacity = '1';
        };

        if (activeItem) {
            updateIndicator(activeItem, false);
        }

        window.addEventListener('resize', () => {
            const act = container.querySelector('.nav-item.active');
            if(act) updateIndicator(act, false);
        });
    });
</script>
@endif
