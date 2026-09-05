async (page) => {
  const url = "http://nice-solutions.local/";
  const widths = [320, 360, 390, 430, 768, 1024, 1440];
  const viewportResults = [];
  const failedRequests = [];

  page.on("requestfailed", (request) => {
    failedRequests.push({ url: request.url(), error: request.failure()?.errorText });
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
    ctaTargets: [...document.querySelectorAll(".nice-contact-band__actions a")].map((link) => link.hash),
    imageCount: document.images.length,
    loadedImageCount: [...document.images].filter((image) => image.complete && image.naturalWidth > 0).length,
    visibleRevealCount: document.querySelectorAll("[data-nice-reveal].is-visible").length,
    revealCount: document.querySelectorAll("[data-nice-reveal]").length,
  }));
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
  };
}
