<?php
/**
 * Which division this installation serves, and where the others live.
 *
 * The repository install is a single combined site: Events and Studio content
 * sits under the /events/ and /studio/ path prefixes. Production splits the same
 * code across three installations, where each division owns its own hostname and
 * its content sits at the root with no prefix at all.
 *
 * Everything that builds or matches a division path reads from here, so the two
 * shapes differ by configuration rather than by code.
 *
 * Configure a division installation in wp-config.php:
 *
 *     define( 'NICE_SITE_DIVISION', 'events' );
 *
 * and tell it where its siblings live, so cross-site links resolve:
 *
 *     define( 'NICE_MAIN_SITE_URL', 'https://nicesolutions.in' );
 *     define( 'NICE_EVENTS_SITE_URL', 'https://events.nicesolutions.in' );
 *     define( 'NICE_STUDIO_SITE_URL', 'https://studios.nicesolutions.in' );
 *
 * With nothing defined the behaviour is the combined site, unchanged.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divisions this codebase knows about.
 *
 * @return string[]
 */
function nice_get_division_slugs() {
	return array( 'events', 'studio' );
}

/**
 * Return the division this installation serves.
 *
 * An empty string means the combined site, which serves every division behind
 * its path prefix. That is the default and the repository's own configuration.
 *
 * @return string Division slug, or an empty string for the combined site.
 */
function nice_get_site_division() {
	$division = defined( 'NICE_SITE_DIVISION' ) ? sanitize_key( (string) NICE_SITE_DIVISION ) : '';

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		$division = '';
	}

	/**
	 * Filter the division this installation serves.
	 *
	 * @param string $division Division slug, or an empty string for the combined site.
	 */
	return (string) apply_filters( 'nice_site_division', $division );
}

/**
 * Report whether this installation serves only one division.
 *
 * @return bool
 */
function nice_is_division_site() {
	return '' !== nice_get_site_division();
}

/**
 * Report whether a division's content is served by this installation.
 *
 * The combined site serves both. A division site serves only its own, and links
 * to the other one by absolute URL.
 *
 * @param string $division Division slug.
 * @return bool
 */
function nice_division_is_local( $division ) {
	$division = sanitize_key( $division );

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return false;
	}

	$site_division = nice_get_site_division();

	return '' === $site_division || $site_division === $division;
}

/**
 * Return the absolute site URLs configured for each division and the gateway.
 *
 * @return array<string, string> Keys are division slugs plus 'main'.
 */
function nice_get_division_site_urls() {
	$urls = array(
		'main'   => defined( 'NICE_MAIN_SITE_URL' ) ? (string) NICE_MAIN_SITE_URL : '',
		'events' => defined( 'NICE_EVENTS_SITE_URL' ) ? (string) NICE_EVENTS_SITE_URL : '',
		'studio' => defined( 'NICE_STUDIO_SITE_URL' ) ? (string) NICE_STUDIO_SITE_URL : '',
	);

	$stored = get_option( 'nice_division_site_urls', array() );
	if ( is_array( $stored ) ) {
		foreach ( $stored as $key => $value ) {
			$key = sanitize_key( $key );

			if ( isset( $urls[ $key ] ) && ! $urls[ $key ] ) {
				$urls[ $key ] = (string) $value;
			}
		}
	}

	foreach ( $urls as $key => $value ) {
		$value = $value ? esc_url_raw( untrailingslashit( trim( $value ) ) ) : '';

		$urls[ $key ] = $value;
	}

	/**
	 * Filter the absolute URLs of the sibling installations.
	 *
	 * @param array<string, string> $urls Keys are division slugs plus 'main'.
	 */
	return (array) apply_filters( 'nice_division_site_urls', $urls );
}

/**
 * Return the path prefix a division's content sits behind on this installation.
 *
 * The combined site keeps the division slug as the prefix. A division site owns
 * its hostname, so its own content sits at the root with no prefix.
 *
 * @param string $division Division slug.
 * @return string Prefix without surrounding slashes, or an empty string.
 */
function nice_get_division_prefix( $division ) {
	$division = sanitize_key( $division );

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return '';
	}

	$prefix = nice_get_site_division() === $division ? '' : $division;

	/**
	 * Filter the path prefix used for a division on this installation.
	 *
	 * @param string $prefix   Prefix without surrounding slashes.
	 * @param string $division Division slug.
	 */
	return (string) apply_filters( 'nice_division_prefix', $prefix, $division );
}

/**
 * Build a URL for a path inside a division.
 *
 * Content served here resolves against this site. Content owned by a sibling
 * installation resolves against its configured URL, and falls back to the
 * combined-site shape when no sibling URL has been configured yet.
 *
 * @param string $division Division slug.
 * @param string $path     Path within the division, without a leading slash.
 * @return string
 */
function nice_get_division_url( $division, $path = '' ) {
	$division = sanitize_key( $division );
	$path     = ltrim( (string) $path, '/' );

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return '';
	}

	if ( ! nice_division_is_local( $division ) ) {
		$urls = nice_get_division_site_urls();

		if ( ! empty( $urls[ $division ] ) ) {
			return user_trailingslashit( $urls[ $division ] . '/' . $path );
		}
	}

	$prefix   = nice_get_division_prefix( $division );
	$relative = ltrim( $prefix . '/' . $path, '/' );

	return $relative ? home_url( user_trailingslashit( $relative ) ) : home_url( '/' );
}

/**
 * Return the URL of the main gateway site.
 *
 * @return string
 */
function nice_get_main_site_url() {
	$urls = nice_get_division_site_urls();

	if ( nice_is_division_site() && ! empty( $urls['main'] ) ) {
		return trailingslashit( $urls['main'] );
	}

	return home_url( '/' );
}

/**
 * Return the request path prefix that identifies a division, with slashes.
 *
 * On a division site every path belongs to that division, so the identifying
 * prefix is simply the site root.
 *
 * @param string $division Division slug.
 * @return string
 */
function nice_get_division_path_prefix( $division ) {
	$prefix = nice_get_division_prefix( $division );

	return $prefix ? '/' . $prefix . '/' : '/';
}
