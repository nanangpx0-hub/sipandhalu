const { test, expect } = require('@playwright/test');

// Pakai Chrome sistem bila browser bawaan Playwright belum terunduh:
//   $env:USE_SYSTEM_CHROME=1; npx playwright test tests/playwright/telaah-ipds.spec.js
if (process.env.USE_SYSTEM_CHROME === '1') {
  test.use({ channel: 'chrome' });
}

const BASE = 'http://localhost:8091';
const OPERATOR_EMAIL = 'operator.demo@bpsjember.go.id';
const OPERATOR_PW = 'Jember3509';
const NOTE_KP = 'Telaah IPDS E2E KP ' + Date.now();
const NOTE_M = 'Telaah IPDS E2E Modul ' + Date.now();

test.describe('SIPANDHALU — Telaah & Keputusan Tim IPDS', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  });

  async function loginOperator(page) {
    await page.waitForSelector('input[name="email"]', { state: 'visible', timeout: 5000 });
    await page.fill('input[name="email"]', OPERATOR_EMAIL);
    await page.fill('input[name="password"]', OPERATOR_PW);
    await page.click('button[type="submit"]');
    await page.waitForURL(/\//, { timeout: 10000 });
  }

  test('T40: Isi Telaah IPDS via modal → tersimpan & muncul lagi', async ({ page }) => {
    await loginOperator(page);
    await page.goto(`${BASE}/pengolahan?periode_id=1`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#tablePengolahan', { timeout: 15000 });

    // 1. Buka modal telaah baris pertama
    await page.locator('.btn-detail-catatan').first().click();
    await expect(page.locator('#m_ket_kp_ipds')).toBeVisible({ timeout: 5000 });

    // 2. Isi kedua telaah IPDS (Tab 2 perlu diklik dulu agar terlihat) lalu simpan
    await page.click('#modul-tab');
    await expect(page.locator('#m_ket_m_ipds')).toBeVisible();
    await page.fill('#m_ket_m_ipds', NOTE_M);
    await page.click('#kp-tab');
    await page.fill('#m_ket_kp_ipds', NOTE_KP);
    await page.click('#btnSimpanCatatan');
    await page.waitForTimeout(3000);
    await page.waitForSelector('#tablePengolahan', { timeout: 15000 });

    // 3. Badge IPDS Selesai tampil di baris pertama
    const firstRow = page.locator('#tablePengolahan tbody tr').first();
    await expect(firstRow.locator('text=IPDS Selesai')).toBeVisible();

    // 4. Buka ulang modal → nilai kembali muncul (terbaca dari database)
    await page.locator('.btn-detail-catatan').first().click();
    await expect(page.locator('#m_ket_kp_ipds')).toHaveValue(NOTE_KP, { timeout: 5000 });
    await expect(page.locator('#m_ket_m_ipds')).toHaveValue(NOTE_M);
  });
});
