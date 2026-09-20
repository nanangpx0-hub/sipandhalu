const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8091';
const PENGOLAH_EMAIL = 'aminatuss182002@gmail.com';
const PENGOLAH_PW = 'Jember3509';
const NEW_PW = 'Pengolah2026!';

test.describe('SIPANDHALU — Peran Pengolah Data', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  });

  async function login(page, email, password) {
    await page.waitForSelector('input[name="email"]', { state: 'visible', timeout: 5000 });
    await page.fill('input[name="email"]', email);
    await page.waitForSelector('input[name="password"]', { state: 'visible', timeout: 5000 });
    await page.fill('input[name="password"]', password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
  }

  async function expectURL(page, pattern) {
    await page.waitForFunction(
      (pat) => window.location.href.match(new RegExp(pat)),
      pattern.source,
      { timeout: 10000 }
    );
  }

  async function resetPassword(page, oldPw, newPw) {
    await page.waitForSelector('input[name="old_password"]', { state: 'visible', timeout: 5000 });
    await page.fill('input[name="old_password"]', oldPw);
    await page.fill('input[name="new_password"]', newPw);
    await page.evaluate(() => {
      document.querySelector('form[action="/password"]').submit();
    });
    await page.waitForLoadState('domcontentloaded');
  }

  // ── 1. LOGIN PENGOLAH (must_reset=1 → redirect /password) ──
  test('T01: Login pengolah → redirect ganti password', async ({ page }) => {
    await login(page, PENGOLAH_EMAIL, PENGOLAH_PW);
    await expectURL(page, /password/);
    await expect(page.locator('h1')).toContainText('Ganti Password');
  });

  // ── 2. PASSWORD SALAH → ERROR (sebelum reset) ──
  test('T02: Password lama salah → error', async ({ page }) => {
    await login(page, PENGOLAH_EMAIL, PENGOLAH_PW);
    await expectURL(page, /password/);

    await page.fill('input[name="old_password"]', 'Salah123');
    await page.fill('input[name="new_password"]', NEW_PW);
    await page.evaluate(() => {
      document.querySelector('form[action="/password"]').submit();
    });
    await page.waitForLoadState('domcontentloaded');
    await page.waitForURL(/password/, { timeout: 5000 }).catch(() => {});
    const error = await page.textContent('.alert-danger').catch(() => '');
    expect(error).toContain('Password lama salah');
  });

  // ── 3. GANTI PASSWORD WAJIB ──────────────────
  test('T03: Ganti password baru berhasil', async ({ page }) => {
    await login(page, PENGOLAH_EMAIL, PENGOLAH_PW);
    await expectURL(page, /password/);

    await resetPassword(page, PENGOLAH_PW, NEW_PW);
    await page.waitForURL(/\//, { timeout: 10000 });
    await expect(page.locator('h1')).toContainText('Dasbor');
    const bodyText = await page.textContent('body');
    expect(bodyText).toContain('Password berhasil');
  });

  // ── SETELAH GANTI: SEMUA TEST BERIKUTNYA ──
  // Login dengan password baru, tidak perlu reset lagi
  async function loginAfterReset(page) {
    await login(page, PENGOLAH_EMAIL, NEW_PW);
    await page.waitForURL(/\//, { timeout: 10000 });
  }

  test('T04: Login setelah ganti → dashboard', async ({ page }) => {
    await loginAfterReset(page);
    await expect(page.locator('.main-sidebar')).toBeVisible();
    const sidebarText = await page.textContent('.user-panel');
    expect(sidebarText).toContain('Aminatus Sholeha');
    expect(sidebarText).toContain('PENGOLAH');
  });

  test('T05: PENGOLAH dapat akses /users (RBAC belum enforced)', async ({ page }) => {
    await loginAfterReset(page);
    await page.goto(`${BASE}/users`, { waitUntil: 'domcontentloaded' });
    const bodyText = await page.textContent('body');
    // PENGOLAH bisa lihat halaman users (sebelum RBAC server-side di-backlog)
    expect(bodyText).toContain('Akun Login');
  });

  // Helper: id baris orang milik pengolah yg login (satu-satunya baris di tabel)
  async function ownPetugasId(page) {
    await page.goto(`${BASE}/petugas`, { waitUntil: 'domcontentloaded' });
    const href = await page.locator('table a[href^="/petugas/"]').first().getAttribute('href');
    return parseInt(href.split('/')[2], 10);
  }

  test('T06: PENGOLAH di /petugas hanya lihat data dirinya', async ({ page }) => {
    await loginAfterReset(page);
    const ownId = await ownPetugasId(page);
    await expect(page).toHaveURL(/petugas/);
    await expect(page.locator('h1')).toContainText('Petugas');
    const table = page.locator('table').first();
    await expect(table).toBeVisible();
    // tepat 1 baris data: milik sendiri
    await expect(table.locator('tbody tr')).toHaveCount(1);
    await expect(table.locator(`a[href="/petugas/${ownId}/edit"]`)).toBeVisible();
    const bodyText = await page.textContent('body');
    expect(bodyText).toContain('data diri Anda');
    // tombol kelola disembunyikan, kecuali export data sendiri
    await expect(page.locator('a[href="/petugas/baru"]')).toHaveCount(0);
    await expect(page.locator('button[data-target="#modalImport"]')).toHaveCount(0);
    await expect(page.locator('a[href="/petugas/export"]')).toBeVisible();
    // data orang lain tidak tampil
    expect(bodyText).not.toContain('Pcl Dummy 01');
  });

  test('T07: PENGOLAH tidak bisa buka /sls → 403', async ({ page }) => {
    await loginAfterReset(page);
    const response = await page.goto(`${BASE}/sls`, { waitUntil: 'domcontentloaded' });
    expect(response.status()).toBe(403);
    await expect(page.locator('h3')).toContainText('Akses ditolak');
    const sidebar = await page.textContent('.main-sidebar');
    expect(sidebar).not.toContain('SLS');
  });

  test('T08: PENGOLAH tidak bisa buka /periode → 403', async ({ page }) => {
    await loginAfterReset(page);
    const response = await page.goto(`${BASE}/periode`, { waitUntil: 'domcontentloaded' });
    expect(response.status()).toBe(403);
    await expect(page.locator('h3')).toContainText('Akses ditolak');
    const sidebar = await page.textContent('.main-sidebar');
    expect(sidebar).not.toContain('Periode');
  });

  test('T09: Halaman pengolahan (LK) dapat diakses', async ({ page }) => {
    await loginAfterReset(page);
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/pengolahan/);
    await expect(page.locator('h1')).toContainText('Lembar Kerja');
    await page.waitForSelector('table#tablePengolahan', { timeout: 10000 }).catch(() => {});
    await expect(page.locator('table').first()).toBeVisible();
  });

  test('T10: Dashboard PENGOLAH tanpa box SLS/Periode', async ({ page }) => {
    await loginAfterReset(page);
    await expect(page.locator('.info-box')).toHaveCount(2);
    const dashText = await page.textContent('body');
    expect(dashText).not.toContain('Master SLS');
    const firstBox = await page.locator('.info-box-number').first().textContent();
    expect(firstBox.trim()).not.toBe('');
  });

  test('T11: Logout berhasil → kembali ke login', async ({ page }) => {
    await loginAfterReset(page);
    // Submit logout via form (inside collapsed dropdown)
    await page.evaluate(() => {
      const form = document.querySelector('form[action="/logout"]');
      if (form) { form.submit(); }
    });
    await page.waitForURL(/login/, { timeout: 10000 }).catch(() => {});
    const bodyText = await page.textContent('body').catch(() => '');
    expect(bodyText).toContain('Masuk');
  });

  test('T12: Akses /pengolahan tanpa login → redirect ke /login', async ({ page }) => {
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/login/, { timeout: 5000 });
  });

  test('T13: Tabel pengolahan menampilkan data', async ({ page }) => {
    await loginAfterReset(page);
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('table#tablePengolahan', { timeout: 10000 }).catch(() => {});
    await expect(page.locator('h1')).toContainText('Lembar Kerja');
    await expect(page.locator('table').first()).toBeVisible();
  });

  test('T14: Dropdown pilih periode di pengolahan', async ({ page }) => {
    await loginAfterReset(page);
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });
    const select = page.locator('select[name="periode_id"]');
    await expect(select).toBeVisible();
    const options = await select.locator('option').count();
    expect(options).toBeGreaterThanOrEqual(1);
  });

  test('T15: Export periode menghasilkan file xlsx (operator)', async ({ page }) => {
    await login(page, 'operator.demo@bpsjember.go.id', 'Jember3509');
    await page.waitForURL(/\//, { timeout: 10000 });
    const response = await page.request.get(`${BASE}/periode/export`);
    expect(response.status()).toBe(200);
    const ct = (response.headers()['content-type'] || '').toLowerCase();
    expect(ct).toContain('spreadsheetml');
    const cd = response.headers()['content-disposition'] || '';
    expect(cd).toContain('.xlsx');
  });

  test('T16: Export petugas menghasilkan file xlsx', async ({ page }) => {
    await loginAfterReset(page);
    const response = await page.request.get(`${BASE}/petugas/export`);
    expect(response.status()).toBe(200);
    const ct = (response.headers()['content-type'] || '').toLowerCase();
    expect(ct).toContain('spreadsheetml');
    const cd = response.headers()['content-disposition'] || '';
    expect(cd).toContain('.xlsx');
  });

  test('T17: Template Excel SLS dapat diunduh (operator)', async ({ page }) => {
    await login(page, 'operator.demo@bpsjember.go.id', 'Jember3509');
    await page.waitForURL(/\//, { timeout: 10000 });
    const response = await page.request.get(`${BASE}/sls/template`);
    expect(response.status()).toBe(200);
    const ct = (response.headers()['content-type'] || '').toLowerCase();
    expect(ct).toContain('spreadsheetml');
    const cd = response.headers()['content-disposition'] || '';
    expect(cd).toContain('.xlsx');
  });

  test('T27: PENGOLAH export/template SLS & Periode → 403', async ({ page }) => {
    await loginAfterReset(page);
    for (const url of [`${BASE}/sls/export`, `${BASE}/sls/template`, `${BASE}/periode/export`]) {
      const response = await page.request.get(url);
      expect(response.status()).toBe(403);
    }
  });

  test('T21: PENGOLAH buka detail orang lain → 403', async ({ page }) => {
    await loginAfterReset(page);
    const ownId = await ownPetugasId(page);
    const otherId = ownId === 1 ? 2 : 1;
    const response = await page.goto(`${BASE}/petugas/${otherId}`, { waitUntil: 'domcontentloaded' });
    expect(response.status()).toBe(403);
    await expect(page.locator('h3')).toContainText('Akses ditolak');
  });

  test('T22: PENGOLAH buka form edit orang lain → 403', async ({ page }) => {
    await loginAfterReset(page);
    const ownId = await ownPetugasId(page);
    const otherId = ownId === 1 ? 2 : 1;
    const response = await page.goto(`${BASE}/petugas/${otherId}/edit`, { waitUntil: 'domcontentloaded' });
    expect(response.status()).toBe(403);
    await expect(page.locator('h3')).toContainText('Akses ditolak');
  });

  test('T23: PENGOLAH buka form tambah petugas → 403', async ({ page }) => {
    await loginAfterReset(page);
    const response = await page.goto(`${BASE}/petugas/baru`, { waitUntil: 'domcontentloaded' });
    expect(response.status()).toBe(403);
    await expect(page.locator('h3')).toContainText('Akses ditolak');
  });

  test('T24: PENGOLAH POST ubah orang lain → 403 (tanpa menulis data)', async ({ page }) => {
    await loginAfterReset(page);
    const ownId = await ownPetugasId(page);
    const otherId = ownId === 1 ? 2 : 1;
    // ambil token CSRF valid dari form edit milik sendiri
    await page.goto(`${BASE}/petugas/${ownId}/edit`, { waitUntil: 'domcontentloaded' });
    const token = await page.locator('input[name="_csrf"]').getAttribute('value');
    // nama dikosongkan: kalau gate jebol pun validasi menggagalkan tulis
    const response = await page.request.post(`${BASE}/petugas/${otherId}`, {
      form: { _csrf: token, nama: '', no_hp: '', email: '', alamat: '' },
    });
    expect(response.status()).toBe(403);
  });

  test('T25: PENGOLAH bisa ubah biodata sendiri (level/status terkunci)', async ({ page }) => {
    await loginAfterReset(page);
    const ownId = await ownPetugasId(page);
    await page.goto(`${BASE}/petugas/${ownId}/edit`, { waitUntil: 'domcontentloaded' });
    // dropdown level + checkbox aktif disembunyikan untuk pengolah
    await expect(page.locator('select[name="role_id"]')).toHaveCount(0);
    await expect(page.locator('input[name="is_aktif"]')).toHaveCount(0);
    const token = await page.locator('input[name="_csrf"]').getAttribute('value');
    const nama = await page.locator('input[name="nama"]').inputValue();
    const noHp = await page.locator('input[name="no_hp"]').inputValue();
    const email = await page.locator('input[name="email"]').inputValue();
    const alamatBaru = 'Jl. Tes E2E ' + Date.now();
    const response = await page.request.post(`${BASE}/petugas/${ownId}`, {
      form: { _csrf: token, nama, no_hp: noHp, email, alamat: alamatBaru },
    });
    expect([200, 302]).toContain(response.status());
    await page.goto(`${BASE}/petugas/${ownId}`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toContainText(alamatBaru);
  });

  test('T26: PENGOLAH tidak bisa toggle status / kelola alias', async ({ page }) => {
    await loginAfterReset(page);
    const ownId = await ownPetugasId(page);
    await page.goto(`${BASE}/petugas/${ownId}/edit`, { waitUntil: 'domcontentloaded' });
    const token = await page.locator('input[name="_csrf"]').getAttribute('value');
    const toggleRes = await page.request.post(`${BASE}/petugas/${ownId}/toggle`, {
      form: { _csrf: token },
    });
    expect(toggleRes.status()).toBe(403);
    const aliasRes = await page.request.post(`${BASE}/petugas/${ownId}/alias`, {
      form: { _csrf: token, alias: 'tes e2e alias' },
    });
    expect(aliasRes.status()).toBe(403);
    // form alias juga disembunyikan di halaman detail
    await page.goto(`${BASE}/petugas/${ownId}`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('input[name="alias"]')).toHaveCount(0);
  });

  test('T18: Viewer demo login → dashboard saja', async ({ page }) => {
    await login(page, 'viewer.demo@bpsjember.go.id', 'Jember3509');
    await page.waitForURL(/\//, { timeout: 10000 });
    await expect(page.locator('h1')).toContainText('Dasbor');
    const sidebar = await page.textContent('.main-sidebar');
    expect(sidebar).not.toContain('Users');
  });

  test('T19: PCL demo login langsung tanpa reset', async ({ page }) => {
    await login(page, 'pcl.demo@bpsjember.go.id', 'Jember3509');
    await page.waitForURL(/\//, { timeout: 10000 });
    await expect(page.locator('h1')).toContainText('Dasbor');
  });

  test('T20: Operator demo login → dashboard', async ({ page }) => {
    await login(page, 'operator.demo@bpsjember.go.id', 'Jember3509');
    await page.waitForURL(/\//, { timeout: 10000 });
    await expect(page.locator('h1')).toContainText('Dasbor');
  });
});