async (page) => {
  const origin = "http://nice-solutions.local";
  const studioUrl = `${origin}/studio/`;
  const widths = [320, 360, 390, 430, 768, 900, 1024, 1200, 1440];
  const expectedServices = ["Corporate Videos", "Digital Content Creation", "Films & Entertainment"];
  const expectedServiceSlugs = ["corporate-videos", "digital-content-creation", "films-entertainment"];
  const expectedProjects = [
    "strata-geosystems-factory-shoot",
    "career-agents-academy",
    "krish-e",
    "crisil-financial-literacy-content",
    "jayanti",
  ];
  const futureRoutes = [
    "/studio/services/",
    "/studio/services/corporate-videos/",
    "/studio/case-studies/",
    "/studio/case-studies/krish-e/",
    "/studio/clients/",
    "/studio/team/",
    "/studio/contact/",
  ];
  const viewportResults = [];
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
    for (let top = 0; top < await page.evaluate(() => document.documentElement.scrollHeight); top += step) {
      await page.evaluate((scrollTop) => window.scrollTo(0, scrollTop), top);
      await page.waitForTimeout(80);
    }
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(180);
  };

  for (const width of widths) {
    await page.setViewportSize({ width, height: width < 768 ? 844 : 900 });
    const response = await page.goto(studioUrl, { waitUntil: "networkidle" });
    const result = await page.evaluate(() => {
      const root = document.documentElement;
      const images = [...document.images];
      const headings = [...document.querySelectorAll("h1, h2, h3")];
      const heroImage = document.querySelector(".nice-studio-hero__media img");
      const headingLevels = headings.map((heading) => Number(heading.tagName.slice(1)));
      return {
        title: document.querySelector("main h1")?.textContent.trim() ?? "",
        h1Count: document.querySelectorAll("main h1").length,
        canonical: document.querySelector('link[rel="canonical"]')?.href ?? "",
        hasMain: Boolean(document.querySelector("main#main-content")),
        hasFooter: Boolean(document.querySelector(".nice-site-footer")),
        headingHierarchy: headingLevels.every((level, index) => index === 0 || level <= headingLevels[index - 1] + 1),
        unnamedLinkCount: [...document.links].filter((link) => !link.textContent.trim() && !link.getAttribute("aria-label")).length,
        activeStudioNav: document.querySelectorAll('.nice-studio-subnav [aria-current="page"]').length,
        hasHorizontalOverflow: root.scrollWidth > root.clientWidth,
        headingsFit: headings.every((heading) => heading.scrollWidth <= heading.clientWidth + 1),
        imagesHaveDimensions: images.every((image) => image.hasAttribute("width") && image.hasAttribute("height")),
        imagesStayInBounds: images.every((image) => {
          const box = image.getBoundingClientRect();
          return box.left >= -1 && box.right <= root.clientWidth + 1;
        }),
        heroPriority: heroImage?.getAttribute("fetchpriority") ?? "",
        heroAlt: heroImage?.getAttribute("alt") ?? null,
        serviceNames: [...document.querySelectorAll(".nice-studio-service h3")].map((item) => item.textContent.trim()),
        serviceSlugs: [...document.querySelectorAll("[data-nice-studio-service]")].map((item) => item.dataset.niceStudioService),
        projectSlugs: [...document.querySelectorAll("[data-nice-studio-project]")].map((item) => item.dataset.niceStudioProject),
        clientCount: document.querySelectorAll(".nice-studio-clients__list li").length,
        socialSectionCount: document.querySelectorAll(".nice-studio-social").length,
        contactActionCount: document.querySelectorAll(".nice-studio-contact__actions a").length,
        contactPending: Boolean(document.querySelector("[data-nice-studio-contact-pending]")),
        formCount: document.querySelectorAll(".nice-studio-page form").length,
        futureHrefCount: [...document.querySelectorAll('.nice-studio-page a[href]')].filter((link) =>
          /^\/studio\/(services|case-studies|clients|team|contact)\//.test(link.pathname),
        ).length,
        cumulativeLayoutShift: window.__niceCumulativeLayoutShift,
      };
    });
    viewportResults.push({ width, navigationStatus: response.status(), ...result });
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(studioUrl, { waitUntil: "networkidle" });
  const menuButton = page.locator("[data-nice-menu-open]");
  await menuButton.click();
  await page.waitForTimeout(260);
  const menuOpen = await page.evaluate(() => ({
    expanded: document.querySelector("[data-nice-menu-open]")?.getAttribute("aria-expanded"),
    state: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("data-state"),
    hidden: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("aria-hidden"),
    focusedControl: document.activeElement?.getAttribute("aria-label"),
  }));
  await page.locator("[data-nice-mobile-menu] a").first().focus();
  await page.keyboard.press("Shift+Tab");
  const focusWrapTarget = await page.evaluate(() => document.activeElement?.textContent?.trim());
  await page.keyboard.press("Escape");
  const menuClosed = await page.evaluate(() => ({
    expanded: document.querySelector("[data-nice-menu-open]")?.getAttribute("aria-expanded"),
    hidden: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("aria-hidden"),
    focusRestored: document.activeElement === document.querySelector("[data-nice-menu-open]"),
  }));

  await page.evaluate(() => window.scrollTo(0, 220));
  await page.waitForTimeout(350);
  const condensedAtScroll = await page.locator("[data-nice-header]").evaluate((element) => element.classList.contains("is-condensed"));
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.waitForTimeout(350);
  const expandedAtTop = await page.locator("[data-nice-header]").evaluate((element) => !element.classList.contains("is-condensed"));

  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.reload({ waitUntil: "networkidle" });
  const reducedMotion = await page.evaluate(() => ({
    revealsRemainVisible: !document.documentElement.classList.contains("nice-has-reveal"),
    mediaTransition: getComputedStyle(document.querySelector(".nice-studio-service__media img")).transitionDuration,
  }));
  await page.emulateMedia({ reducedMotion: "no-preference" });

  const landingPage = await page.context().newPage();
  await landingPage.setViewportSize({ width: 1200, height: 900 });
  await landingPage.goto(origin + "/", { waitUntil: "networkidle" });
  const landingStudioRoutes = await landingPage
    .locator('.nice-landing-hero__routes a, .nice-pathway, .nice-site-header a')
    .evaluateAll((links) => links.filter((link) => link.textContent.includes("Studio")).map((link) => link.pathname));
  await landingPage.close();

  const studioHomeRoutes = await page.locator('.nice-brand-link, .nice-studio-return').evaluateAll((links) => links.map((link) => link.pathname));

  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto(studioUrl, { waitUntil: "networkidle" });
  await scrollThroughPage();
  await page.waitForFunction(() => [...document.images].every((image) => image.complete && image.naturalWidth > 0));
  const loadedMedia = await page.evaluate(() => ({
    imageCount: document.images.length,
    loadedImageCount: [...document.images].filter((image) => image.complete && image.naturalWidth > 0).length,
    belowFoldLazy: [...document.querySelectorAll(".nice-studio-service img, .nice-studio-project img")].every((image) => image.loading === "lazy"),
    meaningfulAlts: [...document.querySelectorAll(".nice-studio-service img, .nice-studio-project img")].every((image) => image.alt.trim()),
    visibleRevealCount: document.querySelectorAll("[data-nice-reveal].is-visible").length,
    revealCount: document.querySelectorAll("[data-nice-reveal]").length,
  }));
  await page.screenshot({ path: "output/playwright/nice-phase7-studio-desktop.png", fullPage: true });

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(studioUrl, { waitUntil: "networkidle" });
  await scrollThroughPage();
  await page.screenshot({ path: "output/playwright/nice-phase7-studio-mobile.png", fullPage: true });
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.locator("[data-nice-menu-open]").click();
  await page.waitForTimeout(260);
  await page.screenshot({ path: "output/playwright/nice-phase7-studio-mobile-menu.png" });
  await page.keyboard.press("Escape");

  page.off("console", consoleHandler);
  page.off("pageerror", pageErrorHandler);
  const invalidRoutes = [];
  for (const path of futureRoutes) {
    const response = await page.request.get(origin + path, { maxRedirects: 0 });
    invalidRoutes.push({ path, status: response.status(), location: response.headers().location ?? "" });
  }

  const failures = [];
  if (viewportResults.some((result) => result.navigationStatus !== 200)) failures.push("Studio status");
  if (viewportResults.some((result) => result.title !== "Studio" || result.h1Count !== 1)) failures.push("logical H1");
  if (viewportResults.some((result) => result.canonical !== studioUrl)) failures.push("canonical URL");
  if (viewportResults.some((result) => !result.hasMain || !result.hasFooter || result.activeStudioNav !== 1 || !result.headingHierarchy || result.unnamedLinkCount)) failures.push("semantic landmarks");
  if (viewportResults.some((result) => result.hasHorizontalOverflow || !result.headingsFit)) failures.push("responsive overflow");
  if (viewportResults.some((result) => !result.imagesHaveDimensions || !result.imagesStayInBounds)) failures.push("responsive media");
  if (viewportResults.some((result) => result.heroPriority !== "high" || result.heroAlt !== "")) failures.push("hero media priority");
  if (viewportResults.some((result) => result.serviceNames.join(",") !== expectedServices.join(",") || result.serviceSlugs.join(",") !== expectedServiceSlugs.join(","))) failures.push("Studio Services");
  if (viewportResults.some((result) => result.projectSlugs.join(",") !== expectedProjects.join(","))) failures.push("Studio Case Studies");
  if (viewportResults.some((result) => result.clientCount !== 8)) failures.push("shared Clients");
  if (viewportResults.some((result) => result.socialSectionCount !== 0)) failures.push("empty social settings");
  if (viewportResults.some((result) => result.contactActionCount !== 0 || !result.contactPending || result.formCount !== 0)) failures.push("contact safety");
  if (viewportResults.some((result) => result.futureHrefCount !== 0)) failures.push("future route links");
  if (viewportResults.some((result) => result.cumulativeLayoutShift > 0.1)) failures.push("layout shift");
  if (menuOpen.expanded !== "true" || menuOpen.state !== "open" || menuOpen.hidden !== "false" || menuOpen.focusedControl !== "Close menu") failures.push("mobile menu open");
  if (menuClosed.expanded !== "false" || menuClosed.hidden !== "true" || !menuClosed.focusRestored) failures.push("mobile menu close");
  if (!condensedAtScroll || !expandedAtTop) failures.push("sticky navigation");
  if (!reducedMotion.revealsRemainVisible || Number.parseFloat(reducedMotion.mediaTransition) > 0.001) failures.push("reduced motion");
  if (!landingStudioRoutes.length || landingStudioRoutes.some((path) => path !== "/studio/")) failures.push("Landing to Studio");
  if (!studioHomeRoutes.includes("/")) failures.push("Studio to Landing");
  if (loadedMedia.imageCount !== loadedMedia.loadedImageCount || !loadedMedia.belowFoldLazy || !loadedMedia.meaningfulAlts) failures.push("image loading");
  if (loadedMedia.visibleRevealCount !== loadedMedia.revealCount) failures.push("revealed content");
  if (invalidRoutes.some((result) => result.status !== 404 || result.location)) failures.push("future route behavior");
  if (failedRequests.length) failures.push("failed network requests");
  if (consoleErrors.length) failures.push("console errors");

  if (failures.length) {
    throw new Error(`Phase 7 validation failed: ${failures.join(", ")}`);
  }

  return {
    checkedViewports: viewportResults.length,
    services: expectedServices,
    projects: expectedProjects,
    clients: 8,
    menuOpen,
    focusWrapTarget,
    menuClosed,
    condensedAtScroll,
    expandedAtTop,
    reducedMotion,
    loadedMedia,
    landingStudioRoutes,
    studioHomeRoutes,
    invalidRoutes,
    failedRequests,
    consoleErrors,
  };
}
