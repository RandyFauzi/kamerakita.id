# Laporan Audit Code Review & Rekomendasi Perbaikan Fitur Mailbox KameraKita.id

## Informasi Metadata

- **Tanggal Audit**: 10 Agustus 2026
- **Target Application**: KameraKita AI / Mailbox System (`c:\laragon\www\kamerakita.id-main`)
- **Status Audit**: Read-Only Audit (Tanpa Perubahan Kode Sumber Aplikasi)
- **Overview**: 
  Dokumen ini menyajikan hasil evaluasi menyeluruh (*code review*) terhadap fitur Mailbox pada platform KameraKita.id. Audit mencakup tiga pilar utama: **Standar Pengkodean Backend & Best Practices (R1)**, **Performa & Efisiensi Database Backend (R2)**, serta **UI/UX & Logika Client-Side Frontend (R3)**. Evaluasi ini bertujuan untuk mengidentifikasi celah keamanan, kemacetan performa (*performance bottleneck*), risiko memori (*OOM*), serta isu aksesibilitas dan antarmuka pengguna, lengkap dengan cuplikan kode eksisting dan solusi perbaikannya.

---

## Ringkasan Eksekutif

Audit komprehensif yang dilakukan terhadap modul Mailbox berhasil menemukan total **19 temuan audit**, dengan rincian tingkat keparahan (*severity level*) sebagai berikut:

- 🔴 **Critical**: **2 Temuan** (1 Backend Memory Exhaustion, 1 DOM-Based Stored XSS Frontend)
- 🟠 **High**: **6 Temuan** (3 Backend Database & IMAP Performance, 3 Frontend Async/State/UI Handlers)
- 🟡 **Medium**: **7 Temuan** (3 Backend Best Practices & Safety, 4 Frontend Layout/Binding/Styling/Filter)
- 🔵 **Low**: **4 Temuan** (2 Backend Abstraction & CLI Description, 2 Frontend Defensive Parsing & Avatar CDN)

---

## Tabel Ringkasan Temuan Audit

