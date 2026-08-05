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
                    radial-gradient(circle at 70% 30%, rgba(139, 117, 255, 0.4), transparent 60%);
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
        background: #8b75ff; 
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
    
    <!-- MAGIC ORB -->
    <div class="orb-wrapper hidden sm:block">
        <div class="orb-glow"></div>
        <div class="orb-sphere">
            <div class="orb-color-orange"></div>
            <div class="orb-color-purple"></div>
        </div>
        <div class="orb-glass-layer"></div>
    </div>

    <!-- MAGIC ORB (MOBILE POSITIONING) -->
    <div class="orb-wrapper sm:hidden" style="right: -10px; top: -10px; opacity: 0.6;">
        <div class="orb-glow"></div>
        <div class="orb-sphere">
            <div class="orb-color-orange"></div>
            <div class="orb-color-purple"></div>
        </div>
        <div class="orb-glass-layer"></div>
    </div>

    <!-- TOP CONTENT -->
    <div class="relative z-10 pt-7 px-7 pb-6">
        
        <!-- Header -->
        <div class="mb-8 pr-8 sm:pr-28">
            <div class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 font-mono mb-2">{{ $subtitle }}</div>
            <div class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight break-all">{!! $title !!}</div>
        </div>

        <!-- Grid Info -->
        <div class="flex flex-wrap items-center gap-5">
            <!-- Left -->
            <div class="flex flex-col">
                <span class="text-[11px] text-gray-400 font-medium mb-1 uppercase tracking-wider">{{ $label1 }}</span>
                <span class="text-sm font-bold text-gray-700">{{ $value1 }}</span>
            </div>

            <!-- Divider -->
            <div class="w-px h-8 bg-gray-200"></div>

            <!-- Right -->
            <div class="flex flex-col">
                <span class="text-[11px] text-gray-400 font-medium mb-1 uppercase tracking-wider">{{ $label2 }}</span>
                <span class="text-sm font-bold text-gray-700">{{ $value2 }}</span>
            </div>
        </div>

    </div>

    <!-- HORIZONTAL DIVIDER -->
    <div class="h-px w-full bg-gradient-to-r from-slate-200/20 via-slate-200/80 to-slate-200/20 relative z-10 mt-auto"></div>

    <!-- BOTTOM ACTION -->
    @if(isset($actionSlot))
        {{ $actionSlot }}
    @else
        <a href="{{ $actionUrl }}" class="relative z-10 px-7 py-4 cursor-pointer hover:bg-black/5 transition-colors flex justify-between items-center rounded-b-[28px] group">
            <span class="text-sm font-bold text-indigo-700 group-hover:text-indigo-800">{{ $actionText }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-indigo-600 transition-transform group-hover:translate-x-1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
    @endif

</div>