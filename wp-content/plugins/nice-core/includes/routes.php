<?php
/**
 * Deliberate public routes for NICE content.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the approved content detail-route families.
 */
function nice_register_content_rewrite_rules() {
	// Events routes.
	add_rewrite_rule( '^events/services/([^/]+)/?$', 'index.php?nice_service=$matches[1]', 'top' );
	add_rewrite_rule( '^events/case-studies/([^/]+)/?$', 'index.php?nice_case_study=$matches[1]', 'top' );
	// Studio routes (templates not yet active — will 404 until Phase 8).
	add_rewrite_rule( '^studio/services/([^/]+)/?$', 'index.php?nice_service=$matches[1]', 'top' );
	add_rewrite_rule( '^studio/case-studies/([^/]+)/?$', 'index.php?nice_case_study=$matches[1]', 'top' );
}

/**
 * Backward-compatible wrapper for registering content rewrite rules.
 */
function nice_register_events_rewrite_rules() {
	nice_register_content_rewrite_rules();
}

/**
 * Determine the division a post belongs to.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return string|false
 */
function nice_get_content_division( $post ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, array( 'nice_service', 'nice_case_study' ), true ) ) {
		return false;
	}

	foreach ( array( 'events', 'studio' ) as $division ) {
		if ( has_term( $division, 'nice_division', $post ) ) {
			return $division;
		}
	}

	return false;
}

/**
 * Determine whether a post belongs to the Events division.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return bool
 */
function nice_is_events_content( $post ) {
	return nice_get_content_division( $post ) === 'events';
}

/**
 * Determine whether a post belongs to the Studio division.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return bool
 */
function nice_is_studio_content( $post ) {
	return nice_get_content_division( $post ) === 'studio';
}

/**
 * Return the controlled URL for a supported content record.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return string
 */
function nice_get_content_url( $post ) {
	$post     = get_post( $post );
	$division = nice_get_content_division( $post );

	if ( ! $division ) {
		return '';
	}

	if ( 'nice_service' === $post->post_type ) {
		return home_url( user_trailingslashit( $division . '/services/' . $post->post_name ) );
	}

	if ( 'nice_case_study' === $post->post_type ) {
		return home_url( user_trailingslashit( $division . '/case-studies/' . $post->post_name ) );
	}

	return '';
}

/**
 * Backward-compatible wrapper for Events URLs.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return string
 */
function nice_get_events_content_url( $post ) {
	return nice_get_content_url( $post );
}

/**
 * Publish controlled NICE permalinks instead of raw post-type query URLs.
 *
 * @param string  $permalink Default permalink.
 * @param WP_Post $post      Content record.
 * @return string
 */
function nice_filter_content_permalink( $permalink, $post ) {
	$content_url = nice_get_content_url( $post );

	return $content_url ? $content_url : $permalink;
}
add_filter( 'post_type_link', 'nice_filter_content_permalink', 10, 2 );

/**
 * Convert a non-public NICE content request into a normal WordPress 404.
 */
function nice_set_content_request_404() {
	global $wp_query;

	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}

/**
 * Enforce the canonical content paths and keep non-division CPT records private.
 */
function nice_enforce_content_routes() {
	if ( ! is_singular( array( 'nice_service', 'nice_case_study' ) ) ) {
		return;
	}

	$post      = get_queried_object();
	$canonical = nice_get_content_url( $post );

	if ( ! $canonical ) {
		nice_set_content_request_404();
		return;
	}

	$request_path   = trailingslashit( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ) );
	$canonical_path = trailingslashit( (string) wp_parse_url( $canonical, PHP_URL_PATH ) );

	if ( $request_path !== $canonical_path ) {
		wp_safe_redirect( $canonical, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'nice_enforce_content_routes', 1 );

/**
 * Prevent WordPress from guessing unapproved global or raw CPT routes.
 *
 * @param string|false $redirect_url  Proposed canonical URL.
 * @param string       $requested_url Requested URL.
 * @return string|false
 */
function nice_filter_unapproved_canonical_guesses( $redirect_url, $requested_url ) {
	$path = trim( (string) wp_parse_url( $requested_url, PHP_URL_PATH ), '/' );

	if ( 'team' === $path || str_starts_with( $path, 'nice_service/' ) || str_starts_with( $path, 'nice_case_study/' ) ) {
		return false;
	}

	return $redirect_url;
}
add_filter( 'redirect_canonical', 'nice_filter_unapproved_canonical_guesses', 10, 2 );
