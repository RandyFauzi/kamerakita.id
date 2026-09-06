import os

def update_lang_file(path, content_str):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        f.write(content_str)

id_onboarding = """<?php
return [
    'title' => 'KameraKita AI - Form Pendaftaran Kontributor',
    'alert' => 'Hati-hati terhadap penipuan (SCAM)! KameraKita AI 100% GRATIS dan tidak pernah memungut biaya apapun.',
    'welcome_badge' => 'Selamat Datang',
    'welcome_title' => 'Mulai Hasilkan Cuan dari<br/>Rekaman Video Kamu',
    'welcome_desc' => 'Langsung isi formulir, atau daftar lewat WhatsApp agar dibantu oleh tim kami.',
    'btn_form' => 'ISI FORM PENDAFTARAN',
    'btn_wa' => 'DAFTAR LEWAT WHATSAPP',
    'step_name_badge' => 'Langkah 1 dari 4',
    'step_name_title' => 'Siapa Nama Lengkap Kamu?',
    'step_name_input' => 'Ketik nama lengkap...',
    'btn_back' => 'Kembali',
    'btn_next' => 'Lanjut',
    'step_wa_badge' => 'Langkah 2 dari 4',
    'step_wa_title' => 'Berapa Nomor WhatsApp Kamu?',
    'step_wa_desc' => 'Pastikan nomor ini aktif untuk komunikasi project.',
    'step_wa_input' => 'Contoh: 08123456789',
    'step_device_badge' => 'Langkah 3 dari 4',
    'step_device_title' => 'HP apa yang bakal kamu pake?',
    'step_device_input' => 'Ketik merk & tipe HP kamu...',
    'step_headstrap_badge' => 'Langkah terakhir!',
    'step_headstrap_title' => 'Udah Punya Aksesoris Headstrap?',
    'step_headstrap_yes' => 'Ya, sudah punya',
    'step_headstrap_no' => 'Tidak, belum Punya',
    'btn_submit' => 'Selesai & Kirim Formulir',
    'submitting' => 'Memproses...',
    'success_badge' => 'Hore! Pendaftaran Berhasil!',
    'success_title' => 'Akun KameraKita AI Kamu Sedang Disiapkan',
    'success_desc' => 'Tim kami akan segera menghubungi nomor WhatsApp yang kamu daftarkan untuk memberikan pengarahan project pertama.',
    'btn_back_home' => 'KEMBALI KE BERANDA'
];
"""

en_onboarding = """<?php
return [
    'title' => 'KameraKita AI - Contributor Registration Form',
    'alert' => 'Beware of SCAMS! KameraKita AI is 100% FREE and never asks for any payments.',
    'welcome_badge' => 'Welcome',
    'welcome_title' => 'Start Earning from<br/>Your Video Recordings',
    'welcome_desc' => 'Fill out the form directly, or register via WhatsApp to be assisted by our team.',
    'btn_form' => 'FILL REGISTRATION FORM',
    'btn_wa' => 'REGISTER VIA WHATSAPP',
    'step_name_badge' => 'Step 1 of 4',
    'step_name_title' => 'What is Your Full Name?',
    'step_name_input' => 'Type your full name...',
    'btn_back' => 'Back',
    'btn_next' => 'Next',
    'step_wa_badge' => 'Step 2 of 4',
    'step_wa_title' => 'What is Your WhatsApp Number?',
    'step_wa_desc' => 'Ensure this number is active for project communication.',
    'step_wa_input' => 'Example: 08123456789',
    'step_device_badge' => 'Step 3 of 4',
    'step_device_title' => 'What Phone Will You Use?',
    'step_device_input' => 'Type your phone brand & model...',
    'step_headstrap_badge' => 'Last Step!',
    'step_headstrap_title' => 'Do You Have a Headstrap Accessory?',
    'step_headstrap_yes' => 'Yes, I have one',
    'step_headstrap_no' => 'No, I don\\'t have one',
    'btn_submit' => 'Finish & Submit Form',
    'submitting' => 'Processing...',
    'success_badge' => 'Hooray! Registration Successful!',
    'success_title' => 'Your KameraKita AI Account is Being Prepared',
    'success_desc' => 'Our team will contact the WhatsApp number you registered shortly to provide the first project briefing.',
    'btn_back_home' => 'BACK TO HOME'
];
"""

