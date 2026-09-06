import re

def update_lang_file(path, replacements):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()

    for k, v in replacements.items():
        # Insert key-value pairs into the respective array
        content = re.sub(f"('{k}'\s*=>\s*\[)", f"\\1\n{v}", content)

    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

id_replacements = {
    'nav': "        'vendor' => 'Jadi Mitra Vendor',",
    'features': """        'title2' => 'Kerja simpel, hasil maksimal!',
        'subtitle2' => 'Kamu cukup rekam aktivitas harian dari rumah. Laporan yang lolos approved akan masuk rekap pendapatan secara transparan.',
        'item_1_title' => 'Bantu AI Biar Pintar',
        'item_1_desc' => 'KameraKita AI ngajarin teknologi pintar (AI) biar bisa paham cara manusia beraktivitas di dalam rumah.',
        'item_2_title' => 'Rekam Kegiatan Rumah',
        'item_2_desc' => 'Tugas kamu cuma pakai alat di kepala, lalu rekam aktivitas harian kayak ngepel, nyuci piring, atau beres-beres.',
        'item_3_title' => 'Kirim Video & Terima Cuan',
        'item_3_desc' => 'Kerjaan rumah beres, dompet tetep tebel. Gak perlu keahlian khusus, semua orang semua kalangan pasti bisa!',""",
    
    'calculator': """        'title2' => 'Pilih ritme kerja yang paling cocok',
        'subtitle2' => 'Rate dasar Rp60.000 per jam rekaman bersih. Simulasi ini membantu kamu membayangkan potensi cuan mingguan sebelum mulai.',""",
    
    'steps': """        'title2' => 'Cuma 3 langkah buat mulai dapet cuan',
        'subtitle2' => 'Dari daftar sampai pembayaran, semuanya dibuat simpel dan bakal dipandu tim KameraKita.',
        'step_1_title' => 'Gabung & Ikuti Briefing',
        'step_1_desc' => 'Daftar lewat WhatsApp, lalu tim kami bakal jelasin cara kerja, tugas, dan kebutuhan alat.',
        'step_2_title' => 'Rekam Aktivitasmu',
        'step_2_desc' => 'Pilih tugas yang tersedia, pasang HP sesuai panduan, lalu rekam aktivitas sehari-hari seperti biasa.',
        'step_3_title' => 'Upload & Terima Bayaran',
        'step_3_desc' => 'Kirim hasil rekaman untuk dicek. Setelah lolos QC, durasi approved masuk ke pembayaran bulanan.',""",

    'testimonials': """        'title2' => 'Cerita kontributor yang mulai punya<br>penghasilan tambahan',
        'subtitle2' => 'Beberapa pengalaman yang menggambarkan bagaimana alur kerja, QC, dan pembayaran dijalankan dengan lebih rapi.',""",
    
    'faq': """        'title' => 'Pertanyaan yang Sering Diajukan',
        'q1_q' => 'Apakah pendaftaran mitra dipungut biaya?',
        'q1_a' => '100% GRATIS. Kami tidak pernah memungut biaya apapun dari kontributor. Segala bentuk pungutan mengatasnamakan KameraKita adalah penipuan.',
        'q3_q2' => 'Kapan komisi hasil rekap durasi akan dicairkan?',
        'q3_a2' => 'Pencairan komisi diproses manual oleh admin sesuai jadwal operasional berdasarkan rekap durasi yang sudah approved.',"""
}

en_replacements = {
    'nav': "        'vendor' => 'Become a Vendor',",
    'features': """        'title2' => 'Simple work, maximum results!',
        'subtitle2' => 'Just record your daily activities from home. Approved reports transparently enter your earnings summary.',
        'item_1_title' => 'Help AI Get Smarter',
        'item_1_desc' => 'KameraKita AI teaches smart technology (AI) to understand human activities inside the house.',
        'item_2_title' => 'Record Home Activities',
        'item_2_desc' => 'Your task is just wearing a head mount and recording daily activities like mopping, washing dishes, or cleaning up.',
        'item_3_title' => 'Send Video & Get Paid',
        'item_3_desc' => 'Housework done, wallet stays thick. No special skills needed, everyone can definitely do it!',""",
    
    'calculator': """        'title2' => 'Choose the work rhythm that fits you',
        'subtitle2' => 'Base rate is Rp60,000 per hour of clean recording. This simulation helps you imagine your weekly potential before starting.',""",
    
    'steps': """        'title2' => 'Only 3 steps to start earning',
        'subtitle2' => 'From registration to payment, everything is made simple and guided by the KameraKita team.',
        'step_1_title' => 'Join & Attend Briefing',
        'step_1_desc' => 'Register via WhatsApp, and our team will explain how it works, tasks, and equipment needs.',
        'step_2_title' => 'Record Your Activities',
        'step_2_desc' => 'Choose available tasks, mount your phone according to guidelines, and record daily activities as usual.',
        'step_3_title' => 'Upload & Get Paid',
        'step_3_desc' => 'Send your recordings for checking. Once they pass QC, approved durations are added to your monthly payout.',""",

    'testimonials': """        'title2' => 'Stories of contributors who started earning extra income',
        'subtitle2' => 'Experiences that describe how the workflow, QC, and payments are executed neatly.',""",
    
    'faq': """        'title' => 'Frequently Asked Questions',
        'q1_q' => 'Is there a registration fee for contributors?',
        'q1_a' => '100% FREE. We never charge any fees to contributors. Any form of collection on behalf of KameraKita is a scam.',
        'q3_q2' => 'When will the accumulated duration commissions be disbursed?',
        'q3_a2' => 'Commission disbursements are processed manually by admins according to the operational schedule based on approved durations.',"""
}

update_lang_file("lang/id/landing.php", id_replacements)
update_lang_file("lang/en/landing.php", en_replacements)

print("Lang files updated!")
