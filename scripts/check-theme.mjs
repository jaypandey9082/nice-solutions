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
	'assets/css/site.css',
	'assets/css/landing.css',
	'assets/css/editor.css',
	'assets/js/navigation.js',
	'assets/js/landing.js',
	'assets/js/media.js',
	'assets/images/nice-logo.png',
	'assets/images/nice-site-icon.png',
	'parts/header.html',
	'parts/footer.html',
	'templates/index.html',
	'templates/front-page.html',
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
].join('\n');
const header = readFileSync(resolve(themeDirectory, 'patterns/site-header.php'), 'utf8');
const frontPage = readFileSync(resolve(themeDirectory, 'templates/front-page.html'), 'utf8');

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

if (!header.includes('assets/images/nice-logo.png')) {
	fail('Global header must use the supplied NICE logo asset.');
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
