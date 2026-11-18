<x-app-layout>
    <x-slot name="title">Panduan</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Panduan') }}
            </h2>

            @if(false)
                <a href="{{ asset('assets/ManualBook_Atlas.pdf') }}" target="_blank" class="text-sm px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh PDF
                </a>
            @endif
        </div>
    </x-slot>

    @php
        $tutorials = [
            [
                'id' => 'kamerakita',
                'step' => 'Langkah 1',
                'category' => 'Akun KameraKita',
                'title' => 'Video Tutorial Daftar KameraKita',
                'description' => 'Buat akun baru, masukkan kode aktivasi, dan lengkapi data rekening.',
                'tip' => 'Siapkan kode aktivasi dan nomor rekening aktif sebelum mendaftar.',
                'videoUrl' => asset('assets/tutorials/tutorial-daftar-kamerakita.mp4'),
                'accent' => 'indigo',
            ],
            [
                'id' => 'atlas',
                'step' => 'Langkah 2',
                'category' => 'Aplikasi Atlas',
                'title' => 'Video Tutorial Daftar Atlas',
                'description' => 'Masuk sebagai Mitra Eksternal, lalu cek kode OTP di Mailbox KameraKita.',
                'tip' => 'Gunakan email @kamerakitaid.site Anda, lalu ambil kode OTP dari menu Mailbox.',
                'videoUrl' => asset('assets/tutorials/tutorial-daftar-atlas.mp4'),
                'accent' => 'emerald',
            ],
        ];
    @endphp

    <div
        x-data='{
            tutorials: @json($tutorials),
            activeTutorial: null,
            openTutorial(item) {
                this.activeTutorial = item;
                this.$nextTick(() => {
                    const video = this.$refs.player;
                    if (video) {
                        video.load();
                        video.play().catch(() => {});
                    }
                });
            },
            closeTutorial() {
                const video = this.$refs.player;
                if (video) {
                    video.pause();
                    video.currentTime = 0;
                }
                this.activeTutorial = null;
            }
        }'
        @keydown.escape.window="if (activeTutorial) closeTutorial()"
        class="py-6 sm:py-8"
    >
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Intro Ringkas -->
            <div class="bg-white/95 backdrop-blur border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">
                        Pusat Panduan
                    </p>
                    <h3 class="mt-1 text-lg sm:text-xl font-bold text-slate-900">
                        Mulai dari Langkah 1 ke Langkah 2
                    </h3>
                    <p class="mt-1 text-sm text-slate-600">
                        Pilih kartu tutorial di bawah untuk memutar video panduan.
                    </p>
                </div>
                <div class="inline-flex items-center gap-2 self-start sm:self-center px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-600 shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>2 Video Tersedia</span>
                </div>
            </div>

            <!-- Grid Pilihan Card Tutorial -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($tutorials as $item)
                    @php
                        $isIndigo = $item['accent'] === 'indigo';
                        $coverClasses = $isIndigo
                            ? 'from-blue-600 via-indigo-600 to-indigo-700'
                            : 'from-emerald-600 via-teal-600 to-cyan-700';
                        $badgeClasses = $isIndigo
                            ? 'bg-blue-50 text-blue-700 border-blue-100'
                            : 'bg-emerald-50 text-emerald-700 border-emerald-100';
                        $btnClasses = $isIndigo
                            ? 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'
                            : 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500';
                    @endphp

                    <article
                        @click="openTutorial(tutorials[{{ $loop->index }}])"
                        role="button"
                        tabindex="0"
                        @keydown.enter.prevent="openTutorial(tutorials[{{ $loop->index }}])"
                        @keydown.space.prevent="openTutorial(tutorials[{{ $loop->index }}])"
                        class="group cursor-pointer bg-white border border-slate-200/90 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 overflow-hidden flex flex-col focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <!-- Visual Cover Card (Video belum diputar sebelum diklik) -->
                        <div class="relative h-44 bg-gradient-to-br {{ $coverClasses }} p-5 flex flex-col justify-between overflow-hidden">
                            <div class="absolute -right-8 -bottom-10 w-40 h-40 rounded-full bg-white/10 pointer-events-none"></div>
                            <div class="absolute -left-6 top-10 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>

                            <div class="relative flex items-center justify-between">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/95 text-slate-800 shadow-sm">
                                    {{ $item['step'] }}
                                </span>
                                <span class="text-xs font-medium text-white/90 bg-black/20 backdrop-blur-sm px-2.5 py-1 rounded-full">
                                    {{ $item['category'] }}
                                </span>
                            </div>

                            <div class="relative flex items-center justify-center my-auto">
                                <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md border border-white/40 flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:bg-white/30 transition-all duration-200">
                                    <svg class="w-7 h-7 text-white translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="relative flex items-center justify-between text-xs text-white/90 font-medium">
                                <span>Klik untuk memutar video</span>
                                <span>Format MP4</span>
                            </div>
                        </div>

                        <!-- Konten Card Ringkas -->
                        <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between gap-5">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $badgeClasses }}">
                                        Video Tutorial
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-slate-900 group-hover:text-indigo-700 transition-colors">
                                    {{ $item['title'] }}
                                </h4>
                                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                                    {{ $item['description'] }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                                <span class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition-colors {{ $btnClasses }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    Tonton Tutorial
                                </span>

                                <a
                                    href="{{ $item['videoUrl'] }}"
                                    target="_blank"
                                    rel="noopener"
                                    @click.stop
                                    class="text-xs font-semibold text-slate-500 hover:text-indigo-600 inline-flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors"
                                >
                                    Tab baru
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <!-- Modal Player Video (Hanya muncul saat card diklik) -->
        <div
            x-show="activeTutorial !== null"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/75 backdrop-blur-sm"
            @click.self="closeTutorial()"
        >
            <div
                x-show="activeTutorial !== null"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh]"
            >
                <!-- Header Modal -->
                <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
                    <div>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700"
                            x-text="activeTutorial?.step"
                        ></span>
                        <h3 class="mt-1 text-base font-bold text-slate-900" x-text="activeTutorial?.title"></h3>
                    </div>
                    <button
                        type="button"
                        @click="closeTutorial()"
                        aria-label="Tutup video tutorial"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Video Frame -->
                <div class="bg-slate-950 p-3 sm:p-4 flex items-center justify-center overflow-hidden">
                    <div class="w-full max-w-[300px] aspect-[9/16] max-h-[62vh] rounded-2xl overflow-hidden bg-black shadow-inner border border-slate-800">
                        <video
                            x-ref="player"
                            :src="activeTutorial?.videoUrl || ''"
                            class="w-full h-full object-contain"
                            controls
                            playsinline
                            preload="none"
                        >
                            Browser Anda tidak mendukung pemutar video HTML5.
                        </video>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-600 flex-1 min-w-[180px]" x-text="activeTutorial?.tip"></p>
                    <div class="flex items-center gap-2 shrink-0">
                        <a
                            :href="activeTutorial?.videoUrl || '#'"
                            target="_blank"
                            rel="noopener"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors"
                        >
                            Buka di Tab Baru
                        </a>
                        <button
                            type="button"
                            @click="closeTutorial()"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 transition-colors"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(false)
        <!-- Container Utama dengan tinggi tetap agar tidak terlipat -->
        <div class="w-full bg-white relative" style="height: calc(100vh - 140px); min-height: 700px;">
            <!-- Menggunakan iframe alih-alih object untuk kompatibilitas dan caching (cache) browser yang lebih baik -->
            <iframe 
                src="{{ asset('assets/ManualBook_Atlas.pdf') }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                class="absolute top-0 left-0 w-full h-full border-none shadow-inner"
                title="Panduan KameraKita AI"
            >
                <p>Browser Anda tidak mendukung iframe PDF.</p>
            </iframe>
        </div>
    @endif
</x-app-layout>
