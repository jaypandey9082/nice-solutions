<?php
/**
 * Curated project previews owned by the main gateway.
 *
 * The gateway publishes no division content. What it does publish is a short,
 * hand-picked set of previews that send a reader to the installation that owns
 * the work, so these records hold their own title, summary, image and
 * destination rather than reaching across hostnames for a Case Study that lives
 * in another database.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report whether this installation owns gateway previews.
 *
 * The gateway owns them. The combined development site owns them too, so the
 * feature can be built and reviewed before the split. A division installation
 * never does: it publishes the work itself.
 *
 * @return bool
 */
function nice_site_owns_gateway_projects() {
	return nice_is_gateway_site() || ! nice_is_division_site();
}

/**
 * Register the Gateway Project content type.
 *
 * Deliberately not publicly queryable. These records exist to be rendered on
 * the front page; giving them their own URL would create a third place the same
 * project appears, competing with the case study that owns it.
 */
function nice_register_gateway_project_post_type() {
	if ( ! nice_site_owns_gateway_projects() ) {
		return;
	}

	register_post_type(
		'nice_gateway_project',
		array(
			'labels'              => nice_get_post_type_labels( __( 'Gateway Project', 'nice-core' ), __( 'Gateway Projects', 'nice-core' ) ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'query_var'           => false,
			'map_meta_cap'        => true,
			'capability_type'     => 'page',
			'menu_icon'           => 'dashicons-admin-links',
			'menu_position'       => 24,
			'supports'            => array( 'title', 'thumbnail', 'revisions' ),
		)
	);
}

/**
 * Register Gateway Project metadata.
 */
function nice_register_gateway_project_meta() {
	if ( ! nice_site_owns_gateway_projects() ) {
		return;
	}

	nice_register_post_meta_field( 'nice_gateway_project', '_nice_gateway_summary', 'string', 'sanitize_textarea_field', '', 'nice_authorize_post_meta', false );
	nice_register_post_meta_field( 'nice_gateway_project', '_nice_gateway_division', 'string', 'sanitize_key', '', 'nice_authorize_post_meta', false );
	nice_register_post_meta_field( 'nice_gateway_project', '_nice_gateway_destination_url', 'string', 'nice_sanitize_gateway_destination_url', '', 'nice_authorize_post_meta', false );
	nice_register_post_meta_field( 'nice_gateway_project', '_nice_display_order', 'integer', 'nice_sanitize_integer', 0, 'nice_authorize_post_meta', false );
	nice_register_post_meta_field( 'nice_gateway_project', '_nice_media_approved', 'boolean', 'rest_sanitize_boolean', false, 'nice_authorize_post_meta', false );
}

/**
 * Return the URL prefix a division's case studies must sit behind.
 *
 * @param string $division Division slug.
 * @return string Absolute URL prefix, or an empty string for an unknown division.
 */
function nice_get_gateway_destination_prefix( $division ) {
	$division = sanitize_key( $division );

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return '';
	}

	return nice_get_division_url( $division, 'case-studies/' );
}

/**
 * Keep only a destination that lands on a division's case studies.
 *
 * This runs as the meta sanitizer rather than only in the admin screen, so a
 * value written by import, REST or WP-CLI faces the same rule the editor does.
 *
 * @param mixed $value Candidate destination URL.
 * @return string The URL, or an empty string when it is not an allowed destination.
 */
function nice_sanitize_gateway_destination_url( $value ) {
	$url = esc_url_raw( (string) $value, array( 'https', 'http' ) );

	if ( ! $url ) {
		return '';
	}

	foreach ( nice_get_division_slugs() as $division ) {
		if ( ! nice_get_gateway_destination_problem( $url, $division ) ) {
			return $url;
		}
	}

	return '';
}

/**
 * Explain why a destination URL cannot be stored, or return an empty string.
 *
 * A gateway preview exists to hand the reader to the installation that owns the
 * work. An address anywhere else turns the front page into an open redirect
 * with NICE's name on it, so the accepted set is exactly one: that division's
 * own case studies.
 *
 * @param string $url      Candidate destination URL.
 * @param string $division Division slug.
 * @return string Empty when the URL is usable, otherwise the reason it is not.
 */
function nice_get_gateway_destination_problem( $url, $division ) {
	$url      = trim( (string) $url );
	$division = sanitize_key( $division );

	if ( '' === $url ) {
		return __( 'A destination URL is required before this preview can be published.', 'nice-core' );
	}

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return __( 'Choose the division that owns this project before setting its destination.', 'nice-core' );
	}

	$prefix = nice_get_gateway_destination_prefix( $division );

	if ( ! $prefix ) {
		return __( 'This installation does not know where that division lives. Define its site URL in wp-config.php first.', 'nice-core' );
	}

	/*
	 * The prefix itself comes from configuration, so matching it is the whole
	 * test: on production that means https on the division's own hostname, and
	 * on the combined development site it means the /events/ or /studio/ path.
	 */
	if ( ! str_starts_with( trailingslashit( $url ), $prefix ) || untrailingslashit( $url ) === untrailingslashit( $prefix ) ) {
		return sprintf(
			/* translators: %s: required URL prefix. */
			__( 'The destination must be a case study on that division: it has to start with %s and name a project.', 'nice-core' ),
			$prefix
		);
	}

	return '';
}

