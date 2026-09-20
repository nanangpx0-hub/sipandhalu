/**
 * RBAC LK Pengolahan Sampel (/pengolahan) — SIPANDHALU.
 *
 * Skenario (sesuai spesifikasi):
 *  A. Login sebagai role read-only (PML / PCL / PENGOLAH / VIEWER):
 *     - banner "Mode Tinjau (Read-Only)" terlihat,
 *     - tombol Import LK Excel & Terima Dokumen (1 SLS) TIDAK ada,
 *     - toggle dokumen .btn-toggle-dok TIDAK ada (diganti badge),
 *     - seluruh .chk-transfer disabled,
 *     - window.CAN_EDIT === false,
 *     - POST /pengolahan/update-ruta ditolak 400/403.
 *  B. Login sebagai OPERATOR (IPDS):
 *     - tombol Import & Terima Dokumen ADA,
 *     - window.CAN_EDIT === true, checkbox enabled, toggle dokumen ADA.
 *
 * Prasyarat: server dev `php -S localhost:8091 -t public` (atau via webServer config).
 * Jalankan : `npx playwright test --project=chromium tests/playwright/rbac-pengolahan.spec.js`
 */
const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8091';
const PASS = 'Jember3509';

const READ_ONLY = [
  { role: 'PML', email: 'pml.demo@bpsjember.go.id' },
  { role: 'PCL', email: 'pcl.demo@bpsjember.go.id' },
  { role: 'PENGOLAH', email: 'aminatuss182002@gmail.com' },
  { role: 'VIEWER', email: 'viewer.demo@bpsjember.go.id' },
];

const EDITOR = { role: 'OPERATOR', email: 'operator.demo@bpsjember.go.id' };

async function login(page, email, password) {
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('input[name="email"]', { state: 'visible', timeout: 8000 });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  // Akun demo must_reset=0 → langsung ke dashboard; akun lain mungkin ke /password.
  await page.waitForLoadState('domcontentloaded');
}

async function logout(page) {
  const token = await page.locator('input[name="_csrf"]').first().getAttribute('value').catch(() => null);
  if (token) {
    await page.request.post(`${BASE}/logout`, { form: { _csrf: token } }).catch(() => {});
  }
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' }).catch(() => {});
}

test.describe('RBAC LK Pengolahan Sampel', () => {
  for (const u of READ_ONLY) {
    test(`A. ${u.role} read-only: banner + kontrol terkunci + POST ditolak`, async ({ page }) => {
      await login(page, u.email, PASS);
      await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });

      // 1. Banner Mode Tinjau terlihat.
      await expect(page.locator('#bannerReadOnly')).toBeVisible();
      await expect(page.locator('#bannerReadOnly')).toContainText('Mode Tinjau');

      // 2. Tombol tulis disembunyikan (Export tetap ada).
      await expect(page.locator('#btnImportLk')).toHaveCount(0);
      await expect(page.locator('#btnTerimaDokumenBatch')).toHaveCount(0);
      await expect(page.locator('#wrapBatchTransfer')).toHaveCount(0);
      await expect(page.locator('.btn-toggle-dok')).toHaveCount(0);
      await expect(page.locator('a[href*="/pengolahan/export"]').first()).toBeVisible();

      // 3. Checkbox transfer seluruhnya disabled (bila grid ada baris).
      const total = await page.locator('.chk-transfer').count();
      expect(total).toBeGreaterThan(0);
      expect(await page.locator('.chk-transfer:not([disabled])').count()).toBe(0);

      // 4. Flag client-side.
      expect(await page.evaluate(() => window.CAN_EDIT)).toBe(false);

      // 5. Backend menolak POST mutasi (403 RBAC / 400 validasi).
      const csrf = await page.locator('#modalCatatan input[name="_csrf"]').first().getAttribute('value')
        .catch(() => null)
        || await page.locator('input[name="_csrf"]').first().getAttribute('value');
      const rutaId = await page.locator('.chk-transfer').first().getAttribute('data-id');
      const resp = await page.request.post(`${BASE}/pengolahan/update-ruta`, {
        form: { _csrf: csrf, ruta_id: String(rutaId), status_transfer_k: '1' },
      });
      expect([400, 403]).toContain(resp.status());

      await logout(page);
    });
  }

  test('B. OPERATOR IPDS: kontrol tulis aktif + window.CAN_EDIT true', async ({ page }) => {
    await login(page, EDITOR.email, PASS);
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });

    await expect(page.locator('#bannerReadOnly')).toHaveCount(0);
    await expect(page.locator('#btnImportLk')).toHaveCount(1);
    await expect(page.locator('#btnTerimaDokumenBatch')).toHaveCount(1);
    await expect(page.locator('#wrapBatchTransfer')).toHaveCount(1);
    expect(await page.locator('.btn-toggle-dok').count()).toBeGreaterThan(0);
    expect(await page.locator('.chk-transfer:not([disabled])').count()).toBeGreaterThan(0);
    expect(await page.evaluate(() => window.CAN_EDIT)).toBe(true);

    await logout(page);
  });

  test('C. Modal telaah read-only: input terkunci, tombol simpan hilang', async ({ page }) => {
    await login(page, READ_ONLY[0].email, PASS);
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });

    // Buka modal telaah baris pertama (tetap ada untuk semua role;
    // berlabel "Telaah" untuk editor, "Lihat" untuk read-only).
    const btnDetail = page.locator('.btn-detail-catatan').first();
    await btnDetail.scrollIntoViewIfNeeded();
    await btnDetail.click();
    await expect(page.locator('#modalCatatan')).toBeVisible();
    await expect(page.locator('#badgeModalReadOnly')).toBeVisible();
    await expect(page.locator('#btnSimpanCatatan')).toHaveCount(0);

    await logout(page);
  });
});
