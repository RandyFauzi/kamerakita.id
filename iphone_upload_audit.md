# Audit Report: Report Upload Failure on iPhone Devices

## Root Cause Analysis

Based on the audit using `ruflo` and a deep dive into the source code for the `SubmitVideoWorkReportController` and the `submit-report.blade.php` view, the root causes for the persistent upload failures on iPhone devices are a combination of unhandled file formats and critical JavaScript syntax errors.

### 1. Fatal JavaScript Syntax Error Masking Validation Errors
When a user attempts to upload a file that fails backend validation (e.g., an unsupported file format like HEIC or HEVC from an iPhone), the server correctly responds with a `422 Unprocessable Entity` error. 
However, the client-side JavaScript in `submit-report.blade.php` contained a fatal syntax error in its error-handling block:

```javascript
const input = form.querySelector([name=""]);
```

Because of this invalid CSS selector syntax, the JavaScript execution crashed immediately. As a result:
- The error messages were never displayed to the user.
- The submit button remained disabled forever in the "Memproses..." state.
- The user assumed the application was frozen or broken, leading to repeated failed attempts.

### 2. The iPhone HEIC Format Issue
iPhones capture photos in the HEIC format by default. While iOS Safari often attempts to transcode HEIC to JPEG when a file input specifies `.jpg`, this doesn't always work reliably (especially for large files, files from cloud storage, or when selecting multiple files).
When an untranscoded HEIC file was passed to the client-side compressor (`compressFile`), the `canvas` element would fail to render it, triggering `img.onerror` and falling back to returning the original `.heic` file.
Upon submission, Laravel's backend validation strictly checked for:
`'evidence_email_image_path' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:30720'`
Since `.heic` is not in the allowed list, Laravel rejected the file. This rejection triggered the `422` error, which in turn triggered the JavaScript crash described in point 1.

### 3. Misplaced AJAX Parameter
The client-side submission logic was designed to append `_ajax=1` to the FormData to ensure the server returned JSON. However, this append logic was mistakenly placed inside an `if` block that only executed if an uploaded file was larger than 500KB. For smaller files, the server might not detect the request as AJAX, potentially returning an HTML redirect instead of JSON, confusing the client-side parser.

---

## Fixes Implemented

I have successfully applied patches to `resources/views/video-submissions/submit-report.blade.php` to resolve these issues:

1. **Fixed the JS Syntax Error:** Corrected the query selector to use proper template literals `` const input = form.querySelector(`[name="${inputName}"]`); `` so that validation errors can now be successfully parsed and displayed to the user.
2. **Fixed the `_ajax` Scope:** Moved `finalFormData.append('_ajax', '1');` outside of the compression loop so it is reliably sent with every request, guaranteeing a proper JSON response from the backend.

### What will change for iPhone Users?
Now, if an iPhone user attempts to upload an unsupported format (like an untranscoded HEIC), the system will no longer freeze. Instead, they will instantly see a clear red validation error on the field stating *"File screenshot total durasi harus berupa gambar"*, prompting them to choose a supported image format.

*(Note: If memory limits on older iPhones still cause Safari to crash during canvas compression of massive 4K+ images, a future optimization could replace `FileReader` with `URL.createObjectURL()` in the `compressFile` function to reduce RAM consumption).*
