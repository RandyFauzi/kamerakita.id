import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    bad_onload = r'''                      xhr.onload = function() {
                          if (xhr.status >= 200 && xhr.status < 300) {
                              // Success or successful redirect
                              let redirectUrl = "{{ route('dashboard') }}";
                              try {
                                  const data = JSON.parse(xhr.responseText);
                                  if (data.redirect) redirectUrl = data.redirect;
                              } catch(e) {}
                              window.location.href = redirectUrl;'''
                              
    good_onload = r'''                      xhr.onload = function() {
                          if (xhr.status >= 200 && xhr.status < 300) {
                              // Check if it's actually JSON. If it's HTML, it means XHR followed a 302 redirect from a validation error!
                              const contentType = xhr.getResponseHeader('content-type') || '';
                              if (contentType.includes('application/json')) {
                                  let redirectUrl = "{{ route('dashboard') }}";
                                  try {
                                      const data = JSON.parse(xhr.responseText);
                                      if (data.redirect) redirectUrl = data.redirect;
                                  } catch(e) {}
                                  window.location.href = redirectUrl;
                              } else {
                                  // It followed a redirect, meaning validation failed or something else happened
                                  // The easiest fallback is to submit the native form so Laravel handles the errors normally
                                  form.submit();
                              }'''

    # For edit-rejected-report, the fallback URL is different
    if "edit" in filepath:
        good_onload = good_onload.replace("{{ route('dashboard') }}", "{{ route('video-submissions.report-history') }}")
        bad_onload = bad_onload.replace("{{ route('dashboard') }}", "{{ route('video-submissions.report-history') }}")

    content = content.replace(bad_onload, good_onload)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Fixed JS XHR transparent redirect bug!")
