# Panduan Pindah Komputer

Panduan lengkap memindahkan **SIPANDHALU** (kode + database) ke komputer lain —
misalnya dari komputer kantor ke laptop, atau ke komputer rekan.

## 1. Syarat di Komputer Baru

| Komponen | Versi | Cara cek |
|---|---|---|
| PHP | 8.2+ | `php -v` |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `zip`, `xml`, `fileinfo`, `gd` | `php -m` |
| MySQL | 8.0+ | `mysql --version` |
| Composer | 2.x | `composer --version` |
| Git | bebas | `git --version` |

> Cara termudah: install **Laragon Full** — sekali install otomatis dapat PHP 8.2 + MySQL 8 + composer.

## 2. Pilih Jalur

| Kondisi | Jalur yang dipakai |
|---|---|
| Belum ada data kerja / mau kondisi awal (data dummy seed) | **Jalur A — Clone + Seed** |
| Sudah ada data kerja asli (hasil input lapangan) di komputer lama | **Jalur B — Dump/Restore** |
| Mau kode terbaru TAPI data tetap | Jalur B untuk data, lalu `git pull` untuk kode |

## 3. Jalur A — Clone + Seed (install bersih)

```bash
# 1. Ambil kode
git clone https://github.com/nanangpx0-hub/sipandhalu.git
cd sipandhalu

# 2. Dependensi PHP
composer install

# 3. Konfigurasi environment
copy .env.example .env        # Linux/Mac: cp .env.example .env
# edit .env -> sesuaikan DB_USER / DB_PASS komputer baru

# 4. Database + skema + seed (URUTAN PENTING, jangan paralel)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php database/migrate.php
php database/seeds/seed_tahap1.php
php database/seeds/002_wilayah_seed.php
php database/seeds/003_sls_pdf_seed.php
php database/seeds/004_dummy_seed.php
```

Hasil akhir **identik** dengan kondisi awal: 18 kecamatan, 27 desa, 28 SLS,
3 periode, 66 sampel, 66 penugasan, 51 orang, 13 user (termasuk 4 akun demo).

### Yang TIDAK ikut git (perhatikan!)

| Item | Kenapa | Solusi |
|---|---|---|
| `.env` | berisi kredensial, di-ignore | buat dari `.env.example` |
| `vendor/` | dependensi composer | `composer install` |
| `storage/logs`, `storage/backup` | runtime + backup, di-ignore | salin manual jika perlu |
| `data/` | dokumen BPS RAHASIA (Excel alokasi, PDF VSEN26) | **salin manual lewat flashdisk** — sengaja tidak di-commit |

## 4. Jalur B — Bawa Database Aktual (dump/restore)

### Di komputer LAMA

```bash
mysqldump -u root --single-transaction --routines --triggers --databases sipandhalu > sipandhalu-backup.sql
```

Simpan `sipandhalu-backup.sql` ke flashdisk/cloud internal (bukan repo publik!).

### Di komputer BARU (setelah clone + composer install + .env)

```bash
mysql -u root < sipandhalu-backup.sql
```

> File dump **self-contained**: sudah menyertakan `CREATE DATABASE` + `USE sipandhalu`,
> jadi tidak perlu buat database manual dulu.

Contoh backup yang sudah terbukti valid tersedia di
`storage/backup/sipandhalu-2026-09-10.sql` (diuji restore identik:
51 orang, 13 users, 18 kecamatan, 27 desa, 28 SLS, 3 periode, 66 sampel, 66 penugasan).

### ⚠️ Sensitivitas dump

Isi dump = nama petugas + data SLS + hasil pemutakhiran (setara dokumen kerja BPS).
**Jangan** di-commit, di-upload ke repo publik, atau dikirim lewat channel terbuka.
Nanti bila sudah berisi data lapangan asli (nama KRT, no HP), berlaku label RAHASIA.

## 5. Arahkan Web Server

