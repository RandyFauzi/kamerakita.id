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

            <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-xl shadow-gray-200/50 rounded-3xl overflow-hidden border border-gray-100" x-data="{ app: 'atlas', isSubmitting: false }" @submit="if(isSubmitting) { $event.preventDefault(); } else { isSubmitting = true; }">
                @csrf

                <div class="p-6 sm:p-8 space-y-6">
                    
                    <!-- App Selection -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Pilih Aplikasi</label>
                        <select name="project_name" x-model="app" required class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-3">
                            <option value="atlas">Atlas</option>
                            <option value="minutes_data">Minutes Data</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Tanggal</label>
                            <input type="date" name="submission_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-3">
                        </div>
                        
                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Total Durasi (Menit)</label>
                            <input type="number" name="submitted_duration_minutes" placeholder="Misal: 120" min="1" required class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-3">
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- File Upload 1 -->
                    <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100">
                        <label class="block text-sm font-bold text-gray-800 mb-2">1. Upload Bukti Durasi (Wajib)</label>
                        <input type="file" name="evidence_email_image_path" required class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>

                    <!-- File Upload 2 (Atlas) -->
                    <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100" x-show="app === 'atlas'">
                        <label class="block text-sm font-bold text-gray-800 mb-2">2. Upload Bukti Submit (Wajib)</label>
                        <p class="text-xs text-gray-500 mb-2">Bisa pilih lebih dari 1 file.</p>
                        <input type="file" name="evidence_submitted_image_paths[]" multiple :required="app === 'atlas'" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>

                    <!-- File Upload 2 (Minutes Data) -->
                    <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100" x-cloak x-show="app === 'minutes_data'">
                        <label class="block text-sm font-bold text-gray-800 mb-2">2. Upload Bukti Quality (Wajib)</label>
                        <input type="file" name="evidence_app_quality_image_path" :required="app === 'minutes_data'" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>

                </div>

                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end">
                    <button type="submit" :class="isSubmitting ? 'bg-gray-400 cursor-wait' : 'bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-600/30'" class="w-full md:w-auto text-white font-bold py-3 px-8 rounded-xl transition-colors">
                        <span x-text="isSubmitting ? 'Sedang Mengupload...' : 'Kirim Laporan Sekarang'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
