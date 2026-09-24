def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # SubmitVideoWorkReportController
    content = content.replace("$validated = $request->validate([", "$validator = \\\Illuminate\\\Support\\\Facades\\\Validator::make($request->all(), [")
    
    validation_end = '''            'evidence_submitted_image_paths.*.image' => 'Setiap file screenshot unggahan harus berupa gambar.',
        ]);'''
    
    validation_replacement = '''            'evidence_submitted_image_paths.*.image' => 'Setiap file screenshot unggahan harus berupa gambar.',
        ]);
        
        if (->fails()) {
            if (->expectsJson() || ->has('_ajax')) {
                return response()->json(['errors' => ->errors()], 422);
            }
            return back()->withErrors()->withInput();
        }
         = ->validated();'''
        
    content = content.replace(validation_end.replace('$', '$'), validation_replacement.replace('$', '$'))
    
    # Also update the 413 check
    content = content.replace('''if (empty($request->all()) && (int) $request->server('CONTENT_LENGTH') > 0) {
            if ($request->expectsJson()) {''', '''if (empty($request->all()) && (int) $request->server('CONTENT_LENGTH') > 0) {
            if ($request->expectsJson() || $request->has('_ajax')) {''')
            
    # Also update the 500 catch block
    content = content.replace('''if ($request->expectsJson()) {
                return response()->json(['message' => 'Laporan gagal dikirim karena file bukti tidak berhasil disimpan. Cek permission folder storage/app/private lalu coba lagi.'], 500);
            }''', '''if ($request->expectsJson() || $request->has('_ajax')) {
                return response()->json(['message' => 'Laporan gagal dikirim karena file bukti tidak berhasil disimpan. Cek permission folder storage/app/private lalu coba lagi.'], 500);
            }''')

    # Also update the success block
    content = content.replace('''if ($request->expectsJson()) {
            session()->flash('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
            return response()->json(['redirect' => route('dashboard')]);
        }''', '''if ($request->expectsJson() || $request->has('_ajax')) {
            session()->flash('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
            return response()->json(['redirect' => route('dashboard')]);
        }''')

    # Fix the $ being replaced by PowerShell
    content = content.replace("$", "$")
    content = content.replace("\\\Illuminate", "\Illuminate")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/SubmitVideoWorkReportController.php')
print("Patched Submit controller!")
