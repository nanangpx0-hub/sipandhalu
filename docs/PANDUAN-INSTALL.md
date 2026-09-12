# Panduan Instalasi & Deployment SIPANDHALU

## Tentang Dokumen Ini

Dokumen ini menjelaskan cara menginstal, mengkonfigurasi, dan men-deploy SIPANDHALU di komputer baru melalui alur kerja Git. Juga mencakup audit ringkas aplikasi, dependensi, arsitektur, performa, dan keamanan.

Referensi terkait:

- `docs/ANALISIS.md` — audit kode sumber, arsitektur, dependensi, performa, dan keamanan.
- `docs/DEPLOY.md` — setup cepat setelah clone/pull.
- `docs/INSTALASI.md`, `docs/PANDUAN-PENGGUNAAN.md`, `docs/SKEMA-DATABASE.md` — panduan operasional dan teknis lainnya.

---

## 1. Prasyarat

- **PHP 8.2+** (PHPUnit 10 membutuhkan PHP 8.1+, aplikasi membutuhkan 8.2).
- **MySQL 8+** dengan InnoDB dan `utf8mb4`.
- **Composer** tersedia di PATH.
- **Git**.
- Ekstensi PHP yang dibutuhkan: `mbstring`, `zlib`, `gd`, `zip` (PhpSpreadsheet), `json`, `PDO`, `pdo_mysql`.

Pada Laragon, ekstensi di atas sudah tersedia kecuali jika dimatikan secara manual.

---

## 2. Arsitektur Singkat

SIPANDHALU adalah aplikasi monolitik native PHP tanpa framework.

Alur request:

```
Browser → public/index.php
  → App\Core\Router
    → Middleware (Auth, CSRF, Role)
      → Controller → Service → Repository → PDO → MySQL
        → View .phtml
```

Komponen utama:

| Komponen | Lokasi | Fungsi |
|---|---|---|
| Front controller | `public/index.php` | Satu titik masuk semua request |
| Router | `App\Core\Router` | Routing manual dengan parametro `{id}` |
| Request/Response | `App\Core\Request`, `Response` | Objek request immutable dan helper keluaran |
| Session/CSRF | `App\Core\Session`, `Csrf` | Session hardening + token CSRF `hash_equals` |
| Database | `App\Core\Database`, `config/database.php` | PDO singleton + prepared statement |
| Model/Service/Repository | `app/Models`, `app/Services`, `app/Repositories` | Logika bisnis dan akses data |
| Views | `app/Views` | Template `.phtml`, semua output di-escape |
| Excel | `App\Core\Excel` | Impor/ekspor XLSX via PhpSpreadsheet |
| Tests | `tests/` | PHPUnit + CLI smoke test |

Skema database ada di `database/schema.sql` dan migrasi di `database/migrations/`. Data uji ada di `database/seeds/`.

---

## 3. Dependensi

`composer.json` hanya mengharuskan PHP `>=8.2`. Dependensi runtime yang terpasang:

- `phpoffice/phpspreadsheet` — untuk impor/ekspor Excel.

Dependensi pengembangan:

- `phpunit/phpunit` — testing unit/integrasi.

`vendor/` tidak di-commit. `composer.lock` di-commit agar versi library konsisten. Instal dependensi dengan:

```bash
composer install
```

---

## 4. Instalasi di PC Baru

### 4.1 Ambil kode dari Git

```bash
git clone https://github.com/nanangpx0-hub/sipandhalu.git
cd sipandhalu
```

Jika komputer sudah punya repo dan baru saja ditarik dari remote:

```bash
git pull origin main
```

### 4.2 Jalankan setup otomatis

**Windows:**

```cmd
scripts\setup.bat
```

**Linux/macOS:**

```bash
bash scripts/setup.sh
```

Script akan:

1. Menjalankan `composer install`.
2. Membuat database `sipandhalu` dan `sipandhalu_test`.
3. Menjalankan `php database/migrate.php`.
4. Menjalankan seed data dummy 001–004.
5. Membuat `.env` dari `.env.example` jika `.env` belum ada.

### 4.3 Atur variabel lingkungan jika perlu

Salin dan edit file environment:

```bash
cp .env.example .env
```

Contoh untuk Laragon:

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sipandhalu
DB_USER=root
DB_PASS=
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8091
```

**Jangan commit `.env` ke Git.** File `.env` sudah ada di `.gitignore`.

Variabel penting:

| Variabel | Keterangan |
|---|---|
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` | Kredensial koneksi MySQL. |
| `APP_ENV`, `APP_DEBUG` | `local/true` untuk development, `production/false` untuk produksi. |
| `APP_URL` | URL publik aplikasi. |
| `SESSION_SECURE` | `true` jika menggunakan HTTPS. |

### 4.4 Jalankan aplikasi

