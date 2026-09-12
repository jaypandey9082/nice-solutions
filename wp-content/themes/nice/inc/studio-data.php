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

	$case_studies = nice_get_featured_case_studies(
		array(
			'division'       => 'studio',
			'posts_per_page' => 20,
		)
	);
	$by_slug      = array();

	foreach ( $case_studies as $case_study ) {
		if ( $case_study instanceof WP_Post ) {
			$by_slug[ $case_study->post_name ] = $case_study;
		}
	}

	$selected = array();
	foreach ( array( 'strata-geosystems-factory-shoot', 'career-agents-academy', 'krish-e' ) as $slug ) {
		if ( isset( $by_slug[ $slug ] ) ) {
			$selected[] = $by_slug[ $slug ];
		}
	}

	return apply_filters( 'nice_studio_home_case_studies', $selected );
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
 * Return the first readable term name for a Studio Case Study.
 *
 * @param int $post_id Case Study ID.
 * @return string
 */
function nice_get_studio_case_study_service_name( $post_id ) {
	$terms = wp_get_object_terms( $post_id, 'nice_service_type' );

	return ! is_wp_error( $terms ) && ! empty( $terms ) ? $terms[0]->name : '';
}
