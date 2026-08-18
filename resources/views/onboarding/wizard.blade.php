<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Get Started') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-100">
                
                <div class="bg-blue-600 text-white text-center py-6 px-4">
                    <h3 class="text-2xl font-bold mb-1">Selamat Datang di KameraKita!</h3>
                    <p class="text-blue-100 text-sm">Mari lengkapi profil Anda sebelum mulai bekerja</p>
                </div>

                <div class="p-6 sm:p-10">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Wizard Navigation (Visual only) -->
                    <div class="relative mb-10 flex justify-between items-center w-full">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-200 z-0 rounded-full"></div>
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-600 z-0 transition-all duration-300 rounded-full" id="wizard-progress" style="width: 0%;"></div>
                        
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white bg-blue-600 relative z-10 shadow-md" id="indicator-1">1</div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-white border-2 border-gray-300 relative z-10 shadow-sm transition-colors duration-300" id="indicator-2">2</div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-white border-2 border-gray-300 relative z-10 shadow-sm transition-colors duration-300" id="indicator-3">3</div>
                    </div>

                    <form action="{{ route('onboarding.save') }}" method="POST" id="onboarding-form">
                        @csrf

                        <!-- STEP 1: Panduan -->
                        <div class="wizard-step" id="step-1">
                            <h4 class="text-xl font-bold text-gray-800 mb-4">Langkah 1: Panduan Dasar Bekerja</h4>
                            
                            <div class="p-5 bg-gray-50 rounded-lg border border-gray-200 mb-6 max-h-[300px] overflow-y-auto">
                                <h5 class="font-semibold text-gray-800 mb-2">Ketentuan Bekerja di KameraKita</h5>
                                <p class="text-sm text-gray-600 mb-3">Sebagai partner/pekerja di KameraKita, Anda diwajibkan untuk:</p>
                                <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600 mb-4">
                                    <li>Membaca dan mengikuti seluruh instruksi kerja dengan teliti.</li>
                                    <li>Menjaga kerahasiaan data yang diberikan oleh KameraKita.</li>
                                    <li>Memastikan kualitas kerja sesuai dengan standar yang ditetapkan.</li>
                                    <li>Menjaga komunikasi yang baik dengan tim Quality Control.</li>
                                </ul>
                                <p class="text-xs text-gray-500 italic">Catatan: Detail panduan lengkap dari PDF akan ditampilkan di sini.</p>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors shadow-sm flex items-center" onclick="nextStep(2)">
                                    Selanjutnya
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2: Data Pembayaran -->
                        <div class="wizard-step hidden" id="step-2">
                            <h4 class="text-xl font-bold text-gray-800 mb-4">Langkah 2: Data Pembayaran & Kontak</h4>
                            
                            <div class="mb-5">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor WhatsApp Aktif</label>
                                <div class="flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-4 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 font-semibold text-sm">
                                        +62
                                    </span>
                                    <input type="text" name="whatsapp_number" class="flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 p-2.5 border" placeholder="81234567890" value="{{ old('whatsapp_number', $partner->whatsapp_number ?? '') }}" required>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Gunakan nomor yang aktif untuk koordinasi dengan tim.</p>
                            </div>

                            <hr class="border-gray-200 my-6">
                            
                            <h6 class="font-bold text-gray-800 mb-4">Informasi Rekening Bank (Untuk Payroll)</h6>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Bank</label>
                                <select name="bank_name" class="mt-1 block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border shadow-sm" required>
                                    <option value="">-- Pilih Bank --</option>
                                    @php
                                        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN', 'Mega'];
                                        $selectedBank = old('bank_name', $partner->bank_name ?? '');
                                    @endphp
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank }}" {{ $selectedBank == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Rekening</label>
                                <input type="text" name="bank_account_number" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2.5 border" placeholder="Contoh: 1234567890" value="{{ old('bank_account_number', $partner->bank_account_number ?? '') }}" required>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pemilik Rekening</label>
                                <input type="text" name="bank_account_owner" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2.5 border" placeholder="Sesuai buku tabungan" value="{{ old('bank_account_owner', $partner->bank_account_owner ?? '') }}" required>
                            </div>

                            <div class="flex justify-between items-center">
                                <button type="button" class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg transition-colors flex items-center shadow-sm" onclick="nextStep(1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Kembali
                                </button>
                                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors shadow-sm flex items-center" onclick="nextStep(3)">
                                    Selanjutnya
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 3: Persetujuan (Consent) -->
                        <div class="wizard-step hidden" id="step-3">
                            <h4 class="text-xl font-bold text-gray-800 mb-4">Langkah 3: Syarat & Ketentuan (ToS)</h4>
                            
                            <div class="p-5 bg-gray-50 rounded-lg border border-gray-200 mb-6 text-sm max-h-[250px] overflow-y-auto">
                                <h6 class="font-bold text-gray-800 mb-2">Persetujuan Kepatuhan & Kerahasiaan Data (Consent Form)</h6>
                                <p class="mb-3 text-gray-600">Dengan mendaftar sebagai partner/pekerja di sistem KameraKita, saya menyatakan bahwa:</p>
                                <ol class="list-decimal pl-5 space-y-2 text-gray-600 mb-4">
                                    <li><strong class="text-gray-800">Kerahasiaan Data (NDA):</strong> Saya setuju untuk menjaga kerahasiaan seluruh data proyek, video, dan informasi internal KameraKita. Saya tidak akan menyebarkan, mengunduh secara ilegal, atau memperjualbelikan data tersebut kepada pihak manapun.</li>
                                    <li><strong class="text-gray-800">Izin Penggunaan Data Diri:</strong> Saya memberikan izin kepada KameraKita untuk menyimpan dan menggunakan data pribadi saya (Nomor WhatsApp, Rekening Bank, dll) secara eksklusif untuk keperluan internal seperti komunikasi kerja dan pembayaran (payroll).</li>
                                    <li><strong class="text-gray-800">Kepatuhan Aturan Kerja:</strong> Saya bersedia mengikuti semua Standar Operasional Prosedur (SOP) dan arahan kerja yang ditetapkan. Pelanggaran terhadap SOP dapat berakibat pada penalti atau pemutusan hubungan kemitraan.</li>
                                </ol>
                                <p class="font-bold text-red-600">Jika terbukti melakukan pelanggaran fatal terkait kebocoran data, KameraKita berhak memproses secara hukum yang berlaku.</p>
                            </div>

                            <div class="mb-6 bg-yellow-50 p-4 rounded-lg border border-yellow-200 flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <input type="checkbox" name="tos_accepted" id="tos_accepted" value="1" required class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="tos_accepted" class="font-bold text-gray-800 cursor-pointer">
                                        Saya telah membaca, memahami, dan menyetujui seluruh Syarat, Ketentuan, dan Aturan Kerahasiaan Data di atas.
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <button type="button" class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg transition-colors flex items-center shadow-sm" onclick="nextStep(2)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Kembali
                                </button>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors shadow-md flex items-center disabled:opacity-50 disabled:cursor-not-allowed" id="btn-submit" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Selesai & Mulai Bekerja
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function nextStep(step) {
            // Hide all steps
            document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('hidden'));
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
                    ind.classList.add('bg-blue-600', 'text-white');
                } else {
                    ind.classList.remove('bg-blue-600', 'text-white');
                    ind.classList.add('bg-white', 'text-gray-500', 'border-2', 'border-gray-300');
                }
            }
        }

        // Enable/Disable submit button based on checkbox
        document.getElementById('tos_accepted').addEventListener('change', function() {
            document.getElementById('btn-submit').disabled = !this.checked;
        });
    </script>
</x-app-layout>
