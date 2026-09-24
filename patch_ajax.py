def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    content = content.replace("finalFormData.append(key, compressedFile, value.name);", "finalFormData.append(key, compressedFile, value.name);\n                            finalFormData.append('_ajax', '1');")
    
    if "finalFormData.append('_ajax', '1');" not in content:
        content = content.replace("xhr.send(finalFormData);", "finalFormData.append('_ajax', '1');\n                    xhr.send(finalFormData);")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')