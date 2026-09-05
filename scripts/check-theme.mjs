import { existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptsDirectory = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(scriptsDirectory, '..');
const themeDirectory = resolve(projectRoot, 'wp-content/themes/nice');
const themePath = resolve(themeDirectory, 'theme.json');
const theme = JSON.parse(readFileSync(themePath, 'utf8'));

const requiredFiles = [
	'style.css',
	'theme.json',
	'functions.php',
	'inc/contact.php',
	'inc/events-data.php',
	'inc/landing-data.php',
	'assets/css/site.css',
	'assets/css/events.css',
	'assets/css/landing.css',
	'assets/css/editor.css',
	'assets/js/navigation.js',
	'assets/js/landing.js',
	'assets/js/media.js',
	'assets/images/nice-logo.png',
	'assets/images/nice-site-icon.png',
	'assets/images/exhibition-stall.webp',
	'assets/images/exhibition-stall-480.webp',
	'assets/images/power-champs.webp',
	'assets/images/power-champs-480.webp',
	'assets/images/run-for-equity.webp',
	'assets/images/run-for-equity-360.webp',
	'assets/images/vision-to-victory.webp',
	'assets/images/vision-to-victory-360.webp',
	'parts/header.html',
	'parts/footer.html',
	'templates/index.html',
	'templates/front-page.html',
	'templates/page-events.html',
	'templates/page.html',
	'templates/single.html',
	'templates/archive.html',
	'templates/404.html',
	'patterns/section-heading.php',
	'patterns/project-card.php',
	'patterns/service-card.php',
	'patterns/contact-cta.php',
	'patterns/site-header.php',
	'patterns/site-footer.php',
	'patterns/landing-hero.php',
	'patterns/landing-pathways.php',
	'patterns/landing-philosophy.php',
	'patterns/landing-work.php',
	'patterns/landing-capabilities.php',
	'patterns/landing-clients.php',
	'patterns/events-hero.php',
	'patterns/events-services.php',
	'patterns/events-work.php',
	'patterns/events-process.php',
	'patterns/events-proof.php',
	'patterns/events-clients.php',
	'patterns/events-contact.php',
	'preview/index.html',
	'preview/landing.html',
	'preview/tokens.css',
];

const requiredColors = [
	'background',
	'warm',
	'surface',
	'text',
	'muted',
	'brand-red',
	'action-red',
	'brand-blue',
	'border',
	'media-neutral',
];

const requiredFontSizes = [
	'label',
	'small',
	'body',
	'body-large',
	'heading-3',
	'heading-2',
	'heading-1',
	'display',
];

const fail = (message) => {
	throw new Error(message);
};

if (theme.version !== 3) {
	fail('theme.json must use version 3.');
}

for (const file of requiredFiles) {
	if (!existsSync(resolve(themeDirectory, file))) {
		fail(`Missing required theme file: ${file}`);
	}
}

const paletteSlugs = theme.settings?.color?.palette?.map(({ slug }) => slug) ?? [];
const fontSizeSlugs = theme.settings?.typography?.fontSizes?.map(({ slug }) => slug) ?? [];

for (const slug of requiredColors) {
	if (!paletteSlugs.includes(slug)) {
		fail(`Missing required color token: ${slug}`);
	}
}

for (const slug of requiredFontSizes) {
	if (!fontSizeSlugs.includes(slug)) {
		fail(`Missing required typography token: ${slug}`);
	}
}

const css = [
	readFileSync(resolve(themeDirectory, 'assets/css/site.css'), 'utf8'),
	readFileSync(resolve(themeDirectory, 'assets/css/landing.css'), 'utf8'),
	readFileSync(resolve(themeDirectory, 'assets/css/events.css'), 'utf8'),
].join('\n');
const header = readFileSync(resolve(themeDirectory, 'patterns/site-header.php'), 'utf8');
const footer = readFileSync(resolve(themeDirectory, 'patterns/site-footer.php'), 'utf8');
const hero = readFileSync(resolve(themeDirectory, 'patterns/landing-hero.php'), 'utf8');
const pathways = readFileSync(resolve(themeDirectory, 'patterns/landing-pathways.php'), 'utf8');
const contact = readFileSync(resolve(themeDirectory, 'inc/contact.php'), 'utf8');
const frontPage = readFileSync(resolve(themeDirectory, 'templates/front-page.html'), 'utf8');
const eventsPage = readFileSync(resolve(themeDirectory, 'templates/page-events.html'), 'utf8');
const eventsData = readFileSync(resolve(themeDirectory, 'inc/events-data.php'), 'utf8');
const eventsHero = readFileSync(resolve(themeDirectory, 'patterns/events-hero.php'), 'utf8');
const eventsServices = readFileSync(resolve(themeDirectory, 'patterns/events-services.php'), 'utf8');
const eventsWork = readFileSync(resolve(themeDirectory, 'patterns/events-work.php'), 'utf8');
const eventsContact = readFileSync(resolve(themeDirectory, 'patterns/events-contact.php'), 'utf8');

if (/linear-gradient|radial-gradient/i.test(css)) {
	fail('Gradient usage is not allowed in the Phase 2 foundation.');
}

for (const requiredPattern of [
	'nice/landing-hero',
	'nice/landing-pathways',
	'nice/landing-philosophy',
	'nice/landing-work',
	'nice/landing-capabilities',
	'nice/landing-clients',
	'nice/direct-contact',
]) {
	if (!frontPage.includes(requiredPattern)) {
		fail(`Front page is missing required pattern: ${requiredPattern}`);
	}
}

for (const requiredPattern of [
	'nice/events-hero',
	'nice/events-services',
	'nice/events-work',
	'nice/events-process',
	'nice/events-proof',
	'nice/events-clients',
	'nice/events-contact',
]) {
	if (!eventsPage.includes(requiredPattern)) {
		fail(`Events page is missing required pattern: ${requiredPattern}`);
	}
}

for (const service of [
	'Corporate Events',
	'Exhibitions & Conferences',
	'Activations & Promotions',
]) {
	if (!eventsData.includes(service)) {
		fail(`Events data is missing approved service: ${service}`);
	}
}

for (const route of [
	'/events/services/',
	'/events/case-studies/',
	'/events/clients/',
	'/events/contact/',
]) {
	if (![eventsHero, eventsServices, eventsWork, eventsContact].some((markup) => markup.includes(route))) {
		fail(`Events home is missing approved future route: ${route}`);
	}
}

if (existsSync(resolve(themeDirectory, 'templates/page-studio.html'))) {
	fail('Phase 4 must not add a Studio page template.');
}

if ((eventsHero.match(/fetchpriority="high"/g) ?? []).length !== 1) {
	fail('Events hero must prioritize exactly one LCP image.');
}

if (!eventsServices.includes('loading="lazy"') || !eventsWork.includes('loading="lazy"')) {
	fail('Below-the-fold Events imagery must load lazily.');
}

if (!eventsContact.includes("nice_get_contact_action( 'whatsapp'") || !eventsContact.includes("nice_get_contact_action( 'email'")) {
	fail('Events contact must use the centralized contact adapter.');
}

if (!header.includes('assets/images/nice-logo.png')) {
	fail('Global header must use the supplied NICE logo asset.');
}

if ([header, footer].some((markup) => markup.includes('/team/'))) {
	fail('Team must remain division-specific and cannot be exposed as a global route.');
}

for (const destination of ["home_url( '/events/' )", "home_url( '/studio/' )"]) {
	if (!hero.includes(destination)) {
		fail(`Hero is missing direct division route: ${destination}`);
	}
}

if (hero.includes('href="#choose-path"')) {
	fail('Hero division choices must not point to the pathway section.');
}

if (/loading="eager"/.test(pathways)) {
	fail('Below-the-fold pathway images must not load eagerly.');
}

for (const setting of ['whatsapp_url', 'email_address', 'phone_url', 'social_urls']) {
	if (!contact.includes(`'${setting}'`)) {
		fail(`Contact adapter is missing future setting: ${setting}`);
	}
}

if (/transition\s*:\s*all/i.test(css)) {
	fail('Use explicit transition properties instead of transition: all.');
}

for (const requiredMarkup of [
	'aria-controls="nice-mobile-menu"',
	'aria-expanded="false"',
	'aria-hidden="true"',
	'data-nice-menu-open',
	'data-nice-menu-close',
	'inert',
]) {
	if (!header.includes(requiredMarkup)) {
		fail(`Header is missing accessibility markup: ${requiredMarkup}`);
	}
}

console.log(
	`Validated theme.json v${theme.version}, ${requiredFiles.length} required files, ` +
		`${requiredColors.length} colors, and ${requiredFontSizes.length} type tokens.`
);