| ID | Judul Temuan | Kategori | Severitas | Lokasi File |
| :--- | :--- | :--- | :--- | :--- |
| **BE-01** | Pengambilan Seluruh Data Email Tanpa Paginasi (`->get()`) Mengakibatkan Memory Exhaustion & Monolithic JSON Payload | R2: Performa & DB | 🔴 **Critical** | `app/Http/Controllers/MailboxController.php:12` |
| **FE-01** | Kerentanan DOM-Based XSS pada Rendering Email Body (`x-html`) Tanpa Sanitasi DOMPurify | R3: UI/UX & Frontend | 🔴 **Critical** | `resources/views/mailbox/index.blade.php:275, 405-449` |
| **BE-02** | Masalah Kueri N+1 pada Pencarian User dalam Loop Pemrosesan Email Catch-All | R2: Performa & DB | 🟠 **High** | `app/Services/ProcessCatchAllEmailService.php:64` |
| **BE-03** | Risiko Out-Of-Memory (OOM) Akibat Penarikan Seluruh Folder IMAP Tanpa Chunking/Filtering | R2: Performa & DB | 🟠 **High** | `app/Services/ProcessCatchAllEmailService.php:24` |
| **BE-04** | Kurangnya Indeks Database (DB Indexes) untuk Query Sort, Filtering, dan Cleanup Email | R2: Performa & DB | 🟠 **High** | `database/migrations/2026_08_09_101751_create_captured_emails_table.php` |
| **FE-02** | Masalah Sinkronisasi State AlpineJS Antara Array `emails` dan Object Reference `selectedEmail` | R3: UI/UX & Frontend | 🟠 **High** | `resources/views/mailbox/index.blade.php:308-393` |
| **FE-03** | Penanganan Error Asinkron Menggunakan `alert()` Bawaan Browser Tanpa Indicator Loading UI | R3: UI/UX & Frontend | 🟠 **High** | `resources/views/mailbox/index.blade.php:357-366, 381-391` |
| **FE-04** | Komponen Tombol Interaktif Tanpa Event Handler (Dead Action Buttons) | R3: UI/UX & Frontend | 🟠 **High** | `resources/views/mailbox/index.blade.php:40, 61-68, 87-94, 177-181` |
| **BE-05** | Absensi Perlindungan Cron Overlapping (`withoutOverlapping`) pada Jadwal Penarikan Email 1 Menit | R2: Performa & DB | 🟡 **Medium** | `routes/console.php:15` |
| **BE-06** | Tidak Adanya Validasi Input (Request Validation) dan Type Casting pada Controller Status Email | R1: Best Practices | 🟡 **Medium** | `app/Http/Controllers/MailboxController.php:19, 26` |
| **BE-07** | Pencampuran Logika Output Terminal (`echo`) pada Service Layer Mengabaikan Separation of Concerns | R1: Best Practices | 🟡 **Medium** | `app/Services/ProcessCatchAllEmailService.php:14, 19, 25` |
| **FE-05** | Tata Letak Responsive Bermasalah & Penggunaan Inline Style `style="display: none;"` Redundan Tanpa Global CSS `[x-cloak]` | R3: UI/UX & Frontend | 🟡 **Medium** | `resources/views/layouts/mailbox-layout.blade.php:1-35` |
| **FE-06** | Higiene Data Binding Campuran (Blade String Interpolation dalam Template Literal JS) | R3: UI/UX & Frontend | 🟡 **Medium** | `resources/views/mailbox/index.blade.php:236` |
| **FE-07** | Penggunaan Palet Warna Hex Manual (Hardcoded Hex) Menyebabkan Ketidakseragaman UI & Menyulitkan Mode Gelap | R3: UI/UX & Frontend | 🟡 **Medium** | `resources/views/mailbox/index.blade.php:5, 59, 72, 108` |
| **FE-08** | Potensi Implikasi Performa & Alokasi Memori pada Getter `filteredEmails` | R3: UI/UX & Frontend | 🟡 **Medium** | `resources/views/mailbox/index.blade.php:314-337` |
| **BE-08** | Penggunaan Otorisasi Manual Berulang Tanpa Laravel Policy / Gate Abstraction | R1: Best Practices | 🔵 **Low** | `app/Http/Controllers/MailboxController.php:18, 25` |
| **BE-09** | Discrepancy Deskripsi Command CLI dan Kurangnya DB Batching Transaction | R1 & R2 | 🔵 **Low** | `app/Console/Commands/PullMailboxEmailsCommand.php:10` |
| **FE-09** | Risiko Parsing Date Mengembalikan String `"Invalid Date"` pada Format Tanggal | R3: UI/UX & Frontend | 🔵 **Low** | `resources/views/mailbox/index.blade.php:394-404` |
| **FE-10** | Ketergantungan Eksternal CDN Tanpa Fallback Asinkron untuk Avatar Pengguna | R3: UI/UX & Frontend | 🔵 **Low** | `resources/views/mailbox/index.blade.php:195` |

---

## Bab 1: Analisis Standar Pengkodean (Best Practices - R1)

### BE-06: Tidak Adanya Validasi Input (Request Validation) dan Type Casting pada Controller Status Email

- **Tingkat Keparahan**: 🟡 `Medium`
- **Kategori**: R1: Standar Pengkodean (Best Practices)
- **Lokasi File**: `app/Http/Controllers/MailboxController.php` (Baris 19 & 26)

#### Deskripsi Masalah & Analisis Dampak
Pada method `toggleRead` dan `toggleStarred`:
```php
$email->update(['is_read' => $request->is_read]);
$email->update(['is_starred' => $request->is_starred]);
```
Controller langsung menggunakan properti `$request->is_read` dan `$request->is_starred` tanpa melakukan validasi tipe data (`boolean`, `required`). Jika request mengirimkan payload yang tidak valid (string acak, null, atau array), pembaruan data dapat menghasilkan behavior yang tidak konsisten pada database.

