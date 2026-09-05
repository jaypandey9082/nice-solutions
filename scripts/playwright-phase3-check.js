async (page) => {
  const url = "http://nice-solutions.local/";
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
      // Older browsers still receive the intrinsic-dimension checks below.
    }
  });

  const scrollThroughPage = async () => {
    const height = await page.evaluate(() => document.documentElement.scrollHeight);
    const step = Math.max(300, Math.floor((await page.viewportSize()).height * 0.7));
    for (let y = 0; y < height; y += step) {
      await page.evaluate((top) => window.scrollTo(0, top), y);
      await page.waitForTimeout(90);
    }
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(180);
  };

  for (const width of widths) {
    await page.setViewportSize({ width, height: width < 768 ? 844 : 900 });
    await page.goto(url, { waitUntil: "networkidle" });
    const result = await page.evaluate(() => {
      const hero = document.querySelector(".nice-landing-hero");
      const mosaic = document.querySelector(".nice-landing-hero__mosaic");
      const nav = document.querySelector(".nice-nav-shell");
      const brand = document.querySelector(".nice-landing-hero__brand");
      const dimensions = {
        clientWidth: document.documentElement.clientWidth,
        scrollWidth: document.documentElement.scrollWidth,
      };

      return {
        ...dimensions,
        hasHorizontalOverflow: dimensions.scrollWidth > dimensions.clientWidth,
        heroHeight: Math.round(hero?.getBoundingClientRect().height ?? 0),
        heroMosaicColumns: getComputedStyle(mosaic).gridTemplateColumns.split(" ").length,
        heroSources: [...mosaic.querySelectorAll("img")].map((image) => new URL(image.currentSrc).pathname),
        cumulativeLayoutShift: window.__niceCumulativeLayoutShift,
        imagesHaveIntrinsicDimensions: [...document.images].every(
          (image) => image.hasAttribute("width") && image.hasAttribute("height"),
        ),
        navClearsHeroContent:
          (nav?.getBoundingClientRect().bottom ?? 0) <
          (brand?.getBoundingClientRect().top ?? 0),
      };
    });
    viewportResults.push({ width, ...result });
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: "networkidle" });
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

  await menuButton.click();
  await page.locator('.nice-mobile-menu [data-nice-contact-channel="whatsapp"]').click();
  const placeholderClick = await page.evaluate(() => ({
    hash: window.location.hash,
    menuState: document.querySelector("[data-nice-mobile-menu]")?.dataset.state,
    statusVisible: Boolean(document.querySelector("#contact-details-pending")),
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
  const reducedMotion = await page.locator(".nice-pathway").first().evaluate((element) => ({
    duration: getComputedStyle(element).transitionDuration,
    revealsRemainVisible: !document.documentElement.classList.contains("nice-has-reveal"),
  }));
  await page.emulateMedia({ reducedMotion: "no-preference" });

  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto(url, { waitUntil: "networkidle" });
  await scrollThroughPage();
  const contentChecks = await page.evaluate(() => ({
    logo: {
      src: document.querySelector(".nice-logo--hero")?.getAttribute("src"),
      naturalWidth: document.querySelector(".nice-logo--hero")?.naturalWidth,
      naturalHeight: document.querySelector(".nice-logo--hero")?.naturalHeight,
    },
    pathwayTargets: [...document.querySelectorAll(".nice-pathway")].map((link) => link.pathname),
    heroTargets: [...document.querySelectorAll(".nice-landing-hero__routes a")].map((link) => link.pathname),
    headerLabels: [...document.querySelectorAll(".nice-desktop-nav a")].map((link) => link.textContent.trim()),
    mobileLabels: [...document.querySelectorAll(".nice-mobile-menu__links a")].map((link) => link.textContent.trim()),
    hasGlobalTeamRoute: [...document.querySelectorAll(".nice-site-header a, .nice-site-footer a")].some(
      (link) => link.pathname === "/team/",
    ),
    contactActions: [...document.querySelectorAll(".nice-contact-band__actions a")].map((link) => ({
      channel: link.dataset.niceContactChannel,
      placeholder: link.dataset.niceContactPlaceholder,
      hash: link.hash,
    })),
    heroMedia: [...document.querySelectorAll(".nice-landing-hero__mosaic img")].map((image) => ({
      priority: image.getAttribute("fetchpriority"),
      sizes: image.getAttribute("sizes"),
      source: image.currentSrc,
      alt: image.getAttribute("alt"),
    })),
    pathwayMedia: [...document.querySelectorAll(".nice-pathway img")].map((image) => ({
      loading: image.loading,
      sizes: image.sizes,
      loaded: image.complete && image.naturalWidth > 0,
    })),
    meaningfulImageAlts: [...document.querySelectorAll(".nice-pathway img, .nice-landing-project img")].map(
      (image) => image.alt.trim(),
    ),
    imageCount: document.images.length,
    loadedImageCount: [...document.images].filter((image) => image.complete && image.naturalWidth > 0).length,
    visibleRevealCount: document.querySelectorAll("[data-nice-reveal].is-visible").length,
    revealCount: document.querySelectorAll("[data-nice-reveal]").length,
  }));

  const freshMobilePage = await page.context().newPage();
  await freshMobilePage.setViewportSize({ width: 390, height: 844 });
  await freshMobilePage.goto(url, { waitUntil: "networkidle" });
  contentChecks.mobileHeroSources = await freshMobilePage
    .locator(".nice-landing-hero__mosaic img")
    .evaluateAll((images) => images.map((image) => new URL(image.currentSrc).pathname));
  await freshMobilePage.close();

  await page.screenshot({ path: "output/playwright/nice-phase3-landing-desktop.png", fullPage: true });

  await page.evaluate(() => window.scrollTo(0, 240));
  await page.waitForTimeout(350);
  await page.screenshot({ path: "output/playwright/nice-phase3-navbar-condensed.png" });

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: "networkidle" });
  await scrollThroughPage();
  await page.screenshot({ path: "output/playwright/nice-phase3-landing-mobile.png", fullPage: true });
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.waitForTimeout(200);
  await page.locator("[data-nice-menu-open]").click();
  await page.waitForTimeout(260);
  await page.screenshot({ path: "output/playwright/nice-phase3-mobile-menu.png" });

  const validationFailures = [];

  if (viewportResults.some((result) => result.hasHorizontalOverflow)) validationFailures.push("horizontal overflow");
  if (viewportResults.some((result) => !result.imagesHaveIntrinsicDimensions)) validationFailures.push("missing image dimensions");
  if (viewportResults.some((result) => !result.navClearsHeroContent)) validationFailures.push("navbar overlaps hero content");
  if (viewportResults.some((result) => result.heroMosaicColumns !== (result.width < 768 ? 2 : 4))) validationFailures.push("hero mosaic columns");
  if (contentChecks.mobileHeroSources.some((source) => !source.includes("-480.webp"))) validationFailures.push("oversized mobile hero source");
  if (viewportResults.some((result) => result.cumulativeLayoutShift > 0.1)) validationFailures.push("layout shift");
  if (contentChecks.loadedImageCount !== contentChecks.imageCount) validationFailures.push("broken image");
  if (contentChecks.visibleRevealCount !== contentChecks.revealCount) validationFailures.push("hidden revealed content");
  if (contentChecks.heroTargets.join(",") !== "/events/,/studio/") validationFailures.push("hero routes");
  if (contentChecks.hasGlobalTeamRoute) validationFailures.push("global team route");
  if (contentChecks.heroMedia.filter((image) => image.priority === "high").length !== 1) validationFailures.push("hero priority");
  if (contentChecks.heroMedia.some((image) => image.alt !== "")) validationFailures.push("decorative hero alt text");
  if (contentChecks.pathwayMedia.some((image) => image.loading !== "lazy" || !image.loaded)) validationFailures.push("pathway loading");
  if (contentChecks.meaningfulImageAlts.some((alt) => !alt)) validationFailures.push("meaningful image alt text");
  if (contentChecks.contactActions.some((action) => action.placeholder !== "true" || action.hash !== "#contact-details-pending")) validationFailures.push("contact placeholders");
  if (placeholderClick.hash !== "#contact-details-pending" || placeholderClick.menuState !== "closed" || !placeholderClick.statusVisible) validationFailures.push("contact placeholder interaction");
  if (!condensedAtScroll || !expandedAtTop) validationFailures.push("sticky navbar state");
  if (menuOpen.expanded !== "true" || menuClosed.expanded !== "false" || !menuClosed.focusRestored) validationFailures.push("mobile menu accessibility");
  if (!reducedMotion.revealsRemainVisible) validationFailures.push("reduced motion visibility");
  if (failedRequests.length) validationFailures.push("failed network request");
  if (consoleErrors.length) validationFailures.push("browser console error");

  if (validationFailures.length) {
    throw new Error(`Phase 3.1 validation failed: ${validationFailures.join(", ")}`);
  }

  return {
    viewportResults,
    menuOpen,
    focusWrapTarget,
    menuClosed,
    placeholderClick,
    condensedAtScroll,
    expandedAtTop,
    reducedMotion,
    contentChecks,
    failedRequests,
    consoleErrors,
  };
}
