import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Change the catch alert to show the exact error message
    old_catch = "alert('Koneksi terputus atau file terlalu besar. Harap muat ulang halaman. (Err: JSON parse)');"
    new_catch = "alert('JS Error: ' + e.message + ' | Data: ' + xhr.responseText.substring(0, 100));"
    content = content.replace(old_catch, new_catch)
    
    # Also for the compression error
    content = content.replace("alert('Error saat kompresi: ' + err.message);", "alert('Kompresi Error: ' + err.message);")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched alerts!")