#### Cuplikan Kode Saat Ini
```php
// app/Http/Controllers/MailboxController.php:16-31
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
// app/Http/Controllers/MailboxController.php:16-35
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

- **Tingkat Keparahan**: 🟡 `Medium`
- **Kategori**: R1: Standar Pengkodean (Best Practices)
- **Lokasi File**: `app/Services/ProcessCatchAllEmailService.php` (Baris 14, 19, 25, 30, 32, dll.)

#### Deskripsi Masalah & Analisis Dampak
`ProcessCatchAllEmailService` menggunakan pernyataan `echo` langsung ke konsol terminal. Hal ini melanggar *Single Responsibility Principle* (SRP). Ketika service dipanggil di luar CLI (seperti Queue Worker), pernyataan `echo` akan merusak output buffer dan tidak tercatat pada file log aplikasi Laravel (`storage/logs/laravel.log`).

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
        } catch (\Exception $e) {
            Log::error('IMAP Catch-All Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
```

---

### BE-08: Penggunaan Otorisasi Manual Berulang Tanpa Laravel Policy / Gate Abstraction

- **Tingkat Keparahan**: 🔵 `Low`
- **Kategori**: R1: Standar Pengkodean (Best Practices)
- **Lokasi File**: `app/Http/Controllers/MailboxController.php` (Baris 18 & 25)

#### Deskripsi Masalah & Analisis Dampak
Pengecekan hak akses `if ($email->user_id !== Auth::id()) abort(403);` ditulis secara hardcode di controller. Hal ini menyulitkan pengembangan di masa depan jika ada role khusus seperti Admin/Superadmin yang membutuhkan akses ke mailbox pengguna lain.

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

- **Tingkat Keparahan**: 🔵 `Low`
- **Kategori**: R1: Best Practices & R2: Performa
- **Lokasi File**: `app/Console/Commands/PullMailboxEmailsCommand.php` (Baris 10) & `app/Services/ProcessCatchAllEmailService.php` (Baris 82-92)

#### Deskripsi Masalah & Analisis Dampak
Deskripsi atribut `PullMailboxEmailsCommand` menuliskan `Pull unseen emails from catch-all IMAP server`, namun service mengeksekusi `->all()->get()` (menarik seluruh email). Selain itu, penyimpan email dilakukan satu per satu tanpa dibungkus `DB::transaction()`.

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

---

## Bab 2: Analisis Performa & Efisiensi Backend (R2)

### BE-01: Pengambilan Seluruh Data Email Tanpa Paginasi (`->get()`) Mengakibatkan Memory Exhaustion & Monolithic JSON Payload

- **Tingkat Keparahan**: 🔴 `Critical`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `app/Http/Controllers/MailboxController.php` (Baris 12) & `resources/views/mailbox/index.blade.php` (Baris 310)

