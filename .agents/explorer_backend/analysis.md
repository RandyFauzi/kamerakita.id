# Laporan Audit Backend & Performa Database Fitur Mailbox

> **Tanggal Audit**: 10 Agustus 2026  
> **Status**: Read-Only Code Review Audit (Tanpa Perubahan Kode Sumber)  
> **Target Proyek**: KameraKita.id (`c:\laragon\www\kamerakita.id-main`)  
> **Fokus**: Standar Pengkodean Backend (Best Practices - R1) & Performa/Efisiensi Database (R2)

---

## Ringkasan Eksekutif

Audit backend dan performa basis data terhadap fitur Mailbox di aplikasi KameraKita.id mengidentifikasi **9 temuan utama** yang mencakup aspek performa kueri Eloquent, manajemen memori pada cron/background job, optimasi penanganan email IMAP Hostinger, serta penerapan standar pengkodean Laravel (SOLID, tipe data, validasi, dan otorisasi).

---

## Tabel Ringkasan Temuan Audit

| ID | Judul Temuan | Kategori | Tingkat Keparahan | Komponen Terdampak |
|---|---|---|---|---|
| **BE-01** | Pengambilan Seluruh Data Email Tanpa Paginasi (`->get()`) Mengakibatkan Memory Exhaustion & Monolithic JSON Payload | R2: Performa & DB | **Critical** | `MailboxController.php:12` |
| **BE-02** | Masalah Kueri N+1 pada Pencarian User dalam Loop Pemrosesan Email Catch-All | R2: Performa & DB | **High** | `ProcessCatchAllEmailService.php:64` |
| **BE-03** | Risiko Out-Of-Memory (OOM) Akibat Penarikan Seluruh Folder IMAP Tanpa Chunking/Filtering | R2: Performa & DB | **High** | `ProcessCatchAllEmailService.php:24` |
| **BE-04** | Kurangnya Indeks Database (DB Indexes) untuk Query Sort, Filtering, dan Cleanup Email | R2: Performa & DB | **High** | Migration `captured_emails` |
| **BE-05** | Absensi Perlindungan Cron Overlapping (`withoutOverlapping`) pada Jadwal Penarikan Email 1 Menit | R2: Performa & DB | **Medium** | `routes/console.php:15` |
| **BE-06** | Tidak Adanya Validasi Input (Request Validation) dan Type Casting pada Controller Status Email | R1: Best Practices | **Medium** | `MailboxController.php:19,26` |
| **BE-07** | Pencampuran Logika Output Terminal (`echo`) pada Service Layer Mengabaikan Separation of Concerns | R1: Best Practices | **Medium** | `ProcessCatchAllEmailService.php` |
| **BE-08** | Penggunaan Otorisasi Manual Berulang Tanpa Laravel Policy / Gate Abstraction | R1: Best Practices | **Low** | `MailboxController.php:18,25` |
| **BE-09** | Discrepancy Deskripsi Command CLI dan Kurangnya DB Batching Transaction | R1 & R2 | **Low** | `PullMailboxEmailsCommand.php:10` |

---

## Detail Analisis Temuan Audit

---

### BE-01: Pengambilan Seluruh Data Email Tanpa Paginasi (`->get()`) Mengakibatkan Memory Exhaustion & Monolithic JSON Payload

- **Tingkat Keparahan**: `Critical`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `app/Http/Controllers/MailboxController.php` (Baris 12) & `resources/views/mailbox/index.blade.php` (Baris 310)

#### Deskripsi Masalah & Analisis Dampak
Pada method `index()` di `MailboxController`, kueri Eloquent dipanggil dengan method `->get()` tanpa batasan jumlah baris (*limit*) maupun paginasi (*pagination*):
```php
$emails = Auth::user()->capturedEmails()->orderBy('received_at', 'desc')->get();
```
Seluruh record email milik pengguna (termasuk kolom `message_content` yang berisi dokumen HTML email lengkap yang bisa berukuran puluhan hingga ratusan kilobyte) ditarik sekaligus dari database MySQL ke dalam memori PHP server. Data ini kemudian di-encode menjadi JSON raksasa pada Blade view via `{{ Js::from($emails) }}`.

