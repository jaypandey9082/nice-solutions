<?php
/**
 * Theme supports and editor configuration.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure theme supports.
 */
function nice_theme_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 369,
			'width'       => 1080,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'editor-styles' );
	add_editor_style(
		array(
			'assets/css/site.css',
			'assets/css/landing.css',
			'assets/css/events.css',
			'assets/css/editor.css',
		)
	);
}
add_action( 'after_setup_theme', 'nice_theme_setup' );

/**
 * Add a stable body hook for global theme styles.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function nice_theme_body_classes( $classes ) {
	$classes[] = 'nice-theme';

	if ( is_front_page() ) {
		$classes[] = 'nice-is-front-page';
	}

	if ( is_page( 'events' ) ) {
		$classes[] = 'nice-is-events-page';
	}

	return $classes;
}
add_filter( 'body_class', 'nice_theme_body_classes' );
