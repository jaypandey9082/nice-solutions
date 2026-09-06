<?php
/**
 * NICE content taxonomies and approved terms.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the only approved division terms.
 *
 * @return array<string, string>
 */
function nice_get_approved_divisions() {
	return array(
		'events' => __( 'Events', 'nice-core' ),
		'studio' => __( 'Studio', 'nice-core' ),
	);
}

/**
 * Return the approved service types and their required divisions.
 *
 * @return array<string, array{name: string, division: string}>
 */
function nice_get_approved_service_types() {
	return array(
		'corporate-events'            => array(
			'name'     => __( 'Corporate Events', 'nice-core' ),
			'division' => 'events',
		),
		'exhibitions-conferences'     => array(
			'name'     => __( 'Exhibitions & Conferences', 'nice-core' ),
			'division' => 'events',
		),
		'activations-promotions'      => array(
			'name'     => __( 'Activations & Promotions', 'nice-core' ),
			'division' => 'events',
		),
		'corporate-videos'            => array(
			'name'     => __( 'Corporate Videos', 'nice-core' ),
			'division' => 'studio',
		),
		'digital-content-creation'    => array(
			'name'     => __( 'Digital Content Creation', 'nice-core' ),
			'division' => 'studio',
		),
		'films-entertainment'         => array(
			'name'     => __( 'Films & Entertainment', 'nice-core' ),
			'division' => 'studio',
		),
	);
}

/**
 * Register Division and Service Type.
 */
