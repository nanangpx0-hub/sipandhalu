# Basis Data SIPANDHALU

## Skema Umum

Database: `sipandhalu`
Charset: utf8mb4, Collation: utf8mb4_unicode_ci
Engine: InnoDB (semua tabel)

## Daftar Tabel

### 1. `roles` — Peran Pengguna

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | TINYINT UNSIGNED PK | ID peran |
| code | VARCHAR(20), UNIQUE | Kode peran (ADMIN, OPERATOR, PCL, PENGOLAH, VIEWER, dll.) |
| label | VARCHAR(60) | Label deskriptif |
| created_at | TIMESTAMP | Waktu pembuatan |

### 2. `orang` — Master Orang/Petugas

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID orang |
| nama | VARCHAR(100), NOT NULL | Nama kanonik (Title Case, TRIM) |
| nama_normalized | VARCHAR(100) GENERATED | LOWER(TRIM(nama)) — untuk dedup |
| no_hp | VARCHAR(20), NULL | Nomor HP |
| email | VARCHAR(190), NULL | Email |
| alamat | VARCHAR(255), NULL | Alamat |
| is_aktif | TINYINT(1) | 1=aktif, 0=nonaktif |
| created_at / updated_at | TIMESTAMP | Timestamp |

**Index**: UNIQUE(nama_normalized), ix_orang_aktif, ix_orang_nama, FULLTEXT(ft_orang_nama)

### 3. `orang_alias` — Alias Nama

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID alias |
| orang_id | INT UNSIGNED FK | Referensi ke orang |
| alias_normalized | VARCHAR(100) | LOWER(TRIM(varian)) |
| created_at | TIMESTAMP | Waktu pembuatan |

**Index**: UNIQUE(alias_normalized), ix_alias_orang

### 4. `users` — Akun Login

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID user |
| orang_id | INT UNSIGNED FK, NULL | Referensi ke orang |
| nama | VARCHAR(100) | Nama user |
| email | VARCHAR(190), UNIQUE | Email login |
| password_hash | VARCHAR(255) | Hash Argon2id |
| role_id | TINYINT UNSIGNED FK | Referensi ke roles |
| is_aktif | TINYINT(1) | 1=aktif |
| must_reset | TINYINT(1) | 1=wajib ganti password pertama kali |
| last_login_at | TIMESTAMP, NULL | Login terakhir |
| created_at / updated_at | TIMESTAMP | Timestamp |

### 5. `audit_logs` — Jejak Audit

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | ID log |
| user_id | INT UNSIGNED FK, NULL | ID user yang melakukan aksi |
| aksi | VARCHAR(20) | CREATE/UPDATE/DELETE/LOGIN/LOGOUT/RESET_PW/IMPORT |
| tabel_target | VARCHAR(50) | Tabel yang terdampak |
| id_target | VARCHAR(50), NULL | ID record target |
| before_json | JSON, NULL | Data sebelum perubahan |
| after_json | JSON, NULL | Data sesudah perubahan |
| ip_address | VARCHAR(45), NULL | IP address |
| user_agent | VARCHAR(255), NULL | User agent |
| created_at | TIMESTAMP | Waktu aksi |

### 6. `kecamatan` — Kecamatan

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID kecamatan |
| kode | VARCHAR(6) | Kode BPS kecamatan (6 digit) |
| nama | VARCHAR(100) | Nama kecamatan |
| kabupaten | VARCHAR(100) | Kabupaten |

### 7. `desa` — Desa

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID desa |
| kecamatan_id | INT UNSIGNED FK | Referensi ke kecamatan |
| kode | VARCHAR(10) | Kode BPS desa (10 digit) |
| nama | VARCHAR(100) | Nama desa |

### 8. `sls` — SLS/RT

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID SLS |
| desa_id | INT UNSIGNED FK | Referensi ke desa |
| nks | VARCHAR(5) | NKS (5 digit) |
| nama_sls | VARCHAR(100) | Nama SLS |
| kode_full | VARCHAR(16) | Kode BPS lengkap (16 digit) |
| rt | VARCHAR(3) | RT |
| rw | VARCHAR(3) | RW |
| dusun | VARCHAR(50) | Nama dusun |
| klasifikasi | TINYINT | Klasifikasi perkotaan/perdesaan |
| jml_kk | INT | Jumlah KK |
| jml_rt | INT | Jumlah RT |
| is_aktif | TINYINT(1) | 1=aktif |

