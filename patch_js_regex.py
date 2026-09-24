import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # We want to replace the first JSON.parse(cleanText) in the 200 block
    
    # Let's just find the catch(e) {} block of the 200 section and replace it entirely!
    old_200_block = re.search(r'const data = JSON\.parse\(cleanText\);\s*if \(data\.redirect\) redirectUrl = data\.redirect;\s*\} catch\(e\) \{\}\s*window\.location\.href = redirectUrl;', content)
    
    if old_200_block:
        new_200_block = """const data = JSON.parse(cleanText);
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
                                    if (data.redirect) redirectUrl = data.redirect;
                                } catch(e) {}
                                window.location.href = redirectUrl;"""
        content = content[:old_200_block.start()] + new_200_block + content[old_200_block.end():]

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')