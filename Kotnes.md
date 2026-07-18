# Kotnes.md - Konteks Lengkap Project Kamerakita.ai

Dokumen ini dibuat sebagai peta konteks untuk manusia dan AI yang ingin memahami project ini dengan cepat tetapi tetap detail. Isinya menjelaskan struktur, format, logic, fitur, database, variable penting, dan catatan teknis yang perlu diperhatikan sebelum mengubah kode.

> Catatan keamanan: file `.env` lokal tidak ditulis nilai rahasianya di dokumen ini. Yang dijelaskan hanya nama variable dan fungsinya.

## 1. Ringkasan Project

Project ini adalah aplikasi web berbasis Laravel untuk mengelola operasional agensi Kamerakita.ai. Domain utamanya adalah manajemen mitra/worker perekam video, pengiriman laporan kerja video berbasis bukti gambar, proses QC/verifikasi laporan, dashboard metrik pendapatan, dan export payroll CSV.

Fitur inti:

- Autentikasi user memakai stack Laravel Breeze.
- Role user: `superadmin`, `admin`, `verifikator`, `finance`.
- Profil operasional partner: `worker` dan `mitra`.
- Worker dapat submit laporan kerja video harian dengan dua bukti gambar.
- Worker dan mitra punya halaman khusus riwayat laporan.
- QC room menampilkan laporan pending dan memungkinkan approve penuh, approve sebagian, atau reject.
- Dashboard berbeda tergantung user yang login dan apakah user terhubung ke data partner.
- Superadmin/admin dapat mengelola akun admin internal.
- Superadmin/admin/finance dapat export CSV payroll untuk laporan approved yang belum dibayar.
- Sistem menyimpan invoice klien di tabel `client_invoices`.

## 2. Teknologi dan Dependency

Backend:

- PHP `^8.3`.
- Laravel Framework `^13.8` berdasarkan `composer.json`.
- Laravel Tinker.
- Laravel Breeze sebagai basis auth scaffolding.
- PHPUnit `^12.5.12` untuk test.
- Faker untuk factory/seeder.
- Laravel Pint untuk format kode.

Frontend:

- Vite.
- Tailwind CSS.
- Alpine.js.
- Plugin Tailwind Forms.

Catatan versi:

- `composer.json` menyatakan Laravel `^13.8`.
- `README.md` dan salah satu teks dashboard masih menyebut Laravel 11.x. Jadi dokumentasi lama kemungkinan belum diperbarui.

## 3. Struktur Folder Penting

Root project:

- `app/`: kode aplikasi Laravel, model, controller, middleware, service, action.
- `bootstrap/`: bootstrap Laravel 13 style, termasuk alias middleware di `bootstrap/app.php`.
- `config/`: konfigurasi Laravel.
- `database/`: migration, factory, seeder.
- `public/`: entry point web (`index.php`) dan asset publik.
- `resources/views/`: Blade view.
- `resources/css/app.css`: entry Tailwind.
- `resources/js/app.js`: entry JS, mengaktifkan Alpine.
- `routes/web.php`: route utama aplikasi.
- `routes/auth.php`: route auth Breeze.
- `tests/`: test bawaan Breeze/Laravel.
- `.env`: konfigurasi lokal aktif, jangan commit nilai rahasia.
- `.env.example`: contoh konfigurasi environment.

File build/config utama:

- `composer.json`: dependency PHP dan script `setup`, `dev`, `test`.
- `package.json`: dependency frontend dan script Vite.
- `vite.config.js`: input Vite adalah `resources/css/app.css` dan `resources/js/app.js`.
- `tailwind.config.js`: scan Blade di `resources/views/**/*.blade.php`.

## 4. Entry Point dan Bootstrap

### `public/index.php`

Entry point HTTP Laravel standar.

### `bootstrap/app.php`

Mengatur routing:

- Web route: `routes/web.php`.
- Console route: `routes/console.php`.
- Health endpoint: `/up`.

Mengatur alias middleware:

- `role` diarahkan ke `App\Http\Middleware\RoleMiddleware`.

Middleware role sudah terdaftar dan dipakai untuk route sensitif seperti `partners`, `admin-users`, `qc-room`, dan `payroll`. Route dashboard tetap memakai pembacaan role/partner di controller karena dashboard berbeda berdasarkan konteks user.

## 5. Route Aplikasi

### `routes/web.php`

Route utama:

- `GET /`
  - Redirect ke route `dashboard`.

- `GET /dashboard`
  - Controller: `RenderDashboardOverviewController`
  - Middleware: `auth`, `verified`
  - Name: `dashboard`

Group middleware `auth`, `verified`:

- Resource `partners`
  - Controller: `ManagePartnerDemographicsController`
  - Fitur CRUD data mitra/worker.
  - Middleware: `role:superadmin,admin`.

- Resource `admin-users`
  - Controller: `ManageAdminUsersController`
  - Fitur CRUD akun internal non-superadmin.
  - Parameter route: `adminUser`.
  - Tidak memakai method `show`.
  - Middleware: `role:superadmin,admin`.

- `GET /submit-report`
  - Controller: `SubmitVideoWorkReportController@create`
  - Name: `video-submissions.submit-report.create`

- `POST /submit-report`
  - Controller: `SubmitVideoWorkReportController@store`
  - Name: `video-submissions.submit-report.store`

- `GET /report-history`
  - Controller: `ListPartnerReportHistoryController`
  - Name: `video-submissions.report-history`
  - Dipakai worker dan mitra untuk melihat riwayat laporan masing-masing.

- `GET /qc-room`
  - Controller: `VerifyVideoWorkReportController@index`
  - Name: `video-submissions.qc-room`
  - Middleware: `role:superadmin,admin,finance`

- `POST /qc-room/{report}/verify`
  - Controller: `VerifyVideoWorkReportController@verify`
  - Name: `video-submissions.verify`
  - Middleware: `role:superadmin,admin,finance`

