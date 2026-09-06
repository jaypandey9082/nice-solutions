<?php
/**
 * NICE Core preserves content and options on uninstall by design.
 *
 * @package NiceCore
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Content removal requires a separate, explicitly approved migration plan.