update_lang_file("lang/id/onboarding.php", id_onboarding)
update_lang_file("lang/en/onboarding.php", en_onboarding)

def replace_in_file(path, replacements):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()
    for old, new in replacements:
        content = content.replace(old, new)
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

reps = [
    ("KameraKita AI - Form Pendaftaran Kontributor", "{{ __('onboarding.title') }}"),
    ("Hati-hati terhadap penipuan (SCAM)! KameraKita AI 100% GRATIS dan tidak pernah memungut biaya apapun.", "{{ __('onboarding.alert') }}"),
    (">Selamat Datang<", ">{{ __('onboarding.welcome_badge') }}<"),
    ("Mulai Hasilkan Cuan dari<br/>Rekaman Video Kamu", "{!! __('onboarding.welcome_title') !!}"),
    ("Langsung isi formulir, atau daftar lewat WhatsApp agar dibantu oleh tim kami.", "{{ __('onboarding.welcome_desc') }}"),
    (">ISI FORM PENDAFTARAN<", ">{{ __('onboarding.btn_form') }}<"),
    (">DAFTAR LEWAT WHATSAPP<", ">{{ __('onboarding.btn_wa') }}<"),
    (">Langkah 1 dari 4<", ">{{ __('onboarding.step_name_badge') }}<"),
    (">Siapa Nama Lengkap Kamu?<", ">{{ __('onboarding.step_name_title') }}<"),
    ("Ketik nama lengkap...", "{{ __('onboarding.step_name_input') }}"),
    (">Kembali<", ">{{ __('onboarding.btn_back') }}<"),
    (">Lanjut<", ">{{ __('onboarding.btn_next') }}<"),
    (">Langkah 2 dari 4<", ">{{ __('onboarding.step_wa_badge') }}<"),
    (">Berapa Nomor WhatsApp Kamu?<", ">{{ __('onboarding.step_wa_title') }}<"),
    ("Pastikan nomor ini aktif untuk komunikasi project.", "{{ __('onboarding.step_wa_desc') }}"),
    ("Contoh: 08123456789", "{{ __('onboarding.step_wa_input') }}"),
    (">Langkah 3 dari 4<", ">{{ __('onboarding.step_device_badge') }}<"),
    (">HP apa yang bakal kamu pake?<", ">{{ __('onboarding.step_device_title') }}<"),
    ("Ketik merk & tipe HP kamu...", "{{ __('onboarding.step_device_input') }}"),
    (">Langkah terakhir!<", ">{{ __('onboarding.step_headstrap_badge') }}<"),
    (">Udah Punya Aksesoris Headstrap?<", ">{{ __('onboarding.step_headstrap_title') }}<"),
    (">Ya, sudah punya<", ">{{ __('onboarding.step_headstrap_yes') }}<"),
    (">Tidak, belum Punya<", ">{{ __('onboarding.step_headstrap_no') }}<"),
    (">Selesai &amp; Kirim Formulir<", ">{{ __('onboarding.btn_submit') }}<"),
    ("Selesai & Kirim Formulir", "{{ __('onboarding.btn_submit') }}"),
    ("Memproses...", "{{ __('onboarding.submitting') }}"),
    (">Hore! Pendaftaran Berhasil!<", ">{{ __('onboarding.success_badge') }}<"),
    (">Akun KameraKita AI Kamu Sedang Disiapkan<", ">{{ __('onboarding.success_title') }}<"),
    ("Tim kami akan segera menghubungi nomor WhatsApp yang kamu daftarkan untuk memberikan pengarahan project pertama.", "{{ __('onboarding.success_desc') }}"),
    (">KEMBALI KE BERANDA<", ">{{ __('onboarding.btn_back_home') }}<"),
]

replace_in_file("resources/views/onboarding/form.blade.php", reps)
print("Done")
