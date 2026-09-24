import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find the end of the form
    old_end = r'''</form>\s*</div>\s*</div>\s*</div>\s*</div>\s*</x-app-layout>'''
    
    # We will replace it with the form closing, then the new script, then the layout closing
    new_script = '''</form>
            </div>
        </div>
    </div>
    
    <!-- Bulletproof Client-Side Compression -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('submit-report-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            async function compressFile(file, maxWidth, maxHeight, quality) {
                if (!file.type.startsWith('image/')) return file; // Only compress images
                
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
                                    const newFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(newFile);
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
                        if (value instanceof File && value.size > 0 && value.type.startsWith('image/')) {
                            // Compress images > 500KB
                            if (value.size > 500 * 1024) {
                                const compressedFile = await compressFile(value, 1920, 1920, 0.8);
                                finalFormData.append(key, compressedFile, value.name);
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
                            let redirectUrl = "''' + "{{ route('dashboard') }}" + '''";
                            try {
                                const data = JSON.parse(xhr.responseText);
                                if (data.redirect) redirectUrl = data.redirect;
                            } catch(e) {}
                            window.location.href = redirectUrl;
                        } else if (xhr.status === 422) {
                            // Validation error
                            document.querySelectorAll('.js-error-msg').forEach(el => el.remove());
                            try {
                                const data = JSON.parse(xhr.responseText);
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
                                alert('Gagal memvalidasi form.');
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
</x-app-layout>'''

    # Remove the old script I injected earlier
    content = re.sub(r'<script>[\s\S]*?BUG TERDETEKSI[\s\S]*?</script>', '', content)
    
    # Replace end of form
    content = re.sub(old_end, new_script, content)
    
    # Remove version badge I added earlier to avoid confusion
    content = content.replace('<span class="text-xs text-gray-400 mr-4" id="app-version">v3.0.1 (No-JS Native)</span>\n                              ', '')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Injected robust JS compression!")