#### Deskripsi Masalah & Analisis Dampak
Method `index()` memanggil `->get()` tanpa paginasi untuk seluruh record `capturedEmails`. Kolom `message_content` yang berisi string HTML email utuh ditarik sekaligus dan di-serialize menjadi JSON raksasa `{{ Js::from($emails) }}` di Blade view. Seiring waktu, hal ini mengakibatkan **Memory Exhaustion (Out Of Memory)** di PHP server dan **Browser DOM Freezing**.

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
// app/Http/Controllers/MailboxController.php:10-17
public function index()
{
    $emails = Auth::user()->capturedEmails()
        ->select(['id', 'user_id', 'sender_address', 'subject', 'received_at', 'is_read', 'is_starred'])
        ->orderBy('received_at', 'desc')
        ->paginate(50);

    return view('mailbox.index', compact('emails'));
}
```

---

### BE-02: Masalah Kueri N+1 pada Pencarian User dalam Loop Pemrosesan Email Catch-All

- **Tingkat Keparahan**: 🟠 `High`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `app/Services/ProcessCatchAllEmailService.php` (Baris 64)

#### Deskripsi Masalah & Analisis Dampak
Service melakukan kueri database `User::where('email', $emailAddress)->first()` di dalam perulangan `foreach` untuk setiap alamat email tujuan. Jika terdapat 200 email di INBOX, service mengeksekusi **200+ kueri SELECT individual**, memicu N+1 Database Query problem dan memperlambat sync job.

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
// Pre-load seluruh mapping email -> user_id ke memory (1 kueri)
$userMap = User::pluck('id', 'email')
    ->keyBy(fn ($id, $email) => strtolower(trim($email)));

foreach ($messages as $message) {
    foreach ($toAddresses as $to) {
        $emailAddress = strtolower(trim($to->mail));
        $userId = $userMap->get($emailAddress);

        if ($userId) {
            // User ditemukan tanpa kueri tambahan!
```

---

### BE-03: Risiko Out-Of-Memory (OOM) Akibat Penarikan Seluruh Folder IMAP Tanpa Chunking/Filtering

- **Tingkat Keparahan**: 🟠 `High`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `app/Services/ProcessCatchAllEmailService.php` (Baris 24)

#### Deskripsi Masalah & Analisis Dampak
Service menarik seluruh pesan di folder INBOX menggunakan `$folder->query()->all()->get();`. Mengunduh ribuan pesan sekaligus dari server IMAP Hostinger akan dengan cepat melampaui `memory_limit` PHP CLI dan tidak memanfaatkan pembersihan memori `gc_collect_cycles()`.

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
// Hanya tarik email yang belum dibaca (UNSEEN) atau gunakan limit
$messages = $folder->query()->unseen()->limit(50)->get();

foreach ($messages as $message) {
    // Process message...
    
    unset($message, $content);
    if (gc_enabled()) {
        gc_collect_cycles();
    }
}
```

---

### BE-04: Kurangnya Indeks Database (DB Indexes) untuk Query Sort, Filtering, dan Cleanup Email

- **Tingkat Keparahan**: 🟠 `High`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `database/migrations/2026_08_09_101751_create_captured_emails_table.php` (Baris 14-22)

#### Deskripsi Masalah & Analisis Dampak
Tabel `captured_emails` tidak memiliki indeks pada kolom `received_at`, `is_read`, maupun `is_starred`. Hal ini menyebabkan operasi pembersihan email harian (`CleanExpiredEmailsCommand`) dan sorting Inbox melakukan **Full Table Scan** dan **filesort** di MySQL.

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
Buat migration baru `add_indexes_to_captured_emails_table.php`:
```php
Schema::table('captured_emails', function (Blueprint $table) {
    $table->index(['user_id', 'received_at'], 'idx_captured_emails_user_received');
    $table->index('received_at', 'idx_captured_emails_received_at');
    $table->index(['user_id', 'is_read'], 'idx_captured_emails_user_read');
    $table->index(['user_id', 'is_starred'], 'idx_captured_emails_user_starred');
});
```

---

### BE-05: Absensi Perlindungan Cron Overlapping (`withoutOverlapping`) pada Jadwal Penarikan Email 1 Menit

- **Tingkat Keparahan**: 🟡 `Medium`
- **Kategori**: R2: Performa & Efisiensi Backend
- **Lokasi File**: `routes/console.php` (Baris 15)

#### Deskripsi Masalah & Analisis Dampak
Jadwal cron `Schedule::command('app:pull-mailbox-emails')->everyMinute();` tidak menggunakan `withoutOverlapping()`. Jika penarikan email membutuhkan waktu lebih dari 60 detik, instance cron baru akan berjalan bersamaan, memicu kecemasan resource CPU/RAM dan race condition pada database.

#### Cuplikan Kode Saat Ini
```php
// routes/console.php:14-16
Schedule::command('app:pull-mailbox-emails')->everyMinute();
Schedule::command('app:clean-expired-emails')->daily();
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```php
// routes/console.php:14-16
Schedule::command('app:pull-mailbox-emails')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->onOneServer();

Schedule::command('app:clean-expired-emails')->daily();
```

