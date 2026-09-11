# Rancangan: Alur Dokumen & Feedback Periode (Pemutakhiran Susenas)

> Dokumen rancangan untuk persetujuan — belum ada implementasi kode.
> Cakupan: penerimaan dokumen peta & pemutakhiran → penyerahan → feedback CLEAN/ERROR
> multi-level per NKS → status "lolos" otomatis.

## 1. Tujuan

Menyelaraskan SIPANDHALU dengan alur kerja lapangan yang mengikat:

1. Operator meng-*entry* daftar sampel/NKS per periode *(fitur ada)*.
2. Operator memasukkan nama pencacah, pengawas, petugas pengolah/entry *(fitur ada)*.
3. Operator menata penugasan: PCL diawasi PML, NKS dialokasikan, pengolah ditunjuk *(fitur ada, K4)*.
4. Dokumen **peta (WS)** dan **pemutakhiran (VSEN)** diterima → **ditandai di sistem** oleh
   operator **atau** pengawas pengolahan *(BARU)*.
5. Dokumen **diserahkan** — dicatat **tanggal dan jam**, menyesuaikan jadwal kegiatan periode *(BARU)*.
6. Entry dilakukan di **aplikasi pengolahan eksternal** → **TIDAK dibangun di sini**;
   sistem ini menjadi kanal feedback-nya.
7. **Feedback CLEAN/ERROR + rincian error** per NKS/SLS berbentuk **percakapan terbuka**
   yang bisa diisi berkali-kali oleh semua level *(BARU)*.
8. Dokumen dianggap **lolos hanya jika semua level menyatakan CLEAN** *(rule otomatis, BARU)*.

## 2. Skema Data

### 2.1 Tabel yang TIDAK berubah

`periode`, `sls`, `penugasan`, `orang`, `orang_alias`, `users`, `roles`, `audit_logs` —
struktur tetap; tidak ada perubahan pada fitur yang sudah jalan.

### 2.2 Kolom baru pada tabel `sampel`

| Kolom | Tipe | Arti |
|---|---|---|
| `dok_vsen_oleh` | INT UNSIGNED NULL, FK → users (SET NULL) | user yang menandai dokumen VSEN diterima |
| `dok_vsen_pada` | DATETIME NULL | waktu penandaan penerimaan VSEN |
| `peta_oleh` | INT UNSIGNED NULL, FK → users (SET NULL) | user yang menandai peta WS diterima |
| `peta_pada` | DATETIME NULL | waktu penandaan penerimaan peta |
| `status_dok` | ENUM('MENUNGGU','PERLU_PERBAIKAN','CLEAN') NOT NULL DEFAULT 'MENUNGGU' | status kualitas dokumen NKS; dihitung ulang otomatis |

Kolom lama `dokumen_vsen` / `peta_ws` (TINYINT flag) **tetap** menjadi penanda utama
"sudah masuk" — dipakai ringkasan periode yang sudah berjalan. Kolom `*_oleh`/`*_pada`
hanya menambah jejak siapa/kapan. NULL = belum pernah ditandai.

### 2.3 Tabel baru `penyerahan_dokumen` (riwayat — boleh lebih dari 1 per sampel)

Sengaja berupa tabel riwayat: setelah ERROR dan perbaikan dokumen, dokumen bisa
**diserahkan ulang**, sehingga catatan serah lama tidak hilang.

| Kolom | Tipe | Arti |
|---|---|---|
| `id` | INT UNSIGNED PK | — |
| `sampel_id` | INT UNSIGNED NOT NULL, FK → sampel (RESTRICT) | NKS/SLS terkait |
| `diserahkan_oleh` | INT UNSIGNED NULL, FK → users (SET NULL) | operator yang menyerahkan |
| `diterima_oleh` | INT UNSIGNED NULL, FK → users (SET NULL) | pengawas pengolahan yang menerima |
| `waktu_serah` | DATETIME NOT NULL | **tanggal dan jam penyerahan** |
| `catatan` | VARCHAR(255) NULL | mis. "serah ulang hasil revisi" |
| `created_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | — |

INDEX: `(sampel_id)`, `(waktu_serah)`.

### 2.4 Tabel baru `dokumen_feedback` (percakapan — baris baru setiap kali diisi)

| Kolom | Tipe | Arti |
|---|---|---|
| `id` | INT UNSIGNED PK | — |
| `sampel_id` | INT UNSIGNED NOT NULL, FK → sampel (RESTRICT) | NKS/SLS yang dibicarakan |
| `user_id` | INT UNSIGNED NULL, FK → users (SET NULL) | penulis pesan |
| `level` | VARCHAR(30) NOT NULL | **snapshot peran saat menulis**: PCL / PML / PENGOLAH / PENGAWAS_OLAH / SM_SOSEK / SM_OLAH / OPERATOR / ADMIN |
| `jenis_dok` | ENUM('UMUM','PETA','PEMUTAKHIRAN') DEFAULT 'UMUM' | opsional: dokumen yang dibahas |
| `status` | ENUM('CLEAN','ERROR','CATATAN') NOT NULL | penilaian; CATATAN = obrolan biasa (tidak dihitung sebagai penilaian) |
| `isi` | TEXT NOT NULL | rincian catatan / rincian errornya apa |
| `created_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | waktu kirim |

