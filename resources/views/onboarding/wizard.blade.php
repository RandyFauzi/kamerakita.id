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
<body class="antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen text-slate-800 flex items-center justify-center sm:py-12 sm:px-6">

    <!-- App Container: Mobile (Full Screen) | Desktop (Centered Phone-like Frame) -->
    <div class="bg-white w-full h-[100dvh] sm:h-auto sm:min-h-[800px] sm:max-w-md sm:rounded-[40px] sm:shadow-2xl sm:border-[8px] sm:border-white relative flex flex-col overflow-hidden">
        
        <!-- Top Navigation Bar (Fixed at top inside container) -->
        <div class="px-6 py-5 flex items-center justify-between bg-white z-10 shrink-0">
            <button type="button" id="btn-back" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-600 hover:bg-slate-100 transition-colors invisible" onclick="navigate(-1)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            
            <div class="flex-1 flex justify-center px-4">
                <!-- Progress Bars -->
                <div class="flex gap-1.5 w-full max-w-[150px]">
                    <div class="h-1.5 rounded-full flex-1 bg-blue-600 transition-colors" id="prog-1"></div>
                    <div class="h-1.5 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-2"></div>
                    <div class="h-1.5 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-3"></div>
                    <div class="h-1.5 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-4"></div>
                    <div class="h-1.5 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-5"></div>
                    <div class="h-1.5 rounded-full flex-1 bg-slate-200 transition-colors" id="prog-6"></div>
                </div>
            </div>
            
            <div class="w-10 text-right">
                <span class="text-xs font-bold text-slate-400" id="step-counter">1/6</span>
            </div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar px-6 relative" id="content-area">
            <form action="{{ route('onboarding.save') }}" method="POST" id="onboarding-form" class="h-full relative pb-10">
                @csrf

                <!-- SCREEN 1: SOP Headmount -->
                <div class="step-screen w-full pb-10" id="screen-1">
                    <div class="flex justify-center mb-8 mt-4">
                        <img src="{{ asset('images/onboarding/get-started.webp') }}" alt="Onboarding" class="w-48 h-auto object-contain drop-shadow-md">
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
                <div class="step-screen w-full pb-10 hidden" id="screen-2">
                    <div class="flex justify-center mb-8 mt-4">
                        <img src="{{ asset('images/onboarding/get-started.webp') }}" alt="Onboarding" class="w-48 h-auto object-contain drop-shadow-md">
                    </div>
                    
                    <div class="text-center mb-8">
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
                <div class="step-screen w-full pb-10 hidden" id="screen-3">
                    <div class="flex justify-center mb-8 mt-4">
                        <img src="{{ asset('images/onboarding/get-started.webp') }}" alt="Onboarding" class="w-48 h-auto object-contain drop-shadow-md">
                    </div>
                    
                    <div class="text-center mb-8">
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
                <div class="step-screen w-full pb-10 hidden" id="screen-4">
                    <div class="flex justify-center mb-6 mt-4">
                        <img src="{{ asset('images/onboarding/get-started.webp') }}" alt="Onboarding" class="w-48 h-auto object-contain drop-shadow-md">
                    </div>
                    
                    <div class="text-center mb-6">
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
                <div class="step-screen w-full pb-10 hidden" id="screen-5">
                    <div class="mb-8 mt-4 text-center">
                        <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Profil Data</h2>
                        <p class="text-sm text-slate-500">Lengkapi data untuk keperluan komunikasi dan pencairan honor (Payroll).</p>
                    </div>

                    <div class="space-y-6">
                        <!-- WhatsApp -->
                        <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100">
                            <label class="block text-sm font-bold text-slate-800 mb-2">No. WhatsApp Aktif</label>
                            <div class="flex rounded-xl shadow-sm border border-slate-200 overflow-hidden bg-white focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                                <span class="inline-flex items-center px-4 bg-slate-50 text-slate-500 font-bold border-r border-slate-200">+62</span>
                                <input type="tel" name="whatsapp_number" id="input-wa" class="flex-1 block w-full border-0 p-3.5 text-slate-900 font-medium focus:ring-0 outline-none" placeholder="81234567890" value="{{ old('whatsapp_number', $partner->whatsapp_number ?? '') }}" required>
                            </div>
                        </div>
                        
                        <!-- Bank Info -->
                        <div class="space-y-4">
                            <h3 class="font-bold text-slate-800 text-lg border-b border-slate-100 pb-2">Informasi Rekening Bank</h3>
                            
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
                    </div>
                </div>

                <!-- SCREEN 6: Persetujuan (Consent) -->
                <div class="step-screen w-full pb-10 hidden" id="screen-6">
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
        <div class="px-6 py-6 bg-white z-20 shrink-0 border-t border-slate-50">
            <button type="button" id="btn-next" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 rounded-2xl shadow-[0_8px_16px_rgba(37,99,235,0.2)] transition-all active:scale-[0.98]" onclick="navigate(1)">
                Lanjut
            </button>
            <button type="submit" form="onboarding-form" id="btn-submit" class="hidden w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 rounded-2xl shadow-[0_8px_16px_rgba(37,99,235,0.2)] transition-all disabled:opacity-50 disabled:bg-slate-400 disabled:shadow-none" disabled>
                Selesai & Akses Dashboard
            </button>
        </div>

    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 6;
        let isAnimating = false;

        function navigate(direction) {
            if (isAnimating) return;
            
            // Validate Screen 5 (Data) before going to 6
            if (currentStep === 5 && direction === 1) {
                const wa = document.getElementById('input-wa').value;
                const bank = document.getElementById('input-bank').value;
                const acc = document.getElementById('input-acc').value;
                const owner = document.getElementById('input-owner').value;
                
                if(!wa || !bank || !acc || !owner) {
                    const container = document.getElementById('screen-5');
                    container.classList.add('animate-[shake_0.5s_ease-in-out]');
                    setTimeout(() => container.classList.remove('animate-[shake_0.5s_ease-in-out]'), 500);
                    return;
                }
            }

            const newStep = currentStep + direction;
            if (newStep < 1 || newStep > totalSteps) return;

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
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