---

## Bab 3: Analisis UI/UX & Logika Frontend (R3)

### FE-01: Kerentanan DOM-Based XSS pada Rendering Email Body (`x-html`) Tanpa Sanitasi DOMPurify

- **Tingkat Keparahan**: 🔴 `Critical`
- **Kategori**: R3: UI/UX & Logika Frontend (Keamanan)
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 275 & 405-449)

#### Deskripsi Masalah & Analisis Dampak
Fungsi `formatEmailBody(content)` langsung mengembalikan string HTML tanpa sanitasi jika terdeteksi tag dasar (`<p>`, `<div>`). Penggunaan `x-html="formatEmailBody(...)"` memungkinkan penyerang mengeksekusi payload Stored DOM XSS (seperti `<script>` atau `<img src=x onerror=...>`) pada browser pengguna.

#### Cuplikan Kode Saat Ini
```javascript
// resources/views/mailbox/index.blade.php:405-413
formatEmailBody(content) {
    if (!content) return '';
    
    if (/<(br|p|div|html|body|table|a|span|blockquote)[^>]*>/i.test(content)) {
        return content;
    }
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Tambahkan library DOMPurify pada `mailbox-layout.blade.php`, lalu perbaiki fungsi `formatEmailBody`:
```javascript
// resources/views/mailbox/index.blade.php
formatEmailBody(content) {
    if (!content) return '';
    
    let html = content;
    if (!/<(br|p|div|html|body|table|a|span|blockquote)[^>]*>/i.test(content)) {
        let escaped = content.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        html = escaped.split(/\r?\n/).join('<br>');
    }
    
    return typeof DOMPurify !== 'undefined' 
        ? DOMPurify.sanitize(html, { ADD_ATTR: ['target'] })
        : html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
}
```

---

### FE-02: Masalah Sinkronisasi State AlpineJS Antara Array `emails` dan Object Reference `selectedEmail`

- **Tingkat Keparahan**: 🟠 `High`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 308-393)

#### Deskripsi Masalah & Analisis Dampak
State `selectedEmail: null` menyimpan referensi objek terpisah saat `selectEmail(id)` dipanggil. Ketika terjadi rollback status pada `toggleRead` / `toggleStar`, mutasi hanya memicu perbaikan di array `emails`, sehingga panel pembaca email di sebelah kanan menjadi desinkronisasi dari list inbox.

#### Cuplikan Kode Saat Ini
```javascript
// resources/views/mailbox/index.blade.php:308-320
function mailboxApp() {
    return {
        selectedEmail: null,
        emails: {{ Js::from($emails) }},
        selectEmail(id) {
            this.selectedEmail = this.emails.find(e => e.id === id);
        }
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Gunakan Primitive `selectedEmailId` dan buat getter reaktif `selectedEmail`:
```javascript
function mailboxApp() {
    return {
        selectedEmailId: null,
        emails: {{ Js::from($emails) }},
        
        get selectedEmail() {
            return this.emails.find(e => e.id === this.selectedEmailId) || null;
        },
        
        selectEmail(id) {
            this.selectedEmailId = id;
            if (this.selectedEmail && !this.selectedEmail.is_read) {
                this.toggleRead(id, true);
            }
        }
    }
}
```

---

### FE-03: Penanganan Error Asinkron Menggunakan `alert()` Bawaan Browser Tanpa Indicator Loading UI

- **Tingkat Keparahan**: 🟠 `High`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 357-366 & 381-391)

#### Deskripsi Masalah & Analisis Dampak
Permintaan AJAX `fetch()` pada `toggleRead` dan `toggleStar` tidak menyajikan loading indicator visual dan mengeksekusi blocking `alert()` native browser saat error terjadi, merusak pengalaman pengguna.

#### Cuplikan Kode Saat Ini
```javascript
// resources/views/mailbox/index.blade.php:360-366
if (!res.ok) {
    email.is_read = !status;
    let text = await res.text();
    alert(`Gagal menyimpan status (Error ${res.status}): ${text.substring(0, 50)}`);
}
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Ganti `alert()` dengan Toast Notification non-blocking:
```html
<!-- Toast UI Component -->
<div x-show="toast.show" x-cloak 
     class="fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl shadow-lg text-sm text-white"
     :class="toast.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
     x-text="toast.message">
</div>
```
```javascript
// Helper di AlpineJS App
showToast(message, type = 'error') {
    this.toast = { show: true, message, type };
    setTimeout(() => { this.toast.show = false; }, 3500);
}
```

---

### FE-04: Komponen Tombol Interaktif Tanpa Event Handler (Dead Action Buttons)

- **Tingkat Keparahan**: 🟠 `High`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 40, 61-68, 87-94, 177-181)

#### Deskripsi Masalah & Analisis Dampak
Beberapa tombol UI penting, seperti Bulk Action Toolbar (Tandai Belum Dibaca, Hapus, Pindahkan), Navigasi Sidebar "Marked", dan Tombol Aksi Detail Email (Delete/Archive/Reply) tidak memiliki event handler `@click` sama sekali.

#### Cuplikan Kode Saat Ini
```html
<!-- resources/views/mailbox/index.blade.php:87-94 -->
<div x-show="checkedEmails.length > 0" x-cloak class="flex items-center gap-3 shrink-0 text-slate-700">
    <button class="p-1 hover:bg-slate-300 rounded" title="Tandai Belum Dibaca"><svg ...></svg></button>
    <button class="p-1 hover:bg-slate-300 rounded" title="Hapus"><svg ...></svg></button>
</div>
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```html
<div x-show="checkedEmails.length > 0" x-cloak class="flex items-center gap-3 shrink-0 text-slate-700">
    <button @click="bulkToggleRead(false)" class="p-1 hover:bg-slate-300 rounded" title="Tandai Belum Dibaca">
        <svg ...></svg>
    </button>
    <button @click="showToast('Fitur hapus massal belum tersedia', 'info')" class="p-1 hover:bg-slate-300 rounded" title="Hapus">
        <svg ...></svg>
    </button>
</div>
```

---

### FE-05: Tata Letak Responsive Bermasalah & Penggunaan Inline Style `style="display: none;"` Redundan Tanpa Global CSS `[x-cloak]`

- **Tingkat Keparahan**: 🟡 `Medium`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/layouts/mailbox-layout.blade.php` & `resources/views/mailbox/index.blade.php:171`

#### Deskripsi Masalah & Analisis Dampak
Elemen UI menggunakan atribut manual `style="display: none;"` berdampingan dengan `x-show` dan `x-cloak`. Selain itu, layout utama tidak mendefinisikan aturan CSS global `[x-cloak] { display: none !important; }`, memicu kedipan tampilan (FOUC).

#### Cuplikan Kode Saat Ini
```html
<!-- resources/views/mailbox/index.blade.php:171 -->
<button class="md:hidden p-2 -ml-2 rounded-lg text-slate-400" x-show="selectedEmail" @click="selectedEmail = null" style="display: none;">
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Tambahkan CSS global pada `mailbox-layout.blade.php`:
```html
<style>
    [x-cloak] { display: none !important; }
</style>
```
Dan bersihkan inline style di `index.blade.php`:
```html
<button class="md:hidden p-2 -ml-2 rounded-lg text-slate-400" x-show="selectedEmail" x-cloak @click="selectedEmailId = null">
```

---

### FE-06: Higiene Data Binding Campuran (Blade String Interpolation dalam Template Literal JS)

- **Tingkat Keparahan**: 🟡 `Medium`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 236)