- `GET /video-work-reports/{report}/evidence/{type}`
  - Controller: `ShowVideoWorkReportEvidenceController`
  - Name: `video-submissions.evidence.show`
  - Middleware: `role:superadmin,admin,finance,worker,mitra`
  - Dipakai untuk membuka gambar evidence lewat controller backend, bukan public storage langsung.

- `GET /payroll/export-csv`
  - Controller: `ExportPayrollDataController@exportCsv`
  - Name: `payroll.export-csv`
  - Middleware: `role:superadmin,admin,finance`

- `POST /payroll/mark-as-paid`
  - Controller: `ExportPayrollDataController@markAsPaid`
  - Name: `payroll.mark-as-paid`
  - Middleware: `role:superadmin,admin,finance`

Group middleware `auth`:

- `GET /profile`
- `PATCH /profile`
- `DELETE /profile`

### `routes/auth.php`

Route auth bawaan Breeze:

- Register.
- Login.
- Forgot password.
- Reset password.
- Email verification.
- Confirm password.
- Update password.
- Logout.

Throttle:

- Register dibatasi `5` request per menit per IP.
- Login dibatasi `10` request per menit per IP.
- Email verification signed route dibatasi `6` per menit.

## 6. Role dan Tipe Partner

Ada dua konsep berbeda yang mudah tertukar:

### Role user (`users.role`)

Kolom di tabel `users`, default `verifikator`.

Nilai yang digunakan:

- `superadmin`: akses dashboard global, kelola partner, kelola admin, QC, payroll.
- `admin`: akses penuh mirip superadmin untuk operasional, tetapi bukan superadmin utama.
- `finance`: akses dashboard global dan payroll/QC link di sidebar.
- `verifikator`: role default, dapat login. Jika user tidak ditautkan ke partner dan bukan admin/finance, masuk ke fallback dashboard.

### Partner role (`partners.partner_role`)

Kolom di tabel `partners`.

Nilai:

- `worker`: perekam video, dapat submit laporan.
- `mitra`: koordinator yang memiliki worker di bawahnya.

Hubungan user-partner:

- `partners.user_id` menghubungkan akun login dengan profil partner.
- Dashboard pertama-tama mencari `Partner::where('user_id', Auth::id())`.
- Jika partner ditemukan dan `partner_role` adalah `worker`, tampil dashboard worker.
- Jika partner ditemukan dan `partner_role` adalah `mitra`, tampil dashboard mitra.
- Jika tidak ada partner dan user role `superadmin`, `admin`, atau `finance`, tampil dashboard admin/global.
- Selain itu tampil fallback dashboard.

## 7. Database dan Migration

### Tabel `users`

Dibuat oleh `0001_01_01_000000_create_users_table.php`.

Kolom:

- `id`: primary key integer.
- `name`: nama user.
- `email`: email unik.
- `email_verified_at`: timestamp nullable.
- `password`: password hash.
- `remember_token`: token remember me.
- `role`: string, default `verifikator`.
- `created_at`, `updated_at`.

Catatan model:

- `App\Models\User` memakai attribute `#[Fillable(['name', 'email', 'password', 'role', 'email_verified_at'])]`.
- Helper akses penting:
  - `hasFullAdminAccess()`: true untuk `superadmin` dan `admin`.
  - `canAccessQcRoom()`: true untuk `superadmin`, `admin`, dan `finance`.
  - `canAccessPayroll()`: true untuk `superadmin`, `admin`, dan `finance`.

### Tabel `partners`

Dibuat oleh `2026_07_17_060448_create_partners_table.php`, lalu ditambah/diubah migration berikutnya.

Kolom akhir yang digunakan:

- `id`: UUID primary key.
- `partner_role`: enum `worker` atau `mitra`.
- `mitra_parent_id`: UUID nullable, self-reference ke `partners.id`.
- `mitra_id`: string unik, format seperti `KMK-001`.
- `nik`: string unik nullable.
- `full_name`: nama lengkap.
- `whatsapp_number`: nomor WhatsApp.
- `email`: email partner nullable.
- `full_address`: alamat lengkap nullable.
- `bank_name`: nama bank nullable.
- `account_number`: field legacy nomor rekening.
- `account_owner_name`: field legacy nama pemilik rekening.
- `bank_account_number`: field baru nomor rekening.
- `bank_account_owner`: field baru nama pemilik rekening.
- `smartphone_type`: tipe perangkat nullable.
- `status`: enum `active` atau `suspended`, default `active`.
- `base_hourly_rate`: integer default `54000` di migration awal.
- `user_id`: foreign key ke `users.id`, nullable, cascade on delete.
- `created_at`, `updated_at`.

Catatan penting:

- Migration awal membuat `contributor_id`, lalu migration `2026_07_17_065051...` mengganti nama menjadi `mitra_parent_id`.
- Model `Partner` memakai UUID via `HasUuids`.
- Field rekening ada dua versi: legacy `account_number`/`account_owner_name` dan baru `bank_account_number`/`bank_account_owner`.
- Controller create/update menyalin field baru ke field legacy agar export payroll lama tetap jalan.

### Tabel `video_work_reports`

Dibuat oleh `2026_07_17_060449_create_video_work_reports_table.php`.

Kolom:

- `id`: UUID primary key.
- `partner_id`: foreign UUID ke `partners.id`, cascade on delete.
- `submission_date`: tanggal kerja.
- `evidence_email_image_path`: path bukti gambar email.
- `evidence_app_quality_image_path`: path bukti kualitas aplikasi.
- `submitted_duration_minutes`: durasi yang dikirim worker.
- `approved_duration_minutes`: durasi yang disetujui QC, default `0`.
- `qc_status`: enum `pending`, `approved`, `rejected`, default `pending`.
- `payment_status`: enum `unpaid`, `paid`, default `unpaid`.
- `verifier_notes`: catatan verifikator nullable.
- `verified_by`: user verifikator nullable, set null on delete.
- `verified_at`: waktu verifikasi nullable.
- `created_at`, `updated_at`.

### Tabel `client_invoices`

Dibuat oleh `2026_07_17_063028_create_client_invoices_table.php`.

Kolom:

