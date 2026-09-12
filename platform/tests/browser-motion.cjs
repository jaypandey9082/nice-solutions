/* Run with PLAYWRIGHT_MODULE pointing to an installed playwright package.
   Only browser-output artifacts are written; no CMS data is changed. */
const fs = require('node:fs');
const path = require('node:path');
const playwright = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const engine = process.env.BROWSER_ENGINE || 'chrome';
const base = process.env.NICE_MAIN_URL || 'http://localhost:8180/';
const output = path.resolve(__dirname, '../../output/playwright/platform-stage1', engine);
fs.mkdirSync(output, { recursive: true });
const results = { browser: '', base, timestamp: new Date().toISOString(), checks: [], layouts: [], screenshots: [], errors: [], failedRequests: [] };
const check = (name, pass, detail) => { results.checks.push({ name, pass: Boolean(pass), detail }); };
const pause = (page, ms = 450) => page.waitForTimeout(ms);
const screenshot = async (page, name, fullPage = false) => {
  const file = path.join(output, `${name}.png`);
  await page.screenshot({ path: file, fullPage });
  results.screenshots.push(file);
};
const sweep = async (page) => {
  await page.evaluate(async () => {
    for (let y = 0; y < document.documentElement.scrollHeight; y += innerHeight * .65) {
      scrollTo({ top: y, behavior: 'instant' });
      await new Promise(resolve => setTimeout(resolve, 75));
    }
  });
  await pause(page, 500);
};
const settle = async (page) => {
  await page.evaluate(() => document.fonts.ready);
  await pause(page);
};
const layout = async (page) => page.evaluate(() => {
  const visible = e => e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden';
  const overflow = [...document.querySelectorAll('body *')].filter(e => {
    if (!visible(e) || e.closest('#nice-menu,[hidden]') || e.matches('.skip-link')) return false;
    const r = e.getBoundingClientRect();
    return r.right > innerWidth + 1 || r.left < -1;
  }).map(e => ({ tag: e.tagName, class: e.className, text: e.textContent.trim().slice(0, 55) })).slice(0, 15);
  return { width: innerWidth, height: innerHeight, scrollWidth: document.documentElement.scrollWidth, overflow,
    header: document.querySelector('[data-site-header]').getBoundingClientRect().toJSON(),
    hero: document.querySelector('.hero')?.getBoundingClientRect().toJSON(),
    brokenImages: [...document.images].filter(e => e.complete && !e.naturalWidth).map(e => e.currentSrc),
    media: [...document.images].map(e => ({ src: e.currentSrc, width: e.naturalWidth, loading: e.loading })),
    invisibleReveals: [...document.querySelectorAll('[data-reveal]')].filter(e => getComputedStyle(e).opacity !== '1').length };
});

