import re
import os

def update_lang_file(path, replacements):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()

    for k, v in replacements.items():
        if f"'{k}' =>" not in content:
            content = content.replace("];", f"    '{k}' => '{v}',\n];")

    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

id_auth = {
    'username_hint': 'Gunakan huruf kecil dan angka tanpa spasi. Ini akan menjadi email internal Anda untuk menerima informasi tugas.',
    'username_placeholder': 'contoh: budi123',
    'confirm_password_label_full': 'Konfirmasi Kata Sandi',
    'activation_code_label': 'Kode Aktivasi (Activation Code)',
    'activation_code_placeholder': 'Contoh: KMK-01ASQW',
    'referral_code_label': 'Kode Referral',
    'referral_code_optional': '(Opsional — dari Vendor/Rekruter Anda)',
    'referral_code_placeholder': 'Contoh: REF-ABCDEF',
}

en_auth = {
    'username_hint': 'Use lowercase letters and numbers without spaces. This will be your internal email to receive task information.',
    'username_placeholder': 'example: budi123',
    'confirm_password_label_full': 'Confirm Password',
    'activation_code_label': 'Activation Code',
    'activation_code_placeholder': 'Example: KMK-01ASQW',
    'referral_code_label': 'Referral Code',
    'referral_code_optional': '(Optional — from your Vendor/Recruiter)',
    'referral_code_placeholder': 'Example: REF-ABCDEF',
}

update_lang_file("lang/id/auth_view.php", id_auth)
update_lang_file("lang/en/auth_view.php", en_auth)

def replace_in_file(path, replacements):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()
    for old, new in replacements:
        content = content.replace(old, new)
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

reps = [
    (">Konfirmasi Kata Sandi<", ">{{ __('auth_view.confirm_password_label_full') }}<"),
    (">Gunakan huruf kecil dan angka tanpa spasi. Ini akan menjadi email internal Anda untuk menerima informasi tugas.<", ">{{ __('auth_view.username_hint') }}<"),
    ("placeholder=\"contoh: budi123\"", "placeholder=\"{{ __('auth_view.username_placeholder') }}\""),
    (">Kode Aktivasi (Activation Code)<", ">{{ __('auth_view.activation_code_label') }}<"),
    ("placeholder=\"Contoh: KMK-01ASQW\"", "placeholder=\"{{ __('auth_view.activation_code_placeholder') }}\""),
    (">Kode Referral<", ">{{ __('auth_view.referral_code_label') }}<"),
    (">(Opsional — dari Vendor/Rekruter Anda)<", ">{{ __('auth_view.referral_code_optional') }}<"),
    ("placeholder=\"Contoh: REF-ABCDEF\"", "placeholder=\"{{ __('auth_view.referral_code_placeholder') }}\""),
]

replace_in_file("resources/views/auth/register.blade.php", reps)
print("Done")
