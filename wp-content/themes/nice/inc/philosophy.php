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
	return '<div class="nice-philosophy-strip"><p class="nice-philosophy-strip__text">'
		. '<span class="nice-philosophy-strip__item">Emagine</span> '
		. '<span class="nice-philosophy-strip__separator">-</span> '
		. '<span class="nice-philosophy-strip__item">Explore</span> '
		. '<span class="nice-philosophy-strip__separator">-</span> '
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
