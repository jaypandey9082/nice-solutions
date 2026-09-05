<?php
/**
 * Reusable styles for core WordPress blocks.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme-owned block style choices.
 */
function nice_theme_register_block_styles() {
	$styles = array(
		'core/button' => array(
			array(
				'name'  => 'nice-primary',
				'label' => __( 'NICE Primary', 'nice' ),
			),
			array(
				'name'  => 'nice-secondary',
				'label' => __( 'NICE Secondary', 'nice' ),
			),
			array(
				'name'  => 'nice-text',
				'label' => __( 'NICE Text Link', 'nice' ),
			),
		),
		'core/group'  => array(
			array(
				'name'  => 'nice-section',
				'label' => __( 'NICE Section', 'nice' ),
			),
			array(
				'name'  => 'nice-section-compact',
				'label' => __( 'NICE Compact Section', 'nice' ),
			),
		),
		'core/image'  => array(
			array(
				'name'  => 'nice-editorial',
				'label' => __( 'NICE Editorial', 'nice' ),
			),
			array(
				'name'  => 'nice-portrait',
				'label' => __( 'NICE Portrait', 'nice' ),
			),
			array(
				'name'  => 'nice-square',
				'label' => __( 'NICE Square', 'nice' ),
			),
			array(
				'name'  => 'nice-full-bleed',
				'label' => __( 'NICE Full Bleed', 'nice' ),
			),
		),
	);

	foreach ( $styles as $block_name => $block_styles ) {
		foreach ( $block_styles as $block_style ) {
			register_block_style( $block_name, $block_style );
		}
	}
}
add_action( 'init', 'nice_theme_register_block_styles' );

