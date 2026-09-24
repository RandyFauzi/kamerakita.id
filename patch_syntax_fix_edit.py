def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Fix the missing comma syntax error
    old_broken = """                    'message' => 'Gagal mengirim laporan: Total ukuran file yang diunggah terlalu besar. Harap perkecil/kompres ukuran screenshot Anda (Otomatis dikompres) lalu coba lagi.'
                'server_error' => true], 200);"""
    
    new_fixed = """                    'message' => 'Gagal mengirim laporan: Total ukuran file yang diunggah terlalu besar. Harap perkecil/kompres ukuran screenshot Anda (Otomatis dikompres) lalu coba lagi.',
                    'server_error' => true
                ], 200);"""
                
    content = content.replace(old_broken, new_fixed)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/EditRejectedVideoWorkReportController.php')