<x-guest-layout>
    <div x-data="onboardingForm()" x-init="initForm" class="w-full">
        
        <!-- Progress Bar -->
        <div class="h-1.5 w-full bg-gray-100 rounded-full mb-8 overflow-hidden">
            <div class="h-1.5 bg-indigo-500 transition-all duration-500 ease-out" :style="'width: ' + ((step / 5) * 100) + '%'"></div>
        </div>

        <form method="POST" action="{{ route('onboarding.submit') }}" id="registrationForm" @submit.prevent="submitForm">
            @csrf
            
            <!-- Step 1: Name -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-cloak>
                <div class="flex flex-col justify-center pb-8">
                    <img src="{{ asset('images/onboarding/step1.jpg') }}" alt="Welcome" class="w-48 h-48 mx-auto object-cover rounded-2xl mb-8 shadow-sm">
                    <h3 class="text-2xl font-black text-gray-900 leading-tight mb-2 text-center">Haloo, selamat bergabung di KameraKita AI! 👋</h3>
                    <p class="text-gray-500 text-sm mb-8 text-center">Siapa nama kamu?</p>
                    
                    <div>
                        <input x-model="formData.full_name" type="text" name="full_name" required placeholder="Masukkan nama lengkap Anda" class="block w-full px-4 py-3.5 border-2 border-gray-200 rounded-2xl text-base font-semibold text-gray-800 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-colors text-center shadow-sm placeholder-gray-400">
                        <template x-if="errors.full_name">
                            <p class="text-red-500 text-xs mt-2 text-center font-medium" x-text="errors.full_name"></p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Step 2: WhatsApp -->
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-cloak>
                <div class="flex flex-col justify-center pb-8">
                    <img src="{{ asset('images/onboarding/step2.jpg') }}" alt="Chat" class="w-48 h-48 mx-auto object-cover rounded-2xl mb-8 shadow-sm">
                    <h3 class="text-2xl font-black text-gray-900 leading-tight mb-2 text-center">Bisa kita terhubung lebih dekat? 📱</h3>
                    <p class="text-gray-500 text-sm mb-8 text-center">Masukkan nomor WhatsApp aktif kamu untuk koordinasi.</p>
                    
                    <div>
                        <input x-model="formData.whatsapp_number" type="tel" name="whatsapp_number" required placeholder="Contoh: 081234567890" class="block w-full px-4 py-3.5 border-2 border-gray-200 rounded-2xl text-base font-semibold text-gray-800 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-colors text-center shadow-sm placeholder-gray-400">
                        <template x-if="errors.whatsapp_number">
                            <p class="text-red-500 text-xs mt-2 text-center font-medium" x-text="errors.whatsapp_number"></p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Step 3: Fastwork -->
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-cloak>
                <div class="flex flex-col justify-center pb-8">
                    <img src="{{ asset('images/onboarding/step3.jpg') }}" alt="Profile" class="w-48 h-48 mx-auto object-cover rounded-2xl mb-8 shadow-sm">
                    <h3 class="text-2xl font-black text-gray-900 leading-tight mb-2 text-center">Username Fastwork 💼</h3>
                    <p class="text-gray-500 text-sm mb-8 text-center">Jika ada, apa username Fastwork kamu? (Opsional)</p>
                    
                    <div>
                        <input x-model="formData.fastwork_username" type="text" name="fastwork_username" placeholder="Contoh: randy_fauzi" class="block w-full px-4 py-3.5 border-2 border-gray-200 rounded-2xl text-base font-semibold text-gray-800 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-colors text-center shadow-sm placeholder-gray-400">
                    </div>
                </div>
            </div>

            <!-- Step 4: Device -->
            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-cloak>
                <div class="flex flex-col justify-center pb-8">
                    <img src="{{ asset('images/onboarding/step4.jpg') }}" alt="Device" class="w-48 h-48 mx-auto object-cover rounded-2xl mb-8 shadow-sm">
                    <h3 class="text-2xl font-black text-gray-900 leading-tight mb-2 text-center">Tipe Perangkat Apple 🍎</h3>
                    <p class="text-gray-500 text-sm mb-8 text-center">Pilih seri iPhone yang kamu gunakan saat ini.</p>
                    
                    <div>
                        <select x-model="formData.device_type" name="device_type" required class="block w-full px-4 py-3.5 border-2 border-gray-200 rounded-2xl text-base font-semibold text-gray-800 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-colors shadow-sm appearance-none bg-white text-center cursor-pointer">
                            <option value="" disabled>Pilih Tipe iPhone...</option>
                            <option value="iPhone 12">iPhone 12</option>
                            <option value="iPhone 12 Pro / Max">iPhone 12 Pro / Max</option>
                            <option value="iPhone 13">iPhone 13</option>
                            <option value="iPhone 13 Pro / Max">iPhone 13 Pro / Max</option>
                            <option value="iPhone 14">iPhone 14</option>
                            <option value="iPhone 14 Pro / Max">iPhone 14 Pro / Max</option>
                            <option value="iPhone 15">iPhone 15</option>
                            <option value="iPhone 15 Pro / Max">iPhone 15 Pro / Max</option>
                            <option value="Di bawah iPhone 12">Di bawah iPhone 12</option>
                        </select>
                        <template x-if="errors.device_type">
                            <p class="text-red-500 text-xs mt-2 text-center font-medium" x-text="errors.device_type"></p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Step 5: Headstrap -->
            <div x-show="step === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-cloak>
                <div class="flex flex-col justify-center pb-8">
                    <img src="{{ asset('images/onboarding/step5.jpg') }}" alt="Headstrap" class="w-48 h-48 mx-auto object-cover rounded-2xl mb-8 shadow-sm">
                    <h3 class="text-2xl font-black text-gray-900 leading-tight mb-2 text-center">Persiapan Alat 📸</h3>
                    <p class="text-gray-500 text-sm mb-8 text-center">Apakah kamu sudah memiliki aksesoris Headstrap?</p>
                    
                    <label class="flex flex-col items-center gap-3 cursor-pointer p-6 border-2 border-gray-200 rounded-3xl hover:bg-indigo-50 hover:border-indigo-200 transition-all group" :class="{ 'bg-indigo-50 border-indigo-500': formData.has_headstrap }">
                        <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-colors" :class="formData.has_headstrap ? 'bg-indigo-500 border-indigo-500' : 'border-gray-300 bg-white group-hover:border-indigo-300'">
                            <svg x-show="formData.has_headstrap" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <input type="checkbox" x-model="formData.has_headstrap" name="has_headstrap" value="1" class="hidden">
                        <span class="text-base font-bold text-gray-800 text-center">Ya, saya punya Headstrap</span>
                    </label>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="pt-2">
                <div class="flex items-center gap-3">
                    <button type="button" x-show="step > 1" @click="prevStep" class="p-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    
                    <button type="button" x-show="step < 5" @click="nextStep" class="flex-1 py-4 bg-gray-900 hover:bg-black text-white font-bold text-sm uppercase tracking-widest rounded-2xl transition shadow-lg flex items-center justify-center gap-2">
                        <span>Lanjut</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <button type="submit" x-show="step === 5" class="flex-1 py-4 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm uppercase tracking-widest rounded-2xl transition shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                        <span>Daftar Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
                <!-- Disclaimer on last step -->
                <div x-show="step === 5" class="text-[10px] text-gray-400 text-center leading-relaxed mt-4">
                    🔒 Setelah dikirim, Anda otomatis dialihkan ke WhatsApp Grup.
                </div>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('onboardingForm', () => ({
                step: 1,
                formData: {
                    full_name: '',
                    whatsapp_number: '',
                    fastwork_username: '',
                    device_type: '',
                    has_headstrap: false
                },
                errors: {},
                initForm() {
                    // pre-fill from old input if any
                    @if(old('full_name')) this.formData.full_name = "{{ old('full_name') }}"; @endif
                    @if(old('whatsapp_number')) this.formData.whatsapp_number = "{{ old('whatsapp_number') }}"; @endif
                    @if(old('fastwork_username')) this.formData.fastwork_username = "{{ old('fastwork_username') }}"; @endif
                    @if(old('device_type')) this.formData.device_type = "{{ old('device_type') }}"; @endif
                    @if(old('has_headstrap')) this.formData.has_headstrap = true; @endif
                },
                validateStep() {
                    this.errors = {};
                    let valid = true;

                    if (this.step === 1 && !this.formData.full_name.trim()) {
                        this.errors.full_name = 'Nama lengkap wajib diisi.';
                        valid = false;
                    }
                    if (this.step === 2 && !this.formData.whatsapp_number.trim()) {
                        this.errors.whatsapp_number = 'Nomor WhatsApp wajib diisi.';
                        valid = false;
                    }
                    if (this.step === 4 && !this.formData.device_type) {
                        this.errors.device_type = 'Tipe perangkat wajib dipilih.';
                        valid = false;
                    }

                    return valid;
                },
                nextStep() {
                    if (this.validateStep()) {
                        if (this.step < 5) this.step++;
                    }
                },
                prevStep() {
                    if (this.step > 1) this.step--;
                },
                submitForm() {
                    if (this.validateStep()) {
                        document.getElementById('registrationForm').submit();
                    }
                }
            }))
        })
    </script>
</x-guest-layout>
