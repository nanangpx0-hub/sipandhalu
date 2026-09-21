# SIPANDHALU — Dokumentasi Lengkap

**Sistem Informasi Pandhalungan Susenas-Seruti** — BPS Kabupaten Jember

> *"Siji Data, Akeh Dulur — Ganti Triwulan, Ganti NKS, Tetap Siji Data"*

Aplikasi alokasi & pemutakhiran sampel Susenas (semesteran) dan Seruti (triwulanan) tingkat SLS/RT.
Native PHP 8.2 + MySQL 8, tanpa framework.

---

## Daftar Isi

1. [Ikhtisar](#1-ikhtisar)
2. [Arsitektur](#2-arsitektur)
3. [Struktur Kode](#3-struktur-kode)
4. [Basis Data](#4-basis-data)
5. [Modul & Fitur](#5-modul--fitur)
6. [Rute & API](#6-rute--api)
7. [Autentikasi & RBAC](#7-autentikasi--rbac)
8. [Keamanan](#8-keamanan)
9. [Pengujian](#9-pengujian)
10. [Instalasi & Setup](#10-instalasi--setup)
11. [Kontribusi](#11-kontribusi)

---

## 1. Ikhtisar

| Properti | Detail |
|---|---|
| Nama | SIPANDHALU |
| Kegunaan | Sistem Informasi Pandhalungan Susenas-Seruti |
| Institusi | BPS Kabupaten Jember (Kode BPS 3509) |
| Teknologi | Native PHP 8.2, MySQL 8, HTML/CSS/JS (AdminLTE 3) |
| Framework | Tidak ada (tanpa framework) |
| Arsitektur | MVC monolitik dengan front controller |
| Database | MySQL 8 (InnoDB, utf8mb4_unicode_ci) |
| PHP | >= 8.2 |
| Dependensi Utama | PhpOffice/PhpSpreadsheet ^5.9 |

### Peran Pengguna

| Kode | Peran | Keterangan |
|---|---|---|
| ADMIN | Administrator | Akses penuh, termasuk manajemen users |
| OPERATOR | Operator Pengolahan | Dapat mengolah data, transfer Seruti |
| PML | Pengawas Lapangan | Read-only pada modul tertentu |
| PCL | Pencacah Lapangan | Read-only pada modul tertentu |
| PENGOLAH | Pengolah Data | Dapat input data binaan sendiri, terbatas |
| VIEWER | Viewer | Hanya bisa lihat dashboard |

### Akun Demo

| Email | Password | Peran | Keterangan |
|---|---|---|---|
| admin@bpsjember.go.id | Jember3509 | ADMIN | Wajib ganti password pertama kali |
| aminatuss182002@gmail.com | Jember3509 | PENGOLAH | Wajib ganti password pertama kali |
| operator.demo@bpsjember.go.id | Jember3509 | OPERATOR | Tanpa reset |
| viewer.demo@bpsjember.go.id | Jember3509 | VIEWER | Tanpa reset |
| pcl.demo@bpsjember.go.id | Jember3509 | PCL | Tanpa reset |
| pml.demo@bpsjember.go.id | Jember3509 | PML | Tanpa reset |

---

## 2. Arsitektur

### Pola Arsitektur

```
Browser → public/index.php (Front Controller)
        → app/Core/bootstrap.php (Inisialisasi)
        → App\Core\Router (Dispatch)
        → Middleware (Auth, CSRF, Role)
        → Controller → Service → Repository → Database
        → View (.phtml template)
```

### Konvensi MVC

- **Controller**: `app/Controllers/{Nama}Controller.php` — menangani request HTTP, memanggil Service, mengirim ke View
- **Service**: `app/Services/{Nama}Service.php` — logika bisnis, validasi RBAC, manipulasi data
- **Repository**: `app/Repositories/{Nama}Repository.php` — akses data (SQL queries), abstraksi DB
- **View**: `app/Views/{module}/{nama}.phtml` — template HTML (PHP embedded)
- **Layout**: `app/Views/layouts/header.phtml` & `footer.phtml` — layout bersama

### Komponen Core

| Kelas | File | Fungsi |
|---|---|---|
| `Router` | `app/Core/Router.php` | Routing HTTP, pattern matching, middleware pipeline |
| `Request` | `app/Core/Request.php` | Kapsulasi request (method, path, GET, POST, server) |
| `Response` | `app/Core/Response.php` | Helper respons (view, redirect, json) |
| `Session` | `app/Core/Session.php` | Manajemen sesi (flash, regenerate, destroy) |
| `Csrf` | `app/Core/Csrf.php` | Token CSRF generation & validation |
| `View` | `app/Core/View.php` | Escaping output (XSS prevention) |
| `Database` | `app/Core/Database.php` | Singleton PDO connection |
| `Excel` | `app/Core/Excel.php` | PhpSpreadsheet helper (export, download, import) |
| `Logger` | `app/Core/Logger.php` | File-based logging (INFO/WARNING/ERROR) |
| `Config` | `app/Core/Config.php` | Environment variable loader |

### Middleware Pipeline

Middleware dijalankan berurutan sebelum controller ditangani:

1. **AuthMiddleware** — Memeriksa apakah pengguna sudah login (sesi `$_SESSION['user']`). Jika tidak, redirect ke `/login` (302).
2. **CsrfMiddleware** — Memvalidasi token CSRF pada request POST/PUT/PATCH/DELETE. Jika tidak valid,返回 419.
3. **RoleMiddleware** — Memeriksa apakah pengguna memiliki peran yang diizinkan. Jika tidak,返回 403.

---

## 3. Struktur Kode

```
sipandhalu/
├── public/                     # Front controller & assets
│   ├── index.php               # Front controller
│   └── assets/                 # CSS, JS, vendor (AdminLTE, Bootstrap, FontAwesome)
├── app/
│   ├── Core/                   # Framework inti
│   │   ├── bootstrap.php       # Inisialisasi aplikasi
│   │   ├── Router.php          # Router
│   │   ├── Request.php         # Request object
│   │   ├── Response.php        # Response helpers
│   │   ├── Session.php         # Session management
│   │   ├── Csrf.php            # CSRF protection
│   │   ├── View.php            # View escaping
│   │   ├── Database.php        # DB connection (singleton)
│   │   ├── Excel.php           # Excel export/import helper
│   │   ├── Logger.php          # File logger
│   │   └── Config.php          # Env config loader
│   ├── Controllers/            # HTTP controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── OrangController.php
│   │   ├── UserController.php
│   │   ├── SlsController.php
│   │   ├── PeriodeController.php
│   │   ├── PengolahanController.php
│   │   ├── SerutiController.php
│   │   ├── MonitoringController.php
│   │   └── SerutiController.php
│   ├── Services/               # Business logic
│   │   ├── AuthService.php
│   │   ├── SerutiService.php
│   │   ├── PengolahanService.php
│   │   ├── MonitoringService.php
│   │   ├── PeriodeService.php
│   │   ├── OrangService.php
│   │   ├── SlsService.php
│   │   ├── PenugasanService.php
│   │   ├── DokumenService.php
│   │   └── DokumenKirimService.php
│   ├── Repositories/           # Data access
│   │   ├── UserRepository.php
│   │   ├── PeriodeRepository.php
│   │   ├── SampelRepository.php
│   │   ├── SampelRutaRepository.php
│   │   ├── SlsRepository.php
│   │   ├── WilayahRepository.php
│   │   ├── DesaRepository.php
│   │   ├── OrangRepository.php
│   │   ├── AuditRepository.php
│   │   ├── DashboardRepository.php
│   │   ├── DokumenRepository.php
│   │   └── JadwalPengawasRepository.php
│   ├── Middleware/             # HTTP middleware
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── RoleMiddleware.php
│   └── Views/                  # Templates
│       ├── layouts/
│       │   ├── header.phtml    # Layout utama (sidebar, navbar)
│       │   ├── footer.phtml
│       │   ├── pagination.phtml
│       │   └── import_excel.phtml
│       ├── auth/
│       │   ├── login.phtml
│       │   └── password.phtml
│       ├── dashboard/
│       │   └── index.phtml
│       ├── orang/
│       │   ├── index.phtml
│       │   ├── show.phtml
│       │   └── form.phtml
│       ├── users/
│       │   ├── index.phtml
│       │   └── form.phtml
│       ├── sls/
│       │   ├── index.phtml
│       │   └── form.phtml
│       ├── periode/
│       │   ├── index.phtml
│       │   ├── show.phtml
│       │   └── form.phtml
│       ├── pengolahan/
│       │   └── index.phtml
│       ├── seruti/
│       │   └── index.phtml
│       ├── monitoring/
│       │   └── index.phtml
│       └── errors/
│           ├── 403.phtml
│           ├── 404.phtml
│           ├── 419.phtml
│           └── 500.phtml
├── config/
│   ├── database.php            # DB config (env-driven)
│   └── routes.php              # Route definitions
├── database/
│   ├── schema.sql              # DB schema (tahap 1)
│   ├── sipandhalu.sql          # Full DB dump
│   ├── migrations/             # Schema migrations
│   │   ├── 002a_wilayah.sql
│   │   ├── 002b_periode.sql
│   │   ├── 002c_desa_sls.sql
│   │   ├── 002d_wilayah_final.sql
│   │   ├── 003_dokumen_penerimaan_pinjam.sql
│   │   ├── 004_dokumen_kirim_ruta.sql
│   │   ├── 005_level_petugas.sql
│   │   ├── 006_lk_pengolahan.sql
│   │   ├── 007_jadwal_pengawas.sql
│   │   ├── 008_monitoring_optimizations.sql
│   │   └── 009_telaah_ipds.sql
│   └── seeds/                  # Data seeds
│       ├── seed_tahap1.php
│       ├── 002_wilayah_seed.php
│       ├── 003_sls_pdf_seed.php
│       ├── 004_dummy_seed.php
│       ├── 005_jadwal_pengawas_seed.php
│       └── 005_dummy_ruta.php
├── app/Core/                    # Core framework classes
├── scripts/
│   ├── sync_seruti_2026.php    # Sync all Seruti periods Q1-Q4
│   ├── verify_sync.php         # Verify sync results
│   └── compare_nks.php         # Compare NKS values
├── tests/
│   ├── SerutiModulTest.php     # PHPUnit (64 tests)
│   ├── SerutiPengolahanTest.php
│   ├── PengolahanTest.php
│   ├── Tahap1Test.php
│   ├── Tahap2Test.php
│   ├── ExcelTest.php
│   ├── DokumenTest.php
│   ├── DokumenKirimTest.php
│   └── playwright/             # E2E tests (Playwright)
│       ├── pengolah.spec.js
│       └── seruti-page-test.spec.cjs
├── vendor/                       # Composer dependencies
├── composer.json
├── phpunit.xml
└── .env.example                  # Environment template
```

---

## 4. Basis Data

### Skema Utama (Tabel)

| Tabel | Deskripsi |
|---|---|
| `roles` | Daftar peran (ADMIN, OPERATOR, PML, PCL, PENGOLAH, VIEWER, dll.) |
| `orang` | Master orang (pool petugas). Nama kanonik Title Case, TRIM. |
| `orang_alias` | Alias nama untuk toleransi varian tulis |
| `users` | Akun login (email, password_hash Argon2id, role_id, must_reset) |
| `audit_logs` | Jejak audit JSON (CREATE/UPDATE/DELETE/LOGIN/LOGOUT/RESET_PW) |
| `kecamatan` | Kecamatan (kode BPS 6 digit) |
| `desa` | Desa (kode BPS 10 digit, FK ke kecamatan) |
| `sls` | SLS/RT (kode 16 digit NKS, FK ke desa) |
| `periode` | Periode survei (SUSENAS_S1/S2, SERUTI_Q1-Q4) |
| `sampel` | Sampel per periode per SLS (target, muatan_awal, dokumen) |
| `penugasan` | Penugasan per SLS (pcl_id, pml_id, pengolah_id, status) |
| `sampel_ruta` | Ruta pengolahan per sampel (no_urut, status dokumen/transfer) |
| `peminjaman_dokumen` | Peminjaman dokumen lapangan |
| `dokumen_kirim` | Pengiriman kuesioner ke Kabupaten |
| `jadwal_pengawas` | Jadwal pengawas pengolahan |

### Konvensi Database

- **Charset**: utf8mb4_unicode_ci
- **Engine**: InnoDB
- **Kunci asing**: ON UPDATE CASCADE, ON DELETE RESTRICT/SET NULL sesuai konteks
- **Timestamp**: `created_at`, `updated_at` (auto-set oleh MySQL)
- **Enums**: `periode.jenis` ENUM('SERUTI_Q1'...'SUSENAS_S2'), `penugasan.status` ENUM('AKTIF','SELESAI','DRAFT','BELUM')

### Status Periode

```
DRAFT → AKTIF → TUTUP
```

- **DRAFT**: Periode belum dimulai, data dapat dimasukkan
- **AKTIF**: Periode sedang berjalan, bisa dipilih untuk pengolahan
- **TUTUP**: Periode selesai, data read-only, histori tersimpan

---

## 5. Modul & Fitur

### 5.1 Dashboard (`/`)
- Info box ringkasan: Orang, User, SLS, Periode (AKTIF/DRAFT/TUTUP)
- Audit trail 10 entri terakhir
- Hanya dapat diakses oleh user yang sudah login

### 5.2 Login & Autentikasi (`/login`, `/password`)
- Login dengan email + password (Argon2id hashing)
- Wajib ganti password pertama kali (`must_reset=1`)
- Timing-safe password verification (generik pesan error)
- Audit trail semua percobaan login (sukses & gagal)
- Session regenerate setelah login

### 5.3 Master Petugas (`/petugas`)
- Pool orang tunggal untuk semua peran
- Peran ditentukan per penugasan periode (bukan permanen)
- CRUD + toggle aktif/nonaktif
- Alias nama (varian tulis)
- Export/Import Excel
- Lihat data diri sendiri (PENGOLAH), kelola semua (ADMIN/OPERATOR)

### 5.4 Users (`/users`) — Hanya ADMIN
- Manajemen akun login (create, edit, toggle, reset password)
- Peran dan level petugas

### 5.5 Master SLS (`/sls`) — Bukan PENGOLAH
- Master SLS/RT dengan kode BPS 16 digit
- NKS 5 digit per SLS
- Import/Export Excel

### 5.6 Periode (`/periode`) — Bukan PENGOLAH
- CRUD periode (SUSENAS & SERUTI)
- Status: DRAFT → AKTIF → TUTUP
- Sampel per SLS per periode (aturan K4: 10 KK/RT per SLS)
- Penugasan (1 orang 1 peran per periode)
- Penerimaan & peminjaman dokumen

### 5.7 Pengolahan / LK (`/pengolahan`)
- Lembar Kerja pengolahan sampel & kendali mutu
- Update per-rute (status dokumen, transfer, catatan)
- Transfer dokumen (terima, pinjam, kembali)
- Batch transfer
- Export Excel
- RBAC server-side (PENGOLAH bisa tulis data binaan sendiri saja)

### 5.8 Pengolahan Seruti (`/seruti`)
- KPI Seruti per periode (Total Sampel, Kesiapan Susenas, Siap Olah, Terkunci, Selesai Transfer)
- Data grid rute Seruti dengan filter (NKS, Kesiapan, Pengolah, Pencarian)
- Telaah kuesioner Seruti (modal)
- Transfer Seruti (hanya ADMIN/OPERATOR/SM_PLS/PENGAWAS_OLAH)
- Export Excel Seruti

### 5.9 Monitoring (`/monitoring`)
- Dashboard Enterprise (Tier 1 KPI, Tier 2 chart, Tier 3 grid)
- Filter cascading (kecamatan → desa → SLS)
- Detail rute dengan jejak audit (drawer)
- Quick verify & bulk verify
- Export CSV/XLSX

---

## 6. Rute & API

### Konvensi Rute

- GET → Halaman (server-rendered HTML)
- POST → Aksi (form submission / AJAX)
- `{id}` → Parameter rute (dalam URL path)

### Daftar Rute Lengkap

| Method | Path | Controller | Middleware | Deskripsi |
|---|---|---|---|---|
| GET | `/login` | AuthController::showLogin | — | Halaman login |
| POST | `/login` | AuthController::login | Csrf | Proses login |
| POST | `/logout` | AuthController::logout | Auth, Csrf | Logout |
| GET | `/` | DashboardController::index | Auth | Dashboard |
| GET | `/password` | AuthController::showPassword | Auth | Ganti password |
| POST | `/password` | AuthController::updatePassword | Auth, Csrf | Update password |
| GET | `/petugas` | OrangController::index | Auth | Daftar petugas |
| GET | `/petugas/baru` | OrangController::create | Auth | Form tambah petugas |
| GET | `/petugas/export` | OrangController::exportExcel | Auth | Export Excel |
| GET | `/petugas/template` | OrangController::templateExcel | Auth | Template Excel |
| POST | `/petugas/import` | OrangController::importExcel | Auth, Csrf | Import Excel |
| GET | `/petugas/{id}` | OrangController::show | Auth | Detail petugas |
| GET | `/petugas/{id}/edit` | OrangController::edit | Auth | Form edit petugas |
| POST | `/petugas/{id}` | OrangController::update | Auth, Csrf | Update petugas |
| POST | `/petugas/{id}/toggle` | OrangController::toggle | Auth, Csrf | Toggle aktif |
| POST | `/petugas/{id}/alias` | OrangController::addAlias | Auth, Csrf | Tambah alias |
| POST | `/petugas/{id}/alias/hapus` | OrangController::deleteAlias | Auth, Csrf | Hapus alias |
| GET | `/users` | UserController::index | Auth | Daftar users (ADMIN) |
| POST | `/users` | UserController::store | Auth, Csrf | Tambah user |
| POST | `/users/{id}` | UserController::update | Auth, Csrf | Update user |
| POST | `/users/{id}/toggle` | UserController::toggle | Auth, Csrf | Toggle user |
| POST | `/users/{id}/reset` | UserController::reset | Auth, Csrf | Reset password |
| GET | `/sls` | SlsController::index | Auth | Daftar SLS |
| POST | `/sls/import` | SlsController::importExcel | Auth, Csrf | Import SLS |
| POST | `/sls/{id}` | SlsController::update | Auth, Csrf | Update SLS |
| GET | `/periode` | PeriodeController::index | Auth | Daftar periode |
| POST | `/periode` | PeriodeController::store | Auth, Csrf | Tambah periode |
| GET | `/periode/{id}` | PeriodeController::show | Auth | Detail periode |
| POST | `/periode/{id}/status` | PeriodeController::setStatus | Auth, Csrf | Set status |
| POST | `/periode/{id}/sampel` | PeriodeController::addSampel | Auth, Csrf | Tambah sampel |
| POST | `/periode/{id}/assign` | PeriodeController::assign | Auth, Csrf | Penugasan |
| POST | `/periode/{id}/sampel/{sid}/terima` | PeriodeController::terimaDokumen | Auth, Csrf | Terima dokumen |
| POST | `/periode/{id}/terima-kolektif` | PeriodeController::terimaKolektif | Auth, Csrf | Terima kolektif |
| POST | `/periode/{id}/sampel/{sid}/pinjam` | PeriodeController::pinjamDokumen | Auth, Csrf | Pinjam dokumen |
| POST | `/periode/{id}/kembali-dokumen` | PeriodeController::kembaliDokumen | Auth, Csrf | Kembali dokumen |
| POST | `/periode/{id}/dok-kirim/import` | PeriodeController::importDokKirim | Auth, Csrf | Import kirim |
| POST | `/periode/{id}/sampel/{sid}/ruta` | PeriodeController::simpanRuta | Auth, Csrf | Simpan rute |
| POST | `/periode/{id}/sampel/{sid}/ruta/selesai-semua` | PeriodeController::selesaiSemuaRuta | Auth, Csrf | Selesai semua |
| GET | `/pengolahan` | PengolahanController::index | Auth | LK Pengolahan |
| POST | `/pengolahan/update-ruta` | PengolahanController::updateRuta | Auth, Csrf | Update rute |
| POST | `/pengolahan/terima-dokumen` | PengolahanController::terimaDokumen | Auth, Csrf | Terima dokumen |
| POST | `/pengolahan/batch-transfer` | PengolahanController::batchTransfer | Auth, Csrf | Batch transfer |
| GET | `/seruti` | SerutiController::index | Auth | Pengolahan Seruti |
| POST | `/seruti/update-ruta` | SerutiController::updateRuta | Auth, Csrf | Update rute Seruti |
| POST | `/seruti/batch-transfer` | SerutiController::batchTransfer | Auth, Csrf | Batch transfer Seruti |
| GET | `/seruti/export` | SerutiController::export | Auth | Export Seruti Excel |
| GET | `/monitoring` | MonitoringController::index | Auth | Monitoring dashboard |
| GET | `/monitoring/data` | MonitoringController::data | Auth | JSON KPI+chart |
| GET | `/monitoring/options` | MonitoringController::options | Auth | JSON filter options |
| GET | `/monitoring/export` | MonitoringController::export | Auth | Export CSV/XLSX |
| GET | `/monitoring/detail/{id}` | MonitoringController::detail | Auth | JSON detail |
| POST | `/monitoring/quick-verify` | MonitoringController::quickVerify | Auth, Csrf | Quick verify |
| POST | `/monitoring/bulk-verify` | MonitoringController::bulkVerify | Auth, Csrf | Bulk verify |

### Kode Respons HTTP

| Kode | Penggunaan |
|---|---|
| 200 | Sukses |
| 302 | Redirect (AuthMiddleware tanpa sesi, Response::redirect) |
| 403 | Akses ditolak (RoleMiddleware, controller RBAC check) |
| 404 | Rute tidak ditemukan |
| 419 | Token CSRF tidak valid |
| 500 | Error server (unhandled exception) |

---

## 7. Autentikasi & RBAC

### Alur Login

1. User mengisi email + password di `/login`
2. `AuthService::attempt()` memverifikasi kredensial
3. Jika `must_reset=1`, redirect ke `/password` (wajib ganti password)
4. Jika sukses, session dibuat, session ID di-regenerate
5. Audit trail dicatat (LOGIN sukses/gagal)

### Password Policy

- Hash: Argon2id (PASSWORD_ARGON2ID)
- Minimal 8 karakter
- Rehash otomatis jika parameter Argon2 berubah
- Timing-safe comparison (hash_equals)
- Pesan error generik ("Email atau password salah") untuk tidak membocorkan akun

### RBAC Enforcement

RBAC ditegakkan pada beberapa level:

1. **Middleware level**: AuthMiddleware (login required), RoleMiddleware (peran required)
2. **Controller constructor**: Beberapa controller mengecek peran langsung (contoh: PeriodeController menolak PENGOLAH, SlsController menolak PENGOLAH)
3. **Service level**: SerutiService::canTransferSeruti(), SerutiService::canEdit(), SerutiService::assertCanTransferSeruti(), SerutiService::assertCanEditRuta()
4. **View level**: Sidebar menu ditampilkan/disembunyikan berdasarkan peran

### Scoping Data PENGOLAH

PENGOLAH hanya dapat melihat dan mengubah data miliknya sendiri (berdasarkan `orang_id`). Scoping diterapkan di:
- `/petugas` — hanya lihat data diri sendiri
- `/seruti` — hanya rute binaan sendiri
- `/pengolahan` — hanya rute binaan sendiri

---

## 8. Keamanan

### CSRF Protection
- Setiap form POST menyertakan token CSRF (hidden field `_csrf`)
- CsrfMiddleware memvalidasi token sebelum memproses request
- Token berbasis session, di-generate dengan `random_bytes(32)`
- Validation menggunakan `hash_equals` (timing-safe)

### Session Security
- HttpOnly cookies (tidak dapat diakses JavaScript)
- SameSite=Lax (mencegah CSRF cross-site)
- Session regenerate setelah login (fixation prevention)
- Session destroy pada logout
- Session name: `SID_SIPANDHALU`

### SQL Injection Prevention
- Seluruh query menggunakan PDO prepared statements
- Parameter binding untuk semua input pengguna

### XSS Prevention
- `App\Core\View::e()` — escaping dengan `htmlspecialchars(ENT_QUOTES, 'UTF-8')`
- Semua output dinamis di-escape di view template

### Security Headers
- `X-Frame-Options: DENY` (clickjacking prevention)
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`

### Password Reset Flow
- Password lama diverifikasi sebelum ganti
- Minimal 8 karakter untuk password baru
- `must_reset` flag di-update setelah reset

---

## 9. Pengujian

### PHPUnit

```bash
php vendor/bin/phpunit                    # Semua test
php vendor/bin/phpunit tests/SerutiModulTest.php   # Seruti saja
```

**File test utama:**

| File | Jumlah | Cakupan |
|---|---|---|
| `tests/SerutiModulTest.php` | 64 | Routes, Controllers, Services, RBAC, Export |
| `tests/Tahap1Test.php` | - | Tahap 1 (user, petugas) |
| `tests/Tahap2Test.php` | - | Tahap 2 (wilayah, periode, sampel) |
| `tests/PengolahanTest.php` | - | Pengolahan (LK) |
| `tests/ExcelTest.php` | - | Excel import/export |
| `tests/DokumenTest.php` | - | Peminjaman dokumen |
| `tests/DokumenKirimTest.php` | - | Pengiriman dokumen |

### Playwright (E2E)

```bash
npx playwright test                     # Semua E2E
npx playwright test --project=chromium seruti   # Seruti saja
```

**File test:**

| File | Jumlah | Cakupan |
|---|---|---|
| `tests/playwright/pengolah.spec.js` | 20 | Login, password reset, RBAC, CRUD, export |
| `tests/playwright/seruti-page-test.spec.cjs` | 8 | Login, page render, sidebar, export, logout |

**Pastikan PHP server berjalan:**
```bash
php -S localhost:8091 -t public
```

### Reset DB untuk Testing

```bash
php database/migrate.php --fresh
php database/seeds/seed_tahap1.php
php database/seeds/002_wilayah_seed.php
php database/seeds/003_sls_pdf_seed.php
php database/seeds/004_dummy_seed.php
php add_periodes.php
php scripts/sync_seruti_2026.php
```

---

## 10. Instalasi & Setup

### Prasyarat

- PHP >= 8.2
- MySQL 8+
- Composer
- Node.js + npm (untuk E2E tests)

### Setup Cepat

```bash
# 1. Install dependensi PHP
composer install

# 2. Install Playwright (untuk E2E tests)
npm install
npx playwright install chromium

# 3. Setup database
php database/migrate.php --fresh
php database/seeds/seed_tahap1.php
php database/seeds/002_wilayah_seed.php
php database/seeds/003_sls_pdf_seed.php
php database/seeds/004_dummy_seed.php
php add_periodes.php
php scripts/sync_seruti_2026.php

# 4. Jalankan PHP server
php -S localhost:8091 -t public

# 5. Buka browser
# http://localhost:8091/login
```

### Konfigurasi

Salin `.env.example` ke `.env` dan sesuaikan:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sipandhalu
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
SESSION_NAME=SID_SIPANDHALU
SESSION_SECURE=false
LOG_PATH=storage/logs/app.log
APP_DEBUG=true
```

### Development Environment

Aplikasi berjalan di:
- **PHP built-in server**: `php -S localhost:8091 -t public`
- **MySQL**: localhost:3306 (default)
- **Base URL**: `http://localhost:8091`

---

## 11. Kontribusi

### Alur Kerja

1. Fork & clone repository
2. Buat fitur/fix di branch baru
3. Jalankan test: `php vendor/bin/phpunit` & `npx playwright test`
4. Pastikan semua test hijau
5. Commit & push
6. Buat Pull Request

### Standar Kode

- `strict_types=1` di semua file PHP
- PSR-4 autoloading (`App\` → `app/`)
- Type declarations di mana mungkin
- Prepared statements untuk semua SQL
- Output escaping (View::e()) di semua template

### Laporkan Masalah

Buat issue dengan detail:
- Langkah reproduksi
- Expected vs actual behavior
- Environment (PHP version, browser, DB)

---

## Riwayat Versi

| Tahap | Fitur | Status |
|---|---|---|
| 1 | Manajemen user + petugas (login Argon2id, RBAC, alias nama, audit) | ✅ Selesai |
| 2 | Master wilayah + periode + sampel + penugasan (aturan K4) | ✅ Selesai |
| 3 | RBAC tulis server-side, dropdown penugasan, import Excel per gelombang | ⏳ Backlog |
| 4 | Pad SLS dari PDF BPS (56361) | ✅ Selesai |
| — | Data dummy semua tabel + akun demo | ✅ Selesai |
| — | Modul Seruti (Pengolahan Seruti triwulanan) | ✅ Selesai |
| — | Dashboard Monitoring Enterprise | ✅ Selesai |
