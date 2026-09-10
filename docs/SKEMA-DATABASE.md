# Skema Database & Keputusan Desain

Database: `sipandhalu` (dev) / `sipandhalu_test` (PHPUnit) — MySQL 8, InnoDB, `utf8mb4_unicode_ci`.

## 1. Keputusan Desain yang Mengikat (hasil diskusi)

| # | Keputusan | Alasan |
|---|---|---|
| K1 | `kode_sls` ≠ `nks`; simpan keduanya | Terbukti dari PDF BPS: SLS `0042`, NKS `56361` untuk RT yang sama |
| K2 | 1 SLS = 1 RT | Ada dusun, RW, RT, ketua, jumlah KK |
| K3 | Kolom H sheet Rincian diabaikan; 28 baris Excel = 28 SLS sampel (masing-masing 10 RT sampel) | Kesepakatan dengan pengguna |
| K4 | 1 orang **1 peran per periode** (boleh banyak NKS peran sama; boleh ganti peran antar periode) | Ditegakkan `PenugasanService::validate` + query verifikasi |
| Q2/Q3 | Histori wajib; 1 SLS boleh disampel berulang | Tidak ada delete/timpa; kunci unik per periode |
| Q6 | Satu frame SLS untuk Susenas + Seruti | Cukup 1 master SLS |
| S1 | Level data = SLS (tanpa Blok V per KRT) | Pengguna memilih cukup level SLS |

## 2. Diagram ER (tekstual)

```
MASTER (stabil)
roles(ADMIN,OPERATOR,PML,PCL,PENGOLAH,VIEWER)
orang(id, nama kanonik, nama_normalized GENERATED UNIQUE)  1--N  orang_alias
orang 1--0..1 users(orang_id, email UNIQUE, password_hash Argon2id, must_reset, last_login_at)

kecamatan(kode CHAR(3) PK, nama UNIQUE)  1--N  desa(kecamatan_kode FK, kode CHAR(3), UNIQUE(kec,kode))
desa 1--N sls(kode_full CHAR(16) UNIQUE, kec/desa, sls CHAR(4), sub CHAR(2),
              nks CHAR(5) UNIQUE, dusun, rw, rt, nama_sls, ketua, klasifikasi, jml_kk, jml_rt, is_aktif)

PERIODE (histori)
periode(tahun, jenis ENUM(SERUTI_Q1..Q4, SUSENAS_S1,S2), label, tgl_mulai/selesai,
        status ENUM(DRAFT,AKTIF,TUTUP), UNIQUE(tahun,jenis))
periode 1--N sampel(periode_id FK, sls_id FK, target_sampel=10, muatan_awal,
                    hasil_kk, hasil_rt, dokumen_vsen, peta_ws, UNIQUE(periode_id,sls_id))
sampel 1--1 penugasan(sampel_id UNIQUE FK, pcl_id FK, pml_id FK, pengolah_id FK,
                      status ENUM(DRAFT,AKTIF,SELESAI,BATAL))

audit_logs(user_id, aksi, tabel_target, id_target, before_json, after_json, ip VARBINARY(16))
```

## 3. Tabel (11)

| Tabel | Isi | Kunci/Index penting |
|---|---|---|
| `roles` | 6 peran login | `code UNIQUE` |
| `orang` | pool semua petugas + admin | `nama_normalized` GENERATED UNIQUE, FULLTEXT |
| `orang_alias` | varian tulisan Excel (6 mismatch nama) | `alias_normalized UNIQUE` |
| `users` | akun login | `email UNIQUE`, `role_id FK`, `orang_id FK SET NULL` |
| `kecamatan` | 18 wilayah | `kode CHAR(3) PK` |
| `desa` | 27 desa | UNIQUE(kecamatan_kode, kode), FK kecamatan |
| `sls` | 28 SLS/RT | `nks UNIQUE`, `kode_full UNIQUE` + CHECK regex 16 digit |
| `periode` | gelombang survei | UNIQUE(tahun, jenis), status DRAFT→AKTIF→TUTUP |
| `sampel` | SLS terpilih per periode | UNIQUE(periode_id, sls_id), FK RESTRICT |
| `penugasan` | PCL+PML+Pengolah per sampel | `sampel_id UNIQUE`, 3 FK ke orang |
| `audit_logs` | jejak perubahan | JSON before/after |

## 4. Aturan yang Ditegakkan

- **K4** — `PenugasanService::validate`: 3 peran 1 sampel wajib 3 orang berbeda;
  1 orang tidak boleh 2 peran dalam 1 periode. Query verifikasi:
  ```sql
  SELECT periode_id, o, COUNT(DISTINCT peran) c FROM (
    SELECT sp.periode_id, pg.pcl_id o, 'PCL' peran FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
    UNION ALL
    SELECT sp.periode_id, pg.pml_id, 'PML' FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
    UNION ALL
    SELECT sp.periode_id, pg.pengolah_id, 'PENGOLAH' FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
  ) t GROUP BY periode_id, o HAVING c > 1;   -- harus 0 baris
  ```
- **Periode TUTUP** → semua perubahan penugasan/sampel ditolak (read-only).
- **CHECK MySQL 8** — `nks` regex `^[0-9]{5}$`, `kode_full` regex `^[0-9]{16}$` atau NULL.
- FK `ON DELETE RESTRICT` (kecuali `users.orang_id SET NULL`, `sls.desa_id SET NULL`).

## 5. Fitur MySQL 8 yang Dipakai

- **CTE + window function** — rekap per peran (`UserRepository::countByRole`),
  rekap beban per periode (COUNT/SUM per pengolah, target 28×10=280).
- **JSON** — `audit_logs.before_json / after_json` (before-after audit trail).
- **GENERATED column** — `orang.nama_normalized` (LOWER+TRIM) untuk dedup & alias.
- **CHECK constraint** — validasi regex kode di level database.

## 6. Isi Data Saat Ini

| Tabel | Baris | Sumber |
|---|---|---|
| `kecamatan` / `desa` | 18 / 27 | Excel `Alokasi (2).xlsx` |
| `sls` | 28 | Excel; `56361` dipadankan PDF BPS |
| `periode` | 3 | 2026-S2 AKTIF (Excel), 2026-S1 TUTUP + 2026-Q1 DRAFT (dummy) |
| `sampel` / `penugasan` | 66 / 66 | seed 002 + 004 |
| `orang` / `users` | 51 / 13 | 8 pengolah asli + 43 dummy + admin + 4 akun demo |

## 7. Migrasi & Seed

```
database/
  schema.sql                # tahap 1 (roles, orang, alias, users, audit)
  migrations/
    002d_wilayah_final.sql  # kecamatan, desa, sls (kode 16 digit)
    002b_periode.sql        # periode, sampel, penugasan
  migrate.php               # runner idempoten (--fresh = reset)
  seeds/
    001_roles.sql, seed_tahap1.php, 002_wilayah_seed.php,
    003_sls_pdf_seed.php, 004_dummy_seed.php
```

Urutan jalankan seed: `seed_tahap1` → `002_wilayah_seed` → `003_sls_pdf_seed` → `004_dummy_seed` (berurutan, jangan paralel).
