<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Kirim Laporan Kerja Video') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 space-y-6">
                
                <div class="border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Form Laporan Harian Worker</h3>
                    <p class="text-xs text-gray-400">Harap lampirkan bukti otentik pengiriman email dan tangkapan layar kualitas aplikasi.</p>
                </div>

                <form action="{{ route('video-submissions.submit-report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Submission Date -->
                        <div>
                            <label for="submission_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengambilan Data <span class="text-red-500">*</span></label>
                            <input type="date" name="submission_date" id="submission_date" value="{{ old('submission_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('submission_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Submitted Duration -->
                        <div>
                            <label for="submitted_duration_minutes" class="block text-sm font-semibold text-gray-700 mb-1">Total Durasi Kerja (Menit) <span class="text-red-500">*</span></label>
                            <input type="number" name="submitted_duration_minutes" id="submitted_duration_minutes" value="{{ old('submitted_duration_minutes') }}" min="1" placeholder="Cth: 120" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('submitted_duration_minutes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Evidence 1: Email Screenshot -->
                    <div class="bg-slate-50 border border-gray-100 rounded-2xl p-5 space-y-3">
                        <div class="flex justify-between items-center border-b border-gray-200/50 pb-2">
                            <span class="text-sm font-bold text-slate-800">1. Screenshot total durasi di aplikasi <span class="text-red-500">*</span></span>
                            <span class="text-xs text-gray-400">Format: JPG, PNG (Maks: 2MB)</span>
                        </div>
                        <input type="file" name="evidence_email_image_path" id="evidence_email_image_path" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_email_image_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">Unggah tangkapan layar dari email notifikasi otomatis yang Anda terima dari Minutes App.</p>
                    </div>

                    <!-- Evidence 2: App Quality Screenshot -->
                    <div class="bg-slate-50 border border-gray-100 rounded-2xl p-5 space-y-3">
                        <div class="flex justify-between items-center border-b border-gray-200/50 pb-2">
                            <span class="text-sm font-bold text-slate-800">2. Screenshot Bagian Kualitas di Aplikasi <span class="text-red-500">*</span></span>
                            <span class="text-xs text-gray-400">Format: JPG, PNG (Maks: 2MB)</span>
                        </div>
                        <input type="file" name="evidence_app_quality_image_path" id="evidence_app_quality_image_path" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_app_quality_image_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">Unggah tangkapan layar kualitas rekaman/dashboard statistik di dalam Minutes Data Apps.</p>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition duration-150">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md shadow-indigo-100">
                            Kirim Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
