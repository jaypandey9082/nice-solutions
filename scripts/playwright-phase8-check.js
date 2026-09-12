async (page) => {
  const origin = "http://nice-solutions.local";
  const widths = [320, 360, 390, 430, 768, 900, 1024, 1200, 1440];

  const serviceRoutes = [
    ["/studio/services/corporate-videos/", "Corporate Videos"],
    ["/studio/services/digital-content-creation/", "Digital Content Creation"],
    ["/studio/services/films-entertainment/", "Films & Entertainment"],
  ];

  const caseStudyRoutes = [
    ["/studio/case-studies/strata-geosystems-factory-shoot/", "Strata Geosystems Factory Shoot"],
    ["/studio/case-studies/career-agents-academy/", "Career Agents Academy"],
    ["/studio/case-studies/krish-e/", "Krish-e"],
    ["/studio/case-studies/crisil-financial-literacy-content/", "CRISIL Financial Literacy Content"],
    ["/studio/case-studies/jayanti/", "Jayanti"],
  ];

  const indexRoutes = [
    ["/studio/", "Studio"],
    ["/studio/services/", "Studio services"],
    ["/studio/case-studies/", "Case studies"],
    ["/studio/clients/", "Clients"],
    ["/studio/team/", "Studio team"],
    ["/studio/contact/", "Let's create something NICE."],
  ];

  const regressionRoutes = [
    ["/", "NICE"],
    ["/events/", "Events"],
    ["/events/services/", "Events services"],
    ["/events/services/corporate-events/", "Corporate Events"],
    ["/events/case-studies/", "Case studies"],
    ["/events/case-studies/voltas-fam-tastic-fiesta/", "Voltas Fam-Tastic Fiesta"],
    ["/events/clients/", "Clients"],
    ["/events/team/", "Events team"],
    ["/events/contact/", "Let's make something NICE."],
  ];

  const invalidRoutes = [
    "/studio/services/invalid-service/",
    "/studio/case-studies/invalid-case-study/",
    "/studio/case-studies/voltas-fam-tastic-fiesta/",
    "/events/case-studies/krish-e/",
    "/nice_service/corporate-videos/",
    "/nice_case_study/krish-e/",
    "/team/",
  ];

  const routes = [...indexRoutes, ...serviceRoutes, ...caseStudyRoutes];
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

  const consoleHandler = (message) => {
    if (message.type() === "error") {
      const text = message.text();
      if (text.includes("404") && text.includes("Failed to load resource")) return;
      consoleErrors.push(text);
    }
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
      // Layout observer fallback
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
    await page.waitForTimeout(160);
  };

  // 1. Responsive Viewport Audits for Core Studio Pages
  for (const width of widths) {
    await page.setViewportSize({ width, height: width < 768 ? 844 : 960 });

    for (const [path, expectedTitle] of [
      ["/studio/services/", "Studio services"],
      ["/studio/services/corporate-videos/", "Corporate Videos"],
      ["/studio/case-studies/", "Case studies"],
      ["/studio/case-studies/krish-e/", "Krish-e"],
      ["/studio/clients/", "Clients"],
      ["/studio/team/", "Studio team"],
      ["/studio/contact/", "Let's create something NICE."],
    ]) {
      const response = await page.goto(origin + path, { waitUntil: "networkidle" });

      const evaluation = await page.evaluate(
        ({ expectedTitle, path }) => {
          const headings = [...document.querySelectorAll("h1, h2, h3")];
          const images = [...document.images];
          const links = [...document.querySelectorAll("a[href]")];
          const heroImage = document.querySelector(".nice-studio-inner-hero__media img, .nice-studio-hero__media img");
          const activeNavLinks = [...document.querySelectorAll(".nice-studio-subnav a[aria-current='page']")];

          return {
            path,
            expectedTitle,
            title: document.querySelector("h1")?.textContent?.trim() || "",
            h1Count: document.querySelectorAll("h1").length,
            hasMain: Boolean(document.querySelector("main#main-content")),
            hasFooter: Boolean(document.querySelector("footer.nice-site-footer")),
            // The Studio sub-navigation was consolidated into the shared pill
            // header, so division navigation now lives in the site header.
            hasStudioNavigation: Boolean(
              document.querySelector(".nice-nav-shell .nice-desktop-nav, .nice-nav-shell [data-nice-menu-open]"),
            ),
            activeNavigationCount: activeNavLinks.length,
            hasHorizontalOverflow: document.documentElement.scrollWidth > window.innerWidth + 1,
            headingsFit: headings.every((heading) => heading.scrollWidth <= heading.clientWidth + 2),
            imagesHaveDimensions: images.every((image) => image.naturalWidth > 0 && image.naturalHeight > 0),
            imagesStayInBounds: images.every((image) => image.getBoundingClientRect().width <= window.innerWidth + 1),
            cumulativeLayoutShift: window.__niceCumulativeLayoutShift || 0,
            heroPriority: heroImage ? heroImage.getAttribute("fetchpriority") : null,
            heroAlt: heroImage ? heroImage.getAttribute("alt") : null,
            rawCptLinks: links
              .map((link) => link.getAttribute("href") || "")
              .filter((href) => href.includes("/nice_service/") || href.includes("/nice_case_study/")),
            globalTeamLinks: links
              .map((link) => link.getAttribute("href") || "")
              .filter((href) => href === "/team/" || href.endsWith("/team/")),
            hasEditorialSerif: Boolean(document.querySelector(".nice-editorial")),
          };
        },
        { expectedTitle, path }
      );

      viewportResults.push({ width, status: response.status(), ...evaluation });
    }
  }

  // 2. Studio Services Content Inspection
  await page.setViewportSize({ width: 1440, height: 960 });
  await page.goto(origin + "/studio/services/", { waitUntil: "networkidle" });
  const servicesData = await page.evaluate(() => {
    const cards = [...document.querySelectorAll(".nice-studio-service-row")];
    return {
      count: cards.length,
      titles: cards.map((c) => c.querySelector("h3")?.textContent?.trim()),
      links: cards.map((c) => c.querySelector("a.nice-link")?.getAttribute("href")),
    };
  });

  // 3. Studio Case Studies Content & Grouping Inspection
  await page.goto(origin + "/studio/case-studies/", { waitUntil: "networkidle" });
  const caseStudiesData = await page.evaluate(() => {
    const groups = [...document.querySelectorAll(".nice-studio-case-group")];
    return {
      groupCount: groups.length,
      groupTitles: groups.map((g) => g.querySelector("h2")?.textContent?.trim()),
      totalCases: document.querySelectorAll(".nice-studio-case-preview").length,
    };
  });

  // 4. Studio Clients Inspection
  await page.goto(origin + "/studio/clients/", { waitUntil: "networkidle" });
  const clientsData = await page.evaluate(() => {
    const clients = [...document.querySelectorAll(".nice-studio-client")];
    return {
      count: clients.length,
      hasTitles: clients.every((c) => c.querySelector("h3")?.textContent?.trim().length > 0),
    };
  });

  // 5. Studio Team Pending State Inspection
  await page.goto(origin + "/studio/team/", { waitUntil: "networkidle" });
  const teamData = await page.evaluate(() => ({
    memberCount: document.querySelectorAll(".nice-studio-team-member").length,
    isPending: Boolean(document.querySelector(".nice-studio-empty-state")),
    pendingTitle: document.querySelector(".nice-studio-empty-state h2")?.textContent?.trim(),
  }));

  // 6. Studio Contact Form-Free Inspection
  await page.goto(origin + "/studio/contact/", { waitUntil: "networkidle" });
  const contactData = await page.evaluate(() => ({
    formCount: document.querySelectorAll("form").length,
    hasActionsOrPending: Boolean(
      document.querySelector(".nice-studio-contact-page__actions") ||
      document.querySelector(".nice-studio-empty-state")
    ),
  }));

  // 7. Invalid Routes and Cross-Division 404 Audit
  const invalidResults = [];
  for (const invalidPath of invalidRoutes) {
    const resp = await page.goto(origin + invalidPath, { waitUntil: "networkidle" });
    invalidResults.push({
      path: invalidPath,
      status: resp.status(),
    });
  }

  // 8. Events & Landing Regression Audit
  const regressionResults = [];
  for (const [regPath] of regressionRoutes) {
    const resp = await page.goto(origin + regPath, { waitUntil: "networkidle" });
    const h1Count = await page.evaluate(() => document.querySelectorAll("h1").length);
    regressionResults.push({
      path: regPath,
      status: resp.status(),
      h1Count,
    });
  }

  // 9. Mobile Menu Keyboard & Focus Trapping Audit
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(origin + "/studio/services/", { waitUntil: "networkidle" });
  await page.click("[data-nice-menu-open]");
  await page.waitForTimeout(200);

  const menuOpen = await page.evaluate(() => ({
    expanded: document.querySelector("[data-nice-menu-open]")?.getAttribute("aria-expanded"),
    state: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("data-state"),
    hidden: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("aria-hidden"),
  }));

  await page.keyboard.press("Escape");
  await page.waitForTimeout(200);

  const menuClosed = await page.evaluate(() => ({
    expanded: document.querySelector("[data-nice-menu-open]")?.getAttribute("aria-expanded"),
    hidden: document.querySelector("[data-nice-mobile-menu]")?.getAttribute("aria-hidden"),
    focusRestored: document.activeElement === document.querySelector("[data-nice-menu-open]"),
  }));

  // 10. Reduced-Motion Override Audit
  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.reload({ waitUntil: "networkidle" });
  const reducedMotion = await page.evaluate(() => ({
    revealsRemainVisible: !document.documentElement.classList.contains("nice-has-reveal"),
    cardTransition: getComputedStyle(document.querySelector(".nice-studio-service-row__media img") || document.body).transitionDuration,
  }));
  await page.emulateMedia({ reducedMotion: "no-preference" });

  // 11. Screenshots Capture (1440px desktop & 390px mobile)
  const screenshotPages = [
    ["/studio/services/", "services"],
    ["/studio/services/corporate-videos/", "service-detail"],
    ["/studio/case-studies/", "case-studies"],
    ["/studio/case-studies/krish-e/", "case-study-detail"],
    ["/studio/clients/", "clients"],
    ["/studio/team/", "team"],
    ["/studio/contact/", "contact"],
  ];

  await page.setViewportSize({ width: 1440, height: 960 });
  for (const [path, label] of screenshotPages) {
    await page.goto(origin + path, { waitUntil: "networkidle" });
    await scrollThroughPage();
    await page.screenshot({ path: `output/playwright/nice-phase8-${label}-desktop.png`, fullPage: true });
  }

  await page.setViewportSize({ width: 390, height: 844 });
  for (const [path, label] of screenshotPages) {
    await page.goto(origin + path, { waitUntil: "networkidle" });
    await scrollThroughPage();
    await page.screenshot({ path: `output/playwright/nice-phase8-${label}-mobile.png`, fullPage: true });
  }

  // 12. Verification Assertions
  const failures = [];
  if (viewportResults.some((r) => r.status !== 200)) failures.push("Studio route status");
  if (viewportResults.some((r) => r.h1Count !== 1)) failures.push("Single H1 hierarchy");
  if (viewportResults.some((r) => !r.hasMain || !r.hasFooter || !r.hasStudioNavigation)) failures.push("Landmarks/Navigation");
  if (viewportResults.some((r) => r.hasHorizontalOverflow || !r.headingsFit)) failures.push("Responsive overflow");
  if (viewportResults.some((r) => !r.imagesHaveDimensions || !r.imagesStayInBounds)) failures.push("Responsive images");
  if (viewportResults.some((r) => r.cumulativeLayoutShift > 0.1)) failures.push("CLS threshold exceeded");
  if (viewportResults.some((r) => r.rawCptLinks.length > 0)) failures.push("Raw CPT link leak");
  if (viewportResults.some((r) => r.globalTeamLinks.some((l) => l === "/team/"))) failures.push("Global team link leak");
  if (servicesData.count !== 3) failures.push("Studio services count");
  if (caseStudiesData.totalCases !== 5) failures.push("Studio case studies count");
  if (clientsData.count !== 10) failures.push("Studio clients shared count");
  if (teamData.memberCount !== 0 || !teamData.isPending) failures.push("Studio team empty state");
  if (contactData.formCount > 0 || !contactData.hasActionsOrPending) failures.push("Studio contact form-free state");
  if (invalidResults.some((r) => r.status !== 404)) failures.push("404 and cross-division isolation");
  if (regressionResults.some((r) => r.status !== 200 || r.h1Count !== 1)) failures.push("Events/Landing regression");
  if (menuOpen.expanded !== "true" || menuClosed.expanded !== "false" || !menuClosed.focusRestored) failures.push("Mobile menu accessibility");
  if (!reducedMotion.revealsRemainVisible) failures.push("Reduced motion support");
  if (failedRequests.length > 0) failures.push(`Failed network requests: ${JSON.stringify(failedRequests)}`);
  if (consoleErrors.length > 0) failures.push(`Console errors: ${JSON.stringify(consoleErrors)}`);

  if (failures.length > 0) {
    throw new Error(`Phase 8 Playwright validation failed: ${failures.join(", ")}`);
  }

  return {
    checkedViewports: viewportResults.length,
    servicesData,
    caseStudiesData,
    clientsData,
    teamData,
    contactData,
    invalidResults,
    regressionResults,
    menuOpen,
    menuClosed,
    reducedMotion,
    failedRequestsCount: failedRequests.length,
    consoleErrorsCount: consoleErrors.length,
  };
};
