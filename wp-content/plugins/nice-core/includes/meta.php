<?php
/**
 * Registered NICE content metadata.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a protected post-meta field with a REST schema.
 *
 * @param string   $post_type Post type.
 * @param string   $meta_key  Meta key.
 * @param string   $type      REST/meta type.
 * @param callable $sanitize  Sanitization callback.
 * @param mixed    $default   Default value.
 */
function nice_register_post_meta_field( $post_type, $meta_key, $type, $sanitize, $default ) {
	register_post_meta(
		$post_type,
		$meta_key,
		array(
			'type'              => $type,
			'single'            => true,
			'default'           => $default,
			'sanitize_callback' => $sanitize,
			'auth_callback'     => 'nice_authorize_post_meta',
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => $type,
					'default' => $default,
				),
			),
		)
	);
}

/**
 * Register metadata for Case Studies, Clients, and Team Members.
 */
function nice_register_content_meta() {
	nice_register_post_meta_field( 'nice_case_study', '_nice_client_id', 'integer', 'nice_sanitize_client_id', 0 );
	nice_register_post_meta_field( 'nice_case_study', '_nice_client_name', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_location', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_year', 'integer', 'nice_sanitize_year', 0 );
	nice_register_post_meta_field( 'nice_case_study', '_nice_featured', 'boolean', 'rest_sanitize_boolean', false );
	nice_register_post_meta_field( 'nice_case_study', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );
	nice_register_post_meta_field( 'nice_case_study', '_nice_reference_url', 'string', 'nice_sanitize_https_url', '' );

	nice_register_post_meta_field( 'nice_client', '_nice_client_url', 'string', 'nice_sanitize_https_url', '' );
	nice_register_post_meta_field( 'nice_client', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );
	nice_register_post_meta_field( 'nice_client', '_nice_featured', 'boolean', 'rest_sanitize_boolean', false );

	nice_register_post_meta_field( 'nice_team_member', '_nice_role', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_team_member', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );
}
