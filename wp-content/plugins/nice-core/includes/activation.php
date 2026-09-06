<?php
/**
 * NICE Core lifecycle hooks.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register content structures, create approved terms, and refresh rewrites.
 */
function nice_core_activate() {
	nice_register_post_types();
	nice_register_taxonomies();
	nice_register_content_meta();
	nice_register_content_rewrite_rules();
	nice_ensure_default_terms();
	flush_rewrite_rules();
}

/**
 * Refresh rewrites without deleting content or settings.
 */
function nice_core_deactivate() {
	flush_rewrite_rules();
}
