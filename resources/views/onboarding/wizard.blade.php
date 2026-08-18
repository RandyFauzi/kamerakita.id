<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get Started - {{ config('app.name', 'Kamerakita.ai') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        .animate-slide-in { animation: slideIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        
        /* Custom scrollbar for content areas */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-slate-50 text-slate-800 min-h-screen py-10 px-4 sm:px-6 lg:px-8 flex flex-col justify-center">

    <div class="max-w-5xl mx-auto w-full">
        <!-- Modern Logo Header -->
        <div class="mb-6 flex justify-center">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/Logo.webp') }}" alt="Kamerakita.ai" class="h-10 w-auto object-contain">
                <span class="text-xl font-black tracking-tight text-slate-800">KameraKita AI</span>
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-200 flex flex-col">
            
            <div class="p-6 sm:p-10 flex-1">
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-8 shadow-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Main Wizard Progress (Hidden during SOP reading, shown in step 2 and 3) -->
                <div id="main-wizard-progress" class="hidden mb-10 flex justify-between items-center w-full max-w-2xl mx-auto relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 z-0 rounded-full"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-600 z-0 transition-all duration-500 rounded-full" id="wizard-progress-bar" style="width: 50%;"></div>
                    
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm text-white bg-blue-600 relative z-10 shadow-md ring-4 ring-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm text-white bg-blue-600 relative z-10 shadow-sm ring-4 ring-white transition-colors duration-300" id="indicator-2">2</div>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm text-slate-400 bg-white border-2 border-slate-200 relative z-10 shadow-sm ring-4 ring-white transition-colors duration-300" id="indicator-3">3</div>
                </div>

                <form action="{{ route('onboarding.save') }}" method="POST" id="onboarding-form">
                    @csrf

                    <!-- STEP 1: SOP INTERAKTIF -->
                    <div class="wizard-step" id="step-1">
                        <!-- Header SOP -->
                        <div class="flex justify-between items-center mb-8 border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-semibold text-slate-500">Standard Operational Protocol</h2>
                            
                            <!-- SOP Sub-Progress -->
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-bold text-slate-700 w-8" id="sop-counter">1/4</span>
                                <div class="flex gap-2">
                                    <div class="h-1.5 w-8 rounded-full bg-blue-500 transition-colors" id="sop-dot-1"></div>
                                    <div class="h-1.5 w-8 rounded-full bg-slate-200 transition-colors" id="sop-dot-2"></div>
                                    <div class="h-1.5 w-8 rounded-full bg-slate-200 transition-colors" id="sop-dot-3"></div>
                                    <div class="h-1.5 w-8 rounded-full bg-slate-200 transition-colors" id="sop-dot-4"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>
                        </div>

                        <!-- SOP Content Container -->
                        <div class="min-h-[400px]">
                            <!-- SOP Slide 1: Headmount -->
                            <div class="sop-slide animate-slide-in" id="sop-slide-1">
                                <h3 class="text-3xl font-bold text-center text-slate-800 mb-8">Cara Pasang <span class="text-sky-500">Headmount</span></h3>
                                
                                <div class="flex justify-center mb-8">
                                    <div class="bg-slate-100 h-48 w-full max-w-lg rounded-xl flex items-center justify-center border border-slate-200 text-slate-400 overflow-hidden">
                                        <!-- Placeholder untuk ilustrasi Headmount -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="ml-2 font-medium">Ilustrasi Headmount (Tambahkan Gambar Nanti)</span>
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <div class="space-y-3">
                                        <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                            <p class="text-sm text-slate-700 font-medium leading-relaxed">HP terpasang di head strap, sejajar dahi/mata.</p>
                                        </div>
                                        <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                            <p class="text-sm text-slate-700 font-medium leading-relaxed">Lengan dan tangan selalu kelihatan utuh di kamera selama aktivitas berlangsung.</p>
                                        </div>
                                        <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                            <p class="text-sm text-slate-700 font-medium leading-relaxed">Kamera mengarah ke bawah (sudut ±45°).</p>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 font-medium leading-relaxed">Jangan pegang HP, kedua tangan harus bebas beraktivitas (hands-free).</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 font-medium leading-relaxed">Jangan taruh HP di meja/permukaan lain — HP wajib stay di kepala dari awal sampai akhir.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP Slide 2: Tangkapan Tangan -->
                            <div class="sop-slide hidden animate-slide-in" id="sop-slide-2">
                                <h3 class="text-3xl font-bold text-center text-slate-800 mb-10"><span class="text-sky-500">Tangkapan Tangan</span> dan Kamera</h3>
                                
                                <div class="space-y-4 max-w-4xl mx-auto">
                                    <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                        <p class="text-sm text-slate-700 font-medium leading-relaxed">Arahkan kamera sedikit ke bawah supaya tanganmu selalu kelihatan saat kamu memegang atau menyentuh barang.</p>
                                    </div>
                                    <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                        <p class="text-sm text-slate-700 font-medium leading-relaxed">Pastikan lengan dan tanganmu terus terlihat dari awal sampai akhir tugas.</p>
                                    </div>
                                    <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                        <p class="text-sm text-slate-700 font-medium leading-relaxed">Kamu boleh menggerakkan kepala seperti biasa (alami) saat sedang berjalan atau pindah ruangan.</p>
                                    </div>

                                    <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm mt-6">
                                        <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                        <p class="text-sm text-slate-700 font-medium leading-relaxed">Jangan bergerak secara tiba-tiba atau patah-patah — hasil rekaman video harus terlihat halus dan mengalir secara alami.</p>
                                    </div>
                                    <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                        <p class="text-sm text-slate-700 font-medium leading-relaxed">Jangan sampai kamera tertutup oleh badan atau pakaianmu.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP Slide 3: Kondisi Cahaya -->
                            <div class="sop-slide hidden animate-slide-in" id="sop-slide-3">
                                <h3 class="text-3xl font-bold text-center text-slate-800 mb-10">Kondisi <span class="text-sky-500">Cahaya</span></h3>
                                
                                <div class="space-y-4 max-w-4xl mx-auto">
                                    <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                        <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Cahaya terang:</strong> Boleh pakai cahaya matahari yang kuat atau cahaya lampu yang terang.</p>
                                    </div>
                                    <div class="bg-green-50 border border-green-200 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="bg-green-500 text-white rounded shrink-0 p-0.5 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                        <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Kontras tinggi:</strong> Boleh ada perpaduan antara area yang terkena cahaya dan area bayangan.</p>
                                    </div>

                                    <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm mt-6">
                                        <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                        <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Lampu berkedip:</strong> Jangan merekam video di bawah lampu yang kedap-kedip.</p>
                                    </div>
                                    <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                        <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Terlalu silau:</strong> Hindari cahaya yang terlalu putih atau silau sampai gambarnya pudar atau tidak jelas.</p>
                                    </div>
                                    <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                        <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                        <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Terlalu gelap:</strong> Kalau tanganmu sampai tidak terlihat jelas, videonya tidak akan bisa dipakai.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP Slide 4: Kriteria Penolakan -->
                            <div class="sop-slide hidden animate-slide-in" id="sop-slide-4">
                                <h3 class="text-3xl font-bold text-center text-slate-800 mb-8">Kriteria <span class="text-sky-500">Penolakan</span></h3>
                                
                                <div class="grid md:grid-cols-2 gap-4 max-w-5xl mx-auto">
                                    <div class="space-y-4">
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Terlalu lama diam:</strong> Jangan berhenti atau diam lebih dari 3 detik. Kamu harus terus bergerak melakukan tugasmu.</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Ada wajah yang terekam:</strong> Wajah siapa pun (wajahmu sendiri, pantulan wajah di kaca/cermin, atau bahkan wajah di dalam foto cetak) sama sekali tidak boleh masuk ke video.</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Rekaman vertikal:</strong> Semua video yang direkam dalam posisi portrait akan langsung ditolak. Video harus mendatar (landscape).</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Begerak terlalu cepat/lambat:</strong> Gerakan harus dibuat terlihat natural dan tidak tergesa-gesa dan tidak dibuat lambat secara sengaja.</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Video slow-motion:</strong> Rekaman gerak lambat tidak akan diterima dalam kondisi apa pun.</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Tidak pakai head mount:</strong> Video akan ditolak jika terlihat seperti HP-nya dipegang pakai tangan atau menggunakan tripod.</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Pengaturan zoom salah:</strong> Jika kamu menggunakan zoom 1x atau lebih, tapi lengan dan tanganmu malah jadi tidak terlihat jelas.</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Video terbalik:</strong> Video yang terekam dengan posisi terbalik (atas-bawah) pasti langsung ditolak.</p>
                                        </div>
                                        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 shadow-sm">
                                            <div class="text-red-500 shrink-0 font-bold text-lg leading-none mt-0.5">✕</div>
                                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold">Video buram (blur):</strong> Rekaman yang tidak fokus atau sangat buram tidak akan diterima.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SOP Controls -->
                        <div class="mt-10 flex justify-between items-center border-t border-slate-100 pt-6">
                            <button type="button" class="text-slate-500 hover:text-slate-800 font-semibold py-2 px-4 transition-colors flex items-center invisible" id="sop-btn-prev" onclick="changeSopSlide(-1)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                Sebelumnya
                            </button>
                            
                            <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-8 rounded-xl transition-all shadow-md flex items-center" id="sop-btn-next" onclick="changeSopSlide(1)">
                                Selanjutnya
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Data Pembayaran -->
                    <div class="wizard-step hidden animate-fade-in" id="step-2">
                        <div class="text-center mb-8">
                            <h4 class="text-2xl font-bold text-slate-800">Langkah 2: Data Pembayaran & Kontak</h4>
                            <p class="text-slate-500 mt-2">Pastikan data yang dimasukkan valid untuk keperluan Payroll.</p>
                        </div>
                        
                        <div class="max-w-2xl mx-auto space-y-6">
                            <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 shadow-sm">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nomor WhatsApp Aktif</label>
                                <div class="flex rounded-lg shadow-sm border border-slate-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 bg-white transition-all">
                                    <span class="inline-flex items-center px-4 bg-slate-50 text-slate-600 font-bold text-base border-r border-slate-300">
                                        +62
                                    </span>
                                    <input type="text" name="whatsapp_number" class="flex-1 block w-full border-0 p-3 text-base outline-none ring-0 focus:ring-0" placeholder="81234567890" value="{{ old('whatsapp_number', $partner->whatsapp_number ?? '') }}" required>
                                </div>
                                <p class="mt-2 text-sm text-slate-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Tim kami akan menghubungi Anda via WhatsApp ini.
                                </p>
                            </div>
                            
                            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 space-y-5 shadow-sm">
                                <h6 class="font-bold text-slate-800 text-lg flex items-center gap-2 border-b border-slate-200 pb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Informasi Rekening Bank
                                </h6>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Bank</label>
                                    <select name="bank_name" class="block w-full pl-3 pr-10 py-3 text-base border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-lg shadow-sm bg-white" required>
                                        <option value="">-- Pilih Bank Anda --</option>
                                        @php
                                            $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN', 'Mega', 'SeaBank', 'Jago', 'Neo Commerce', 'Blu BCA'];
                                            $selectedBank = old('bank_name', $partner->bank_name ?? '');
                                        @endphp
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank }}" {{ $selectedBank == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Nomor Rekening</label>
                                    <input type="text" name="bank_account_number" class="focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-base border-slate-300 rounded-lg p-3" placeholder="Contoh: 1234567890" value="{{ old('bank_account_number', $partner->bank_account_number ?? '') }}" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Pemilik Rekening</label>
                                    <input type="text" name="bank_account_owner" class="focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-base border-slate-300 rounded-lg p-3 uppercase" placeholder="Sesuai nama di buku tabungan" value="{{ old('bank_account_owner', $partner->bank_account_owner ?? '') }}" required>
                                    <p class="mt-2 text-xs text-amber-600 font-medium bg-amber-50 p-2 rounded border border-amber-100">Penting: Nama harus sama persis dengan buku tabungan agar proses transfer (payroll) tidak gagal.</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                                <button type="button" class="text-slate-500 hover:text-slate-900 font-semibold py-2 px-4 transition-colors flex items-center" onclick="nextMainStep(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Kembali Baca SOP
                                </button>
                                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center transform hover:-translate-y-0.5" onclick="nextMainStep(3)">
                                    Lanjut Persetujuan
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Persetujuan (Consent) -->
                    <div class="wizard-step hidden animate-fade-in" id="step-3">
                        <div class="text-center mb-8">
                            <h4 class="text-2xl font-bold text-slate-800">Langkah 3: Persetujuan Akhir</h4>
                            <p class="text-slate-500 mt-2">Langkah terakhir sebelum Anda mulai bekerja.</p>
                        </div>
                        
                        <div class="max-w-3xl mx-auto">
                            <div class="p-6 bg-slate-50 rounded-xl border border-slate-200 mb-8 shadow-sm">
                                <h6 class="font-bold text-lg text-slate-900 mb-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Persetujuan Kepatuhan & Kerahasiaan Data (NDA)
                                </h6>
                                <p class="mb-5 text-slate-700 leading-relaxed">Dengan menekan tombol setuju di bawah, Anda secara sadar menyatakan bahwa:</p>
                                
                                <ul class="space-y-4 mb-6">
                                    <li class="flex items-start">
                                        <svg class="h-6 w-6 text-green-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-slate-700 leading-relaxed"><strong class="text-slate-900">Kerahasiaan Data Mutlak:</strong> Anda wajib menjaga kerahasiaan seluruh video proyek, instruksi, dan data internal KameraKita. Dilarang keras menyebarkan atau memperjualbelikan data.</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-6 w-6 text-green-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-slate-700 leading-relaxed"><strong class="text-slate-900">Izin Data Diri:</strong> KameraKita berhak menyimpan nomor rekening dan kontak Anda secara aman untuk kebutuhan internal dan kelancaran pembayaran honor Anda.</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-6 w-6 text-green-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-slate-700 leading-relaxed"><strong class="text-slate-900">Kepatuhan Kualitas:</strong> Anda bersedia mengikuti standar (SOP) dari tim QC seperti yang tertera pada tahap pertama. Pelanggaran berat dapat berakibat penolakan video dan pemutusan kemitraan.</span>
                                    </li>
                                </ul>
                                
                                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r mt-4">
                                    <p class="font-bold text-red-700 text-sm">PENTING: Pelanggaran kerahasiaan data (NDA) akan ditindaklanjuti secara hukum yang berlaku di Indonesia.</p>
                                </div>
                            </div>

                            <div class="mb-8 bg-blue-50 p-5 rounded-xl border-2 border-blue-200 cursor-pointer hover:bg-blue-100 transition-colors shadow-sm group" onclick="document.getElementById('tos_accepted').click()">
                                <label class="flex items-start cursor-pointer">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <input type="checkbox" name="tos_accepted" id="tos_accepted" value="1" required class="h-6 w-6 text-blue-600 focus:ring-blue-500 border-slate-300 rounded transition-all cursor-pointer">
                                    </div>
                                    <div class="ml-4">
                                        <span class="block font-bold text-slate-900 text-lg group-hover:text-blue-800 transition-colors">Saya Menyetujui Persetujuan Ini</span>
                                        <span class="block text-sm text-slate-600 mt-1">Saya telah membaca Standard Operational Protocol di langkah 1 dan menyetujui seluruh aturan di atas secara sadar tanpa paksaan.</span>
                                    </div>
                                </label>
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-4 pt-4 border-t border-slate-100">
                                <button type="button" class="text-slate-500 hover:text-slate-900 font-semibold py-3 px-6 transition-colors flex items-center w-full sm:w-auto justify-center" onclick="nextMainStep(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Cek Kembali Data Pembayaran
                                </button>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-lg font-bold py-4 px-8 rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center justify-center w-full sm:w-auto transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none" id="btn-submit" disabled>
                                    Selesai & Mulai Bekerja
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
        
        <div class="text-center mt-6 text-slate-400 text-sm font-medium pb-8">
            &copy; {{ date('Y') }} KameraKita AI. All rights reserved.
        </div>
    </div>

    <script>
        // --- SOP Sub-Wizard Logic (Step 1) ---
        let currentSopSlide = 1;
        const totalSopSlides = 4;

        function changeSopSlide(direction) {
            // Hide current slide
            document.getElementById(`sop-slide-${currentSopSlide}`).classList.add('hidden');
            
            // Calculate new slide
            currentSopSlide += direction;
            
            // Show new slide
            document.getElementById(`sop-slide-${currentSopSlide}`).classList.remove('hidden');
            
            // Update UI (Counter & Dots)
            document.getElementById('sop-counter').innerText = `${currentSopSlide}/${totalSopSlides}`;
            for(let i=1; i<=totalSopSlides; i++) {
                let dot = document.getElementById(`sop-dot-${i}`);
                if(i <= currentSopSlide) {
                    dot.classList.replace('bg-slate-200', 'bg-blue-500');
                } else {
                    dot.classList.replace('bg-blue-500', 'bg-slate-200');
                }
            }

            // Update Buttons
            const btnPrev = document.getElementById('sop-btn-prev');
            const btnNext = document.getElementById('sop-btn-next');

            if(currentSopSlide === 1) {
                btnPrev.classList.add('invisible');
            } else {
                btnPrev.classList.remove('invisible');
            }

            if(currentSopSlide === totalSopSlides) {
                btnNext.innerHTML = `Lanjut Isi Data Profil 
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>`;
                btnNext.classList.replace('bg-blue-600', 'bg-green-600');
                btnNext.classList.replace('hover:bg-blue-700', 'hover:bg-green-700');
                btnNext.onclick = () => nextMainStep(2);
            } else {
                btnNext.innerHTML = `Selanjutnya 
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>`;
                btnNext.classList.replace('bg-green-600', 'bg-blue-600');
                btnNext.classList.replace('hover:bg-green-700', 'hover:bg-blue-700');
                btnNext.onclick = () => changeSopSlide(1);
            }
            
            // Scroll to top of form
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // --- Main Wizard Logic ---
        function nextMainStep(step) {
            // Validation before leaving step 2
            if (step === 3) {
                const wa = document.querySelector('input[name="whatsapp_number"]').value;
                const bank = document.querySelector('select[name="bank_name"]').value;
                const acc = document.querySelector('input[name="bank_account_number"]').value;
                const owner = document.querySelector('input[name="bank_account_owner"]').value;
                
                if(!wa || !bank || !acc || !owner) {
                    alert('Mohon lengkapi seluruh Data Pembayaran (WhatsApp, Bank, No. Rekening, dan Nama Pemilik) sebelum melanjutkan.');
                    return;
                }
            }

            // Hide all steps
            document.querySelectorAll('.wizard-step').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Show target step
            document.getElementById('step-' + step).classList.remove('hidden');
            
            // Handle Top Progress Bar Visibility
            const progressBarContainer = document.getElementById('main-wizard-progress');
            if(step === 1) {
                progressBarContainer.classList.add('hidden');
            } else {
                progressBarContainer.classList.remove('hidden');
                
                // Update Top Progress Bar visually
                let progress = step === 2 ? 50 : 100;
                document.getElementById('wizard-progress-bar').style.width = progress + '%';
                
                let ind2 = document.getElementById('indicator-2');
                let ind3 = document.getElementById('indicator-3');
                
                if(step >= 2) {
                    ind2.classList.replace('bg-white', 'bg-blue-600');
                    ind2.classList.replace('text-gray-500', 'text-white');
                    ind2.classList.replace('border-gray-300', 'border-blue-600');
                }
                
                if(step === 3) {
                    ind3.classList.replace('bg-white', 'bg-blue-600');
                    ind3.classList.replace('text-slate-400', 'text-white');
                    ind3.classList.replace('border-slate-200', 'border-blue-600');
                } else {
                    ind3.classList.replace('bg-blue-600', 'bg-white');
                    ind3.classList.replace('text-white', 'text-slate-400');
                    ind3.classList.replace('border-blue-600', 'border-slate-200');
                }
            }
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Enable/Disable submit button based on checkbox
        const checkbox = document.getElementById('tos_accepted');
        const btn = document.getElementById('btn-submit');
        
        checkbox.addEventListener('change', function() {
            btn.disabled = !this.checked;
            if(this.checked) {
                btn.classList.add('ring-4', 'ring-green-200', 'scale-105');
            } else {
                btn.classList.remove('ring-4', 'ring-green-200', 'scale-105');
            }
        });
        
        // Prevent double submit
        document.getElementById('onboarding-form').addEventListener('submit', function() {
            if(checkbox.checked) {
                btn.innerHTML = 'Memproses...';
                btn.classList.add('opacity-75');
                btn.disabled = true;
            }
        });
    </script>
</body>
</html>
