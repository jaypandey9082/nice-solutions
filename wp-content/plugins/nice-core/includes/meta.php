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
 * @param array|null $items    Item schema, for an array field.
 */
function nice_register_post_meta_field( $post_type, $meta_key, $type, $sanitize, $default, $authorize = 'nice_authorize_post_meta', $show_in_rest = true, $items = null ) {
	$rest_schema = false;

	if ( $show_in_rest ) {
		$schema = array(
			'type'    => $type,
			'default' => $default,
		);

		/*
		 * An array has to describe what it holds or REST rejects the field. Only
		 * the array case needs this, so it stays optional rather than becoming a
		 * parameter every scalar registration has to pass.
		 */
		if ( 'array' === $type && $items ) {
			$schema['items'] = $items;
		}

		$rest_schema = array( 'schema' => $schema );
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
 * Keep a gallery to real images, in the editor's order, within the cap.
 *
 * Every id is checked against the media library rather than trusted: an id that
 * no longer resolves to an image is a deleted attachment or a guess, and either
 * way it would render as a broken frame on a published page.
 *
 * Order is the editor's and is preserved. Duplicates are dropped, because the
 * same photograph twice in one gallery is a mistake every time.
 *
 * @param mixed $value Candidate attachment ids.
 * @return int[]
 */
function nice_sanitize_gallery_ids( $value ) {
	if ( is_string( $value ) ) {
		$value = explode( ',', $value );
	}

	if ( ! is_array( $value ) ) {
		return array();
	}

	$ids = array();

	foreach ( $value as $candidate ) {
		$id = absint( $candidate );

		if ( ! $id || in_array( $id, $ids, true ) ) {
			continue;
		}

		if ( 'attachment' !== get_post_type( $id ) || ! wp_attachment_is_image( $id ) ) {
			continue;
		}

		$ids[] = $id;

		if ( count( $ids ) >= NICE_GALLERY_MAX ) {
			break;
		}
	}

	return $ids;
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

	/*
	 * The gallery shares the hero image's approval gate rather than carrying its
	 * own. One tick governs every photograph on the record, so there is no state
	 * where a hero is cleared and the gallery beneath it is not.
	 */
	nice_register_post_meta_field(
		'nice_case_study',
		'_nice_gallery_ids',
		'array',
		'nice_sanitize_gallery_ids',
		array(),
		'nice_authorize_post_meta',
		true,
		array( 'type' => 'integer' )
	);

	nice_register_post_meta_field( 'nice_client', '_nice_client_url', 'string', 'nice_sanitize_https_url', '' );
	nice_register_post_meta_field( 'nice_client', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );
	nice_register_post_meta_field( 'nice_client', '_nice_featured', 'boolean', 'rest_sanitize_boolean', false );

	nice_register_post_meta_field( 'nice_team_member', '_nice_role', 'string', 'sanitize_text_field', '' );
	nice_register_post_meta_field( 'nice_team_member', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0 );

	/*
	 * Per-person contact points shown on the About page.
	 *
	 * The address field is named _nice_public_email rather than _nice_email on
	 * purpose. The company profile lists each director's personal work address,
	 * and a field called "email" invites an editor to paste one in without
	 * weighing that up. The name says out loud that whatever is stored here
	 * gets published.
	 *
	 * The two profile URLs reuse the HTTPS-only sanitizer the company social
	 * map already uses, so a person's links are held to the same standard as
	 * the site-wide ones.
	 */
	nice_register_post_meta_field( 'nice_team_member', '_nice_linkedin_url', 'string', 'nice_sanitize_https_url', '' );
	nice_register_post_meta_field( 'nice_team_member', '_nice_instagram_url', 'string', 'nice_sanitize_https_url', '' );
	nice_register_post_meta_field( 'nice_team_member', '_nice_public_email', 'string', 'sanitize_email', '' );
}
