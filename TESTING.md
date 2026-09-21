# Pengujian SIPANDHALU

## PHPUnit (Unit & Integration Test)

### Menjalankan Test

```bash
# Semua test
php vendor/bin/phpunit

# Test spesifik
php vendor/bin/phpunit tests/SerutiModulTest.php
php vendor/bin/phpunit tests/Tahap1Test.php
php vendor/bin/phpunit tests/PengolahanTest.php
php vendor/bin/phpunit tests/ExcelTest.php

# Dengan verbose output
php vendor/bin/phpunit --testdox

# Hanya test tertentu
php vendor/bin/phpunit --filter testRouteSerutiIndexExists
```

### Struktur Test

```
tests/
├── SerutiModulTest.php       # 64 test — Modul Seruti (rute, controller, service, RBAC)
├── SerutiPengolahanTest.php  # Test Seruti Pengolahan
├── PengolahanTest.php        # Test LK Pengolahan
├── Tahap1Test.php            # Test Tahap 1 (user, petugas)
├── Tahap2Test.php            # Test Tahap 2 (wilayah, periode, sampel)
├── ExcelTest.php             # Test Excel import/export
├── DokumenTest.php           # Test peminjaman dokumen
├── DokumenKirimTest.php      # Test pengiriman dokumen
└── smoke_tahap1.php          # Smoke test
```

### Setup DB untuk PHPUnit

PHPUnit menggunakan database `sipandhalu` (bukan `sipandhalu_test`). Pastikan DB sudah dimigrasi dan di-seed:

```bash
php database/migrate.php --fresh
php database/seeds/seed_tahap1.php
php database/seeds/002_wilayah_seed.php
php database/seeds/003_sls_pdf_seed.php
php database/seeds/004_dummy_seed.php
php add_periodes.php
php scripts/sync_seruti_2026.php
```

### Contoh Output Sukses

```
PHPUnit 10.0.0 by Sebastian Bergmann and contributors.

................................................................  64 / 64 (100%)

Time: 00:01.474, Memory: 12.00 MB

OK (64 tests, 263 assertions)
```

---

## Playwright (E2E Test)

### Prasyarat

```bash
npm install                          # Install Playwright
npx playwright install chromium      # Install Chromium browser
php -S localhost:8091 -t public      # Jalankan PHP server
```

### Menjalankan E2E Test

```bash
# Semua E2E test
npx playwright test

# Project spesifik
npx playwright test --project=chromium

# File spesifik
npx playwright test --project=chromium pengolah
npx playwright test --project=chromium seruti

# Tampilkan hasil detail
npx playwright test --project=chromium --reporter=list

# Jalankan dalam mode headed (lihat browser)
npx playwright test --project=chromium --headed

# Screenshot saat gagal (otomatis)
npx playwright test --project=chromium --reporter=html
```

### Struktur E2E Test

```
tests/playwright/
├── pengolah.spec.js            # 20 test — Peran Pengolah Data
│                                 • T01-T03: Login + password reset
│                                 • T04-T10: Akses berbagai halaman
│                                 • T11-T14: CRUD petugas
│                                 • T15-T17: Export Excel
│                                 • T18-T20: Login viewer/PCL/operator
│                                 • T21-T26: RBAC enforcement
│                                 • T27-T29: RBAC LK Pengolahan
│                                 • T41: Dokumen info wewenang
└── seruti-page-test.spec.cjs   # 8 test — Modul Seruti
                                  • Admin login + /seruti page
                                  • Sidebar menu
                                  • Tab triwulan
                                  • Export API
                                  • Pengolah login + page
                                  • Transfer toggle disabled
                                  • Viewer unauthorized access
                                  • Logout flow
```

### Helper Functions (dalam pengolah.spec.js)

```javascript
async function login(page, email, password) {
    // Login via form (CSRF included automatically)
    await page.goto(`${BASE}/login`);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
}

async function resetPassword(page, oldPw, newPw) {
    // Ganti password via form
    await page.waitForSelector('input[name="old_password"]');
    await page.fill('input[name="old_password"]', oldPw);
    await page.fill('input[name="new_password"]', newPw);
    await page.evaluate(() => { document.querySelector('form[action="/password"]').submit(); });
    await page.waitForLoadState('domcontentloaded');
}

async function loginAfterReset(page, email = PENGOLAH_EMAIL, password = NEW_PW) {
    // Login dengan password baru (setelah reset)
    await login(page, email, password);
    await page.waitForURL(/\//, { timeout: 10000 }).catch(() => {});
}
```

### Login Flow untuk Must_Reset

Pengguna dengan `must_reset=1` akan diarahkan ke `/password` setelah login:

```javascript
await login(page, email, password);
await page.waitForURL(/password/, { timeout: 10000 }).catch(() => {});
if (page.url().includes('/password')) {
    await resetPassword(page, oldPw, newPw);
}
```

### Kode Respons yang Diharapkan

| Skenario | Kode |
|---|---|
| Login sukses | 200 (redirect ke /) |
| Akses tanpa login | 302 (redirect ke /login) |
| Akses tidak berotorisasi | 403 (halaman error) |
| CSRF tidak valid | 419 (halaman error) |
| Export sukses | 200 + content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet |

### Tips Debugging E2E Test

```bash
# Lihat trace detail
npx playwright show-trace test-results/.../trace.zip

# Jalankan dengan browser terlihat
npx playwright test --project=chromium --headed

# Ambil screenshot
npx playwright test --project=chromium --reporter=html

# Buka halaman debug
php tests/smoke_tahap1.php  # Smoke test via browser
```

### Password Akun Demo

| Akun | Password Awal | Setelah Reset |
|---|---|---|
| admin@bpsjember.go.id | Jember3509 | AdminBaru2026! |
| aminatuss182002@gmail.com | Jember3509 | Pengolah2026! |
| operator.demo@bpsjember.go.id | Jember3509 | (tidak direset) |
| viewer.demo@bpsjember.go.id | Jember3509 | (tidak direset) |

---

## Reset Database

Untuk mengembalikan DB ke state awal sebelum test:

```bash
php database/migrate.php --fresh && \
php database/seeds/seed_tahap1.php && \
php database/seeds/002_wilayah_seed.php && \
php database/seeds/003_sls_pdf_seed.php && \
php database/seeds/004_dummy_seed.php && \
php add_periodes.php && \
php scripts/sync_seruti_2026.php
```
