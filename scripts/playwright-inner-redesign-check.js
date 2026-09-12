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
	'/events/team/',
	'/events/contact/',
	'/studio/services/',
	'/studio/services/corporate-videos/',
	'/studio/case-studies/',
	'/studio/case-studies/strata-geosystems-factory-shoot/',
	'/studio/clients/',
	'/studio/team/',
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
				}));

				assert(state.h1Count === 1, `${route} must render exactly one H1`);
				assert(state.stripCount === 1, `${route} must render exactly one philosophy strip`);
				assert(!state.hasLegacySubnav, `${route} still renders the legacy secondary navigation`);
				assert(!state.hasOverflow, `${route} overflows at ${width}px`);
				assert(state.hasMain && state.hasFooter, `${route} is missing its main region or footer`);
				if (route.endsWith('/contact/')) assert(!state.hasForm, `${route} must remain form-free`);

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
