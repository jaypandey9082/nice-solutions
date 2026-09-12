/**
 * Runner for the phase check suites.
 *
 * Each scripts/playwright-phase*.js file is a bare `async (page) => {}`
 * expression rather than an executable script, so running one with node
 * evaluates the expression and exits 0 without ever calling it. This runner
 * supplies the Playwright page they expect and reports the real result.
 *
 * Usage:
 *   node scripts/run-playwright-phase.js                 # every phase suite
 *   node scripts/run-playwright-phase.js 7 8             # selected suites
 *   node scripts/run-playwright-phase.js scripts/playwright-phase7-check.js
 *
 * Phase 2 checks the static preview rather than the WordPress site, so it needs
 * `npm run preview:serve` running in another shell.
 */

const fs = require("fs");
const path = require("path");
const { spawnSync } = require("child_process");
const { chromium } = require("playwright");

const scriptsDir = __dirname;

const resolveSuites = (args) => {
	if (!args.length) {
		return fs
			.readdirSync(scriptsDir)
			.filter((name) => /^playwright-phase.*-check\.js$/.test(name))
			.sort()
			.map((name) => path.join(scriptsDir, name));
	}

	return args.map((arg) => {
		if (arg.endsWith(".js")) return path.resolve(arg);

		const slug = arg.replace(/\./g, "-");
		return path.join(scriptsDir, `playwright-phase${slug}-check.js`);
	});
};

/* Suites that drive themselves rather than accepting a page. */
const isStandalone = (source) => /require\(["']playwright["']\)/.test(source);

(async () => {
	const suites = resolveSuites(process.argv.slice(2));
	const browser = await chromium.launch({ headless: true });
	const failures = [];

	try {
		for (const suite of suites) {
			const name = path.basename(suite);

			if (!fs.existsSync(suite)) {
				failures.push(name);
				console.log(`${name.padEnd(38)} MISSING`);
				continue;
			}

			const source = fs.readFileSync(suite, "utf8");

			/*
			 * A standalone suite drives its own browser, so it runs as a child
			 * process rather than being handed a page. It must still run: skipping
			 * it and then reporting "all passed" is how it went unexercised.
			 */
			if (isStandalone(source)) {
				const run = spawnSync(process.execPath, [suite], {
					stdio: "inherit",
					env: { ...process.env, NODE_PATH: path.resolve(scriptsDir, "../node_modules") },
				});

				if (run.status === 0) {
					console.log(`${name.padEnd(38)} PASS (standalone)`);
				} else {
					failures.push(name);
					console.log(`${name.padEnd(38)} FAIL (standalone, exit ${run.status})`);
				}

				continue;
			}

			let suiteFn;
			try {
				/* The trailing semicolon would make the wrapped expression invalid. */
				suiteFn = eval(`(${source.trim().replace(/;+$/, "")})`);
			} catch (error) {
				failures.push(name);
				console.log(`${name.padEnd(38)} LOAD ERROR ${error.message}`);
				continue;
			}

			const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
			const page = await context.newPage();

			try {
				await suiteFn(page);
				console.log(`${name.padEnd(38)} PASS`);
			} catch (error) {
				failures.push(name);
				console.log(`${name.padEnd(38)} FAIL -> ${String(error.message).split("\n")[0]}`);
			} finally {
				await context.close();
			}
		}
	} finally {
		await browser.close();
	}

	if (failures.length) {
		console.error(`\n${failures.length} suite(s) failed: ${failures.join(", ")}`);
		process.exit(1);
	}

	console.log("\nAll phase suites passed.");
})();
