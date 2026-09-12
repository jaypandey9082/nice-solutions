<?php
/**
 * Shared NICE Core validation and content helpers.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a valid HTTPS URL or an empty string.
 *
 * @param mixed $value Candidate URL.
 * @return string
 */
function nice_sanitize_https_url( $value ) {
	$url = esc_url_raw( (string) $value, array( 'https' ) );

	return $url && 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) ? $url : '';
}

/**
 * Sanitize an integer meta value.
 *
 * @param mixed $value Candidate value.
 * @return int
 */
function nice_sanitize_integer( $value ) {
	return is_numeric( $value ) ? (int) $value : 0;
}

/**
 * Sanitize a percentage used for media focal positioning.
 *
 * @param mixed $value Candidate percentage.
 * @return int
 */
function nice_sanitize_percentage( $value ) {
	return max( 0, min( 100, nice_sanitize_integer( $value ) ) );
}

/**
 * Return whether a Page is the top-level Studio home.
 *
 * @param int $post_id Candidate Page ID.
 * @return bool
 */
function nice_is_studio_home_page( $post_id ) {
	$post = get_post( $post_id );

	return $post instanceof WP_Post
		&& 'page' === $post->post_type
		&& 0 === (int) $post->post_parent
		&& 'studio' === $post->post_name;
}

/**
 * Return whether a Page is the top-level Events home.
 *
 * @param int $post_id Candidate Page ID.
 * @return bool
 */
function nice_is_events_home_page( $post_id ) {
	$post = get_post( $post_id );

	return $post instanceof WP_Post
		&& 'page' === $post->post_type
		&& 0 === (int) $post->post_parent
		&& 'events' === $post->post_name;
}

/**
 * Accept only existing image attachments, or zero for an empty selection.
 *
 * @param mixed $value Candidate attachment ID.
 * @return int
 */
function nice_sanitize_hero_image_id( $value ) {
	if ( ! is_int( $value ) && ! is_string( $value ) ) {
		return 0;
	}
	$id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );

	return $id && 'attachment' === get_post_type( $id ) && wp_attachment_is_image( $id ) ? $id : 0;
}

/**
 * Restrict Events hero REST edits to editors of the top-level Events Page.
 *
 * @param bool   $allowed   Existing decision.
 * @param string $meta_key  Meta key.
 * @param int    $object_id Page ID.
 * @return bool
 */
function nice_authorize_events_hero_meta( $allowed, $meta_key, $object_id ) {
	return nice_is_events_home_page( $object_id ) && current_user_can( 'edit_post', $object_id );
}

/**
 * Sanitize an optional four-digit year.
 *
 * @param mixed $value Candidate year.
 * @return int
 */
function nice_sanitize_year( $value ) {
	$year = nice_sanitize_integer( $value );

	return $year >= 1000 && $year <= 9999 ? $year : 0;
}

/**
 * Sanitize a post relationship to a Client record.
 *
 * @param mixed $value Candidate post ID.
 * @return int
 */
function nice_sanitize_client_id( $value ) {
	$client_id = absint( $value );

	return $client_id && 'nice_client' === get_post_type( $client_id ) ? $client_id : 0;
}

/**
 * Authorize edits to registered NICE post meta.
 *
 * @param bool   $allowed   Existing decision.
 * @param string $meta_key  Meta key.
 * @param int    $object_id Post ID.
 * @return bool
 */
function nice_authorize_post_meta( $allowed, $meta_key, $object_id ) {
	return current_user_can( 'edit_post', $object_id );
}

/**
 * Resolve a case study's related client name.
 *
 * @param int $case_study_id Case Study post ID.
 * @return string
 */
function nice_get_case_study_client_name( $case_study_id ) {
	$client_id = nice_sanitize_client_id( get_post_meta( $case_study_id, '_nice_client_id', true ) );

	if ( $client_id ) {
		return get_the_title( $client_id );
	}

	return sanitize_text_field( (string) get_post_meta( $case_study_id, '_nice_client_name', true ) );
}