- `id`: UUID primary key.
- `invoice_month`: string seperti `Juni 2026`.
- `total_minutes_billed`: total menit ditagihkan ke klien.
- `total_amount_usd`: nominal tagihan USD decimal 10,2.
- `status`: string default `unpaid_by_client`, nilai yang dipakai: `unpaid_by_client`, `paid_by_client`.
- `created_at`, `updated_at`.

## 8. Model dan Relasi

### `App\Models\User`

Fungsi:

- Model authenticatable untuk login.
- Cast:
  - `email_verified_at` sebagai datetime.
  - `password` sebagai hashed.
- Hidden:
  - `password`
  - `remember_token`

Catatan:

- Tidak ada relasi eksplisit ke `Partner`, walaupun `partners.user_id` mengarah ke users.

### `App\Models\Partner`

Trait:

- `HasUuids`
- `HasFactory`

Fillable:

- `partner_role`
- `mitra_parent_id`
- `mitra_id`
- `nik`
- `full_name`
- `whatsapp_number`
- `email`
- `full_address`
- `bank_name`
- `bank_account_number`
- `bank_account_owner`
- `account_number`
- `account_owner_name`
- `smartphone_type`
- `status`
- `base_hourly_rate`
- `user_id`

Relasi:

- `user()`: belongsTo `User`.
- `mitraParent()`: belongsTo `Partner` lewat `mitra_parent_id`.
- `workers()`: hasMany `Partner` lewat `mitra_parent_id`.
- `videoWorkReports()`: hasMany `VideoWorkReport`.

### `App\Models\VideoWorkReport`

Trait:

- `HasUuids`
- `HasFactory`

Fillable:

- `partner_id`
- `submission_date`
- `evidence_email_image_path`
- `evidence_app_quality_image_path`
- `submitted_duration_minutes`
- `approved_duration_minutes`
- `qc_status`
- `payment_status`
- `verifier_notes`
- `verified_by`
- `verified_at`

Cast:

- `submission_date` sebagai date.
- `verified_at` sebagai datetime.

Relasi:

- `partner()`: belongsTo `Partner`.
- `verifier()`: belongsTo `User` lewat `verified_by`.

Accessor/helper:

- `formatMinutes(int $minutes)`: format `Hh Mm` atau `Mm`.
- `submitted_duration_formatted`.
- `approved_duration_formatted`.

### `App\Models\ClientInvoice`

Trait:

- `HasUuids`

Fillable:

- `invoice_month`
- `total_minutes_billed`
- `total_amount_usd`
- `status`

## 9. Controller dan Logic Bisnis

### `RenderDashboardOverviewController`

Tujuan:

- Menentukan dashboard yang tampil berdasarkan user login dan partner yang terhubung.

Alur:

1. Ambil `Auth::user()`.
2. Cari `Partner` berdasarkan `user_id`.
3. Jika partner adalah `worker`:
   - Ambil metrics worker dari `CalculatePartnerMetricsService::getWorkerMetrics`.
   - Ambil 10 report terakhir milik worker.
   - Render `dashboard.worker`.
4. Jika partner adalah `mitra`:
   - Ambil metrics mitra dari `CalculatePartnerMetricsService::getMitraMetrics`.
   - Render `dashboard.mitra`.
5. Jika user role `superadmin`, `admin`, atau `finance`:
   - Ambil global metrics.
   - Ambil 10 report terbaru global.
   - Ambil semua client invoices.
   - Render `dashboard.admin`.
6. Selain itu render `dashboard.fallback`.

### `ManagePartnerDemographicsController`

Tujuan:

- CRUD data partner/mitra/worker.

Method:

- `index(Request $request)`
  - Filter `search`, `role`, `status`.
  - Search mencocokkan `full_name`, `mitra_id`, `whatsapp_number`.
  - Eager load `mitraParent`.
  - Order by `mitra_id` ascending.
  - Paginate 15.

- `create()`
  - Ambil daftar partner role `mitra` untuk dropdown atasan.
  - Generate `mitra_id` berikutnya dari record terakhir, format `KMK-XXX`.

- `store(Request $request)`
  - Validasi field partner.
  - `partner_role` wajib `worker` atau `mitra`.
  - `mitra_id` unik.
  - `nik` unik nullable.
  - `base_hourly_rate` numeric min 0.
  - Menyalin `bank_account_number` ke `account_number`.
  - Menyalin `bank_account_owner` ke `account_owner_name`.
  - Membuat `Partner`.

- `edit(Partner $partner)`
  - Ambil mitra selain partner yang sedang diedit.

- `update(Request $request, Partner $partner)`
  - Validasi mirip store.
  - `nik` unik dengan pengecualian id partner saat ini.
  - Update data partner.

- `destroy(Partner $partner)`
  - Hapus partner.

Catatan:

- Route resource `partners` dilindungi middleware `role:superadmin,admin`.
- Saat membuat partner baru, controller juga membuat akun `users` untuk partner dan menghubungkan `partners.user_id`, sehingga worker/mitra bisa login.
- Email partner dipakai sebagai email login. Password awal ditentukan dari form create.
- Worker/mitra yang sudah punya `user_id` akan masuk dashboard sesuai `partner_role`.

### `ManageAdminUsersController`

Tujuan:

- CRUD akun internal untuk admin operasional.
- Dipakai agar ada akun admin tambahan yang dapat mengakses fitur penuh seperti owner, tetapi bukan `superadmin`.

Pembatasan:

- Route dilindungi middleware `role:superadmin,admin`.
- Akun `superadmin` utama tidak boleh sembarang dibuat lewat halaman ini.
- Email unik di tabel `users`.

### `ListPartnerReportHistoryController`

Tujuan:

- Menampilkan halaman khusus riwayat laporan untuk akun `worker` dan `mitra`.

Alur:

