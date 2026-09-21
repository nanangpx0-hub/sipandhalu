# Modul & Fitur SIPANDHALU — Dokumentasi Detail

## Daftar Modul

| No | Modul | URL | Peran yang Bisa Akses |
|---|---|---|---|
| 1 | Dashboard | `/` | Semua yang login |
| 2 | Login | `/login` | Publik |
| 3 | Petugas | `/petugas` | Semua yang login |
| 4 | Users | `/users` | ADMIN saja |
| 5 | SLS | `/sls` | Bukan PENGOLAH |
| 6 | Periode | `/periode` | Bukan PENGOLAH |
| 7 | Pengolahan (LK) | `/pengolahan` | Semua yang login |
| 8 | Seruti | `/seruti` | Semua yang login |
| 9 | Monitoring | `/monitoring` | Semua yang login |

---

## Modul 1: Dashboard

**URL**: `/`  
**Controller**: `DashboardController::index()`  
**Fitur**:
- Info box ringkasan (Total Orang, User Aktif, SLS, Periode AKTIF/DRAFT/TUTUP)
- Audit trail 10 entri terakhir
- Pesan toast sukses/error

---

## Modul 2: Login & Autentikasi

**URL**: `/login`, `/password`  
**Controller**: `AuthController`  
**Flow**:
1. Login form → POST `/login` → validasi CSRF → `AuthService::attempt()`
2. Jika `must_reset=1` → redirect ke `/password`
3. Ganti password → POST `/password` → redirect ke `/`
4. Logout → POST `/logout` → redirect ke `/login`

---

## Modul 3: Petugas (Orang)

**URL**: `/petugas`, `/petugas/{id}`, `/petugas/baru`  
**Controller**: `OrangController`  
**Fitur Utama**:
- Pool orang tunggal (semua manusia dalam satu tabel)
- Peran ditentukan per penugasan periode (bukan permanen)
- Nama kanonik: Title Case, TRIM
- Alias nama untuk toleransi varian tulis (contoh: *Junaidi* vs *Junaidi Ari Siswanto*)
- CRUD + toggle aktif
- Export/Import Excel
- **RBAC**: PENGOLAH hanya lihat data diri sendiri

---

## Modul 4: Users

**URL**: `/users`  
**Controller**: `UserController`  
**Akses**: ADMIN saja  
**Fitur**:
- Manajemen akun login (create, edit, toggle, reset password)
- Peran dan level petugas

---

## Modul 5: Master SLS

**URL**: `/sls`  
**Controller**: `SlsController`  
**Akses**: Bukan PENGOLAH  
**Fitur**:
- Master SLS/RT dengan kode BPS 16 digit + NKS 5 digit
- Import/Export Excel

---

## Modul 6: Periode

**URL**: `/periode`, `/periode/{id}`  
**Controller**: `PeriodeController`  
**Akses**: Bukan PENGOLAH  
**Fitur**:
- CRUD periode (SUSENAS & SERUTI)
- Status flow: DRAFT → AKTIF → TUTUP
- Sampel per SLS per periode (aturan K4: 10 KK/RT per SLS)
- Penugasan (1 orang 1 peran per periode)
- Penerimaan & peminjaman dokumen
- Import sampel dari Excel

---

## Modul 7: Pengolahan / LK

**URL**: `/pengolahan`  
**Controller**: `PengolahanController`  
**Fitur Utama**:
- Data grid rute pengolahan
- Update per-rute (status dokumen, transfer, catatan kendali mutu)
- Transfer dokumen (terima, pinjam, kembali)
- Batch transfer
- Export Excel
- **RBAC**: PENGOLAH bisa tulis data binaan sendiri saja

---

## Modul 8: Pengolahan Seruti

**URL**: `/seruti`  
**Controller**: `SerutiController`  
**Service**: `SerutiService`  
**Fitur Utama**:
- KPI Seruti per periode:
  - Total Sampel
  - Kesiapan Susenas (%), progress bar
  - Sampel Siap Olah
  - Sampel Terkunci
  - Selesai Transfer
- Data grid rute Seruti:
  - NKS, Kecamatan/Desa, No. Urut Ruta, Pengolah
  - Prasyarat Susenas (Susenas Selesai/Terkunci)
  - Status Seruti (Siap Olah/Terkunci)
  - Transfer Seruti (toggle switch)
  - Catatan Kendali Mutu
  - Tombol Telaah (modal detail)
- Filter & Pencarian Cepat:
  - Filter NKS, Kesiapan, Pengolah, Pencarian teks
- PILL TABS triwulan (pergantian periode)
- Export Excel (pengolah, rekap pengolahan)
- **RBAC**:
  - Transfer Seruti: hanya ADMIN, OPERATOR, SM_PLS, PENGAWAS_OLAH
  - Tulis data: ADMIN, OPERATOR, SM_PLS, PENGOLAH, PENGAWAS_OLAH
  - PENGOLAH: hanya data binaan sendiri

### Struktur Halaman Seruti

```
┌─────────────────────────────────────────────┐
│ <h1>Pengolahan Seruti</h1> + Filter form    │
├─────────────────────────────────────────────┤
│ PILL TABS (Q1, Q2, Q3, Q4)                 │
├─────────────────────────────────────────────┤
│ KPI Cards (5 cards)                         │
│ Total Sampel | Kesiapan | Siap | Terkunci | 
│ Selesai Transfer                            │
├─────────────────────────────────────────────┤
│ Filter & Pencarian Cepat                    │
├─────────────────────────────────────────────┤
│ Data Grid #tableSeruti                      │
│ NKS | Kecamatan/Desa | Ruta | Pengolah |   │
│ Prasyarat | Status | Transfer | Catatan |   │
│ Aksi (Telaah)                               │
├─────────────────────────────────────────────┤
│ Modal Telaah (klik Tombol Telaah)           │
│ Transfer Seruti toggle                      │
│ Catatan Kendala Mutu                        │
└─────────────────────────────────────────────┘
```

---

## Modul 9: Monitoring

**URL**: `/monitoring`  
**Controller**: `MonitoringController`  
**Service**: `MonitoringService`  
**Fitur Utama**:
- Dashboard Enterprise (server-rendered untuk FCP cepat)
- Tier 1 KPI, Tier 2 chart, Tier 3 grid (via AJAX)
- Filter cascading: Kecamatan → Desa → SLS
- Detail rute dengan jejak audit (drawer)
- Quick verify & bulk verify
- Export CSV/XLSX sesuai filter aktif
- Cache-busting aset
