<?php
/**
 * Gateway preview adapter.
 *
 * NICE Core owns the records. The theme asks for them through this wrapper so
 * the front page still renders with the plugin inactive, and so the gateway
 * strip simply disappears when nothing is published rather than leaving an
 * empty section behind.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the published gateway previews.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_theme_get_gateway_projects() {
	if ( ! function_exists( 'nice_get_gateway_project_views' ) ) {
		return array();
	}

	return nice_get_gateway_project_views();
}