**Dampak**:
1. **Memory Exhaustion (OOM)**: Seiring berjalannya waktu dan bertambahnya akumulasi email (misalnya ratusan hingga ribuan email), penggunaan RAM PHP server akan membengkak drastis saat membuka halaman `/mailbox`, berujung pada error `Fatal error: Allowed memory size of X bytes exhausted`.
2. **Payload HTML Raksasa & Slow Network**: Ukuran response HTTP menjadi sangat besar (bisa mencapai 5MB–20MB+), memperlambat loading halaman secara signifikan.
3. **DOM & JS Freezing**: Browser pengguna akan mengalami lag/freeze saat memuat array JSON yang sangat besar ke AlpineJS store.

#### Cuplikan Kode Saat Ini
```php
// app/Http/Controllers/MailboxController.php:10-14
public function index()
{
    $emails = Auth::user()->capturedEmails()->orderBy('received_at', 'desc')->get();
    return view('mailbox.index', compact('emails'));
}
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// app/Http/Controllers/MailboxController.php:10-16
public function index()
{
    // Hanya ambil atribut ringan untuk list inbox, hindari message_content yang berat
    // Atau gunakan pagination jika ingin membatasi payload per halaman
    $emails = Auth::user()->capturedEmails()
        ->select(['id', 'user_id', 'sender_address', 'subject', 'received_at', 'is_read', 'is_starred'])
        ->orderBy('received_at', 'desc')
        ->paginate(50);

    return view('mailbox.index', compact('emails'));
}
```
*Catatan Tambahan*: Untuk membaca isi lengkap pesan (`message_content`), sediakan endpoint REST API terpisah (misal `GET /mailbox/{email}`) yang dipanggil secara on-demand via AJAX ketika pengguna mengklik email di list.

---

### BE-02: Masalah Kueri N+1 pada Pencarian User dalam Loop Pemrosesan Email Catch-All

- **Tingkat Keparahan**: `High`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `app/Services/ProcessCatchAllEmailService.php` (Baris 64)

#### Deskripsi Masalah & Analisis Dampak
Dalam method `processEmails()`, service mengiterasi seluruh pesan dari server IMAP Hostinger. Untuk setiap alamat penerima email (`$toAddresses`), service melakukan kueri individual ke database untuk mencari instance model `User`:
```php
foreach ($toAddresses as $to) {
    $emailAddress = strtolower(trim($to->mail));
    $user = User::where('email', $emailAddress)->first(); // N+1 Database Query!
    ...
}
```

**Dampak**:
Jika dalam satu siklus penarikan terdapat 200 pesan di INBOX, maka service akan menjalankan **200+ kueri database `SELECT * FROM users WHERE email = ?` secara individual**. Hal ini menimbulkan *database I/O latency* yang tinggi, memperlambat proses sync IMAP dari hitungan detik menjadi puluhan detik/menit, serta membebani koneksi MySQL server secara tidak perlu.

#### Cuplikan Kode Saat Ini
```php
// app/Services/ProcessCatchAllEmailService.php:58-66
foreach ($toAddresses as $to) {
    $emailAddress = strtolower(trim($to->mail));
    echo "🎯 Alamat tujuan terdeteksi: " . $emailAddress . "\n";
    
    // Mencari user di database KameraKita
    $user = User::where('email', $emailAddress)->first();
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// app/Services/ProcessCatchAllEmailService.php
// Pre-load seluruh mapping email -> user_id ke dalam memory hashtable (1 kueri saja)
$userMap = User::pluck('id', 'email')
    ->keyBy(fn ($id, $email) => strtolower(trim($email)));

foreach ($messages as $message) {
    ...
    foreach ($toAddresses as $to) {
        $emailAddress = strtolower(trim($to->mail));
        $userId = $userMap->get($emailAddress);

        if ($userId) {
            // User ditemukan tanpa kueri tambahan!
```

---

### BE-03: Risiko Out-Of-Memory (OOM) Akibat Penarikan Seluruh Folder IMAP Tanpa Chunking/Filtering

- **Tingkat Keparahan**: `High`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `app/Services/ProcessCatchAllEmailService.php` (Baris 24)

#### Deskripsi Masalah & Analisis Dampak
Service menarik seluruh pesan di folder INBOX menggunakan `$folder->query()->all()->get();`.
Library Webklex IMAP akan mengunduh header, struktur MIME, dan body pesan dari server remote ke dalam objek PHP collection.

**Dampak**:
1. **Tingginya Penggunaan Memori**: Menarik ribuan email dari Hostinger IMAP sekaligus akan dengan cepat melampaui `memory_limit` PHP CLI (misal 128M/256M).
2. **Pembersihan Garbaged Objects**: Objek `$message` dan string `$content` berukuran besar tetap berada di dalam memori selama siklus loop `foreach` berjalan jika tidak dihancurkan secara eksplisit (`unset()` / `gc_collect_cycles()`).

