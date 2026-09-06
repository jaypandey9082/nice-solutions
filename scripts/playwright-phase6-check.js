async (page) => {
  const origin = "http://nice-solutions.local";
  const widths = [320, 360, 390, 430, 768, 900, 1024, 1200, 1440];
  const serviceRoutes = [
    ["/events/services/corporate-events/", "Corporate Events"],
    ["/events/services/exhibitions-conferences/", "Exhibitions & Conferences"],
    ["/events/services/activations-promotions/", "Activations & Promotions"],
  ];
  const caseStudyRoutes = [
    ["/events/case-studies/voltas-fam-tastic-fiesta/", "Voltas Fam-Tastic Fiesta"],
    ["/events/case-studies/gca-2025/", "GCA 2025"],
    ["/events/case-studies/zoetis-employee-engagement-day/", "Zoetis Employee Engagement Day"],
    ["/events/case-studies/vision-to-victory/", "Vision to Victory"],
    ["/events/case-studies/run-for-equity/", "RunForEquity"],
  ];
  const indexRoutes = [
    ["/events/services/", "Events services"],
    ["/events/case-studies/", "Case studies"],
    ["/events/clients/", "Clients"],
    ["/events/team/", "Events team"],
    ["/events/contact/", "Let's make something NICE."],
  ];
  const routes = [...indexRoutes, ...serviceRoutes, ...caseStudyRoutes];
  const viewportResults = [];
  const failedRequests = [];
  const consoleErrors = [];

  page.on("requestfailed", (request) => {
    failedRequests.push({ url: request.url(), error: request.failure()?.errorText });
  });
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
      // Layout and intrinsic-dimension checks remain active in older browsers.
    }
  });

  const scrollThroughPage = async () => {
    const height = await page.evaluate(() => document.documentElement.scrollHeight);
    const viewport = await page.viewportSize();
    const step = Math.max(320, Math.floor(viewport.height * 0.72));

    for (let top = 0; top < height; top += step) {
      await page.evaluate((scrollTop) => window.scrollTo(0, scrollTop), top);
      await page.waitForTimeout(80);
    }

    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(180);
  };

  for (const width of widths) {
    await page.setViewportSize({ width, height: width < 768 ? 844 : 900 });

    for (const [path, expectedTitle] of routes) {
      const response = await page.goto(origin + path, { waitUntil: "networkidle" });
      const result = await page.evaluate(() => {
        const root = document.documentElement;
        const images = [...document.images];
        const headings = [...document.querySelectorAll("h1, h2, h3")];
        const h1s = [...document.querySelectorAll("main h1")];
        const heroImage = document.querySelector(".nice-events-inner-hero__media img");

        return {
          title: h1s[0]?.textContent.trim() ?? "",
          h1Count: h1s.length,
          hasMain: Boolean(document.querySelector("main#main-content")),
          hasFooter: Boolean(document.querySelector(".nice-site-footer")),
          hasEventsNavigation: Boolean(document.querySelector('.nice-events-subnav[aria-label="Events navigation"]')),
          activeNavigationCount: document.querySelectorAll('.nice-events-subnav [aria-current="page"]').length,
          hasHorizontalOverflow: root.scrollWidth > root.clientWidth,
          headingsFit: headings.every((heading) => heading.scrollWidth <= heading.clientWidth + 1),
          imagesHaveDimensions: images.every(
            (image) => image.hasAttribute("width") && image.hasAttribute("height"),
          ),
          imagesStayInBounds: images.every((image) => {
            const box = image.getBoundingClientRect();
            return box.left >= -1 && box.right <= root.clientWidth + 1;
          }),
          heroPriority: heroImage?.getAttribute("fetchpriority") ?? "",
          heroAlt: heroImage?.getAttribute("alt") ?? null,
          cumulativeLayoutShift: window.__niceCumulativeLayoutShift,
          rawCptLinks: [...document.links]
            .map((link) => link.href)
            .filter((href) => /nice_(service|case_study)|[?&]nice_(service|case_study)=/.test(href)),
          globalTeamLinks: [...document.links]
            .map((link) => new URL(link.href).pathname)
            .filter((pathname) => pathname === "/team/"),
          unnamedLinks: [...document.links].filter((link) => !link.textContent.trim() && !link.getAttribute("aria-label")).length,
        };
      });

      viewportResults.push({ width, path, status: response?.status() ?? 0, expectedTitle, ...result });
    }
  }

  await page.setViewportSize({ width: 1440, height: 960 });
  await page.goto(origin + "/events/services/", { waitUntil: "networkidle" });
  const services = await page.evaluate(() => ({
    names: [...document.querySelectorAll(".nice-events-service-row h3")].map((node) => node.textContent.trim()),
    routes: [...document.querySelectorAll(".nice-events-service-row .nice-link")].map((link) => link.pathname),
  }));

  await page.goto(origin + "/events/case-studies/", { waitUntil: "networkidle" });
  const caseStudies = await page.evaluate(() => ({
    groups: ["corporate-events", "exhibitions-conferences", "activations-promotions"].map((id) => ({
      id,
      count: document.querySelectorAll(`#${id} .nice-events-case-preview`).length,
      titles: [...document.querySelectorAll(`#${id} .nice-events-case-preview h3`)].map((node) => node.textContent.trim()),
    })),
    total: document.querySelectorAll(".nice-events-case-preview").length,
  }));

  const detailChecks = [];
  for (const [path, expectedTitle] of [...serviceRoutes, ...caseStudyRoutes]) {
    await page.goto(origin + path, { waitUntil: "networkidle" });
    detailChecks.push(await page.evaluate(({ path, expectedTitle }) => ({
      path,
      expectedTitle,
      title: document.querySelector("main h1")?.textContent.trim(),
      canonical: document.querySelector('link[rel="canonical"]')?.getAttribute("href"),
      hasEditorContent: Boolean(document.querySelector(".nice-events-editor-content p")),
      hasRelatedContent: Boolean(document.querySelector(".nice-events-related-work, .nice-events-related-services")),
      division: [...document.querySelectorAll(".nice-events-case-meta div")]
        .find((item) => item.querySelector("dt")?.textContent.trim() === "Division")
        ?.querySelector("dd")?.textContent.trim(),
    }), { path, expectedTitle }));
  }

  await page.goto(origin + "/events/clients/", { waitUntil: "networkidle" });
  const clients = await page.evaluate(() => ({
    count: document.querySelectorAll(".nice-events-client").length,
    brokenLogoCount: [...document.querySelectorAll(".nice-events-client__logo img")]
      .filter((image) => image.complete && !image.naturalWidth).length,
  }));

  await page.goto(origin + "/events/team/", { waitUntil: "networkidle" });
  const team = await page.evaluate(() => ({
    cardCount: document.querySelectorAll(".nice-events-team-member").length,
    pending: document.querySelector(".nice-events-empty-state--feature")?.textContent.includes("Team details are being prepared"),
  }));

  await page.goto(origin + "/events/contact/", { waitUntil: "networkidle" });
  const contact = await page.evaluate(() => ({
    actionCount: document.querySelectorAll(".nice-events-contact-page__actions a").length,
    formCount: document.querySelectorAll("main form").length,
    pending: document.querySelector(".nice-events-empty-state--feature")?.textContent.includes("Contact details are being prepared"),
  }));

  page.off("console", consoleHandler);
  page.off("pageerror", pageErrorHandler);

  const invalidRoutes = [];
  for (const path of [
    "/events/services/not-a-real-service/",
    "/events/case-studies/not-a-real-project/",
    "/nice_service/corporate-events/",
    "/nice_case_study/gca-2025/",
    "/team/",
  ]) {
    const response = await page.goto(origin + path, { waitUntil: "networkidle" });
    const result = await page.evaluate((currentPath) => ({
      path: currentPath,
      title: document.querySelector("main h1")?.textContent.trim(),
      homeRoute: document.querySelector('main a[href="/"]')?.getAttribute("href"),
      eventsRoute: document.querySelector('main a[href="/events/"]')?.getAttribute("href"),
    }), path);
    invalidRoutes.push({ ...result, status: response?.status() ?? 0 });
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(origin + "/events/services/exhibitions-conferences/", { waitUntil: "networkidle" });
  const menuButton = page.locator("[data-nice-menu-open]");
  await menuButton.click();
  await page.waitForTimeout(260);
  const menuOpen = await page.evaluate(() => ({
    expanded: document.querySelector("[data-nice-menu-open]")?.getAttribute("aria-expanded"),
    state: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("data-state"),
    hidden: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("aria-hidden"),
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

  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.reload({ waitUntil: "networkidle" });
  const reducedMotion = await page.evaluate(() => ({
    revealsRemainVisible: !document.documentElement.classList.contains("nice-has-reveal"),
    mediaTransition: getComputedStyle(document.querySelector(".nice-events-case-preview__media img")).transitionDuration,
  }));
  await page.emulateMedia({ reducedMotion: "no-preference" });

  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto(origin + "/events/services/", { waitUntil: "networkidle" });
  await scrollThroughPage();
  const servicesImagesLoaded = await page.evaluate(() => [...document.images].every((image) => image.complete && image.naturalWidth > 0));
  await page.screenshot({ path: "output/playwright/nice-phase6-services-desktop.png", fullPage: true });
  await page.goto(origin + "/events/case-studies/voltas-fam-tastic-fiesta/", { waitUntil: "networkidle" });
  await scrollThroughPage();
  const caseStudyImagesLoaded = await page.evaluate(() => [...document.images].every((image) => image.complete && image.naturalWidth > 0));
  await page.screenshot({ path: "output/playwright/nice-phase6-case-study-desktop.png", fullPage: true });
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(origin + "/events/team/", { waitUntil: "networkidle" });
  await page.screenshot({ path: "output/playwright/nice-phase6-team-mobile.png", fullPage: true });
  await page.goto(origin + "/events/contact/", { waitUntil: "networkidle" });
  await page.screenshot({ path: "output/playwright/nice-phase6-contact-mobile.png", fullPage: true });

  const failures = [];
  if (viewportResults.some((result) => result.status !== 200)) failures.push("approved route status");
  if (viewportResults.some((result) => result.title !== result.expectedTitle || result.h1Count !== 1)) failures.push("logical H1");
  if (viewportResults.some((result) => !result.hasMain || !result.hasFooter || !result.hasEventsNavigation)) failures.push("semantic landmarks");
  if (viewportResults.some((result) => result.activeNavigationCount !== 1)) failures.push("Events navigation context");
  if (viewportResults.some((result) => result.hasHorizontalOverflow || !result.headingsFit)) failures.push("responsive overflow");
  if (viewportResults.some((result) => !result.imagesHaveDimensions || !result.imagesStayInBounds)) failures.push("responsive media");
  if (viewportResults.some((result) => result.cumulativeLayoutShift > 0.1)) failures.push("layout shift");
  if (viewportResults.some((result) => result.rawCptLinks.length || result.globalTeamLinks.length || result.unnamedLinks)) failures.push("link semantics");
  if (viewportResults.filter((result) => result.heroAlt !== null).some((result) => result.heroPriority !== "high" || result.heroAlt !== "")) failures.push("hero media priority");
  if (services.names.join(",") !== "Corporate Events,Exhibitions & Conferences,Activations & Promotions") failures.push("Service content");
  if (services.routes.join(",") !== serviceRoutes.map(([path]) => path).join(",")) failures.push("Service routes");
  if (caseStudies.total !== 5 || caseStudies.groups.map((group) => group.count).join(",") !== "3,1,1") failures.push("Case Study grouping");
  if (detailChecks.some((result) => result.title !== result.expectedTitle || result.canonical !== origin + result.path || !result.hasEditorContent || !result.hasRelatedContent)) failures.push("detail content");
  if (detailChecks.filter((result) => result.path.includes("/case-studies/")).some((result) => result.division !== "Events")) failures.push("Case Study division");
  if (clients.count !== 10 || clients.brokenLogoCount) failures.push("Client directory");
  if (team.cardCount !== 0 || !team.pending) failures.push("Team empty state");
  if (contact.actionCount !== 0 || contact.formCount !== 0 || !contact.pending) failures.push("Contact empty state");
  if (!servicesImagesLoaded || !caseStudyImagesLoaded) failures.push("lazy image loading");
  if (invalidRoutes.some((result) => result.status !== 404 || result.title !== "Page not found." || result.homeRoute !== "/" || result.eventsRoute !== "/events/")) failures.push("404 behavior");
  if (menuOpen.expanded !== "true" || menuOpen.state !== "open" || menuOpen.hidden !== "false") failures.push("mobile menu open");
  if (menuClosed.expanded !== "false" || menuClosed.hidden !== "true" || !menuClosed.focusRestored) failures.push("mobile menu close");
  if (!reducedMotion.revealsRemainVisible || Number.parseFloat(reducedMotion.mediaTransition) > 0.001) failures.push("reduced motion");
  if (failedRequests.length) failures.push("failed network requests");
  if (consoleErrors.length) failures.push("console errors");

  if (failures.length) {
    throw new Error(`Phase 6 validation failed: ${failures.join(", ")}`);
  }

  return {
    checkedViewports: viewportResults.length,
    routeCount: routes.length,
    services,
    caseStudies,
    clients,
    team,
    contact,
    invalidRoutes,
    menuOpen,
    focusWrapTarget,
    menuClosed,
    reducedMotion,
    failedRequests,
    consoleErrors,
  };
}
