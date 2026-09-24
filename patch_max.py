import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Change max:2048 to max:30720 (30MB) for all image rules
    content = re.sub(r'max:2048', 'max:30720', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/SubmitVideoWorkReportController.php')
patch_file('app/Http/Controllers/EditRejectedVideoWorkReportController.php')
print("Patched backend validation max limits!")
