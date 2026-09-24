
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('submit-report-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Setup diagnostic log panel
            const diagPanel = document.createElement('div');
            diagPanel.className = 'mt-8 p-4 bg-gray-900 text-green-400 font-mono text-xs rounded-xl overflow-x-auto whitespace-pre-wrap';
            diagPanel.style.display = 'none';
            diagPanel.id = 'diagnostic-log';
            form.parentNode.appendChild(diagPanel);
            
            function logDiag(phase, data) {
                diagPanel.style.display = 'block';
                const str = `\n[${phase}] ` + (typeof data === 'object' ? JSON.stringify(data, null, 2) : data);
                console.log(str);
                diagPanel.innerText += str;
            }

            async function compressFile(file, maxWidth, maxHeight, quality) {
                if (!file || typeof file !== 'object' || !(file instanceof File || file instanceof Blob)) return file;
                if (file.type !== '' && !file.type.startsWith('image/') && file.type !== 'application/octet-stream') return file;
                
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.onload = function() {
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
                                    resolve(blob);
                                } else {
                                    resolve(file); 
                                }
                            }, 'image/jpeg', quality);
                        };
                        img.onerror = () => resolve(file);
                        img.src = e.target.result;
                    };
                    reader.onerror = () => resolve(file);
                    try {
                        reader.readAsDataURL(file);
                    } catch(e) {
                        resolve(file);
                    }
                });
            }

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (submitBtn.disabled) return;
                
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Memproses...';
                
                diagPanel.innerText = '=== DIAGNOSTIC START ===\n';
                logDiag('PHASE 1', 'Submit event JS triggered');

                try {
                    // PHASE 2 — LOG ACTUAL FORM VALUES
                    const projectInput = document.getElementById('project_name');
                    const dateInput = document.getElementById('submission_date');
                    const durationInput = document.querySelector('[name="submitted_duration_minutes"]');
                    const emailInput = document.getElementById('evidence_email_image_path');
                    const atlasInput = document.getElementById('evidence_submitted_image_paths');
                    const qualityInput = document.getElementById('evidence_app_quality_image_path');

                    logDiag('PHASE 2', {
                        project_name: projectInput ? projectInput.value : 'missing_dom',
                        submission_date: dateInput ? dateInput.value : 'missing_dom',
                        duration: durationInput ? durationInput.value : 'missing_dom',
                        email_file: emailInput && emailInput.files ? emailInput.files.length : 0,
                        atlas_files: atlasInput && atlasInput.files ? atlasInput.files.length : 0,
                        minutes_quality_file: qualityInput && qualityInput.files ? qualityInput.files.length : 0
                    });

                    // PHASE 4 — CHECK NATIVE HTML VALIDATION
                    logDiag('PHASE 4', {
                        formValid: form.checkValidity(),
                        projectValid: projectInput ? projectInput.checkValidity() : false,
                        dateValid: dateInput ? dateInput.checkValidity() : false,
                        durationValid: durationInput ? durationInput.checkValidity() : false,
                        emailEvidenceValid: emailInput ? emailInput.checkValidity() : false,
                        atlasEvidenceValid: atlasInput ? atlasInput.checkValidity() : false
                    });

                    if (!form.checkValidity()) {
                        const invalidEl = form.querySelector(':invalid');
                        if (invalidEl) {
                            logDiag('PHASE 4_FAIL', {
                                name: invalidEl.name,
                                type: invalidEl.type,
                                value: invalidEl.value,
                                required: invalidEl.required,
                                disabled: invalidEl.disabled,
                                hidden: invalidEl.hidden
                            });
                        }
                    }

                    // PHASE 5 — DYNAMIC REQUIRED
                    logDiag('PHASE 5', {
                        quality_required: qualityInput ? qualityInput.required : null,
                        quality_disabled: qualityInput ? qualityInput.disabled : null,
                        atlas_required: atlasInput ? atlasInput.required : null,
                        atlas_disabled: atlasInput ? atlasInput.disabled : null
                    });

                    // PHASE 7 — FILE INPUT
                    const extractFileInfo = (files) => {
                        if (!files) return [];
                        return Array.from(files).map(f => ({ name: f.name, size: f.size, type: f.type }));
                    };
                    logDiag('PHASE 7', {
                        emailFiles: extractFileInfo(emailInput ? emailInput.files : []),
                        atlasFiles: extractFileInfo(atlasInput ? atlasInput.files : []),
                        qualityFiles: extractFileInfo(qualityInput ? qualityInput.files : [])
                    });

                    // PHASE 6 — STOP DEPENDING ON ALPINE FOR CRITICAL FORM VALUES
                    const finalFormData = new FormData();
                    
                    const pName = projectInput ? projectInput.value : '';
                    finalFormData.append('project_name', pName);
                    finalFormData.append('submission_date', dateInput ? dateInput.value : '');
                    finalFormData.append('submitted_duration_minutes', durationInput ? durationInput.value : '');
                    finalFormData.append('_token', document.querySelector('input[name="_token"]').value);
                    finalFormData.append('_ajax', '1');

                    // PHASE 8 — COMPRESSION
                    async function handleFileAppend(fieldName, filesArray, isMultiple = false) {
                        if (!filesArray || filesArray.length === 0) return;
                        for (let i = 0; i < filesArray.length; i++) {
                            let file = filesArray[i];
                            let originalSize = file.size;
                            let originalType = file.type;
                            
                            if (file && file.size > 500 * 1024) {
                                if (file.type === '' || file.type.startsWith('image/') || file.type === 'application/octet-stream') {
                                    file = await compressFile(file, 1920, 1920, 0.8);
                                    logDiag('PHASE 8', {
                                        field: fieldName,
                                        originalSize,
                                        compressedSize: file.size,
                                        originalType,
                                        resultType: file.type
                                    });
                                }
                            }
                            finalFormData.append(isMultiple ? fieldName + '[]' : fieldName, file, file.name || 'image.jpg');
                        }
                    }

                    if (emailInput && emailInput.files) {
                        await handleFileAppend('evidence_email_image_path', emailInput.files, false);
                    }

                    if (pName === 'minutes_data' && qualityInput && qualityInput.files) {
                        await handleFileAppend('evidence_app_quality_image_path', qualityInput.files, false);
                    }

                    if (pName === 'atlas' && atlasInput && atlasInput.files) {
                        await handleFileAppend('evidence_submitted_image_paths', atlasInput.files, true);
                    }

                    // PHASE 3 — LOG FORMDATA KEYS ONLY
                    const finalKeys = Array.from(finalFormData.keys());
                    logDiag('PHASE 3', {
                        keys: finalKeys,
                        project_name_val: finalFormData.get('project_name'),
                        submission_date_val: finalFormData.get('submission_date'),
                        duration_val: finalFormData.get('submitted_duration_minutes'),
                        has_email: finalFormData.has('evidence_email_image_path'),
                        atlas_count: finalFormData.getAll('evidence_submitted_image_paths[]').length
                    });

                    // PHASE 9 — XHR
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    logDiag('PHASE 9_PRE', { method: 'POST', url: form.action });
                    
                    xhr.onload = function() {
                        logDiag('PHASE 9_POST', {
                            status: xhr.status,
                            responseContentType: xhr.getResponseHeader('Content-Type'),
                            responseTextPreview: xhr.responseText.substring(0, 500)
                        });
                        
                        try {
                            const data = JSON.parse(xhr.responseText);
                            
                            if (xhr.status >= 200 && xhr.status < 300) {
                                if (data.success && data.redirect) {
                                    alert('Berhasil terkirim! Klik OK untuk kembali ke Dashboard.');
                                    window.location.href = data.redirect;
                                    return;
                                }
                                alert(data.message || 'Berhasil terkirim.');
                                window.location.href = "{{ route('dashboard') }}";
                                return;
                            }
                            
                            if (xhr.status === 422) {
                                document.querySelectorAll('.js-error-msg').forEach(el => el.remove());
                                if (data.errors) {
                                    for (const [field, msgs] of Object.entries(data.errors)) {
                                        let inputName = field;
                                        if (field.startsWith('evidence_submitted_image_paths')) {
                                            inputName = 'evidence_submitted_image_paths[]';
                                        }
                                        const input = form.querySelector(`[name="${inputName}"]`);
                                        if (input) {
                                            const p = document.createElement('p');
                                            p.className = 'text-red-500 text-xs mt-1 js-error-msg';
                                            p.innerText = msgs[0];
                                            input.parentElement.appendChild(p);
                                        }
                                    }
                                } else {
                                    alert(data.message || 'Terdapat kesalahan validasi.');
                                }
                            } else if (xhr.status === 413) {
                                alert(data.message || 'Ukuran file terlalu besar. Gagal mengirim laporan.');
                            } else if (xhr.status === 419) {
                                alert('Sesi Anda telah berakhir. Silakan muat ulang halaman ini.');
                            } else {
                                alert(data.message || 'Terjadi kesalahan sistem (Kode: ' + xhr.status + ').');
                            }
                        } catch (e) {
                            alert('Gagal membaca response server.');
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    };
                    
                    xhr.onerror = function() {
                        logDiag('PHASE 9_ERR', 'XHR onerror fired');
                        alert('Koneksi terputus. Pastikan internet Anda stabil lalu coba lagi.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    };
                    
                    xhr.send(finalFormData);
                    
                } catch (err) {
                    logDiag('FATAL_ERROR', err.message);
                    alert('Terjadi kesalahan kompresi atau logika: ' + err.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    