#### Deskripsi Masalah & Analisis Dampak
Baris 236 menggunakan `x-text="`{{ auth()->user()->email }} `"` yang mencampurkan evaluasi Blade di dalam JavaScript Template Literal, yang berisiko merusak sintaks JS client jika email berisi karakter tertentu.

#### Cuplikan Kode Saat Ini
```html
<span x-text="`{{ auth()->user()->email }} `"></span>
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```html
<span>{{ auth()->user()->email }}</span>
```

---

### FE-07: Penggunaan Palet Warna Hex Manual (Hardcoded Hex) Menyebabkan Ketidakseragaman UI & Menyulitkan Mode Gelap

- **Tingkat Keparahan**: 🟡 `Medium`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 5, 59, 72, 108)

#### Deskripsi Masalah & Analisis Dampak
Penggunaan kelas CSS hex manual seperti `bg-[#e2e4e7]` dan `bg-[#F9FAFB]` merusak konsistensi visual palet warna Slate Tailwind dan menyulitkan dukungan Dark Mode.

#### Cuplikan Kode Saat Ini
```html
<div class="px-4 py-3 flex items-center justify-between border-b border-slate-200 shrink-0 bg-[#e2e4e7]">
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```html
<div class="px-4 py-3 flex items-center justify-between border-b border-slate-200 shrink-0 bg-slate-100">
```

---

### FE-08: Potensi Implikasi Performa & Alokasi Memori pada Getter `filteredEmails`

- **Tingkat Keparahan**: 🟡 `Medium`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 314-337)

#### Deskripsi Masalah & Analisis Dampak
Getter `filteredEmails` mengeksekusi `.filter()` dan `.toLowerCase()` secara berulang-ulang pada setiap re-render AlpineJS. Getter `allChecked` juga memanggil `filteredEmails` dua kali secara beruntun.

#### Cuplikan Kode Saat Ini
```javascript
// resources/views/mailbox/index.blade.php:324-337
get filteredEmails() {
    let list = this.emails;
    if (this.search !== '') {
        list = list.filter(e => e.subject?.toLowerCase().includes(this.search.toLowerCase()) || e.sender_address.toLowerCase().includes(this.search.toLowerCase()));
    }
    if (this.filterMode === 'unread') list = list.filter(e => !e.is_read);
    return list;
}
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Optimalkan penyaringan ke dalam 1-pass filter:
```javascript
get filteredEmails() {
    const query = this.search.trim().toLowerCase();
    const mode = this.filterMode;

    return this.emails.filter(email => {
        const matchesSearch = !query || 
            (email.subject && email.subject.toLowerCase().includes(query)) || 
            (email.sender_address && email.sender_address.toLowerCase().includes(query));

        if (!matchesSearch) return false;
        if (mode === 'unread') return !email.is_read;
        if (mode === 'read') return email.is_read;
        if (mode === 'starred') return email.is_starred;
        return true;
    });
}
```

---

### FE-09: Risiko Parsing Date Mengembalikan String `"Invalid Date"` pada Format Tanggal

- **Tingkat Keparahan**: 🔵 `Low`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 394-404)

