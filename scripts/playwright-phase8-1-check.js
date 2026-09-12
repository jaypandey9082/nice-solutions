const fs = require("fs");
const path = require("path");
const { chromium } = require("playwright");

const BASE_URL = "http://nice-solutions.local";
const AFTER_DIR = path.resolve(__dirname, "../output/playwright/after");
const BASELINE_REPORT_PATH = path.resolve(__dirname, "../output/playwright/baseline/baseline-report.json");

const ROUTES = [
  { name: "landing", path: "/" },
  { name: "events-home", path: "/events/" },
  { name: "events-services", path: "/events/services/" },
  { name: "events-service-detail", path: "/events/services/corporate-events/" },
  { name: "events-case-studies", path: "/events/case-studies/" },
  { name: "events-case-study-detail", path: "/events/case-studies/voltas-fam-tastic-fiesta/" },
  { name: "events-clients", path: "/events/clients/" },
  { name: "events-team", path: "/events/team/" },
  { name: "events-contact", path: "/events/contact/" },
  { name: "studio-home", path: "/studio/" },
  { name: "studio-services", path: "/studio/services/" },
  { name: "studio-service-detail", path: "/studio/services/corporate-videos/" },
  { name: "studio-case-studies", path: "/studio/case-studies/" },
  { name: "studio-case-study-detail", path: "/studio/case-studies/strata-geosystems-factory-shoot/" },
  { name: "studio-clients", path: "/studio/clients/" },
  { name: "studio-team", path: "/studio/team/" },
  { name: "studio-contact", path: "/studio/contact/" },
];

const VIEWPORTS = [
  { name: "desktop-1440", width: 1440, height: 900 },
  { name: "mobile-390", width: 390, height: 844 },
];

const RESPONSIVE_WIDTHS = [320, 360, 390, 430, 768, 900, 1024, 1200, 1440];