**Opsi A — Laragon vhost (disarankan):**
buat vhost dengan DocumentRoot mengarah ke `<folder-proyek>/public`
→ buka `http://sipandhalu.test` (sesuaikan nama).

**Opsi B — PHP built-in (cepat, tanpa konfigurasi):**
```bash
php -S localhost:8091 -t public
# buka http://localhost:8091
```

> Hanya folder `public/` yang boleh terekspos web server.

## 6. Verifikasi Setelah Pindah

```bash
# 1. Unit + integrasi (pakai DB sipandhalu_test)
php vendor/phpunit/phpunit/phpunit --testdox
#    harapan: OK (8 tests, 34 assertions)

# 2. Smoke terhadap DB dev
php tests/smoke_tahap1.php
#    harapan: 10/10 OK

# 3. Cek jumlah baris database (untuk Jalur B, harus sama dengan komputer lama)
mysql -u root sipandhalu -e "SELECT
  (SELECT COUNT(*) FROM orang)     AS orang,
  (SELECT COUNT(*) FROM users)     AS users,
  (SELECT COUNT(*) FROM kecamatan) AS kecamatan,
  (SELECT COUNT(*) FROM desa)      AS desa,
  (SELECT COUNT(*) FROM sls)       AS sls,
  (SELECT COUNT(*) FROM periode)   AS periode,
  (SELECT COUNT(*) FROM sampel)    AS sampel,
  (SELECT COUNT(*) FROM penugasan) AS penugasan"
```

Lalu login dengan akun demo — daftar lengkap di
[Panduan Penggunaan](PANDUAN-PENGGUNAAN.md). Yang paling cepat:

```
http://localhost:8091/login
pcl.demo@bpsjember.go.id / Jember3509
```

## 7. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Halaman blank / error 500 | `.env` belum ada atau salah kredensial | buat `.env` dari `.env.example`, cek `DB_USER`/`DB_PASS` |
| `Access denied for user 'root'` | MySQL komputer baru punya password root | isi `DB_PASS` di `.env` sesuai MySQL baru |
| `Unknown database 'sipandhalu'` | Jalur A: belum migrate; Jalur B: dump gagal | Jalur A: `php database/migrate.php`; Jalur B: ulangi `mysql -u root < dump.sql` |
| PHPUnit gagal total | DB `sipandhalu_test` belum ada skemanya | buat DB test + migrate (lihat `phpunit.xml`) |
| CSS berantakan | DocumentRoot tidak mengarah ke `public/` | pastikan vhost ke `<proyek>/public`, bukan root proyek |
| Error 419 saat submit form | session/CSRF bermasalah | izinkan cookie; konsisten satu host (jangan ganti localhost ↔ 127.0.0.1) |

## 8. FAQ

**T: Apakah harus pakai Laragon?**
J: Tidak. Yang penting PHP 8.2 + MySQL 8. Laragon hanya cara termudah di Windows; XAMPP (PHP 8.2) atau install manual juga bisa.

**T: Bisa dipakai banyak komputer sekaligus dengan data yang sama?**
J: Ya — arahkan `.env` semua komputer ke satu server MySQL yang sama (mis. satu komputer server di kantor, `DB_HOST` diisi IP server). Dump/restore bukan untuk sinkronisasi rutin.

**T: Password saya di komputer lama ikut pindah?**
J: Ikut, asal pakai **Jalur B** (hash tersimpan di tabel `users`). Kalau pakai **Jalur A**, semuanya kembali ke password seed (`must_reset` aktif lagi untuk admin + pengolah).

**T: Data di `storage/logs` perlu dibawa?**
J: Tidak wajib, itu hanya log aplikasi. Yang penting adalah **dump database**.

**T: Backup rutin di komputer kerja bagaimana?**
J: Berkala (mis. mingguan):
```bash
mysqldump -u root --single-transaction --routines --triggers --databases sipandhalu > storage/backup/sipandhalu-%DATE%.sql
```
Folder `storage/backup` di-ignore git — simpan salinan di drive lain/cloud internal.