1. Cari `Partner` berdasarkan `Auth::id()` lewat `partners.user_id`.
2. Jika partner bukan `worker` atau `mitra`, request ditolak.
3. Jika partner `worker`, query hanya mengambil `video_work_reports.partner_id = partner.id`.
4. Jika partner `mitra`, query mengambil laporan milik mitra tersebut dan worker direct di bawahnya (`mitra_parent_id = partner.id`).
5. Eager load relasi `partner` dan `verifier`.
6. Hitung summary:
   - total laporan.
   - laporan pending QC.
   - laporan approved.
   - durasi approved yang masih `unpaid`.
7. Filter opsional:
   - `search`: mencocokkan ID laporan, nama partner, `mitra_id`, atau nomor WhatsApp.
   - `qc_status`: `pending`, `approved`, `rejected`.
   - `payment_status`: `unpaid`, `paid`.
8. Order by `submission_date` terbaru lalu `created_at` terbaru.
9. Paginate 20 dan render `video-submissions.report-history`.

### `SubmitVideoWorkReportController`

Tujuan:

- Worker submit laporan video harian dengan bukti gambar.

Method:

- `create()`
  - Cari partner berdasarkan `Auth::id()`.
  - Hanya partner dengan `partner_role === worker` yang boleh akses.
  - Render `video-submissions.submit-report`.

- `store(Request $request)`
  - Validasi hanya worker.
  - Validasi input:
    - `submission_date`: required, date, sebelum/sama dengan hari ini.
    - `submitted_duration_minutes`: required, integer, min 1, max 1440.
    - `evidence_email_image_path`: required image jpeg/png/jpg/gif max 2048 KB.
    - `evidence_app_quality_image_path`: required image jpeg/png/jpg/gif max 2048 KB.
  - Gambar dikompresi dan disimpan via `compressAndStoreImage`.
  - Membuat `VideoWorkReport` dengan:
    - `qc_status = pending`
    - `payment_status = unpaid`
    - `approved_duration_minutes = 0`

Private helper:

- `compressAndStoreImage($file, string $folder)`
  - Simpan file ke disk `evidence`.
  - Root disk `evidence` adalah `storage/app/private/...`, sama pola private storage Laravel/ONFIX.
  - Convert ke JPEG dengan kualitas 75 jika GD berhasil.
  - Fallback ke `$file->store($folder, 'evidence')` jika GD gagal.

Requirement penting:

- PHP GD extension perlu aktif.
- Evidence laporan tidak perlu `php artisan storage:link` karena tidak dilayani dari public storage.
- Akses gambar evidence dilakukan lewat signed route `video-submissions.evidence.show`.

### `ShowVideoWorkReportEvidenceController`

Tujuan:

- Melayani file evidence secara private melalui route backend yang dilindungi login dan role.

Alur:

1. Route menerima `VideoWorkReport $report` dan `type`.
2. `type` hanya boleh:
   - `email`: membaca `evidence_email_image_path`.
   - `app-quality`: membaca `evidence_app_quality_image_path`.
3. Route wajib login dan user wajib punya role yang diizinkan.
4. Controller membaca file dari disk `evidence`.
5. Jika file belum ada di disk `evidence`, controller fallback membaca disk `local`, lalu disk `public` untuk kompatibilitas file lama.
6. Response memakai header `Cache-Control` private/no-store agar browser tidak menyimpan evidence sensitif terlalu lama.

### `VerifyVideoWorkReportController`

Tujuan:

- Menampilkan antrean QC dan menjalankan aksi verifikasi.

Method:

- `index(Request $request)`
  - Search pending report berdasarkan nama partner, `mitra_id`, atau report id.
  - Hanya menampilkan `qc_status = pending`.
  - Order by `submission_date` ascending.
  - Paginate 15.
  - Hitung statistik:
    - total pending.
    - approved hari ini.
    - rejected hari ini.
  - Render `video-submissions.qc-room`.

- `verify(Request $request, VideoWorkReport $report, VerifyVideoWorkReportAction $verifyAction)`
  - Validasi:
    - `action`: `approve_full`, `approve_partial`, atau `reject`.
    - `approved_duration_minutes`: nullable integer min 0 max submitted duration.
    - `verifier_notes`: wajib jika reject.
  - Jalankan action service.
  - Redirect ke QC room dengan message.

Catatan:

- Route QC dilindungi middleware `role:superadmin,admin,finance`.
- Sidebar menampilkan link QC hanya untuk user yang `canAccessQcRoom()`.

### `ExportPayrollDataController`

Tujuan:

- Export data payroll CSV dan menandai laporan sebagai paid.

Method:

- `exportCsv()`
  - Ambil report dengan:
    - `qc_status = approved`
    - `payment_status = unpaid`
  - Group by `partner_id`.
  - Output stream CSV dengan header:
    - `Nomor Rekening`
    - `Nama Pemilik Rekening`
    - `Nama Bank`
    - `ID Mitra`
    - `Mata Uang`
    - `Rate per Jam Rupiah`
    - `Total Menit Kerja`
    - `Total Jam Kerja`
    - `Total Nominal Rupiah`
  - Perhitungan:
    - `totalMinutes = sum(approved_duration_minutes)`.
    - `hours = totalMinutes / 60`.
    - `hourlyRate = partner.base_hourly_rate ?: 54000`.
    - `totalEarnings = round(hours * hourlyRate)`.
    - Mata uang selalu `IDR`.
  - Fallback rekening:
    - nomor rekening: `account_number` atau `0000000000`.
    - pemilik rekening: `account_owner_name` atau `full_name`.
    - bank: `bank_name` atau `BCA`.

- `markAsPaid()`
  - Update semua report approved+unpaid menjadi `payment_status = paid`.

Catatan penting:

- CSV payroll menggunakan `base_hourly_rate`.
- Di migration default awal `base_hourly_rate` adalah `54000` rupiah.
- Form create/edit partner sekarang memakai label Rupiah, sehingga user operasional tidak perlu memahami kurs atau nilai USD.

## 10. Action dan Service

### `VerifyVideoWorkReportAction`

Method `execute(VideoWorkReport $report, array $data, int $verifierId): string`.

Aksi:

