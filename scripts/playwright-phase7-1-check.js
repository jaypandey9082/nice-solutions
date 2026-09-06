async (page) => {
  const origin = "http://nice-solutions.local";
  const widths = [390, 1440];
  const pagesToTest = [
    { name: "landing", path: "/" },
    { name: "events", path: "/events/" },
    { name: "events-case-studies", path: "/events/case-studies/" },
    { name: "events-case-study-detail", path: "/events/case-studies/voltas-fam-tastic-fiesta/" },
    { name: "studio", path: "/studio/" },
  ];

  const futureStudioRoutes = [
    "/studio/services/",
    "/studio/services/corporate-videos/",
    "/studio/case-studies/",
    "/studio/case-studies/krish-e/",
  ];

  const failedRequests = [];
  const consoleErrors = [];

  page.on("requestfailed", (request) => failedRequests.push({ url: request.url(), error: request.failure()?.errorText }));
  const consoleHandler = (message) => {
    if (message.type() === "error") consoleErrors.push(message.text());
  };
  const pageErrorHandler = (error) => consoleErrors.push(error.message);
  page.on("console", consoleHandler);
  page.on("pageerror", pageErrorHandler);

  await page.addInitScript(() => {
    window.__niceCumulativeLayoutShift = 0;
    try {
      new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          if (!entry.hadRecentInput) window.__niceCumulativeLayoutShift += entry.value;
        }
      }).observe({ type: "layout-shift", buffered: true });
    } catch {
      // Intrinsic dimensions and overflow checks remain active in older browsers.
    }
  });

  const scrollThroughPage = async () => {
    const viewport = await page.viewportSize();
    const step = Math.max(320, Math.floor(viewport.height * 0.72));
    const maxScroll = await page.evaluate(() => document.documentElement.scrollHeight);
    for (let top = 0; top < maxScroll; top += step) {
      await page.evaluate((scrollTop) => window.scrollTo(0, scrollTop), top);
      await page.waitForTimeout(60);
    }
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(120);
  };

  const results = {};

  for (const pageInfo of pagesToTest) {
    results[pageInfo.name] = {};

    for (const width of widths) {
      await page.setViewportSize({ width, height: width < 768 ? 844 : 900 });
      const response = await page.goto(origin + pageInfo.path, { waitUntil: "networkidle" });
      await scrollThroughPage();

      const evaluation = await page.evaluate((pName) => {
        const root = document.documentElement;
        const header = document.querySelector("[data-nice-header]");
        const workLink = header ? header.querySelector('.nice-desktop-nav a[href*="work"], .nice-desktop-nav a[href*="case-studies"]') : null;
        const servicesLink = header ? header.querySelector('.nice-desktop-nav a[href*="services"], .nice-desktop-nav a[href*="capabilities"]') : null;
        const contactLink = header ? header.querySelector('.nice-nav-cta, a[href*="contact"]') : null;

        const editorialElements = document.querySelectorAll(".nice-editorial, [data-nice-editorial-reveal]");
        const editorialDisplayElements = document.querySelectorAll(".nice-editorial-display");
        const featuredPreviews = document.querySelectorAll(".nice-events-case-preview--featured");
        const navCards = document.querySelectorAll(".nice-events-nav-card");
        const infobar = document.querySelector(".nice-events-case-infobar");
        const studioHeroH1 = document.querySelector(".nice-studio-hero h1");
        const studioHeroStatement = document.querySelector(".nice-studio-hero__statement > p");
        const studioStory = document.querySelector(".nice-studio-story");
        const innerCtaHeading = document.querySelector(".nice-events-inner-cta h2, .nice-studio-contact h2");

        return {
          status: 200,
          division: header?.getAttribute("data-nice-division") || "global",
          workHref: workLink?.getAttribute("href") || "",
          servicesHref: servicesLink?.getAttribute("href") || "",
          contactHref: contactLink?.getAttribute("href") || "",
          editorialCount: editorialElements.length,
          hasEditorialDisplay: editorialDisplayElements.length > 0 || editorialElements.length > 0,
          featuredPreviewCount: featuredPreviews.length,
          navCardCount: navCards.length,
          hasInfobar: Boolean(infobar),
          studioHeroHasEditorial: studioHeroH1?.classList.contains("nice-editorial") || false,
          studioStatementHasEditorial: studioHeroStatement?.classList.contains("nice-editorial") || false,
          innerCtaHasEditorial: innerCtaHeading?.classList.contains("nice-editorial") || false,
          hasHorizontalOverflow: root.scrollWidth > root.clientWidth,
          cls: window.__niceCumulativeLayoutShift || 0,
        };
      }, pageInfo.name);

      evaluation.navigationStatus = response.status();
      results[pageInfo.name][width] = evaluation;

      await page.screenshot({
        path: `output/playwright/nice-phase7-1-${pageInfo.name}-${width}.png`,
        fullPage: true,
      });
    }
  }

  // Check future Studio routes are 404
  const invalidRoutes = [];
  for (const path of futureStudioRoutes) {
    const response = await page.request.get(origin + path, { maxRedirects: 0 });
    invalidRoutes.push({ path, status: response.status() });
  }

  page.off("console", consoleHandler);
  page.off("pageerror", pageErrorHandler);

  const failures = [];

  // 1. Landing navigation
  const landing1440 = results.landing[1440];
  if (!landing1440.workHref.includes("/#work")) failures.push("Landing Work link should point to /#work");
  if (!landing1440.servicesHref.includes("/#capabilities")) failures.push("Landing Services link should point to /#capabilities");

  // 2. Events navigation
  const events1440 = results.events[1440];
  if (!events1440.workHref.includes("/events/case-studies/")) failures.push("Events Work link should point to /events/case-studies/");
  if (!events1440.servicesHref.includes("/events/services/")) failures.push("Events Services link should point to /events/services/");
  if (events1440.division !== "events") failures.push("Events header division attribute should be 'events'");

  // 3. Studio navigation
  const studio1440 = results.studio[1440];
  if (!studio1440.workHref.includes("/studio/#studio-work")) failures.push("Studio Work link should point to /studio/#studio-work");
  if (!studio1440.servicesHref.includes("/studio/#studio-services")) failures.push("Studio Services link should point to /studio/#studio-services");
  if (studio1440.division !== "studio") failures.push("Studio header division attribute should be 'studio'");

  // 4. Studio hero editorial treatment
  if (!studio1440.studioHeroHasEditorial) failures.push("Studio hero H1 must use .nice-editorial");
  if (!studio1440.studioStatementHasEditorial) failures.push("Studio hero statement must use .nice-editorial");

  // 5. Case studies hierarchy
  const caseStudies1440 = results["events-case-studies"][1440];
  if (caseStudies1440.featuredPreviewCount === 0) failures.push("Case studies index must have featured previews");
  if (!caseStudies1440.innerCtaHasEditorial) failures.push("Case studies inner CTA must have .nice-editorial");

  // 6. Case study detail infobar & nav cards
  const detail1440 = results["events-case-study-detail"][1440];
  if (!detail1440.hasInfobar) failures.push("Case study detail must have .nice-events-case-infobar");
  if (detail1440.navCardCount === 0) failures.push("Case study detail must have .nice-events-nav-card");

  // 7. Future Studio routes must 404
  for (const route of invalidRoutes) {
    if (route.status !== 404) failures.push(`Future route ${route.path} must 404 but got ${route.status}`);
  }

  // 8. Layout shift & overflow
  for (const [pName, pData] of Object.entries(results)) {
    for (const [w, evalData] of Object.entries(pData)) {
      if (evalData.hasHorizontalOverflow) failures.push(`Horizontal overflow on ${pName} at ${w}px`);
      if (evalData.cls > 0.05) failures.push(`CLS ${evalData.cls} on ${pName} at ${w}px exceeds 0.05`);
    }
  }

  if (failures.length) {
    throw new Error(`Phase 7.1 validation failed: ${failures.join(", ")}`);
  }

  return {
    results,
    invalidRoutes,
    failedRequests,
    consoleErrors,
    screenshots: [
      "output/playwright/nice-phase7-1-landing-390.png",
      "output/playwright/nice-phase7-1-landing-1440.png",
      "output/playwright/nice-phase7-1-events-390.png",
      "output/playwright/nice-phase7-1-events-1440.png",
      "output/playwright/nice-phase7-1-events-case-studies-390.png",
      "output/playwright/nice-phase7-1-events-case-studies-1440.png",
      "output/playwright/nice-phase7-1-events-case-study-detail-390.png",
      "output/playwright/nice-phase7-1-events-case-study-detail-1440.png",
      "output/playwright/nice-phase7-1-studio-390.png",
      "output/playwright/nice-phase7-1-studio-1440.png",
    ],
  };
};
