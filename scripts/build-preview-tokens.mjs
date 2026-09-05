import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptsDirectory = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(scriptsDirectory, '..');
const themeDirectory = resolve(projectRoot, 'wp-content/themes/nice');
const theme = JSON.parse(readFileSync(resolve(themeDirectory, 'theme.json'), 'utf8'));
const variables = [];

const addPresetVariables = (items, prefix, valueKey) => {
	for (const item of items ?? []) {
		variables.push(`\t--wp--preset--${prefix}--${item.slug}: ${item[valueKey]};`);
	}
};

const addCustomVariables = (object, path = []) => {
	for (const [key, value] of Object.entries(object ?? {})) {
		const nextPath = [...path, key];

		if (value && typeof value === 'object' && !Array.isArray(value)) {
			addCustomVariables(value, nextPath);
			continue;
		}

		variables.push(`\t--wp--custom--${nextPath.join('--')}: ${value};`);
	}
};

addPresetVariables(theme.settings?.color?.palette, 'color', 'color');
addPresetVariables(theme.settings?.typography?.fontFamilies, 'font-family', 'fontFamily');
addPresetVariables(theme.settings?.typography?.fontSizes, 'font-size', 'size');
addPresetVariables(theme.settings?.spacing?.spacingSizes, 'spacing', 'size');
addCustomVariables(theme.settings?.custom);

const output = [
	'/* Generated from ../theme.json by scripts/build-preview-tokens.mjs. */',
	':root {',
	...variables,
	'}',
	'',
].join('\n');

writeFileSync(resolve(themeDirectory, 'preview/tokens.css'), output);
console.log(`Generated ${variables.length} preview token variables.`);

