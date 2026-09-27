const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const origin = process.env.NICE_BASE_URL || 'http://nice-solutions.local';
const outputDir = path.resolve(__dirname, '../output/playwright/inner-redesign');
const widths = [320, 390, 768, 1024, 1440];
const routes = [
	// The landing page belongs here too: it is the one page that carried
	// hardcoded deck photography, and it previously sat outside every suite.
	'/',
	'/events/',
	'/studio/',
	'/events/services/',
	'/events/services/corporate-events/',
	'/events/case-studies/',
	'/events/case-studies/voltas-fam-tastic-fiesta/',
	'/events/clients/',
	'/events/about/',
	'/events/contact/',
	'/studio/services/',
	'/studio/services/corporate-videos/',
	'/studio/case-studies/',
	'/studio/case-studies/strata-geosystems-factory-shoot/',
	'/studio/case-studies/krish-e/',
	'/studio/clients/',
	'/studio/about/',
	'/studio/contact/',
];
/*
 * Every migrated deck image. Keep this the single list: a second, narrower copy
 * in another suite is how the landing page went unchecked.
 */
const retiredMedia = /(voltas|gca-2025|zoetis|power-champs|run-for-equity|vision-to-victory|strata-production|exhibition-stall|studio-career-agents|studio-crisil|studio-jayanti|studio-krish-e)/i;

const assert = (condition, message) => {
	if (!condition) {
		throw new Error(message);
	}
};

