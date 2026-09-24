import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Update compressImage function
    old_compress = r'''async function compressImage\(file, maxWidth = 1920, quality = 0\.85\) \{[\s\S]*?\}\s*\}'''
    
    new_compress = '''async function compressImage(file, maxWidth = 1920, quality = 0.85) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    try {
                        let width = img.width;
                        let height = img.height;
                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        const mimeType = 'image/jpeg';
                        canvas.toBlob((blob) => {
                            if (!blob) {
                                resolve(file);
                                return;
                            }
                            try {
                                const newFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                                    type: mimeType,
                                    lastModified: Date.now()
                                });
                                resolve(newFile);
                            } catch (e) {
                                resolve(blob);
                            }
                        }, mimeType, quality);
                    } catch (err) {
                        resolve(file);
                    }
                };
                img.onerror = function() {
                    resolve(file);
                };
                img.src = event.target.result;
            };
            reader.onerror = function() {
                resolve(file);
            };
            reader.readAsDataURL(file);
        });
    }'''
    
    content = re.sub(old_compress, new_compress, content)

    # 2. Add client side size check before fetch
    old_fetch_start = r'''const formDataKeys = Array\.from\(formData\.keys\(\)\);\s*const response = await fetch'''
    
    new_fetch_start = '''// Client-side payload size validation
            let hasOversized = false;
            let isVideo = false;
            if (window.compressedFiles) {
                for (const [inputId, files] of Object.entries(window.compressedFiles)) {
                    const inputElement = document.getElementById(inputId);
                    if (inputElement && files.length > 0) {
                        const fieldName = inputElement.name;
                        // Skip fields we just deleted
                        if (projectName === 'atlas' && fieldName === 'evidence_app_quality_image_path') continue;
                        if (projectName === 'minutes_data' && (fieldName === 'evidence_submitted_image_paths[]' || fieldName === 'evidence_submitted_image_paths')) continue;
                        
                        for (const file of files) {
                            if (file.type.startsWith('video/')) isVideo = true;
                            if (file.size > 3.5 * 1024 * 1024) { // > 3.5MB per file
                                hasOversized = true;
                            }
                        }
                    }
                }
            }

            if (isVideo || hasOversized) {
                let errStr = isVideo ? 'Mohon HANYA unggah screenshot berupa Gambar (JPG/PNG), bukan file Video (layar rekam/MP4).' : 'Terdapat gambar yang gagal dikompresi dan ukurannya terlalu besar (> 3.5MB). Harap pilih gambar lain atau kurangi jumlah file.';
                alert(errStr);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                isSubmittingReport = false;
                return;
            }

            const response = await fetch'''

    content = re.sub(old_fetch_start, new_fetch_start, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched!")
