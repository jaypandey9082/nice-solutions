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
 * @param callable $authorize    Authorization callback.
 * @param bool     $show_in_rest Whether the field is exposed through REST.
 */
function nice_register_post_meta_field( $post_type, $meta_key, $type, $sanitize, $default, $authorize = 'nice_authorize_post_meta', $show_in_rest = true ) {
	$rest_schema = false;

	if ( $show_in_rest ) {
		$rest_schema = array(
			'schema' => array(
				'type'    => $type,
				'default' => $default,
			),
		);
	}

	register_post_meta(
		$post_type,
		$meta_key,
		array(
			'type'              => $type,
			'single'            => true,
			'default'           => $default,
			'sanitize_callback' => $sanitize,
			'auth_callback'     => $authorize,
			'show_in_rest'      => $rest_schema,
		)
	);
}

/**
 * Return a supported editorial approval state.
 *
 * @param mixed $value Candidate approval state.
 * @return string
 */
function nice_sanitize_case_study_approval_status( $value ) {
	$status = sanitize_key( (string) $value );

	return in_array( $status, array( 'draft', 'review', 'approved' ), true ) ? $status : 'draft';
}

/**
 * Restrict private source metadata to editors of Case Study records.
 *
 * @param bool   $allowed   Existing decision.
 * @param string $meta_key  Meta key.
 * @param int    $object_id Case Study ID.
 * @return bool
 */
function nice_authorize_case_study_source_meta( $allowed, $meta_key, $object_id ) {
	return 'nice_case_study' === get_post_type( $object_id ) && current_user_can( 'edit_post', $object_id );
}

/**
 * Register metadata for Case Studies, Clients, and Team Members.
 */
function nice_register_content_meta() {
	nice_register_post_meta_field( 'page', '_nice_events_hero_image_id', 'integer', 'nice_sanitize_hero_image_id', 0, 'nice_authorize_events_hero_meta' );
	nice_register_post_meta_field( 'page', '_nice_events_hero_mobile_image_id', 'integer', 'nice_sanitize_hero_image_id', 0, 'nice_authorize_events_hero_meta' );
	nice_register_post_meta_field( 'page', '_nice_events_hero_focal_x', 'integer', 'nice_sanitize_percentage', 50, 'nice_authorize_events_hero_meta' );
	nice_register_post_meta_field( 'page', '_nice_events_hero_focal_y', 'integer', 'nice_sanitize_percentage', 50, 'nice_authorize_events_hero_meta' );
	nice_register_post_meta_field( 'page', '_nice_events_hero_reference', 'boolean', 'rest_sanitize_boolean', false, 'nice_authorize_events_hero_meta' );
	nice_register_post_meta_field( 'page', '_nice_events_hero_media_initialized', 'boolean', 'rest_sanitize_boolean', false, 'nice_authorize_events_hero_meta' );

	nice_register_post_meta_field( 'page', '_nice_studio_hero_image_id', 'integer', 'absint', 0 );
	nice_register_post_meta_field( 'page', '_nice_studio_hero_mobile_image_id', 'integer', 'absint', 0 );
	nice_register_post_meta_field( 'page', '_nice_studio_hero_focal_x', 'integer', 'nice_sanitize_percentage', 50 );
	nice_register_post_meta_field( 'page', '_nice_studio_hero_focal_y', 'integer', 'nice_sanitize_percentage', 50 );
	nice_register_post_meta_field( 'page', '_nice_studio_hero_reference', 'boolean', 'rest_sanitize_boolean', false );
	nice_register_post_meta_field( 'page', '_nice_studio_hero_media_initialized', 'boolean', 'rest_sanitize_boolean', false );

	nice_register_post_meta_field( 'nice_case_study', '_nice_client_id', 'integer', 'nice_sanitize_client_id', 0 );
	nice_register_post_meta_field( 'nice_case_study', '_nice_client_name', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_location', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_year', 'integer', 'nice_sanitize_year', 0 );
	nice_register_post_meta_field( 'nice_case_study', '_nice_featured', 'boolean', 'rest_sanitize_boolean', false );
	nice_register_post_meta_field( 'nice_case_study', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );
	nice_register_post_meta_field( 'nice_case_study', '_nice_reference_url', 'string', 'nice_sanitize_https_url', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_proof_value', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_proof_label', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_hero_video_url', 'string', 'nice_sanitize_https_url', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_quote_text', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_quote_author', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_case_study', '_nice_source_url', 'string', 'nice_sanitize_https_url', '', 'nice_authorize_case_study_source_meta', false );
	nice_register_post_meta_field( 'nice_case_study', '_nice_source_note', 'string', 'sanitize_textarea_field', '', 'nice_authorize_case_study_source_meta', false );
	nice_register_post_meta_field( 'nice_case_study', '_nice_source_approval_status', 'string', 'nice_sanitize_case_study_approval_status', 'draft', 'nice_authorize_case_study_source_meta', false );
	/*
	 * Where the wording came from. A record seeded from LinkedIn has to cite a
	 * LinkedIn post, so swapping in an unrelated address cannot clear it.
	 */
	nice_register_post_meta_field( 'nice_case_study', '_nice_source_origin', 'string', 'sanitize_key', '', 'nice_authorize_case_study_source_meta', false );
	/*
	 * Deliberately separate from the source approval above. That one clears the
	 * wording and its provenance; this one clears the right to publish the
	 * attached photograph. Migrated records carry deck imagery that has not been
	 * cleared, so approving the copy must never publish the picture with it.
	 */
	nice_register_post_meta_field( 'nice_case_study', '_nice_media_approved', 'boolean', 'rest_sanitize_boolean', false, 'nice_authorize_case_study_source_meta', false );

	nice_register_post_meta_field( 'nice_client', '_nice_client_url', 'string', 'nice_sanitize_https_url', '' );
	nice_register_post_meta_field( 'nice_client', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );
	nice_register_post_meta_field( 'nice_client', '_nice_featured', 'boolean', 'rest_sanitize_boolean', false );

	nice_register_post_meta_field( 'nice_team_member', '_nice_role', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_team_member', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );
}
