<?php
/**
 * Frontend asset loading.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a cache-safe asset version.
 *
 * @param string $relative_path Theme-relative asset path.
 * @return string
 */
function nice_theme_asset_version( $relative_path ) {
	$file_path = get_theme_file_path( $relative_path );

	return file_exists( $file_path ) ? (string) filemtime( $file_path ) : '0.7.0';
}

/**
 * Load the small shared stylesheet and progressive enhancement scripts.
 */
function nice_theme_enqueue_assets() {
	$nice_is_events_page    = is_page( 'events' );
	$nice_is_events_context = function_exists( 'nice_theme_is_events_context' ) && nice_theme_is_events_context();
	$nice_is_events_inner   = function_exists( 'nice_theme_is_events_inner_page' ) && nice_theme_is_events_inner_page();
	$nice_is_studio_page    = function_exists( 'nice_theme_is_studio_home' ) && nice_theme_is_studio_home();

	wp_enqueue_style(
		'nice-site',
		get_theme_file_uri( '/assets/css/site.css' ),
		array(),
		nice_theme_asset_version( '/assets/css/site.css' )
	);

	wp_enqueue_script(
		'nice-navigation',
		get_theme_file_uri( '/assets/js/navigation.js' ),
		array(),
		nice_theme_asset_version( '/assets/js/navigation.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_enqueue_script(
		'nice-media',
		get_theme_file_uri( '/assets/js/media.js' ),
		array(),
		nice_theme_asset_version( '/assets/js/media.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_front_page() ) {
		wp_enqueue_style(
			'nice-landing',
			get_theme_file_uri( '/assets/css/landing.css' ),
			array( 'nice-site' ),
			nice_theme_asset_version( '/assets/css/landing.css' )
		);
	}

	if ( $nice_is_events_context ) {
		wp_enqueue_style(
			'nice-events',
			get_theme_file_uri( '/assets/css/events.css' ),
			array( 'nice-site' ),
			nice_theme_asset_version( '/assets/css/events.css' )
		);
	}

	if ( $nice_is_events_inner ) {
		wp_enqueue_style(
			'nice-events-inner',
			get_theme_file_uri( '/assets/css/events-inner.css' ),
			array( 'nice-site', 'nice-events' ),
			nice_theme_asset_version( '/assets/css/events-inner.css' )
		);
	}

	if ( $nice_is_studio_page ) {
		wp_enqueue_style(
			'nice-studio',
			get_theme_file_uri( '/assets/css/studio.css' ),
			array( 'nice-site' ),
			nice_theme_asset_version( '/assets/css/studio.css' )
		);
	}

	if ( is_front_page() || $nice_is_events_context || $nice_is_studio_page ) {
		wp_enqueue_script(
			'nice-reveal',
			get_theme_file_uri( '/assets/js/landing.js' ),
			array(),
			nice_theme_asset_version( '/assets/js/landing.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'nice_theme_enqueue_assets' );

/**
 * Provide the approved NICE mark until WordPress has a configured Site Icon.
 */
function nice_theme_fallback_site_icon() {
	if ( has_site_icon() ) {
		return;
	}

	printf(
		'<link rel="icon" href="%s" sizes="512x512" type="image/png">' . "\n",
		esc_url( get_theme_file_uri( '/assets/images/nice-site-icon.png' ) )
	);
}
add_action( 'wp_head', 'nice_theme_fallback_site_icon' );
