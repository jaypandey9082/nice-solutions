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
	/*
	 * Only register routes this installation actually serves, and build them
	 * from the configured prefix. The combined site matches
	 * ^events/services/... while an Events installation owns its hostname and
	 * matches ^services/... instead.
	 */
	foreach ( nice_get_division_slugs() as $division ) {
		if ( ! nice_division_is_local( $division ) ) {
			continue;
		}

		$prefix = nice_get_division_prefix( $division );
		$base   = $prefix ? '^' . $prefix . '/' : '^';

		add_rewrite_rule( $base . 'services/([^/]+)/?$', 'index.php?nice_service=$matches[1]', 'top' );
		add_rewrite_rule( $base . 'case-studies/([^/]+)/?$', 'index.php?nice_case_study=$matches[1]', 'top' );
	}
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
		return nice_get_division_url( $division, 'services/' . $post->post_name );
	}

	if ( 'nice_case_study' === $post->post_type ) {
		return nice_get_division_url( $division, 'case-studies/' . $post->post_name );
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
	$division  = nice_get_content_division( $post );
	$canonical = nice_get_content_url( $post );

	if ( ! $division || ! $canonical ) {
		nice_set_content_request_404();
		return;
	}

	/*
	 * A division installation holds the whole dataset but only publishes its
	 * own division, so a record belonging to the sibling site is not found here.
	 */
	if ( ! nice_division_is_local( $division ) ) {
		nice_set_content_request_404();
		return;
	}

	$raw_request_path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
	$request_path     = trailingslashit( $raw_request_path );
	$canonical_path   = trailingslashit( (string) wp_parse_url( $canonical, PHP_URL_PATH ) );

	/*
	 * Disallow raw CPT URLs and cross-division requests (404, not a 301).
	 *
	 * This compares against the canonical directory rather than the division
	 * prefix, because on a dedicated division installation the prefix is "/" and
	 * every path would match it, letting raw /nice_service/{slug}/ URLs redirect
	 * to the canonical page instead of being refused.
	 */
	$canonical_directory = trailingslashit( dirname( untrailingslashit( $canonical_path ) ) );
	if ( ! str_starts_with( $request_path, $canonical_directory ) ) {
		nice_set_content_request_404();
		return;
	}

	if ( $raw_request_path !== (string) wp_parse_url( $canonical, PHP_URL_PATH ) ) {
		wp_safe_redirect( $canonical, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'nice_enforce_content_routes', 1 );

/**
 * Prevent WordPress from guessing unapproved global or raw CPT routes, or cross-division redirects.
 *
 * @param string|false $redirect_url  Proposed canonical URL.
 * @param string       $requested_url Requested URL.
 * @return string|false
 */
function nice_filter_unapproved_canonical_guesses( $redirect_url, $requested_url ) {
	if ( is_404() ) {
		return false;
	}

	$path = trim( (string) wp_parse_url( $requested_url, PHP_URL_PATH ), '/' );

	if ( 'team' === $path || str_starts_with( $path, 'nice_service/' ) || str_starts_with( $path, 'nice_case_study/' ) ) {
		return false;
	}

	$post = get_queried_object();
	if ( $post instanceof WP_Post && in_array( $post->post_type, array( 'nice_service', 'nice_case_study' ), true ) ) {
		$division  = nice_get_content_division( $post );
		$canonical = $division ? nice_get_content_url( $post ) : '';

		if ( $canonical ) {
			/* Same reasoning as above: match the canonical directory, not the prefix. */
			$canonical_directory = trim( dirname( untrailingslashit( (string) wp_parse_url( $canonical, PHP_URL_PATH ) ) ), '/' );

			if ( $canonical_directory && ! str_starts_with( $path, $canonical_directory . '/' ) ) {
				return false;
			}
		}
	}

	return $redirect_url;
}
add_filter( 'redirect_canonical', 'nice_filter_unapproved_canonical_guesses', 10, 2 );

/**
 * Keep a sibling division's records out of every public listing.
 *
 * Route enforcement already 404s a sibling's page, but a record that reached
 * the database another way — a full database copy taken before the split, an
 * import, an editor moving a record between divisions — would still surface in
 * search results, the sitemap and REST collections. Each of those is a way for
 * one hostname to advertise work that belongs to another.
 *
 * Deliberately front-end only. An administrator has to be able to see a stray
 * record in wp-admin in order to move or delete it.
 *
 * @param WP_Query $query Query about to run.
 */
function nice_restrict_query_to_local_divisions( $query ) {
	if ( is_admin() || ! nice_is_division_site() ) {
		return;
	}

	$post_types = (array) $query->get( 'post_type' );
	$divisional = array_intersect( $post_types, array( 'nice_service', 'nice_case_study', 'nice_team_member' ) );

	if ( ! $divisional && ! $query->is_search() ) {
		return;
	}

	$foreign = array_values( array_diff( nice_get_division_slugs(), nice_get_local_division_slugs() ) );

	if ( ! $foreign ) {
		return;
	}

	/*
	 * Excluding the sibling rather than requiring the local division, because a
	 * search spans every public type: an IN clause on a taxonomy that Pages do
	 * not use would drop every Page from the results. NOT IN keeps anything
	 * carrying no division at all.
	 */
	$tax_query   = (array) $query->get( 'tax_query' );
	$tax_query[] = array(
		'taxonomy' => 'nice_division',
		'field'    => 'slug',
		'terms'    => $foreign,
		'operator' => 'NOT IN',
	);

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'nice_restrict_query_to_local_divisions' );
