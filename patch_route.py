import re

def patch_file(filepath, old_str, new_str):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    content = content.replace(old_str, new_str)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('routes/web.php', "Route::get('/submit-report'", "Route::get('/kirim-laporan'")
patch_file('routes/web.php', "Route::post('/submit-report'", "Route::post('/kirim-laporan'")

print("Patched routes!")
