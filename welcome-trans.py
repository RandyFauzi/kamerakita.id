import re
import os

def update_lang_file(path, replacements):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()

    for k, v in replacements.items():
        if f"'{k}' =>" not in content:
            # Add before the last bracket
            content = content.replace("];", f"    '{k}' => '{v}',\n];")

    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

id_landing = {
    'calc_rate': 'RATE PER JAM',
    'calc_rate_desc': 'Dikalikan jam kerja harian yang laporan videonya lolos QC.',
    'calc_choose': 'Pilih simulasi kerja',
    'calc_est_total': 'ESTIMASI TOTAL BULANAN',
    'calc_est_desc': 'Dengan {hours} jam/hari dan rate dasar Rp60.000/jam.',
    'calc_potensi': 'POTENSI MINGGUAN',
    'calc_btn': 'MULAI DAFTAR SEKARANG',
    'calc_note': 'Simulasi bukan jaminan pendapatan. Nominal mengikuti laporan approved dan ketentuan operasional.',
    'calc_base': 'DASAR HITUNG',
    'calc_base_val': 'Jam approved',
    'calc_verif': 'VERIFIKASI',
    'calc_verif_val': 'Lewat QC',
    
    'testi_1': 'Laporan rapi, pembayaran jadi lebih tenang',
    'testi_2': 'Awalnya ragu, setelah rutin submit mulai terasa hasilnya',
    'testi_3': 'QC Jelas, jadi tahu laporan mana yang perlu diperbaiki',
    'testi_4': 'Modal HP dan waktu luang, bisa mulai dari rumah',
    
    'faq_1_ans': 'Sama sekali tidak. Pendaftaran Mitra Kontributor di KAMERAKITA AI 100% GRATIS tanpa modal awal apa pun.',
    'faq_2_ans': 'Tidak perlu. Anda bisa menggunakan smartphone yang sudah Anda miliki untuk mulai merekam.',
    'faq_3_ans': 'Pencairan komisi diproses manual oleh admin sesuai jadwal operasional berdasarkan rekap durasi yang sudah approved.',
    'faq_4_ans': 'Tentu. Anda akan dibekali dokumen standar operasional (SOP) dan arahan aktivitas yang jelas untuk setiap tugas.',
    'faq_5_ans': 'Jika rekaman ditolak (rejected), maka durasi tersebut tidak akan dihitung. Anda bisa mengulang sesuai revisi yang diberikan QC.',
    
    'footer_desc': 'Platform Rekam Video & Datasets Terpercaya No. 1 di Indonesia untuk Mitra & Enterprise Computer Vision.',
    'footer_services': 'LAYANAN MITRA',
    'footer_acc': 'AKSES AKUN',
    'footer_corp': 'PERUSAHAAN',
    'footer_domain': 'Domain Resmi: kamerakitaid.site',
    'footer_pt': 'PT KAMERAKITA AI Indonesia',
    
    'js_santai_title': 'Santai',
    'js_santai_desc': 'Mulai pelan, tetap produktif',
    'js_fokus_title': 'Fokus',
    'js_fokus_desc': 'Lebih rutin, hasil lebih terasa',
    'js_gacor_title': 'Gacor Ketua!!!',
    'js_gacor_desc': 'Mode serius cari cuan',
}

en_landing = {
    'calc_rate': 'RATE PER HOUR',
    'calc_rate_desc': 'Multiplied by daily work hours for video reports that pass QC.',
    'calc_choose': 'Choose work simulation',
    'calc_est_total': 'ESTIMATED MONTHLY TOTAL',
    'calc_est_desc': 'With {hours} hours/day and base rate of Rp60,000/hour.',
    'calc_potensi': 'WEEKLY POTENTIAL',
    'calc_btn': 'START REGISTERING NOW',
    'calc_note': 'Simulation is not a guarantee of income. Nominal follows approved reports and operational terms.',
    'calc_base': 'CALCULATION BASE',
    'calc_base_val': 'Approved hours',
    'calc_verif': 'VERIFICATION',
    'calc_verif_val': 'Pass QC',
    
    'testi_1': 'Neat reports, payments make you calmer',
    'testi_2': 'Doubtful at first, felt the results after routine submits',
    'testi_3': 'Clear QC, so we know which reports need fixing',
    'testi_4': 'Only need a phone and free time, can start from home',
    
    'faq_1_ans': 'Not at all. Contributor Partner Registration at KAMERAKITA AI is 100% FREE without any initial capital.',
    'faq_2_ans': 'No need. You can use the smartphone you already own to start recording.',
    'faq_3_ans': 'Commission disbursements are processed manually by admins according to the operational schedule based on approved durations.',
    'faq_4_ans': 'Of course. You will be provided with standard operating procedures (SOP) and clear activity directions for each task.',
    'faq_5_ans': 'If the recording is rejected, the duration will not be counted. You can repeat according to the revision given by QC.',
    
    'footer_desc': 'No. 1 Trusted Video Recording & Datasets Platform in Indonesia for Partners & Enterprise Computer Vision.',
    'footer_services': 'PARTNER SERVICES',
    'footer_acc': 'ACCOUNT ACCESS',
    'footer_corp': 'COMPANY',
    'footer_domain': 'Official Domain: kamerakitaid.site',
    'footer_pt': 'PT KAMERAKITA AI Indonesia',
    
    'js_santai_title': 'Relaxed',
    'js_santai_desc': 'Start slow, stay productive',
    'js_fokus_title': 'Focused',
    'js_fokus_desc': 'More routine, better results',
    'js_gacor_title': 'Pro Mode!!!',
    'js_gacor_desc': 'Serious mode for making money',
}

