def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    old_onload = """                                    const data = JSON.parse(cleanText);
                                  if (data.redirect) redirectUrl = data.redirect;"""

    new_onload = """                                    const data = JSON.parse(cleanText);
                                    if (data.server_error) {
                                        alert(data.message || 'Terjadi kesalahan sistem dari server.');
                                        submitBtn.disabled = false;
                                        if (submitBtn.innerHTML.includes('svg')) submitBtn.innerHTML = originalText;
                                        return;
                                    }
                                    if (data.validation_failed) {
                                        document.querySelectorAll('.js-error-msg').forEach(el => el.remove());
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
                                        submitBtn.disabled = false;
                                        if (submitBtn.innerHTML.includes('svg')) submitBtn.innerHTML = originalText;
                                        return;
                                    }
                                  if (data.redirect) redirectUrl = data.redirect;"""

    content = content.replace(old_onload, new_onload)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')