# Original User Request

## 2026-08-10T11:29:15Z

<USER_REQUEST>
# Teamwork Project Prompt — Draft

> Status: Launched
> Goal: Craft prompt → get user approval → delegate to teamwork_preview

Lakukan Code Review secara menyeluruh terhadap fitur Mailbox (struktur kode, fungsi, UI/UX, dan penanganan variabel) untuk memberikan rekomendasi perbaikannya. Fokus pada Standar Pengkodean (Best Practices), Performa (Backend/DB), dan UI/UX Frontend (AlpineJS). Hasil akhir berupa dokumen laporan markdown, tim agen dilarang mengubah kode sumber.

Working directory: c:\laragon\www\kamerakita.id-main
Integrity mode: benchmark

## Requirements

### R1. Analisis Standar Pengkodean (Best Practices)
Identifikasi area di mana struktur kode, rute, atau fungsi dapat ditingkatkan berdasarkan standar pengembangan yang baik dan kokoh.

### R2. Analisis Performa & Efisiensi Backend
Evaluasi performa kueri database (Eloquent), logika penyimpanan (ProcessCatchAllEmailService), dan pengelolaan memori pada proses latar belakang (cron jobs).

### R3. Analisis UI/UX & Logika Frontend
Tinjau kembali keterbacaan, kebersihan, dan potensi masalah *state management* pada logika antarmuka pengguna berbasis AlpineJS dan desain berbasis TailwindCSS.

### R4. Dokumentasi Laporan Code Review
Hasilkan file `mailbox_audit_report.md` di dalam *working directory* yang mendokumentasikan semua area peningkatan yang ditemukan dan solusi implementasi (cuplikan kode).

## Acceptance Criteria

### Audit Pengkodean
- [ ] Laporan mengidentifikasi setidaknya satu area peningkatan kode atau memberikan verifikasi bahwa fitur telah ditulis mengikuti praktik terbaik.

### Audit Performa
- [ ] Laporan mencakup setidaknya satu potensi masalah performa kueri basis data (seperti N+1 query problem, kurangnya indeksasi) atau efisiensi pemrosesan layanan IMAP.

### Dokumen Akhir
- [ ] Dokumen laporan (`mailbox_audit_report.md`) dibuat di akar proyek.
- [ ] Setiap masalah yang ditemukan memiliki tingkat keparahan (*Low, Medium, High, Critical*).
- [ ] Setiap masalah yang ditemukan disertai dengan cuplikan kode perbaikan spesifik.
- [ ] Agen tidak memodifikasi file *source code* manapun, murni hanya menghasilkan dokumen laporan.
</USER_REQUEST>
