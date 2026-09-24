import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find the broken block and fix it
    bad_block = '''        if (->fails()) {
            if (->expectsJson() || ->has('_ajax')) {
                return response()->json(['errors' => ->errors()], 422);
            }
            return back()->withErrors()->withInput();
        }
         = ->validated();'''

    good_block = '''        if ($validator->fails()) {
            if ($request->expectsJson() || $request->has('_ajax')) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();'''

    content = content.replace(bad_block, good_block)

    # Also fix 413
    content = content.replace('''if (empty(->all()) && (int) ->server('CONTENT_LENGTH') > 0) {
            if (->expectsJson() || ->has('_ajax')) {''', '''if (empty($request->all()) && (int) $request->server('CONTENT_LENGTH') > 0) {
            if ($request->expectsJson() || $request->has('_ajax')) {''')

    # Also fix 500
    content = content.replace('''if (->expectsJson() || ->has('_ajax')) {
                return response()->json(['message' => 'Laporan gagal dikirim karena file bukti tidak berhasil disimpan. Cek permission folder storage/app/private lalu coba lagi.'], 500);
            }''', '''if ($request->expectsJson() || $request->has('_ajax')) {
                return response()->json(['message' => 'Laporan gagal dikirim karena file bukti tidak berhasil disimpan. Cek permission folder storage/app/private lalu coba lagi.'], 500);
            }''')

    # Also fix success
    content = content.replace('''if (->expectsJson() || ->has('_ajax')) {
            session()->flash('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
            return response()->json(['redirect' => route('dashboard')]);
        }''', '''if ($request->expectsJson() || $request->has('_ajax')) {
            session()->flash('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
            return response()->json(['redirect' => route('dashboard')]);
        }''')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/SubmitVideoWorkReportController.php')