import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replace new File with just resolving the blob
    content = content.replace('''const newFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(newFile);''', '''resolve(blob);''')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched blob!")
