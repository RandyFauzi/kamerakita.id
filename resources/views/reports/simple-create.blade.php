<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
            Form Upload Laporan
        </h2>
        <p class="text-sm text-gray-500 mt-1">Upload pekerjaan Anda dengan mudah. Bebas jenis file gambar (Maksimal 10MB).</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-100 border border-red-200 text-red-700 font-medium text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-100 border border-red-200 text-red-700 font-medium text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-100 border border-green-200 text-green-800 font-bold text-base flex items-start gap-3">
                    <svg class="w-6 h-6 mt-0.5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        {{ session('success') }}
                        <div class="mt-2 text-sm font-normal text-green-700">Laporan Anda telah berhasil masuk ke sistem dan sedang mengantre untuk di-QC.</div>
                    </div>
                </div>
            @endif

            <!-- Wrapper Form Pelaporan dengan Alpine.js yang Disempurnakan -->
            <div class="bg-white shadow-xl shadow-gray-200/50 rounded-3xl overflow-hidden border border-gray-100" x-data="reportUploadForm()">
                <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" onsubmit="setTimeout(function(){ document.getElementById('loading-overlay').classList.remove('hidden'); }, 0);">
                    @csrf

                    <div class="p-6 sm:p-8 space-y-6">
                        
                        <!-- 1. App Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Aplikasi <span class="text-red-500">*</span></label>
                            <select name="project_name" x-model="app" required class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-3">
                                <option value="atlas">Atlas</option>
                                <option value="minutes_data">Minutes Data</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- 2. Date -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" name="submission_date" value="{{ old('submission_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-3">
                            </div>
                            
                            <!-- 3. Duration -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Total Durasi <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="number" name="submitted_duration_minutes" value="{{ old('submitted_duration_minutes') }}" required min="1" step="1"
                                        class="w-full pl-4 pr-14 py-3 font-bold text-gray-900 border border-gray-200 bg-gray-50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="Misal: 120">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <span class="text-gray-500 font-medium text-sm">Menit</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <!-- 4. Upload SS Utama (Bukti Durasi) -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">1. Screenshot Bukti Durasi (Wajib) <span class="text-red-500">*</span></label>
                            
                            <div class="relative flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer overflow-hidden"
                                 @click="$refs.mainImage.click()">
                                
                                <!-- Tampilan Default -->
                                <div class="space-y-1 text-center" x-show="!mainPreview">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium text-blue-600">Klik untuk upload</span> atau drag and drop
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 10MB</p>
                                </div>

                                <!-- Tampilan Preview -->
                                <div x-show="mainPreview" class="w-full flex flex-col items-center" style="display: none;">
                                    <img :src="mainPreview" class="max-h-48 rounded-lg object-contain mb-2 shadow-sm">
                                    <span class="text-xs text-blue-600 font-medium hover:underline">Ubah Gambar Durasi</span>
                                </div>

                                <input type="file" name="evidence_email_image_path" x-ref="mainImage" accept="image/*" class="hidden" @change="previewMain($event)" required>
                            </div>
                        </div>

                        <!-- 5. Upload SS Tambahan (Atlas) -->
                        <div class="mb-6" x-show="app === 'atlas'">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">2. Screenshot Bukti Submit (Wajib) <span class="text-red-500">*</span></label>
                            
                            <button type="button" @click="$refs.batchImages.click()" 
                                    class="w-full py-3 px-4 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-all flex justify-center items-center gap-2 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Pilih Beberapa Screenshot Bukti Submit
                            </button>
                            <input type="file" name="evidence_submitted_image_paths[]" x-ref="batchImages" accept="image/*" multiple class="hidden" :required="app === 'atlas'" @change="previewBatch($event)">

                            <!-- Grid Preview Batch Images -->
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-3 mt-4" x-show="batchPreviews.length > 0" style="display: none;">
                                <template x-for="(src, index) in batchPreviews" :key="index">
                                    <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100 shadow-sm">
                                        <img :src="src" class="w-full h-full object-cover">
                                        <div class="absolute top-1 left-1 bg-black/60 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                            <span x-text="index + 1"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 6. Upload SS Tambahan (Minutes Data) -->
                        <div class="mb-6" x-cloak x-show="app === 'minutes_data'">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">2. Screenshot Bukti Quality (Wajib) <span class="text-red-500">*</span></label>
                            
                            <div class="relative flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer overflow-hidden"
                                 @click="$refs.minutesQualityImage.click()">
                                
                                <div class="space-y-1 text-center" x-show="!minutesDataPreview">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium text-blue-600">Klik untuk upload</span> atau drag and drop
                                    </div>
                                </div>

                                <div x-show="minutesDataPreview" class="w-full flex flex-col items-center" style="display: none;">
                                    <img :src="minutesDataPreview" class="max-h-48 rounded-lg object-contain mb-2 shadow-sm">
                                    <span class="text-xs text-blue-600 font-medium hover:underline">Ubah Gambar Quality</span>
                                </div>

                                <input type="file" name="evidence_app_quality_image_path" x-ref="minutesQualityImage" accept="image/*" class="hidden" :required="app === 'minutes_data'" @change="previewMinutesData($event)">
                            </div>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end">
                        <button type="submit" class="w-full py-3.5 px-4 border border-transparent rounded-xl shadow-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 font-bold text-lg transition-all">
                            Kirim Laporan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Full Screen Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 z-[9999] bg-white/90 backdrop-blur-sm flex flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4 shadow-lg"></div>
        <h3 class="text-xl font-bold text-gray-800 mb-1 drop-shadow-sm">Sedang Mengirim Laporan</h3>
        <p class="text-sm text-gray-600 font-medium animate-pulse">Mohon tunggu, sedang memproses dan mengompres foto Anda...</p>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reportUploadForm', () => ({
                app: '{{ old('project_name', 'atlas') }}',
                mainPreview: null,
                batchPreviews: [],
                minutesDataPreview: null,

                previewMain(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.mainPreview = URL.createObjectURL(file);
                    }
                },

                previewBatch(event) {
                    this.batchPreviews = [];
                    const files = event.target.files;
                    const maxFiles = Math.min(files.length, 10);
                    
                    for (let i = 0; i < maxFiles; i++) {
                        const file = files[i];
                        this.batchPreviews.push(URL.createObjectURL(file));
                    }
                },
                
                previewMinutesData(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.minutesDataPreview = URL.createObjectURL(file);
                    }
                }
            }))
        });
    </script>
</x-app-layout>
