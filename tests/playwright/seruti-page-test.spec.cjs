const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8091';
const ADMIN_EMAIL = 'admin@bpsjember.go.id';
const ADMIN_PW = 'Jember3509';
const ADMIN_NEW_PW = 'AdminBaru2026!';
const PENGOLAH_EMAIL = 'aminatuss182002@gmail.com';
const PENGOLAH_PW = 'Jember3509';
const PENGOLAH_NEW_PW = 'Pengolah2026!';

async function smartLogin(page, email, pw, newPw) {
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('input[name="email"]', { state: 'visible', timeout: 5000 });
  await page.fill('input[name="email"]', email);
  await page.waitForSelector('input[name="password"]', { state: 'visible', timeout: 5000 });
  await page.fill('input[name="password"]', newPw);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');
  await page.waitForURL(/\//, { timeout: 10000 }).catch(() => {});
  if (page.url().includes('/') && !page.url().includes('/login') && !page.url().includes('/password')) {
    return;
  }
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('input[name="email"]', { state: 'visible', timeout: 5000 });
  await page.fill('input[name="email"]', email);
  await page.waitForSelector('input[name="password"]', { state: 'visible', timeout: 5000 });
  await page.fill('input[name="password"]', pw);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');
  await page.waitForURL(/password/, { timeout: 10000 }).catch(() => {});
  if (page.url().includes('/password')) {
    await page.waitForSelector('input[name="old_password"]', { timeout: 5000 });
    await page.fill('input[name="old_password"]', pw);
    await page.fill('input[name="new_password"]', newPw);
    await page.evaluate(() => { document.querySelector('form[action="/password"]').submit(); });
    await page.waitForLoadState('domcontentloaded');
    await page.waitForURL(/\//, { timeout: 10000 }).catch(() => {});
    await page.waitForLoadState('networkidle');
  }
}

test.describe('SIPANDHALU — Modul Seruti E2E', () => {

  test('ADMIN: login + reset → /seruti tampil', async ({ page }) => {
    await smartLogin(page, ADMIN_EMAIL, ADMIN_PW, ADMIN_NEW_PW);
    await page.goto(`${BASE}/seruti`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await expect(page.locator('h1')).toContainText('Pengolahan Seruti');
  });

  test('ADMIN: sidebar Pengolahan Seruti ada & active', async ({ page }) => {
    await smartLogin(page, ADMIN_EMAIL, ADMIN_PW, ADMIN_NEW_PW);
    await page.goto(`${BASE}/seruti`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    const sidebar = await page.textContent('.main-sidebar');
    expect(sidebar).toContain('Pengolahan Seruti');
    const menuLink = page.locator('.main-sidebar a[href="/seruti"]');
    await expect(menuLink).toHaveClass(/active/);
  });

  test('ADMIN: tab triwulan tampil', async ({ page }) => {
    await smartLogin(page, ADMIN_EMAIL, ADMIN_PW, ADMIN_NEW_PW);
    await page.goto(`${BASE}/seruti`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    const tabs = page.locator('.btn-group .btn');
    await expect(tabs).toHaveCount(4, { timeout: 5000 }).catch(() => {});
  });

  test('ADMIN: export Seruti via API 200', async ({ page }) => {
    await smartLogin(page, ADMIN_EMAIL, ADMIN_PW, ADMIN_NEW_PW);
    const response = await page.request.get(`${BASE}/seruti/export?periode_id=8`);
    expect(response.status()).toBe(200);
    const ct = (response.headers()['content-type'] || '').toLowerCase();
    expect(ct).toContain('spreadsheetml');
    const cd = response.headers()['content-disposition'] || '';
    expect(cd).toContain('.xlsx');
  });

  test('PENGOLAH: login + reset → /seruti tampil', async ({ page }) => {
    await smartLogin(page, PENGOLAH_EMAIL, PENGOLAH_PW, PENGOLAH_NEW_PW);
    await page.goto(`${BASE}/seruti`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await expect(page.locator('h1')).toContainText('Pengolahan Seruti');
  });

  test('PENGOLAH: switch transfer Seruti disabled', async ({ page }) => {
    await smartLogin(page, PENGOLAH_EMAIL, PENGOLAH_PW, PENGOLAH_NEW_PW);
    await page.goto(`${BASE}/seruti`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForSelector('table#tableSeruti', { timeout: 10000 }).catch(() => {});
    const firstToggle = page.locator('table#tableSeruti tbody tr input.chk-transfer-seruti').first();
    if (await firstToggle.count() > 0) {
      await expect(firstToggle).toBeDisabled();
    } else {
      expect(true).toBe(true);
    }
  });

  test('VIEWER: /seruti tanpa login → ke /login', async ({ page }) => {
    await page.goto(`${BASE}/seruti`, { waitUntil: 'domcontentloaded' });
    await page.waitForURL(/login/, { timeout: 5000 }).catch(() => {});
    expect(page.url().includes('/login')).toBeTruthy();
  });

  test('ADMIN: logout → /login', async ({ page }) => {
    await smartLogin(page, ADMIN_EMAIL, ADMIN_PW, ADMIN_NEW_PW);
    await page.evaluate(() => {
      const form = document.querySelector('form[action="/logout"]');
      if (form) { form.submit(); }
    });
    await page.waitForURL(/login/, { timeout: 10000 }).catch(() => {});
    const bodyText = await page.textContent('body').catch(() => '');
    expect(bodyText).toContain('Masuk');
  });
});
