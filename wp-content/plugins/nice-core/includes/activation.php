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
 *
 * Deliberately creates no pages and imports no media.
 *
 * Activation runs whenever an administrator clicks Activate, which on a new
 * host is usually before wp-config.php declares which installation this is.
 * Provisioning here therefore ran as the combined development site and built
 * the /events/ and /studio/ page tree on what was about to become the gateway.
 * Content now comes from one place only: Tools -> NICE Setup, or
 * wp nice migrate-content, both of which run after the identity is known.
 *
 * The approved vocabulary is safe to create here, because both divisions'
 * terms are the same on every installation.
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
