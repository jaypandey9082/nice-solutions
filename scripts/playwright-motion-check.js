const { chromium } = require("playwright");

const base = process.env.NICE_BASE_URL || "http://nice-solutions.local";
const failures = [];
const assert = (ok, message) => {
	console.log(`${ok ? "  ok  " : "  FAIL"} ${message}`);
	if (!ok) failures.push(message);
};

(async () => {
	const browser = await chromium.launch({ headless: true });

	/* 1. The wheel is smoothed; every other input stays the browser's. */
	{
		const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
		await page.addInitScript(() => {
			window.__niceListeners = [];
			const add = EventTarget.prototype.addEventListener;
			EventTarget.prototype.addEventListener = function (type, ...rest) {
				window.__niceListeners.push(type);
				return add.call(this, type, ...rest);
			};
		});
		await page.goto(`${base}/events/`, { waitUntil: "load" });

		const registered = await page.evaluate(() => window.__niceListeners);
		const touch = registered.filter((t) => ["touchmove", "touchstart", "touchend"].includes(t));
		assert(touch.length === 0, `touch is never intercepted (found: ${touch.join(", ") || "none"})`);

		const scrollBehavior = await page.evaluate(() => getComputedStyle(document.documentElement).scrollBehavior);
		assert(scrollBehavior === "auto", `html scroll-behavior stays auto (is ${scrollBehavior})`);

		/*
		 * Wheel easing is a setting, so this asserts whichever mode is
		 * configured rather than one of them. theme.json's scroll-ease is the
		 * switch: a fraction eases the wheel, zero hands it to the platform.
		 * Either way the gesture has to land where it was aimed, and either way
		 * the page must never be left unable to scroll.
		 */
		const ease = await page.evaluate(() => Number.parseFloat(
			getComputedStyle(document.documentElement).getPropertyValue("--wp--custom--motion--scroll-ease")
		) || 0);

		await page.evaluate(() => { window.__s = []; const t = setInterval(() => window.__s.push(Math.round(window.scrollY)), 50); setTimeout(() => clearInterval(t), 1500); });
		await page.mouse.move(700, 450);
		await page.mouse.wheel(0, 600);
		await page.waitForTimeout(1200);

		const samples = [...new Set(await page.evaluate(() => window.__s))];
		const landed = samples[samples.length - 1];
		assert(Math.abs(landed - 600) <= 40, `a wheel gesture lands where it was aimed (${landed} of 600)`);

		if (ease > 0) {
			assert(samples.length >= 5, `it eases over several frames (${samples.length} positions)`);
			assert(samples[0] < 300, `and starts moving immediately (first sample ${samples[0]})`);

			/* Deceleration, not a constant slide. */
			const firstStep = samples[1] - samples[0];
			const lastStep = samples[samples.length - 1] - samples[samples.length - 2];
			assert(firstStep > lastStep, `it decelerates (${firstStep}px then ${lastStep}px per frame)`);
		} else {
			/*
			 * With easing off the wheel must not be touched at all: no
			 * preventDefault, so the platform's own scrolling and its overscroll
			 * behaviour are left intact.
			 */
			/*
			 * The listener is armed first and read afterwards. Awaiting a promise
			 * inside evaluate() would block until it resolved, which happens
			 * before the wheel is ever dispatched -- the assertion would then
			 * pass having observed nothing at all.
			 */
			await page.evaluate(() => {
				window.__wheelSeen = null;
				window.addEventListener("wheel", (event) => {
					if (null === window.__wheelSeen) {
						window.__wheelSeen = event.defaultPrevented;
					}
				}, { passive: true });
			});
			await page.mouse.wheel(0, 120);
			await page.waitForTimeout(120);

			const intercepted = await page.evaluate(() => window.__wheelSeen);
			assert(intercepted === false, `the wheel reaches the page unprevented, so the platform scrolls it (defaultPrevented: ${intercepted})`);
		}
		await page.close();
	}

	/* 1b. Keyboard and programmatic scrolling remain native. */
	{
		const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
		await page.goto(`${base}/events/`, { waitUntil: "load" });

		await page.evaluate(() => window.scrollTo(0, 0));
		await page.keyboard.press("End");
		await page.waitForTimeout(400);
		const afterEnd = await page.evaluate(() => window.scrollY);
		assert(afterEnd > 1000, `End still jumps to the bottom (${afterEnd})`);

		await page.keyboard.press("Home");
		await page.waitForTimeout(400);
		const afterHome = await page.evaluate(() => window.scrollY);
		assert(afterHome === 0, `Home still returns to the top (${afterHome})`);

		/* The easing layer must resynchronise rather than fight a native move. */
		await page.evaluate(() => window.scrollTo(0, 900));
		await page.mouse.move(700, 450);
		await page.mouse.wheel(0, 200);
		await page.waitForTimeout(900);
		const resynced = await page.evaluate(() => window.scrollY);
		assert(resynced > 900 && resynced < 1250, `a wheel after a native jump continues from there (${resynced})`);
		await page.close();
	}

	/* 1c. At the top of the page, scrolling up is left entirely to the browser. */
	{
		const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
		await page.goto(`${base}/events/`, { waitUntil: "load" });
		const defaultPrevented = await page.evaluate(() => new Promise((resolve) => {
			window.addEventListener("wheel", (e) => resolve(e.defaultPrevented), { once: true, passive: true });
			window.dispatchEvent(new WheelEvent("wheel", { deltaY: -120, cancelable: true, bubbles: true }));
		}));
		assert(defaultPrevented === false, "an upward gesture at the top is not swallowed");
		await page.close();
	}

	/* 2. Reveals: nothing stays hidden, and above-the-fold content never animates in. */
	{
		const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
		await page.goto(`${base}/events/`, { waitUntil: "load" });

		const aboveFold = await page.evaluate(() => {
			const first = document.querySelector("[data-nice-reveal]");
			return first ? { visible: first.classList.contains("is-visible"), opacity: getComputedStyle(first).opacity } : null;
		});
		assert(aboveFold && aboveFold.visible, "the first section is visible without waiting");
		assert(aboveFold && Number(aboveFold.opacity) === 1, `and fully opaque at load (${aboveFold?.opacity})`);

		/* Fast scroll to the bottom: nothing may be left invisible. */
		await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
		await page.waitForTimeout(1400);
		const hidden = await page.evaluate(() =>
			[...document.querySelectorAll("[data-nice-reveal], [data-nice-editorial-reveal]")]
				.filter((el) => Number(getComputedStyle(el).opacity) < 1).length
		);
		assert(hidden === 0, `no section left invisible after a fast scroll (${hidden} hidden)`);

		const willChange = await page.evaluate(() => document.querySelectorAll(".nice-reveal-pending").length);
		assert(willChange === 0, `will-change hints released (${willChange} left)`);
		await page.close();
	}

	/* 3. Anchor travel: eased, lands below the header, updates the fragment, moves focus. */
	{
		const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
		/* The Events home page is the longest, so there is real distance to travel. */
		await page.goto(`${base}/events/`, { waitUntil: "load" });

		/*
		 * Travel is cancelled on visibilitychange when the document is hidden --
		 * deliberately, because a visitor who switched tabs is not still
		 * travelling. A page Playwright has just created can be in the
		 * background, so the travel is measured on a page known to be visible.
		 *
		 * This is not what caused the intermittent "0 intermediate positions"
		 * failure this block used to report; that was a real bug in motion.js,
		 * where a frame stamped just before the click ran the easing curve
		 * backwards and the resulting clamped scroll ended the travel after one
		 * frame. Fixed in assets/js/motion.js.
		 */
		await page.bringToFront();
		await page.waitForFunction(() => document.visibilityState === "visible");

		/*
		 * The published design currently carries no visible in-page anchor — the
		 * only one is the skip link, which is deliberately off-screen. The
		 * behaviour still has to be correct for the moment one is added, so the
		 * test inserts a link to a real section rather than skipping.
		 */
		const anchor = await page.evaluate(() => {
			const existing = [...document.querySelectorAll('a[href*="#"]')].find((a) => {
				const url = new URL(a.href, location.href);
				if (url.pathname !== location.pathname || !url.hash || url.hash === "#") return false;
				if (!document.querySelector(url.hash)) return false;
				return a.offsetParent !== null && !a.classList.contains("skip-link");
			});

			if (existing) {
				existing.dataset.niceMotionProbe = "1";
				return existing.hash;
			}

			/*
			 * A real content section below the fold and not at the very end of the
			 * page: travel has to be long enough to animate, and the destination
			 * has to be somewhere the page can actually scroll it to.
			 */
			const section = [...document.querySelectorAll("main section[id], main [id][data-nice-reveal]")].find((el) => {
				const box = el.getBoundingClientRect();
				return (
					box.height > 100 &&
					box.top > window.innerHeight * 0.75 &&
					box.top + window.scrollY < document.documentElement.scrollHeight - window.innerHeight &&
					getComputedStyle(el).position !== "fixed"
				);
			});
			if (!section) return null;

			const link = document.createElement("a");
			link.href = `#${section.id}`;
			link.textContent = "probe";
			link.dataset.niceMotionProbe = "1";
			document.querySelector("main, body").prepend(link);
			return link.hash;
		});

		if (!anchor) {
			assert(false, "a same-page anchor exists to test");
		} else {
			/*
			 * Sampling and the click happen in one evaluate, and the sampler
			 * decides when to stop rather than a fixed wait deciding for it.
			 * Split across two calls with a 1100ms timeout, a loaded machine
			 * could start sampling after the 650ms travel had already finished
			 * and report a jump that never happened -- this failed about one run
			 * in five while the animation itself was working.
			 */
			const samples = await page.evaluate(() => new Promise((resolve) => {
				const collected = [];
				let settled = 0;

				const tick = setInterval(() => {
					collected.push(window.scrollY);

					const count = collected.length;
					const isStill = count > 1 && collected[count - 1] === collected[count - 2];
					settled = isStill ? settled + 1 : 0;

					/* Four still samples, or a two-second ceiling so a stuck page still reports. */
					if ((settled >= 4 && count > 6) || count > 50) {
						clearInterval(tick);
						resolve(collected);
					}
				}, 40);

				/* Dispatched in the page: this exercises the delegated handler, not Playwright's scrolling. */
				document.querySelector('[data-nice-motion-probe]').click();
			}));

			const moved = samples.filter((v, i) => i && v !== samples[i - 1]).length;
			assert(moved >= 3, `travel is animated rather than a jump (${moved} intermediate positions)`);

			const landing = await page.evaluate((hash) => {
				const target = document.querySelector(hash);
				const shell = document.querySelector("[data-nice-header] .nice-nav-shell");
				return {
					top: target.getBoundingClientRect().top,
					header: shell ? shell.getBoundingClientRect().bottom : 0,
					hash: location.hash,
					focused: document.activeElement === target,
				};
			}, anchor);

			assert(landing.top >= landing.header - 2, `destination clears the sticky header (top ${Math.round(landing.top)}, header ${Math.round(landing.header)})`);
			assert(landing.hash === anchor, `the URL fragment is ${anchor} (is ${landing.hash})`);
			assert(landing.focused, "keyboard focus moved to the destination");

			await page.goBack();
			await page.waitForTimeout(300);
			const back = await page.evaluate(() => location.hash);
			assert(back !== anchor, `Back leaves the fragment (now "${back}")`);
		}
		await page.close();
	}

	/* 4. Reduced motion: instant travel, nothing hidden, no reveal class. */
	{
		const page = await browser.newPage({ viewport: { width: 1440, height: 900 }, reducedMotion: "reduce" });
		await page.goto(`${base}/events/`, { waitUntil: "load" });

		const state = await page.evaluate(() => ({
			hasReveal: document.documentElement.classList.contains("nice-has-reveal"),
			hidden: [...document.querySelectorAll("[data-nice-reveal]")].filter((el) => Number(getComputedStyle(el).opacity) < 1).length,
		}));
		assert(!state.hasReveal, "no reveal state is applied");
		assert(state.hidden === 0, `nothing is hidden (${state.hidden})`);

		await page.evaluate(() => window.scrollTo(0, 0));
		await page.mouse.move(700, 450);
		await page.mouse.wheel(0, 400);
		await page.waitForTimeout(250);
		const position = await page.evaluate(() => window.scrollY);
		assert(position >= 380, `the wheel moves the page natively, with no easing (${position} of 400)`);
		await page.close();
	}

	/* 5. No horizontal overflow at any width while revealing. */
	{
		for (const width of [320, 390, 768, 1024, 1440]) {
			const page = await browser.newPage({ viewport: { width, height: 900 } });
			await page.goto(`${base}/events/`, { waitUntil: "load" });
			await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight / 2));
			await page.waitForTimeout(500);
			const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
			assert(overflow <= 0, `no horizontal overflow at ${width}px (${overflow}px)`);
			await page.close();
		}
	}

	/* 6. No console errors on any of the three homepages. */
	{
		for (const route of ["/", "/events/", "/studio/"]) {
			const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
			const errors = [];
			page.on("console", (m) => m.type() === "error" && errors.push(m.text()));
			page.on("pageerror", (e) => errors.push(e.message));
			await page.goto(`${base}${route}`, { waitUntil: "load" });
			await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
			await page.waitForTimeout(600);
			assert(errors.length === 0, `no console errors on ${route}${errors.length ? ` (${errors[0]})` : ""}`);
			await page.close();
		}
	}

	await browser.close();

	if (failures.length) {
		console.error(`\n${failures.length} motion assertion(s) failed.`);
		process.exit(1);
	}
	console.log("\nMotion system verified.");
})();
