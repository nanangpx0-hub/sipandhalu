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

## Pindah ke Komputer Lain

Panduan lengkap (2 jalur + verifikasi + troubleshooting + FAQ):
**[docs/PINDAH-KOMPUTER.md](PINDAH-KOMPUTER.md)**

Ringkasnya:

### Jalur A — Install Bersih dari Repo (tanpa data kerja)

```bash
git clone https://github.com/nanangpx0-hub/sipandhalu.git
cd sipandhalu
composer install
copy .env.example .env          # sesuaikan DB_USER/DB_PASS
# lalu lanjut "Langkah Instalasi" no. 3-5 di atas (buat DB + migrate + seed berurutan)
```

Hasilnya identik dengan kondisi awal karena semua data dibangun dari seed yang ikut repo.
Yang TIDAK ikut git: `.env` (buang dari `.env.example`), `vendor/` (composer install),
`storage/logs` (otomatis), `data/` (sengaja diabaikan — berisi dokumen BPS RAHASIA;
salin manual lewat flashdisk jika perlu).

### Jalur B — Bawa Database Aktual (dump/restore)

Di komputer lama:
```bash
mysqldump -u root --single-transaction --routines --triggers --databases sipandhalu > sipandhalu-backup.sql
```

Di komputer baru (setelah clone + composer + `.env`):
```bash
mysql -u root < sipandhalu-backup.sql
# file dump self-contained: sudah menyertakan CREATE DATABASE + USE sipandhalu
```

File backup contoh tersedia di `storage/backup/sipandhalu-2026-09-10.sql`
(sudah diuji restore: 51 orang, 13 users, 18 kecamatan, 27 desa, 28 SLS, 3 periode,
66 sampel, 66 penugasan). Folder `storage/backup` tidak ikut git — salin manual.

> **Catatan sensitivitas:** dump berisi data yang sama dengan seed (nama petugas, SLS,
> hasil pemutakhiran). Simpan seperti dokumen kerja BPS — jangan di-commit atau
> diunggah ke tempat publik, terutama bila nanti berisi data hasil lapangan asli.

### Verifikasi Setelah Pindah

```bash
php vendor/phpunit/phpunit/phpunit --testdox   # OK (8 tests, 34 assertions)
php tests/smoke_tahap1.php                      # 10/10 OK
```

Lalu login dengan akun demo (lihat [Panduan Penggunaan](PANDUAN-PENGGUNAAN.md)).

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