- `approve_full`
  - `approved_duration_minutes = submitted_duration_minutes`.
  - `qc_status = approved`.
  - Set `verified_by`.
  - Set `verified_at = now()`.

- `approve_partial`
  - `approved_duration_minutes = data.approved_duration_minutes`.
  - `qc_status = approved`.
  - Set verifier.

- `reject`
  - `approved_duration_minutes = 0`.
  - `qc_status = rejected`.
  - Simpan `verifier_notes`.
  - Set verifier.

### `CalculatePartnerMetricsService`

Tujuan:

- Menghitung metrik dashboard worker, mitra, dan global.

#### `getWorkerMetrics(Partner $worker)`

Input:

- Partner role worker.

Data:

- Ambil semua report approved milik worker.
- Hitung:
  - all-time approved minutes.
  - paid minutes.
  - pending/unpaid minutes.

Rate:

- Worker rate mengambil `partners.base_hourly_rate`.
- Fallback jika kosong: `DEFAULT_WORKER_HOURLY_RATE_IDR = 54000`.

Output penting:

- `all_time_minutes`
- `paid_minutes`
- `pending_minutes`
- `all_time_hours_formatted`
- `paid_hours_formatted`
- `pending_hours_formatted`
- `paid_earnings`
- `pending_earnings`
- `total_earnings`
- `hourly_rate`

#### `getMitraMetrics(Partner $mitra)`

Data:

- Ambil workers yang `mitra_parent_id` sama dengan id mitra.
- Untuk setiap worker, hitung worker metrics.
- Mitra mendapat komisi dari worker:
  - `MITRA_COMMISSION_HOURLY_RATE_IDR = 9000` dari jam approved worker.
- Mitra juga bisa punya laporan sendiri:
  - Mengambil `partners.base_hourly_rate`.
  - Fallback jika kosong: `DEFAULT_MITRA_OWN_HOURLY_RATE_IDR = 63000`.

Output penting:

- `workers_count`
- `workers_data`
- `total_all_time_minutes`
- `total_paid_minutes`
- `total_pending_minutes`
- formatted total hours.
- `personal_paid_earnings`
- `personal_pending_earnings`
- `personal_all_time_hours_formatted`
- `commission_paid_earnings`
- `commission_pending_earnings`
- `commission_hourly_rate`
- `personal_hourly_rate`

#### `getGlobalMetrics()`

Data:

- Total worker.
- Total mitra.
- Semua report approved.
- Paid/unpaid minutes.

Rate:

- Client billed: `CLIENT_BILLING_HOURLY_RATE_IDR = 90000`.
- Agency margin: `AGENCY_MARGIN_HOURLY_RATE_IDR = 27000`.
- Worker dan mitra dihitung memakai rate Rupiah masing-masing seperti di atas.

Output penting:

- `total_workers`
- `total_mitra`
- `global_all_time_minutes`
- `global_paid_minutes`
- `global_pending_minutes`
- formatted hours.
- `client_paid_amount`
- `client_pending_amount`
- `agency_net_margin`
- `client_billing_hourly_rate`
- `agency_margin_hourly_rate`

Catatan:

- Variable `workerShare` dan `mitraShare` dihitung tetapi tidak dikembalikan di output.

### `ProcessPartnerPayrollService`

Tujuan:

- Memproses payroll per partner atau semua partner aktif.

Method:

- `executeForPartner(Partner $partner)`
  - Transaction DB.
  - Ambil report approved+unpaid milik partner.
  - Hitung total approved minutes, hours, hourly rate, total earnings.
  - Update report tersebut menjadi paid.
  - Return ringkasan payroll.

- `executeAll()`
  - Loop semua partner active.
  - Proses hanya partner yang punya report approved+unpaid.

Catatan:

- Service ini belum terlihat dipakai oleh controller saat ini. Controller payroll memakai logic sendiri.

## 11. View dan UI

Layout utama:

- `resources/views/layouts/app.blade.php`
  - Memakai sidebar fixed.
  - Memakai navbar.
  - Load Vite CSS/JS.
  - Font dari Bunny Fonts Plus Jakarta Sans.

Navigation:

- `resources/views/components/sidebar.blade.php`
  - Link dashboard untuk semua user.
  - Link QC room untuk role `superadmin`, `admin`, atau `finance`.
  - Link Kelola Mitra & Worker untuk role `superadmin` atau `admin`.
  - Link Kelola Admin untuk role `superadmin` atau `admin`.
  - Link Kirim Laporan Video jika user terhubung ke partner role `worker`.
  - Link Riwayat Laporan jika user terhubung ke partner role `worker` atau `mitra`.
  - Menampilkan nama dan role user di footer sidebar.
  - Link profile tidak ditampilkan sebagai menu sidebar; akses profile ada dari nama user di footer.

- `resources/views/layouts/navigation.blade.php`
  - Navigation bawaan Breeze, tampaknya tidak menjadi navigation utama karena layout app memakai `<x-sidebar />` dan `<x-navbar />`.

Dashboard:

- `dashboard.worker`
  - Tampilkan estimasi pendapatan worker.
  - Rate tampilan memakai Rupiah dari `metrics['hourly_rate']`, misalnya `Rp50.000/jam`.
  - Tampilkan paid earnings, pending earnings, bank, status kemitraan.
  - Tampilkan 10 laporan terakhir worker.
  - Tombol utama hanya `Submit laporan`.
  - Tidak ada tombol penarikan gaji/withdraw karena gaji dibayarkan manual oleh admin via transfer.
  - Link `Lihat semua` menuju halaman `video-submissions.report-history`.

- `dashboard.mitra`
  - Tampilkan komisi tim worker dalam Rupiah.
  - Tampilkan pendapatan pribadi mitra dalam Rupiah.
  - Tampilkan daftar worker direct dan metrik mereka.
  - Shortcut kartu utama mengarah ke `Riwayat Laporan` dan `Edit Profil`.

- `dashboard.admin`
  - Tampilkan global approved minutes.
  - Tampilkan margin agensi.
  - Tombol export CSV payroll.
  - Tombol mark as paid.
  - Tampilkan statistik worker/mitra.
  - Tampilkan 10 laporan terbaru global.
  - Tampilkan daftar invoice klien Mytronlabs.

