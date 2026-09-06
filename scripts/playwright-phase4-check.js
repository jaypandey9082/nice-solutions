async (page) => {
  const eventsUrl = "http://nice-solutions.local/events/";
  const landingUrl = "http://nice-solutions.local/";
  const widths = [320, 360, 390, 430, 768, 900, 1024, 1200, 1440];
  const viewportResults = [];
  const failedRequests = [];
  const consoleErrors = [];

  page.on("requestfailed", (request) => {
    failedRequests.push({ url: request.url(), error: request.failure()?.errorText });
  });
  page.on("console", (message) => {
    if (message.type() === "error") consoleErrors.push(message.text());
  });
  page.on("pageerror", (error) => consoleErrors.push(error.message));

  await page.addInitScript(() => {
    window.__niceCumulativeLayoutShift = 0;

    try {
      new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          if (!entry.hadRecentInput) window.__niceCumulativeLayoutShift += entry.value;
        }
      }).observe({ type: "layout-shift", buffered: true });
    } catch {
      // Intrinsic-dimension checks below still protect older browsers.
    }
  });

  const scrollThroughPage = async () => {
    const viewport = await page.viewportSize();
    const step = Math.max(300, Math.floor(viewport.height * 0.7));

    for (let top = 0; top < await page.evaluate(() => document.documentElement.scrollHeight); top += step) {
      await page.evaluate((scrollTop) => window.scrollTo(0, scrollTop), top);
      await page.waitForTimeout(80);
    }

    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(180);
  };

  for (const width of widths) {
    await page.setViewportSize({ width, height: width < 768 ? 844 : 900 });
    await page.goto(eventsUrl, { waitUntil: "networkidle" });
    const result = await page.evaluate(() => {
      const root = document.documentElement;
      const hero = document.querySelector(".nice-events-hero");
      const nav = document.querySelector(".nice-nav-shell");
      const title = document.querySelector(".nice-events-hero__title");
      const serviceRows = [...document.querySelectorAll(".nice-events-service")];
      const images = [...document.images];

      return {
        clientWidth: root.clientWidth,
        scrollWidth: root.scrollWidth,
        hasHorizontalOverflow: root.scrollWidth > root.clientWidth,
        heroHeight: Math.round(hero?.getBoundingClientRect().height ?? 0),
        navClearsHeroContent:
          (nav?.getBoundingClientRect().bottom ?? 0) <
          (title?.getBoundingClientRect().top ?? 0),
        serviceColumns: serviceRows.map((row) => getComputedStyle(row).gridTemplateColumns.split(" ").length),
        imagesHaveIntrinsicDimensions: images.every(
          (image) => image.hasAttribute("width") && image.hasAttribute("height"),
        ),
        imagesStayInBounds: images.every((image) => image.getBoundingClientRect().right <= root.clientWidth + 1),
        cumulativeLayoutShift: window.__niceCumulativeLayoutShift,
      };
    });
    viewportResults.push({ width, ...result });
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(eventsUrl, { waitUntil: "networkidle" });
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
  const condensedAtScroll = await page.locator("[data-nice-header]").evaluate((element) =>
    element.classList.contains("is-condensed"),
  );
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.waitForTimeout(350);
  const expandedAtTop = await page.locator("[data-nice-header]").evaluate((element) =>
    !element.classList.contains("is-condensed"),
  );

  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.reload({ waitUntil: "networkidle" });
  const reducedMotion = await page.evaluate(() => ({
    revealsRemainVisible: !document.documentElement.classList.contains("nice-has-reveal"),
    serviceTransition: getComputedStyle(document.querySelector(".nice-events-service__media img")).transitionDuration,
  }));
  await page.emulateMedia({ reducedMotion: "no-preference" });

  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto(eventsUrl, { waitUntil: "networkidle" });
  await scrollThroughPage();
  await page.waitForFunction(() =>
    [...document.querySelectorAll(".nice-events-services img, .nice-events-work img")].every(
      (image) => image.complete && image.naturalWidth > 0,
    ),
  );
  const contentChecks = await page.evaluate(() => {
    const heroImage = document.querySelector(".nice-events-hero__media");
    const belowFoldImages = [...document.querySelectorAll(".nice-events-services img, .nice-events-work img")];

    return {
      pageTitle: document.querySelector("h1")?.textContent.trim(),
      sectionHeadings: [...document.querySelectorAll("main h2")].map((heading) => heading.textContent.trim()),
      services: [...document.querySelectorAll(".nice-events-service h3")].map((heading) => heading.textContent.trim()),
      serviceRoutes: [...document.querySelectorAll(".nice-events-service__content a")].map((link) => link.pathname),
      sectionRoutes: [...document.querySelectorAll('[data-nice-future-route="true"]')].map((link) => link.pathname),
      eventsNavRoutes: [...document.querySelectorAll(".nice-events-hero__nav a")].map((link) => link.pathname),
      hasGlobalTeamRoute: [...document.querySelectorAll(".nice-site-header a, .nice-site-footer a")].some(
        (link) => link.pathname === "/team/",
      ),
      heroMedia: {
        priority: heroImage?.getAttribute("fetchpriority"),
        alt: heroImage?.getAttribute("alt"),
        loaded: heroImage?.complete && heroImage?.naturalWidth > 0,
      },
      belowFoldMedia: belowFoldImages.map((image) => ({
        loading: image.loading,
        loaded: image.complete && image.naturalWidth > 0,
        alt: image.alt.trim(),
      })),
      imageCount: document.images.length,
      loadedImageCount: [...document.images].filter((image) => image.complete && image.naturalWidth > 0).length,
      visibleRevealCount: document.querySelectorAll("[data-nice-reveal].is-visible").length,
      revealCount: document.querySelectorAll("[data-nice-reveal]").length,
      contactActions: [...document.querySelectorAll(".nice-events-contact [data-nice-contact-channel]")].map((link) => ({
        channel: link.dataset.niceContactChannel,
        placeholder: link.dataset.niceContactPlaceholder,
        hash: link.hash,
      })),
      directContactFormCount: document.querySelectorAll(".nice-events-contact form").length,
      proof: [...document.querySelectorAll(".nice-events-proof__grid article")].map((article) => article.textContent.replace(/\s+/g, " ").trim()),
    };
  });

  const freshMobilePage = await page.context().newPage();
  await freshMobilePage.setViewportSize({ width: 390, height: 844 });
  await freshMobilePage.goto(eventsUrl, { waitUntil: "networkidle" });
  contentChecks.mobileHeroSource = await freshMobilePage
    .locator(".nice-events-hero__media")
    .evaluate((image) => new URL(image.currentSrc).pathname);
  await freshMobilePage.close();

  const landingPage = await page.context().newPage();
  await landingPage.setViewportSize({ width: 1200, height: 900 });
  await landingPage.goto(landingUrl, { waitUntil: "networkidle" });
  contentChecks.landingEventsRoutes = await landingPage
    .locator('.nice-landing-hero__routes a, .nice-pathway')
    .evaluateAll((links) => links.filter((link) => link.textContent.includes("Events")).map((link) => link.pathname));
  await landingPage.close();

  await page.screenshot({ path: "output/playwright/nice-phase4-events-desktop.png", fullPage: true });
  await page.evaluate(() => window.scrollTo(0, 240));
  await page.waitForTimeout(350);
  await page.screenshot({ path: "output/playwright/nice-phase4-events-navbar-condensed.png" });

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(eventsUrl, { waitUntil: "networkidle" });
  await scrollThroughPage();
  await page.screenshot({ path: "output/playwright/nice-phase4-events-mobile.png", fullPage: true });
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.waitForTimeout(200);
  await page.locator("[data-nice-menu-open]").click();
  await page.waitForTimeout(260);
  await page.screenshot({ path: "output/playwright/nice-phase4-events-mobile-menu.png" });

  const expectedServices = "Corporate Events,Exhibitions & Conferences,Activations & Promotions";
  const expectedServiceRoutes = "/events/services/corporate-events/,/events/services/exhibitions-conferences/,/events/services/activations-promotions/";
  const expectedNavRoutes = "/events/services/,/events/case-studies/,/events/clients/,/events/team/,/events/contact/";
  const validationFailures = [];

  if (viewportResults.some((result) => result.hasHorizontalOverflow)) validationFailures.push("horizontal overflow");
  if (viewportResults.some((result) => !result.imagesHaveIntrinsicDimensions)) validationFailures.push("missing image dimensions");
  if (viewportResults.some((result) => !result.imagesStayInBounds)) validationFailures.push("image outside viewport");
  if (viewportResults.some((result) => !result.navClearsHeroContent)) validationFailures.push("navbar overlaps hero content");
  if (viewportResults.some((result) => result.cumulativeLayoutShift > 0.1)) validationFailures.push("layout shift");
  if (contentChecks.pageTitle !== "Events") validationFailures.push("page heading");
  if (contentChecks.services.join(",") !== expectedServices) validationFailures.push("service names");
  if (contentChecks.serviceRoutes.join(",") !== expectedServiceRoutes) validationFailures.push("service routes");
  if (contentChecks.eventsNavRoutes.join(",") !== expectedNavRoutes) validationFailures.push("Events navigation routes");
  if (contentChecks.hasGlobalTeamRoute) validationFailures.push("global team route");
  if (contentChecks.heroMedia.priority !== "high" || contentChecks.heroMedia.alt !== "" || !contentChecks.heroMedia.loaded) validationFailures.push("hero image");
  if (!contentChecks.mobileHeroSource.includes("voltas-crowd-480.webp")) validationFailures.push("oversized mobile hero source");
  if (contentChecks.belowFoldMedia.some((image) => image.loading !== "lazy" || !image.loaded || !image.alt)) validationFailures.push("below-fold image loading");
  if (contentChecks.loadedImageCount !== contentChecks.imageCount) validationFailures.push("broken image");
  if (contentChecks.visibleRevealCount !== contentChecks.revealCount) validationFailures.push("hidden revealed content");
  if (contentChecks.contactActions.some((action) => action.placeholder !== "true" || action.hash !== "#events-contact-details-pending")) validationFailures.push("contact placeholders");
  if (contentChecks.directContactFormCount !== 0) validationFailures.push("unexpected contact form");
  if (!contentChecks.proof.some((item) => item.includes("2,000+") && item.includes("Voltas"))) validationFailures.push("Voltas proof association");
  if (!contentChecks.proof.some((item) => item.includes("5,000+") && item.includes("RunForEquity"))) validationFailures.push("RunForEquity proof association");
  if (contentChecks.landingEventsRoutes.some((route) => route !== "/events/") || !contentChecks.landingEventsRoutes.length) validationFailures.push("landing Events route");
  if (!condensedAtScroll || !expandedAtTop) validationFailures.push("sticky navbar state");
  if (menuOpen.expanded !== "true" || menuOpen.state !== "open" || menuOpen.hidden !== "false") validationFailures.push("mobile menu open state");
  if (menuClosed.expanded !== "false" || menuClosed.hidden !== "true" || !menuClosed.focusRestored) validationFailures.push("mobile menu close state");
  if (!reducedMotion.revealsRemainVisible || Number.parseFloat(reducedMotion.serviceTransition) > 0.001) validationFailures.push("reduced motion");
  if (failedRequests.length) validationFailures.push("failed network request");
  if (consoleErrors.length) validationFailures.push("browser console error");

  if (validationFailures.length) {
    throw new Error(`Phase 4 validation failed: ${validationFailures.join(", ")}`);
  }

  return {
    viewportResults,
    menuOpen,
    focusWrapTarget,
    menuClosed,
    condensedAtScroll,
    expandedAtTop,
    reducedMotion,
    contentChecks,
    failedRequests,
    consoleErrors,
  };
}
