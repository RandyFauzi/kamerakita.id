<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
            Form Upload Laporan (FilePond)
        </h2>
        <p class="text-sm text-gray-500 mt-1">Sistem upload asinkron untuk mencegah error pada Safari iOS.</p>
        
        <!-- FilePond CSS -->
        <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
        <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
        <style>
            .filepond--root { font-family: inherit; }
            .filepond--panel-root { background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 0.75rem; }
            .filepond--drop-label { color: #475569; }
            .filepond--credits { display: none; }
        </style>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-100 border border-green-200 text-green-700 font-semibold flex items-center shadow-sm">
                    <svg class="w-6 h-6 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-100 border border-red-200 text-red-700 font-semibold flex items-center shadow-sm">
                    <svg class="w-6 h-6 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-100 border border-red-200 text-red-700 font-semibold shadow-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-xl shadow-gray-200/50 rounded-3xl overflow-hidden border border-gray-100" x-data="{ app: '{{ old('project_name', 'atlas') }}' }">
                <form action="{{ route('reports.store') }}" method="POST" id="main-form">
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
                            <input type="file" 
                                   class="filepond"
                                   name="evidence_email_image_path" 
                                   accept="image/*"
                                   required>
                        </div>

                        <!-- 5. Upload SS Tambahan (Atlas) -->
                        <div class="mb-6" x-show="app === 'atlas'">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">2. Screenshot Bukti Submit (Wajib) <span class="text-red-500">*</span></label>
                            <input type="file" 
                                   class="filepond"
                                   name="evidence_submitted_image_paths[]" 
                                   accept="image/*"
                                   multiple
                                   data-max-files="10">
                        </div>

                        <!-- 6. Upload SS Tambahan (Minutes Data) -->
                        <div class="mb-6" x-cloak x-show="app === 'minutes_data'">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">2. Screenshot Bukti Quality (Wajib) <span class="text-red-500">*</span></label>
                            <input type="file" 
                                   class="filepond"
                                   name="evidence_app_quality_image_path" 
                                   accept="image/*">
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end">
                        <button type="submit" id="submit-btn" class="w-full py-3.5 px-4 border border-transparent rounded-xl shadow-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 font-bold text-lg transition-all">
                            Kirim Laporan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FilePond JS -->
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Register FilePond plugins
            FilePond.registerPlugin(
                FilePondPluginImagePreview,
                FilePondPluginFileValidateType,
                FilePondPluginImageExifOrientation,
                FilePondPluginFileValidateSize
            );

            // Set global options for FilePond
            FilePond.setOptions({
                server: {
                    process: {
                        url: '/kirim-laporan/temp',
                        withCredentials: true,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    },
                    revert: {
                        url: '/kirim-laporan/temp',
                        withCredentials: true,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }
                },
                labelIdle: 'Tarik gambar Anda atau <span class="filepond--label-action">Telusuri</span>',
                labelFileProcessing: 'Mengunggah...',
                labelFileProcessingComplete: 'Selesai',
                labelFileProcessingAborted: 'Dibatalkan',
                labelFileProcessingError: 'Gagal',
                labelTapToCancel: 'ketuk untuk membatalkan',
                labelTapToRetry: 'ketuk untuk mengulang',
                labelTapToUndo: 'ketuk untuk hapus',
                maxFileSize: '10MB'
            });

            // Turn all file input elements into ponds
            const inputElements = document.querySelectorAll('input.filepond');
            const pondInstances = [];
            Array.from(inputElements).forEach(inputElement => {
                pondInstances.push(FilePond.create(inputElement));
            });
            
            // Prevent form submission if files are still uploading
            document.getElementById('main-form').addEventListener('submit', function(e) {
                let isProcessing = false;
                
                for (let instance of pondInstances) {
                    const files = instance.getFiles();
                    for (let file of files) {
                        if (file.status === 1 || file.status === 2 || file.status === 3) {
                            // 1 = INIT, 2 = PROCESSING, 3 = PROCESSING_QUEUED
                            isProcessing = true;
                        }
                    }
                }
                
                if (isProcessing) {
                    e.preventDefault();
                    alert('Mohon tunggu sampai semua gambar selesai diunggah (Status: Selesai).');
                }
            });
        });
    </script>
</x-app-layout>