- `dashboard.fallback`
  - Tampil jika user belum ditautkan ke partner dan bukan admin/finance.

Partner:

- `partners.index`
  - Search/filter partner.
  - Tabel daftar partner.
  - Aksi edit dan delete.

- `partners.create`
  - Form tambah partner.
  - Section identitas/kontak.
  - Section finansial.
  - Section operasional/hierarki.
  - `mitra_id` digenerate otomatis dan readonly.
  - Dropdown atasan hanya tampil jika role `worker`.

- `partners.edit`
  - Mirip create, tetapi `mitra_id` readonly dan tidak dikirim sebagai field update.

Video submission:

- `video-submissions.submit-report`
  - Form worker submit tanggal kerja, durasi menit, bukti email, bukti kualitas aplikasi.
  - Upload harus multipart/form-data.

- `video-submissions.qc-room`
  - Tabel pending reports.
  - Search report.
  - Modal review berbasis Alpine.
  - Modal review menampilkan dua bukti gambar dinamis:
    - `evidence_email_image_url`.
    - `evidence_app_quality_image_url`.
  - Jika file tidak ada/path kosong, modal menampilkan placeholder teks "file tidak ditemukan".
  - Pilihan QC: approve penuh, approve sebagian, reject.

- `video-submissions.report-history`
  - Halaman khusus worker/mitra untuk melihat riwayat laporan.
  - Worker hanya melihat laporan miliknya.
  - Mitra melihat laporan miliknya dan worker direct di bawahnya.
  - Tersedia summary total, pending QC, approved, dan durasi unpaid.
  - Tersedia filter search, status QC, dan status bayar.
  - Tabel menampilkan ID laporan, worker, tanggal kerja, durasi kirim, durasi disetujui, status QC, status bayar, dan catatan verifikasi.

## 12. Environment Variables

Variable dari `.env.example` dan `.env` lokal:

### Aplikasi

- `APP_NAME`: nama aplikasi.
- `APP_ENV`: environment, misalnya local/production.
- `APP_KEY`: encryption key Laravel. Wajib ada setelah `php artisan key:generate`.
- `APP_DEBUG`: true/false untuk debug.
- `APP_URL`: base URL aplikasi.
- `APP_LOCALE`: locale utama.
- `APP_FALLBACK_LOCALE`: locale fallback.
- `APP_FAKER_LOCALE`: locale Faker.
- `APP_MAINTENANCE_DRIVER`: driver maintenance mode.
- `BCRYPT_ROUNDS`: cost bcrypt.

### Logging

- `LOG_CHANNEL`: channel log utama.
- `LOG_STACK`: stack log.
- `LOG_DEPRECATIONS_CHANNEL`: channel deprecation.
- `LOG_LEVEL`: level log.

### Database

- `DB_CONNECTION`: driver database, contoh sqlite/mysql.
- `DB_HOST`: host database.
- `DB_PORT`: port database.
- `DB_DATABASE`: nama database atau path database.
- `DB_USERNAME`: username database.
- `DB_PASSWORD`: password database.

Catatan lokal:

- `.env` lokal memiliki variable database aktif. Nilainya tidak ditulis di dokumen ini.

### Session

- `SESSION_DRIVER`: driver session, contoh database.
- `SESSION_LIFETIME`: durasi session dalam menit.
- `SESSION_ENCRYPT`: apakah session dienkripsi.
- `SESSION_PATH`: path cookie session.
- `SESSION_DOMAIN`: domain cookie session.

### Queue, Cache, Broadcast, Filesystem

- `BROADCAST_CONNECTION`: koneksi broadcast.
- `FILESYSTEM_DISK`: disk filesystem default.
- `QUEUE_CONNECTION`: koneksi queue.
- `CACHE_STORE`: store cache.
- `MEMCACHED_HOST`: host memcached jika dipakai.

### Redis

- `REDIS_CLIENT`: client redis.
- `REDIS_HOST`: host redis.
- `REDIS_PASSWORD`: password redis.
- `REDIS_PORT`: port redis.

### Mail

- `MAIL_MAILER`: mailer, contoh log/smtp.
- `MAIL_SCHEME`: scheme mail.
- `MAIL_HOST`: host mail.
- `MAIL_PORT`: port mail.
- `MAIL_USERNAME`: username mail.
- `MAIL_PASSWORD`: password mail.
- `MAIL_FROM_ADDRESS`: alamat pengirim.
- `MAIL_FROM_NAME`: nama pengirim.

### AWS/S3

- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `AWS_USE_PATH_STYLE_ENDPOINT`

Dipakai jika filesystem/cloud storage diarahkan ke S3.

### Vite

- `VITE_APP_NAME`: nama aplikasi yang tersedia untuk frontend build.

## 13. Seeder dan Data Demo

`DatabaseSeeder` membuat:

- Super Admin utama.
- Super Admin backup.
- Tim Verifikator.
- Tim Keuangan.
- 5 Mitra Coordinator.
- 95 Worker.
- Tiap worker mendapat 3 sampai 8 laporan video random.
- 2 invoice klien mock untuk Mytronlabs.

Password demo di README:

- Semua akun demo memakai password `password`.

Catatan keamanan:

- Jangan pakai password demo di production.
- Jika seeding production tidak diperlukan, jangan jalankan seeder demo.

## 14. Factory

### `UserFactory`

Default:

- name random.
- email random unique.
- email verified.
- password default `password` di-hash.
- remember token random.

### `PartnerFactory`

Default:

- `mitra_id` auto counter `KMK-XXX`.
- `partner_role` random lebih sering worker.
- `mitra_parent_id` null.
- data bank random.
- `base_hourly_rate = 54000`.
- `user_id = User::factory()`.

Catatan:

- Factory belum mengisi field baru seperti `bank_account_number`, `bank_account_owner`, `nik`, `email`, `full_address`, `smartphone_type`.

### `VideoWorkReportFactory`

