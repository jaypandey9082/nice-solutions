const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');

const origin = 'http://nice-solutions.local';
const outDir = path.resolve('output/playwright/baseline');
if (!fs.existsSync(outDir)) {
  fs.mkdirSync(outDir, { recursive: true });
}

const routes = [
  { path: '/', slug: 'home' },
  { path: '/events/', slug: 'events' },
  { path: '/events/services/', slug: 'events-services' },
  { path: '/events/case-studies/', slug: 'events-case-studies' },
  { path: '/events/case-studies/voltas-fam-tastic-fiesta/', slug: 'events-voltas' },
  { path: '/studio/', slug: 'studio' },
];

const viewports = [
  { width: 390, height: 844, name: 'mobile' },
  { width: 1440, height: 900, name: 'desktop' },
];

(async () => {
	const launchOptions = { headless: true };
	if (process.env.PLAYWRIGHT_CHROMIUM_PATH) {
		launchOptions.executablePath = process.env.PLAYWRIGHT_CHROMIUM_PATH;
	}
	const browser = await chromium.launch(launchOptions);

  const report = {};

  for (const route of routes) {
    report[route.slug] = {};

    for (const vp of viewports) {
      const page = await browser.newPage();
      await page.setViewportSize({ width: vp.width, height: vp.height });

      const consoleErrors = [];
      const networkRequests = [];
      let totalBytes = 0;
      let jsBytes = 0;
      let cssBytes = 0;

      page.on('console', (msg) => {
        if (msg.type() === 'error') {
          consoleErrors.push(msg.text());
        }
      });

      page.on('response', async (res) => {
        try {
          const buffer = await res.body().catch(() => null);
          const size = buffer ? buffer.length : 0;
          totalBytes += size;
          const ct = res.headers()['content-type'] || '';
          if (ct.includes('javascript')) jsBytes += size;
          if (ct.includes('css')) cssBytes += size;
          networkRequests.push({ url: res.url(), status: res.status(), size });
        } catch {}
      });

      await page.addInitScript(() => {
        window.__cls = 0;
        try {
          new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
              if (!entry.hadRecentInput) window.__cls += entry.value;
            }
          }).observe({ type: 'layout-shift', buffered: true });
        } catch {}
      });

      const startTime = Date.now();
      await page.goto(`${origin}${route.path}`, { waitUntil: 'networkidle' });
      const loadDuration = Date.now() - startTime;

      // Scroll to trigger lazy elements/reveals
      const scrollHeight = await page.evaluate(() => document.documentElement.scrollHeight);
      const step = Math.floor(vp.height * 0.7);
      for (let top = 0; top < scrollHeight; top += step) {
        await page.evaluate((y) => window.scrollTo(0, y), top);
        await page.waitForTimeout(40);
      }
      await page.evaluate(() => window.scrollTo(0, 0));
      await page.waitForTimeout(100);

      const metrics = await page.evaluate(() => {
        const perf = performance.getEntriesByType('navigation')[0];
        const paintEntries = performance.getEntriesByType('paint');
        let fcp = 0;
        for (const p of paintEntries) {
          if (p.name === 'first-contentful-paint') fcp = p.startTime;
        }

        return {
          fcp: Math.round(fcp),
          domContentLoaded: Math.round(perf ? perf.domContentLoadedEventEnd : 0),
          cls: window.__cls || 0,
        };
      });

      // Capture screenshot
      const shotPath = path.join(outDir, `${route.slug}-${vp.name}.png`);
      await page.screenshot({ path: shotPath, fullPage: true });

      report[route.slug][vp.name] = {
        screenshot: shotPath,
        loadDurationMs: loadDuration,
        fcpMs: metrics.fcp,
        cls: Number(metrics.cls.toFixed(4)),
        requestCount: networkRequests.length,
        totalBytes,
        jsBytes,
        cssBytes,
        consoleErrorCount: consoleErrors.length,
      };

      await page.close();
    }
  }

  await browser.close();
  fs.writeFileSync(path.join(outDir, 'baseline-report.json'), JSON.stringify(report, null, 2));
  console.log('BASELINE_REPORT_COMPLETED');
  console.log(JSON.stringify(report, null, 2));
})();
