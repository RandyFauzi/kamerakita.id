def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Revert image/* back to force iOS HEIC-to-JPEG transcoding
    content = content.replace('accept="image/*"', 'accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')