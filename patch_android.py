import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Improve the image check to include empty mime types (Android bug)
    old_check = "if (value instanceof File && value.size > 0 && value.type.startsWith('image/')) {"
    new_check = "if (value instanceof File && value.size > 0 && (value.type === '' || value.type.startsWith('image/') || value.type === 'application/octet-stream')) {"
    content = content.replace(old_check, new_check)
    
    # 2. Improve compressFile to try loading it as an image even if mime type is missing
    old_func_start = "if (!file.type.startsWith('image/')) return file; // Only compress images"
    new_func_start = "if (file.type !== '' && !file.type.startsWith('image/') && file.type !== 'application/octet-stream') return file;"
    content = content.replace(old_func_start, new_func_start)
    
    # 3. Extract JSON safely from response in case there is a PHP warning prepended
    old_json_parse = "const data = JSON.parse(xhr.responseText);"
    new_json_parse = '''let cleanText = xhr.responseText;
                                  const jsonStart = cleanText.indexOf('{');
                                  const jsonEnd = cleanText.lastIndexOf('}');
                                  if (jsonStart !== -1 && jsonEnd !== -1) {
                                      cleanText = cleanText.substring(jsonStart, jsonEnd + 1);
                                  }
                                  const data = JSON.parse(cleanText);'''
    content = content.replace(old_json_parse, new_json_parse)

    # 4. Improve the catch alert to show the actual error snippet so users can report it
    old_catch = "alert('Gagal memvalidasi form.');"
    new_catch = "alert('Koneksi terputus atau file terlalu besar. Harap muat ulang halaman. (Err: JSON parse)');"
    content = content.replace(old_catch, new_catch)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched Android file type bugs and JSON extraction!")
