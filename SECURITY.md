# Keamanan SIPANDHALU

## Ringkasan Langkah Keamanan

| Lapisan | Mekanisme | Implementasi |
|---|---|---|
| Transport | HTTPS | Disarankan di production |
| Otentikasi | Argon2id password hashing | `password_hash()` + `password_verify()` |
| Otorisasi | RBAC server-side | Middleware + Controller + Service checks |
| CSRF | Token per sesi | CsrfMiddleware + hidden form field |
| Session | HttpOnly + SameSite=Lax | Session::start() dengan konfigurasi aman |
| SQL Injection | Prepared statements | PDO dengan parameter binding |
| XSS | Output escaping | View::e() dengan ENT_QUOTES |
| Clickjacking | X-Frame-Options: DENY | Header di bootstrap.php |
| Content sniffing | X-Content-Type-Options | Header di bootstrap.php |
| Password reset | Force first-time | must_reset flag di users table |
| Audit | Trail semua aksi | audit_logs table |

## 1. Otentikasi

### Password Hashing

```php
// Hash saat registrasi/update
$hash = password_hash($password, PASSWORD_ARGON2ID);

// Verifikasi (timing-safe)
if (password_verify($password, $row['password_hash'])) {
    // Sukses
}
```

### Pesan Error Generik

```php
// Tidak membocorkan apakah email terdaftar
if ($row === null || (int) $row['is_aktif'] !== 1) {
    // Simulasi verifikasi (timing attack prevention)
    password_verify($password, password_hash('dummy', PASSWORD_ARGON2ID));
    return ['ok' => false, 'error' => 'Email atau password salah.'];
}
```

### Force Password Reset

Pengguna baru dan migrasi memiliki `must_reset=1`. Setelah login pertama kali, pengguna diarahkan ke `/password` dan wajib ganti password. Flag `must_reset` di-update menjadi 0 setelah berhasil reset.

## 2. CSRF Protection

### Mekanisme

1. **Token generation**: `Csrf::token()` — random_bytes(32), disimpan di session
2. **Token field**: `Csrf::field()` — hidden input `<input type="hidden" name="_csrf" value="...">`
3. **Token validation**: `Csrf::check()` — hash_equals comparison (timing-safe)
4. **Middleware**: CsrfMiddleware memvalidasi POST/PUT/PATCH/DELETE

### Contoh

```php
// Form
<form method="post" action="/login">
    <?= Csrf::field() ?>
    ...
</form>

// Middleware validation
CsrfMiddleware::handle($request);
// Jika tidak valid → HTTP 419 + tampil halaman error
```

## 3. Session Security

```php
Session::start(); // Memulai session dengan konfigurasi:
// - lifetime: 0 (sesi browser)
// - path: /
// - httponly: true (tidak dapat diakses JavaScript)
// - samesite: Lax (mencegah CSRF cross-site)
// - secure: $_ENV['SESSION_SECURE'] (true di production)
```

### Session Regenerate

```php
Session::regenerate(); // Dipanggil setelah login (fixation prevention)
```

### Session Destroy

```php
Session::destroy(); // Dipanggil saat logout (cleanup lengkap)
```

## 4. SQL Injection Prevention

Seluruh query menggunakan PDO prepared statements:

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email=:e LIMIT 1");
$stmt->execute([':e' => $email]);
$row = $stmt->fetch();
```

## 5. XSS Prevention

Semua output dinamis di-escape menggunakan `View::e()`:

```php
<?= View::e($user['nama'] ?? '-') ?>
```

Implementasi:

```php
public static function e(?string $v): string
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}
```

## 6. Security Headers

Di `app/Core/bootstrap.php`:

```php
header('X-Frame-Options: DENY');                    // Clickjacking
header('X-Content-Type-Options: nosniff');           // MIME sniffing
header('Referrer-Policy: strict-origin-when-cross-origin');
```

## 7. RBAC Enforcement Levels

RBAC ditegakkan pada 3 level:

1. **Middleware**: AuthMiddleware (login), RoleMiddleware (peran)
2. **Controller constructor**: Beberapa controller menolak peran tertentu
3. **Service**: SerutiService, PengolahanService memvalidasi peran operasi

## 8. Audit Trail

Semua aksi penting dicatat di `audit_logs`:

- **LOGIN/LOGOUT**: Percobaan login dan logout
- **RESET_PW**: Reset password admin oleh ADMIN
- **CREATE/UPDATE/DELETE**: Perubahan data orang, users, ruta
- **IMPORT**: Import data dari Excel

Kolom JSON `before_json` dan `after_json` menyimpan perubahan data.

## 9. Impor/Export Keamanan

### Excel Import

```php
Excel::cekUpload(); // Validasi: ukuran max 5MB, format .xlsx/.xls
Excel::maxImportRows(); // Max 2000 baris per import
```

### Excel Export

- Header diaformat (bold, fill, freeze pane)
- Content-Type: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- Content-Disposition: attachment (forced download)
