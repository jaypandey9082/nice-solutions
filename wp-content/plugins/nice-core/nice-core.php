<?php
/**
 * Plugin Name: NICE Core
 * Plugin URI: https://nicesolutions.in/
 * Description: Content types, taxonomies, metadata, settings, and query helpers for NICE Solutions.
 * Version: 1.6.0
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

define( 'NICE_CORE_VERSION', '1.6.0' );

/*
 * How many images one Case Study gallery may hold. A cap rather than an open
 * list: ten is enough to tell the story of a project, and past that a page
 * stops being a case study and becomes an album nobody scrolls to the end of.
 */
define( 'NICE_GALLERY_MAX', 10 );
define( 'NICE_CORE_FILE', __FILE__ );
define( 'NICE_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once NICE_CORE_DIR . 'includes/helpers.php';
require_once NICE_CORE_DIR . 'includes/sites.php';
require_once NICE_CORE_DIR . 'includes/post-types.php';
require_once NICE_CORE_DIR . 'includes/taxonomies.php';
require_once NICE_CORE_DIR . 'includes/meta.php';
require_once NICE_CORE_DIR . 'includes/settings.php';
require_once NICE_CORE_DIR . 'includes/queries.php';
require_once NICE_CORE_DIR . 'includes/routes.php';
require_once NICE_CORE_DIR . 'includes/admin.php';
require_once NICE_CORE_DIR . 'includes/migration.php';
require_once NICE_CORE_DIR . 'includes/gateway-projects.php';
require_once NICE_CORE_DIR . 'includes/setup-screen.php';
require_once NICE_CORE_DIR . 'includes/activation.php';

add_action( 'init', 'nice_register_post_types', 5 );
add_action( 'init', 'nice_register_taxonomies', 6 );
add_action( 'init', 'nice_register_gateway_project_post_type', 5 );
add_action( 'init', 'nice_register_content_meta', 7 );
add_action( 'init', 'nice_register_gateway_project_meta', 7 );
add_action( 'init', 'nice_register_content_rewrite_rules', 8 );

register_activation_hook( NICE_CORE_FILE, 'nice_core_activate' );
register_deactivation_hook( NICE_CORE_FILE, 'nice_core_deactivate' );
