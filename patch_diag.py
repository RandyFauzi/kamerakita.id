import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Add a version indicator next to the submit button
    version_badge = '''<span class="text-xs text-gray-400 mr-4" id="app-version">v3.0.1 (No-JS Native)</span>'''
    content = content.replace('{{ __("dashboard.submit_report_page.btn_submit") }}', version_badge + '\n                              {{ __("dashboard.submit_report_page.btn_submit") }}')

    # Add a sanity check script
    sanity_script = '''
<script>
    document.getElementById('submit-report-form').addEventListener('submit', function(e) {
        var formData = new FormData(this);
        var project = formData.get('project_name');
        if (!project) {
            alert('BUG TERDETEKSI: Form data hilang sebelum terkirim. Harap hubungi admin.');
        } else {
            // alert('Mengirim data: ' + project);
        }
    });
</script>
    </div>
</x-app-layout>'''
    content = content.replace('    </div>\n</x-app-layout>', sanity_script)
    
    # Also fix accept HEIC bug in iOS
    content = content.replace('accept="image/jpeg,image/png,image/webp"', 'accept="image/*"')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
print("Patched diagnostics and HEIC support!")
