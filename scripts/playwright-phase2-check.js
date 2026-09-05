async (page) => {
  const url = "http://127.0.0.1:8787/preview/";
  const widths = [320, 360, 390, 430, 768, 1024, 1440];
  const viewportResults = [];

  for (const width of widths) {
    await page.setViewportSize({ width, height: width < 768 ? 844 : 900 });
    await page.goto(url, { waitUntil: "networkidle" });
    const dimensions = await page.evaluate(() => ({
      clientWidth: document.documentElement.clientWidth,
      scrollWidth: document.documentElement.scrollWidth,
    }));

    viewportResults.push({
      width,
      ...dimensions,
      hasHorizontalOverflow: dimensions.scrollWidth > dimensions.clientWidth,
    });
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: "networkidle" });

  const menuButton = page.locator("[data-nice-menu-open]");
  await menuButton.click();
  const menuOpen = await page.evaluate(() => {
    const button = document.querySelector("[data-nice-menu-open]");
    const menu = document.querySelector("[data-nice-mobile-menu]");
    return {
      expanded: button?.getAttribute("aria-expanded"),
      state: menu?.getAttribute("data-state"),
      hidden: menu?.getAttribute("aria-hidden"),
      buttonLabel: button?.getAttribute("aria-label"),
    };
  });

  await page.locator("[data-nice-mobile-menu] a").first().focus();
  await page.keyboard.press("Shift+Tab");
  const focusAfterWrap = await page.evaluate(() => ({
    tag: document.activeElement?.tagName,
    text: document.activeElement?.textContent?.trim(),
    label: document.activeElement?.getAttribute("aria-label"),
  }));

  await page.keyboard.press("Escape");
  const menuClosed = await page.evaluate(() => {
    const button = document.querySelector("[data-nice-menu-open]");
    const menu = document.querySelector("[data-nice-mobile-menu]");
    return {
      expanded: button?.getAttribute("aria-expanded"),
      state: menu?.getAttribute("data-state"),
      hidden: menu?.getAttribute("aria-hidden"),
      buttonLabel: button?.getAttribute("aria-label"),
      focusRestored: document.activeElement === button,
    };
  });

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
  const reducedMotionTransition = await page.locator(".nice-button").first().evaluate((element) =>
    getComputedStyle(element).transitionDuration,
  );
  await page.emulateMedia({ reducedMotion: "no-preference" });

  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(url, { waitUntil: "networkidle" });
  await page.screenshot({
    path: "output/playwright/nice-phase2-desktop.png",
    fullPage: true,
  });

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: "networkidle" });
  await page.screenshot({
    path: "output/playwright/nice-phase2-mobile.png",
    fullPage: true,
  });
  await page.locator("[data-nice-menu-open]").click();
  await page.waitForTimeout(300);
  await page.screenshot({
    path: "output/playwright/nice-phase2-mobile-menu.png",
  });

  return {
    viewportResults,
    menuOpen,
    focusAfterWrap,
    menuClosed,
    condensedAtScroll,
    expandedAtTop,
    reducedMotionTransition,
  };
}
