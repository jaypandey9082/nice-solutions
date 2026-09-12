<?php
/**
 * Plugin Name: NICE Platform Core
 * Description: Independent NICE site identity, local gateway content and native media controls.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Text Domain: nice-platform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NICE_PLATFORM_CORE_VERSION', '1.0.0' );
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/seed.php';

register_activation_hook( __FILE__, function () {
	nice_platform_register_content();
	flush_rewrite_rules( false );
} );
register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules( false );
} );
