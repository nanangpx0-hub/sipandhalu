# Dokumentasi Aplikasi SIPANDHALU

**Sistem Informasi Pandhalungan Susenas-Seruti — BPS Kabupaten Jember**
> *Siji Data, Akeh Dulur — Ganti Triwulan, Ganti NKS, Tetap Siji Data*

Dokumen gabungan **teknis + pengguna**. Rujukan ringkas lain:
`README.md`, `docs/INSTALASI.md`, `docs/PANDUAN-PENGGUNAAN.md`,
`docs/SKEMA-DATABASE.md`, `docs/PINDAH-KOMPUTER.md`, `docs/DEPLOY.md`,
`docs/ANALISIS.md`, `docs/walkthrough.md`.

---

## 1. Gambaran Umum

Aplikasi alokasi & pemutakhiran sampel **Susenas (semesteran: S1/S2)**
dan **Seruti (triwulanan: Q1–Q4)** tingkat SLS/RT.

* Native **PHP 8.2 + MySQL 8**, tanpa framework. PSR-4 `App\` → `app/`, `strict_types` menyeluruh.
* Dependensi: `phpoffice/phpspreadsheet ^5.9` (Excel), `phpunit/phpunit 10` (dev) — `composer.json:5-11`.
* Monolit modular: Front Controller → Router → Middleware → Controller tipis → Service → Repository (PDO) → View `.phtml`.
* Level data = **SLS saja** (tanpa Blok V per KRT). Satu frame SLS untuk Susenas + Seruti; yang beda hanya periode.

Status (detail `README.md:11-18`):

| Tahap | Fitur | Status |
|---|---|---|
| 1 | User + petugas (login Argon2id, RBAC, alias, audit) | ✅ Selesai, diuji |
| 2 | Wilayah (kec/desa/SLS 16 digit) + periode + sampel + penugasan (K4) | ✅ Selesai, diuji |
| 4 | Pad SLS dari PDF BPS (`56361`) + Dok terima/pinjam + Dok Kirim Ruta + LK Pengolahan | ✅ Selesai, diuji |
| 3 | RBAC tulis server-side penuh, dashboard rekap, import Excel per gelombang | ⏳ Sebagian / backlog |

---

## 2. Konsep Domain (Mengikat)

| # | Aturan | Implementasi |
|---|---|---|
| K1 | `kode_sls` ≠ `nks`. `kode_full` 16 digit (`prov2+kab2+kec3+desa3+sls4+sub2`, cth `3509170002004200`) dan `nks` 5 digit (cth `56361`). Keduanya stabil, tidak di-generate. | `app/Core/KodeBps.php:28-42` (`pecah16`, `rakit16`), CHECK regex MySQL |
| K2 | 1 SLS = 1 RT (dusun, RW, RT, ketua, jml KK, klasifikasi). Sub `00` = Peta WS, selain itu Peta WSS. | `app/Views/periode/show.phtml:177-178` |
| K3 | Histori wajib. Periode `DRAFT → AKTIF → TUTUP`. `TUTUP` = read-only. 1 SLS boleh disampel berulang beda periode. | `app/Controllers/PeriodeController.php:176-188`, `app/Services/PenugasanService.php:59-63`, `app/Services/DokumenService.php:50-53` |
| K4 | 1 orang 1 peran per periode. Boleh banyak NKS peran sama; boleh ganti peran antar periode. | `app/Services/PenugasanService.php:26-53` + indikator ⚠ di dropdown `app/Views/periode/show.phtml:134-144` |
| K5 | Satu pool `orang` untuk semua manusia; peran ditentukan per penugasan. `orang_alias` toleransi varian Excel. | Kolom GENERATED `nama_normalized` UNIQUE |
| K6 | Dokumen fisik hanya diproses saat periode `AKTIF`. Pinjam hanya bila status `DITERIMA`. | `app/Services/DokumenService.php:108-211` |
| K7 | Tiap sampel punya 10 ruta (`sampel_ruta.no_urut_ruta` 1–10, UNIQUE). | `database/migrations/004_dokumen_kirim_ruta.sql:4-21`, `app/Repositories/SampelRutaRepository.php:135-147` |

---

## 3. Arsitektur & Alur Request

```
Browser → public/index.php:5 → app/Core/bootstrap.php:41-43
 → Router::dispatch() → Middleware → Controller → Service → Repository → MySQL
 → View .phtml (escape via View::e)