```bash
php -S localhost:8091 -t public
```

Buka `http://localhost:8091`.

---

## 5. Alur Kerja Git untuk Deploy

Setelah push ke remote, komputer lain cukup:

```bash
git clone https://github.com/nanangpx0-hub/sipandhalu.git
cd sipandhalu
scripts\setup.bat
php -S localhost:8091 -t public
```

Atau jika sudah ada repo lokal:

```bash
git pull origin main
scripts\setup.bat
```

Verifikasi yang sudah dilakukan:

- `git push origin main` berhasil ke `https://github.com/nanangpx0-hub/sipandhalu.git`.
- `git pull` di direktori uji menghasilkan kode terbaru.
- Setup di direktori uji menghasilkan database terintegrasi dan halaman login berfungsi.
- `phpunit` dan `tests/smoke_tahap1.php` lulus.

---

## 6. Migrasi dan Seed Data

Urutan penting, jangan paralel:

```bash
php database/migrate.php
php database/seeds/seed_tahap1.php
php database/seeds/002_wilayah_seed.php
php database/seeds/003_sls_pdf_seed.php
php database/seeds/004_dummy_seed.php
```

Migrasi bersifat idempoten. Untuk mengulang dari awal:

```bash
php database/migrate.php --fresh
```

Lalu jalankan seed dari tahap 1 sampai 004.

---

## 7. Pengujian

```bash
php vendor/phpunit/phpunit/phpunit --testdox
php tests/smoke_tahap1.php
```

Jika ingin hanya lint file PHP sumber tanpa vendor:

```bash
Get-ChildItem -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch '\\vendor\\' } | ForEach-Object { php -l $_.FullName }
```

---

## 8. Akun Demo

| Email | Password | Peran | Catatan |
|---|---|---|---|
| `admin@bpsjember.go.id` | `Admin3509!` | ADMIN | wajib ganti password pertama kali |
| `pcl.demo@bpsjember.go.id` | `Dummy3509!` | PCL | langsung masuk |
| `pml.demo@bpsjember.go.id` | `Dummy3509!` | PML | langsung masuk |
| `operator.demo@bpsjember.go.id` | `Dummy3509!` | OPERATOR | langsung masuk |
| `viewer.demo@bpsjember.go.id` | `Dummy3509!` | VIEWER | langsung masuk |
| 8 email pengolah dari sheet Rincian | `Jember3509` | PENGOLAH | wajib ganti password pertama kali |

---

## 9. Struktur Repository

```
public/            DocumentRoot
app/               kode sumber aplikasi
  Core/            router, request, session, DB, Excel, validator
  Controllers/     controller tipis
  Services/        logika bisnis
  Repositories/    akses data PDO
  Models/          entitas/enum
  Views/           template .phtml
config/            app, database, routes
database/          schema, migrations, migrate.php, seeds
docs/              dokumentasi
scripts/           setup, migrate, seed untuk Windows/Linux
tests/             PHPUnit dan smoke test
vendor/            dependensi Composer (di-ignore)
.env               kredensial lokal (di-ignore)
.env.example       template kredensial
composer.json      metadata dan dependensi
```

---

## 10. Keamanan yang Diterapkan

- Hash password Argon2id.
- Login generik untuk tidak membocorkan akun terdaftar.
- CSRF token pada semua POST/PUT/PATCH/DELETE dengan `hash_equals`.
- Session `HttpOnly`, `SameSite=Lax`, `Secure` opsional, `session_regenerate_id()` saat login.
- Header `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.
- Output view di-escape `htmlspecialchars(ENT_QUOTES, UTF-8)`.
- Audit trail JSON untuk manipulasi data, login/logout, reset password, dan impor.
- PDO prepared statement di seluruh repository.
- `.env`, `vendor/`, log, dan data sensitif BPS di-ignore dari Git.

---

## 11. Troubleshooting

### Akses denied untuk user `sipandhalu_app`
Pastikan `.env` menggunakan kredensial MySQL yang valid. Untuk Laragon lokal biasanya `root` tanpa password.

### Halaman menampilkan error autoload
Pastikan `composer install` sudah dijalankan dan `vendor/autoload.php` ada. Jangan commit `vendor/`.

### Port 8091 sudah digunakan
Gunakan port lain:

```bash
php -S localhost:8092 -t public
```

### Seed gagal karena data duplikat
Migrasi sudah idempoten, tetapi seed juga sebagian besar idempoten. Jalankan ulang seed yang gagal setelah perbaikan.

### Setelah `git pull`, aplikasi tidak jalan
Pastikan `.env` ada. `git pull` tidak menghapus `.env` karena file ini di-ignore. Jika hilang, salin dari `.env.example`.

```bash
cp .env.example .env
composer install
php database/migrate.php
php database/seeds/seed_tahap1.php && ...
```
