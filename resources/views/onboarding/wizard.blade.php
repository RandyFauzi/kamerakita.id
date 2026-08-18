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
    </style>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-blue-50 text-slate-800 min-h-screen py-12 px-4 sm:px-6 lg:px-8 flex flex-col justify-center">

    <div class="max-w-4xl mx-auto w-full">
        <!-- Modern Logo Header -->
        <div class="mb-8 flex justify-center">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/Logo.webp') }}" alt="Kamerakita.ai" class="h-12 w-auto object-contain">
                <span class="text-2xl font-black tracking-tight text-blue-900">KameraKita AI</span>
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-100">
            
            <div class="bg-blue-600 text-white text-center py-8 px-4">
                <h3 class="text-3xl font-bold mb-2">Selamat Datang di KameraKita!</h3>
                <p class="text-blue-100 text-base">Mari lengkapi profil Anda sebelum mulai bekerja</p>
            </div>

            <div class="p-6 sm:p-10">
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-8 shadow-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Wizard Navigation -->
                <div class="relative mb-12 flex justify-between items-center w-full max-w-2xl mx-auto">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-gray-200 z-0 rounded-full"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-blue-600 z-0 transition-all duration-500 rounded-full" id="wizard-progress" style="width: 0%;"></div>
                    
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg text-white bg-blue-600 relative z-10 shadow-lg ring-4 ring-white" id="indicator-1">1</div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg text-gray-500 bg-white border-2 border-gray-300 relative z-10 shadow-sm ring-4 ring-white transition-colors duration-300" id="indicator-2">2</div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg text-gray-500 bg-white border-2 border-gray-300 relative z-10 shadow-sm ring-4 ring-white transition-colors duration-300" id="indicator-3">3</div>
                </div>

                <form action="{{ route('onboarding.save') }}" method="POST" id="onboarding-form">
                    @csrf

                    <!-- STEP 1: Panduan -->
                    <div class="wizard-step animate-fade-in" id="step-1">
                        <div class="text-center mb-6">
                            <h4 class="text-2xl font-bold text-gray-800">Langkah 1: Panduan Dasar Bekerja</h4>
                            <p class="text-gray-500 mt-2">Mohon baca dengan teliti panduan PDF berikut sebelum melanjutkan.</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl border border-gray-200 mb-8 overflow-hidden shadow-inner h-[600px] flex flex-col">
                            <div class="bg-gray-800 text-gray-200 py-2 px-4 flex justify-between items-center text-sm">
                                <span class="font-medium flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Manual book _Atlas.pdf
                                </span>
                                <a href="{{ asset('Assets/Manual book _Atlas.pdf') }}" target="_blank" class="hover:text-white transition-colors flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Buka di Tab Baru
                                </a>
                            </div>
                            <iframe src="{{ asset('Assets/Manual book _Atlas.pdf') }}#toolbar=0" class="w-full h-full border-0 bg-white" title="Manual Book Atlas"></iframe>
                        </div>
                        
                        <div class="flex justify-center">
                            <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-lg font-semibold py-3 px-12 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center transform hover:-translate-y-0.5" onclick="nextStep(2)">
                                Saya Sudah Membaca & Mengerti
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Data Pembayaran -->
                    <div class="wizard-step hidden animate-fade-in" id="step-2">
                        <div class="text-center mb-8">
                            <h4 class="text-2xl font-bold text-gray-800">Langkah 2: Data Pembayaran & Kontak</h4>
                            <p class="text-gray-500 mt-2">Pastikan data yang dimasukkan valid untuk keperluan Payroll.</p>
                        </div>
                        
                        <div class="max-w-2xl mx-auto space-y-6">
                            <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Nomor WhatsApp Aktif</label>
                                <div class="flex rounded-lg shadow-sm border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all">
                                    <span class="inline-flex items-center px-4 bg-gray-100 text-gray-600 font-bold text-base border-r border-gray-300">
                                        +62
                                    </span>
                                    <input type="text" name="whatsapp_number" class="flex-1 block w-full border-0 p-3 text-base outline-none ring-0" placeholder="81234567890" value="{{ old('whatsapp_number', $partner->whatsapp_number ?? '') }}" required>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Tim kami akan menghubungi Anda via WhatsApp ini.
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 space-y-5">
                                <h6 class="font-bold text-gray-800 text-lg flex items-center gap-2 border-b border-gray-200 pb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Informasi Rekening Bank
                                </h6>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Bank</label>
                                    <select name="bank_name" class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-lg shadow-sm bg-white" required>
                                        <option value="">-- Pilih Bank Anda --</option>
                                        @php
                                            $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN', 'Mega'];
                                            $selectedBank = old('bank_name', $partner->bank_name ?? '');
                                        @endphp
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank }}" {{ $selectedBank == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Rekening</label>
                                    <input type="text" name="bank_account_number" class="focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-base border-gray-300 rounded-lg p-3" placeholder="Contoh: 1234567890" value="{{ old('bank_account_number', $partner->bank_account_number ?? '') }}" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Pemilik Rekening</label>
                                    <input type="text" name="bank_account_owner" class="focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-base border-gray-300 rounded-lg p-3 uppercase" placeholder="Sesuai nama di buku tabungan" value="{{ old('bank_account_owner', $partner->bank_account_owner ?? '') }}" required>
                                    <p class="mt-2 text-xs text-red-500 font-medium">Penting: Nama harus sama persis dengan rekening agar payroll tidak gagal.</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                                <button type="button" class="text-gray-600 hover:text-gray-900 font-semibold py-2 px-4 transition-colors flex items-center" onclick="nextStep(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Kembali
                                </button>
                                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-xl transition-colors shadow-md hover:shadow-lg flex items-center transform hover:-translate-y-0.5" onclick="nextStep(3)">
                                    Selanjutnya
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
                            <h4 class="text-2xl font-bold text-gray-800">Langkah 3: Persetujuan Akhir</h4>
                            <p class="text-gray-500 mt-2">Langkah terakhir sebelum Anda mulai bekerja.</p>
                        </div>
                        
                        <div class="max-w-3xl mx-auto">
                            <div class="p-6 bg-gray-50 rounded-xl border border-gray-200 mb-8 shadow-sm">
                                <h6 class="font-bold text-lg text-gray-900 mb-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Persetujuan Kepatuhan & Kerahasiaan Data (NDA)
                                </h6>
                                <p class="mb-4 text-gray-700 leading-relaxed">Dengan menekan tombol setuju di bawah, Anda secara sadar menyatakan bahwa:</p>
                                
                                <ul class="space-y-4 mb-6">
                                    <li class="flex items-start">
                                        <svg class="h-6 w-6 text-green-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-gray-700 leading-relaxed"><strong class="text-gray-900">Kerahasiaan Data Mutlak:</strong> Anda wajib menjaga kerahasiaan seluruh video proyek, instruksi, dan data internal KameraKita. Dilarang keras menyebarkan atau memperjualbelikan data.</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-6 w-6 text-green-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-gray-700 leading-relaxed"><strong class="text-gray-900">Izin Data Diri:</strong> KameraKita berhak menyimpan nomor rekening dan kontak Anda secara aman untuk kebutuhan internal dan kelancaran pembayaran honor Anda.</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="h-6 w-6 text-green-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-gray-700 leading-relaxed"><strong class="text-gray-900">Kepatuhan Kualitas:</strong> Anda bersedia mengikuti standar (SOP) dari tim QC. Pelanggaran berat dapat berakibat pemutusan kemitraan.</span>
                                    </li>
                                </ul>
                                
                                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                                    <p class="font-bold text-red-700 text-sm">PENTING: Pelanggaran NDA (kebocoran data) akan ditindaklanjuti secara hukum yang berlaku di Indonesia.</p>
                                </div>
                            </div>

                            <div class="mb-8 bg-blue-50 p-5 rounded-xl border-2 border-blue-200 cursor-pointer hover:bg-blue-100 transition-colors shadow-sm" onclick="document.getElementById('tos_accepted').click()">
                                <label class="flex items-start cursor-pointer">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <input type="checkbox" name="tos_accepted" id="tos_accepted" value="1" required class="h-6 w-6 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-all cursor-pointer">
                                    </div>
                                    <div class="ml-4">
                                        <span class="block font-bold text-gray-900 text-lg">Saya Menyetujui Persetujuan Ini</span>
                                        <span class="block text-sm text-gray-600 mt-1">Saya telah membaca panduan PDF di langkah 1 dan menyetujui seluruh aturan di atas secara sadar tanpa paksaan.</span>
                                    </div>
                                </label>
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-4">
                                <button type="button" class="text-gray-600 hover:text-gray-900 font-semibold py-3 px-6 transition-colors flex items-center w-full sm:w-auto justify-center" onclick="nextStep(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Kembali ke Data Rekening
                                </button>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-lg font-bold py-4 px-8 rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center justify-center w-full sm:w-auto transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none" id="btn-submit" disabled>
                                    Selesai & Akses Dashboard
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>

                </form>
            </div>
        </div>
        
        <div class="text-center mt-6 text-gray-500 text-sm font-medium pb-8">
            &copy; {{ date('Y') }} KameraKita AI. All rights reserved.
        </div>
    </div>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        function nextStep(step) {
            // Validate step 2 inputs before proceeding to 3
            if (step === 3) {
                const wa = document.querySelector('input[name="whatsapp_number"]').value;
                const bank = document.querySelector('select[name="bank_name"]').value;
                const acc = document.querySelector('input[name="bank_account_number"]').value;
                const owner = document.querySelector('input[name="bank_account_owner"]').value;
                
                if(!wa || !bank || !acc || !owner) {
                    alert('Mohon lengkapi semua form Data Pembayaran sebelum melanjutkan.');
                    return;
                }
            }

            // Hide all steps
            document.querySelectorAll('.wizard-step').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Show target step
            document.getElementById('step-' + step).classList.remove('hidden');
            
            // Update progress bar
            let progress = (step - 1) * 50;
            document.getElementById('wizard-progress').style.width = progress + '%';
            
            // Update indicators
            for(let i=1; i<=3; i++) {
                let ind = document.getElementById('indicator-' + i);
                if(i <= step) {
                    ind.classList.remove('bg-white', 'text-gray-500', 'border-2', 'border-gray-300');
                    ind.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                } else {
                    ind.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                    ind.classList.add('bg-white', 'text-gray-500', 'border-2', 'border-gray-300');
                }
            }
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Enable/Disable submit button based on checkbox
        const checkbox = document.getElementById('tos_accepted');
        const btn = document.getElementById('btn-submit');
        
        checkbox.addEventListener('change', function() {
            btn.disabled = !this.checked;
            if(this.checked) {
                btn.classList.add('ring-4', 'ring-green-200');
            } else {
                btn.classList.remove('ring-4', 'ring-green-200');
            }
        });
        
        // Prevent accidental double clicks
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
