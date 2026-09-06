<?php

$id = require 'lang/id/landing.php';

$id['features']['title2'] = 'Kerja simpel, hasil maksimal!';
$id['features']['subtitle2'] = 'Kamu cukup rekam aktivitas harian dari rumah. Laporan yang lolos approved akan masuk rekap pendapatan secara transparan.';
$id['features']['item_1_title'] = 'Bantu AI Biar Pintar';
$id['features']['item_1_desc'] = 'KameraKita AI ngajarin teknologi pintar (AI) biar bisa paham cara manusia beraktivitas di dalam rumah.';
$id['features']['item_2_title'] = 'Rekam Kegiatan Rumah';
$id['features']['item_2_desc'] = 'Tugas kamu cuma pakai alat di kepala, lalu rekam aktivitas harian kayak ngepel, nyuci piring, atau beres-beres.';
$id['features']['item_3_title'] = 'Kirim Video & Terima Cuan';
$id['features']['item_3_desc'] = 'Kerjaan rumah beres, dompet tetep tebel. Gak perlu keahlian khusus, semua orang semua kalangan pasti bisa!';

$id['calculator']['title2'] = 'Pilih ritme kerja yang paling cocok';
$id['calculator']['subtitle2'] = 'Rate dasar Rp60.000 per jam rekaman bersih. Simulasi ini membantu kamu membayangkan potensi cuan mingguan sebelum mulai.';

$id['steps']['title2'] = 'Cuma 3 langkah buat mulai dapet cuan';
$id['steps']['subtitle2'] = 'Dari daftar sampai pembayaran, semuanya dibuat simpel dan bakal dipandu tim KameraKita.';
$id['steps']['step_1_title'] = 'Gabung & Ikuti Briefing';
$id['steps']['step_1_desc'] = 'Daftar lewat WhatsApp, lalu tim kami bakal jelasin cara kerja, tugas, dan kebutuhan alat.';
$id['steps']['step_2_title'] = 'Rekam Aktivitasmu';
$id['steps']['step_2_desc'] = 'Pilih tugas yang tersedia, pasang HP sesuai panduan, lalu rekam aktivitas sehari-hari seperti biasa.';
$id['steps']['step_3_title'] = 'Upload & Terima Bayaran';
$id['steps']['step_3_desc'] = 'Kirim hasil rekaman untuk dicek. Setelah lolos QC, durasi approved masuk ke pembayaran bulanan.';

$id['testimonials']['title2'] = 'Cerita kontributor yang mulai punya<br>penghasilan tambahan';
$id['testimonials']['subtitle2'] = 'Beberapa pengalaman yang menggambarkan bagaimana alur kerja, QC, dan pembayaran dijalankan dengan lebih rapi.';

$id['faq']['title'] = 'Pertanyaan yang Sering Diajukan';
$id['faq']['q1_q'] = 'Apakah pendaftaran mitra dipungut biaya?';
$id['faq']['q1_a'] = '100% GRATIS. Kami tidak pernah memungut biaya apapun dari kontributor. Segala bentuk pungutan mengatasnamakan KameraKita adalah penipuan.';
$id['faq']['q3_q2'] = 'Kapan komisi hasil rekap durasi akan dicairkan?';
$id['faq']['q3_a2'] = 'Pencairan komisi diproses manual oleh admin sesuai jadwal operasional berdasarkan rekap durasi yang sudah approved.';

$out = "<?php\n\nreturn " . var_export($id, true) . ";\n";
file_put_contents('lang/id/landing.php', $out);
