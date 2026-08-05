@props([
    'title' => '',
    'subtitle' => '',
    'label1' => '',
    'value1' => '',
    'label2' => '',
    'value2' => '',
    'actionText' => '',
    'actionUrl' => '#'
])

<style>
    .orb-card-container {
        position: relative;
        width: 100%;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        border-radius: 28px;
        box-shadow: 
            0 20px 40px -15px rgba(0, 0, 0, 0.05),
            0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        overflow: hidden;
        text-align: left;
    }

    /* --- THE ORB --- */
    .orb-wrapper {
        position: absolute;
        right: 20px;
        top: 25px;
        width: 110px;
        height: 110px;
        z-index: 1;
        pointer-events: none;
    }

    .orb-glow {
        position: absolute;
        inset: -15px;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 70%, rgba(255, 157, 118, 0.4), transparent 60%),
                    radial-gradient(circle at 70% 30%, rgba(37, 99, 235, 0.4), transparent 60%);
        filter: blur(15px);
        z-index: -1;
    }

    .orb-sphere {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .orb-color-orange {
        position: absolute;
        bottom: -15%;
        left: -15%;
        width: 95%;
        height: 95%;
        background: #ff9d76; 
        border-radius: 50%;
        filter: blur(12px);
    }

    .orb-color-purple {
        position: absolute;
        top: -15%;
        right: -15%;
        width: 85%;
        height: 85%;
        background: #2563eb; 
        border-radius: 50%;
        filter: blur(12px);
    }

    .orb-glass-layer {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        box-shadow: 
            inset 10px 10px 18px rgba(255, 255, 255, 1),
            inset -6px -6px 15px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.7);
        z-index: 2;
    }

    @keyframes softFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .orb-wrapper {
        animation: softFloat 6s ease-in-out infinite;
    }
</style>

<div class="orb-card-container flex flex-col h-full justify-between">
    
    <!-- MAGIC ORB (DESKTOP) -->
    <div class="orb-wrapper hidden sm:block">
        <div class="orb-glow"></div>
        <div class="orb-sphere">
            <div class="orb-color-orange"></div>
            <div class="orb-color-purple"></div>
        </div>
        <div class="orb-glass-layer"></div>
    </div>

    <!-- MAGIC ORB (MOBILE) -->
    <div class="orb-wrapper sm:hidden" style="right: -20px; top: 0px; opacity: 0.5; transform: scale(0.8);">
        <div class="orb-glow"></div>
        <div class="orb-sphere">
            <div class="orb-color-orange"></div>
            <div class="orb-color-purple"></div>
        </div>
        <div class="orb-glass-layer"></div>
    </div>

    <!-- TOP CONTENT -->
    <div class="relative z-10 pt-6 sm:pt-8 px-6 sm:px-8 pb-6 flex-1 flex flex-col justify-center">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 w-full">
            <!-- Header -->
            <div class="pr-0 sm:pr-20">
                <div class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 font-mono mb-2">{{ $subtitle }}</div>
                <div class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight break-words">{{ $title }}</div>
            </div>

            <!-- Grid Info -->
            <div class="flex items-center gap-5 sm:gap-6 shrink-0 bg-white/40 p-4 rounded-2xl backdrop-blur-sm border border-white/60">
                <!-- Left -->
                <div class="flex flex-col">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold mb-1 uppercase tracking-wider font-mono">{{ $label1 }}</span>
                    <span class="text-sm sm:text-base font-bold text-slate-800">{{ $value1 }}</span>
                </div>

                <!-- Divider -->
                <div class="w-px h-10 bg-slate-200/80"></div>

                <!-- Right -->
                <div class="flex flex-col">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold mb-1 uppercase tracking-wider font-mono">{{ $label2 }}</span>
                    <span class="text-sm sm:text-base font-bold text-slate-800">{{ $value2 }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- HORIZONTAL DIVIDER -->
    <div class="h-px w-full bg-gradient-to-r from-slate-200/20 via-slate-200/80 to-slate-200/20 relative z-10"></div>

    <!-- BOTTOM ACTION -->
    @if(isset($actionSlot))
        <div class="relative z-10 bg-white/20 hover:bg-white/40 transition-colors backdrop-blur-md rounded-b-[28px]">
            {{ $actionSlot }}
        </div>
    @else
        <a href="{{ $actionUrl }}" class="relative z-10 px-6 sm:px-8 py-4 sm:py-5 cursor-pointer bg-white/20 hover:bg-white/40 transition-colors backdrop-blur-md flex justify-between sm:justify-start sm:gap-3 items-center rounded-b-[28px] group">
            <span class="text-sm sm:text-base font-bold text-blue-700 group-hover:text-blue-900">{{ $actionText }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-blue-600 transition-transform group-hover:translate-x-1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
    @endif

</div>