(async () => {
  const browser = await (engine === 'chrome' ? playwright.chromium : playwright[engine]).launch({ ...(engine === 'chrome' ? { channel: 'chrome' } : {}), headless: true });
  results.browser = `${engine} ${browser.version()} via Playwright, headless macOS`;
  try {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await context.newPage();
    page.on('pageerror', e => results.errors.push(e.message));
    page.on('requestfailed', r => { if (r.failure()?.errorText !== 'net::ERR_ABORTED') results.failedRequests.push({ url: r.url(), error: r.failure()?.errorText }); });
    await page.addInitScript(() => {
      window.__motionStats = { shifts: 0, longTasks: [] };
      if (PerformanceObserver.supportedEntryTypes.includes('layout-shift')) new PerformanceObserver(list => list.getEntries().forEach(e => {
        if (!e.hadRecentInput) window.__motionStats.shifts += e.value;
      })).observe({ type: 'layout-shift', buffered: true });
      if (PerformanceObserver.supportedEntryTypes.includes('longtask')) new PerformanceObserver(list => list.getEntries().forEach(e => window.__motionStats.longTasks.push(e.duration))).observe({ type: 'longtask', buffered: true });
      window.__motionStats.supported = PerformanceObserver.supportedEntryTypes;
    });
    for (const width of [320, 390, 768, 1024, 1440]) {
      await page.setViewportSize({ width, height: width >= 1024 ? 900 : 844 });
      await page.goto(base); await settle(page);
      await screenshot(page, `main-${width}-hero`);
      await sweep(page);
      await page.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await pause(page);
      await screenshot(page, `main-${width}-full`, true);
      const state = await layout(page); results.layouts.push(state);
      check(`layout-${width}`, state.scrollWidth <= width && !state.overflow.length, state.overflow);
      check(`loaded-images-${width}`, !state.brokenImages.length, state.brokenImages);
      check(`reveals-after-scroll-${width}`, state.invisibleReveals === 0, state.invisibleReveals);
      results.checks.push({ name: `lab-motion-${width}`, detail: await page.evaluate(() => window.__motionStats) });
    }
    await page.setViewportSize({ width: 390, height: 844 }); await page.goto(base); await settle(page);
    await page.keyboard.press('Tab');
    check('first-tab-skip-link', await page.locator('.skip-link').evaluate(e => e === document.activeElement));
    await page.keyboard.press('Enter');
    check('skip-focus-main', await page.evaluate(() => document.activeElement.id === 'main-content'));
    await page.evaluate(() => scrollTo({ top: 600, behavior: 'instant' })); await pause(page);
    const before = await page.evaluate(() => scrollY);
    await page.locator('[data-menu-toggle]').click(); await pause(page, 220);
    await screenshot(page, 'main-390-menu');
    check('menu-focus-close', await page.evaluate(() => document.activeElement.matches('[data-menu-close]')));
    check('menu-background-inert', await page.locator('header,main#main-content,footer').evaluateAll(es => es.every(e => e.inert)));
    const links = await page.locator('#nice-menu a[href],#nice-menu button').all();
    await links[0].focus(); await page.keyboard.press('Shift+Tab');
    check('menu-reverse-trap', await links[links.length - 1].evaluate(e => e === document.activeElement));
    await page.keyboard.press('Tab'); check('menu-forward-trap', await links[0].evaluate(e => e === document.activeElement));
    await page.keyboard.press('Escape'); await page.waitForFunction(() => document.getElementById('nice-menu').hidden);
    check('escape-restores-focus-scroll', await page.evaluate(y => document.activeElement.matches('[data-menu-toggle]') && Math.abs(scrollY-y)<3, before));
    await page.locator('[data-menu-toggle]').click();
    await page.locator('#nice-menu a[href$="#about"]').click(); await pause(page, 1600);
    const anchor = await page.evaluate(() => ({ focus: document.activeElement.id, top: document.getElementById('about').getBoundingClientRect().top, bottom: document.querySelector('header').getBoundingClientRect().bottom, hash: location.hash }));
    check('menu-anchor-focus-offset', anchor.focus === 'about' && anchor.hash === '#about' && Math.abs(anchor.top-anchor.bottom-16)<3, anchor);
    check('anchor-smooth-cleanup', await page.evaluate(() => !document.documentElement.classList.contains('is-anchor-scrolling')));
    await page.locator('[data-menu-toggle]').click(); await page.setViewportSize({ width: 1024, height: 900 }); await pause(page, 100);
    check('resize-dismiss-focus-visible', await page.evaluate(() => document.getElementById('nice-menu').hidden && document.activeElement.closest('header') !== null && document.activeElement.getClientRects().length>0));
    await page.goto(base+'#work'); await settle(page);
    check('direct-fragment-offset', await page.evaluate(() => Math.abs(document.getElementById('work').getBoundingClientRect().top-document.querySelector('header').getBoundingClientRect().bottom-16)<3));
    await page.goto(base); await page.evaluate(() => scrollTo(0,1100)); await pause(page, 100);
    await page.goto(base+'?browser-qa=history'); await page.goBack(); await pause(page);
    check('back-scroll-restoration', await page.evaluate(() => Math.abs(scrollY-1100)<3), await page.evaluate(() => scrollY));
    await page.goto(base); await page.emulateMedia({ reducedMotion: 'reduce' }); await pause(page, 100);
    check('live-reduced-all-visible', await page.locator('[data-reveal]').evaluateAll(es => es.every(e=>getComputedStyle(e).opacity==='1' && getComputedStyle(e).transitionDelay==='0s')));
    await page.setViewportSize({ width: 390, height: 844 }); await page.locator('[data-menu-toggle]').click();
    check('reduced-menu-no-animation', await page.locator('#nice-menu').evaluate(e=>getComputedStyle(e).animationName==='none'));
    await page.keyboard.press('Escape'); await page.emulateMedia({ reducedMotion: 'no-preference' }); await pause(page, 100);
    check('motion-enabled-no-replay', await page.locator('[data-reveal]').evaluateAll(es => es.every(e=>e.classList.contains('is-visible'))));
    await page.setViewportSize({ width: 390, height: 500 }); await page.goto(base); await settle(page);
    await screenshot(page, 'main-390-short-hero');
    await page.locator('[data-menu-toggle]').click(); await pause(page, 220); await screenshot(page,'main-390-short-menu');
    check('short-menu-can-scroll', await page.locator('#nice-menu').evaluate(e=>e.scrollHeight<=e.clientHeight || getComputedStyle(e).overflowY==='auto'));
    await page.keyboard.press('Escape');
    // Reflow equivalent to 200% zoom on a 1440px desktop. This is not a
    // claim of browser-chrome zoom testing or real-device accessibility coverage.
    await page.setViewportSize({ width: 720, height: 450 }); await page.goto(base); await settle(page);
    await screenshot(page, 'main-200percent-reflow');
    const zoom = await layout(page); check('200percent-equivalent-reflow', !zoom.overflow.length && zoom.scrollWidth <= 720, zoom.overflow);
    for (const width of [320, 390, 768, 1024, 1440]) {
      const plainContext = await browser.newContext({ javaScriptEnabled:false, viewport:{width,height:844} });
      const plain = await plainContext.newPage(); await plain.goto(base); await settle(plain);
      const state = await layout(plain);
      check(`nojs-layout-${width}`, state.scrollWidth<=width && !state.overflow.length, state.overflow);
      check(`nojs-navigation-${width}`, await plain.locator('.desktop-navigation').isVisible() && !await plain.locator('[data-menu-toggle]').isVisible());
      check(`nojs-reveals-${width}`, state.invisibleReveals===0);
      if (width===320 || width===768) await screenshot(plain,`main-${width}-nojs`);
      await plainContext.close();
    }
    for (const port of [8181,8182]) {
      const response = await page.goto(`http://localhost:${port}/`); await settle(page);
      check(`division-${port}-homepage-load`, response.status()===200, await page.title());
      await screenshot(page, `division-${port}-390`);
    }
    check('page-errors', results.errors.length===0, results.errors);
    check('failed-requests', results.failedRequests.length===0, results.failedRequests);
  } catch (error) { results.errors.push(error.stack); }
  finally {
    await browser.close();
    fs.writeFileSync(path.join(output,'report.json'),JSON.stringify(results,null,2));
    console.log(JSON.stringify({browser:results.browser,checks:results.checks,errors:results.errors,screenshots:results.screenshots},null,2));
    if(results.errors.length || results.checks.some(c=>c.pass===false)) process.exitCode=1;
  }
})();