update_lang_file("lang/id/landing.php", id_landing)
update_lang_file("lang/en/landing.php", en_landing)

def replace_in_file(path, replacements):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()
    for old, new in replacements:
        content = content.replace(old, new)
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

reps = [
    (">RATE PER JAM<", ">{{ __('landing.calc_rate') }}<"),
    (">Dikalikan jam kerja harian yang laporan videonya lolos QC.<", ">{{ __('landing.calc_rate_desc') }}<"),
    (">Pilih simulasi kerja<", ">{{ __('landing.calc_choose') }}<"),
    (">ESTIMASI TOTAL BULANAN<", ">{{ __('landing.calc_est_total') }}<"),
    (">Dengan 2 jam/hari dan rate dasar Rp60.000/jam.<", ">{{ str_replace('{hours}', '2', __('landing.calc_est_desc')) }}<"),
    (">POTENSI MINGGUAN<", ">{{ __('landing.calc_potensi') }}<"),
    (">MULAI DAFTAR SEKARANG<", ">{{ __('landing.calc_btn') }}<"),
    (">Simulasi bukan jaminan pendapatan. Nominal mengikuti laporan approved dan ketentuan operasional.<", ">{{ __('landing.calc_note') }}<"),
    (">DASAR HITUNG<", ">{{ __('landing.calc_base') }}<"),
    (">Jam approved<", ">{{ __('landing.calc_base_val') }}<"),
    (">VERIFIKASI<", ">{{ __('landing.calc_verif') }}<"),
    (">Lewat QC<", ">{{ __('landing.calc_verif_val') }}<"),
    
    (">Laporan rapi, pembayaran jadi lebih tenang<", ">{{ __('landing.testi_1') }}<"),
    (">Awalnya ragu, setelah rutin submit mulai terasa hasilnya<", ">{{ __('landing.testi_2') }}<"),
    (">QC Jelas, jadi tahu laporan mana yang perlu diperbaiki<", ">{{ __('landing.testi_3') }}<"),
    (">Modal HP dan waktu luang, bisa mulai dari rumah<", ">{{ __('landing.testi_4') }}<"),
    
    (">Sama sekali tidak. Pendaftaran Mitra Kontributor di KAMERAKITA AI 100% GRATIS tanpa modal awal apa pun.<", ">{{ __('landing.faq_1_ans') }}<"),
    (">Tidak perlu. Anda bisa menggunakan smartphone yang sudah Anda miliki untuk mulai merekam.<", ">{{ __('landing.faq_2_ans') }}<"),
    (">Pencairan komisi diproses manual oleh admin sesuai jadwal operasional berdasarkan rekap durasi yang sudah approved.<", ">{{ __('landing.faq_3_ans') }}<"),
    (">Tentu. Anda akan dibekali dokumen standar operasional (SOP) dan arahan aktivitas yang jelas untuk setiap tugas.<", ">{{ __('landing.faq_4_ans') }}<"),
    (">Jika rekaman ditolak (rejected), maka durasi tersebut tidak akan dihitung. Anda bisa mengulang sesuai revisi yang diberikan QC.<", ">{{ __('landing.faq_5_ans') }}<"),
    
    (">Platform Rekam Video &amp; Datasets Terpercaya No. 1 di Indonesia untuk Mitra &amp; Enterprise Computer Vision.<", ">{{ __('landing.footer_desc') }}<"),
    ("Platform Rekam Video & Datasets Terpercaya No. 1 di Indonesia untuk Mitra & Enterprise Computer Vision.", "{{ __('landing.footer_desc') }}"),
    (">LAYANAN MITRA<", ">{{ __('landing.footer_services') }}<"),
    (">AKSES AKUN<", ">{{ __('landing.footer_acc') }}<"),
    (">PERUSAHAAN<", ">{{ __('landing.footer_corp') }}<"),
    (">Domain Resmi: kamerakitaid.site<", ">{{ __('landing.footer_domain') }}<"),
    (">PT KAMERAKITA AI Indonesia<", ">{{ __('landing.footer_pt') }}<"),
    
    ("title: \"Santai\", desc: \"Mulai pelan, tetap produktif\"", "title: \"{{ __('landing.js_santai_title') }}\", desc: \"{{ __('landing.js_santai_desc') }}\""),
    ("title: \"Fokus\", desc: \"Lebih rutin, hasil lebih terasa\"", "title: \"{{ __('landing.js_fokus_title') }}\", desc: \"{{ __('landing.js_fokus_desc') }}\""),
    ("title: \"Gacor Ketua!!!\", desc: \"Mode serius cari cuan\"", "title: \"{{ __('landing.js_gacor_title') }}\", desc: \"{{ __('landing.js_gacor_desc') }}\""),
    ("`<span>ESTIMASI TOTAL BULANAN</span><strong>${formatRp(monthly)}</strong><p>Dengan ${mode.hours} jam/hari dan rate dasar Rp60.000/jam.</p>`", "`<span>{{ __('landing.calc_est_total') }}</span><strong>${formatRp(monthly)}</strong><p>{{ __('landing.calc_est_desc') }}</p>`.replace('{hours}', mode.hours)"),
]
replace_in_file("resources/views/welcome.blade.php", reps)
print("Done")
