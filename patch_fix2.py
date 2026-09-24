with open('app/Http/Controllers/SubmitVideoWorkReportController.php', 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(r'\\Illuminate\\Support\\Facades\\Validator', r'\Illuminate\Support\Facades\Validator')
with open('app/Http/Controllers/SubmitVideoWorkReportController.php', 'w', encoding='utf-8') as f:
    f.write(content)
