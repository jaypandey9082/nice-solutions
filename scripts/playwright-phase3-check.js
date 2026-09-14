async (page) => {
  const url = "http://nice-solutions.local/";
  const widths = [320, 360, 390, 430, 768, 900, 1024, 1200, 1440];
  const viewportResults = [];
  const failedRequests = [];
  const consoleErrors = [];
  /*
   * The theme opts into cross-document view transitions. Driving navigation as
   * fast as these checks do aborts a transition mid-flight, and the engine
   * reports that abort as a page error. It is an artefact of automated
   * navigation rather than a fault on the page, so it is not counted.
   */
  const niceIgnorableEngineError = (text) => /ViewTransition opt-in disabled|Transition was aborted because of invalid state/i.test(String(text));

  page.on("requestfailed", (request) => {
    failedRequests.push({ url: request.url(), error: request.failure()?.errorText });
  });
  page.on("console", (message) => {
    if (message.type() === "error") consoleErrors.push(message.text());
  });
  page.on("pageerror", (error) => { if (!niceIgnorableEngineError(error.message)) consoleErrors.push(error.message); });

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
      // The hero is now text-only and the imagery moved into the doors grid.
      const doors = document.querySelector(".nice-doors-grid");
      const nav = document.querySelector(".nice-nav-shell");
      const heroLead = document.querySelector(".nice-landing-hero__overline");
      const dimensions = {
        clientWidth: document.documentElement.clientWidth,
        scrollWidth: document.documentElement.scrollWidth,
      };

      return {
        ...dimensions,
        hasHorizontalOverflow: dimensions.scrollWidth > dimensions.clientWidth,
        heroHeight: Math.round(hero?.getBoundingClientRect().height ?? 0),
        doorsGridColumns: doors ? getComputedStyle(doors).gridTemplateColumns.split(" ").length : 0,
        doorSources: [...(doors?.querySelectorAll("img") ?? [])].map((image) => new URL(image.currentSrc).pathname),
        cumulativeLayoutShift: window.__niceCumulativeLayoutShift,
        imagesHaveIntrinsicDimensions: [...document.images].every(
          (image) => image.hasAttribute("width") && image.hasAttribute("height"),
        ),
        navClearsHeroContent:
          (nav?.getBoundingClientRect().bottom ?? 0) <
          (heroLead?.getBoundingClientRect().top ?? 0),
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
  /*
   * The drawer now carries approved contact details, so these are inspected
   * rather than clicked. Following a real wa.me or mailto link would navigate
   * away from the site and invalidate every later assertion in this run.
   */
  const drawerContact = await page.evaluate(() => ({
    menuState: document.querySelector("[data-nice-mobile-menu]")?.dataset.state,
    links: [...document.querySelectorAll(".nice-mobile-menu [data-nice-contact-channel]")].map((link) => ({
      channel: link.dataset.niceContactChannel,
      division: link.dataset.niceContactDivision ?? "",
      href: link.getAttribute("href"),
      label: link.textContent.trim(),
    })),
    divisionRoutes: [...document.querySelectorAll(".nice-mobile-menu__actions a[data-nice-contact-division]")]
      .filter((link) => !link.dataset.niceContactChannel)
      .map((link) => ({ path: link.pathname, label: link.textContent.trim() })),
    pendingNotice: Boolean(document.querySelector("#contact-details-pending")),
  }));
  await page.keyboard.press("Escape");

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
      (link) => link.pathname === "/team/" || link.pathname === "/about/",
    ),
    contactActions: [...document.querySelectorAll(".nice-contact-band__actions a")].map((link) => ({
      channel: link.dataset.niceContactChannel,
      placeholder: link.dataset.niceContactPlaceholder,
      hash: link.hash,
    })),
    doorMedia: [...document.querySelectorAll(".nice-door__media img")].map((image) => ({
      priority: image.getAttribute("fetchpriority"),
      sizes: image.getAttribute("sizes"),
      source: image.currentSrc,
      alt: (image.getAttribute("alt") ?? "").trim(),
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
  contentChecks.mobileDoorSources = await freshMobilePage
    .locator(".nice-door__media img")
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
  if (viewportResults.some((result) => result.doorsGridColumns !== (result.width < 1024 ? 1 : 2))) validationFailures.push("doors grid columns");
  if (contentChecks.mobileDoorSources.some((source) => !source.includes("-480.webp"))) validationFailures.push("oversized mobile door source");
  if (viewportResults.some((result) => result.cumulativeLayoutShift > 0.1)) validationFailures.push("layout shift");
  if (contentChecks.loadedImageCount !== contentChecks.imageCount) validationFailures.push("broken image");
  if (contentChecks.visibleRevealCount !== contentChecks.revealCount) validationFailures.push("hidden revealed content");
  if (contentChecks.heroTargets.join(",") !== "/events/,/studio/") validationFailures.push("hero routes");
  if (contentChecks.hasGlobalTeamRoute) validationFailures.push("global team route");
  // The hero is text-first, so there is no priority image to preload. The door
  // imagery is below the fold, lazy, and carries meaningful alt text.
  if (contentChecks.doorMedia.length !== 2) validationFailures.push("doors media count");
  if (contentChecks.doorMedia.some((image) => image.priority === "high")) validationFailures.push("below-fold image marked high priority");
  if (contentChecks.doorMedia.some((image) => !image.alt)) validationFailures.push("missing door alt text");
  if (contentChecks.pathwayMedia.some((image) => image.loading !== "lazy" || !image.loaded)) validationFailures.push("pathway loading");
  if (contentChecks.meaningfulImageAlts.some((alt) => !alt)) validationFailures.push("meaningful image alt text");
  // Contact details are approved and published, so nothing may still render as
  // a placeholder and the pending notice must be gone.
  if (contentChecks.contactActions.some((action) => action.placeholder === "true")) validationFailures.push("contact still placeholder");
  if (drawerContact.menuState !== "open") validationFailures.push("drawer did not open");
  if (drawerContact.pendingNotice) validationFailures.push("contact pending notice still rendered");
  // Neither division owns the landing page, so rather than four ambiguous
  // channel buttons the drawer offers one named route per division, each
  // leading to that division's contact page.
  if (drawerContact.divisionRoutes.length !== 2) validationFailures.push("landing drawer must offer both divisions");
  if (drawerContact.divisionRoutes.some((route) => !/^\/(events|studio)\/contact\/$/.test(route.path))) validationFailures.push("drawer division routes");
  if (drawerContact.divisionRoutes.some((route) => !route.label.includes("Events") && !route.label.includes("Studio"))) validationFailures.push("drawer routes must name their division");
  if (drawerContact.links.length !== 0) validationFailures.push("landing drawer must not expose raw channels");
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
    drawerContact,
    condensedAtScroll,
    expandedAtTop,
    reducedMotion,
    contentChecks,
    failedRequests,
    consoleErrors,
  };
}