/**
 * Return the seed manifest for gateway previews.
 *
 * Three drafts, chosen to show one of each shape the gateway can point at: two
 * Events projects and one Studio project. They are drafts because the wording
 * and the imagery are NICE's to approve, not the migration's to publish.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_gateway_project_manifest() {
	return array(
		array(
			'slug'     => 'voltas-fam-tastic-fiesta',
			'title'    => 'Voltas Fam-Tastic Fiesta',
			'summary'  => 'An employee family fiesta for Voltas Limited at The Parsi Gymkhana, Mumbai.',
			'division' => 'events',
			'order'    => 10,
			'image'    => 'voltas-fam-tastic.webp',
			'alt'      => 'NICE and Voltas team members standing on the event stage',
		),
		array(
			'slug'     => 'gca-2025',
			'title'    => 'GCA 2025',
			'summary'  => 'The three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India.',
			'division' => 'events',
			'order'    => 20,
			'image'    => 'gca-2025.webp',
			'alt'      => 'Audience seated in a conference hall facing the stage',
		),
		array(
			'slug'     => 'strata-geosystems-factory-shoot',
			'title'    => 'Strata Geosystems Factory Shoot',
			'summary'  => 'A factory shoot with drone and FPV presentation for Strata Geosystems India in Gujarat.',
			'division' => 'studio',
			'order'    => 30,
			'image'    => 'strata-production.webp',
			'alt'      => 'NICE production crew filming inside the Strata Geosystems factory',
		),
	);
}

/**
 * Create the gateway preview drafts.
 *
 * An existing slug in any status is a hard stop, so reruns never duplicate a
 * record or overwrite an editor's wording.
 *
 * @return array{created: int, skipped: int, errors: string[], media: int}
 */
function nice_migrate_gateway_project_drafts() {
	$summary = array(
		'created' => 0,
		'skipped' => 0,
		'errors'  => array(),
		'media'   => 0,
	);

	if ( ! nice_site_owns_gateway_projects() ) {
		return $summary;
	}

	foreach ( nice_get_gateway_project_manifest() as $record ) {
		if ( nice_find_migrated_post( $record['slug'], 'nice_gateway_project' ) ) {
			++$summary['skipped'];
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'nice_gateway_project',
				'post_status' => 'draft',
				'post_name'   => sanitize_title( $record['slug'] ),
				'post_title'  => sanitize_text_field( $record['title'] ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$summary['errors'][] = $post_id->get_error_message();
			continue;
		}

		if ( 'draft' !== get_post_status( $post_id ) ) {
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
		}

		update_post_meta( $post_id, '_nice_gateway_summary', sanitize_textarea_field( $record['summary'] ) );
		update_post_meta( $post_id, '_nice_gateway_division', sanitize_key( $record['division'] ) );
		update_post_meta( $post_id, '_nice_display_order', (int) $record['order'] );
		update_post_meta( $post_id, '_nice_media_approved', 0 );

		$destination = nice_get_division_url( $record['division'], 'case-studies/' . $record['slug'] );
		if ( ! nice_get_gateway_destination_problem( $destination, $record['division'] ) ) {
			update_post_meta( $post_id, '_nice_gateway_destination_url', $destination );
		}

		/*
		 * The preview image is copied into this installation's own Media Library
		 * rather than referenced by an ID from another database. Deck photography
		 * is excluded from production packages, so on a real gateway there is
		 * nothing to copy and the editor uploads the approved image instead.
		 */
		$attachment_id = nice_migrate_media_attachment( $record['image'], $record['alt'] );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
			++$summary['media'];
		}

		++$summary['created'];
	}

	return $summary;
}

/**
 * Return the published gateway previews in display order.
 *
 * @return WP_Post[]
 */
function nice_get_gateway_projects() {
	if ( ! nice_site_owns_gateway_projects() ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'      => 'nice_gateway_project',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'orderby'        => array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ),
			'meta_key'       => '_nice_display_order',
			'no_found_rows'  => true,
		)
	);

	return $posts;
}

/**
 * Return one gateway preview as the flat array a template renders.
 *
 * @param WP_Post $post Gateway Project.
 * @return array<string, mixed>
 */
function nice_get_gateway_project_view( $post ) {
	$division    = sanitize_key( (string) get_post_meta( $post->ID, '_nice_gateway_division', true ) );
	$destination = (string) get_post_meta( $post->ID, '_nice_gateway_destination_url', true );
	$divisions   = nice_get_approved_divisions();

	return array(
		'post_id'        => $post->ID,
		'title'          => get_the_title( $post ),
		'summary'        => (string) get_post_meta( $post->ID, '_nice_gateway_summary', true ),
		'division'       => $division,
		'division_label' => $divisions[ $division ] ?? '',
		/* A destination that no longer passes the rule is dropped rather than linked. */
		'url'            => nice_get_gateway_destination_problem( $destination, $division ) ? '' : $destination,
		'attachment_id'  => (int) get_post_thumbnail_id( $post ),
		'media_approved' => (bool) get_post_meta( $post->ID, '_nice_media_approved', true ),
	);
}

/**
 * Return every published gateway preview ready for rendering.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_gateway_project_views() {
	return array_map( 'nice_get_gateway_project_view', nice_get_gateway_projects() );
}
