import re

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Add the missing </div> back before the script tag
    content = content.replace('''</form>
            </div>
        </div>
    </div>
    
    <!-- Bulletproof Client-Side Compression -->''', '''</form>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Bulletproof Client-Side Compression -->''')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('resources/views/video-submissions/submit-report.blade.php')
patch_file('resources/views/video-submissions/edit-rejected-report.blade.php')
print("Restored missing </div>!")
