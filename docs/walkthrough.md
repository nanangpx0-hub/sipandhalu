# Walkthrough: Penerimaan & Peminjaman Dokumen Pemutakhiran (Susenas & Peta WS/WSS)

Fitur penerimaan fisik dan sirkulasi peminjaman dokumen dari lapangan (PCL/PML) ke kantor BPS (Operator/Pengawas Pengolahan) telah selesai diimplementasikan dan diverifikasi dengan tes otomatis.

---

## 1. Ringkasan Perubahan

### A. Database & Migrasi
- **[003_dokumen_penerimaan_pinjam.sql](file:///c:/laragon/www/sipandhalu/database/migrations/003_dokumen_penerimaan_pinjam.sql)**:
  - Menambahkan kolom status fisik pada tabel `sampel`: `dok_pemutakhiran_status`, `dok_pemutakhiran_waktu`, `dok_pemutakhiran_oleh`, `dok_pemutakhiran_penyerah`, `peta_status`, `peta_waktu`, `peta_oleh`, `peta_penyerah`, `catatan_dokumen`.
  - Menambahkan tabel transaksi `peminjaman_dokumen` untuk pencatatan sirkulasi berkas fisik yang dipinjam petugas lapangan untuk konfirmasi/revisi.
- **[migrate.php](file:///c:/laragon/www/sipandhalu/database/migrate.php)**:
  - Mendaftarkan migrasi `003` dan tabel `peminjaman_dokumen` ke dalam urutan migrasi idempoten.

### B. Repositori & Layanan Bisnis
- **[SampelRepository.php](file:///c:/laragon/www/sipandhalu/app/Repositories/SampelRepository.php)**:
  - Query `paginateByPeriode` diperkaya dengan deteksi `sub` (untuk membedakan Peta WS vs Peta WSS), penyerah fisik, dan penerima fisik.
  - Menambahkan `updateDokumenFisik()`, `allPmlInPeriode()`, dan `findByPmlInPeriode()`.
- **[DokumenRepository.php](file:///c:/laragon/www/sipandhalu/app/Repositories/DokumenRepository.php)**:
  - Operasi insert/update peminjaman, pencarian peminjaman aktif, dan histori transaksi per NKS.
- **[DokumenService.php](file:///c:/laragon/www/sipandhalu/app/Services/DokumenService.php)**:
  - Penegakan aturan bisnis: periode harus berstatus `AKTIF`, penerimaan parsial/sekaligus, penerimaan kolektif per PML dalam satu transaksi database, validasi status saat dipinjam (hanya berkas `DITERIMA` yang boleh dipinjam), serta audit logging otomatis ke `audit_logs`.

### C. Kontroler, Rute, & UI
- **[PeriodeController.php](file:///c:/laragon/www/sipandhalu/app/Controllers/PeriodeController.php)**:
  - Endpoint baru: `terimaDokumen`, `ajaxPmlSampel`, `terimaKolektif`, `pinjamDokumen`, `kembaliDokumen`, dan `riwayatDokumen`.
- **[routes.php](file:///c:/laragon/www/sipandhalu/config/routes.php)**:
  - Pendaftaran rute POST & GET dengan middleware `AuthMiddleware` dan `CsrfMiddleware`.
- **[show.phtml](file:///c:/laragon/www/sipandhalu/app/Views/periode/show.phtml)**:
  - Kolom status fisik dokumen interaktif (Pemutakhiran & Peta WS/WSS dinamis).
  - Tombol **"Terima Kolektif per PML"** dengan modal checklist batch NKS binaan.
  - Modal **Terima Satuan** (pilihan penyerah PCL/PML, input hasil KK/RT Blok II, catatan).
  - Modal **Pinjam Dokumen** (pilihan berkas, peminjam, alasan).
  - Modal **Riwayat & Pengembalian Dokumen** (timeline log pergerakan berkas dan form pengembalian jika status sedang dipinjam).

---

## 2. Hasil Verifikasi & Testing

### A. Unit Tests (PHPUnit 10)
Test suite baru `tests/DokumenTest.php` menguji seluruh skenario bisnis:
1. **Penerimaan Satuan Parsial & Sekaligus**: Verifikasi penerimaan bertahap (Pemutakhiran diterima dulu, lalu Peta diserahkan susulan) serta sinkronisasi flag legacy `dokumen_vsen` dan `peta_ws`.
2. **Penerimaan Kolektif per PML**: Verifikasi batch update beberapa NKS binaan PML sekaligus.
3. **Sirkulasi Pinjam & Kembali**: Verifikasi status berkas berubah menjadi `DIPINJAM`, flag legacy tetap `1`, dan saat dikembalikan status kembali menjadi `DITERIMA`.
4. **Validasi Status Periode**: Verifikasi penolakan aksi tulis jika periode berstatus `DRAFT` atau `TUTUP`.

Hasil eksekusi:
```
Dokumen (Tests\Dokumen)
 ✔ Penerimaan satuan parsial dan sekaligus
 ✔ Penerimaan kolektif per pml
 ✔ Peminjaman dan pengembalian dokumen
 ✔ Tolak aksi jika periode bukan aktif

OK (16 tests, 86 assertions)
```

### B. Smoke Tests
```
OK   config database.php terbaca
OK   koneksi PDO sipandhalu
OK   seed baseline minimal
OK   password ter-hash Argon2id + must_reset
OK   semua view bisa di-parse PHP
OK   tabel tahap 2 eksist (@migrate --fresh done)
```

---

## 3. Panduan Penggunaan di Lapangan

1. **Penerimaan Berkas Satuan (Per NKS)**:
   - Di tabel sampel periode (`/periode/{id}`), klik tombol hijau `[ In-box ]` pada kolom **Aksi Berkas**.
   - Pilih penyerah (PCL atau PML), centang dokumen yang diserahkan (Pemutakhiran dan/atau Peta WS/WSS), isi hasil Blok II (KK & RT), lalu klik **Simpan Penerimaan**.
2. **Penerimaan Kolektif (Per PML)**:
   - Klik tombol **`[ Terima Kolektif per PML ]`** di atas tabel sampel.
   - Pilih nama PML penyerah. Daftar NKS binaan PML akan otomatis termuat secara instan.
   - Centang NKS dan jenis dokumen yang diserahkan, sesuaikan hasil KK/RT jika ada, lalu klik **Simpan Penerimaan Kolektif**.
3. **Peminjaman Dokumen (Konfirmasi Lapangan / Revisi)**:
   - Jika dokumen sudah diterima di kantor, klik tombol kuning `[ Pinjam Dokumen ]`.
   - Pilih dokumen yang dipinjam, nama peminjam, peran (PML/PCL/Pengolah), dan tulis alasan peminjaman.
   - Status berkas akan berubah menjadi badge kuning `[ DIPINJAM ]`.
4. **Pengembalian Berkas**:
   - Klik tombol biru `[ Riwayat ]` pada baris NKS yang sedang dipinjam.
   - Pada panel kuning yang muncul, isi waktu kembali dan catatan hasil revisi, lalu klik **Konfirmasi Pengembalian Berkas ke Kantor**.
