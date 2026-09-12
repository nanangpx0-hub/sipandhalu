# SIPANDHALU

**Sistem Informasi Pandhalungan Susenas-Seruti** — BPS Kabupaten Jember

> *"Siji Data, Akeh Dulur — Ganti Triwulan, Ganti NKS, Tetap Siji Data"*

Aplikasi alokasi & pemutakhiran sampel Susenas (semesteran) dan Seruti (triwulanan) tingkat SLS/RT.
Native PHP 8.2 + MySQL 8, tanpa framework.

## Status

| Tahap | Fitur | Status |
|---|---|---|
| 1 | Manajemen user + petugas (login Argon2id, RBAC, alias nama, audit) | ✅ Selesai, diuji |
| 2 | Master wilayah (kecamatan/desa/SLS kode 16 digit) + periode + sampel + penugasan (aturan K4) | ✅ Selesai, diuji |
| 4 | Pad SLS dari PDF BPS (`56361`) | ✅ Selesai |
| — | Data dummy semua tabel + akun demo | ✅ Selesai |
| 3 | RBAC tulis server-side, dropdown penugasan, import Excel per gelombang, dashboard rekap | ⏳ Backlog |

## Fitur Utama (yang sudah jalan)

- **Autentikasi & RBAC** — login/logout, 6 peran (ADMIN, OPERATOR, PML, PCL, PENGOLAH, VIEWER),
  wajib ganti password pertama kali (`must_reset`), audit trail JSON semua aksi.
- **Master petugas** — satu pool `orang` untuk semua manusia (peran ditentukan per penugasan,
  bukan permanen), alias nama untuk menoleransi varian tulis Excel
  (contoh: *Junaidi* vs *Junaidi Ari Siswanto*).
- **Master wilayah** — kecamatan → desa → SLS/RT dengan kode BPS 16 digit + NKS 5 digit,
  atribut dusun/RW/RT/ketua/jumlah KK/klasifikasi perkotaan-perdesaan.
- **Periode survei** — Seruti Q1-Q4 & Susenas S1-S2, siklus `DRAFT → AKTIF → TUTUP`,
  periode TUTUP otomatis read-only, histori tersimpan selamanya.
- **Sampel & penugasan** — 1 SLS terpilih per periode (target 10 sampel RT), alokasi
  PCL + PML + Pengolah dengan validasi aturan K4 (1 orang 1 peran per periode).
- **Pad dokumen BPS** — seed `003` memadankan SLS dari PDF resmi VSEN26-DSRT
  (kode SLS/sub, RT/RW/dusun, klasifikasi, hasil pemutakhiran Blok II).

## Alur Kerja per Periode

```
1. Buat periode DRAFT        (mis. 2026-Q3 Seruti)
2. Terima/daftar SLS sampel  (dari BPS; import Excel = backlog tahap 3)
3. Alokasi penugasan         (PCL/PML/Pengolah; validasi K4 otomatis)
4. Pelaksanaan               (update dok VSEN, peta WS, hasil pemutakhiran)
5. Tutup periode             (TUTUP = read-only; histori tetap bisa dilihat)
```

## Arsitektur

```
Browser → public/index.php (Front Controller)
  → Router → Middleware (Auth, Role, CSRF) → Controller (tipis, tanpa SQL)
    → Service (aturan bisnis + transaksi: PenugasanService, AuthService, dst.)
      → Repository (PDO prepared) → MySQL 8
      → View (.phtml, semua output di-escape)
```

Monolit modular native PHP 8.2 — tanpa framework, autoload PSR-4, `strict_types` menyeluruh.


## Quick Start

```bash
# 1. Syarat: Laragon (PHP 8.2 + MySQL 8), composer install
composer install

# 2. Buat DB + migrasi + seed (idempoten, urutan penting, jangan paralel)
#    Linux/macOS:
bash scripts/setup.sh
#    Windows:
scripts\setup.bat
#    Atau jalankan komponen secara manual:
php database/migrate.php
php database/seeds/seed_tahap1.php && php database/seeds/002_wilayah_seed.php && php database/seeds/003_sls_pdf_seed.php && php database/seeds/004_dummy_seed.php

# 3. Jalankan (pilih salah satu)
#    a) Laragon vhost -> DocumentRoot = <proyek>/public  -> http://sipandhalu.test
#    b) PHP built-in:
php -S localhost:8091 -t public

# 4. Tes
php vendor/phpunit/phpunit/phpunit --testdox
php tests/smoke_tahap1.php
```

## Akun Demo

| Email | Password | Peran | Catatan |
|---|---|---|---|
| `admin@bpsjember.go.id` | `Admin3509!` | ADMIN | wajib ganti password saat login pertama |
| 8 email pengolah asli (sheet Rincian) | `Jember3509` | PENGOLAH | wajib ganti password pertama kali |
| `pcl.demo@bpsjember.go.id` | `Dummy3509!` | PCL | langsung masuk |
| `pml.demo@bpsjember.go.id` | `Dummy3509!` | PML | langsung masuk |
| `operator.demo@bpsjember.go.id` | `Dummy3509!` | OPERATOR | langsung masuk |
| `viewer.demo@bpsjember.go.id` | `Dummy3509!` | VIEWER | langsung masuk |

