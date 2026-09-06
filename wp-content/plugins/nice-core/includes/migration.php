<?php
/**
 * Idempotent migration of approved theme preview content into NICE Core.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the source-approved Phase 5 migration manifest.
 *
 * @return array<string, array<int, array<string, mixed>>>
 */
function nice_get_content_migration_manifest() {
	return array(
		'clients' => array(
			array( 'slug' => 'voltas-limited', 'title' => 'Voltas Limited', 'order' => 10, 'featured' => true ),
			array( 'slug' => 'zoetis', 'title' => 'Zoetis', 'order' => 20, 'featured' => true ),
			array( 'slug' => 'institute-of-actuaries-of-india', 'title' => 'Institute of Actuaries of India', 'order' => 30, 'featured' => true ),
			array( 'slug' => 'franchise-india', 'title' => 'Franchise India', 'order' => 40, 'featured' => true ),
			array( 'slug' => 'airtel', 'title' => 'Airtel', 'order' => 50, 'featured' => true ),
			array( 'slug' => 'ficci', 'title' => 'FICCI', 'order' => 60, 'featured' => true ),
			array( 'slug' => 'bajaj', 'title' => 'Bajaj', 'order' => 70, 'featured' => true ),
			array( 'slug' => 'mahindra', 'title' => 'Mahindra', 'order' => 80, 'featured' => true ),
			array( 'slug' => 'crisil', 'title' => 'CRISIL', 'order' => 90, 'featured' => true ),
			array( 'slug' => 'ajay-thakur', 'title' => 'Ajay Thakur', 'order' => 100, 'featured' => false ),
		),
		'services' => array(
			array(
				'slug'         => 'corporate-events',
				'title'        => 'Corporate Events',
				'description'  => 'Employee gatherings, family celebrations and launches shaped from concept through on-ground execution.',
				'service_type' => 'corporate-events',
				'image'        => 'zoetis-engagement.webp',
				'alt'          => 'Attendee posing with costumed characters at an employee event',
			),
			array(
				'slug'         => 'exhibitions-conferences',
				'title'        => 'Exhibitions & Conferences',
				'description'  => 'Conference environments, exhibition stalls and technical setups coordinated across venue, production and delivery.',
				'service_type' => 'exhibitions-conferences',
				'image'        => 'exhibition-stall.webp',
				'alt'          => 'Custom exhibition stall installed inside an exhibition hall',
			),
			array(
				'slug'         => 'activations-promotions',
				'title'        => 'Activations & Promotions',
				'description'  => 'Audience-facing programmes and awareness initiatives designed to bring people into the experience.',
				'service_type' => 'activations-promotions',
				'image'        => 'power-champs.webp',
				'alt'          => 'Students presenting during a POWER CHAMPS awareness programme',
			),
		),
		'case_studies' => array(
			array(
				'slug'         => 'voltas-fam-tastic-fiesta',
				'title'        => 'Voltas Fam-Tastic Fiesta',
				'description'  => 'An employee family fiesta for Voltas Limited at The Parsi Gymkhana, Mumbai.',
				'service_type' => 'corporate-events',
				'client_slug'  => 'voltas-limited',
				'location'     => 'The Parsi Gymkhana, Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 10,
				'image'        => 'voltas-fam-tastic.webp',
				'alt'          => 'NICE and Voltas team members standing on the event stage',
			),
			array(
				'slug'         => 'gca-2025',
				'title'        => 'GCA 2025',
				'description'  => 'The three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India.',
				'service_type' => 'exhibitions-conferences',
				'client_slug'  => 'institute-of-actuaries-of-india',
				'location'     => 'The Westin Mumbai Powai Lake, Mumbai',
				'year'         => 2025,
				'featured'     => true,
				'order'        => 20,
				'image'        => 'gca-2025.webp',
				'alt'          => 'Audience seated in a conference hall facing the stage',
			),
			array(
				'slug'         => 'zoetis-employee-engagement-day',
				'title'        => 'Zoetis Employee Engagement Day',
				'description'  => 'An employee engagement event for 200 Zoetis team members.',
				'service_type' => 'corporate-events',
				'client_slug'  => 'zoetis',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 30,
				'image'        => 'zoetis-engagement.webp',
				'alt'          => 'Attendee posing with costumed characters at an employee event',
			),
			array(
				'slug'         => 'vision-to-victory',
				'title'        => 'Vision to Victory',
				'description'  => 'A book launch for Ajay Thakur at Hotel Sahara Star, Mumbai.',
				'service_type' => 'corporate-events',
				'client_slug'  => 'ajay-thakur',
				'location'     => 'Hotel Sahara Star, Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 40,
				'image'        => 'vision-to-victory.webp',
				'alt'          => 'Guests lighting a ceremonial lamp at a book launch',
			),
			array(
				'slug'         => 'run-for-equity',
				'title'        => 'RunForEquity',
				'description'  => 'A social marathon conceived as a NICE intellectual event property.',
				'service_type' => 'activations-promotions',
				'client_name'  => 'NICE Intellectual Property',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 50,
				'image'        => 'run-for-equity.webp',
				'alt'          => 'Participants running together during RunForEquity',
			),
		),
	);
}

