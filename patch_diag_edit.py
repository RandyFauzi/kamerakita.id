import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    content = content.replace('accept="image/jpeg,image/png,image/webp"', 'accept="image/*"')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched edit report!")
