# Konteks Proyek: KameraKita.id

Dokumen ini berisi rangkuman arsitektur, struktur database, peran pengguna (roles), dan fitur utama aplikasi **KameraKita.id** untuk mempermudah pemahaman konteks (terutama bagi AI Agent) sebelum melakukan perubahan kode.

## 1. Tech Stack & Environment
- **Framework:** Laravel (PHP)
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Database:** MySQL / MariaDB (via Eloquent ORM)
- **Server Environment:** Laragon (Local: Windows), Hostinger/cPanel (Production: Linux)
- **Version Control:** Git

## 2. Struktur Role (Peran Pengguna)
Aplikasi ini memiliki beberapa peran (*role*) yang diatur melalui tabel `users` (kolom `role`) dan tabel `partners` (kolom `partner_role`):
1. **Superadmin / Admin:** Mengelola sistem keseluruhan, melakukan QC (Quality Control) video, mengatur periode gajian, validasi partner. Dashboard di `resources/views/dashboard/admin.blade.php`.
2. **Rekruter:** Bertugas merekrut *worker* baru melalui kode referral (Activation Code) dan mendapatkan komisi (`RecruiterCommission`). Dashboard di `resources/views/dashboard/rekruter.blade.php`.
3. **Mitra:** Agen atau level di atas *worker* biasa yang membawahi beberapa *worker*. Dashboard di `resources/views/dashboard/mitra.blade.php`.
4. **Worker (Kontributor):** Pekerja utama yang tugasnya merekam dan mengirimkan laporan video. Dashboard di `resources/views/dashboard/worker.blade.php`.

## 3. Struktur Database (Model Utama)

Berikut adalah beberapa entitas penting dalam sistem:

- **`User`** (`app/Models/User.php`)
  Menyimpan otentikasi dasar (email, password, role). Baru saja ditambahkan dukungan avatar via kolom `avatar`.
  
- **`Partner`** (`app/Models/Partner.php`)
  Tabel ekstensi profil untuk pekerja/mitra/rekruter. Berisi detail personal (NIK, no rekening, tipe smartphone), status aktif, tanggal registrasi (`registration_date`), referensi kode, serta status keanggotaan prioritas (`is_vip`).

- **`VideoWorkReport`** (`app/Models/VideoWorkReport.php`)
  Laporan pekerjaan video yang diunggah oleh *worker*. Memiliki status QC (`qc_status`: 'approved', 'rejected', 'on_review') dan status pembayaran (`payment_status`: 'unpaid', 'paid'). Menyimpan bukti foto/screenshot yang diunggah.

- **`PeriodApproval`** (`app/Models/PeriodApproval.php`)
  Manajemen siklus gaji periodik (Siklus Rabu - Selasa). Digunakan Admin untuk menutup buku mingguan dan menetapkan status *Paid*.

- **`ClientInvoice`** (`app/Models/ClientInvoice.php`)
  Invoice yang diterbitkan untuk klien.

- **`CapturedEmail`** (`app/Models/CapturedEmail.php`)
  Pesan atau pengumuman yang masuk ke akun pengguna (Fitur Mailbox).

- **`ActivationCode`** (`app/Models/ActivationCode.php`)
  Kode unik yang digenerate oleh sistem/rekruter untuk onboarding *worker* baru.

- **`ActivityLog`** (`app/Models/ActivityLog.php`)
  Mencatat jejak aktivitas Admin, Finance, dan pengguna penting lainnya.

- **`RecruiterCommission`** (`app/Models/RecruiterCommission.php`)
  Tabel yang mencatat besaran komisi bagi seorang rekruter berdasarkan performa rekrutan mereka.

- **`FastworkOnboarding`** (`app/Models/FastworkOnboarding.php`)
  Untuk proses *onboarding* dari platform Fastwork.

## 4. Fitur Utama & Alur Kerja (Workflow)

1. **Dashboard Khusus Peran:**
   - Sistem akan *redirect* login sesuai peran (`fallback.blade.php`, `admin.blade.php`, `worker.blade.php`, dll).
   - Pengguna *worker* memiliki UI navigasi bawah di *mobile* (`components/mobile-bottom-nav.blade.php`) dan *sidebar* di desktop (`components/sidebar.blade.php`).

2. **Kirim & Validasi Laporan (Submit Report & QC):**
   - *Worker* mengunggah link video dan screenshot bukti (menggunakan form `submit-report`).
   - Admin mengecek di `qc-room-page.blade.php` (ruang QC) dan mengubah status (*Approve*/*Reject*).
   - Laporan yang disetujui akan masuk tagihan untuk dibayarkan.

3. **Mailbox (Kotak Masuk):**
   - Diatur oleh `MailboxController`. 
   - Notifikasi dan pesan dari platform akan muncul di kotak masuk pengguna. Ada *Call-To-Action* (CTA) khusus di bagian bawah halaman Ringkasan/Dashboard pengguna.

4. **Sistem VIP:**
   - Admin dapat mengubah *partner* menjadi **VIP Member**.
   - VIP Member akan mendapatkan *border* bingkai khusus di profil, serta ikon khusus berwana gelap / logo mahkota (*crown*) di panel navigasi (desktop & mobile).

## 5. UI/UX Guidelines
- **CSS:** Tailwind CSS dengan kustomisasi warna khusus seperti `indigo`, `blue`, dan latar `slate/gray`.
- **Interaktivitas:** Sebagian besar menggunakan Alpine.js (`x-data`, `x-show`, dll) dipadukan dengan Blade Components.
- **Responsivitas:** Tampilan sangat membedakan layar Desktop (Sidebar) dan layar Mobile (Bottom Navigation Nav-bar).
- **Icons:** SVG Inline sering digunakan.

## Catatan Khusus
- **File Upload:** Upload file disimpan ke `storage/app/public/...` dan butuh `php artisan storage:link` agar dapat diakses dari browser melalui folder `public/storage/`.
- **Konvensi Tanggal:** Kolom tanggal (*date*) seperti `registration_date` sudah di-*cast* otomatis oleh Laravel Eloquent.

---
*Dokumen ini dapat dibaca oleh AI untuk memandu modifikasi kode secara lebih aman tanpa merusak struktur dan logika yang sudah ada.*