function nice_register_taxonomies() {
	register_taxonomy(
		'nice_division',
		array( 'nice_service', 'nice_case_study', 'nice_team_member' ),
		array(
			'labels'            => array(
				'name'          => __( 'Divisions', 'nice-core' ),
				'singular_name' => __( 'Division', 'nice-core' ),
				'search_items'  => __( 'Search Divisions', 'nice-core' ),
				'all_items'     => __( 'All Divisions', 'nice-core' ),
				'edit_item'     => __( 'Edit Division', 'nice-core' ),
				'update_item'   => __( 'Update Division', 'nice-core' ),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rest_base'         => 'nice-divisions',
			'hierarchical'      => false,
			'rewrite'           => false,
			'meta_box_cb'       => false,
			'capabilities'      => array(
				'manage_terms' => 'do_not_allow',
				'edit_terms'   => 'do_not_allow',
				'delete_terms' => 'do_not_allow',
				'assign_terms' => 'edit_posts',
			),
		)
	);

	register_taxonomy(
		'nice_service_type',
		array( 'nice_service', 'nice_case_study' ),
		array(
			'labels'            => array(
				'name'          => __( 'Service Types', 'nice-core' ),
				'singular_name' => __( 'Service Type', 'nice-core' ),
				'search_items'  => __( 'Search Service Types', 'nice-core' ),
				'all_items'     => __( 'All Service Types', 'nice-core' ),
				'edit_item'     => __( 'Edit Service Type', 'nice-core' ),
				'update_item'   => __( 'Update Service Type', 'nice-core' ),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rest_base'         => 'nice-service-types',
			'hierarchical'      => false,
			'rewrite'           => false,
			'meta_box_cb'       => false,
			'capabilities'      => array(
				'manage_terms' => 'do_not_allow',
				'edit_terms'   => 'do_not_allow',
				'delete_terms' => 'do_not_allow',
				'assign_terms' => 'edit_posts',
			),
		)
	);
}

/**
 * Create missing approved terms without importing content.
 *
 * @return array{created: int, existing: int, errors: string[]}
 */
function nice_ensure_default_terms() {
	$result = array(
		'created'  => 0,
		'existing' => 0,
		'errors'   => array(),
	);

	foreach ( nice_get_approved_divisions() as $slug => $name ) {
		$term = term_exists( $slug, 'nice_division' );

		if ( $term ) {
			++$result['existing'];
			continue;
		}

		$inserted = wp_insert_term( $name, 'nice_division', array( 'slug' => $slug ) );

		if ( is_wp_error( $inserted ) ) {
			$result['errors'][] = $inserted->get_error_message();
		} else {
			++$result['created'];
		}
	}

	foreach ( nice_get_approved_service_types() as $slug => $definition ) {
		$term = term_exists( $slug, 'nice_service_type' );

		if ( $term ) {
			++$result['existing'];
			continue;
		}

		$inserted = wp_insert_term( $definition['name'], 'nice_service_type', array( 'slug' => $slug ) );

		if ( is_wp_error( $inserted ) ) {
			$result['errors'][] = $inserted->get_error_message();
		} else {
			++$result['created'];
		}
	}

	return $result;
}

/**
 * Reject taxonomy terms outside the approved sitemap vocabulary.
 *
 * @param string|WP_Error $term     Term name.
 * @param string          $taxonomy Taxonomy name.
 * @param array           $args     Insert arguments.
 * @return string|WP_Error
 */
function nice_restrict_taxonomy_terms( $term, $taxonomy, $args ) {
	if ( is_wp_error( $term ) || ! in_array( $taxonomy, array( 'nice_division', 'nice_service_type' ), true ) ) {
		return $term;
	}

	$slug    = ! empty( $args['slug'] ) ? sanitize_title( $args['slug'] ) : sanitize_title( $term );
	$allowed = 'nice_division' === $taxonomy
		? array_keys( nice_get_approved_divisions() )
		: array_keys( nice_get_approved_service_types() );

	if ( ! in_array( $slug, $allowed, true ) ) {
		return new WP_Error( 'nice_unapproved_term', __( 'Only sitemap-approved NICE terms can be created.', 'nice-core' ) );
	}

	return $term;
}
add_filter( 'pre_insert_term', 'nice_restrict_taxonomy_terms', 10, 3 );

/**
 * Keep Service Type and Division assignments consistent.
 *
 * @param int    $object_id Object ID.
 * @param array  $terms     Assigned terms.
 * @param array  $tt_ids    Assigned term-taxonomy IDs.
 * @param string $taxonomy  Taxonomy name.
 */
function nice_enforce_taxonomy_relationships( $object_id, $terms, $tt_ids, $taxonomy ) {
	static $validating = false;

	$post_type = get_post_type( $object_id );

	if ( $validating || ! in_array( $post_type, array( 'nice_service', 'nice_case_study', 'nice_team_member' ), true ) ) {
		return;
	}

	if ( ! in_array( $taxonomy, array( 'nice_division', 'nice_service_type' ), true ) ) {
		return;
	}

	$validating = true;

	if ( 'nice_team_member' === $post_type ) {
		$divisions = wp_get_object_terms( $object_id, 'nice_division', array( 'fields' => 'slugs' ) );
		$approved  = array_values( array_intersect( (array) $divisions, array_keys( nice_get_approved_divisions() ) ) );

		if ( count( $approved ) > 1 || count( $approved ) !== count( (array) $divisions ) ) {
			wp_set_object_terms( $object_id, array_slice( $approved, 0, 1 ), 'nice_division', false );
		}

		$validating = false;
		return;
	}

	$assigned_types = wp_get_object_terms( $object_id, 'nice_service_type', array( 'fields' => 'slugs' ) );
	$definitions    = nice_get_approved_service_types();
	$approved_types = array_values( array_filter( (array) $assigned_types, static fn( $slug ) => isset( $definitions[ $slug ] ) ) );

	if ( empty( $approved_types ) ) {
		if ( ! empty( $assigned_types ) ) {
			wp_set_object_terms( $object_id, array(), 'nice_service_type', false );
		}
		$validating = false;
		return;
	}

	$service_type = $approved_types[0];
	$division     = $definitions[ $service_type ]['division'];

	if ( 1 !== count( $assigned_types ) || $assigned_types[0] !== $service_type ) {
		wp_set_object_terms( $object_id, $service_type, 'nice_service_type', false );
	}

	$current_divisions = wp_get_object_terms( $object_id, 'nice_division', array( 'fields' => 'slugs' ) );

	if ( array( $division ) !== array_values( (array) $current_divisions ) ) {
		wp_set_object_terms( $object_id, $division, 'nice_division', false );
	}

	$validating = false;
}
add_action( 'set_object_terms', 'nice_enforce_taxonomy_relationships', 10, 4 );
