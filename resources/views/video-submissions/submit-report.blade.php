<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
            {{ __('Kirim Laporan Kerja Video') }}
        </h2>
    </x-slot>

    <div class="py-2 sm:py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5 sm:space-y-6">
                
                <div class="border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Form Laporan Harian Worker</h3>
                    <p class="text-xs text-gray-400">Harap lampirkan screenshot total durasi dan bagian kualitas dari aplikasi.</p>
                </div>

                <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 flex items-start gap-3.5">
                    <div class="bg-indigo-100 p-1.5 rounded-lg shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-700 leading-relaxed">
                            Pastikan email <strong class="text-indigo-700 font-bold">{{ Auth::user()->email }}</strong> <u>sama persis</u> dengan yang Anda gunakan di aplikasi rekaman. Laporan dengan email yang berbeda akan otomatis ditolak.
                        </p>
                    </div>
                </div>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                <form x-data="{ project_name: '{{ old('project_name', 'atlas') }}' }" action="{{ route('video-submissions.submit-report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- App Selection Dropdown -->
                    <div>
                        <label for="project_name" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Aplikasi <span class="text-red-500">*</span></label>
                        <select name="project_name" id="project_name" x-model="project_name" required class="block w-full min-h-11 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="atlas">Atlas</option>
                            <option value="minutes_data">Minutes Data</option>
                        </select>
                        @error('project_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <!-- Submission Date -->
                        <div>
                            <label for="submission_date" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengambilan Data <span class="text-red-500">*</span></label>
                            <input type="date" name="submission_date" id="submission_date" value="{{ old('submission_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="block w-full min-h-11 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('submission_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Submitted Duration -->
                        <div x-data="{
                            hours: '{{ old('submitted_duration_minutes') ? floor(old('submitted_duration_minutes') / 60) : '' }}',
                            minutes: '{{ old('submitted_duration_minutes') ? (old('submitted_duration_minutes') % 60) : '' }}'
                        }">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Total Durasi Kerja <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative">
                                    <input type="number" inputmode="numeric" x-model="hours" min="0" max="24" placeholder="0" class="block w-full min-h-11 pr-12 pl-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none">Jam</span>
                                </div>
                                <div class="relative">
                                    <input type="number" inputmode="numeric" x-model="minutes" min="0" max="59" placeholder="0" class="block w-full min-h-11 pr-14 pl-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none">Menit</span>
                                </div>
                            </div>
                            <input type="hidden" name="submitted_duration_minutes" :value="(parseInt(hours) || 0) * 60 + (parseInt(minutes) || 0)">
                            @error('submitted_duration_minutes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Evidence 1: Total duration (Changes label based on project_name) -->
                    <div class="bg-slate-50 border border-gray-100 rounded-xl sm:rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1.5 border-b border-gray-200/50 pb-2">
                            <span class="text-sm font-bold text-slate-800">
                                1. <span x-show="project_name === 'atlas'">Screenshot Skor Kualitas Rekaman</span>
                                   <span x-show="project_name === 'minutes_data'" style="display: none;">Screenshot Total Durasi di Aplikasi</span>
                                 <span class="text-red-500">*</span>
                            </span>
                            <span class="text-xs text-gray-400">Format: JPG, PNG, WEBP (Maks: 2MB)</span>
                        </div>
                        <input type="file" accept="image/jpeg,image/png,image/webp" name="evidence_email_image_path" id="evidence_email_image_path" required class="block w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_email_image_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1" x-show="project_name === 'atlas'">Unggah screenshot Skor Kualitas Rekaman</p>
                        <p class="text-xs text-gray-400 mt-1" x-show="project_name === 'minutes_data'" style="display: none;">Unggah tangkapan layar total durasi kerja di aplikasi.</p>
                    </div>

                    <!-- Evidence 2 for Minutes Data: Quality -->
                    <div class="bg-slate-50 border border-gray-100 rounded-xl sm:rounded-2xl p-4 sm:p-5 space-y-3" x-cloak x-show="project_name === 'minutes_data'">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1.5 border-b border-gray-200/50 pb-2">
                            <span class="text-sm font-bold text-slate-800">2. Screenshot Bagian Kualitas di Aplikasi <span class="text-red-500">*</span></span>
                            <span class="text-xs text-gray-400">Format: JPG, PNG, WEBP (Maks: 2MB)</span>
                        </div>
                        <input type="file" accept="image/jpeg,image/png,image/webp" name="evidence_app_quality_image_path" id="evidence_app_quality_image_path" :required="project_name === 'minutes_data'" class="block w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_app_quality_image_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">Unggah tangkapan layar khusus untuk bagian kualitas video dari aplikasi.</p>
                    </div>

                    <!-- Evidence 2 for Atlas: Submitted images screenshot -->
                    <div class="bg-slate-50 border border-gray-100 rounded-xl sm:rounded-2xl p-4 sm:p-5 space-y-3" x-show="project_name === 'atlas'">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1.5 border-b border-gray-200/50 pb-2">
                            <span class="text-sm font-bold text-slate-800">2. Screenshot Rekaman Terbaru <span class="text-red-500">*</span></span>
                            <span class="text-xs text-gray-400">Bisa pilih beberapa gambar. Format: JPG, PNG, WEBP (Maks: 2MB/file)</span>
                        </div>
                        <input type="file" accept="image/jpeg,image/png,image/webp" name="evidence_submitted_image_paths[]" id="evidence_submitted_image_paths" multiple :required="project_name === 'atlas'" class="block w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_submitted_image_paths') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('evidence_submitted_image_paths.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">Unggah screenshot Rekaman Terbaru</p>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition duration-150">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md shadow-indigo-100">
                            Kirim Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
