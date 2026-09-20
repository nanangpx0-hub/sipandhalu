# Panduan Penggunaan & Login

## 1. Membuka Aplikasi

1. Nyalakan **Laragon** → Start All (Apache + MySQL).
2. Pastikan DocumentRoot mengarah ke `...\susenas-seruti\public`
   → buka `http://susenas-seruti.test`
   (atau `php -S localhost:8091 -t public` → `http://localhost:8091`).
3. Halaman pertama = halaman **Login** (`/login`).

## 2. Daftar Akun & Password

| Email | Password | Peran | Catatan |
|---|---|---|---|
| `admin@bpsjember.go.id` | `Jember3509` | ADMIN | ⚠️ diminta ganti password saat login pertama |
| `aminatuss182002@gmail.com` | `Jember3509` | PENGOLAH | ⚠️ wajib ganti password (8 akun pengolah asli dari sheet Rincian) |
| `anunganindhitap@gmail.com` | `Jember3509` | PENGOLAH | idem |
| `putrisalsabhilafahira10@gmail.com` | `Jember3509` | PENGOLAH | idem |
| `bpsbpsiffa36@gmail.com` | `Jember3509` | PENGOLAH | idem |
| `prasistiwi@gmail.com` | `Jember3509` | PENGOLAH | idem |
| `lavianaikarumby@gmail.com` | `Jember3509` | PENGOLAH | idem |
| `nidasuryandari@gmail.com` | `Jember3509` | PENGOLAH | idem |
| `a.widarianti@gmail.com` | `Jember3509` | PENGOLAH | idem |
| `pcl.demo@bpsjember.go.id` | `Jember3509` | PCL | ✅ langsung masuk |
| `pml.demo@bpsjember.go.id` | `Jember3509` | PML | ✅ langsung masuk |
| `operator.demo@bpsjember.go.id` | `Jember3509` | OPERATOR | ✅ langsung masuk |
| `viewer.demo@bpsjember.go.id` | `Jember3509` | VIEWER | ✅ langsung masuk |

> Akun dengan `must_reset` (admin + 8 pengolah) diarahkan ke `/password` setelah login:
> isi password baru (minimal 8 karakter). Password disimpan sebagai hash **Argon2id**,
> tidak ada plaintext di database.

## 3. Alur Login

1. Buka `/login` → email + password → tombol **Masuk** (dilindungi CSRF).
2. Bila `must_reset=1` → diarahkan ke `/password` → ganti password → login normal.
3. **Logout** via menu → sesi dihancurkan (`session_regenerate_id` saat login).
4. Password salah → pesan generik (tidak membocorkan email terdaftar).

## 4. Yang Bisa Dicoba per Peran

| Sebagai | Coba ini |
|---|---|
| **ADMIN** | Dasbor `/` · master petugas `/petugas` (tambah orang + alias) · manajemen user `/users` (tambah/nonaktifkan) · SLS `/sls` · periode `/periode` |
| **PCL demo** | Lihat penugasan periode aktif; akses halaman admin → ditolak **403** |
| **PML demo** | Pantau sampel yang diawasi |
| **OPERATOR demo** | Kelola penugasan periode AKTIF/DRAFT; periode TUTUP otomatis **read-only** |
| **VIEWER demo** | Hanya melihat rekap |

## 5. Skenario Uji Coba (sesuai model dinamis)

1. **Histori**: buka periode `2026-S1 Susenas Maret` (TUTUP) → 28 penugasan SELESAI →
   coba ubah → ditolak *read-only*.
2. **Rotasi petugas**: bandingkan PCL pada NKS yang sama di S1 vs S2 → petugasnya berbeda.
3. **SLS berulang**: NKS `56361` (ROWO TENGAH RT 001 / RW 014 / Dusun SADENGAN)
   muncul di 3 periode dengan baris sampel berbeda.
4. **Aturan K4**: di periode S2, coba set PML = orang yang sudah jadi PCL di periode itu
   → ditolak: *"tidak boleh rangkap (K4)"*.
5. **Muatan vs pemutakhiran**: sampel `56361` di S2 → `muatan_awal=101` (frame Excel),
   `hasil_kk=100 / hasil_rt=97` (hasil pemutakhiran PDF BPS).
6. **Audit**: setelah melakukan perubahan →
   `SELECT aksi, tabel_target, after_json FROM audit_logs ORDER BY id DESC LIMIT 5`

## 6. Rekap Cepat via SQL

```bash
mysql -u root sipandhalu -e "SELECT pe.label, pe.status, COUNT(*) sampel, COUNT(pg.id) tugas
  FROM periode pe JOIN sampel sp ON sp.periode_id=pe.id
  LEFT JOIN penugasan pg ON pg.sampel_id=sp.id GROUP BY pe.id"
```

## 7. Troubleshooting

| Masalah | Solusi |
|---|---|
| Blank page / 500 | cek `storage/logs/`; pastikan `APP_DEBUG=true` saat dev |
| Koneksi DB gagal | cek `.env` + layanan MySQL Laragon jalan |
| 419 saat submit | sesi habis → refresh halaman, ulangi |
| Data dummy hilang | jalankan ulang `php database/seeds/004_dummy_seed.php` (idempoten) |
| Semua tabel kosong | jalankan migrasi + seed sesuai urutan di [Instalasi](INSTALASI.md) |
