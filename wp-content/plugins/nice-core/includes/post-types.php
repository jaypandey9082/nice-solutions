<?php
/**
 * NICE content post types.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build human-readable labels for a post type.
 *
 * @param string $singular Singular label.
 * @param string $plural   Plural label.
 * @return string[]
 */
function nice_get_post_type_labels( $singular, $plural ) {
	return array(
		'name'                  => $plural,
		'singular_name'         => $singular,
		'menu_name'             => $plural,
		'name_admin_bar'        => $singular,
		'add_new'               => __( 'Add New', 'nice-core' ),
		'add_new_item'          => sprintf( __( 'Add New %s', 'nice-core' ), $singular ),
		'edit_item'             => sprintf( __( 'Edit %s', 'nice-core' ), $singular ),
		'new_item'              => sprintf( __( 'New %s', 'nice-core' ), $singular ),
		'view_item'             => sprintf( __( 'View %s', 'nice-core' ), $singular ),
		'view_items'            => sprintf( __( 'View %s', 'nice-core' ), $plural ),
		'search_items'          => sprintf( __( 'Search %s', 'nice-core' ), $plural ),
		'not_found'             => sprintf( __( 'No %s found.', 'nice-core' ), strtolower( $plural ) ),
		'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'nice-core' ), strtolower( $plural ) ),
		'all_items'             => sprintf( __( 'All %s', 'nice-core' ), $plural ),
		'archives'              => sprintf( __( '%s Archives', 'nice-core' ), $singular ),
		'attributes'            => sprintf( __( '%s Attributes', 'nice-core' ), $singular ),
		'featured_image'        => sprintf( __( '%s Image', 'nice-core' ), $singular ),
		'set_featured_image'    => sprintf( __( 'Set %s image', 'nice-core' ), strtolower( $singular ) ),
		'remove_featured_image' => sprintf( __( 'Remove %s image', 'nice-core' ), strtolower( $singular ) ),
		'use_featured_image'    => sprintf( __( 'Use as %s image', 'nice-core' ), strtolower( $singular ) ),
	);
}

/**
 * Register the four NICE business-content post types.
 */
function nice_register_post_types() {
	$shared = array(
		'public'          => true,
		'show_ui'         => true,
		'show_in_rest'    => true,
		'has_archive'     => false,
		'rewrite'         => false,
		'map_meta_cap'    => true,
		'capability_type' => 'post',
	);

	register_post_type(
		'nice_service',
		array_merge(
			$shared,
			array(
				'labels'             => nice_get_post_type_labels( __( 'Service', 'nice-core' ), __( 'Services', 'nice-core' ) ),
				'menu_icon'          => 'dashicons-portfolio',
				'menu_position'      => 20,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
				'query_var'          => 'nice_service',
				'rest_base'          => 'nice-services',
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			)
		)
	);

	register_post_type(
		'nice_case_study',
		array_merge(
			$shared,
			array(
				'labels'             => nice_get_post_type_labels( __( 'Case Study', 'nice-core' ), __( 'Case Studies', 'nice-core' ) ),
				'menu_icon'          => 'dashicons-format-gallery',
				'menu_position'      => 21,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
				'query_var'          => 'nice_case_study',
				'rest_base'          => 'nice-case-studies',
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			)
		)
	);

	register_post_type(
		'nice_client',
		array_merge(
			$shared,
			array(
				'labels'             => nice_get_post_type_labels( __( 'Client', 'nice-core' ), __( 'Clients', 'nice-core' ) ),
				'menu_icon'          => 'dashicons-groups',
				'menu_position'      => 22,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'query_var'          => false,
				'rest_base'          => 'nice-clients',
				'supports'           => array( 'title', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			)
		)
	);

	register_post_type(
		'nice_team_member',
		array_merge(
			$shared,
			array(
				'labels'             => nice_get_post_type_labels( __( 'Team Member', 'nice-core' ), __( 'Team Members', 'nice-core' ) ),
				'menu_icon'          => 'dashicons-businessperson',
				'menu_position'      => 23,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'query_var'          => false,
				'rest_base'          => 'nice-team-members',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields' ),
			)
		)
	);
}
