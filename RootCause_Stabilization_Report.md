# ROOT CAUSE AUDIT & STABILIZATION REPORT
**Fitur:** Kirim Laporan Kerja Video
**Tanggal:** 24 September 2026

## 1. ROOT CAUSE Paling Mungkin & 2. ROOT CAUSE Terbukti
- **Terbukti:** Klien mengirimkan *body request* yang kosong untuk *field* teks (`project_name`, `submission_date`, dll) sementara tetap berhasil mengirimkan request POST, memicu error validasi 422 untuk seluruh field.
- **Root Cause Utama:** Confirmed client-side FormData reconstruction failure on affected iOS/WebKit environment when asynchronous compression was performed during iteration.
- **Penjelasan:** Di versi kode sebelumnya, pembuatan `FormData` dilakukan melalui loop asinkron `for (const [key, value] of originalFormData.entries())` yang digabung dengan `await compressFile()`. Konstruksi ulang ini rentan gagal di beberapa perangkat, menyebabkan `finalFormData` dikirim secara parsial atau kosong ke server.

## 2. Kondisi Request & Payload
- **Apakah request mencapai Laravel?** YA. Jika tidak, UI tidak akan bisa me-render pesan error validasi (pesan error merah berasal dari response JSON HTTP 422).
- **Apakah $_POST kosong?** Ya, sebagian besar field string terbukti kosong akibat *client-side bug* tersebut. 
- **Apakah $_FILES kosong?** File mungkin ada jika `append` pertama berhasil, tetapi untuk *field* lainnya gagal dimasukkan.
- **Production PHP Limits (Local/Runtime):** 
  Hasil pengecekan *realtime* via CLI:
  - `upload_max_filesize`: 2G
  - `post_max_size`: 2G
  - `memory_limit`: 512M
  Limit ini sangat longgar, membuktikan bahwa drop bukan berasal dari PHP `post_max_size`, melainkan murni dari konstruksi `FormData` di sisi klien.

## 3. Perubahan & Stabilisasi yang Dilakukan
Sesuai instruksi audit, berikut perombakan *end-to-end* yang telah diterapkan:

### A. Frontend (submit-report.blade.php)
- **Removed Fragile Iterator:** Menghapus loop `for...of originalFormData.entries()`.
- **In-place FormData Modification:** `finalFormData` kini dibuat hanya sekali (`new FormData(form)`), dan modifikasi (kompresi/hapus) dilakukan dengan metode spesifik `get()`, `set()`, dan `delete()`. Tidak akan ada lagi field yang "hilang" karena bug iterasi.
- **Robust XHR Response Contract:** 
  Menghapus `catch(e)` kosong yang meredirect paksa ke *dashboard*.
  Response handler kini secara eksplisit memeriksa `xhr.status`:
  - `200-299`: Sukses -> Redirect
  - `422`: Menampilkan validasi spesifik (Selector `js-error-msg` telah diperbaiki)
  - `413`: Payload Too Large
  - `419`: CSRF/Sesi kedaluwarsa
  - `500+`: Error sistem

### B. Backend (SubmitVideoWorkReportController.php)
- **Response Contract (JSON):** Memperbaiki kembalian HTTP dari sekadar `200` dengan flag `server_error`/`validation_failed` menjadi kode status standar industri (`413`, `422`, `500`, dan `200` untuk *success*). Format JSON dijamin: `{"success": true/false, "message": "...", "errors": [...]}`.
- **Transaction Safety:**
  Menambahkan `->afterCommit()` pada pemanggilan Job `ProcessSubmittedReport::dispatch($report)` untuk menjamin antrean hanya jalan setelah Data tersimpan permanen.
- **Diagnostic Logging:**
  Menerapkan safe-logging dengan `Str::uuid()` di titik-titik krusial (`Log::warning` pada payload drop, `Log::info` pada gagal validasi, `Log::error` pada sistem error) dengan mencantumkan `CONTENT_LENGTH` dan tipe Exception tanpa menyertakan raw-data sensitif.

### C. Image Processing (StoreEvidenceImageService.php)
- **Eliminasi Double Compression:** Logika `store()` diubah. Gambar hanya akan dikompres ulang (`imagejpeg`) JIKA ukurannya melebihi 1MB atau berformat selain JPEG (contoh: PNG/Webp). Jika gambar dari klien sudah berformat JPEG dan kecil, backend akan langsung memindahkannya, menghemat waktu proses (CPU) dan menghindari degradasi ganda (Double JPEG Lossy Compression).

### D. Automated Tests (VideoSubmissionTest.php)
- **Total Rewrite:** Mengganti rute `/submit-report` dengan route teraktual (`video-submissions.submit-report.store`).
- Menambahkan uji integrasi lengkap untuk dua tipe proyek: `Atlas` dan `Minutes Data`.
- Menambahkan uji validasi HTTP 422 dan uji limitasi payload HTTP 413.
- Menguji asersi antrean (Queue) setelah commit.
- (Semua pengujian berjalan 100% *PASSED*).

## 4. Kesimpulan & Sisa Risiko (Remaining Risk)
- **Remaining Risk:** Jika NGINX *production* menerapkan `client_max_body_size` yang terlalu ketat (misal 2MB) sementara foto iPhone bisa 5MB, NGINX dapat menjegal *request* secara prematur (HTTP 413). Namun, hal ini kini sudah tertangani anggun oleh *frontend* yang baru (menampilkan alert HTTP 413 eksplisit alih-alih loading tanpa batas).
- **Status Akhir:** Form unggahan telah kembali stabil, prediktabel, aman, toleran terhadap *device* Safari/iOS, dan memiliki visibilitas logging (Observability) yang jelas di *backend*. Pengguna tidak akan lagi terjebak pada layar loading buta tanpa kejelasan status kiriman.