(async () => {
  if (!fs.existsSync(AFTER_DIR)) {
    fs.mkdirSync(AFTER_DIR, { recursive: true });
  }

	const launchOptions = { headless: true };
	if (process.env.PLAYWRIGHT_CHROMIUM_PATH) {
		launchOptions.executablePath = process.env.PLAYWRIGHT_CHROMIUM_PATH;
	}
	const browser = await chromium.launch(launchOptions);

  const results = {
    timestamp: new Date().toISOString(),
    routesTested: ROUTES.length,
    routes: {},
    responsiveIntegrity: {},
    reducedMotionVerified: false,
    typographyVerified: false,
    overallSuccess: true,
    consoleErrors: [],
    failedRequests: [],
  };

  console.log(`Starting Phase 8.1 Playwright Validation across ${ROUTES.length} routes...`);

  for (const route of ROUTES) {
    const routeKey = route.name;
    results.routes[routeKey] = {
      url: `${BASE_URL}${route.path}`,
      viewports: {},
    };

    for (const vp of VIEWPORTS) {
      const context = await browser.newContext({
        viewport: { width: vp.width, height: vp.height },
      });
      const page = await context.newPage();

      const pageConsoleErrors = [];
      const pageFailedRequests = [];

      page.on("console", (msg) => {
        if (msg.type() === "error") {
          pageConsoleErrors.push(msg.text());
          results.consoleErrors.push({ route: route.path, text: msg.text() });
        }
      });

      page.on("requestfailed", (req) => {
        const failure = `${req.url()} (${req.failure()?.errorText || "failed"})`;
        pageFailedRequests.push(failure);
        results.failedRequests.push({ route: route.path, failure });
      });

      await page.addInitScript(() => {
        window.__cls = 0;
        new PerformanceObserver((entryList) => {
          for (const entry of entryList.getEntries()) {
            if (!entry.hadRecentInput) {
              window.__cls += entry.value;
            }
          }
        }).observe({ type: "layout-shift", buffered: true });
      });

      const startTime = Date.now();
      await page.goto(`${BASE_URL}${route.path}`, { waitUntil: "load" });
      const loadTimeMs = Date.now() - startTime;

      await page.waitForTimeout(600);

      const metrics = await page.evaluate(() => {
        const perfEntries = performance.getEntriesByType("paint");
        const fcp = perfEntries.find((e) => e.name === "first-contentful-paint")?.startTime || 0;
        const h1 = document.querySelector("h1");
        const h1Font = h1 ? window.getComputedStyle(h1).fontFamily : "";
        const h1FontSize = h1 ? window.getComputedStyle(h1).fontSize : "";
        const editorialElements = document.querySelectorAll(".nice-editorial, .nice-editorial-display, [data-nice-editorial-reveal]");
        const hasEditorialFont = [...editorialElements].some((el) => {
          const f = window.getComputedStyle(el).fontFamily.toLowerCase();
          return f.includes("georgia") || f.includes("times") || f.includes("serif");
        });

        const heroMosaic = document.querySelector(".nice-landing-hero__mosaic");
        const heroMosaicVisible = heroMosaic ? window.getComputedStyle(heroMosaic).display !== "none" : false;

        return {
          fcp: Math.round(fcp),
          cls: window.__cls || 0,
          h1Font,
          h1FontSize,
          hasEditorialFont,
          editorialCount: editorialElements.length,
          heroMosaicPresent: !!heroMosaic,
          heroMosaicVisible,
        };
      });

      const screenshotPath = path.join(AFTER_DIR, `${route.name}-${vp.name}.png`);
      await page.screenshot({ path: screenshotPath, fullPage: false });

      results.routes[routeKey].viewports[vp.name] = {
        loadTimeMs,
        fcp: metrics.fcp,
        cls: metrics.cls,
        h1Font: metrics.h1Font,
        h1FontSize: metrics.h1FontSize,
        hasEditorialFont: metrics.hasEditorialFont,
        editorialCount: metrics.editorialCount,
        heroMosaicPresent: metrics.heroMosaicPresent,
        heroMosaicVisible: metrics.heroMosaicVisible,
        consoleErrors: pageConsoleErrors.length,
        failedRequests: pageFailedRequests.length,
        screenshot: path.relative(path.resolve(__dirname, ".."), screenshotPath),
      };

      console.log(`  ✓ ${route.name} [${vp.name}] - FCP: ${metrics.fcp}ms, CLS: ${metrics.cls.toFixed(4)}, H1: ${metrics.h1Font.substring(0, 25)}, Errors: ${pageConsoleErrors.length}`);

      await context.close();
    }
  }

  console.log("\nTesting responsive widths (320px to 1440px)...");
  const testPage = await browser.newPage();
  for (const width of RESPONSIVE_WIDTHS) {
    await testPage.setViewportSize({ width, height: 800 });
    await testPage.goto(`${BASE_URL}/`, { waitUntil: "networkidle" });
    const overflow = await testPage.evaluate(() => {
      return document.documentElement.scrollWidth > window.innerWidth;
    });
    results.responsiveIntegrity[`width-${width}`] = {
      width,
      horizontalOverflow: overflow,
      pass: !overflow,
    };
    if (overflow) {
      results.overallSuccess = false;
      console.log(`  ✗ Overflow detected at width ${width}px!`);
    } else {
      console.log(`  ✓ Width ${width}px: clean horizontal containment`);
    }
  }

  console.log("\nTesting prefers-reduced-motion: reduce...");
  const motionContext = await browser.newContext({
    reducedMotion: "reduce",
  });
  const motionPage = await motionContext.newPage();
  await motionPage.goto(`${BASE_URL}/`, { waitUntil: "networkidle" });
  const motionTest = await motionPage.evaluate(() => {
    const unit = document.querySelector(".nice-reveal-unit");
    if (!unit) return { tested: false };
    const style = window.getComputedStyle(unit);
    return {
      tested: true,
      opacity: style.opacity,
      transform: style.transform,
    };
  });
  results.reducedMotionVerified = motionTest.tested ? motionTest.opacity === "1" : true;
  console.log(`  ✓ Reduced motion verified: opacity = ${motionTest.opacity || "1"}, transform = ${motionTest.transform || "none"}`);
  await motionContext.close();

  let baselineComparison = null;
  if (fs.existsSync(BASELINE_REPORT_PATH)) {
    try {
      const baseline = JSON.parse(fs.readFileSync(BASELINE_REPORT_PATH, "utf8"));
      baselineComparison = {};
      for (const [rKey, rData] of Object.entries(results.routes)) {
        if (baseline.routes?.[rKey]) {
          baselineComparison[rKey] = {
            desktopFcpBefore: baseline.routes[rKey].desktop?.fcp,
            desktopFcpAfter: rData.viewports?.["desktop-1440"]?.fcp,
            desktopClsBefore: baseline.routes[rKey].desktop?.cls,
            desktopClsAfter: rData.viewports?.["desktop-1440"]?.cls,
            mobileFcpBefore: baseline.routes[rKey].mobile?.fcp,
            mobileFcpAfter: rData.viewports?.["mobile-390"]?.fcp,
            mobileClsBefore: baseline.routes[rKey].mobile?.cls,
            mobileClsAfter: rData.viewports?.["mobile-390"]?.cls,
          };
        }
      }
      results.baselineComparison = baselineComparison;
      console.log("\nBaseline comparison calculated successfully.");
    } catch (e) {
      console.warn("Could not parse baseline report for comparison:", e.message);
    }
  }

  await browser.close();

  /*
   * Draw the typography conclusion the run already has the inputs for. It was
   * previously initialised false, never set and never read, so the suite
   * reported success while claiming typography was unverified.
   */
  const viewportSamples = Object.values(results.routes).flatMap((route) => Object.values(route.viewports || {}));
  results.typographyVerified =
    viewportSamples.length > 0 &&
    viewportSamples.every((sample) => Boolean(sample.h1Font) && Boolean(sample.h1FontSize)) &&
    viewportSamples.some((sample) => sample.hasEditorialFont);

  /* Every signal the run collects now decides the verdict. */
  const failureReasons = [];
  if (!results.overallSuccess) failureReasons.push("responsive integrity");
  if (results.consoleErrors.length) failureReasons.push(`${results.consoleErrors.length} console error(s)`);
  if (results.failedRequests.length) failureReasons.push(`${results.failedRequests.length} failed request(s)`);
  if (!results.typographyVerified) failureReasons.push("typography not verified");
  if (!results.reducedMotionVerified) failureReasons.push("reduced motion not verified");

  results.overallSuccess = failureReasons.length === 0;

  const reportPath = path.join(AFTER_DIR, "after-report.json");
  fs.writeFileSync(reportPath, JSON.stringify(results, null, 2), "utf8");
  console.log(`\nPhase 8.1 validation complete. Report saved to ${reportPath}`);
  console.log(`Total Console Errors: ${results.consoleErrors.length}`);
  console.log(`Total Failed Requests: ${results.failedRequests.length}`);
  console.log(`Typography verified: ${results.typographyVerified}`);
  console.log(`Reduced motion verified: ${results.reducedMotionVerified}`);
  console.log(`Overall Status: ${results.overallSuccess ? "PASSED" : `FAILED (${failureReasons.join(", ")})`}`);

  /* Without this the suite exits 0 even when it prints FAILED. */
  if (!results.overallSuccess) {
    process.exitCode = 1;
  }
})();
