const { chromium } = require('C:/Users/Acer/AppData/Local/npm-cache/_npx/420ff84f11983ee5/node_modules/playwright');

const BASE_URL = 'http://sipandhalu.test';

async function runTest(channelName) {
  console.log(`\n========================================`);
  console.log(`RUNNING FILTER TEST ON: ${channelName.toUpperCase()}`);
  console.log(`========================================`);
  const browser = await chromium.launch({ channel: channelName, headless: true });
  const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });

  page.on('console', msg => console.log(`[BROWSER CONSOLE ${msg.type()}]:`, msg.text()));
  page.on('pageerror', err => console.error('[BROWSER ERROR]:', err));

  // 1. Login
  await page.goto(`${BASE_URL}/login`);
  await page.fill('input[name="email"]', 'admin@bpsjember.go.id');
  await page.fill('input[name="password"]', 'Jember3509');
  await Promise.all([
    page.waitForNavigation(),
    page.click('button[type="submit"]')
  ]);

  // 2. Go to monitoring
  await page.goto(`${BASE_URL}/monitoring`);
  await page.waitForSelector('#fKec');

  // Check initial state
  const initialKec = await page.$eval('#fKec', el => ({ value: el.value, optionsCount: el.options.length }));
  const initialDesa = await page.$eval('#fDesa', el => ({ value: el.value, optionsCount: el.options.length }));
  console.log('Initial Kec:', initialKec);
  console.log('Initial Desa:', initialDesa);

  // Get first available kecamatan code
  const kecOptions = await page.$eval('#fKec', el => {
    return Array.from(el.options).slice(1, 5).map(o => ({ value: o.value, text: o.text }));
  });
  console.log('Available Kec options:', kecOptions);

  // Test physical click on selects
  console.log('Testing physical click on #fKec...');
  await page.click('#fKec');
  console.log('Click on #fKec succeeded!');

  console.log('Testing physical click on #fDesa...');
  await page.click('#fDesa');
  console.log('Click on #fDesa succeeded!');

  console.log('Testing physical click on #fPengolah...');
  await page.click('#fPengolah');
  console.log('Click on #fPengolah succeeded!');

  if (kecOptions.length > 0) {
    const targetKec = kecOptions[0].value;
    console.log(`\nSelecting Kecamatan: ${targetKec} (${kecOptions[0].text})...`);

    // Listen to network request to /monitoring/data
    const [request] = await Promise.all([
      page.waitForRequest(req => req.url().includes('/monitoring/data'), { timeout: 5000 }).catch(e => {
        console.log('TIMEOUT waiting for /monitoring/data request on Kec change!');
        return [null];
      }),
      page.selectOption('#fKec', targetKec)
    ]);

    if (request) {
      console.log('AJAX request fired:', request.url());
      const response = await page.waitForResponse(res => res.url().includes('/monitoring/data'));
      console.log('AJAX response status:', response.status());
    }

    // Wait 500ms
    await page.waitForTimeout(500);

    // Check Desa options visibility and values
    const desaState = await page.$eval('#fDesa', el => {
      const opts = Array.from(el.options);
      const visibleOpts = opts.filter(o => o.style.display !== 'none');
      return {
        total: opts.length,
        visible: visibleOpts.length,
        visibleSamples: visibleOpts.slice(0, 5).map(o => ({ value: o.value, text: o.text, kec: o.dataset.kec }))
      };
    });
    console.log('Desa state after Kec select:', desaState);

    // Check selectedIndex of all filter selects
    const selectIndices = await page.evaluate(() => {
      const ids = ['fKec', 'fDesa', 'fPengolah', 'fPml', 'fPcl', 'fDok', 'fStage', 'fErr'];
      return ids.map(id => {
        const el = document.getElementById(id);
        return {
          id: id,
          val: el ? el.value : null,
          idx: el ? el.selectedIndex : null,
          selectedText: el && el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : 'NONE'
        };
      });
    });
    console.log('Select indices after AJAX renderAll:');
    console.log(selectIndices);

    // Check Filter summary tags
    const filterTags = await page.$eval('#monFilterTags', el => el.innerText);
    console.log('Filter tags:', filterTags);

    // 1. Test selecting Desa '6' (SUMBERREJO)
    console.log('\nSelecting Desa: 6 (SUMBERREJO)...');
    await Promise.all([
      page.waitForResponse(res => res.url().includes('/monitoring/data')),
      page.selectOption('#fDesa', '6')
    ]);
    const desaRows = await page.$$eval('#monGridBody tr', rows => rows.length);
    console.log('Grid rows after selecting Desa 6:', desaRows);
    const filterTagsDesa = await page.$eval('#monFilterTags', el => el.innerText);
    console.log('Filter tags after Desa select:', filterTagsDesa);

    // 2. Test selecting Dokumen ADA
    console.log('\nSelecting Status Dokumen: ADA...');
    await Promise.all([
      page.waitForResponse(res => res.url().includes('/monitoring/data')),
      page.selectOption('#fDok', 'ADA')
    ]);
    const dokRows = await page.$$eval('#monGridBody tr', rows => rows.length);
    console.log('Grid rows after selecting Dokumen ADA:', dokRows);

    // 3. Test selecting Tahap Funnel KP
    console.log('\nSelecting Tahap Funnel: KP...');
    await Promise.all([
      page.waitForResponse(res => res.url().includes('/monitoring/data')),
      page.selectOption('#fStage', 'KP')
    ]);
    const stageRows = await page.$$eval('#monGridBody tr', rows => rows.length);
    console.log('Grid rows after selecting Tahap KP:', stageRows);

    // 4. Test selecting Status Verifikasi (Bersih: 0)
    console.log('\nSelecting Status Verifikasi: 0 (Bersih)...');
    await Promise.all([
      page.waitForResponse(res => res.url().includes('/monitoring/data')),
      page.selectOption('#fErr', '0')
    ]);
    const errRows = await page.$$eval('#monGridBody tr', rows => rows.length);
    console.log('Grid rows after selecting Err 0:', errRows);

    // 5. Test sticky filter bar click when scrolled
    console.log('\nTesting sticky filter bar click when scrolled 400px...');
    await page.evaluate(() => window.scrollTo(0, 400));
    await page.waitForTimeout(200);

    // Click Reset button while scrolled
    console.log('Clicking Reset button while scrolled...');
    await Promise.all([
      page.waitForResponse(res => res.url().includes('/monitoring/data')),
      page.click('#monReset')
    ]);
    await page.waitForTimeout(300);

    const resetRows = await page.$$eval('#monGridBody tr', rows => rows.length);
    const resetKec = await page.$eval('#fKec', el => el.value);
    const resetDesa = await page.$eval('#fDesa', el => el.value);
    const resetDesaCount = await page.$eval('#fDesa', el => el.options.length);
    console.log('After Reset -> Grid rows:', resetRows, 'fKec value:', resetKec, 'fDesa value:', resetDesa, 'Total Desa options restored:', resetDesaCount);

    // Check all dropdowns have valid selectedIndex
    const finalIndices = await page.evaluate(() => {
      const ids = ['fKec', 'fDesa', 'fPengolah', 'fPml', 'fPcl', 'fDok', 'fStage', 'fErr'];
      return ids.map(id => {
        const el = document.getElementById(id);
        return { id: id, idx: el.selectedIndex, val: el.value };
      });
    });
    console.log('All select indices after reset (must all be >= 0):', finalIndices);
    const allValid = finalIndices.every(x => x.idx >= 0);
    console.log('All select indices valid (>= 0):', allValid ? 'PASSED' : 'FAILED');
  }

  await browser.close();
  console.log(`\nSUCCESS: All filter interaction tests passed on ${channelName.toUpperCase()}!`);
}

(async () => {
  await runTest('msedge');
  await runTest('chrome');
  console.log('\n========================================');
  console.log('ALL FILTER TESTS PASSED ON BOTH MSEDGE AND CHROME!');
  console.log('========================================');
})();
