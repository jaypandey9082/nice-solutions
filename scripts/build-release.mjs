/**
 * Build the installable theme and plugin packages.
 *
 * Three WordPress installations are updated from these two files, so the build
 * has to be boring and repeatable: the same commit produces byte-identical
 * archives, and what goes in is decided here rather than by whatever happens to
 * be in the working tree.
 *
 * Deliberately excluded:
 *   - the repository's own machinery (.git, node_modules, scripts, checks)
 *   - the static preview, which exists for design review and not for a server
 *   - deck photography that has not been cleared for publication, listed in
 *     scripts/unapproved-media.json — the theme renders its placeholder when an
 *     image is absent, so leaving them out ships no unapproved photograph
 *
 * Usage: npm run build:release
 */

import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { cpSync, existsSync, mkdirSync, readFileSync, rmSync, statSync, writeFileSync, readdirSync, unlinkSync } from 'node:fs';
import { dirname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const releaseDirectory = resolve(projectRoot, 'output/releases');
const stagingDirectory = resolve(projectRoot, 'output/.release-staging');

const themeDirectory = resolve(projectRoot, 'wp-content/themes/nice');
const pluginDirectory = resolve(projectRoot, 'wp-content/plugins/nice-core');

const unapprovedMedia = new Set(JSON.parse(readFileSync(resolve(projectRoot, 'scripts/unapproved-media.json'), 'utf8')).files);

const readVersion = (file, pattern) => {
	const match = readFileSync(file, 'utf8').match(pattern);

	if (!match) {
		throw new Error(`Could not read a version from ${relative(projectRoot, file)}`);
	}

	return match[1];
};

const themeVersion = readVersion(resolve(themeDirectory, 'style.css'), /Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/);
const pluginVersion = readVersion(resolve(pluginDirectory, 'nice-core.php'), /Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/);

/* Names that never belong in a package, whatever directory they appear in. */
const excludedNames = new Set(['.git', '.github', '.DS_Store', 'node_modules', '.gitignore', 'Thumbs.db']);

/**
 * Report whether one path should be copied into a package.
 *
 * @param {string} source   Absolute source path.
 * @param {Set<string>} skipDirectories Directory names to drop, relative to the package root.
 * @param {string} packageRoot Absolute package root.
 * @returns {boolean}
 */
const shouldInclude = (source, skipDirectories, packageRoot) => {
	const name = source.split('/').pop();

	if (excludedNames.has(name) || name.startsWith('._')) return false;
	if (unapprovedMedia.has(name)) return false;

	const relativePath = relative(packageRoot, source);

	return !skipDirectories.has(relativePath);
};

/**
 * Stage one package directory, then zip it.
 *
 * @param {object} options Package description.
 * @returns {string} Absolute path of the archive.
 */
const buildPackage = ({ sourceDirectory, slug, version, skip }) => {
	const staged = join(stagingDirectory, slug);
	const skipDirectories = new Set(skip ?? []);

	rmSync(staged, { recursive: true, force: true });
	mkdirSync(staged, { recursive: true });

	cpSync(sourceDirectory, staged, {
		recursive: true,
		filter: (source) => shouldInclude(source, skipDirectories, sourceDirectory),
	});

	/*
	 * A fixed timestamp on every staged file is what makes two builds of the
	 * same commit produce identical bytes; zip records mtimes.
	 */
	execFileSync('find', [staged, '-exec', 'touch', '-t', '202001010000.00', '{}', '+']);

	const archive = join(releaseDirectory, `${slug}-${version}.zip`);
	rmSync(archive, { force: true });

	/* -X drops extra file attributes, which differ between machines. */
	execFileSync('zip', ['-q', '-r', '-X', '-9', archive, slug], { cwd: stagingDirectory });

	return archive;
};

rmSync(stagingDirectory, { recursive: true, force: true });
mkdirSync(releaseDirectory, { recursive: true });
mkdirSync(stagingDirectory, { recursive: true });

const archives = [
	buildPackage({
		sourceDirectory: themeDirectory,
		slug: 'nice-theme',
		version: themeVersion,
		skip: ['preview'],
	}),
	buildPackage({
		sourceDirectory: pluginDirectory,
		slug: 'nice-core',
		version: pluginVersion,
	}),
];

rmSync(stagingDirectory, { recursive: true, force: true });

/*
 * A package that is missing its entry file installs and then does nothing, and
 * the failure shows up on a production host rather than here. Check the
 * archives rather than the staging directory, because the archive is what ships.
 */
const requiredEntries = {
	'nice-theme': ['nice-theme/style.css', 'nice-theme/theme.json', 'nice-theme/functions.php', 'nice-theme/templates/front-page.html'],
	'nice-core': ['nice-core/nice-core.php', 'nice-core/readme.txt', 'nice-core/includes/sites.php', 'nice-core/includes/setup-screen.php'],
};

for (const archive of archives) {
	const listing = execFileSync('unzip', ['-Z1', archive]).toString();
	const slug = archive.split('/').pop().replace(/-[0-9.]+\.zip$/, '');

	for (const entry of requiredEntries[slug] ?? []) {
		if (!listing.includes(entry)) {
			throw new Error(`${archive.split('/').pop()} is missing ${entry}`);
		}
	}

	for (const name of unapprovedMedia) {
		if (listing.includes(name)) {
			throw new Error(`${archive.split('/').pop()} contains unapproved photography: ${name}`);
		}
	}
}

/* One checksum file per build, replacing whatever an earlier build left. */
for (const stale of readdirSync(releaseDirectory)) {
	if (stale === 'SHA256SUMS.txt' || stale === 'RELEASE-CHECKLIST.md') {
		unlinkSync(join(releaseDirectory, stale));
	}
}

const checksums = archives
	.map((archive) => `${createHash('sha256').update(readFileSync(archive)).digest('hex')}  ${archive.split('/').pop()}`)
	.join('\n');

writeFileSync(join(releaseDirectory, 'SHA256SUMS.txt'), `${checksums}\n`);

const checklist = `# NICE release ${themeVersion} / ${pluginVersion}

Built from \`${execFileSync('git', ['rev-parse', '--short', 'HEAD'], { cwd: projectRoot }).toString().trim()}\`.

| Package | Bytes |
| --- | --- |
${archives.map((archive) => `| ${archive.split('/').pop()} | ${statSync(archive).size.toLocaleString('en-US')} |`).join('\n')}

Verify with \`shasum -a 256 -c SHA256SUMS.txt\`.

## Before uploading

- [ ] \`npm run check\` and \`npm run check:phases\` pass on this commit.
- [ ] \`wp eval-file scripts/wp-identity-check.php\` passes.
- [ ] A file and database backup exists for the installation being changed, and one restore has been tested.
- [ ] The installation declares \`NICE_SITE_DIVISION\` and the three sibling URLs in \`wp-config.php\`.

## On each installation

- [ ] Upload and activate NICE Core, then the NICE theme.
- [ ] Run **Tools → NICE Setup**. It refuses if the identity is missing or unrecognised.
- [ ] Division installations: confirm the generated home page is the front page under Settings → Reading.
- [ ] Save Permalinks once.
- [ ] Enter that site's contact and social settings.
- [ ] Upload and approve that site's media. Deck photography is not in these packages: every record shows a placeholder until an approved image is attached and cleared.
- [ ] Leave search indexing disabled until the launch review.

## After

- [ ] Events and Studio records from the sibling division return 404.
- [ ] The gateway carries no \`/events/\` or \`/studio/\` path links.
- [ ] Contact, WhatsApp, email and social links open the right destination.
- [ ] Tag the tested candidate \`nice-launch-rc1\`, and the verified production commit \`nice-launch-v1\`.
`;

writeFileSync(join(releaseDirectory, 'RELEASE-CHECKLIST.md'), checklist);

for (const archive of archives) {
	console.log(`${relative(projectRoot, archive)}  ${statSync(archive).size.toLocaleString('en-US')} bytes`);
}
console.log(`${relative(projectRoot, join(releaseDirectory, 'SHA256SUMS.txt'))}`);
console.log(`${relative(projectRoot, join(releaseDirectory, 'RELEASE-CHECKLIST.md'))}`);
