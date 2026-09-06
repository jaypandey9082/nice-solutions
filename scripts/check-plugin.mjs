import { existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptsDirectory = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(scriptsDirectory, '..');
const pluginDirectory = resolve(projectRoot, 'wp-content/plugins/nice-core');
const themeDirectory = resolve(projectRoot, 'wp-content/themes/nice');

const requiredFiles = [
	'nice-core.php',
	'includes/post-types.php',
	'includes/taxonomies.php',
	'includes/meta.php',
	'includes/settings.php',
	'includes/queries.php',
	'includes/routes.php',
	'includes/helpers.php',
	'includes/admin.php',
	'includes/migration.php',
	'includes/activation.php',
	'uninstall.php',
	'readme.txt',
];

const fail = (message) => {
	throw new Error(message);
};

for (const file of requiredFiles) {
	if (!existsSync(resolve(pluginDirectory, file))) {
		fail(`Missing NICE Core file: ${file}`);
	}
}

const source = requiredFiles
	.filter((file) => file.endsWith('.php'))
	.map((file) => readFileSync(resolve(pluginDirectory, file), 'utf8'))
	.join('\n');
const rootPlugin = readFileSync(resolve(pluginDirectory, 'nice-core.php'), 'utf8');
const postTypes = readFileSync(resolve(pluginDirectory, 'includes/post-types.php'), 'utf8');
const taxonomies = readFileSync(resolve(pluginDirectory, 'includes/taxonomies.php'), 'utf8');
const queries = readFileSync(resolve(pluginDirectory, 'includes/queries.php'), 'utf8');
const routes = readFileSync(resolve(pluginDirectory, 'includes/routes.php'), 'utf8');
const settings = readFileSync(resolve(pluginDirectory, 'includes/settings.php'), 'utf8');
const admin = readFileSync(resolve(pluginDirectory, 'includes/admin.php'), 'utf8');
const migration = readFileSync(resolve(pluginDirectory, 'includes/migration.php'), 'utf8');
const themeStyle = readFileSync(resolve(themeDirectory, 'style.css'), 'utf8');
const eventsData = readFileSync(resolve(themeDirectory, 'inc/events-data.php'), 'utf8');
const landingData = readFileSync(resolve(themeDirectory, 'inc/landing-data.php'), 'utf8');

if (!/Version:\s*1\.2\.0/.test(rootPlugin) || !rootPlugin.includes("define( 'NICE_CORE_VERSION', '1.2.0' )")) {
	fail('NICE Core must declare version 1.2.0 consistently.');
}

for (const postType of ['nice_service', 'nice_case_study', 'nice_client', 'nice_team_member']) {
	if (postType.length > 20 || !postTypes.includes(`'${postType}'`)) {
		fail(`Invalid or missing post type: ${postType}`);
	}
}

for (const taxonomy of ['nice_division', 'nice_service_type']) {
	if (!taxonomies.includes(`'${taxonomy}'`)) {
		fail(`Missing taxonomy: ${taxonomy}`);
	}
}

for (const term of [
	'Corporate Events',
	'Exhibitions & Conferences',
	'Activations & Promotions',
	'Corporate Videos',
	'Digital Content Creation',
	'Films & Entertainment',
]) {
	if (!taxonomies.includes(term)) {
		fail(`Missing approved service term: ${term}`);
	}
}

for (const helper of [
	'nice_get_services',
	'nice_get_events_services',
	'nice_get_studio_services',
	'nice_get_service_by_slug',
	'nice_get_services_by_service_type',
	'nice_get_case_studies',
	'nice_get_featured_case_studies',
	'nice_get_case_studies_by_service',
	'nice_get_clients',
	'nice_get_featured_clients',
	'nice_get_client_by_slug',
	'nice_get_team_members',
	'nice_get_team_members_by_division',
]) {
	if (!queries.includes(`function ${helper}`)) {
		fail(`Missing query helper: ${helper}`);
	}
}

for (const helper of [
	'nice_get_contact_settings',
	'nice_get_contact_whatsapp_url',
	'nice_get_contact_email',
	'nice_get_contact_phone_url',
	'nice_get_social_links',
]) {
	if (!settings.includes(`function ${helper}`)) {
		fail(`Missing contact helper: ${helper}`);
	}
}

for (const control of ['current_user_can', 'wp_verify_nonce', 'sanitize_text_field', 'nice_sanitize_https_url']) {
	if (!admin.includes(control) && !settings.includes(control)) {
		fail(`Admin validation is missing: ${control}`);
	}
}

if (!migration.includes("WP_CLI::add_command( 'nice migrate-content'")) {
	fail('The idempotent NICE content migration command is missing.');
}

for (const route of ['^events/services/([^/]+)/?$', '^events/case-studies/([^/]+)/?$']) {
	if (!routes.includes(route)) {
		fail(`Missing controlled Events route: ${route}`);
	}
}

if (!routes.includes('nice_get_events_content_url') || !routes.includes('nice_enforce_content_routes')) {
	fail('Events content routes must publish canonical URLs and reject raw routes.');
}

if (!migration.includes('nice_provision_events_pages') || !migration.includes('page-events-contact')) {
	fail('The migration must provision the five approved Events Pages.');
}

if (!migration.includes('nice_provision_studio_page')) {
	fail('The migration must provision only the Studio Home Page for Phase 7.');
}

for (const slug of ['corporate-videos', 'digital-content-creation', 'films-entertainment', 'strata-geosystems-factory-shoot', 'career-agents-academy', 'krish-e', 'crisil-financial-literacy-content', 'jayanti']) {
	if (!migration.includes(`'slug'         => '${slug}'`)) {
		fail(`Studio migration record is missing: ${slug}`);
	}
}

if (/\b(dbDelta|CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE)\b/i.test(source)) {
	fail('NICE Core must not create or alter custom database tables.');
}

if (/wp_enqueue_(script|style)\s*\(/.test(source)) {
	fail('NICE Core must not add frontend or admin assets in Phase 5.');
}

if (/react|vue|next\.js|tailwind|bootstrap|gsap|jquery/i.test(source)) {
	fail('NICE Core must not introduce a frontend framework dependency.');
}

if (!eventsData.includes("function_exists( 'nice_get_events_services' )") || !landingData.includes("function_exists( 'nice_get_case_study_by_slug' )")) {
	fail('Theme adapters must detect NICE Core and preserve fallbacks.');
}

if (!/Version:\s*0\.7\.0/.test(themeStyle)) {
	fail('The NICE theme must declare Phase 7.1 version 0.7.0.');
}

console.log(`Validated NICE Core 1.2.0 structure, ${requiredFiles.length} files, CMS boundaries, routes, and theme fallbacks.`);