INDEX: `(sampel_id, created_at)`, `(status)`.
`level` disimpan sebagai snapshot agar riwayat tetap benar meski peran user berubah nanti.

### 2.5 Peran baru pada tabel `roles`

| Kode | Label | Fungsi di alur |
|---|---|---|
| `PENGAWAS_OLAH` | Pengawas Pengolahan | menandai penerimaan dokumen, menerima serahan |
| `SM_SOSEK` | SM Tim Statistik Sosial | memberi feedback level subject matter sosial |
| `SM_OLAH` | SM Tim Pengolahan & Layanan Statistik | memberi feedback level subject matter pengolahan |

Peran yang sudah ada dan tetap dipakai: `ADMIN`, `OPERATOR`, `PENGOLAH` (petugas
pengolah/entry), `PCL`, `PML`, `VIEWER`.

### 2.6 Aturan status dokumen (`status_dok`) — dihitung ulang otomatis

- **Level penilai** = 5 level: `PCL`, `PML`, `PENGOLAH`, `SM_SOSEK`, `SM_OLAH`.
  Pesan berstatus `CATATAN` **tidak dihitung** sebagai penilaian.
- Untuk tiap level penilai, ambil penilaian **terakhir** di thread sampel tersebut:
  - kelima level terakhirnya **CLEAN** → `status_dok = CLEAN` (dokumen **lolos**);
  - ada level yang terakhirnya **ERROR** → `status_dok = PERLU_PERBAIKAN`;
  - ada level yang **belum pernah menilai** → `status_dok = MENUNGGU`.
- Perhitungan dijalankan di Service (satu transaksi dengan penyimpanan feedback) dan
  hasilnya disimpan ke `sampel.status_dok` agar rekap/filter di halaman periode cepat.
- **Escape hatch**: ADMIN punya tombol "Tandai Lolos Manual" untuk kasus khusus
  (mis. ada level tidak tersedia) — selalu tercatat di `audit_logs`.

## 3. Hak Akses (RBAC — ditegakkan di sisi server)

| Aksi | ADMIN | OPERATOR | PENGAWAS_OLAH | PENGOLAH | PCL | PML | SM_SOSEK | SM_OLAH | VIEWER |
|---|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| Lihat semua data & thread | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Kelola periode/sampel/penugasan | ✓ | ✓ | — | — | — | — | — | — | — |
| Tandai penerimaan dokumen | ✓ | ✓ | ✓ | — | — | — | — | — | — |
| Catat penyerahan dokumen | ✓ | ✓ | ✓ | — | — | — | — | — | — |
| Buka/isi feedback (CLEAN/ERROR/CATATAN) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — |
| Tandai lolos manual | ✓ | — | — | — | — | — | — | — | — |
| Kelola users/roles | ✓ | — | — | — | — | — | — | — | — |

Aturan pendukung:
- **Periode TUTUP = read-only**: semua aksi tulis (penerimaan, serah, feedback) diblokir —
  konsisten dengan prinsip histori yang sudah berlaku.
- Feedback hanya aktif saat periode **AKTIF** (saat DRAFT pelaksanaan belum jalan;
  setelah TUTUP tidak ada perubahan).
- `waktu_serah` tidak boleh mendahului waktu penerimaan dokumen terkait (ditolak dengan pesan jelas).
- Semua aksi tulis dicatat ke `audit_logs` (infrastruktur sudah ada).

## 4. Layar (UI)

