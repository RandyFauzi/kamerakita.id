def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # EditRejectedVideoWorkReportController
    content = content.replace("$validated = $request->validate([", "$validator = \\Illuminate\\Support\\Facades\\Validator::make($request->all(), [")
    
    validation_end = '''            'evidence_submitted_image_paths.*.image' => 'Setiap file screenshot unggahan harus berupa gambar.',
        ]);'''
    
    validation_replacement = '''            'evidence_submitted_image_paths.*.image' => 'Setiap file screenshot unggahan harus berupa gambar.',
        ]);
        
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->has('_ajax')) {
                return response()->json(['validation_failed' => true, 'errors' => $validator->errors()], 200);
            }
            return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();'''
        
    content = content.replace(validation_end, validation_replacement)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/EditRejectedVideoWorkReportController.php')