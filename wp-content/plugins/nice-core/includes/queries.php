<?php
/**
 * Predictable query helpers for NICE content.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Services.
 *
 * Supported convenience arguments: division and service_type.
 *
 * @param array<string, mixed> $args Query overrides.
 * @return WP_Post[]
 */
function nice_get_services( $args = array() ) {
	$division     = sanitize_title( $args['division'] ?? '' );
	$service_type = sanitize_title( $args['service_type'] ?? '' );
	unset( $args['division'], $args['service_type'] );

	$tax_query = array();
	if ( $division ) {
		$tax_query[] = array(
			'taxonomy' => 'nice_division',
			'field'    => 'slug',
			'terms'    => $division,
		);
	}
	if ( $service_type ) {
		$tax_query[] = array(
			'taxonomy' => 'nice_service_type',
			'field'    => 'slug',
			'terms'    => $service_type,
		);
	}
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$query_args = wp_parse_args(
		$args,
		array(
			'post_type'      => 'nice_service',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	if ( $tax_query ) {
		$query_args['tax_query'] = $tax_query;
	}

	return get_posts( $query_args );
}

/**
 * Sort Services using the approved sitemap order.
 *
 * @param WP_Post[] $services Service posts.
 * @return WP_Post[]
 */
function nice_sort_services_by_approved_order( $services ) {
	$order = array_flip( array_keys( nice_get_approved_service_types() ) );

	usort(
		$services,
		static function ( $first, $second ) use ( $order ) {
			$first_terms  = wp_get_object_terms( $first->ID, 'nice_service_type', array( 'fields' => 'slugs' ) );
			$second_terms = wp_get_object_terms( $second->ID, 'nice_service_type', array( 'fields' => 'slugs' ) );
			$first_order  = $order[ $first_terms[0] ?? '' ] ?? PHP_INT_MAX;
			$second_order = $order[ $second_terms[0] ?? '' ] ?? PHP_INT_MAX;

			return $first_order <=> $second_order;
		}
	);

	return $services;
}

/**
 * Sort posts by optional display order while retaining records without meta.
 *
 * @param WP_Post[] $posts Posts to sort.
 * @return WP_Post[]
 */
function nice_sort_posts_by_display_order( $posts ) {
	usort(
		$posts,
		static function ( $first, $second ) {
			$first_order  = (int) get_post_meta( $first->ID, '_nice_display_order', true );
			$second_order = (int) get_post_meta( $second->ID, '_nice_display_order', true );

			if ( $first_order === $second_order ) {
				return strcasecmp( $first->post_title, $second->post_title );
			}

			return $first_order <=> $second_order;
		}
	);

	return $posts;
}

/** @return WP_Post[] */
function nice_get_events_services() {
	return nice_sort_services_by_approved_order( nice_get_services( array( 'division' => 'events' ) ) );
}

/** @return WP_Post[] */
function nice_get_studio_services() {
	return nice_sort_services_by_approved_order( nice_get_services( array( 'division' => 'studio' ) ) );
}

/**
 * Return one Service by post slug.
 *
 * @param string $slug Service slug.
 * @return WP_Post|null
 */
function nice_get_service_by_slug( $slug ) {
	$post = get_page_by_path( sanitize_title( $slug ), OBJECT, 'nice_service' );

	return $post instanceof WP_Post && 'publish' === $post->post_status ? $post : null;
}

/**
 * Return Services assigned to one approved Service Type.
 *
 * @param string               $service_type Service Type slug.
 * @param array<string, mixed> $args         Query overrides.
 * @return WP_Post[]
 */
function nice_get_services_by_service_type( $service_type, $args = array() ) {
	$args['service_type'] = $service_type;

	return nice_get_services( $args );
}

/**
 * Query Case Studies.
 *
 * Supported convenience arguments: division, service_type, and featured.
 *
 * @param array<string, mixed> $args Query overrides.
 * @return WP_Post[]
 */
function nice_get_case_studies( $args = array() ) {
	$use_default_order = ! array_key_exists( 'orderby', $args );
	$division     = sanitize_title( $args['division'] ?? '' );
	$service_type = sanitize_title( $args['service_type'] ?? '' );
	$featured     = array_key_exists( 'featured', $args ) ? rest_sanitize_boolean( $args['featured'] ) : null;
	unset( $args['division'], $args['service_type'], $args['featured'] );

	$tax_query = array();
	foreach ( array( 'nice_division' => $division, 'nice_service_type' => $service_type ) as $taxonomy => $slug ) {
		if ( $slug ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slug,
			);
		}
	}
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$query_args = wp_parse_args(
		$args,
		array(
			'post_type'      => 'nice_case_study',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	if ( $tax_query ) {
		$query_args['tax_query'] = $tax_query;
	}
	if ( null !== $featured ) {
		$query_args['meta_query'] = array(
			array(
				'key'   => '_nice_featured',
				'value' => $featured ? '1' : '0',
			),
		);
	}

	$posts = get_posts( $query_args );

	return $use_default_order ? nice_sort_posts_by_display_order( $posts ) : $posts;
}

/** @return WP_Post[] */
function nice_get_featured_case_studies( $args = array() ) {
	$args['featured'] = true;

	return nice_get_case_studies( $args );
}

/** @return WP_Post[] */
function nice_get_case_studies_by_service( $service_type, $args = array() ) {
	$args['service_type'] = $service_type;

	return nice_get_case_studies( $args );
}

/**
 * Return one Case Study by post slug.
 *
 * @param string $slug Case Study slug.
 * @return WP_Post|null
 */
function nice_get_case_study_by_slug( $slug ) {
	$post = get_page_by_path( sanitize_title( $slug ), OBJECT, 'nice_case_study' );

	return $post instanceof WP_Post && 'publish' === $post->post_status ? $post : null;
}

/**
 * Query Clients.
 *
 * @param array<string, mixed> $args Query overrides.
 * @return WP_Post[]
 */
function nice_get_clients( $args = array() ) {
	$use_default_order = ! array_key_exists( 'orderby', $args );
	$query_args = wp_parse_args(
		$args,
		array(
			'post_type'      => 'nice_client',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	$posts = get_posts( $query_args );

	return $use_default_order ? nice_sort_posts_by_display_order( $posts ) : $posts;
}

/** @return WP_Post[] */
function nice_get_featured_clients( $args = array() ) {
	$args['meta_query'] = array(
		array(
			'key'   => '_nice_featured',
			'value' => '1',
		),
	);

	return nice_get_clients( $args );
}

/**
 * Return one Client by post slug.
 *
 * @param string $slug Client slug.
 * @return WP_Post|null
 */
function nice_get_client_by_slug( $slug ) {
	$post = get_page_by_path( sanitize_title( $slug ), OBJECT, 'nice_client' );

	return $post instanceof WP_Post && 'publish' === $post->post_status ? $post : null;
}

/**
 * Query Team Members.
 *
 * Supported convenience argument: division.
 *
 * @param array<string, mixed> $args Query overrides.
 * @return WP_Post[]
 */
function nice_get_team_members( $args = array() ) {
	$use_default_order = ! array_key_exists( 'orderby', $args );
	$division = sanitize_title( $args['division'] ?? '' );
	unset( $args['division'] );

	$query_args = wp_parse_args(
		$args,
		array(
			'post_type'      => 'nice_team_member',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	if ( $division ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'nice_division',
				'field'    => 'slug',
				'terms'    => $division,
			),
		);
	}

	$posts = get_posts( $query_args );

	return $use_default_order ? nice_sort_posts_by_display_order( $posts ) : $posts;
}

/** @return WP_Post[] */
function nice_get_team_members_by_division( $division, $args = array() ) {
	$args['division'] = $division;

	return nice_get_team_members( $args );
}
