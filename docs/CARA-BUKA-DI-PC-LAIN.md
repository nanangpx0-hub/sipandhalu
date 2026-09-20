# Cara Buka Proyek SIPANDHALU di PC / Laptop Lain

Tutorial langkah-demi-langkah untuk membuka proyek ini di komputer lain
(Windows + Laragon). Tidak perlu paham Git dalam-dalam — cukup ikuti urutan.

Repo: **https://github.com/nanangpx0-hub/sipandhalu.git**

> Sudah paham dasar? Versi lengkap (2 jalur + verifikasi + FAQ):
> [PINDAH-KOMPUTER.md](PINDAH-KOMPUTER.md)

---

## 1. Yang harus di-install dulu (sekali saja)

1. **Laragon Full** (sudah termasuk PHP 8.2 + MySQL 8 + Composer + Git)
   Download: https://laragon.org/download/
2. Pastikan Laragon berjalan (klik **Start All** sampai Apache + MySQL hijau).
3. Cek di terminal (klik kanan Laragon → Terminal, atau buka CMD):
   ```bash
   php -v        # harus 8.2.x
   composer --version
   git --version
   mysql --version
   ```

Ekstensi PHP yang wajib aktif (di Laragon Full umumnya sudah aktif):
`pdo_mysql`, `mbstring`, `openssl`, `zip`, `xml`, `fileinfo`, `gd`
Cek dengan `php -m`. Fitur **import/export Excel** butuh `zip` + `xml` + `gd`.

---

## 2. Download proyek dari GitHub (Jalur bersih — data awal)

Buka terminal Laragon, lalu:

```bash
cd C:\laragon\www
git clone https://github.com/nanangpx0-hub/sipandhalu.git
cd sipandhalu
```

> Folder hasil clone harus berisi `app/`, `public/`, `database/`, `composer.json`, `.env.example`.

---

## 3. Install dependensi PHP

```bash
composer install
```

Tunggu sampai selesai. Folder `vendor/` akan dibuat otomatis
(folder ini **tidak** ikut GitHub — wajar kalau tidak ada setelah clone).

---

## 4. Buat file `.env`

```bash
copy .env.example .env
```

Buka file `.env` dengan Notepad/VS Code. Untuk Laragon standar (MySQL tanpa password),
pastikan isinya seperti ini:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sipandhalu
DB_USER=root
DB_PASS=
```

> Kalau MySQL di PC baru pakai password root, isi `DB_PASS` dengan password tersebut.

---

## 5. Buat database + isi data awal (URUT, jangan dilompat)

Jalankan **berurutan satu per satu**:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php database/migrate.php
php database/seeds/seed_tahap1.php
php database/seeds/002_wilayah_seed.php
php database/seeds/003_sls_pdf_seed.php
php database/seeds/004_dummy_seed.php
```

Hasil akhir: 18 kecamatan, 27 desa, 28 SLS, 51 orang, 13 user.
Kalau ada tulisan `OK` / `selesai` tanpa error merah = berhasil.

---

## 6. Jalankan aplikasinya (pilih salah satu)

**Opsi A — paling mudah (tanpa setting apa pun):**

```bash
php -S localhost:8091 -t public
```

Buka browser: **http://localhost:8091/login**

**Opsi B — pakai Laragon vhost (URL cantik):**

1. Laragon → Menu → www → arahkan DocumentRoot ke `C:\laragon\www\sipandhalu\public`
2. Buka `http://sipandhalu.test/login` (nama menyesuaikan vhost yang dibuat)

> Yang wajib: DocumentRoot mengarah ke folder `public/`, BUKAN ke root proyek.
> Kalau CSS berantakan / halaman blank, 90% penyebabnya ini.

---

## 7. Login pertama kali

| Email | Password | Untuk |
|---|---|---|
| `pcl.demo@bpsjember.go.id` | `Jember3509` | coba cepat, langsung masuk |
| `admin@bpsjember.go.id` | `Jember3509` | admin (wajib ganti password saat login pertama) |

Daftar akun lengkap ada di `README.md` bagian **Akun Demo**.

---

## 8. Kalau mau bawa DATA KERJA dari PC lama (opsional)

Jalur di atas menghasilkan **data awal** (dummy). Kalau di PC lama sudah ada
data hasil input, ikuti ini sebagai tambahan:

**Di PC LAMA:**

```bash
mysqldump -u root --single-transaction --routines --triggers --databases sipandhalu > sipandhalu-backup.sql
```

Pindahkan file `sipandhalu-backup.sql` ke PC baru via flashdisk
(jangan di-commit ke GitHub — berisi data kerja).

**Di PC BARU** (setelah langkah 1–4 di atas, LEWATI langkah 5):

```bash
mysql -u root < sipandhalu-backup.sql
```

Lalu lanjut langkah 6–7. Password user ikut pindah karena tersimpan di database.

---

## 9. Cek kalau ada masalah

| Gejala | Solusi |
|---|---|
| Halaman blank / error 500 | `.env` belum dibuat atau `DB_USER`/`DB_PASS` salah |
| `Access denied for user 'root'` | MySQL PC baru pakai password → isi `DB_PASS` di `.env` |
| `Unknown database 'sipandhalu'` | belum migrate / restore gagal → ulangi langkah 5 atau 8 |
| CSS berantakan | DocumentRoot salah → harus ke folder `public/` |
| Error 419 saat submit form | cookie/session → pakai satu host konsisten (`localhost`, jangan campur `127.0.0.1`) |
| Import Excel gagal | pastikan ekstensi `zip`, `xml`, `gd` aktif (`php -m`) |

Cek kesehatan instalasi:

```bash
php vendor/phpunit/phpunit/phpunit --testdox
php tests/smoke_tahap1.php
```

---

## 10. Update kode di kemudian hari

Kalau proyek di GitHub ada update, di PC kerja tinggal:

```bash
cd C:\laragon\www\sipandhalu
git pull
composer install
```

Selesai — tidak perlu ulang migrate/seed kecuali ada pengumuman migrasi baru.