### 4.1 Halaman detail periode (`/periode/{id}`) — diperkaya
- Tabel sampel mendapat kolom: **Dokumen** (badge VSEN ✓/✗ dan Peta ✓/✗ + tanggal),
  **Diserahkan** (tanggal+jam terakhir), **Status Dok** (badge CLEAN / PERLU PERBAIKAN / MENUNGGU),
  dan tombol **Detail** per baris.
- **Info-box rekap**: NKS Lolos · Perlu Perbaikan · Menunggu Penilaian · Dokumen belum lengkap.
- Filter daftar sampel berdasarkan status dokumen.
- Form "tambah NKS", import Excel, dan penugasan **tetap seperti sekarang**.

### 4.2 Halaman detail sampel (BARU) — `/periode/{pid}/sampel/{sid}`
- Kartu identitas NKS/SLS + petugas (PCL / PML / Pengolah).
- Panel **Penerimaan**: dua baris dokumen (VSEN, Peta) dengan status diterima/belum,
  tombol "Tandai diterima", jejak siapa + kapan.
- Panel **Penyerahan**: form tanggal+jam, riwayat serah (termasuk serah ulang).
- Panel **Percakapan feedback**: kronologis, tiap pesan menampilkan badge level penulis +
  status (CLEAN hijau / ERROR merah / CATATAN abu), form pesan baru (status, jenis dokumen, isi).
- Gaya visual mengikuti standar yang sudah ditetapkan di `periode/baru`: skema indigo–emas,
  responsif (desktop/tablet/mobile), animasi transisi halus, aksesibel (fieldset/legend,
  label-for, aria).

### 4.3 Halaman daftar periode (`/periode`)
- Info-box agregat status dokumen antar periode (lolos / perlu perbaikan / menunggu).

## 5. Route Baru

| Method | Path | Fungsi | Akses |
|---|---|---|---|
| GET | `/periode/{pid}/sampel/{sid}` | detail sampel + thread | semua login |
| POST | `/periode/{pid}/sampel/{sid}/terima` | tandai penerimaan dokumen | ADMIN, OPERATOR, PENGAWAS_OLAH |
| POST | `/periode/{pid}/sampel/{sid}/serah` | catat penyerahan | ADMIN, OPERATOR, PENGAWAS_OLAH |
| POST | `/periode/{pid}/sampel/{sid}/feedback` | kirim pesan feedback | semua level kecuali VIEWER |
| POST | `/periode/{pid}/sampel/{sid}/lolos` | tandai lolos manual | ADMIN |

Semua route POST dijaga `AuthMiddleware` + `CsrfMiddleware` (pola yang sudah ada).

## 6. Migrasi

File baru `database/migrations/003_feedback_dokumen.sql` (pola sama dengan 002b, idempotent):
1. `ALTER TABLE sampel` — tambah 5 kolom (2.2).
2. `CREATE TABLE penyerahan_dokumen` (2.3).
3. `CREATE TABLE dokumen_feedback` (2.4).
4. `INSERT IGNORE` peran baru ke `roles` (2.5).
5. Data seed yang sudah ada tidak diubah; `status_dok` semua sampel lama default `MENUNGGU`.

## 7. Testing & Penerimaan

- **PHPUnit** (menambah test, yang lama tidak boleh rusak):
  - rule status 5-level (semua CLEAN → CLEAN; satu ERROR → PERLU_PERBAIKAN; belum lengkap → MENUNGGU);
  - CATATAN tidak mengubah status;
  - validasi K4 penugasan tetap berjalan;
  - RBAC: VIEWER tidak bisa menulis; level lain sesuai matriks.
- **Uji HTTP**: alur penuh (tandai terima → serah → thread ERROR → perbaikan → CLEAN semua → lolos).
- **Responsif**: cek 375 / 768 / 1366 px pada tampilan baru.

## 8. Keputusan yang Dimintakan Persetujuan

1. Metadata penerimaan ditambah sebagai **kolom baru di `sampel`** (bukan tabel terpisah) — setuju?
2. Penyerahan berupa **tabel riwayat** (boleh serah ulang) — setuju?
3. Pesan feedback **wajib memilih status** CLEAN/ERROR/CATATAN; CATATAN tidak dihitung sebagai penilaian — setuju?
4. Aturan lolos dihitung dari **5 level penilai**; ADMIN punya tombol lolos manual — setuju?
5. RBAC ditegakkan server-side mulai modul ini (menyentuh route periode/sampel yang selama ini hanya cek login) — setuju?

