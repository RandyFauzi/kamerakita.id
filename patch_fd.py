import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    old_fd_block = r'''const formData = new FormData\(form\);[\s\S]*?// Client-side payload size validation'''
    
    new_fd_block = '''// Create a fresh FormData to avoid WebKit mutation bugs (formData.delete corruption)
            const finalFormData = new FormData();
            const originalFormData = new FormData(form);
            const projectName = originalFormData.get('project_name');
            
            // Iterate original fields and only keep the ones we need
            for (const [key, value] of originalFormData.entries()) {
                // Skip hidden fields based on project_name
                if (projectName === 'atlas' && key === 'evidence_app_quality_image_path') continue;
                if (projectName === 'minutes_data' && (key === 'evidence_submitted_image_paths[]' || key === 'evidence_submitted_image_paths')) continue;
                
                // Skip file fields that have compressed versions (we will append them later)
                let isReplaced = false;
                if (window.compressedFiles) {
                    for (const [inputId, files] of Object.entries(window.compressedFiles)) {
                        const inputElement = document.getElementById(inputId);
                        if (inputElement && inputElement.name === key && files.length > 0) {
                            isReplaced = true;
                            break;
                        }
                    }
                }
                
                if (!isReplaced) {
                    finalFormData.append(key, value);
                }
            }
            
            // Append the compressed files manually
            if (window.compressedFiles) {
                for (const [inputId, files] of Object.entries(window.compressedFiles)) {
                    const inputElement = document.getElementById(inputId);
                    if (inputElement && files.length > 0) {
                        const fieldName = inputElement.name;
                        
                        // Skip if it's for the other project
                        if (projectName === 'atlas' && fieldName === 'evidence_app_quality_image_path') continue;
                        if (projectName === 'minutes_data' && (fieldName === 'evidence_submitted_image_paths[]' || fieldName === 'evidence_submitted_image_paths')) continue;
                        
                        for (const file of files) {
                            finalFormData.append(fieldName, file, file.name || 'image.jpg');
                        }
                    }
                }
            }

            // Set final reference so the rest of the script works
            const formData = finalFormData;

            // Client-side payload size validation'''

    content = re.sub(old_fd_block, new_fd_block, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Patched FormData logic!")