(async () => {
	fs.mkdirSync(outputDir, { recursive: true });
	const browser = await chromium.launch({ headless: true });
	const results = [];

	try {
		for (const width of widths) {
			const context = await browser.newContext({
				viewport: { width, height: width < 768 ? 844 : 900 },
				reducedMotion: 'no-preference',
			});
			const page = await context.newPage();
			const errors = [];
			const retiredRequests = [];

			page.on('console', (message) => {
				if (message.type() === 'error') errors.push(message.text());
			});
			page.on('pageerror', (error) => errors.push(error.message));
			page.on('request', (request) => {
				/* Only asset requests count. A case-study route such as
				   /events/case-studies/voltas-fam-tastic-fiesta/ legitimately carries a
				   retired name in its slug, so testing every request URL flags the page
				   navigation itself. */
				if (!['image', 'media', 'font'].includes(request.resourceType())) return;
				/* The ban is on migrated deck photography, not on video an editor
				   uploaded for a project. A film named after its client would
				   otherwise trip the list purely because of the client's name. */
				if (/\/wp-content\/uploads\/.+\.(mp4|webm)$/i.test(request.url())) return;
				if (retiredMedia.test(request.url())) retiredRequests.push(request.url());
			});

			for (const route of routes) {
				const response = await page.goto(origin + route, { waitUntil: 'networkidle' });
				assert(response && response.status() === 200, `${route} returned ${response?.status()}`);
				const state = await page.evaluate(() => ({
					h1Count: document.querySelectorAll('h1').length,
					stripCount: document.querySelectorAll('.nice-philosophy-strip').length,
					hasLegacySubnav: Boolean(document.querySelector('.nice-events-subnav, .nice-studio-subnav')),
					hasOverflow: document.documentElement.scrollWidth > window.innerWidth + 1,
					hasMain: Boolean(document.querySelector('main#main-content')),
					hasFooter: Boolean(document.querySelector('footer.nice-site-footer')),
					hasForm: Boolean(document.querySelector('form')),
					proofBands: document.querySelectorAll('.nice-events-project-proof, .nice-studio-project-proof').length,
					caseMedia: document.querySelectorAll('.nice-case-media-wrap').length,
					/* The write-up is the hero description now; the section that
					   used to repeat it below the details is gone. */
					bodySections: document.querySelectorAll('.nice-events-case-intro, .nice-studio-case-intro').length,
					/*
					 * A photograph must be held to its frame, never the other way
					 * round. An Events home card once grew to a portrait shot's own
					 * 1024px because the only rule sizing it lived in a stylesheet
					 * that page does not load.
					 */
					unheldMedia: [...document.querySelectorAll('[class*="__media"], .nice-case-media, .nice-project-gallery__item')]
						.filter((frame) => {
							const image = frame.querySelector('img');
							if (!image) return false;
							/*
							 * offset*, not getBoundingClientRect: the reveal animation
							 * scales media by 1.018, and a rect measured mid-flight
							 * reads as an overflow the frame is already clipping.
							 * Layout size is the question being asked.
							 */
							return (
								image.offsetHeight > frame.offsetHeight + 1 ||
								image.offsetWidth > frame.offsetWidth + 1 ||
								getComputedStyle(image).objectFit !== 'cover'
							);
						})
						.map((frame) => frame.className.toString().slice(0, 40)),
					heroIntroWords: (document.querySelector('.nice-events-inner-hero__content p:not(.nice-eyebrow), .nice-studio-inner-hero__content p:not(.nice-eyebrow)')?.textContent || '').trim().split(/\s+/).filter(Boolean).length,
				}));

				assert(state.h1Count === 1, `${route} must render exactly one H1`);
				assert(state.stripCount === 1, `${route} must render exactly one philosophy strip`);
				assert(!state.hasLegacySubnav, `${route} still renders the legacy secondary navigation`);
				assert(!state.hasOverflow, `${route} overflows at ${width}px`);
				assert(state.hasMain && state.hasFooter, `${route} is missing its main region or footer`);
				if (route.endsWith('/contact/')) assert(!state.hasForm, `${route} must remain form-free`);
				assert(state.proofBands === 0, `${route} still renders a project proof band`);
				assert(state.bodySections === 0, `${route} still repeats its description below the details`);
				assert(state.unheldMedia.length === 0, `${route} lets a picture size its own frame at ${width}px: ${state.unheldMedia.join(', ')}`);
				if (route.match(/\/case-studies\/[^/]+\/$/)) {
					assert(state.heroIntroWords >= 12, `${route} hero description is only ${state.heroIntroWords} words; it should carry the project write-up`);
				}
				if (route.startsWith('/events/case-studies/')) {
					assert(state.caseMedia === 0, `${route} must not render feature media; Events leads with the gallery`);
				}
				if (route.startsWith('/studio/case-studies/')) {
					assert(state.caseMedia <= 1, `${route} renders ${state.caseMedia} feature media blocks`);
				}

				results.push({ width, route, ...state });
			}

			assert(errors.length === 0, `Console errors at ${width}px: ${errors.join(' | ')}`);
			assert(retiredRequests.length === 0, `Retired media requested at ${width}px: ${retiredRequests.join(', ')}`);
			await context.close();
		}

		const mobile = await browser.newContext({ viewport: { width: 390, height: 844 } });
		const page = await mobile.newPage();
		await page.goto(origin + '/studio/services/', { waitUntil: 'networkidle' });
		await page.click('[data-nice-menu-open]');
		assert(await page.getAttribute('[data-nice-menu-open]', 'aria-expanded') === 'true', 'Menu did not open');
		await page.keyboard.press('Escape');
		assert(await page.getAttribute('[data-nice-menu-open]', 'aria-expanded') === 'false', 'Menu did not close with Escape');
		assert(await page.evaluate(() => document.activeElement === document.querySelector('[data-nice-menu-open]')), 'Menu focus was not restored');
		await mobile.close();

		const report = path.join(outputDir, 'report.json');
		fs.writeFileSync(report, JSON.stringify({ origin, results }, null, 2));
		console.log(`Inner-page redesign verified across ${routes.length} routes and ${widths.length} widths.`);
		console.log(`Report: ${report}`);
	} finally {
		await browser.close();
	}
})().catch((error) => {
	console.error(error);
	process.exit(1);
});
