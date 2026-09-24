import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    log_statement = r'''        \Illuminate\Support\Facades\Log::info('DIAGNOSTIC STORE', ['all' => ->all(), 'files' => ->allFiles(), 'content_len' => ->server('CONTENT_LENGTH')]);
         = Partner::where('user_id', Auth::id())->first();'''

    content = content.replace(' = Partner::where(\'user_id\', Auth::id())->first();', log_statement, 1)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/SubmitVideoWorkReportController.php')
print("Injected diagnostic log!")
