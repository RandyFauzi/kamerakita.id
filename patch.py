import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Remove debugLog function
    content = re.sub(r'function debugLog\(.*?\)\s*\{[\s\S]*?console\.log\([^)]+\);\s*\}', '', content)
    
    # 2. Remove all debugLog calls
    content = re.sub(r'\s*debugLog\([^)]+\);', '', content)

    # 3. Enhance JSON parsing and redirect handling
    # Replace the fetch response handling block
    old_fetch_handling = r'''const response = await fetch\(form\.action, \{[\s\S]*?\}\);[\s\S]*?if \(response\.ok\) \{'''
    
    new_fetch_handling = '''const response = await fetch(form.action, {
                method: 'POST', // Blade has @method('PUT') inside, which will be included in FormData
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Check for loop redirects (Laravel back())
            if (response.redirected && response.url === window.location.href) {
                alert('Sistem menolak request (kemungkinan karena total ukuran gambar terlalu besar sehingga melebihi batas server). Harap perkecil ukuran file atau kurangi jumlah gambar, lalu muat ulang halaman.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                isSubmittingReport = false;
                return;
            }

            if (response.ok) {'''
    
    content = re.sub(old_fetch_handling, new_fetch_handling, content)

    # Replace 413 json parsing
    old_413 = r'''\} else if \(response\.status === 413\) \{\s*const data = await response\.json\(\);\s*alert\(data\.message[^\)]+\);'''
    new_413 = '''} else if (response.status === 413) {
                let msg = 'Gagal mengirim laporan: Total ukuran file terlalu besar (Max server limit).';
                try {
                    const data = await response.json();
                    msg = data.message || msg;
                } catch(e) {}
                alert(msg);'''
    content = re.sub(old_413, new_413, content)

    # Replace 422 json parsing
    old_422 = r'''\} else if \(response\.status === 422\) \{\s*// Validation error\s*const data = await response\.json\(\);'''
    new_422 = '''} else if (response.status === 422) {
                // Validation error
                let data = { errors: {} };
                try {
                    data = await response.json();
                } catch(e) {
                    alert('Validasi gagal dan format error tidak dikenali.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    isSubmittingReport = false;
                    return;
                }'''
    content = re.sub(old_422, new_422, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched JS!")
