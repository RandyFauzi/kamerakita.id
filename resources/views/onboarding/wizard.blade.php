<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Get Started - {{ config('app.name', 'Kamerakita.ai') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .slide-enter-active, .slide-leave-active { transition: all 0.3s ease; }
        .slide-enter-from { opacity: 0; transform: translateX(20px); }
        .slide-leave-to { opacity: 0; transform: translateX(-20px); display: none; }
        
        .slide-enter-reverse-from { opacity: 0; transform: translateX(-20px); }
        .slide-leave-reverse-to { opacity: 0; transform: translateX(20px); display: none; }
        
        /* Hide scrollbar for clean mobile look */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white min-h-screen text-slate-800 flex flex-col items-center">

    <!-- App Container: Full Screen Mobile & Wide Desktop -->
    <div class="w-full h-[100dvh] sm:h-auto sm:min-h-screen sm:max-w-2xl relative flex flex-col overflow-hidden">
        
        <!-- Top Navigation Bar (Fixed at top inside container) -->
        <div class="px-6 py-6 sm:py-8 flex items-center justify-between bg-white z-10 shrink-0 hidden opacity-0 transition-opacity duration-300" id="top-nav">
            <button type="button" id="btn-back" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-600 hover:bg-slate-100 transition-colors invisible" onclick="navigate(-1)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            
            <div class="flex-1 flex justify-center px-4">
                <!-- Progress Bars -->
                <div class="flex gap-2 w-full max-w-[200px]">
                    <div class="h-2 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-1"></div>
                    <div class="h-2 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-2"></div>
                    <div class="h-2 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-3"></div>
                    <div class="h-2 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-4"></div>
                    <div class="h-2 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-5"></div>
                    <div class="h-2 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-6"></div>
                </div>
            </div>
            
            <div class="flex items-center gap-4 text-right">
                <span class="text-sm font-bold text-slate-400" id="step-counter">1/6</span>
                <form method="POST" action="{{ route('logout') }}" class="inline m-0 p-0">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition-colors" title="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar relative" id="content-area">
            <form action="{{ route('onboarding.save') }}" method="POST" id="onboarding-form" class="h-full relative flex flex-col justify-center">
                @csrf

                <!-- SCREEN 0: Welcome Screen -->
                <div class="step-screen w-full h-full flex-1 flex flex-col pt-6 relative overflow-hidden" id="screen-0">
                    <!-- Top Content -->
                    <div class="flex flex-col items-center flex-shrink-0 z-10 px-6 sm:px-10">
                        <!-- Logo -->
                        <div class="mb-6 mt-2">
                            <img src="{{ asset('images/onboarding/kamerakita.png') }}" alt="Logo" class="h-10 w-auto mx-auto drop-shadow-sm object-contain" onerror="this.outerHTML='<div class=\'w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/30 flex items-center justify-center mx-auto\'><svg class=\'w-8 h-8 text-white\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z\'></path></svg></div>'">
                        </div>
                        
                        <h1 class="text-3xl font-extrabold text-slate-900 mb-3 leading-tight text-center">
                            Welcome to <br>
                            <span class="text-blue-600">KameraKita AI</span>
                        </h1>
                        <p class="text-base text-slate-500 max-w-xs mx-auto text-center leading-relaxed">
                            Langkah awal Anda untuk berkontribusi merekam aktivitas dan mendapatkan penghasilan!
                        </p>
                    </div>

                    <!-- Bottom Illustration & Button -->
                    <div class="relative flex-1 w-full mt-4 flex justify-center min-h-0">
                        <!-- Illustration filling the bottom -->
                        <img src="{{ asset('images/onboarding/welcome-fix.webp') }}" alt="Welcome to KameraKita AI" class="w-full max-w-md h-full object-contain object-bottom drop-shadow-md">
                        
                        <!-- White Gradient Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-white via-white/90 to-transparent z-10"></div>
                        
                        <!-- Get Started Button -->
                        <div class="absolute bottom-8 left-0 right-0 px-6 sm:px-10 z-20 flex justify-center">
                            <button type="button" class="w-full max-w-xs bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 rounded-2xl shadow-[0_8px_16px_rgba(37,99,235,0.2)] transition-all active:scale-[0.98]" onclick="navigate(1)">
                                Mulai
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SCREEN 1: SOP Headmount -->
                <div class="step-screen w-full px-6 sm:px-10 pb-10 hidden" id="screen-1">
                    <div class="flex justify-center mb-8 mt-2">
                        <img src="{{ asset('images/onboarding/get-started.webp') }}" alt="Onboarding" class="w-full max-w-sm sm:max-w-md h-auto object-contain drop-shadow-md">
                    </div>
                    
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Cara Pasang Headmount</h2>
                        <p class="text-sm text-slate-500">Pahami aturan pengambilan video (SOP) agar video Anda tidak ditolak.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex gap-4 items-start p-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium">HP terpasang di head strap, sejajar dahi/mata. Kamera mengarah ke bawah (±45°).</p>
                        </div>
                        <div class="flex gap-4 items-start p-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium">Lengan dan tangan selalu kelihatan utuh di kamera selama aktivitas berlangsung.</p>
                        </div>
                        <div class="flex gap-4 items-start p-4 bg-red-50 border border-red-100 rounded-2xl">
                            <div class="bg-red-100 text-red-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></div>
                            <p class="text-sm text-red-900 leading-relaxed">Jangan pegang HP, kedua tangan harus bebas. Jangan taruh HP di meja.</p>
                        </div>
                    </div>
                </div>

                <!-- SCREEN 2: SOP Tangkapan Tangan -->
                <div class="step-screen w-full px-6 sm:px-10 pb-10 hidden" id="screen-2">
                    <div class="text-center mb-8 mt-10">
                        <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Tangkapan Tangan</h2>
                        <p class="text-sm text-slate-500">Pastikan aktivitas tangan terlihat jelas oleh kamera selama perekaman.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex gap-4 items-start p-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium">Arahkan kamera sedikit ke bawah supaya tangan selalu kelihatan saat memegang barang.</p>
                        </div>
                        <div class="flex gap-4 items-start p-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium">Kamu boleh menggerakkan kepala secara alami saat berjalan pindah ruangan.</p>
                        </div>
                        <div class="flex gap-4 items-start p-4 bg-red-50 border border-red-100 rounded-2xl">
                            <div class="bg-red-100 text-red-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></div>
                            <p class="text-sm text-red-900 leading-relaxed">Jangan bergerak tiba-tiba / patah-patah. Jangan biarkan kamera tertutup pakaian.</p>
                        </div>
                    </div>
                </div>

                <!-- SCREEN 3: SOP Cahaya -->
                <div class="step-screen w-full px-6 sm:px-10 pb-10 hidden" id="screen-3">
                    <div class="text-center mb-8 mt-10">
                        <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Kondisi Cahaya</h2>
                        <p class="text-sm text-slate-500">Pencahayaan sangat penting untuk kualitas data video AI.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex gap-4 items-start p-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>
                            <p class="text-sm text-slate-700 leading-relaxed"><strong class="font-bold text-slate-900 block">Cahaya Terang & Kontras:</strong> Boleh pakai cahaya matahari kuat/lampu terang. Boleh ada perpaduan cahaya & bayangan.</p>
                        </div>
                        
                        <div class="flex gap-4 items-start p-4 bg-red-50 border border-red-100 rounded-2xl">
                            <div class="bg-red-100 text-red-600 p-2 rounded-xl shrink-0"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></div>
                            <div class="text-sm text-red-900 leading-relaxed">
                                <ul class="list-disc pl-4 space-y-1">
                                    <li>Jangan merekam di bawah lampu kedap-kedip.</li>
                                    <li>Hindari cahaya terlalu silau hingga gambar pudar.</li>
                                    <li>Hindari terlalu gelap hingga tangan tidak jelas.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SCREEN 4: SOP Penolakan -->
                <div class="step-screen w-full px-6 sm:px-10 pb-10 hidden" id="screen-4">
                    <div class="text-center mb-6 mt-10">
                        <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Kriteria Penolakan</h2>
                        <p class="text-sm text-slate-500">Video langsung DITOLAK jika memenuhi kriteria ini.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex gap-3 items-center p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                            <div class="text-red-500 font-bold shrink-0">✕</div>
                            <p class="text-xs text-slate-700 font-medium">Terlalu lama diam (> 3 detik).</p>
                        </div>
                        <div class="flex gap-3 items-center p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                            <div class="text-red-500 font-bold shrink-0">✕</div>
                            <p class="text-xs text-slate-700 font-medium">Ada wajah terekam (termasuk kaca/foto).</p>
                        </div>
                        <div class="flex gap-3 items-center p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                            <div class="text-red-500 font-bold shrink-0">✕</div>
                            <p class="text-xs text-slate-700 font-medium">Rekaman vertikal/portrait (Wajib Landscape).</p>
                        </div>
                        <div class="flex gap-3 items-center p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                            <div class="text-red-500 font-bold shrink-0">✕</div>
                            <p class="text-xs text-slate-700 font-medium">Bergerak sengaja dipercepat/diperlambat.</p>
                        </div>
                        <div class="flex gap-3 items-center p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                            <div class="text-red-500 font-bold shrink-0">✕</div>
                            <p class="text-xs text-slate-700 font-medium">HP dipegang tangan / pakai tripod.</p>
                        </div>
                        <div class="flex gap-3 items-center p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                            <div class="text-red-500 font-bold shrink-0">✕</div>
                            <p class="text-xs text-slate-700 font-medium">Video terbalik (atas-bawah).</p>
                        </div>
                    </div>
                </div>

                <!-- SCREEN 5: Data Pembayaran -->
                <div class="step-screen w-full px-6 sm:px-10 pb-10 hidden" id="screen-5">
                    <div class="mb-8 mt-4 text-center">
                        <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Profil Data</h2>
                        <p class="text-sm text-slate-500">Lengkapi data untuk keperluan komunikasi dan pencairan honor (Payroll).</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Country of Residence -->
                        <div>
                            <label class="block text-sm font-bold text-slate-800 mb-2">Negara Tempat Tinggal (Country of Residence)</label>
                            <select name="country_code" id="input-country" class="w-full p-3.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 font-medium shadow-sm outline-none" required>
                                @php
                                    $countries = config('countries');
                                    $selectedCountry = old('country_code', $partner->country_code ?? 'ID');
                                @endphp
                                @foreach($countries as $code => $country)
                                    <option value="{{ $code }}" data-code="{{ $country['phone_code'] }}" {{ $selectedCountry == $code ? 'selected' : '' }}>
                                        {{ $country['name'] }} ({{ $country['phone_code'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- WhatsApp -->
                        <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100">
                            <label class="block text-sm font-bold text-slate-800 mb-2">No. WhatsApp Aktif</label>
                            <div class="flex rounded-xl shadow-sm border border-slate-200 overflow-hidden bg-white focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                                <span id="wa-prefix" class="inline-flex items-center px-4 bg-slate-50 text-slate-500 font-bold border-r border-slate-200">{{ $countries[$selectedCountry]['phone_code'] ?? '+62' }}</span>
                                <input type="tel" name="whatsapp_number" id="input-wa" class="flex-1 block w-full border-0 p-3.5 text-slate-900 font-medium focus:ring-0 outline-none" placeholder="81234567890" value="{{ old('whatsapp_number', $partner->whatsapp_number ?? '') }}" required>
                            </div>
                        </div>
                        
                        <!-- Bank Info (Indonesia) -->
                        <div id="indonesia-payment" class="space-y-4">
                            <input type="hidden" name="payment_method" id="input-payment-method" value="bank_transfer">
                            <h3 class="font-bold text-slate-800 text-lg border-b border-slate-100 pb-2">Informasi Rekening Bank (Indonesia)</h3>
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Bank</label>
                                <select name="bank_name" id="input-bank" class="w-full p-3.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 font-medium shadow-sm outline-none" required>
                                    <option value="">Pilih Bank...</option>
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
                                <input type="text" name="bank_account_number" id="input-acc" class="w-full p-3.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 font-medium shadow-sm outline-none" placeholder="Contoh: 1234567890" value="{{ old('bank_account_number', $partner->bank_account_number ?? '') }}" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Pemilik Rekening</label>
                                <input type="text" name="bank_account_owner" id="input-owner" class="w-full p-3.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 font-medium uppercase shadow-sm outline-none" placeholder="Sesuai buku tabungan" value="{{ old('bank_account_owner', $partner->bank_account_owner ?? '') }}" required>
                                <p class="mt-2 text-xs text-amber-600 font-medium flex items-start gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                    Nama harus sesuai buku tabungan.
                                </p>
                            </div>
                        </div>

                        <!-- International Payment (AirTM) -->
                        <div id="international-payment" class="space-y-4 hidden">
                            <h3 class="font-bold text-slate-800 text-lg border-b border-slate-100 pb-2">International Payment</h3>
                            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex gap-3 text-sm text-indigo-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p>International payments are currently processed exclusively through <strong>AirTM</strong>.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">AirTM Username / Email</label>
                                <input type="text" name="airtm_username" id="input-airtm" class="w-full p-3.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 font-medium shadow-sm outline-none" placeholder="e.g. john_doe" value="{{ old('airtm_username', $partner->airtm_username ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SCREEN 6: Persetujuan (Consent) -->
                <div class="step-screen w-full px-6 sm:px-10 pb-10 hidden" id="screen-6">
                    <div class="mb-8 mt-4 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Satu Langkah Lagi</h2>
                        <p class="text-sm text-slate-500">Konfirmasi persetujuan keamanan data (NDA) sebelum bekerja.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                            <h4 class="font-bold text-slate-800 mb-3 text-sm">Dengan ini saya menyatakan:</h4>
                            <ul class="space-y-3">
                                <li class="flex items-start">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-2 mr-3 shrink-0"></div>
                                    <span class="text-sm text-slate-600 leading-relaxed">Menjaga kerahasiaan seluruh data proyek, video, dan instruksi kerja.</span>
                                </li>
                                <li class="flex items-start">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-2 mr-3 shrink-0"></div>
                                    <span class="text-sm text-slate-600 leading-relaxed">Tidak akan menyebarkan atau memperjualbelikan data ke pihak manapun.</span>
                                </li>
                                <li class="flex items-start">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-2 mr-3 shrink-0"></div>
                                    <span class="text-sm text-slate-600 leading-relaxed">Bersedia mengikuti standar kualitas QC yang telah dibaca sebelumnya.</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="p-4 bg-red-50 rounded-2xl border border-red-100 flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-xs text-red-800 font-medium leading-relaxed">Pelanggaran kebocoran data (NDA) akan ditindaklanjuti secara hukum.</p>
                        </div>

                        <!-- Checkbox Agreement -->
                        <div class="mt-6 border-2 border-slate-200 rounded-2xl p-1 transition-colors group cursor-pointer" id="agreement-container">
                            <label class="flex items-center p-3 cursor-pointer w-full">
                                <div class="relative flex items-center justify-center w-6 h-6 mr-4 shrink-0">
                                    <input type="checkbox" name="tos_accepted" id="tos_accepted" value="1" class="peer appearance-none w-6 h-6 border-2 border-slate-300 rounded bg-white checked:bg-blue-600 checked:border-blue-600 transition-colors cursor-pointer">
                                    <svg class="absolute w-4 h-4 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </div>
                                <span class="font-bold text-slate-800 text-sm group-hover:text-blue-700 transition-colors">Saya setuju & siap bekerja.</span>
                            </label>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- Sticky Bottom Action Bar (Fixed at bottom inside container) -->
        <div class="px-6 py-6 bg-white z-20 shrink-0 border-t border-slate-50 hidden opacity-0 transition-opacity duration-300" id="bottom-action-bar">
            <button type="button" id="btn-next" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 rounded-2xl shadow-[0_8px_16px_rgba(37,99,235,0.2)] transition-all active:scale-[0.98]" onclick="navigate(1)">
                Lanjut
            </button>
            <button type="submit" form="onboarding-form" id="btn-submit" class="hidden w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 rounded-2xl shadow-[0_8px_16px_rgba(37,99,235,0.2)] transition-all disabled:opacity-50 disabled:bg-slate-400 disabled:shadow-none" disabled>
                Selesai & Akses Dashboard
            </button>
        </div>

    </div>

    <script>
        let currentStep = 0;
        const totalSteps = 6;
        let isAnimating = false;

        function navigate(direction) {
            if (isAnimating) return;
            
            // Validate Screen 5 (Data) before going to 6
            if (currentStep === 5 && direction === 1) {
                const wa = document.getElementById('input-wa').value;
                const isID = document.getElementById('input-country').value === 'ID';
                
                let isValid = true;
                if (!wa) isValid = false;
                
                if (isID) {
                    const bank = document.getElementById('input-bank').value;
                    const acc = document.getElementById('input-acc').value;
                    const owner = document.getElementById('input-owner').value;
                    if(!bank || !acc || !owner) isValid = false;
                } else {
                    const airtm = document.getElementById('input-airtm').value;
                    if(!airtm) isValid = false;
                }
                
                if(!isValid) {
                    const container = document.getElementById('screen-5');
                    container.classList.add('animate-[shake_0.5s_ease-in-out]');
                    setTimeout(() => container.classList.remove('animate-[shake_0.5s_ease-in-out]'), 500);
                    return;
                }
            }

            const newStep = currentStep + direction;
            if (newStep < 0 || newStep > totalSteps) return;

            isAnimating = true;
            
            const oldScreen = document.getElementById(`screen-${currentStep}`);
            const newScreen = document.getElementById(`screen-${newStep}`);
            
            // Fade out old screen
            oldScreen.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            oldScreen.classList.add('opacity-0', direction > 0 ? '-translate-x-10' : 'translate-x-10');
            
            setTimeout(() => {
                // Hide old screen completely
                oldScreen.classList.add('hidden');
                oldScreen.classList.remove('opacity-0', '-translate-x-10', 'translate-x-10');
                
                // Show new screen (transparent and offset initially)
                newScreen.classList.remove('hidden');
                newScreen.classList.add('opacity-0', direction > 0 ? 'translate-x-10' : '-translate-x-10');
                
                void newScreen.offsetWidth; // trigger reflow
                
                // Fade in new screen
                newScreen.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                newScreen.classList.remove('opacity-0', 'translate-x-10', '-translate-x-10');
                
                updateUI(newStep);
                currentStep = newStep;
                
                setTimeout(() => { 
                    isAnimating = false; 
                    document.getElementById('content-area').scrollTo({top: 0, behavior: 'smooth'});
                }, 200);
            }, 200);
        }

        function updateUI(step) {
            const topNav = document.getElementById('top-nav');
            const bottomBar = document.getElementById('bottom-action-bar');

            if (step === 0) {
                topNav.classList.add('hidden', 'opacity-0');
                topNav.classList.remove('opacity-100');
                bottomBar.classList.add('hidden', 'opacity-0');
                bottomBar.classList.remove('opacity-100');
            } else {
                topNav.classList.remove('hidden');
                bottomBar.classList.remove('hidden');
                // Trigger reflow to apply transition
                void topNav.offsetWidth; 
                void bottomBar.offsetWidth;
                topNav.classList.remove('opacity-0');
                topNav.classList.add('opacity-100');
                bottomBar.classList.remove('opacity-0');
                bottomBar.classList.add('opacity-100');
            }

            if (step > 0) {
                document.getElementById('step-counter').innerText = `${step}/${totalSteps}`;
                
                for(let i=1; i<=totalSteps; i++) {
                    const prog = document.getElementById(`prog-${i}`);
                    if(i <= step) {
                        prog.classList.replace('bg-slate-200', 'bg-blue-600');
                    } else {
                        prog.classList.replace('bg-blue-600', 'bg-slate-200');
                    }
                }

                const btnBack = document.getElementById('btn-back');
                if(step === 1) {
                    btnBack.classList.add('invisible');
                } else {
                    btnBack.classList.remove('invisible');
                }

                const btnNext = document.getElementById('btn-next');
                const btnSubmit = document.getElementById('btn-submit');
                
                if(step === totalSteps) {
                    btnNext.classList.add('hidden');
                    btnSubmit.classList.remove('hidden');
                } else {
                    btnNext.classList.remove('hidden');
                    btnSubmit.classList.add('hidden');
                }
            }
        }

        
        // Handle Country Change
        document.getElementById('input-country').addEventListener('change', function(e) {
            const selectedOption = this.options[this.selectedIndex];
            const countryCode = this.value;
            const phoneCode = selectedOption.getAttribute('data-code');
            
            // Update WA Prefix
            document.getElementById('wa-prefix').innerText = phoneCode;
            
            const indoSection = document.getElementById('indonesia-payment');
            const intlSection = document.getElementById('international-payment');
            const paymentMethodInput = document.getElementById('input-payment-method');
            
            const bankInput = document.getElementById('input-bank');
            const accInput = document.getElementById('input-acc');
            const ownerInput = document.getElementById('input-owner');
            const airtmInput = document.getElementById('input-airtm');
            
            if (countryCode === 'ID') {
                indoSection.classList.remove('hidden');
                intlSection.classList.add('hidden');
                
                paymentMethodInput.value = 'bank_transfer';
                
                // Enable Indonesia inputs, require them
                bankInput.disabled = false; bankInput.required = true;
                accInput.disabled = false; accInput.required = true;
                ownerInput.disabled = false; ownerInput.required = true;
                
                // Disable AirTM inputs, remove require
                airtmInput.disabled = true; airtmInput.required = false;
            } else {
                indoSection.classList.add('hidden');
                intlSection.classList.remove('hidden');
                
                paymentMethodInput.value = 'airtm';
                
                // Disable Indonesia inputs, remove require
                bankInput.disabled = true; bankInput.required = false;
                accInput.disabled = true; accInput.required = false;
                ownerInput.disabled = true; ownerInput.required = false;
                
                // Enable AirTM inputs, require them
                airtmInput.disabled = false; airtmInput.required = true;
                
                // Confirm clear data if bank had values
                if (bankInput.value || accInput.value || ownerInput.value) {
                    if (confirm("Changing your country will switch your payment method and clear the current bank details. Proceed?")) {
                        bankInput.value = '';
                        accInput.value = '';
                        ownerInput.value = '';
                    } else {
                        // Revert country selection
                        this.value = 'ID';
                        this.dispatchEvent(new Event('change'));
                    }
                }
            }
        });
        
        // Trigger initial load
        document.getElementById('input-country').dispatchEvent(new Event('change'));

        const checkbox = document.getElementById('tos_accepted');
        const submitBtn = document.getElementById('btn-submit');
        const agreeContainer = document.getElementById('agreement-container');

        checkbox.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
            if(this.checked) {
                agreeContainer.classList.replace('border-slate-200', 'border-blue-500');
                agreeContainer.classList.add('bg-blue-50/50');
            } else {
                agreeContainer.classList.replace('border-blue-500', 'border-slate-200');
                agreeContainer.classList.remove('bg-blue-50/50');
            }
        });

        document.getElementById('onboarding-form').addEventListener('submit', function() {
            if(checkbox.checked) {
                submitBtn.innerHTML = 'Memproses...';
                submitBtn.disabled = true;
            }
        });

        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-4px); }
            }
            .animate-float {
                animation: float 3s ease-in-out infinite;
            }
        `;
        document.head.appendChild(style);
    </script>
    
    <!-- Watermark -->
    <div class="fixed bottom-4 right-5 z-50 opacity-60 hover:opacity-100 transition-all duration-300 animate-float group cursor-default">
        <p class="text-[10px] sm:text-xs font-bold text-slate-400 tracking-wide uppercase group-hover:text-blue-700 transition-colors duration-300">
            KameraKita AI 
            <span class="font-normal normal-case group-hover:text-slate-900 transition-colors duration-300">powered by</span> 
            <span class="group-hover:text-slate-900 transition-colors duration-300">UNEVA AI</span>
        </p>
    </div>
</body>
</html>
