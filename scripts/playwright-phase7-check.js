async (page) => {
  const origin = "http://nice-solutions.local";
  const studioUrl = `${origin}/studio/`;
  const widths = [320, 360, 390, 430, 768, 900, 1024, 1200, 1440];
  const expectedServices = ["Corporate Videos", "Digital Content Creation", "Films & Entertainment"];
  const expectedServiceSlugs = ["corporate-videos", "digital-content-creation", "films-entertainment"];
  const expectedServicePaths = expectedServiceSlugs.map((slug) => `/studio/services/${slug}/`);
  const expectedProjects = [
    "strata-geosystems-factory-shoot",
    "career-agents-academy",
    "krish-e",
  ];
  const expectedProjectPaths = expectedProjects.map((slug) => `/studio/case-studies/${slug}/`);
  // Services now precedes Work in the consolidated division nav.
  const expectedMenuPaths = [
    "/",
    "/studio/",
    "/studio/services/",
    "/studio/case-studies/",
    "/studio/clients/",
    "/studio/contact/",
    "/events/",
  ];
  const expectedRoutes = [
    "/studio/",
    "/studio/services/",
    ...expectedServicePaths,
    "/studio/case-studies/",
    ...expectedProjectPaths,
    "/studio/clients/",
    "/studio/team/",
    "/studio/contact/",
  ];
  const retiredDeckImagePattern = /\/(?:strata-production(?:-480)?|studio-(?:career-agents|crisil-literacy|jayanti|krish-e))\.webp(?:\?|$)/i;
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
  const studioHomeRequestedUrls = [];

  page.on("request", (request) => {
	const framePath = request.frame().url().replace(origin, "").split(/[?#]/)[0];
	const requestPath = request.url().replace(origin, "").split(/[?#]/)[0];
    if (framePath === "/studio/" || requestPath === "/studio/") studioHomeRequestedUrls.push(request.url());
  });
  page.on("requestfailed", (request) => failedRequests.push({ url: request.url(), error: request.failure()?.errorText }));
  const consoleHandler = (message) => {
    if (message.type() === "error") consoleErrors.push(message.text());
  };
  const pageErrorHandler = (error) => { if (!niceIgnorableEngineError(error.message)) consoleErrors.push(error.message); };
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
      const projectRows = [...document.querySelectorAll("[data-nice-studio-project]")];
      const serviceRows = [...document.querySelectorAll("[data-nice-studio-service]")];
      const headingLevels = headings.map((heading) => Number(heading.tagName.slice(1)));
      return {
        title: document.querySelector("main h1")?.textContent.trim() ?? "",
        h1Count: document.querySelectorAll("main h1").length,
        canonical: document.querySelector('link[rel="canonical"]')?.href ?? "",
        hasMain: Boolean(document.querySelector("main#main-content")),
        hasFooter: Boolean(document.querySelector(".nice-site-footer")),
        headingHierarchy: headingLevels.every((level, index) => index === 0 || level <= headingLevels[index - 1] + 1),
        unnamedLinkCount: [...document.links].filter((link) => !link.textContent.trim() && !link.getAttribute("aria-label")).length,
        removedChromeCount: document.querySelectorAll(".nice-studio-subnav-shell, .nice-studio-subnav, .nice-studio-return").length,
        hasHorizontalOverflow: root.scrollWidth > root.clientWidth,
        headingsFit: headings.every((heading) => heading.scrollWidth <= heading.clientWidth + 1),
        imagesHaveDimensions: images.every((image) => image.hasAttribute("width") && image.hasAttribute("height")),
        imagesHaveAlt: images.every((image) => image.hasAttribute("alt")),
        imagesStayInBounds: images.every((image) => {
          const box = image.getBoundingClientRect();
          return box.left >= -1 && box.right <= root.clientWidth + 1;
        }),
        heroStateValid: heroImage
          ? heroImage.getAttribute("fetchpriority") === "high"
          : Boolean(document.querySelector(".nice-studio-hero__media--empty, [data-nice-studio-hero-empty]")),
        serviceNames: serviceRows.map((item) => item.querySelector("h3")?.textContent.trim() ?? ""),
        serviceSlugs: serviceRows.map((item) => item.dataset.niceStudioService),
        servicePaths: serviceRows.map((item) => item.querySelector("a[href]")?.pathname ?? ""),
        projectSlugs: projectRows.map((item) => item.dataset.niceStudioProject),
        projectPaths: projectRows.map((item) => item.querySelector("a[href]")?.pathname ?? ""),
        projectPlaceholderCount: projectRows.filter((item) =>
          item.querySelector(".nice-studio-project__media--empty, [data-nice-project-media-placeholder]"),
        ).length,
        projectImageCount: projectRows.reduce((count, item) => count + item.querySelectorAll("img").length, 0),
        clientCount: document.querySelectorAll(".nice-studio-clients__list li").length,
        contactActionCount: document.querySelectorAll(".nice-studio-contact__actions a").length,
        contactPending: Boolean(document.querySelector("[data-nice-studio-contact-pending]")),
        formCount: document.querySelectorAll(".nice-studio-page form").length,
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
    paths: [...document.querySelectorAll("[data-nice-mobile-menu] .nice-mobile-menu__links a")].map((link) => link.pathname),
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

  const firstProject = page.locator(`[data-nice-studio-project="${expectedProjects[0]}"] a[href]`).first();
  await firstProject.focus();
	await Promise.all([
		page.waitForURL(origin + expectedProjectPaths[0], { waitUntil: "domcontentloaded" }),
		page.keyboard.press("Enter"),
	]);
  const keyboardProjectPath = page.url().replace(origin, "").split(/[?#]/)[0];
	await page.goBack({ waitUntil: "domcontentloaded" });
  const backForwardRestoredStudio = page.url() === studioUrl;

  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.reload({ waitUntil: "networkidle" });
  const reducedMotion = await page.evaluate(() => ({
    revealsRemainVisible: !document.documentElement.classList.contains("nice-has-reveal"),
    revealsDoNotTransition: [...document.querySelectorAll("[data-nice-reveal]")].every(
      (element) => Number.parseFloat(getComputedStyle(element).transitionDuration) <= 0.001,
    ),
  }));
  await page.emulateMedia({ reducedMotion: "no-preference" });

  const landingPage = await page.context().newPage();
  await landingPage.setViewportSize({ width: 1200, height: 900 });
  await landingPage.goto(origin + "/", { waitUntil: "networkidle" });
	const landingStudioRoutes = await landingPage
		.locator('a[href$="/studio/"]')
		.evaluateAll((links) => links.map((link) => link.pathname));
  await landingPage.close();

  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto(studioUrl, { waitUntil: "networkidle" });
  const studioToLandingRoutes = await page.locator(".nice-brand-link").evaluateAll((links) => links.map((link) => link.pathname));
  await scrollThroughPage();
  await page.waitForFunction(() => [...document.images].every((image) => image.complete && image.naturalWidth > 0));
  const loadedMedia = await page.evaluate(() => ({
    imageCount: document.images.length,
    loadedImageCount: [...document.images].filter((image) => image.complete && image.naturalWidth > 0).length,
    projectImageCount: document.querySelectorAll("[data-nice-studio-project] img").length,
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
  const routeResults = [];
  for (const path of expectedRoutes) {
    const response = await page.request.get(origin + path, { maxRedirects: 0 });
    routeResults.push({ path, status: response.status(), location: response.headers().location ?? "" });
  }

  const retiredDeckRequests = [...new Set(studioHomeRequestedUrls.filter((url) => retiredDeckImagePattern.test(url)))];
  const failures = [];
  if (viewportResults.some((result) => result.navigationStatus !== 200)) failures.push("Studio status");
  if (viewportResults.some((result) => result.title !== "NICE Studio" || result.h1Count !== 1)) failures.push("logical H1");
  if (viewportResults.some((result) => result.canonical !== studioUrl)) failures.push("canonical URL");
  if (viewportResults.some((result) => !result.hasMain || !result.hasFooter || !result.headingHierarchy || result.unnamedLinkCount)) failures.push("semantic landmarks");
  if (viewportResults.some((result) => result.removedChromeCount !== 0)) failures.push("removed Studio navigation chrome");
  if (viewportResults.some((result) => result.hasHorizontalOverflow || !result.headingsFit)) failures.push("responsive overflow");
  if (viewportResults.some((result) => !result.imagesHaveDimensions || !result.imagesHaveAlt || !result.imagesStayInBounds || !result.heroStateValid)) failures.push("responsive media");
  if (viewportResults.some((result) => result.serviceNames.join(",") !== expectedServices.join(",") || result.serviceSlugs.join(",") !== expectedServiceSlugs.join(",") || result.servicePaths.join(",") !== expectedServicePaths.join(","))) failures.push("linked Studio Services");
  if (viewportResults.some((result) => result.projectSlugs.join(",") !== expectedProjects.join(",") || result.projectPaths.join(",") !== expectedProjectPaths.join(","))) failures.push("linked Studio Case Studies");
  if (viewportResults.some((result) => result.projectPlaceholderCount !== expectedProjects.length || result.projectImageCount !== 0)) failures.push("neutral project media");
  if (viewportResults.some((result) => result.clientCount !== 8)) failures.push("shared Clients");
  // Studio contact is approved and published, so actions render and the pending
  // state is gone. The page must still never grow a form.
  if (viewportResults.some((result) => result.contactActionCount === 0 || result.contactPending || result.formCount !== 0)) failures.push("contact safety");
  if (viewportResults.some((result) => result.cumulativeLayoutShift > 0.1)) failures.push("layout shift");
  if (menuOpen.expanded !== "true" || menuOpen.state !== "open" || menuOpen.hidden !== "false" || menuOpen.focusedControl !== "Close menu") failures.push("mobile menu open");
  if (menuOpen.paths.join(",") !== expectedMenuPaths.join(",")) failures.push("shared Studio menu routes");
  if (menuClosed.expanded !== "false" || menuClosed.hidden !== "true" || !menuClosed.focusRestored) failures.push("mobile menu close");
  if (!condensedAtScroll || !expandedAtTop) failures.push("sticky navigation");
  if (keyboardProjectPath !== expectedProjectPaths[0] || !backForwardRestoredStudio) failures.push("keyboard project navigation");
  if (!reducedMotion.revealsRemainVisible || !reducedMotion.revealsDoNotTransition) failures.push("reduced motion");
  if (!landingStudioRoutes.length || landingStudioRoutes.some((path) => path !== "/studio/")) failures.push("Landing to Studio");
  if (!studioToLandingRoutes.includes("/")) failures.push("Studio to Landing");
  if (loadedMedia.imageCount !== loadedMedia.loadedImageCount || loadedMedia.projectImageCount !== 0) failures.push("image loading");
  if (loadedMedia.visibleRevealCount !== loadedMedia.revealCount) failures.push("revealed content");
  if (routeResults.some((result) => result.status !== 200 || result.location)) failures.push("Studio route behavior");
  if (retiredDeckRequests.length) failures.push("retired deck media requests");
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
    keyboardProjectPath,
    backForwardRestoredStudio,
    reducedMotion,
    loadedMedia,
    landingStudioRoutes,
    studioToLandingRoutes,
    routeResults,
    retiredDeckRequests,
    failedRequests,
    consoleErrors,
  };
}
