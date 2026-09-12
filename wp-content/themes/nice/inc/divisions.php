<?php
/**
 * Division-aware URL helpers for the theme.
 *
 * The combined installation serves Events and Studio behind path prefixes, while
 * each production installation owns a hostname and serves its own division at
 * the root. Templates ask for a division and a path rather than assembling one,
 * so the same markup is correct in both shapes.
 *
 * NICE Core owns the configuration. These wrappers keep the theme renderable
 * with the plugin inactive by falling back to the combined-site paths.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a URL for a path inside a division.
 *
 * @param string $division Division slug.
 * @param string $path     Path within the division, without a leading slash.
 * @return string
 */
function nice_theme_division_url( $division, $path = '' ) {
	if ( function_exists( 'nice_get_division_url' ) ) {
		return nice_get_division_url( $division, $path );
	}

	$relative = ltrim( $division . '/' . ltrim( (string) $path, '/' ), '/' );

	return home_url( user_trailingslashit( $relative ) );
}

/**
 * Return the URL of the main gateway site.
 *
 * @return string
 */
function nice_theme_main_url() {
	if ( function_exists( 'nice_get_main_site_url' ) ) {
		return nice_get_main_site_url();
	}

	return home_url( '/' );
}

/**
 * Return the division this installation serves.
 *
 * @return string Division slug, or an empty string for the combined site.
 */
function nice_theme_site_division() {
	return function_exists( 'nice_get_site_division' ) ? nice_get_site_division() : '';
}

/**
 * Report whether this installation serves a single division.
 *
 * @return bool
 */
function nice_theme_is_division_site() {
	return '' !== nice_theme_site_division();
}