#### Cuplikan Kode Saat Ini
```php
// app/Services/ProcessCatchAllEmailService.php:21-24
$folder = $client->getFolder('INBOX');

// Mengambil SEMUA pesan (dibaca maupun belum)
$messages = $folder->query()->all()->get();
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// app/Services/ProcessCatchAllEmailService.php
// Opsi A: Hanya tarik email yang belum dibaca (UNSEEN)
$messages = $folder->query()->unseen()->get();

// Opsi B: Gunakan pagination / batch limit pada IMAP query & panggil garbage collector
$messages = $folder->query()->all()->limit(50)->get();

foreach ($messages as $message) {
    // Process message...
    
    // Bebaskan memori setelah memproses 1 pesan
    unset($message, $content);
    if (gc_enabled()) {
        gc_collect_cycles();
    }
}
```

---

### BE-04: Kurangnya Indeks Database (DB Indexes) untuk Query Sort, Filtering, dan Cleanup Email

- **Tingkat Keparahan**: `High`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**:
  - `database/migrations/2026_08_09_101751_create_captured_emails_table.php` (Baris 14-22)
  - `database/migrations/2026_08_09_152322_add_read_and_starred_to_captured_emails_table.php` (Baris 14-17)

#### Deskripsi Masalah & Analisis Dampak
Tabel `captured_emails` hanya memiliki primary key `id` dan foreign key `user_id`. Tidak ada index opsional maupun komposit pada kolom-kolom berikut:
1. `received_at`: Digunakan pada `CleanExpiredEmailsCommand::handle()` (`where('received_at', '<', subDays(14))->delete()`) dan sorting `orderBy('received_at', 'desc')`.
2. Komposit `(user_id, received_at)`: Digunakan pada `MailboxController::index()` dan pengecekan anti-duplikat `CapturedEmail::firstOrCreate(['user_id' => ..., 'received_at' => ...])`.
3. `is_read` & `is_starred`: Digunakan untuk filter tab email pada antarmuka.

**Dampak**:
Tanpa indeks basis data:
- `CleanExpiredEmailsCommand` melakukan **FULL TABLE SCAN** pada seluruh record `captured_emails` saat menjalankan proses penghapusan harian.
- `MailboxController::index()` memaksa MySQL melakukan operasi **filesort** untuk mengurutkan email berdasarkan tanggal untuk setiap user.
- Operasi `firstOrCreate` pada service IMAP melambat secara eksponensial seiring bertambahnya jumlah baris data.

#### Cuplikan Kode Migration Saat Ini
```php
// database/migrations/2026_08_09_101751_create_captured_emails_table.php:14-22
Schema::create('captured_emails', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('sender_address');
    $table->string('subject')->nullable();
    $table->text('message_content')->nullable();
    $table->timestamp('received_at')->nullable();
    $table->timestamps();
});
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Metode perbaikan dilakukan dengan membuat migration baru (misal `add_indexes_to_captured_emails_table.php`):
```php
Schema::table('captured_emails', function (Blueprint $table) {
    // Indeks untuk pencarian & sorting email per user
    $table->index(['user_id', 'received_at'], 'idx_captured_emails_user_received');
    
    // Indeks untuk cron job pembersihan email kadaluarsa
    $table->index('received_at', 'idx_captured_emails_received_at');
    
    // Indeks pendukung filtering status read/starred
    $table->index(['user_id', 'is_read'], 'idx_captured_emails_user_read');
    $table->index(['user_id', 'is_starred'], 'idx_captured_emails_user_starred');
});
```

---

### BE-05: Absensi Perlindungan Cron Overlapping (`withoutOverlapping`) pada Jadwal Penarikan Email 1 Menit

- **Tingkat Keparahan**: `Medium`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `routes/console.php` (Baris 15)

#### Deskripsi Masalah & Analisis Dampak
Pada `routes/console.php`, perintah penarikan email dijadwalkan setiap 1 menit:
```php
Schedule::command('app:pull-mailbox-emails')->everyMinute();
```
Perintah ini **tidak dilengkapi dengan method `->withoutOverlapping()`**.

**Dampak**:
Jika koneksi ke server IMAP Hostinger mengalami kecemasan jaringan, atau jika penarikan ribuan email membutuhkan waktu lebih dari 60 detik, maka scheduler Laravel akan memicu instance proses baru pada menit berikutnya.
Proses-proses yang saling tumpang tindih (*overlapping processes*) ini akan berebut resource CPU/RAM server, memicu race condition pada database, dan berpotensi mengalami deadlock.

#### Cuplikan Kode Saat Ini
```php
// routes/console.php:14-16
// Mailbox Catch-all Polling
Schedule::command('app:pull-mailbox-emails')->everyMinute();
Schedule::command('app:clean-expired-emails')->daily();
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// routes/console.php:14-16
// Mailbox Catch-all Polling dengan perlindungan overlapping lock 10 menit
Schedule::command('app:pull-mailbox-emails')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->onOneServer();

