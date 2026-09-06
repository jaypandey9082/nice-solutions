<?php
/**
 * Plugin Name: NICE Core
 * Plugin URI: https://nicesolutions.in/
 * Description: Content types, taxonomies, metadata, settings, and query helpers for NICE Solutions.
 * Version: 1.0.0
 * Author: NICE Solutions
 * Text Domain: nice-core
 * Requires at least: 6.6
 * Requires PHP: 8.2
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NICE_CORE_VERSION', '1.0.0' );
define( 'NICE_CORE_FILE', __FILE__ );
define( 'NICE_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once NICE_CORE_DIR . 'includes/helpers.php';
require_once NICE_CORE_DIR . 'includes/post-types.php';
require_once NICE_CORE_DIR . 'includes/taxonomies.php';
require_once NICE_CORE_DIR . 'includes/meta.php';
require_once NICE_CORE_DIR . 'includes/settings.php';
require_once NICE_CORE_DIR . 'includes/queries.php';
require_once NICE_CORE_DIR . 'includes/admin.php';
require_once NICE_CORE_DIR . 'includes/migration.php';
require_once NICE_CORE_DIR . 'includes/activation.php';

add_action( 'init', 'nice_register_post_types', 5 );
add_action( 'init', 'nice_register_taxonomies', 6 );
add_action( 'init', 'nice_register_content_meta', 7 );

register_activation_hook( NICE_CORE_FILE, 'nice_core_activate' );
register_deactivation_hook( NICE_CORE_FILE, 'nice_core_deactivate' );
