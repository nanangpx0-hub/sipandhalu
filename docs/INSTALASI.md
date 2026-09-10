# Instalasi & Konfigurasi

## Syarat

| Komponen | Versi | Catatan |
|---|---|---|
| PHP | 8.2+ | ekstensi `pdo_mysql`, `mbstring`, `openssl` |
| MySQL | 8.0+ | charset `utf8mb4`, collation `utf8mb4_unicode_ci` |
| Composer | 2.x | untuk autoload + PHPUnit (dev) |
| Laragon | full | lingkungan pengembangan yang dipakai |

## Langkah Instalasi

```bash
# 1. Dependensi
composer install

# 2. Salin konfigurasi environment
copy .env.example .env      # sesuaikan DB_USER/DB_PASS bila perlu

# 3. Buat database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
# (opsional) database khusus test:
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sipandhalu_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 4. Migrasi skema (idempoten)
php database/migrate.php
#    --fresh = drop semua tabel lalu buat ulang (HATI-HATI, hapus data)

# 5. Seed — jalankan BERURUTAN, jangan paralel (race condition)
php database/seeds/seed_tahap1.php        # roles + 8 pengolah + alias + users
php database/seeds/002_wilayah_seed.php   # 18 kecamatan, 27 desa, 28 SLS, periode 2026-S2 + sampel
php database/seeds/003_sls_pdf_seed.php   # pad SLS 56361 dari PDF BPS (kode_full, RT/RW/dusun)
php database/seeds/004_dummy_seed.php     # DUMMY: PCL/PML, akun demo, periode S1+Q1, penugasan
```

## Menjalankan Aplikasi

**Opsi A — Laragon vhost (disarankan):** arahkan DocumentRoot ke
`c:\laragon\www\susenas-seruti\public` lalu buka `http://susenas-seruti.test`.

**Opsi B — PHP built-in:**
```bash
php -S localhost:8091 -t public
# buka http://localhost:8091
```

> Hanya folder `public/` yang boleh terekspos web server.

## Testing

```bash
php vendor/phpunit/phpunit/phpunit --testdox   # unit + integrasi (DB sipandhalu_test)
php tests/smoke_tahap1.php                     # smoke CLI 10 cek (DB dev)
```

## Konfigurasi Environment (`.env`)

| Variabel | Dev | Produksi |
|---|---|---|
| `APP_ENV` | development | production |
| `APP_DEBUG` | true | **false** (wajib) |
| `DB_HOST` / `DB_PORT` | 127.0.0.1 / 3306 | sesuai server |
| `DB_NAME` | sipandhalu | sipandhalu |
| `DB_USER` / `DB_PASS` | root / (kosong) | user khusus minimal privilege |
| `DB_CHARSET` | utf8mb4 | utf8mb4 |

Catatan produksi: `display_errors=Off`, opcache aktif, HTTPS (cookie `secure`),
backup `mysqldump --single-transaction` berkala, log di `storage/logs/` (blokir akses web).