Schedule::command('app:clean-expired-emails')->daily();
```

---

### BE-06: Tidak Adanya Validasi Input (Request Validation) dan Type Casting pada Controller Status Email

- **Tingkat Keparahan**: `Medium`
- **Kategori**: R1: Standar Pengkodean (Best Practices)
- **Lokasi File**: `app/Http/Controllers/MailboxController.php` (Baris 19 & 26)

#### Deskripsi Masalah & Analisis Dampak
Pada method `toggleRead` dan `toggleStarred`:
```php
$email->update(['is_read' => $request->is_read]);
$email->update(['is_starred' => $request->is_starred]);
```
Controller langsung menggunakan properti `$request->is_read` dan `$request->is_starred` tanpa melakukan validasi tipe data (`boolean`, `required`).

**Dampak**:
1. Melanggar standar keamanan dan pengkodean Laravel (Validation Layer).
2. Jika request mengirimkan payload yang salah (misal string acak, null, atau array), query Eloquent update dapat gagal atau berperilaku tidak konsisten.

#### Cuplikan Kode Saat Ini
```php
// app/Http/Controllers/MailboxController.php:16-28
public function toggleRead(Request $request, \App\Models\CapturedEmail $email)
{
    if ($email->user_id !== Auth::id()) abort(403);
    $email->update(['is_read' => $request->is_read]);
    return response()->json(['success' => true]);
}

public function toggleStarred(Request $request, \App\Models\CapturedEmail $email)
{
    if ($email->user_id !== Auth::id()) abort(403);
    $email->update(['is_starred' => $request->is_starred]);
    return response()->json(['success' => true]);
}
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// app/Http/Controllers/MailboxController.php:16-28
public function toggleRead(Request $request, \App\Models\CapturedEmail $email)
{
    $this->authorize('update', $email);

    $validated = $request->validate([
        'is_read' => 'required|boolean',
    ]);

    $email->update($validated);

    return response()->json(['success' => true]);
}

public function toggleStarred(Request $request, \App\Models\CapturedEmail $email)
{
    $this->authorize('update', $email);

    $validated = $request->validate([
        'is_starred' => 'required|boolean',
    ]);

    $email->update($validated);

    return response()->json(['success' => true]);
}
```

---

### BE-07: Pencampuran Logika Output Terminal (`echo`) pada Service Layer Mengabaikan Separation of Concerns

- **Tingkat Keparahan**: `Medium`
- **Kategori**: R1: Standar Pengkodean (Best Practices)
- **Lokasi File**: `app/Services/ProcessCatchAllEmailService.php` (Baris 14, 19, 25, 30, 32, dll.)

#### Deskripsi Masalah & Analisis Dampak
`ProcessCatchAllEmailService` dipenuhi oleh lebih dari 15 pernyataan `echo` bertema emoji (`echo "🔍 Menghubungkan...";`, `echo "✅ Berhasil...";`).

**Dampak**:
1. **Violates Single Responsibility Principle (SRP)**: Domain Service seharusnya murni menangani logika bisnis pemrosesan email, bukan menangani formatting atau I/O terminal.
2. **Logging Disruption**: Ketika dipanggil dari Queue Background Worker atau skrip non-CLI, `echo` akan mengganggu standard output stream dan tidak terekam pada file log aplikasi Laravel (`storage/logs/laravel.log`).

#### Cuplikan Kode Saat Ini
```php
// app/Services/ProcessCatchAllEmailService.php:14-19
echo "🔍 Menghubungkan ke server IMAP Hostinger...\n";

