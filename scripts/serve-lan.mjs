/**
 * Serve the Local WordPress site to other devices on this WiFi network.
 *
 * Local runs two nginx layers: a router on :80 that picks a site by Host
 * header, and one nginx per site on a high loopback port. The router only
 * knows nice-solutions.local, so a request that arrives by IP gets a 404 from
 * it -- but each site's own nginx has a single server block and therefore
 * answers on any Host. This relays straight to that one and skips the router.
 *
 * A plain TCP pipe rather than an HTTP proxy, deliberately: nothing here needs
 * to read or rewrite a header, so there is nothing to get wrong about
 * keep-alive, chunked bodies, or upgrades. The Host the phone sent arrives
 * untouched, which is what the wp-config LAN block keys off to build matching
 * URLs.
 *
 * Usage:
 *   npm run serve:lan              # discover the site port, listen on 8080
 *   npm run serve:lan -- --port 9000 --target 10003
 */

import { createServer, connect } from 'node:net';
import { execSync } from 'node:child_process';
import { networkInterfaces } from 'node:os';

const argument = (name, fallback) => {
	const index = process.argv.indexOf(`--${name}`);
	return index === -1 ? fallback : process.argv[index + 1];
};

/** Ask the OS which loopback port Local's per-site nginx is listening on. */
const discoverSitePort = () => {
	const output = execSync('lsof -nP -iTCP -sTCP:LISTEN 2>/dev/null || true', { encoding: 'utf8' });
	const ports = output
		.split('\n')
		.filter((line) => line.startsWith('nginx') && line.includes('127.0.0.1:'))
		.map((line) => Number(line.match(/127\.0\.0\.1:(\d+)/)?.[1]))
		.filter((port) => Number.isInteger(port));

	return ports[0];
};

const lanAddress = () =>
	Object.values(networkInterfaces())
		.flat()
		.find((entry) => entry?.family === 'IPv4' && !entry.internal)?.address;

const listenPort = Number(argument('port', 8080));
const targetPort = Number(argument('target', discoverSitePort()));

if (!Number.isInteger(targetPort)) {
	console.error('Could not find the Local site nginx port. Is the site running in Local?');
	console.error('Pass it explicitly:  npm run serve:lan -- --target <port>');
	process.exit(1);
}

const server = createServer((incoming) => {
	const upstream = connect(targetPort, '127.0.0.1');

	incoming.pipe(upstream);
	upstream.pipe(incoming);

	/*
	 * A phone closing a tab mid-request, or Local restarting the site, both
	 * surface as a socket error. Tear down the pair and keep the relay up --
	 * one dropped connection is not a reason to stop serving the others.
	 */
	const teardown = () => {
		incoming.destroy();
		upstream.destroy();
	};

	incoming.on('error', teardown);
	upstream.on('error', teardown);
});

server.on('error', (error) => {
	if (error.code === 'EADDRINUSE') {
		console.error(`Port ${listenPort} is already in use. Try:  npm run serve:lan -- --port 8081`);
		process.exit(1);
	}

	throw error;
});

server.listen(listenPort, '0.0.0.0', () => {
	const address = lanAddress();

	console.log(`Relaying 0.0.0.0:${listenPort} -> 127.0.0.1:${targetPort}`);
	console.log('');
	console.log(`  On this WiFi, open:  http://${address ?? '<this-mac-ip>'}:${listenPort}`);
	console.log('');
	console.log('Stop with Ctrl+C. macOS may ask to allow incoming connections the first time.');
});
