import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    content = content.replace('(Maks: 2MB)', '(Otomatis dikompres)')
    content = content.replace('(Maks: 2MB/file)', '(Otomatis dikompres)')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('lang/id/dashboard.php')
patch_file('lang/en/dashboard.php')
print("Patched language files!")