## Konsep Kunci (hasil diskusi + keputusan yang mengikat)

1. **`kode_sls` ≠ `nks`** — 1 SLS/RT punya 2 identitas: `kode_full` 16 digit BPS
   (`prov2+kab2+kec3+desa3+sls4+sub2`, contoh `3509170002004200`) dan `nks` 5 digit (contoh `56361`).
   Keduanya **stabil**, tidak di-generate aplikasi.
2. **1 SLS = 1 RT** (dusun, RW, RT, ketua, jumlah KK).
3. **Histori wajib** — data per periode tidak boleh timpa/hapus; 1 SLS boleh disampel berulang
   di periode berbeda (Q1, Q3, S1…). Periode `DRAFT → AKTIF → TUTUP` (TUTUP = read-only).
4. **Aturan K4** — 1 orang hanya 1 peran per periode; boleh pegang banyak NKS dalam peran yang sama;
   boleh ganti peran di periode berbeda.
5. **Satu frame SLS** untuk Susenas + Seruti; yang beda hanya periodenya.
6. Level data = **SLS saja** (tanpa Blok V per KRT).

## Struktur Proyek

```
public/            DocumentRoot (index.php front controller, assets)
app/Core/          Config, Database(PDO), Request, Response, Router, Session, Csrf, Validator, Logger, KodeBps
app/Models/        Entity readonly + Enum (Role, Orang, User)
app/Repositories/  PDO prepared statement (Orang, User, Audit, Wilayah, Desa, Sls, Periode, Sampel)
app/Services/      Aturan bisnis + transaksi (Auth, User, Orang, Sls, Penugasan)
app/Controllers/   Tipis, tanpa SQL (Auth, Dashboard, Orang, User, Sls, Periode)
app/Views/         Template .phtml + layout + error pages
config/            app.php, database.php, routes.php
database/          schema.sql, migrations/, migrate.php, seeds/ (001-004)
tests/             PHPUnit (Tahap1Test, Tahap2Test) + smoke CLI
storage/logs/      Log aplikasi
```

## Dokumentasi Lengkap

- [Instalasi & Konfigurasi](docs/INSTALASI.md)
- [Panduan Penggunaan & Login](docs/PANDUAN-PENGGUNAAN.md)
- [Skema Database & Keputusan Desain](docs/SKEMA-DATABASE.md)
- [Pindah ke Komputer Lain](docs/PINDAH-KOMPUTER.md) — clone+seed vs dump/restore, verifikasi, troubleshooting, FAQ

## Testing

| Perintah | Cakupan |
|---|---|
| `php vendor/phpunit/phpunit/phpunit --testdox` | Unit + integrasi DB (`sipandhalu_test`): validator, dedup orang, login/reset, kode 16 digit, NKS unik, aturan K4, rekap 28×10 |
| `php tests/smoke_tahap1.php` | Smoke CLI DB dev: koneksi, seed baseline, Argon2id, resolveAlias, rekap CTE, 17 view ter-parse, tabel tahap 2 |
| `php -l` semua file | Lint sintaks (41 file PHP + 17 view) |

## Keamanan

`declare(strict_types=1)` di semua file · PDO prepared + `EMULATE_PREPARES=false` ·
Argon2id + `password_needs_rehash` · CSRF `hash_equals` (HTTP 419) · session hardening
(`httponly`, `samesite`, `session_regenerate_id` saat login) · header `X-Frame-Options: DENY`,
`X-Content-Type-Options: nosniff` · semua output `htmlspecialchars(ENT_QUOTES)` ·
audit trail JSON (before/after) · pesan login generik.

## Data Sensitif

Folder `data/` **sengaja tidak di-commit** — berisi dokumen BPS berlabel **RAHASIA**
(`Alokasi (2).xlsx`, PDF VSEN26-DSRT) yang memuat PII. Seed database dibangun dari hasil
ekstraksi file tersebut, sehingga aplikasi tetap berjalan tanpa perlu file asli di repo.

## Roadmap

- [x] Tahap 1 — user + petugas (RBAC, alias, audit)
- [x] Tahap 2 — wilayah/SLS + periode + sampel + penugasan (K4)
- [x] Tahap 4 — pad SLS dari PDF BPS (pilot NKS 56361)
- [x] Data dummy + akun demo + dokumentasi
- [ ] Tahap 3 — RBAC tulis server-side di semua controller
- [ ] Tahap 3 — dropdown penugasan dari pool orang (ganti input ID)
- [ ] Tahap 3 — import Excel alokasi per gelombang (dry-run → commit)
- [ ] Tahap 3 — dashboard rekap triwulan vs semester (CTE + window function)
- [ ] Tahap 4+ — pad kode_full 16 digit untuk 27 SLS lain dari BPS

## Lisensi & Kredit

Dikembangkan untuk kebutuhan internal **BPS Kabupaten Jember**.
Branding "SIPANDHALU" dari kearifan lokal *Pandhalungan* (pembauran Jawa-Madura-Osing-Bugis).

