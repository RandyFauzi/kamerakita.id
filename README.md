# Kamerakita.ai - Agency Management Dashboard

Kamerakita.ai adalah sistem manajemen data data collector AI video (sejenis Atlas/Mytronlabs) skala agensi untuk mengelola mitra perekam, verifikasi QC berkas side-by-side, penggajian otomatis bulk payroll, dan visualisasi dasbor analitik hierarkis (Worker & Contributor).

---

## Persyaratan Sistem Produksi
- PHP >= 8.3 (dengan ekstensi GD enabled untuk kompresi foto bukti)
- Composer
- Node.js & NPM
- Database (SQLite, MySQL, atau PostgreSQL)

---

## Langkah Deployment di Server Produksi

### 1. Kloning Repositori & Instal Dependensi
Kloning kode sumber ke direktori web server Anda (cth: `/var/www/kamerakita`), kemudian jalankan perintah berikut:
```bash
composer install --no-dev --optimize-autoloader
npm install
```

### 2. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`, lalu sesuaikan variabel koneksi database dan kredensial utama:
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Migrasi & Seeding Database
Jalankan migrasi database produksi:
```bash
php artisan migrate --force
```

### 4. Build Aset Frontend
Lakukan kompilasi aset (Tailwind & JavaScript) untuk produksi menggunakan Vite:
```bash
npm run build
```

### 5. Hubungkan Symlink Storage (Penting)
Agar bukti gambar yang diunggah oleh Worker dapat diakses oleh Verifikator secara publik, buatlah symlink ke direktori publik:
```bash
php artisan storage:link
```

### 6. Caching Optimasi Produksi (Crucial)
Untuk performa maksimal di lingkungan produksi, jalankan perintah caching berikut:
```bash
# Cache Konfigurasi Aplikasi
php artisan config:cache

# Cache Rute/Routing Aplikasi
php artisan route:cache

# Cache File View Blade
php artisan view:cache
```

---

## Akun Demonstrasi Default (Testing)
- Super Admin: `randyfauzi24@gmail.com`
- Admin / QC: `admin@kamerakita.id`
- Finance: `finance@kamerakita.id`

> **PENTING**: Semua akun di atas secara bawaan menggunakan kata sandi `password`. **Ubah kata sandi ini segera** jika di-*deploy* ke *server production*!
- **Contributor**: `contributor1@kamerakita.id` (hingga `contributor5`)
- **Worker**: `worker1@kamerakita.id` (hingga `worker95`)