try {
    $client = Client::account('default');
    $client->connect();
    echo "✅ Berhasil terhubung ke server!\n";
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// app/Services/ProcessCatchAllEmailService.php
use Illuminate\Support\Facades\Log;

class ProcessCatchAllEmailService
{
    public function processEmails(): void
    {
        Log::info('IMAP Catch-All: Connecting to Hostinger mail server...');

        try {
            $client = Client::account('default');
            $client->connect();
            Log::info('IMAP Catch-All: Connected successfully.');
            ...
        } catch (\Exception $e) {
            Log::error('IMAP Catch-All Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
```

---

### BE-08: Penggunaan Otorisasi Manual Berulang Tanpa Laravel Policy / Gate Abstraction

- **Tingkat Keparahan**: `Low`
- **Kategori**: R1: Standar Pengkodean (Best Practices)
- **Lokasi File**: `app/Http/Controllers/MailboxController.php` (Baris 18 & 25)

#### Deskripsi Masalah & Analisis Dampak
Pengecekan hak akses `if ($email->user_id !== Auth::id()) abort(403);` ditulis secara manual dan berulang pada method controller.

**Dampak**:
Melanggar prinsip DRY (Don't Repeat Yourself). Jika kelak terdapat peran khusus (seperti Superadmin) yang berhak melihat/mengelola email user lain, logika manual ini harus diubah pada banyak titik secara berisiko.

#### Cuplikan Kode Saat Ini
```php
// app/Http/Controllers/MailboxController.php:18,25
if ($email->user_id !== Auth::id()) abort(403);
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Buat `CapturedEmailPolicy`:
```php
// app/Policies/CapturedEmailPolicy.php
namespace App\Policies;

use App\Models\CapturedEmail;
use App\Models\User;

class CapturedEmailPolicy
{
    public function update(User $user, CapturedEmail $email): bool
    {
        return $user->id === $email->user_id || $user->hasRole('superadmin');
    }
}
```
Lalu panggil di Controller:
```php
$this->authorize('update', $email);
```

---

### BE-09: Discrepancy Deskripsi Command CLI dan Kurangnya DB Batching Transaction

- **Tingkat Keparahan**: `Low`
- **Kategori**: R1: Best Practices & R2: Performa
- **Lokasi File**: `app/Console/Commands/PullMailboxEmailsCommand.php` (Baris 10) & `app/Services/ProcessCatchAllEmailService.php` (Baris 82-92)

#### Deskripsi Masalah & Analisis Dampak
1. Attributes deskripsi pada `PullMailboxEmailsCommand` menuliskan `#[Description('Pull unseen emails from catch-all IMAP server')]`, padahal `ProcessCatchAllEmailService` mengeksekusi `->all()->get()` (menarik semua email, baik yang sudah maupun belum dibaca).
2. Pemasupan record baru pada `firstOrCreate` dilakukan secara individual dalam mode autocommit tanpa pembungkusan `DB::transaction(...)`.

#### Cuplikan Kode Saat Ini
```php
// app/Console/Commands/PullMailboxEmailsCommand.php:9-10
#[Signature('app:pull-mailbox-emails')]
#[Description('Pull unseen emails from catch-all IMAP server')]
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// app/Console/Commands/PullMailboxEmailsCommand.php:9-10
#[Signature('app:pull-mailbox-emails')]
#[Description('Fetch and process incoming emails from catch-all IMAP server')]
```
Dan bungkus pemrosesan simpan batch email pada service dengan `DB::transaction()` untuk efisiensi penulisan disk I/O.

---

## Rekomendasi Langkah Implemetasi

1. **Prioritas Utama (Critical & High)**:
   - Tambahkan paginasi atau optimasi kueri kolom pada `MailboxController::index()`.
   - Lakukan refactoring pada `ProcessCatchAllEmailService` untuk pre-load mapping user (`$userMap`), menggantikan kueri N+1.
   - Tambahkan migration indeks database (`user_id`, `received_at`, `is_read`, `is_starred`).
2. **Prioritas Menengah (Medium)**:
   - Tambahkan `withoutOverlapping()` pada penjadwalan `app:pull-mailbox-emails` di `routes/console.php`.
   - Tambahkan Request Validation pada method `toggleRead` & `toggleStarred`.
   - Ganti pernyataan `echo` pada `ProcessCatchAllEmailService` dengan panggilan `Log::info()` / `Log::error()`.
3. **Prioritas Rendah (Low)**:
   - Terapkan Laravel Policy (`CapturedEmailPolicy`) untuk otorisasi email.
   - Sesuaikan deskripsi `PullMailboxEmailsCommand`.
