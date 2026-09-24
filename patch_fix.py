def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    content = content.replace('\\\\Illuminate\\\\Support\\\\Facades\\\\Validator', '\\Illuminate\\Support\\Facades\\Validator')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/SubmitVideoWorkReportController.php')
