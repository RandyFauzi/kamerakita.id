<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
            {{ __("dashboard.submit_report_page.title") }}
        </h2>
    </x-slot>

    <div class="py-2 sm:py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5 sm:space-y-6">
                
                <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 flex items-center gap-4">
                    <div class="bg-indigo-100 p-2.5 rounded-lg shrink-0 mr-1">
                        <svg class="w-5 h-5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-700 leading-relaxed">
                            {!! __("dashboard.submit_report_page.alert_email_match", ["email" => Auth::user()->email]) !!}
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

                <form id="submit-report-form" x-data="{ project_name: '{{ old('project_name', 'atlas') }}' }" action="{{ route('video-submissions.submit-report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- App Selection Dropdown -->
                    <div>
                        <label for="project_name" class="block text-sm font-semibold text-gray-700 mb-1">{{ __("dashboard.submit_report_page.select_app") }} <span class="text-red-500">*</span></label>
                        <select name="project_name" id="project_name" x-model="project_name" required class="block w-full min-h-11 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="atlas">Atlas</option>
                            <option value="minutes_data">Minutes Data</option>
                        </select>
                        @error('project_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <!-- Submission Date -->
                        <div>
                            <label for="submission_date" class="block text-sm font-semibold text-gray-700 mb-1">{{ __("dashboard.submit_report_page.submission_date") }} <span class="text-red-500">*</span></label>
                            <input type="date" name="submission_date" id="submission_date" value="{{ old('submission_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="block w-full min-h-11 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('submission_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Submitted Duration -->
                        <div x-data="{
                            hours: '{{ old('submitted_duration_minutes') ? floor(old('submitted_duration_minutes') / 60) : '' }}',
                            minutes: '{{ old('submitted_duration_minutes') ? (old('submitted_duration_minutes') % 60) : '' }}'
                        }">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __("dashboard.submit_report_page.total_duration") }} <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative">
                                    <input type="number" inputmode="numeric" x-model="hours" min="0" max="24" placeholder="0" class="block w-full min-h-11 pr-12 pl-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none">{{ __("dashboard.submit_report_page.hours") }}</span>
                                </div>
                                <div class="relative">
                                    <input type="number" inputmode="numeric" x-model="minutes" min="0" max="59" placeholder="0" class="block w-full min-h-11 pr-14 pl-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 pointer-events-none">{{ __("dashboard.submit_report_page.minutes") }}</span>
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
                                1. <span x-show="project_name === 'atlas'">{{ __("dashboard.submit_report_page.evidence_1_atlas") }}</span>
                                   <span x-show="project_name === 'minutes_data'" style="display: none;">{{ __("dashboard.submit_report_page.evidence_1_minutes") }}</span>
                                 <span class="text-red-500">*</span>
                            </span>
                            <span class="text-xs text-gray-400">{{ __("dashboard.submit_report_page.format_info") }}</span>
                        </div>
                        <input type="file" accept="image/*" name="evidence_email_image_path" id="evidence_email_image_path" required  class="block w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_email_image_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1" x-show="project_name === 'atlas'">{{ __("dashboard.submit_report_page.evidence_1_desc_atlas") }}</p>
                        <p class="text-xs text-gray-400 mt-1" x-show="project_name === 'minutes_data'" style="display: none;">{{ __("dashboard.submit_report_page.evidence_1_desc_minutes") }}</p>
                    </div>

                    <!-- Evidence 2 for Minutes Data: Quality -->
                    <div class="bg-slate-50 border border-gray-100 rounded-xl sm:rounded-2xl p-4 sm:p-5 space-y-3" x-cloak x-show="project_name === 'minutes_data'">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1.5 border-b border-gray-200/50 pb-2">
                            <span class="text-sm font-bold text-slate-800">2. {{ __("dashboard.submit_report_page.evidence_2_minutes") }} <span class="text-red-500">*</span></span>
                            <span class="text-xs text-gray-400">{{ __("dashboard.submit_report_page.format_info") }}</span>
                        </div>
                        <input type="file" accept="image/*" name="evidence_app_quality_image_path" id="evidence_app_quality_image_path" :required="project_name === 'minutes_data'"  class="block w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_app_quality_image_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">{{ __("dashboard.submit_report_page.evidence_2_desc_minutes") }}</p>
                    </div>

                    <!-- Evidence 2 for Atlas: Submitted images screenshot -->
                    <div class="bg-slate-50 border border-gray-100 rounded-xl sm:rounded-2xl p-4 sm:p-5 space-y-3" x-show="project_name === 'atlas'">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1.5 border-b border-gray-200/50 pb-2">
                            <span class="text-sm font-bold text-slate-800">2. {{ __("dashboard.submit_report_page.evidence_2_atlas") }} <span class="text-red-500">*</span></span>
                            <span class="text-xs text-gray-400">{{ __("dashboard.submit_report_page.format_info_multiple") }}</span>
                        </div>
                        <input type="file" accept="image/*" name="evidence_submitted_image_paths[]" id="evidence_submitted_image_paths" multiple :required="project_name === 'atlas'"  class="block w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-55 file:text-indigo-700 hover:file:bg-indigo-100 file:transition-all">
                        @error('evidence_submitted_image_paths') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('evidence_submitted_image_paths.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">{{ __("dashboard.submit_report_page.evidence_2_desc_atlas") }}</p>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition duration-150">
                            {{ __("dashboard.submit_report_page.btn_cancel") }}
                        </a>
                        <button type="submit" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md shadow-indigo-100">
                            {{ __("dashboard.submit_report_page.btn_submit") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Bulletproof Client-Side Compression -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('submit-report-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            async function compressFile(file, maxWidth, maxHeight, quality) {
                if (file.type !== '' && !file.type.startsWith('image/') && file.type !== 'application/octet-stream') return file;
                
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.onload = function() {
                            // Max dimensions to prevent iOS memory crash
                            let width = img.width;
                            let height = img.height;
                            
                            if (width > maxWidth || height > maxHeight) {
                                const ratio = Math.min(maxWidth / width, maxHeight / height);
                                width = Math.round(width * ratio);
                                height = Math.round(height * ratio);
                            }
                            
                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;
                            
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);
                            
                            canvas.toBlob((blob) => {
                                if (blob) {
                                    // Create a new File from the blob
                                    resolve(blob);
                                } else {
                                    resolve(file); // Fallback to original
                                }
                            }, 'image/jpeg', quality);
                        };
                        img.onerror = () => resolve(file); // Fallback on error
                        img.src = e.target.result;
                    };
                    reader.onerror = () => resolve(file); // Fallback on error
                    reader.readAsDataURL(file);
                });
            }

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (submitBtn.disabled) return;
                
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...';
                
                try {
                    // Create fresh FormData to bypass iOS WebKit mutation bugs
                    const originalFormData = new FormData(form);
                    const finalFormData = new FormData();
                    
                    const projectName = originalFormData.get('project_name');
                    
                    // Iterate and selectively append original data
                    for (const [key, value] of originalFormData.entries()) {
                        // Skip hidden fields logic
                        if (projectName === 'atlas' && key === 'evidence_app_quality_image_path') continue;
                        if (projectName === 'minutes_data' && (key === 'evidence_submitted_image_paths[]' || key === 'evidence_submitted_image_paths')) continue;
                        
                        // If it's a file, we compress it before appending
                        if (value instanceof File && value.size > 0 && (value.type === '' || value.type.startsWith('image/') || value.type === 'application/octet-stream')) {
                            // Compress images > 500KB
                            if (value.size > 500 * 1024) {
                                const compressedFile = await compressFile(value, 1920, 1920, 0.8);
                                finalFormData.append(key, compressedFile, value.name);
                            finalFormData.append('_ajax', '1');
                            } else {
                                finalFormData.append(key, value, value.name);
                            }
                        } else {
                            finalFormData.append(key, value);
                        }
                    }
                    
                    // Submit via XMLHttpRequest (more reliable than fetch for large payloads on iOS)
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            // Success or successful redirect
                            let redirectUrl = "{{ route('dashboard') }}";
                            try {
                                let cleanText = xhr.responseText;
                                  const jsonStart = cleanText.indexOf('{');
                                  const jsonEnd = cleanText.lastIndexOf('}');
                                  if (jsonStart !== -1 && jsonEnd !== -1) {
                                      cleanText = cleanText.substring(jsonStart, jsonEnd + 1);
                                  }
                                  const data = JSON.parse(cleanText);
                                if (data.redirect) redirectUrl = data.redirect;
                            } catch(e) {}
                            window.location.href = redirectUrl;
                        } else if (xhr.status === 422) {
                            // Validation error
                            document.querySelectorAll('.js-error-msg').forEach(el => el.remove());
                            try {
                                let cleanText = xhr.responseText;
                                  const jsonStart = cleanText.indexOf('{');
                                  const jsonEnd = cleanText.lastIndexOf('}');
                                  if (jsonStart !== -1 && jsonEnd !== -1) {
                                      cleanText = cleanText.substring(jsonStart, jsonEnd + 1);
                                  }
                                  const data = JSON.parse(cleanText);
                                if (data.errors) {
                                    for (const [field, msgs] of Object.entries(data.errors)) {
                                        let inputName = field;
                                        if (field.startsWith('evidence_submitted_image_paths')) {
                                            inputName = 'evidence_submitted_image_paths[]';
                                        }
                                        const input = form.querySelector([name=""]);
                                        if (input) {
                                            const p = document.createElement('p');
                                            p.className = 'text-red-500 text-xs mt-1 js-error-msg';
                                            p.innerText = msgs[0];
                                            input.parentElement.appendChild(p);
                                        }
                                    }
                                }
                            } catch(e) {
                                alert('Koneksi terputus atau file terlalu besar. Harap muat ulang halaman. (Err: JSON parse)');
                            }
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        } else {
                            alert('Terjadi kesalahan sistem (Kode: ' + xhr.status + '). Coba lagi nanti.');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    };
                    
                    xhr.onerror = function() {
                        alert('Koneksi terputus. Pastikan internet stabil.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    };
                    
                    xhr.send(finalFormData);
                    
                } catch (err) {
                    alert('Error saat kompresi: ' + err.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    </script>
</x-app-layout>
