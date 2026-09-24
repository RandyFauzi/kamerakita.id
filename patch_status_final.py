def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Change 422 to 200 and add validation_failed flag
    old_val = "return response()->json(['errors' => $validator->errors()], 422);"
    new_val = "return response()->json(['validation_failed' => true, 'errors' => $validator->errors()], 200);"
    content = content.replace(old_val, new_val)

    # Change 413 to 200 and add server_error flag
    old_413 = "], 413);"
    new_413 = ", 'server_error' => true], 200);"
    content = content.replace(old_413, new_413)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/SubmitVideoWorkReportController.php')
patch_file('app/Http/Controllers/EditRejectedVideoWorkReportController.php')