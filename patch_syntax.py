import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    bad_string = r'''                \Illuminate\Support\Facades\Log::info('DIAGNOSTIC STORE', ['all' => ->all(), 'files' => ->allFiles(), 'content_len' => ->server('CONTENT_LENGTH')]);
         = Partner::where('user_id', Auth::id())->first();'''
    good_string = r'''         = Partner::where('user_id', Auth::id())->first();'''

    content = content.replace(bad_string, good_string)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('app/Http/Controllers/SubmitVideoWorkReportController.php')
print("Fixed PHP syntax error!")
