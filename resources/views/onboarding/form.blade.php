<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('onboarding.title') }}</title>
    <link rel="icon" href="{{ asset('images/Logo.webp') }}" type="image/webp">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter & Fredoka -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Alpine.js CDN for interactive multi-step transitions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#e6f5fd',
                            100: '#b8e3fa',
                            500: '#0285c6',
                            600: '#026fa7',
                            700: '#025885',
                        },
                        neutralText: {
                            dark: '#0E161A',
                            muted: '#767B7D',
                            placeholder: '#A4A7A8',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        logo: ['Fredoka', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body class="font-sans antialiased text-neutralText-dark min-h-screen flex flex-col justify-between py-8 px-4 relative">

    <div class="absolute top-4 right-4 z-50">
        <x-language-switcher />
    </div>

    <!-- App Container -->
    <div x-data="{
        step: 1,
        formData: {
            full_name: '',
            whatsapp_number: '',
            device_type: '',
            has_headstrap: ''
        },
        isSubmitting: false,
        errorMessage: '',
        nextStep() {
            if (this.step < 6) this.step++;
        },
        prevStep() {
            if (this.step > 1) this.step--;
        },
        async submitForm() {
            this.isSubmitting = true;
            this.errorMessage = '';
            
            let payload = {
                full_name: this.formData.full_name,
                whatsapp_number: this.formData.whatsapp_number,
                device_type: this.formData.device_type
            };
            if (this.formData.has_headstrap === 'Ya, sudah punya') {
                payload.has_headstrap = '1';
            }
            
            try {
                const response = await fetch('{{ route('onboarding.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                if (response.ok) {
                    this.step = 6;
                } else {
                    const data = await response.json();
                    this.errorMessage = data.message || 'Terjadi kesalahan. Silakan coba lagi.';
                }
            } catch (error) {
                this.errorMessage = 'Gagal mengirim data. Periksa koneksi internet Anda.';
            } finally {
                this.isSubmitting = false;
            }
        }
    }" class="w-full max-w-[800px] mx-auto flex flex-col items-center gap-8">

        <!-- Top Header Logo -->
        <header class="flex items-center justify-center">
            <img src="{{ asset('images/onboarding/kamerakita.png') }}" alt="KameraKita AI Logo" class="h-10 w-auto object-contain">
        </header>

        <!-- Form Card Container -->
        <main :class="{'bg-white shadow-[0px_4px_24px_rgba(0,0,0,0.08)]': step === 1 || step === 6}" class="rounded-[32px] w-full overflow-hidden transition-all duration-300 relative">
            
            <form action="{{ route('onboarding.submit') }}" method="POST" @submit.prevent="submitForm">
                @csrf

                <!-- ================= STEP 1: HERO / LANDING ================= -->
                <div x-show="step === 1" x-transition.opacity.duration.300ms class="flex flex-col">
                    <!-- Banner Graphic Area with Seamless White Gradient Fade -->
                    <div class="h-[340px] sm:h-[420px] relative overflow-hidden bg-white">
                        <img src="{{ asset('images/onboarding/page_1_thumbnail.jpg') }}" alt="Hero Banner" class="w-full h-full object-cover object-center">
                        
                        <!-- Smooth White Gradient Fade overlay (exact blend as Figma) -->
                        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white via-white/85 to-transparent pointer-events-none z-10"></div>
                    </div>
                    
                    <!-- Content & CTAs -->
                    <div class="p-8 sm:p-10 flex flex-col gap-8 text-center -mt-6 relative z-20">
                        <div class="space-y-3">
                            <h1 class="text-2xl sm:text-[32px] font-semibold text-neutralText-dark leading-tight">Gabung jadi Kontributor Sekarang!</h1>
                            <p class="text-neutralText-muted text-base sm:text-[20px] leading-snug max-w-[600px] mx-auto">{{ __('onboarding.welcome_desc') }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="button" @click="nextStep()" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-medium py-4 px-6 rounded-[18px] flex items-center justify-center gap-3 transition-all shadow-md text-[18px]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>{{ __('onboarding.btn_form') }}</span>
                            </button>
                            <a href="https://wa.me/6285389933194" target="_blank" class="flex-1 border-2 border-brand-500 text-brand-500 hover:bg-brand-50 font-medium py-4 px-6 rounded-[18px] flex items-center justify-center gap-3 transition-all text-[18px]">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.099 4.019 4.142-1.087z"/></svg>
                                <span>{{ __('onboarding.btn_wa') }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ================= STEP 2: NAME INPUT ================= -->
                <div x-show="step === 2" x-transition.opacity.duration.300ms class="p-8 sm:p-12 flex flex-col gap-8 max-w-[630px] mx-auto min-h-[446px] justify-between">
                    <div class="space-y-8 flex flex-col items-center">
                        <img src="{{ asset('images/onboarding/page1.png') }}" alt="Ilustrasi Nama" class="w-32 h-32 object-contain mx-auto">
                        <h2 class="text-2xl sm:text-[32px] font-semibold text-center text-neutralText-dark leading-tight">Kenalan dulu yuk, siapa nama kamu?</h2>
                        
                        <div class="w-full bg-white border border-[#a7a7a7] rounded-[12px] px-5 py-4 flex items-center shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20">
                            <input type="text" name="full_name" x-model="formData.full_name" placeholder="Nama Kamu" class="w-full text-lg sm:text-[20px] text-neutralText-dark placeholder:text-neutralText-placeholder outline-none bg-transparent font-medium">
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center w-full pt-4">
                        <button type="button" @click="prevStep()" class="flex items-center gap-2 px-6 py-4 rounded-[18px] text-brand-500 hover:bg-brand-50 font-medium text-[18px] transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>{{ __('onboarding.btn_back') }}</span>
                        </button>
                        <button type="button" @click="if(formData.full_name) nextStep()" :disabled="!formData.full_name" class="flex items-center justify-center gap-2 px-6 py-4 rounded-[56px] bg-brand-500 text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-[18px] min-w-[150px] transition-all shadow-md">
                            <span>{{ __('onboarding.btn_next') }}</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- ================= STEP 3: WHATSAPP INPUT ================= -->
                <div x-show="step === 3" x-transition.opacity.duration.300ms class="p-8 sm:p-12 flex flex-col gap-8 max-w-[630px] mx-auto min-h-[446px] justify-between">
                    <div class="space-y-8 flex flex-col items-center">
                        <img src="{{ asset('images/onboarding/page2.png') }}" alt="Ilustrasi WhatsApp" class="w-32 h-32 object-contain mx-auto">
                        <h2 class="text-2xl sm:text-[32px] font-semibold text-center text-neutralText-dark leading-tight">Berapa nomor WhatsApp kamu?</h2>
                        
                        <div class="w-full bg-white border border-[#a7a7a7] rounded-[12px] px-5 py-4 flex items-center shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20">
                            <input type="tel" name="whatsapp_number" x-model="formData.whatsapp_number" placeholder="Nomor WhatsApp" class="w-full text-lg sm:text-[20px] text-neutralText-dark placeholder:text-neutralText-placeholder outline-none bg-transparent font-medium">
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center w-full pt-4">
                        <button type="button" @click="prevStep()" class="flex items-center gap-2 px-6 py-4 rounded-[18px] text-brand-500 hover:bg-brand-50 font-medium text-[18px] transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>{{ __('onboarding.btn_back') }}</span>
                        </button>
                        <button type="button" @click="if(formData.whatsapp_number) nextStep()" :disabled="!formData.whatsapp_number" class="flex items-center justify-center gap-2 px-6 py-4 rounded-[56px] bg-brand-500 text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-[18px] min-w-[150px] transition-all shadow-md">
                            <span>{{ __('onboarding.btn_next') }}</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- ================= STEP 4: IPHONE MODEL SELECT ================= -->
                <div x-show="step === 4" x-transition.opacity.duration.300ms class="p-8 sm:p-12 flex flex-col gap-8 max-w-[630px] mx-auto min-h-[446px] justify-between">
                    <div class="space-y-8 flex flex-col items-center">
                        <img src="{{ asset('images/onboarding/page3.png') }}" alt="Ilustrasi iPhone" class="w-32 h-32 object-contain mx-auto">
                        <h2 class="text-2xl sm:text-[32px] font-semibold text-center text-neutralText-dark leading-tight">Pakai seri iPhone apa sekarang?</h2>
                        
                        <div class="w-full bg-white border border-[#a7a7a7] rounded-[12px] px-5 py-4 flex items-center justify-between shadow-sm relative cursor-pointer">
                            <select name="device_type" x-model="formData.device_type" class="w-full text-lg sm:text-[20px] text-neutralText-dark placeholder:text-neutralText-placeholder outline-none bg-transparent font-medium appearance-none cursor-pointer pr-8">
                                <option value="" disabled selected>Pilih tipe iphone</option>
                                <option value="iPhone 15 Pro / Pro Max">iPhone 15 Pro / Pro Max</option>
                                <option value="iPhone 15 / 15 Plus">iPhone 15 / 15 Plus</option>
                                <option value="iPhone 14 Pro / Pro Max">iPhone 14 Pro / Pro Max</option>
                                <option value="iPhone 14 / 14 Plus">iPhone 14 / 14 Plus</option>
                                <option value="iPhone 13 Series">iPhone 13 Series</option>
                                <option value="iPhone 12 Series">iPhone 12 Series</option>
                                <option value="Di bawah iPhone 12">Di bawah iPhone 12</option>
                            </select>
                            <div class="absolute right-5 pointer-events-none text-neutralText-muted">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center w-full pt-4">
                        <button type="button" @click="prevStep()" class="flex items-center gap-2 px-6 py-4 rounded-[18px] text-brand-500 hover:bg-brand-50 font-medium text-[18px] transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>{{ __('onboarding.btn_back') }}</span>
                        </button>
                        <button type="button" @click="if(formData.device_type) nextStep()" :disabled="!formData.device_type" class="flex items-center justify-center gap-2 px-6 py-4 rounded-[56px] bg-brand-500 text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-[18px] min-w-[150px] transition-all shadow-md">
                            <span>{{ __('onboarding.btn_next') }}</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- ================= STEP 5: HEADSTRAP SELECTION ================= -->
                <div x-show="step === 5" x-transition.opacity.duration.300ms class="p-8 sm:p-12 flex flex-col gap-8 max-w-[630px] mx-auto min-h-[446px] justify-between">
                    <div class="space-y-8 flex flex-col items-center">
                        <img src="{{ asset('images/onboarding/page4.png') }}" alt="Ilustrasi Headstrap" class="w-32 h-32 object-contain mx-auto">
                        <h2 class="text-2xl sm:text-[32px] font-semibold text-center text-neutralText-dark leading-tight">Udah punya Headstrap?</h2>
                        
                        <div class="flex flex-col sm:flex-row gap-4 w-full">
                            <button type="button" @click="formData.has_headstrap = 'Ya, sudah punya'" 
                                    :class="formData.has_headstrap === 'Ya, sudah punya' ? 'bg-[#0285c6]/10 border-brand-500 text-brand-500' : 'bg-white border-[#a7a7a7] text-neutralText-muted hover:border-brand-500'"
                                    class="flex-1 border py-4 px-6 rounded-[41px] flex items-center justify-between transition-all font-medium text-[20px]">
                                <span>{{ __('onboarding.step_headstrap_yes') }}</span>
                                <div class="w-6 h-6 rounded-full border-2 border-brand-500 flex items-center justify-center" :class="formData.has_headstrap === 'Ya, sudah punya' ? 'bg-brand-500' : ''">
                                    <svg x-show="formData.has_headstrap === 'Ya, sudah punya'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>
                            <button type="button" @click="formData.has_headstrap = 'Tidak, belum Punya'" 
                                    :class="formData.has_headstrap === 'Tidak, belum Punya' ? 'bg-[#0285c6]/10 border-brand-500 text-brand-500' : 'bg-white border-[#a7a7a7] text-neutralText-muted hover:border-brand-500'"
                                    class="flex-1 border py-4 px-6 rounded-[41px] flex items-center justify-between transition-all font-medium text-[20px]">
                                <span>{{ __('onboarding.step_headstrap_no') }}</span>
                                <div class="w-6 h-6 rounded-full border-2 border-brand-500 flex items-center justify-center" :class="formData.has_headstrap === 'Tidak, belum Punya' ? 'bg-brand-500' : ''">
                                    <svg x-show="formData.has_headstrap === 'Tidak, belum Punya'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center w-full pt-4">
                        <button type="button" @click="prevStep()" class="flex items-center gap-2 px-6 py-4 rounded-[18px] text-brand-500 hover:bg-brand-50 font-medium text-[18px] transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>{{ __('onboarding.btn_back') }}</span>
                        </button>
                        <button type="button" @click="if(formData.has_headstrap) submitForm()" :disabled="!formData.has_headstrap || isSubmitting" class="flex items-center justify-center gap-2 px-6 py-4 rounded-[56px] bg-brand-500 text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-[18px] min-w-[150px] transition-all shadow-md">
                            <span x-show="!isSubmitting">{{ __('onboarding.btn_next') }}</span>
                            <span x-show="isSubmitting">{{ __('onboarding.submitting') }}</span>
                            <svg x-show="!isSubmitting" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <div x-show="errorMessage" class="text-red-500 text-center text-sm font-medium pt-2" x-text="errorMessage"></div>
                </div>

                <!-- ================= STEP 6: SUCCESS / WA GROUP CTA ================= -->
                <div x-show="step === 6" x-transition.opacity.duration.300ms class="flex flex-col">
                    <!-- Banner Graphic Area with Seamless White Gradient Fade -->
                    <div class="h-[340px] sm:h-[420px] relative overflow-hidden bg-white">
                        <img src="{{ asset('images/onboarding/page_6_thumbnail.jpg') }}" alt="Success Cuan" class="w-full h-full object-cover object-center">
                        
                        <!-- Smooth White Gradient Fade overlay -->
                        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white via-white/85 to-transparent pointer-events-none z-10"></div>
                    </div>

                    <!-- Content & CTAs -->
                    <div class="p-8 sm:p-10 flex flex-col gap-8 text-center -mt-6 relative z-20">
                        <div class="space-y-3">
                            <h2 class="text-2xl sm:text-[32px] font-semibold text-neutralText-dark leading-tight">Selamat, selangkah lagi menuju cuan! 💸</h2>
                            <p class="text-neutralText-muted text-base sm:text-[20px] leading-snug max-w-[600px] mx-auto">Yuk gabung grup WhatsApp sekarang buat dapet arahan tugas (briefing) dan follow-up langsung dari tim kita!</p>
                        </div>

                        <div>
                            <a href="https://chat.whatsapp.com/EWzTpticIllFogSNYx0TTt" target="_blank" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-medium py-4 px-6 rounded-[18px] flex items-center justify-center gap-3 transition-all shadow-md text-[18px]">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.099 4.019 4.142-1.087z"/></svg>
                                <span>JOIN GRUP WA SEKARANG</span>
                            </a>
                        </div>
                    </div>
                </div>

            </form>
        </main>

        <!-- Footer -->
        <footer class="text-xs text-neutralText-muted text-center">
            &copy; {{ date('Y') }} KameraKita AI. All rights reserved.
        </footer>
    </div>

</body>
</html>
