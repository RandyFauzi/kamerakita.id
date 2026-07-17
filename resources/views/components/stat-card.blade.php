@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
])

<div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-all duration-300 transform hover:-translate-y-0.5">
    <div class="space-y-2">
        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $title }}</span>
        <span class="block text-3xl font-black text-slate-800 tracking-tight">{{ $value }}</span>
        @if($trend)
            <div class="flex items-center gap-1 mt-1">
                <span class="text-xs font-semibold {{ $trendUp ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $trend }}
                </span>
            </div>
        @endif
    </div>

    @if($icon)
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-100">
            {!! $icon !!}
        </div>
    @endif
</div>
