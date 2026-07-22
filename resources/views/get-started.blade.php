<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mulai Bergabung - KAMERAKITA AI</title>
    <meta name="description" content="Panduan singkat sebelum daftar sebagai kontributor KAMERAKITA AI.">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    @php
        $manualBookUrl = asset('images/Assest/Manual Book/ManualBook_KameraKitaAI_22072026.pdf');
        $whatsappUrl = 'https://wa.me/6287886272647?text='.rawurlencode('Halo Admin KameraKita AI, saya ingin bergabung dan membutuhkan referral/invite code untuk registrasi aplikasi Minute.');

        $registrationSteps = [
            [
                'title' => 'Pastikan perangkat didukung',
                'body' => 'Gunakan iPhone 12 ke atas, Google Pixel 6 ke atas, atau Samsung Galaxy S21 ke atas agar kualitas rekaman sesuai standar.',
            ],
            [
                'title' => 'Buat email kerja di mail.tm',
                'body' => 'Buka mail.tm, buat akun email baru, lalu simpan email dan password dengan aman karena dipakai untuk proses pendaftaran.',
            ],
            [
                'title' => 'Daftar akun KameraKita AI',
                'body' => 'Gunakan email yang sudah dibuat untuk mendaftar akun KameraKita AI. Akun ini dipakai untuk dashboard dan laporan harian.',
            ],
            [
                'title' => 'Download aplikasi Minute',
                'body' => 'Install aplikasi Minute, buka aplikasinya, lalu baca dan setujui ToU serta policy sebelum mulai.',
            ],
            [
                'title' => 'Minta referral / invite code',
                'body' => 'Referral code diberikan oleh tim. Japri WhatsApp admin dulu untuk mendapatkan kode sebelum membuat akun Minute.',
            ],
            [
                'title' => 'Pilih tugas rekaman',
                'body' => 'Di aplikasi Minute, pilih kategori tugas yang tersedia seperti tidy tasks, laundry tasks, dan tugas rumah lainnya.',
            ],
        ];

        $sopItems = [
            'HP wajib terpasang di headstrap, sejajar dahi atau mata, dan hands-free dari awal sampai akhir.',
            'Arah kamera sedikit ke bawah agar tangan dan lengan terlihat utuh selama aktivitas.',
            'Gunakan cahaya yang cukup terang, tidak berkedip, tidak terlalu silau, dan tidak terlalu gelap.',
            'Video harus landscape, tidak blur, tidak slow-motion, tidak terbalik, dan tidak menampilkan wajah siapa pun.',
        ];

        $reportSteps = [
            'Ambil screenshot all-time minutes / total durasi di aplikasi.',
            'Ambil screenshot bagian kualitas atau Minutes Quality di aplikasi.',
            'Buka dashboard KameraKita AI dan klik Kirim Laporan Baru.',
            'Isi tanggal kerja, total durasi, upload dua screenshot, lalu kirim laporan.',
            'Pantau hasil QC dan status pembayaran di halaman Riwayat Laporan.',
        ];
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-sky-700">
                    <img src="{{ asset('images/Logo.webp') }}" alt="KAMERAKITA AI" class="h-full w-full object-contain">
                </span>
                <span class="font-black tracking-tight">KAMERAKITA<span class="text-indigo-600">.AI</span></span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="hidden rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 sm:inline-flex">Masuk</a>
                <a href="#mulai" class="rounded-xl bg-slate-950 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800">Lihat Step</a>
            </div>
        </div>
    </header>

    <main>
        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-16">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-sky-50 px-3 py-1 text-xs font-black uppercase tracking-widest text-sky-700">Get Started</span>
                    <h1 class="max-w-3xl text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl">
                        Baca tutorial singkat sebelum daftar sebagai kontributor.
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                        Ikuti urutan ini agar pendaftaran, perekaman video, dan laporan harian berjalan rapi sejak awal. Referral atau invite code tidak dibuka umum, jadi minta dulu melalui WhatsApp admin.
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ $whatsappUrl }}" target="_blank" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                            Minta Referral Code via WhatsApp
                        </a>
                        <a href="{{ $manualBookUrl }}" target="_blank" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                            Buka Manual Book Lengkap
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-950 p-5 text-white shadow-sm sm:p-7">
                    <span class="block text-xs font-black uppercase tracking-widest text-sky-300">Ringkasan Alur</span>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <span class="block text-xs font-bold uppercase text-slate-300">1. Siapkan akun</span>
                            <p class="mt-1 text-sm text-white">Buat email, daftar KameraKita AI, dan minta invite code.</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <span class="block text-xs font-bold uppercase text-slate-300">2. Rekam sesuai SOP</span>
                            <p class="mt-1 text-sm text-white">Gunakan headstrap, landscape, cahaya cukup, dan hindari wajah.</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <span class="block text-xs font-bold uppercase text-slate-300">3. Kirim laporan</span>
                            <p class="mt-1 text-sm text-white">Upload screenshot total durasi dan kualitas aplikasi ke dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="mulai" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-indigo-600">Sebelum Daftar</span>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">Step by Step Pendaftaran Awal</h2>
                </div>
                <span class="text-xs font-bold text-slate-400">Diambil dari Manual Book KameraKita AI</span>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($registrationSteps as $index => $step)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-sm font-black text-indigo-700">{{ $index + 1 }}</span>
                        <h3 class="mt-4 text-base font-black text-slate-950">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step['body'] }}</p>
                        @if($index === 4)
                            <a href="{{ $whatsappUrl }}" target="_blank" class="mt-4 inline-flex text-sm font-black text-emerald-700 hover:text-emerald-800">Japri Admin WA &rarr;</a>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="border-y border-slate-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <span class="text-xs font-black uppercase tracking-widest text-sky-700">SOP Rekaman</span>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">Standar agar video tidak ditolak</h2>
                    <div class="mt-5 space-y-3">
                        @foreach($sopItems as $item)
                            <div class="flex gap-3 rounded-2xl bg-white p-4 text-sm leading-6 text-slate-600">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-sky-600"></span>
                                <span>{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <span class="text-xs font-black uppercase tracking-widest text-indigo-700">Laporan Harian</span>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">Yang perlu dikirim setiap hari</h2>
                    <ol class="mt-5 space-y-3">
                        @foreach($reportSteps as $index => $step)
                            <li class="flex gap-3 rounded-2xl bg-white p-4 text-sm leading-6 text-slate-600">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-xs font-black text-indigo-700">{{ $index + 1 }}</span>
                                <span>{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-slate-950 p-6 text-white shadow-sm sm:p-8 lg:flex lg:items-center lg:justify-between lg:gap-6">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-sky-300">Siap mulai?</span>
                    <h2 class="mt-2 text-2xl font-black">Setelah memahami tutorial, lanjut daftar akun.</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Pastikan referral/invite code sudah kamu dapatkan dari admin agar proses di aplikasi Minute lancar.</p>
                </div>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:mt-0">
                    <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-slate-100">Daftar Akun KameraKita</a>
                    <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/20 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">Saya Sudah Punya Akun</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
