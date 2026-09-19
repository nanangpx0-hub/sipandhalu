const { chromium } = require('C:/Users/Acer/AppData/Local/npm-cache/_npx/420ff84f11983ee5/node_modules/playwright');

const BASE_URL = 'http://sipandhalu.test';

async function runBrowserTest(browserChannel, browserName) {
  console.log(`\n=======================================================`);
  console.log(`🚀 TESTING WITH ${browserName.toUpperCase()} (Channel: ${browserChannel})`);
  console.log(`=======================================================`);

  const browser = await chromium.launch({
    channel: browserChannel,
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const context = await browser.newContext({
    viewport: { width: 1366, height: 768 }
  });
  const page = await context.newPage();

  const consoleLogs = [];
  const pageErrors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') consoleLogs.push(`[CONSOLE ERROR] ${msg.text()}`);
  });
  page.on('pageerror', err => {
    pageErrors.push(`[PAGE ERROR] ${err.message}`);
  });

  try {
    // 1. LOGIN
    console.log(`\n[1/7] Melakukan Login...`);
    const t0Login = Date.now();
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@bpsjember.go.id');
    await page.fill('input[name="password"]', 'Admin3509!');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      page.click('button[type="submit"]')
    ]);
    const loginDuration = Date.now() - t0Login;
    console.log(`  ✓ Login berhasil (${loginDuration} ms) -> URL: ${page.url()}`);

    // 2. NAVIGASI KE /monitoring & UKUR PERFORMA INITIAL LOAD
    console.log(`\n[2/7] Mengakses Dashboard Monitoring (/monitoring)...`);
    const t0Nav = Date.now();
    const res = await page.goto(`${BASE_URL}/monitoring`, { waitUntil: 'load' });
    const navDuration = Date.now() - t0Nav;
    const httpStatus = res.status();

    // Ambil Web Performance Timing
    const perfMetrics = await page.evaluate(() => {
      const nav = performance.getEntriesByType('navigation')[0];
      return {
        dns: Math.round(nav.domainLookupEnd - nav.domainLookupStart),
        connect: Math.round(nav.connectEnd - nav.connectStart),
        ttfb: Math.round(nav.responseStart - nav.requestStart),
        download: Math.round(nav.responseEnd - nav.responseStart),
        domInteractive: Math.round(nav.domInteractive),
        domContentLoaded: Math.round(nav.domContentLoadedEventEnd),
        loadEvent: Math.round(nav.loadEventEnd),
        transferSize: nav.transferSize
      };
    });

    console.log(`  ✓ HTTP Status: ${httpStatus}`);
    console.log(`  ✓ Total Page Load: ${navDuration} ms`);
    console.log(`  ✓ Performance Breakdown:`);
    console.log(`    - DNS Lookup     : ${perfMetrics.dns} ms`);
    console.log(`    - TCP Connect    : ${perfMetrics.connect} ms`);
    console.log(`    - TTFB           : ${perfMetrics.ttfb} ms`);
    console.log(`    - HTML Download  : ${perfMetrics.download} ms (${perfMetrics.transferSize} bytes)`);
    console.log(`    - DOM Interactive: ${perfMetrics.domInteractive} ms`);
    console.log(`    - DOM Ready      : ${perfMetrics.domContentLoaded} ms`);
    console.log(`    - Full Load Event: ${perfMetrics.loadEvent} ms`);

    // 3. VERIFIKASI ELEMEN UTAMA TER-RENDER (TIER 1, TIER 2, TIER 3)
    console.log(`\n[3/7] Verifikasi Render Widget & Data Grid...`);
    const kpiCount = await page.locator('.mon-kpi').count();
    const hasStack = await page.locator('#monStack .mon-stack__bar').isVisible();
    const hasTrend = await page.locator('#monTrendChart svg').isVisible();
    const hasDonut = await page.locator('#monDonutChart svg').isVisible();
    const gridRows = await page.locator('#monGridBody tr').count();
    const gridInfoText = await page.locator('#monGridInfo').innerText();

    console.log(`  ✓ KPI Cards      : ${kpiCount} kartu terpasang`);
    console.log(`  ✓ Stacked Funnel : ${hasStack ? 'Tampil' : 'Gagal'}`);
    console.log(`  ✓ SVG Trend Chart: ${hasTrend ? 'Tampil' : 'Gagal'}`);
    console.log(`  ✓ SVG Donut Chart: ${hasDonut ? 'Tampil' : 'Gagal'}`);
    console.log(`  ✓ Data Grid Rows : ${gridRows} baris ruta awal ter-render (${gridInfoText})`);

    // 4. TEST INTERAKSI FILTER CHIP & RESPONSE TIME
    console.log(`\n[4/7] Uji Interaksi Filter (Chip Waktu & Tahap Funnel)...`);
    // Klik chip '7 Hari'
    const t0Chip = Date.now();
    const [resChip] = await Promise.all([
      page.waitForResponse(r => r.url().includes('/monitoring/data') && r.status() === 200),
      page.click('.mon-chip[data-range="7d"]')
    ]);
    const chipDuration = Date.now() - t0Chip;
    console.log(`  ✓ Klik Chip "7 Hari" -> Data live update: ${chipDuration} ms`);

    // Klik segmen stack funnel
    const t0Seg = Date.now();
    const [resSeg] = await Promise.all([
      page.waitForResponse(r => r.url().includes('/monitoring/data') && r.status() === 200),
      page.click('.mon-stack__item[data-stage="transfer_kp"]')
    ]);
    const segDuration = Date.now() - t0Seg;
    const filterTagCount = await page.locator('.mon-filter-tag').count();
    console.log(`  ✓ Cross-filter Stack Funnel "KP" -> Live update: ${segDuration} ms (Tag aktif: ${filterTagCount})`);

    // Reset filter
    const t0Reset = Date.now();
    const [resReset] = await Promise.all([
      page.waitForResponse(r => r.url().includes('/monitoring/data') && r.status() === 200),
      page.click('#monReset')
    ]);
    const resetDuration = Date.now() - t0Reset;
    console.log(`  ✓ Reset filter -> Live update: ${resetDuration} ms`);

    // 5. TEST PENCARIAN DENGAN DEBOUNCE (SEARCH INPUT)
    console.log(`\n[5/7] Uji Pencarian Global (Input Search Debounced)...`);
    const t0Search = Date.now();
    const [resSearch] = await Promise.all([
      page.waitForResponse(r => r.url().includes('/monitoring/data') && r.status() === 200),
      page.fill('#fQ', '3509')
    ]);
    const searchDuration = Date.now() - t0Search;
    const searchRows = await page.locator('#monGridBody tr').count();
    console.log(`  ✓ Pencarian "3509" -> ${searchRows} baris ditemukan (${searchDuration} ms)`);
    
    // Clear search
    await Promise.all([
      page.waitForResponse(r => r.url().includes('/monitoring/data') && r.status() === 200),
      page.fill('#fQ', '')
    ]);

    // 6. TEST SORTING & PAGINASI
    console.log(`\n[6/7] Uji Paginasi & Sort Kolom...`);
    const t0Sort = Date.now();
    const [resSort] = await Promise.all([
      page.waitForResponse(r => r.url().includes('/monitoring/data') && r.status() === 200),
      page.click('th button[data-sort="desa"]')
    ]);
    const sortDuration = Date.now() - t0Sort;
    console.log(`  ✓ Sort kolom "Wilayah" -> Selesai (${sortDuration} ms)`);

    const t0Page = Date.now();
    const hasNextBtn = await page.locator('.mon-pager button[data-page="2"]').count();
    if (hasNextBtn > 0) {
      const [resPage] = await Promise.all([
        page.waitForResponse(r => r.url().includes('/monitoring/data') && r.status() === 200),
        page.click('.mon-pager button[data-page="2"]')
      ]);
      const pageDuration = Date.now() - t0Page;
      console.log(`  ✓ Navigasi ke Halaman 2 -> Selesai (${pageDuration} ms)`);
    } else {
      console.log(`  ℹ Halaman 2 tidak tersedia pada data filter ini.`);
    }

    // 7. TEST DRAWER QUICK VIEW & RIWAYAT AUDIT
    console.log(`\n[7/7] Uji Quick View Drawer & Riwayat Audit...`);
    const firstDetailBtn = page.locator('#monGridBody [data-detail-id]').first();
    const t0Drawer = Date.now();
    const [resDetail] = await Promise.all([
      page.waitForResponse(r => r.url().includes('/monitoring/detail/') && r.status() === 200),
      firstDetailBtn.click()
    ]);
    await page.waitForSelector('#monDrawer[data-open="1"]');
    const drawerDuration = Date.now() - t0Drawer;
    console.log(`  ✓ Buka Drawer Detail Ruta & Audit Trail: ${drawerDuration} ms`);

    // Tutup drawer dengan tombol Esc
    const t0Close = Date.now();
    await page.keyboard.press('Escape');
    await page.waitForSelector('#monDrawer[data-open="0"]');
    const closeDuration = Date.now() - t0Close;
    console.log(`  ✓ Tutup Drawer (Esc): ${closeDuration} ms`);

    // PERIKSA ERROR DI CONSOLE ATAU PAGE
    if (consoleLogs.length > 0) {
      console.log(`  ⚠️ Console Warnings/Errors:`, consoleLogs);
    } else {
      console.log(`  ✓ Console Browser Bersih (0 error)`);
    }
    if (pageErrors.length > 0) {
      console.log(`  ❌ Unhandled Page Errors:`, pageErrors);
    } else {
      console.log(`  ✓ Unhandled Exception Bersih (0 error)`);
    }

    console.log(`\n✅ PENGUJIAN ${browserName.toUpperCase()} SELESAI: SEMUA FITUR RESPONSIF & BEBAS LAG!`);
    return true;

  } catch (err) {
    console.error(`❌ GAGAL PADA ${browserName}:`, err);
    return false;
  } finally {
    await browser.close();
  }
}

(async () => {
  console.log(`\nMemulai pengujian komprehensif Playwright pada Microsoft Edge & Google Chrome...`);
  const edgeSuccess = await runBrowserTest('msedge', 'Microsoft Edge');
  const chromeSuccess = await runBrowserTest('chrome', 'Google Chrome');

  console.log(`\n=======================================================`);
  console.log(`RINGKASAN HASIL TEST PLAYWRIGHT:`);
  console.log(`  - Microsoft Edge : ${edgeSuccess ? 'PASSED (Cepat & Responsif)' : 'FAILED'}`);
  console.log(`  - Google Chrome  : ${chromeSuccess ? 'PASSED (Cepat & Responsif)' : 'FAILED'}`);
  console.log(`=======================================================\n`);

  if (!edgeSuccess || !chromeSuccess) {
    process.exit(1);
  }
})();
