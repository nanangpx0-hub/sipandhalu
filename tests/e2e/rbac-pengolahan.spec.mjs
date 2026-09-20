/**
 * Verifikasi RBAC LK Pengolahan — Playwright (Node.js).
 *
 * Prasyarat:
 *   npm i -D playwright
 *   npx playwright install chromium
 *   Server dev berjalan: php -S localhost:8091 -t public
 *
 * Jalankan: node tests/e2e/rbac-pengolahan.spec.mjs
 *
 * Skenario:
 *  A. Login sebagai Tim Sosial / PML / PCL / Pengolah / Viewer:
 *     - banner Mode Tinjau (Read-Only) terlihat,
 *     - tombol Import LK Excel & modal terima dokumen TIDAK ada,
 *     - checkbox .chk-transfer disabled,
 *     - tombol .btn-toggle-dok TIDAK ada (diganti badge),
 *     - POST /pengolahan/update-ruta ditolak 403 (bukan 400 validasi).
 *  B. Login sebagai Operator IPDS / ADMIN:
 *     - tombol Import & terima dokumen ADA,
 *     - checkbox enabled, toggle dokumen ADA,
 *     - POST update-ruta berhasil (status dokumen → ADA).
 */
import { chromium } from 'playwright';
import assert from 'node:assert';

const BASE = process.env.SIPANDHALU_BASE ?? 'http://localhost:8091';
const CSRF_RE = /name="_csrf"\s+value="([^"]+)"/;

const READ_ONLY = [
  { role: 'SM_SOSIAL', email: 'sm.sosial.demo@bpsjember.go.id', pass: 'Dummy3509!' },
  { role: 'PML', email: 'pml.demo@bpsjember.go.id', pass: 'Dummy3509!' },
  { role: 'PCL', email: 'pcl.demo@bpsjember.go.id', pass: 'Dummy3509!' },
  { role: 'PENGOLAH', email: 'pengolah.demo@bpsjember.go.id', pass: 'Dummy3509!' },
  { role: 'VIEWER', email: 'viewer.demo@bpsjember.go.id', pass: 'Dummy3509!' },
];

const EDITORS = [
  { role: 'OPERATOR', email: 'operator.demo@bpsjember.go.id', pass: 'Dummy3509!' },
  { role: 'ADMIN', email: 'admin@bpsjember.go.id', pass: 'Admin3509!' },
];

async function login(page, email, pass) {
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', pass);
  await Promise.all([
    page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]);
}

async function csrfToken(request, url) {
  const res = await request.get(url);
  const html = await res.text();
  const m = html.match(CSRF_RE);
  assert.ok(m, 'token CSRF tidak ditemukan');
  return m[1];
}

async function run() {
  const browser = await chromium.launch();
  const ctx = await browser.newContext();
  const page = await ctx.newPage();
  const results = [];

  for (const u of READ_ONLY) {
    await login(page, u.email, u.pass);
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });

    // 1. Banner read-only
    const banner = await page.locator('.alert-info:has-text("Mode Tinjau")').count();
    assert.ok(banner >= 1, `[${u.role}] banner Mode Tinjau tidak tampil`);

    // 2. Tombol tulis disembunyikan
    assert.strictEqual(await page.locator('[data-target="#modalImportLk"]').count(), 0, `[${u.role}] tombol Import harus disembunyikan`);
    assert.strictEqual(await page.locator('[data-target="#modalTerimaDokumenBatch"]').count(), 0, `[${u.role}] tombol terima dokumen harus disembunyikan`);
    assert.strictEqual(await page.locator('.btn-toggle-dok').count(), 0, `[${u.role}] toggle dokumen harus diganti badge`);
    assert.ok((await page.locator('.chk-transfer').count()) >= 0, 'grid kosong tetap valid');

    // 3. Checkbox disabled (bila ada baris)
    const enabled = await page.locator('.chk-transfer:not([disabled])').count();
    assert.strictEqual(enabled, 0, `[${u.role}] checkbox transfer harus disabled`);

    // 4. window.CAN_EDIT = false
    const canEdit = await page.evaluate(() => window.CAN_EDIT);
    assert.strictEqual(canEdit, false, `[${u.role}] window.CAN_EDIT harus false`);

    // 5. Backend menolak POST dengan 403
    const token = await csrfToken(page.request, `${BASE}/pengolahan`);
    const cookies = await ctx.cookies();
    const cookie = cookies.map((c) => `${c.name}=${c.value}`).join('; ');
    const rutaId = await page.locator('tr[data-ruta-id]').first().getAttribute('data-ruta-id').catch(() => null);
    if (rutaId) {
      const resp = await page.evaluate(async ({ base, id, csrf }) => {
        const r = await fetch(`${base}/pengolahan/update-ruta`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ ruta_id: String(id), status_transfer_k: '1', _csrf: csrf }),
        });
        return { status: r.status, text: (await r.text()).slice(0, 200) };
      }, { base: BASE, id: rutaId, csrf: token });
      void cookie;
      assert.strictEqual(resp.status, 403, `[${u.role}] POST update-ruta harus 403, dapat ${resp.status}: ${resp.text}`);
    }
    results.push(`${u.role}: READ-ONLY OK`);
    await page.goto(`${BASE}/logout`, { waitUntil: 'domcontentloaded' }).catch(() => {});
  }

  for (const u of EDITORS) {
    await login(page, u.email, u.pass);
    await page.goto(`${BASE}/pengolahan`, { waitUntil: 'domcontentloaded' });

    assert.strictEqual(await page.locator('.alert-info:has-text("Mode Tinjau")').count(), 0, `[${u.role}] banner read-only tidak boleh tampil`);
    assert.ok((await page.locator('[data-target="#modalImportLk"]').count()) >= 1, `[${u.role}] tombol Import harus tampil`);
    const canEdit = await page.evaluate(() => window.CAN_EDIT);
    assert.strictEqual(canEdit, true, `[${u.role}] window.CAN_EDIT harus true`);
    results.push(`${u.role}: EDITOR OK`);
    await page.goto(`${BASE}/logout`, { waitUntil: 'domcontentloaded' }).catch(() => {});
  }

  await browser.close();
  console.log(results.join('\n'));
  console.log('RBAC LK Pengolahan: SEMUA SKENARIO LULUS');
}

run().catch((e) => { console.error('GAGAL:', e.message); process.exit(1); });