Default:

- Durasi 10 sampai 300 menit.
- Status random: pending, approved, rejected.
- Jika approved, approved duration sama atau sedikit kurang dari submitted.
- Verifier dari user role `verifikator`.
- Payment status hanya bisa paid/unpaid jika approved.
- Evidence path memakai sample static.

## 15. Alur Fitur Utama

### Alur worker submit laporan

1. Worker login.
2. Sistem mencari partner dengan `user_id` user login.
3. Jika partner role worker, user bisa buka `/submit-report`.
4. Worker mengisi tanggal kerja, durasi menit, dua bukti gambar.
5. Sistem validasi input.
6. Sistem kompres gambar ke disk `evidence`, default-nya `storage/app/private/evidences/...`.
7. Sistem membuat `video_work_reports` dengan status:
   - `qc_status = pending`
   - `payment_status = unpaid`
8. Worker diarahkan kembali ke dashboard.

### Alur QC

1. User buka `/qc-room`.
2. Sistem mengambil report `pending`.
3. User klik Review.
4. Modal menampilkan info report, evidence dari route private backend, dan pilihan aksi.
5. Jika approve penuh:
   - approved minutes = submitted minutes.
6. Jika approve sebagian:
   - approved minutes dari input.
7. Jika reject:
   - verifier notes wajib.
8. Status report berubah menjadi approved/rejected, verifier dan verified_at tersimpan.

### Alur payroll CSV

1. Superadmin/admin/finance buka dashboard admin.
2. Klik export CSV payroll.
3. Sistem mengambil semua report approved+unpaid.
4. Sistem group per partner.
5. Sistem menghitung nominal dalam `IDR` memakai `partners.base_hourly_rate`.
6. CSV menampilkan mata uang, rate Rupiah per jam, total menit, total jam, dan total nominal Rupiah.
7. Browser download file CSV.
5. Sistem hitung total menit dan nominal.
6. Browser download file CSV.
7. Setelah dibayar manual di bank/payment system, user klik Mark as Paid.
8. Sistem mengubah semua approved+unpaid menjadi paid.

### Alur dashboard

1. User login dan verified.
2. Sistem cek partner terkait user.
3. Worker melihat earnings personal dan riwayat laporan.
4. Mitra melihat komisi tim, pendapatan pribadi, dan daftar worker direct.
5. Superadmin/admin/finance melihat metrik global, latest reports, invoice klien, payroll action.
6. User tanpa partner dan bukan admin/finance melihat fallback.

## 16. Command Penting

Install dependency:

```bash
composer install
npm install
```

Generate key:

```bash
php artisan key:generate
```

Migrasi database:

```bash
php artisan migrate
```

Migrasi dan seed demo:

```bash
php artisan migrate:fresh --seed
```

Build frontend:

```bash
npm run build
```

Jalankan Vite:

```bash
npm run dev
```

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan semua dev process sesuai composer script:

```bash
composer run dev
```

Buat storage symlink:

```bash
php artisan storage:link
```

Catatan:

- Evidence laporan baru tidak bergantung pada symlink ini karena disimpan di private storage.
- Symlink masih boleh diperlukan untuk asset publik lain yang memang disimpan di disk `public`.

Migrasi evidence lama dari public/local storage ke disk evidence:

```bash
php artisan evidence:migrate-to-private --delete-public
```

Cek apakah path evidence di database masih punya file fisik:

```bash
php artisan evidence:check-files --show-missing
```

Catatan deployment production:

- File upload user tidak pernah ikut Git dan memang harus begitu.
- Bukti laporan/bukti transfer disimpan di `storage/app/private/evidences/...`.
- `git pull` normal tidak akan menghapus isi upload karena file upload tidak tracked oleh Git.
- Jangan deploy dengan cara menghapus folder `storage/app/private` atau menjalankan clean yang ikut menghapus ignored files seperti `git clean -fdx`.
- Backup database saja tidak cukup; backup juga folder `storage/app/private/evidences`.

Run test:

```bash
composer run test
```

atau:

```bash
php artisan test
```

## 17. Catatan Inkonsistensi dan Potensi Perbaikan

Bagian ini penting untuk AI/developer berikutnya.

### 1. Unit finansial sudah diarahkan ke Rupiah

- `base_hourly_rate` dipakai sebagai rate Rupiah per jam.
- Form create/edit partner menampilkan rate dalam Rupiah.
- Dashboard worker/mitra menampilkan estimasi pendapatan dalam Rupiah.
- Payroll CSV menghasilkan `Total Nominal Rupiah`.

Catatan:

- Jika suatu saat ada kebutuhan multi-currency, jangan memakai ulang `base_hourly_rate` tanpa nama yang lebih eksplisit. Buat field baru seperti `base_hourly_rate_idr` atau tabel rate terpisah.

### 2. Middleware role route sensitif

Route sensitif utama sekarang sudah memakai middleware role:

- `partners`: `role:superadmin,admin`.
- `admin-users`: `role:superadmin,admin`.
- `qc-room`: `role:superadmin,admin,finance`.
- `payroll`: `role:superadmin,admin,finance`.

Catatan:

- Tetap cek setiap route baru agar tidak hanya mengandalkan sidebar.

### 3. QC modal memakai gambar upload asli via route private

- Model `VideoWorkReport` memiliki accessor:
  - `evidence_email_image_url`.
  - `evidence_app_quality_image_url`.
- URL dibentuk lewat route `video-submissions.evidence.show`.
- Route evidence tetap membutuhkan login, verified user, dan role yang diizinkan.
- View QC memakai accessor tersebut untuk menampilkan gambar evidence.

Catatan:

- Upload evidence baru disimpan di disk `evidence`.
- Disk `evidence` adalah `storage/app/private/evidences/...`, sama seperti pola private storage ONFIX.
- File lama yang masih berada di public storage bisa dimigrasikan dengan `php artisan evidence:migrate-to-private --delete-public`.
- Controller evidence masih punya fallback membaca disk `local` dan `public` untuk kompatibilitas selama file lama belum dimigrasikan.