```

| Lapisan | Isi |
|---|---|
| Front Controller | `public/index.php`, `public/.htaccess`, `public/assets/` (AdminLTE/jQuery lokal) |
| Core | `app/Core/Config.php`, `Database.php` (PDO singleton per-request), `Request.php`, `Response.php`, `Router.php:28-46` (match `{param}`), `Session.php`, `Csrf.php`, `Validator.php`, `Excel.php`, `KodeBps.php`, `Logger.php`, `View.php`, `bootstrap.php` (env, error handler, header `X-Frame-Options: DENY` dkk) |
| Middleware | `app/Middleware/AuthMiddleware.php`, `CsrfMiddleware.php` (dipakai di semua POST — `config/routes.php:17-92`), `RoleMiddleware.php` (ada, belum dipasang di semua route tulis) |
| Controller (tipis, tanpa SQL) | `Auth, Dashboard, Orang, User, Sls, Periode, Pengolahan` — `app/Controllers/` |
| Service (aturan bisnis + transaksi) | `AuthService, UserService, OrangService, SlsService, PenugasanService, DokumenService, DokumenKirimService, PengolahanService` — `app/Services/` |
| Repository (PDO prepared) | `Orang, User, Audit, Wilayah, Desa, Sls, Periode, Sampel, SampelRuta, Dokumen` — `app/Repositories/` |
| View | `app/Views/{auth,dashboard,orang,users,sls,periode,pengolahan,layouts,errors}/` (22 file `.phtml`), layout `layouts/header.phtml:89-103` (sidebar: Dasbor, Petugas, SLS, Periode, Pengolahan, Users khusus ADMIN) |
| Config | `config/app.php:6-12`, `config/database.php`, `config/routes.php` |
| DB | `database/schema.sql`, `database/migrations/002a-006_*`, `database/migrate.php`, `database/seeds/001-004` |
| Test | `tests/Tahap1Test.php`, `Tahap2Test.php`, `DokumenTest.php`, `DokumenKirimTest.php`, `PengolahanTest.php`, `ExcelTest.php`, `tests/smoke_tahap1.php` |

---

## 4. Struktur Proyek (Ringkas)

```
public/            DocumentRoot (index.php, .htaccess, assets/)
app/Core/          Config, Database, Request, Response, Router, Session, Csrf, ...
app/Models/        Entity readonly + Enum (Role, Orang, User)
app/Repositories/  PDO prepared (Orang, User, Audit, Wilayah, Desa, Sls, Periode, Sampel, ...)
app/Services/      Bisnis + transaksi (Auth, User, Orang, Sls, Penugasan, Dokumen, ...)
app/Controllers/   Tipis (Auth, Dashboard, Orang, User, Sls, Periode, Pengolahan)
app/Views/         .phtml + layout + error pages
config/            app.php, database.php, routes.php
database/          schema.sql, migrations/, migrate.php, seeds/, backups/
docs/              Dokumentasi (file ini + INSTALASI, PANDUAN-PENGGUNAAN, SKEMA, ...)
tests/             PHPUnit + smoke CLI
storage/logs/      Log aplikasi (jangan diekspos web)
data/              TIDAK di-commit — dokumen BPS RAHASIA (PII)
```

---

## 5. Instalasi & Konfigurasi

Rujukan penuh: `docs/INSTALASI.md`, `docs/DEPLOY.md`. Ringkas:

```bash
composer install
copy .env.example .env        # sesuaikan DB_USER/DB_PASS
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php database/migrate.php      # idempoten; --fresh = reset (hapus data!)
php database/seeds/seed_tahap1.php && php database/seeds/002_wilayah_seed.php && php database/seeds/003_sls_pdf_seed.php && php database/seeds/004_dummy_seed.php
# URUTAN PENTING, jangan paralel
php -S localhost:8091 -t public   # atau Laragon vhost → public/ → http://sipandhalu.test
php vendor/phpunit/phpunit/phpunit --testdox
php tests/smoke_tahap1.php
```

`.env` dev (`/.env:1-16`) vs produksi (`.env.example:1-16`):
`APP_DEBUG true→false` (wajib), `SESSION_SECURE false→true`, `APP_URL` https,
DB user khusus minimal privilege, opcache aktif, backup `mysqldump --single-transaction` berkala.
Hanya `public/` boleh terekspos web server.
Pindah komputer: lihat `docs/PINDAH-KOMPUTER.md` (jalur clone+seed vs dump/restore).

---

## 6. Akun & Peran (RBAC)

9 roles di DB (`005_level_petugas.sql:5-15`): `ADMIN, OPERATOR, PML, PCL, PENGOLAH, PENGAWAS_OLAH, SM_SOSIAL, SM_PLS, VIEWER`.
6 peran login utama tetap dipakai di UI.

| Email | Password | Peran | Catatan |
|---|---|---|---|
| `admin@bpsjember.go.id` | `Admin3509!` | ADMIN | `must_reset=1` → wajib ganti di `/password` |
| 8 email pengolah asli (sheet Rincian) | `Jember3509` | PENGOLAH | `must_reset=1` |
| `pcl.demo@bpsjember.go.id` | `Dummy3509!` | PCL | langsung masuk |
| `pml.demo@bpsjember.go.id` | `Dummy3509!` | PML | langsung masuk |
| `operator.demo@bpsjember.go.id` | `Dummy3509!` | OPERATOR | langsung masuk |
| `viewer.demo@bpsjember.go.id` | `Dummy3509!` | VIEWER | langsung masuk |

Alur: `/login` (CSRF, pesan generik, timing dummy) → bila `must_reset` → `/password` (min 8 char, Argon2id + `password_needs_rehash`) → `/` → logout POST (regenerate session).
HakMenu: sidebar `header.phtml:91-99` — semua login lihat Dasbor/Petugas/SLS/Periode/Pengolahan; `Users` hanya ADMIN.
Hak tulis fungsional: transfer/dokumen/LK hanya `PENGOLAH, OPERATOR, PENGAWAS_OLAH, SM_PLS, ADMIN` (`PengolahanService.php:69,152,250`); `PCL/PML` hanya konfirmasi `ket_*_lapangan` di NKS binaannya (`PengolahanService.php:82-87`).

---

## 7. Panduan Penggunaan per Modul

### 7.1 Dasbor (`/` — `DashboardController.php:15-39`)
CTE ringkas orang/user/sls/periode + 10 audit terakhir. Titik awal navigasi + cek `success/error` flash.

### 7.2 Petugas (`/petugas` — `OrangController`)
Pool semua manusia. Tambah orang + alias (toleransi varian Excel), toggle aktif, export/template/import Excel.
Validasi header fleksibel via `Excel::pick` (`Excel.php:93-106`).

### 7.3 Users (`/users` — khusus ADMIN)
Kelola akun login (tambah/edit/nonaktif/reset password). Terkait `orang_id` + `role_id`.

### 7.4 SLS (`/sls` — `SlsController`)
Master 28 SLS. Cari by NKS/kode_full, tambah/edit, export/template/import Excel.
NKS 5 digit UNIQUE + `kode_full` 16 digit UNIQUE + CHECK regex.

### 7.5 Periode + Sampel + Penugasan (`/periode` — `PeriodeController.php:50-316`)
1. **Buat periode** (`/periode/baru`): tahun 2020–2035, jenis `SERUTI_Q1-Q4, SUSENAS_S1-S2`, mulai `DRAFT`.
2. **Tambah sampel**: manual NKS (`addSampel:190-211`, NKS harus ada di master) atau import Excel (`templateSampel` + `importSampel:243-301`, NKS duplikat dilewati, TUTUP ditolak).
3. **Penugasan K4** (`assign:303-316` → `PenugasanService.php:56-80`): 3 dropdown PCL/PML/Pengolah per baris sampel (`show.phtml:297-304`), tanda ⚠ bila orang sudah pegang peran lain (`show.phtml:134-144`).
4. **Ubah status** (`setStatus:167-188`): hanya `DRAFT→AKTIF→TUTUP`.
5. **Detail** (`show:121-165`): ringkasan CTE (`PeriodeRepository.php:58-76`), beban pengolah + ranking window (`82-102`), paginasi sampel 15/baris + cari NKS/SLS, peta `peranMap` K4, `pmlList`, `rutaSummary`.

### 7.6 Penerimaan & Pinjam Dokumen (`DokumenService.php:38-334`)
Syarat: periode `AKTIF`. Kolom di `sampel` + tabel `peminjaman_dokumen` (`003_dokumen_penerimaan_pinjam.sql`).
* **Terima satuan** (tombol inbox per baris): pilih penyerah PCL/PML, centang Pemutakhiran dan/atau Peta WS/WSS, isi hasil Blok II KK/RT + waktu + catatan.
* **Terima kolektif per PML** (tombol atas, hanya AKTIF): pilih PML → AJAX muat NKS binaan (`ajaxPmlSampel`), checklist NKS + jenis dokumen + KK/RT.
* **Pinjam** (tombol kuning, muncul bila ada `DITERIMA`): jenis `SEMUA/PEMUTAKHIRAN/PETA` (hanya yang `DITERIMA` yang bisa dipilih — JS `show.phtml:654-663`), peminjam + peran + alasan wajib → status jadi `DIPINJAM` (badge kuning).
* **Kembali** (di modal Riwayat bila ada pinjam aktif): isi waktu kembali + catatan → status kembali `DITERIMA`.
* **Riwayat** (tombol biru): timeline pinjam/kembali per NKS (`riwayatDokumen`).
Detail UI: `docs/walkthrough.md:71-86`.

### 7.7 Dok Kirim Kab / Ruta (`DokumenKirimService.php:26-329`)
10 ruta per sampel. Modal **Rincian Ruta** per baris (`show.phtml:771-817`): switch SUDAH/BELUM, checkbox Modul/KP, tanggal, TTD Sos/IPDS, simpan per baris (AJAX) atau **Tandai 10 Ruta Selesai Sekaligus**.
Import/export mengikuti template `Data Progress Pengiriman Kuesioner` (sheet `data` baris 4+, kolom C=NKS D=no E=sudah/belum F/G=1/0 H=tanggal I/J=TTD). NKS divalidasi ke periode; no 1–10. Badge `n/10` + progress bar di tabel sampel (`show.phtml:231-248`).

### 7.8 Pengolahan LK (`/pengolahan` — `PengolahanController.php:44-268`, `PengolahanService.php:19-669`)
* Header: pilih periode (default AKTIF), Export/Import LK Excel.
* 6 kartu: total ruta, dok masuk %, transfer K/KP/Seruti, error.
* Tabel beban 8 pengolah + filter (pengolah/status dok/error/q).
* Tabel ruta inline: toggle dokumen ADA/BELUM, checkbox Trf K/KP/Seruti (simpan AJAX instan), kolom catatan (Error KP/Modul/Uji Petik), tombol **Telaah** → modal 3 tab (KP, Modul, Uji Petik) dengan 3 level tiap tab: temuan Pengolah → konfirmasi Lapangan (PCL/PML) → keputusan Sosial + switch transfer.
* Batch: dropdown transfer serentak + tombol `All` per kolom + modal Terima Dokumen 1 SLS.
* Export multi-sheet (`Alokasi, Rekap, Jadwal Pengawas, <1 sheet per pengolah>`); import sinkronkan status + catatan (`importLkExcel:475-668`, dukung 2 varian kolom Rekap).
* Skema: `006_lk_pengolahan.sql` (11 kolom: `status_dokumen, transfer_k/kp/seruti, ket_kp_*, ket_m_*, uji_petik`).

---

## 8. Referensi Route (`config/routes.php:16-92`)

| Method | Path | Handler |
|---|---|---|
| GET/POST | `/login`, `/logout`, `/password` | `AuthController` |
| GET | `/` | `DashboardController@index` |
| GET/POST | `/petugas`, `/petugas/baru`, `/petugas/export`, `/petugas/template`, `/petugas/import`, `/petugas/{id}`, `/petugas/{id}/edit`, `/petugas/{id}/toggle`, `/petugas/{id}/alias`, `/petugas/{id}/alias/hapus` | `OrangController` |
| GET/POST | `/users`, `/users/baru`, `/users/{id}/edit`, `/users/{id}/toggle`, `/users/{id}/reset` | `UserController` (ADMIN) |
| GET/POST | `/sls`, `/sls/baru`, `/sls/export`, `/sls/template`, `/sls/import`, `/sls/{id}/edit` | `SlsController` |
| GET/POST | `/periode`, `/periode/baru`, `/periode/export`, `/periode/{id}`, `/periode/{id}/status`, `/periode/{id}/sampel`, `/periode/{id}/assign`, `/periode/{id}/template-sampel`, `/periode/{id}/import-sampel` | `PeriodeController` |
| POST/GET | `/periode/{id}/sampel/{sid}/terima`, `/periode/{id}/pml/{pmlId}/sampel`, `/periode/{id}/terima-kolektif`, `/periode/{id}/sampel/{sid}/pinjam`, `/periode/{id}/kembali-dokumen`, `/periode/{id}/sampel/{sid}/riwayat-dokumen` | `PeriodeController` (dok fisik) |
| POST/GET | `/periode/{id}/sampel/{sid}/ruta`, `/periode/{id}/sampel/{sid}/ruta/selesai-semua`, `/periode/{id}/dok-kirim/import`, `/periode/{id}/dok-kirim/export` | `PeriodeController` (ruta) |
| GET/POST | `/pengolahan`, `/pengolahan/update-ruta`, `/pengolahan/terima-dokumen`, `/pengolahan/batch-transfer`, `/pengolahan/export`, `/pengolahan/import` | `PengolahanController` |

Semua route tulis memakai `AuthMiddleware + CsrfMiddleware`. Semua GET list memakai `AuthMiddleware`.

---

## 9. Skema Database (Ringkasan)

Rujukan penuh: `docs/SKEMA-DATABASE.md`. DB `sipandhalu` / `sipandhalu_test`, InnoDB, `utf8mb4_unicode_ci`.

```
roles ← users → orang → orang_alias
kecamatan → desa → sls → sampel → penugasan
periode → sampel → peminjaman_dokumen
sampel → sampel_ruta
users/apa pun → audit_logs (aksi, tabel_target, before/after JSON, ip, user_agent)
```

| Tabel | Kunci penting |
|---|---|
| `roles` | 9 baris, `code UNIQUE` |
| `orang` | `nama_normalized` GENERATED UNIQUE, FULLTEXT, `role_id FK`, `no_hp`, `email` |
| `users` | `email UNIQUE`, `orang_id FK SET NULL`, `role_id FK`, Argon2id, `must_reset` |
| `kecamatan/desa/sls` | 18/27/28 baris; `sls.nks UNIQUE`, `kode_full UNIQUE` + CHECK |
| `periode` | `UNIQUE(tahun,jenis)`, `status DRAFT/AKTIF/TUTUP` |
| `sampel` | `UNIQUE(periode_id,sls_id)`, `target_sampel=10`, kolom dok fisik + `dokumen_vsen/peta_ws` legacy |
| `penugasan` | `sampel_id UNIQUE`, `pcl/pml/pengolah FK→orang`, `status DRAFT/AKTIF/SELESAI/BATAL` |
| `peminjaman_dokumen` | `sampel_id FK CASCADE`, status `DIPINJAM/DIKEMBALIKAN` |
| `sampel_ruta` | `UNIQUE(sampel_id,no_urut_ruta)`, CHECK 1–10, status dok/transfer + `ket_*`, `uji_petik` |
| `audit_logs` | JSON before/after, `ip VARBINARY(16)` |

Migrasi (`database/migrations/`): `002a_wilayah, 002b_periode, 002c_desa_sls, 002d_wilayah_final, 003_dokumen_penerimaan_pinjam, 004_dokumen_kirim_ruta, 005_level_petugas, 006_lk_pengolahan` via `migrate.php` (idempoten).
Seed (BERURUTAN): `seed_tahap1 → 002_wilayah_seed → 003_sls_pdf_seed → 004_dummy_seed`.
Data kini: 51 orang, 13 users, 66 sampel/penugasan, 3 periode.

Fitur MySQL 8: CTE + window (`ringkasan`, `bebanPengolah`, rekap beban LK), JSON audit, GENERATED, CHECK.

---

## 10. Import / Export Excel (`app/Core/Excel.php:18-225`)

* Helper terpusat: `download/save` (header navy, freeze, autosize, sheet Petunjuk), `readFirstSheet` (header baris 1), `pick` (match kolom abaikan case/spasi), `parseDate` (serial/`d/m/Y`/`Ymd`), `cellToString` (float bulat tanpa `.0`, RichText).
* Batas: `MAX_IMPORT_ROWS=2000`, `cekUpload` 5 MB + ekstensi xls/xlsx.
* Template: petugas, SLS, sampel per periode, Dok Kirim Kab (layout presisi + sheet `contoh`), LK Pengolahan multi-sheet.
* Pola import: baca → validasi per baris (NKS ada? format benar? duplikat?) → `ok + errors[]` → flash `success + import_errors` (max 20 ditampilkan).

---

## 11. Keamanan

Sudah ada: `strict_types` semua file · PDO prepared + `EMULATE_PREPARES=false` · Argon2id + rehash · CSRF `hash_equals` (419) · session hardening (`httponly`, `samesite`, `regenerate_id` saat login, `SESSION_SECURE` prod) · header `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy` (`bootstrap.php:37-39`) · escape `View::e` · audit JSON + IP/UA tiap mutasi · pesan login generik + timing dummy · `data/`, `.env`, log di-ignore git.
Gap (lihat `docs/ANALISIS.md:69`): tanpa rate-limit login, tanpa CSP/HSTS, `RoleMiddleware` belum di semua route tulis (masih cek di service/controller), `APP_DEBUG=true` hanya untuk dev.

---

## 12. Testing & Verifikasi

```bash
php vendor/phpunit/phpunit/phpunit --testdox  # unit + integrasi DB sipandhalu_test
php tests/smoke_tahap1.php                     # smoke CLI DB dev (koneksi, seed, Argon2id, alias, CTE, view ter-parse)
php -l app/...                                 # lint sintaks
```

Cakupan: validator, dedup orang, login/reset, kode 16 digit, NKS unik, K4, rekap 28×10, terima/pinjam/kembali + tolak non-AKTIF (`DokumenTest`), ruta import/export (`DokumenKirimTest`), LK transfer + telaah + RBAC (`PengolahanTest`).

---

## 13. Troubleshooting

| Gejala | Solusi |
|---|---|
| Blank/500 | cek `storage/logs/`, pastikan `APP_DEBUG=true` saat dev |
| DB gagal konek | cek `.env` + MySQL Laragon jalan |
| 419 saat submit | sesi habis → refresh |
| Data dummy hilang | ulang `004_dummy_seed.php` (idempoten) |
| Tabel kosong | migrate + seed sesuai urutan §5 |
| `sipandhalu.test` tak bisa dibuka | tambah `127.0.0.1 sipandhalu.test` ke hosts atau pakai `php -S localhost:8091 -t public` |
| Import ditolak | cek format/ukuran (§10), NKS harus ada di master/SLS periode |

Rekap cepat SQL (`docs/PANDUAN-PENGGUNAAN.md:66-70`):
```sql
SELECT pe.label, pe.status, COUNT(*) sampel, COUNT(pg.id) tugas
FROM periode pe JOIN sampel sp ON sp.periode_id=pe.id
LEFT JOIN penugasan pg ON pg.sampel_id=sp.id GROUP BY pe.id;
```

---

## 14. Roadmap

- [x] Tahap 1, 2, 4 + dummy + docs
- [ ] Tahap 3: RBAC tulis server-side di semua controller (pindah cek service → `RoleMiddleware` di `routes.php`)
- [ ] Rate-limit `/login` + `/password`, CSP, HSTS, `APP_DEBUG=false` prod
- [ ] Dashboard rekap triwulan vs semester (CTE + window)
- [ ] Pad `kode_full` 27 SLS lain dari BPS
- [ ] Pecah `periode/show.phtml` (975 baris) jadi partial + JS terpisah; optimasi rekap untuk data besar

---

## 15. Lisensi & Kredit

Internal **BPS Kabupaten Jember**. Branding SIPANDHALU dari kearifan lokal *Pandhalungan* (Jawa-Madura-Osing-Bugis).

*Dokumen ini digenerate dari baca kode per 2026-09-19. Bila kode berubah, perbarui §7–§9 terlebih dulu.*