### 9. `periode` — Periode Survei

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID periode |
| tahun | INT | Tahun (2026) |
| jenis | ENUM | SUSENAS_S1, SUSENAS_S2, SERUTI_Q1-Q4 |
| label | VARCHAR(100) | Label deskriptif |
| tgl_mulai | DATE | Tanggal mulai |
| tgl_selesai | DATE | Tanggal selesai |
| status | ENUM | DRAFT, AKTIF, TUTUP |
| catatan | TEXT | Catatan |
| created_at / updated_at | TIMESTAMP | Timestamp |

**Status flow**: DRAFT → AKTIF → TUTUP

### 10. `sampel` — Sampel per Periode per SLS

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID sampel |
| periode_id | INT UNSIGNED FK | Referensi ke periode |
| sls_id | INT UNSIGNED FK | Referensi ke SLS |
| target_sampel | INT | Target sampel (KK/RT) |
| muatan_awal | INT | Muatan awal |
| dokumen_vsen | TINYINT | Status dokumen vsense |
| peta_ws | TINYINT | Status peta workspace |

### 11. `penugasan` — Penugasan Petugas per SLS

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID penugasan |
| sampel_id | INT UNSIGNED FK | Referensi ke sampel |
| pcl_id | INT UNSIGNED FK, NULL | PCL assigned |
| pml_id | INT UNSIGNED FK, NULL | PML assigned |
| pengolah_id | INT UNSIGNED FK, NULL | Pengolah assigned |
| status | ENUM | AKTIF, SELESAI, DRAFT, BELUM |

**Aturan K4**: 1 orang 1 peran per periode

### 12. `sampel_ruta` — Rute Pengolahan

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID ruta |
| sampel_id | INT UNSIGNED FK | Referensi ke sampel |
| no_urut_ruta | INT | Nomor urut rute |
| status_dokumen | ENUM | ADA, TIDAK, BELUM |
| status_transfer_k | TINYINT | Transfer ke |
| status_transfer_kp | TINYINT | Transfer KP |
| status_transfer_seruti | TINYINT | Transfer Seruti |
| catatan_kp | TEXT | Catatan KP |
| catatan_modul | TEXT | Catatan modul |
| uji_petik_pengawas | TINYINT | Hasil uji petik |
| dan lainnya | | Kolom kendali mutu lainnya |

### 13. `peminjaman_dokumen` — Peminjaman Dokumen Lapangan

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID |
| ... | | (lihat schema) |

### 14. `dokumen_kirim` — Pengiriman Kuesioner ke Kabupaten

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID |
| ... | | (lihat schema) |

### 15. `jadwal_pengawas` — Jadwal Pengawas

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED PK | ID |
| ... | | (lihat schema) |

## Hubungan Tabel (ERD)

```
roles ──┐
        │ (role_id FK)
users ──┘
        │ (orang_id FK, nullable)
orang ──┘
        │ (FK)       │ (FK)
orang_alias    penugasan
                     │ (sampel_id FK)
                     sampel
                     │ (periode_id FK, sls_id FK)
                     │              │
                    periode          sls
                     │              │ (desa_id FK)
                     │              desa
                     │              │ (kecamatan_id FK)
                     │              kecamatan
                     │ (sampel_id FK)
                     sampel_ruta
```

## Kode BPS

- **Kecamatan**: 6 digit (contoh: 350904)
- **Desa**: 10 digit (contoh: 3509040010)
- **NKS**: 5 digit (contoh: 0042)
- **Kode SLS full**: 16 digit (contoh: 3509170002004200)
  = kode_kecamatan(6) + kode_desa(4 digit terakhir) + kode_sls(4 digit) + rt(2) + rw(2)
  = 350917 0002 0042 0014