/**
 * Find a record regardless of status.
 *
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return WP_Post|null
 */
function nice_find_migrated_post( $slug, $post_type ) {
	$posts = get_posts(
		array(
			'name'           => sanitize_title( $slug ),
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
		)
	);

	return $posts[0] ?? null;
}

/**
 * Import one existing theme image into the media library once.
 *
 * @param string $filename Theme image filename.
 * @param string $alt      Approved alt text.
 * @return int|WP_Error Attachment ID or error.
 */
function nice_migrate_media_attachment( $filename, $alt ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => '_nice_source_asset',
			'meta_value'     => sanitize_file_name( $filename ),
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = get_theme_file_path( '/assets/images/' . sanitize_file_name( $filename ) );
	if ( ! is_readable( $source ) ) {
		return new WP_Error( 'nice_missing_source_image', sprintf( 'Source image is unavailable: %s', $filename ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$temp_file = wp_tempnam( $filename );
	if ( ! $temp_file || ! copy( $source, $temp_file ) ) {
		return new WP_Error( 'nice_media_copy_failed', sprintf( 'Could not prepare image: %s', $filename ) );
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => sanitize_file_name( $filename ),
			'tmp_name' => $temp_file,
		),
		0,
		pathinfo( $filename, PATHINFO_FILENAME )
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $temp_file );
		return $attachment_id;
	}

	update_post_meta( $attachment_id, '_nice_source_asset', sanitize_file_name( $filename ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );

	return $attachment_id;
}

/**
 * Insert one approved post without overwriting an existing record.
 *
 * @param string               $post_type Post type.
 * @param array<string, mixed> $record    Migration record.
 * @return array{status: string, post_id: int}|WP_Error
 */
function nice_migrate_content_post( $post_type, $record ) {
	$existing = nice_find_migrated_post( $record['slug'], $post_type );

	if ( $existing ) {
		return array( 'status' => 'skipped', 'post_id' => $existing->ID );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			'post_name'    => sanitize_title( $record['slug'] ),
			'post_title'   => sanitize_text_field( $record['title'] ),
			'post_excerpt' => sanitize_textarea_field( $record['description'] ?? '' ),
			'post_content' => wp_kses_post( $record['description'] ?? '' ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	return array( 'status' => 'created', 'post_id' => $post_id );
}

/**
 * Run the complete approved content migration.
 *
 * @return array<string, mixed>|WP_Error
 */
function nice_run_content_migration() {
	$manifest = nice_get_content_migration_manifest();
	$summary  = array(
		'terms'        => nice_ensure_default_terms(),
		'clients'      => array( 'created' => 0, 'skipped' => 0 ),
		'services'     => array( 'created' => 0, 'skipped' => 0 ),
		'case_studies' => array( 'created' => 0, 'skipped' => 0 ),
		'media'        => array( 'linked' => 0, 'errors' => array() ),
	);

	if ( ! empty( $summary['terms']['errors'] ) ) {
		return new WP_Error( 'nice_term_migration_failed', implode( ' ', $summary['terms']['errors'] ) );
	}

	$client_ids = array();
	foreach ( $manifest['clients'] as $record ) {
		$result = nice_migrate_content_post( 'nice_client', $record );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$client_ids[ $record['slug'] ] = $result['post_id'];
		++$summary['clients'][ $result['status'] ];
		if ( 'created' === $result['status'] ) {
			update_post_meta( $result['post_id'], '_nice_display_order', (int) $record['order'] );
			update_post_meta( $result['post_id'], '_nice_featured', $record['featured'] ? 1 : 0 );
		}
	}

	foreach ( $manifest['services'] as $record ) {
		$result = nice_migrate_content_post( 'nice_service', $record );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		++$summary['services'][ $result['status'] ];

		if ( 'created' === $result['status'] ) {
			wp_set_object_terms( $result['post_id'], $record['service_type'], 'nice_service_type', false );
			$attachment_id = nice_migrate_media_attachment( $record['image'], $record['alt'] );
			if ( is_wp_error( $attachment_id ) ) {
				$summary['media']['errors'][] = $attachment_id->get_error_message();
			} else {
				set_post_thumbnail( $result['post_id'], $attachment_id );
				++$summary['media']['linked'];
			}
		}
	}

	foreach ( $manifest['case_studies'] as $record ) {
		$result = nice_migrate_content_post( 'nice_case_study', $record );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$post_id = $result['post_id'];
		++$summary['case_studies'][ $result['status'] ];

		if ( 'created' === $result['status'] ) {
			wp_set_object_terms( $post_id, $record['service_type'], 'nice_service_type', false );
			update_post_meta( $post_id, '_nice_client_id', $client_ids[ $record['client_slug'] ?? '' ] ?? 0 );
			update_post_meta( $post_id, '_nice_client_name', sanitize_text_field( $record['client_name'] ?? '' ) );
			update_post_meta( $post_id, '_nice_location', sanitize_text_field( $record['location'] ) );
			update_post_meta( $post_id, '_nice_year', nice_sanitize_year( $record['year'] ) );
			update_post_meta( $post_id, '_nice_featured', $record['featured'] ? 1 : 0 );
			update_post_meta( $post_id, '_nice_display_order', (int) $record['order'] );
			$attachment_id = nice_migrate_media_attachment( $record['image'], $record['alt'] );
			if ( is_wp_error( $attachment_id ) ) {
				$summary['media']['errors'][] = $attachment_id->get_error_message();
			} else {
				set_post_thumbnail( $post_id, $attachment_id );
				++$summary['media']['linked'];
			}
		}
	}

	update_option( 'nice_core_content_migration_version', NICE_CORE_VERSION, false );

	return $summary;
}

/**
 * Run the migration from WP-CLI.
 */
function nice_cli_migrate_content() {
	$result = nice_run_content_migration();

	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}

	foreach ( array( 'clients', 'services', 'case_studies' ) as $content_type ) {
		WP_CLI::log(
			sprintf(
				'%s: %d created, %d skipped',
				ucwords( str_replace( '_', ' ', $content_type ) ),
				$result[ $content_type ]['created'],
				$result[ $content_type ]['skipped']
			)
		);
	}

	WP_CLI::log( sprintf( 'Terms: %d created, %d existing', $result['terms']['created'], $result['terms']['existing'] ) );
	WP_CLI::log( sprintf( 'Media linked: %d', $result['media']['linked'] ) );

	if ( $result['media']['errors'] ) {
		foreach ( array_unique( $result['media']['errors'] ) as $error ) {
			WP_CLI::warning( $error );
		}
	}

	WP_CLI::success( 'NICE content migration completed.' );
}

/**
 * Register the optional CLI command without affecting web requests.
 */
function nice_register_cli_commands() {
	WP_CLI::add_command( 'nice migrate-content', 'nice_cli_migrate_content' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	add_action( 'cli_init', 'nice_register_cli_commands' );
}
