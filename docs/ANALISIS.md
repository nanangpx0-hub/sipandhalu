# Analisis SIPANDHALU

## Ringkasan

SIPANDHALU adalah aplikasi monolitik native PHP 8.2 + MySQL 8 untuk manajemen sampel Susenas-Seruti tingkat SLS/RT. Tidak menggunakan framework; mengikuti pola front controller, router sederhana, middleware, controller tipis, service, dan repository.

## Audit Kode Sumber

- **Basis kode**: ~41 file PHP sumber + 17 view `.phtml`, semua menggunakan `declare(strict_types=1)`.
- **Arsitektur alur request**:
  1. `public/index.php` → `App\Core\Bootstrap`
  2. `App\Core\Router::dispatch()`
  3. Middleware: Auth, CSRF, Role
  4. Controller → Service → Repository → PDO prepared statement → MySQL
  5. View `.phtml` dengan `htmlspecialchars(ENT_QUOTES)` pada helper `View::e()`.
- **Konvensi**: PSR-4 autoload (`App\` → `app/`), file SQL/migration/seed terpusat di `database/`, konfigurasi di `config/`, aset statis di `public/assets/`.
- **State**: PDO singleton di `App\Core\Database` (per request, bukan proses).

## Identifikasi Arsitektur Sistem

| Komponen | Implementasi |
|---|---|
| Front controller | `public/index.php` |
| Router | `App\Core\Router` dengan polling route list + regex `{param}` |
| Request/Response | Objek immutable `Request`, helper `Response` |
| Session | Custom `Session` dengan cookie `HttpOnly`, `SameSite=Lax`, opsional `Secure` |
| CSRF | Token random 256-bit, disimpan session, diverifikasi `hash_equals` |
| ORM/DB | PDO prepared statement manual; tidak ada ORM |
| Templating | PHP native `.phtml` via `extract($data)` |
| Excel | PhpSpreadsheet |
| Testing | PHPUnit 10 + CLI smoke test, DB terpisah `sipandhalu_test` |

Tabel utama: `roles`, `orang`, `orang_alias`, `users`, `audit_logs`, `kecamatan`, `desa`, `sls`, `periode`, `sampel`, `penugasan`. Desain menganut 1 SLS = 1 RT, histori tidak ditimpa, dan aturan K4 (1 orang 1 peran per periode).

## Analisis Dependensi

`composer.json` hanya mengharuskan PHP `>=8.2`. Dependensi langsung terpasang:

- `phpoffice/phpspreadsheet` — baca/tulis XLSX untuk impor/ekspor.
- `phpunit/phpunit` — testing.

File `vendor/` dan `composer.lock` dikelola secara terpisah: `vendor/` di-ignore dari repo, sementara `composer.lock` di-commit untuk reproduksi versi library. Tidak ada dependensi JavaScript/runtime front-end pihak ketiga selain aset AdminLTE/jQuery yang sudah dalam `public/assets/`.

## Penilaian Performa

- **Koneksi DB**: singleton per request sudah cukup untuk skala ini; tidak ada connection pooling.
- **Query**: sebagian besar menggunakan prepared statement dan indeks sesuai kebutuhan. Dashboard menggunakan CTE dengan subquery agregat; untuk data sampel 28 SLS / 66 penugasan tidak menjadi bottleneck.
- **Ekspor/impor Excel**: PhpSpreadsheet memuat file secara keseluruhan; limit impor 2.000 baris dan ukuran 5 MB sudah ditetapkan.
- **Paging**: menggunakan `COUNT(*)` terpisah + `LIMIT/OFFSET`; memadai untuk dataset kecil-menengah.
- **Potensi**: tidak ada opcode cache/OPcache dimanfaatkan secara eksplisit, tidak ada HTTP caching, dan tidak ada eager loading relasi kompleks. Untuk skala ratusan ribu baris, perlu optimasi query/rekap dan cache.

## Penilaian Keamanan

**Kontrol yang sudah ada**:

- Hash password Argon2id (`PASSWORD_ARGON2ID`) dengan rehash otomatis jika parameter berubah.
- Login generik (`Email atau password salah`) untuk tidak membocorkan akun terdaftar; timing dummy untuk user tidak ditemukan.
- CSRF protection pada semua POST/PUT/PATCH/DELETE, diverifikasi dengan `hash_equals`.
- Session hardening: `HttpOnly`, `SameSite=Lax`, `Secure` bisa diaktifkan via `.env`, `session_regenerate_id()` saat login.
- Header keamanan: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`.
- Output view di-escape `htmlspecialchars(ENT_QUOTES, UTF-8)`.
- Audit trail JSON untuk CREATE/UPDATE/DELETE/LOGIN/LOGOUT/RESET_PW/IMPORT, termasuk IP dan user agent.
- PDO prepared statement di seluruh repository; tidak ada query string langsung.
- Data sensitif (`data/`, log, `.env`) di-ignore dari Git.
- RBAC diimplementasikan di `RoleMiddleware`, meskipun rute saat ini belum menautkannya secara eksplisit pada semua endpoint tulis.

**Kekuatan utama**: tidak ada SQL injection langsung, hash kuat, CSRF aktif, session regenerate, dan data PII BPS tidak di-commit.

**Area yang perlu diperhatikan**: tidak ada rate limiting/brute-force protection, tidak ada Content-Security-Policy, tidak ada HSTS, middleware Role belum terpasang pada semua rute penting di `config/routes.php`, dan `.env` menggunakan `APP_DEBUG=true` untuk lingkungan dev.

## Rekomendasi Prioritas

1. Pasang `RoleMiddleware` pada rute tulis sensitif di `config/routes.php` alih-alih mengandalkan komentar/kontroler.
2. Tambahkan rate limiting sederhana pada `/login` dan `/password`.
3. Aktifkan OPcache, `APP_DEBUG=false`, dan HTTPS/HSTS di produksi.
4. Tambahkan header `Content-Security-Policy`.
5. Optimasi rekap dashboard dengan materialized/CTE window yang lebih ringkas untuk dataset besar.
