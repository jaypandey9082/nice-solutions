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
	$nice_is_studio_context = function_exists( 'nice_theme_is_studio_context' ) && nice_theme_is_studio_context();
	$nice_is_studio_home    = function_exists( 'nice_theme_is_studio_home' ) && nice_theme_is_studio_home();

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

	wp_enqueue_style( 'nice-philosophy', get_theme_file_uri( '/assets/css/philosophy.css' ), array( 'nice-site' ), nice_theme_asset_version( '/assets/css/philosophy.css' ) );

	if ( is_front_page() || $nice_is_studio_context || $nice_is_events_context ) {
		wp_enqueue_style(
			'nice-editorial-foundation',
			get_theme_file_uri( '/assets/css/editorial-foundation.css' ),
			array( 'nice-site' ),
			nice_theme_asset_version( '/assets/css/editorial-foundation.css' )
		);
	}

	if ( is_front_page() ) {
		wp_enqueue_style(
			'nice-landing',
			get_theme_file_uri( '/assets/css/landing.css' ),
			array( 'nice-editorial-foundation' ),
			nice_theme_asset_version( '/assets/css/landing.css' )
		);
	}

	if ( $nice_is_events_context && ! $nice_is_events_page ) {
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
			array( 'nice-editorial-foundation', 'nice-events' ),
			nice_theme_asset_version( '/assets/css/events-inner.css' )
		);
	}

	/*
	 * Both divisions' About pages share one stylesheet, so it is enqueued on
	 * the page rather than on the division, and it depends on whichever inner
	 * sheet is already loading so its rules land after the division's.
	 */
	if ( is_page( 'about' ) && ( $nice_is_events_inner || $nice_is_studio_context ) ) {
		wp_enqueue_style(
			'nice-about',
			get_theme_file_uri( '/assets/css/about.css' ),
			array( $nice_is_events_inner ? 'nice-events-inner' : 'nice-studio' ),
			nice_theme_asset_version( '/assets/css/about.css' )
		);
	}

	/*
	 * Both divisions' case study pages share one gallery stylesheet, so it is
	 * enqueued on the record rather than on the division, and it depends on
	 * whichever inner sheet is already loading so its rules land after.
	 *
	 * Asked of the record itself, not just the route: a project with no approved
	 * gallery renders no gallery, and should not carry the stylesheet for one.
	 */
	if ( is_singular( 'nice_case_study' )
		&& ( $nice_is_events_inner || $nice_is_studio_context )
		&& function_exists( 'nice_theme_get_case_study_gallery' )
		&& nice_theme_get_case_study_gallery( get_queried_object_id() ) ) {
		wp_enqueue_style(
			'nice-gallery',
			get_theme_file_uri( '/assets/css/gallery.css' ),
			array( $nice_is_events_inner ? 'nice-events-inner' : 'nice-studio' ),
			nice_theme_asset_version( '/assets/css/gallery.css' )
		);
	}

	if ( $nice_is_events_page ) {
		wp_enqueue_style( 'nice-events-home', get_theme_file_uri( '/assets/css/events-home.css' ), array( 'nice-editorial-foundation' ), nice_theme_asset_version( '/assets/css/events-home.css' ) );
	}

	if ( $nice_is_studio_context ) {
		wp_enqueue_style(
			'nice-studio',
			get_theme_file_uri( '/assets/css/studio.css' ),
			array( 'nice-editorial-foundation' ),
			nice_theme_asset_version( '/assets/css/studio.css' )
		);
	}

	if ( $nice_is_studio_home ) {
		wp_enqueue_style(
			'nice-studio-home',
			get_theme_file_uri( '/assets/css/studio-home.css' ),
			array( 'nice-editorial-foundation', 'nice-studio' ),
			nice_theme_asset_version( '/assets/css/studio-home.css' )
		);
	}

	/*
	 * Site-wide rather than per-template. The reveal half returns immediately
	 * when a page has nothing to reveal, and the anchor half is needed wherever
	 * a link points at a section of the same page, which includes the header
	 * navigation on every page.
	 */
	wp_enqueue_script(
		'nice-motion',
		get_theme_file_uri( '/assets/js/motion.js' ),
		array(),
		nice_theme_asset_version( '/assets/js/motion.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
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