### 4. `User` model dan role

- `role` sudah masuk fillable.
- Helper role ada di model `User`.

Rekomendasi:

- Untuk role baru, update:
  - validasi `ManageAdminUsersController`.
  - helper `User`.
  - middleware route.
  - sidebar.

### 5. README menyebut Laravel 11.x, composer Laravel 13.x

Rekomendasi:

- Perbarui README agar sesuai dependency aktual.

### 6. Access control backend

- Sidebar menyembunyikan link berdasarkan role.
- Backend route sensitif juga sudah memakai middleware role.
- Test regresi proteksi route ada di `tests/Feature/RouteRoleProtectionTest.php`.

Rekomendasi:

- Jangan mengandalkan UI untuk security.
- Setiap route sensitif baru wajib diberi middleware/policy dan test akses langsung.

### 7. `ProcessPartnerPayrollService` belum dipakai

- Ada service payroll yang lebih transaksional.
- Controller export/mark paid memakai logic sendiri.

Rekomendasi:

- Konsolidasikan payroll logic agar tidak duplikatif.

### 8. File upload evidence private

- Upload evidence baru disimpan di disk `evidence`.
- Akses evidence memakai route backend dan role middleware.
- Test akses evidence ada di `tests/Feature/VideoWorkReportEvidenceAccessTest.php`.

Rekomendasi:

- Untuk production yang sudah punya file lama di public/local storage, jalankan migrasi evidence, lalu backup folder `storage/app/private/evidences`.

## 18. Peta File Penting untuk AI

Jika ingin mengubah dashboard:

- `app/Http/Controllers/RenderDashboardOverviewController.php`
- `app/Services/CalculatePartnerMetricsService.php`
- `resources/views/dashboard/*.blade.php`

Jika ingin mengubah partner CRUD:

- `app/Http/Controllers/ManagePartnerDemographicsController.php`
- `app/Models/Partner.php`
- `resources/views/partners/*.blade.php`
- migration `partners`.

Jika ingin mengubah submit laporan:

- `app/Http/Controllers/SubmitVideoWorkReportController.php`
- `app/Models/VideoWorkReport.php`
- `resources/views/video-submissions/submit-report.blade.php`
- private storage setup.
- `app/Http/Controllers/ShowVideoWorkReportEvidenceController.php`
- `app/Console/Commands/MigrateEvidenceFilesToPrivateStorage.php`

Jika ingin mengubah riwayat laporan worker/mitra:

- `app/Http/Controllers/ListPartnerReportHistoryController.php`
- `resources/views/video-submissions/report-history.blade.php`
- `resources/views/components/sidebar.blade.php`
- `resources/views/dashboard/worker.blade.php`
- `resources/views/dashboard/mitra.blade.php`

Jika ingin mengubah QC:

- `app/Http/Controllers/VerifyVideoWorkReportController.php`
- `app/Actions/VerifyVideoWorkReportAction.php`
- `resources/views/video-submissions/qc-room.blade.php`

Jika ingin mengubah payroll:

- `app/Http/Controllers/ExportPayrollDataController.php`
- `app/Services/ProcessPartnerPayrollService.php`
- `app/Services/CalculatePartnerMetricsService.php`
- `app/Models/VideoWorkReport.php`
- `app/Models/Partner.php`

Jika ingin mengubah role/access:

- `app/Http/Middleware/RoleMiddleware.php`
- `app/Models/User.php`
- `bootstrap/app.php`
- `routes/web.php`
- `resources/views/components/sidebar.blade.php`

Jika ingin mengubah CRUD admin internal:

- `app/Http/Controllers/ManageAdminUsersController.php`
- `resources/views/admin-users/*.blade.php`
- `app/Models/User.php`

Jika ingin mengubah profile:

- `app/Http/Controllers/ProfileController.php`
- `resources/views/profile/*.blade.php`
- `resources/views/components/sidebar.blade.php`

Jika ingin mengubah frontend style:

- `resources/views/layouts/app.blade.php`
- `resources/views/components/sidebar.blade.php`
- `resources/views/components/navbar.blade.php`
- `resources/css/app.css`
- `tailwind.config.js`

## 19. Mental Model Data

Model hubungan:

```text
User
  optional one Partner via partners.user_id

Partner (mitra)
  has many Partner (worker) via workers.mitra_parent_id

Partner (worker or mitra)
  has many VideoWorkReport

VideoWorkReport
  belongs to Partner
  optionally belongs to User as verifier

ClientInvoice
  standalone record invoice klien
```

Status penting:

```text
partners.status:
  active
  suspended

video_work_reports.qc_status:
  pending
  approved
  rejected

video_work_reports.payment_status:
  unpaid
  paid

client_invoices.status:
  unpaid_by_client
  paid_by_client
```

Money/rate mental model saat ini:

```text
Dashboard worker:
  approved worker minutes * worker base_hourly_rate (Rupiah/jam)

Dashboard mitra:
  managed worker approved minutes * komisi mitra (Rupiah/jam)
  mitra own approved minutes * mitra base_hourly_rate (Rupiah/jam)

Dashboard global:
  client billing dan margin dihitung dalam Rupiah sesuai konstanta service

Payroll CSV:
  approved unpaid minutes * partners.base_hourly_rate
  output Rupiah
```

Pembayaran gaji tidak memakai flow withdraw dari sisi worker. Admin/finance memproses pembayaran manual via transfer, lalu sistem bisa menandai laporan approved-unpaid menjadi `paid`.

## 20. Kesimpulan

Project ini adalah dashboard operasional Laravel untuk agensi data/video dengan empat area besar:

1. Manajemen akun dan partner.
2. Submit laporan kerja video oleh worker.
3. QC/verifikasi laporan oleh tim internal.
4. Perhitungan dashboard dan payroll.

Sebelum mengubah kode, pahami dulu bahwa `users.role` dan `partners.partner_role` adalah dua hal berbeda. Area paling sensitif untuk production adalah authorization route, bukti gambar QC, akun login partner, dan payroll.
