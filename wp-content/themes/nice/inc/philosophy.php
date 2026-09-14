<?php
/**
 * Shared, server-rendered NICE philosophy strip.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the shared slogan without request-level state or duplicate IDs.
 *
 * @return string
 */
function nice_render_philosophy_strip() {
	/*
	 * The separators are drawn, not typed, and hidden from assistive technology:
	 * a screen reader announced the old hyphens, so the strip read as "Emagine
	 * hyphen Explore hyphen Execute". The words keep their real capitalisation
	 * in the markup and are uppercased by CSS, so what is copied and what is
	 * announced stays "Emagine", not "EMAGINE".
	 */
	return '<div class="nice-philosophy-strip"><p class="nice-philosophy-strip__text">'
		. '<span class="nice-philosophy-strip__item">Emagine</span>'
		. '<span class="nice-philosophy-strip__separator" aria-hidden="true"></span>'
		. '<span class="nice-philosophy-strip__item">Explore</span>'
		. '<span class="nice-philosophy-strip__separator" aria-hidden="true"></span>'
		. '<span class="nice-philosophy-strip__item">Execute</span>'
		. '</p></div>';
}

/**
 * Register the dynamic block used by templates and patterns.
 */
function nice_register_philosophy_strip_block() {
	register_block_type(
		'nice/philosophy-strip',
		array(
			'api_version'     => 3,
			'render_callback' => 'nice_render_philosophy_strip',
		)
	);
}
add_action( 'init', 'nice_register_philosophy_strip_block' );