#### Deskripsi Masalah & Analisis Dampak
Jika data `received_at` bernilai `null` atau string yang tidak valid, fungsi `toLocaleDateString()` akan menampilkan string `"Invalid Date"` secara mentah pada UI.

#### Cuplikan Kode Saat Ini
```javascript
formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
```javascript
formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '-';
    
    const today = new Date();
    if (d.toDateString() === today.toDateString()) {
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
```

---

### FE-10: Ketergantungan Eksternal CDN Tanpa Fallback Asinkron untuk Avatar Pengguna

- **Tingkat Keparahan**: 🔵 `Low`
- **Kategori**: R3: UI/UX & Logika Frontend
- **Lokasi File**: `resources/views/mailbox/index.blade.php` (Baris 195)

#### Deskripsi Masalah & Analisis Dampak
Gambar avatar pengguna diambil langsung dari service eksternal `https://ui-avatars.com`. Jika koneksi eksternal terputus, avatar akan tampil sebagai gambar rusak (broken image).

#### Cuplikan Kode Saat Ini
```html
<img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff" alt="Avatar" class="w-8 h-8 rounded-full border border-slate-200">
```

#### Cuplikan Kode Perbaikan yang Direkomendasikan
Gunakan avatar inisial CSS/Blade internal:
```html
<div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xs border border-slate-200 shrink-0">
    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
</div>
```

---

## Bab 4: Panduan Prioritas Remediasi & Roadmap Implementasi

### Tahap 1: Tindakan Segera (Critical & High Priority) - Target: 1-2 Hari
1. **Keamanan Frontend (FE-01)**: Pasang pustaka `DOMPurify` pada layout dan sanitasi rendering `x-html` di `formatEmailBody`.
2. **Memory Exhaustion Backend (BE-01)**: Tambahkan paginasi (`paginate(50)`) dan batasi kolom `select()` pada `MailboxController::index()`.
3. **Database Performance (BE-02 & BE-04)**:
   - Buat migration penambahan indeks database pada tabel `captured_emails` (`user_id`, `received_at`, `is_read`, `is_starred`).
   - Lakukan refactoring pada `ProcessCatchAllEmailService` untuk mem-preload user mapping (`$userMap`), menghilangkan kueri N+1.
4. **Reliabilitas Job & State UI (BE-03, FE-02, FE-03, FE-04)**:
   - Batasi IMAP query fetch (`unseen()->limit(50)`) dan panggil garbage collector.
   - Refactor state AlpineJS menggunakan primitive `selectedEmailId` dan getter reaktif `selectedEmail`.
   - Ganti `alert()` browser dengan Toast Notification UI.
   - Hubungkan event `@click` pada seluruh Dead Action Buttons.

### Tahap 2: Prioritas Menengah (Medium Priority) - Target: 3-5 Hari
1. **Pencegahan Race Condition (BE-05)**: Tambahkan `withoutOverlapping(10)` pada jadwal penarikan email di `routes/console.php`.
2. **Validasi & Kebersihan Logika Backend (BE-06 & BE-07)**:
   - Terapkan Request Validation pada `MailboxController::toggleRead` dan `toggleStarred`.
   - Ganti `echo` pada Service Layer dengan panggilan `Log::info()` / `Log::error()`.
3. **Peningkatan Kualitas UI/UX Frontend (FE-05, FE-06, FE-07, FE-08)**:
   - Definisikan aturan CSS global `[x-cloak] { display: none !important; }` pada `mailbox-layout.blade.php`.
   - Rapikan sintaks data binding Blade-JS pada baris 236.
   - Ganti hardcoded hex (`bg-[#e2e4e7]`) dengan kelas Tailwind Slate semantic.
   - Optimalkan getter `filteredEmails` menjadi 1-pass filter.

### Tahap 3: Prioritas Rendah & Polishing (Low Priority) - Target: 1 Minggu
1. **Arsitektur Otorisasi (BE-08 & BE-09)**:
   - Buat `CapturedEmailPolicy` dan terapkan `$this->authorize('update', $email)`.
   - Sesuaikan deskripsi `PullMailboxEmailsCommand`.
2. **Ketahanan Frontend (FE-09 & FE-10)**:
   - Tambahkan Defensive Date Checking pada fungsi `formatDate`.
   - Ganti gambar avatar CDN dengan komponen avatar inisial CSS/Blade internal.

---

*Laporan audit ini dihasilkan secara otomatis oleh Mailbox Code Review Teamwork Agent.*
