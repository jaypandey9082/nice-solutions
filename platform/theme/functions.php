<?php
/** Shared presentation for the three independently installed NICE sites. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/inc/presentation.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/main.css' );
	add_theme_support( 'html5', array( 'style', 'script', 'gallery', 'caption' ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	$base = get_template_directory_uri();
	foreach ( array( 'main', 'motion' ) as $file ) {
		$path = '/assets/css/' . $file . '.css';
		if ( file_exists( __DIR__ . $path ) ) {
			wp_enqueue_style( 'nice-platform-' . $file, $base . $path, array(), (string) filemtime( __DIR__ . $path ) );
		}
	}
	$script = '/assets/js/interactions.js';
	if ( file_exists( __DIR__ . $script ) ) {
		wp_enqueue_script( 'nice-platform-interactions', $base . $script, array(), (string) filemtime( __DIR__ . $script ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
} );

add_action( 'wp_head', function () {
	foreach ( array( 'montserrat-latin', 'cabin-latin' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>', esc_url( get_template_directory_uri() . '/assets/fonts/' . $font . '.woff2' ) );
	}
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" type="image/png">', esc_url( get_template_directory_uri() . '/assets/images/nice-site-icon.png' ) );
	}
}, 2 );

add_action( 'init', function () {
	foreach ( array( 'header', 'gateway', 'footer', 'page' ) as $part ) {
		register_block_type( 'nice-platform/' . $part, array(
			'api_version' => 3,
			'render_callback' => 'nice_platform_render_' . $part,
		) );
	}
} );
