import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Remove the entire script block that handles the form submission and compression
    # The script block starts with <script> after the form and ends with </script>
    content = re.sub(r'<script>[\s\S]*?async function compressImage[\s\S]*?</script>', '', content)
    
    # Remove the onchange attribute that triggered compression
    content = re.sub(r'onchange="handleFileCompression\(event\)"', '', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched and removed JS!")
