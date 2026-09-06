<?php
/**
 * Studio Home content adapters.
 *
 * NICE Core remains the primary content source. When it is unavailable or a
 * required set is incomplete, the page renders an intentional section state
 * instead of mixing CMS records with a parallel theme dataset.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Determine whether the current request is the Studio Home Page.
 *
 * @return bool
 */
function nice_theme_is_studio_home() {
	return is_page() && 'studio' === get_page_uri( get_queried_object_id() );
}

/**
 * Return the complete, approved Studio Service set from NICE Core.
 *
 * @return WP_Post[]
 */
function nice_get_studio_home_services() {
	if ( ! function_exists( 'nice_get_studio_services' ) ) {
		return array();
	}

	$services = array_values(
		array_filter(
			nice_get_studio_services(),
			static fn( $service ) => $service instanceof WP_Post
		)
	);
	$expected = array( 'corporate-videos', 'digital-content-creation', 'films-entertainment' );

	if ( $expected !== wp_list_pluck( $services, 'post_name' ) ) {
		return array();
	}

	return apply_filters( 'nice_studio_home_services', $services );
}

/**
 * Return the source-approved Studio Case Studies currently in NICE Core.
 *
 * @return WP_Post[]
 */
function nice_get_studio_home_case_studies() {
	if ( ! function_exists( 'nice_get_featured_case_studies' ) ) {
		return array();
	}

	return apply_filters(
		'nice_studio_home_case_studies',
		nice_get_featured_case_studies(
			array(
				'division'       => 'studio',
				'posts_per_page' => 5,
			)
		)
	);
}

/**
 * Return a restrained selection from the shared Client dataset.
 *
 * @return WP_Post[]
 */
function nice_get_studio_home_clients() {
	if ( ! function_exists( 'nice_get_featured_clients' ) ) {
		return array();
	}

	return apply_filters(
		'nice_studio_home_clients',
		nice_get_featured_clients( array( 'posts_per_page' => 8 ) )
	);
}

/**
 * Return the Corporate Videos record used as the Studio hero media source.
 *
 * @param WP_Post[] $services Studio Services.
 * @return WP_Post|null
 */
function nice_get_studio_hero_source( $services ) {
	foreach ( $services as $service ) {
		if ( 'corporate-videos' === $service->post_name && has_post_thumbnail( $service ) ) {
			return $service;
		}
	}

	return null;
}

/**
 * Return the first readable term name for a Studio Case Study.
 *
 * @param int $post_id Case Study ID.
 * @return string
 */
function nice_get_studio_case_study_service_name( $post_id ) {
	$terms = wp_get_object_terms( $post_id, 'nice_service_type' );

	return ! is_wp_error( $terms ) && ! empty( $terms ) ? $terms[0]->name : '';
}
