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
				'content'      => '<p>An integral part of brand communication, Events is where our heart is. NICE brings thoughtful planning and execution to corporate and audience experiences.</p><h2>What NICE delivers</h2><ul><li>Corporate Events</li><li>Shows &amp; Concerts</li><li>Technology Integration</li></ul>',
				'service_type' => 'corporate-events',
				'image'        => 'zoetis-engagement.webp',
				'alt'          => 'Attendee posing with costumed characters at an employee event',
			),
			array(
				'slug'         => 'exhibitions-conferences',
				'title'        => 'Exhibitions & Conferences',
				'description'  => 'Conference environments, exhibition stalls and technical setups coordinated across venue, production and delivery.',
				'content'      => '<p>Thoughtful planning and execution shape NICE work across corporate and trade conferences, exhibition stall design and fabrication.</p><h2>What NICE delivers</h2><ul><li>Trade Meets &amp; Conferences</li><li>Venue Construction / Hanger Installations</li><li>Custom Stall Design &amp; Fabrication</li><li>Interactive &amp; Engaging Displays</li><li>Seminar Set-ups in Exhibitions</li><li>End-to-End Project Management</li></ul>',
				'service_type' => 'exhibitions-conferences',
				'image'        => 'exhibition-stall.webp',
				'alt'          => 'Custom exhibition stall installed inside an exhibition hall',
			),
			array(
				'slug'         => 'activations-promotions',
				'title'        => 'Activations & Promotions',
				'description'  => 'Audience-facing programmes and awareness initiatives designed to bring people into the experience.',
				'content'      => '<p>NICE plans promotions and activations designed to bring people into the experience through focused, audience-facing execution.</p><h2>What NICE delivers</h2><ul><li>Promotions &amp; Activations</li></ul>',
				'service_type' => 'activations-promotions',
				'image'        => 'power-champs.webp',
				'alt'          => 'Students presenting during a POWER CHAMPS awareness programme',
			),
			array(
				'slug'         => 'corporate-videos',
				'title'        => 'Corporate Videos',
				'description'  => 'Professionally crafted corporate profiles and product or solution audiovisuals shaped to communicate with clarity and impact.',
				'content'      => '<p>NICE creates audiovisual communication for organisations across manufacturing, technology, finance and services, bringing brand identity, core values and key offerings to the screen.</p><h2>What NICE delivers</h2><ul><li>Corporate profiles</li><li>Product and solution audiovisuals</li><li>Motion graphics</li><li>Stock-video integration</li><li>Factory and location shoots</li></ul>',
				'service_type' => 'corporate-videos',
				'image'        => 'strata-production.webp',
				'alt'          => 'NICE production crew filming inside the Strata Geosystems factory',
			),
			array(
				'slug'         => 'digital-content-creation',
				'title'        => 'Digital Content Creation',
				'description'  => 'Digital videos and platform-aware stories that make products, solutions and ideas clear for audiences across digital channels.',
				'content'      => '<p>NICE develops digital storytelling for apps, YouTube channels and social platforms, translating products, solutions and educational ideas into clear visual narratives.</p><h2>What NICE delivers</h2><ul><li>Digital videos</li><li>Social and digital storytelling</li><li>Platform-specific content</li><li>Product and solution explainers</li><li>YouTube and digital-channel content</li></ul>',
				'service_type' => 'digital-content-creation',
				'image'        => 'studio-krish-e.webp',
				'alt'          => 'Farmer working in a field in a Krish-e digital content frame',
			),
			array(
				'slug'         => 'films-entertainment',
				'title'        => 'Films & Entertainment',
				'description'  => 'Film production and cinematic storytelling carried from creative production through marketing and promotion.',
				'content'      => '<p>NICE works across film and entertainment production with a focus on meaningful cinema, cinematic storytelling and the path from production to screen.</p><h2>What NICE delivers</h2><ul><li>Film production</li><li>Cinematic storytelling</li><li>Entertainment production</li><li>Film marketing and promotions</li></ul>',
				'service_type' => 'films-entertainment',
				'image'        => 'studio-jayanti.webp',
				'alt'          => 'Jayanti cast and production team gathered outdoors',
			),
		),
		'case_studies' => array(
			array(
				'slug'         => 'voltas-fam-tastic-fiesta',
				'title'        => 'Voltas Fam-Tastic Fiesta',
				'description'  => 'An employee family fiesta for Voltas Limited at The Parsi Gymkhana, Mumbai.',
				'content'      => '<p>The Voltas Fam-Tastic Fiesta brought more than 2,000 people together for an employee family celebration at The Parsi Gymkhana in Mumbai. NICE planned and executed the event around shared moments, participation and celebration.</p>',
				'service_type' => 'corporate-events',
				'client_slug'  => 'voltas-limited',
				'location'     => 'The Parsi Gymkhana, Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 10,
				'image'        => 'voltas-fam-tastic.webp',
				'alt'          => 'NICE and Voltas team members standing on the event stage',
				'proof_value'  => '2,000+',
				'proof_label'  => 'attendees at the Voltas Fam-Tastic Fiesta',
			),
			array(
				'slug'         => 'gca-2025',
				'title'        => 'GCA 2025',
				'description'  => 'The three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India.',
				'content'      => '<p>NICE executed the three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India. The programme brought together performances, awards and panel discussions in an event shaped around learning, energy and exchange.</p>',
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
				'content'      => '<p>A power-packed engagement day for 200 Zoetis team members, built around connection and celebration from start to finish.</p>',
				'service_type' => 'corporate-events',
				'client_slug'  => 'zoetis',
				'location'     => 'Zoetis Campus, Navi Mumbai',
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
				'content'      => '<p>NICE partnered with Ajay Thakur, former Head of SME and Startups at BSE, for the launch of his book <em>Vision to Victory</em>. From concept through celebration, the event reflected his journey in India\'s SME ecosystem.</p>',
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
				'content'      => '<p>RunForEquity was conceived in 2017 as a social run and a tribute to Dr. Babasaheb Ambedkar. Its second edition brought together more than 5,000 runners and was rated among India\'s top runs through participant ratings.</p>',
				'service_type' => 'activations-promotions',
				'client_name'  => 'NICE Intellectual Property',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 50,
				'image'        => 'run-for-equity.webp',
				'alt'          => 'Participants running together during RunForEquity',
				'proof_value'  => '5,000+',
				'proof_label'  => 'runners in the second edition of RunForEquity',
			),
			array(
				'slug'         => 'strata-geosystems-factory-shoot',
				'title'        => 'Strata Geosystems Factory Shoot',
				'description'  => 'A factory shoot with drone and FPV presentation for Strata Geosystems India in Gujarat.',
				'content'      => '<p>NICE produced a factory shoot video for Strata Geosystems, capturing the scale, precision and technology of its Gujarat operations. The finished film presents the company\'s manufacturing capabilities and infrastructure for international audiences.</p>',
				'service_type' => 'corporate-videos',
				'client_name'  => 'Strata Geosystems India',
				'location'     => 'Gujarat',
				'year'         => 0,
				'featured'     => true,
				'order'        => 110,
				'image'        => 'strata-production.webp',
				'alt'          => 'NICE production crew filming inside the Strata Geosystems factory',
			),
			array(
				'slug'         => 'career-agents-academy',
				'title'        => 'Career Agents Academy',
				'description'  => 'A digital campaign video for the Bajaj Group\'s Career Agents Academy.',
				'content'      => '<p>NICE partnered with the Bajaj Group to create a digital campaign video for Career Agents Academy, a programme for insurance advisors. The film presents the programme\'s benefits and professional opportunities for digital outreach and recruitment.</p>',
				'service_type' => 'digital-content-creation',
				'client_slug'  => 'bajaj',
				'location'     => 'Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 120,
				'image'        => 'studio-career-agents.webp',
				'alt'          => 'Camera monitor framing a Career Agents Academy production scene',
			),
			array(
				'slug'         => 'krish-e',
				'title'        => 'Krish-e',
				'description'  => 'Digital content for the Mahindra Group\'s Krish-e farmer facilitation app and YouTube channel.',
				'content'      => '<p>NICE partnered with the Mahindra Group to produce digital content for the Krish-e farmer facilitation app and YouTube channel. The videos simplify agri-tech solutions and best practices for rural audiences across digital touchpoints.</p>',
				'service_type' => 'digital-content-creation',
				'client_slug'  => 'mahindra',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 130,
				'image'        => 'studio-krish-e.webp',
				'alt'          => 'Farmer working in a field in a Krish-e digital content frame',
			),
			array(
				'slug'         => 'crisil-financial-literacy-content',
				'title'        => 'CRISIL Financial Literacy Content',
				'description'  => 'Drama-based visual content designed to make financial literacy accessible and engaging.',
				'content'      => '<p>NICE produced drama-based content for CRISIL Foundation\'s financial literacy initiative. The visual narrative makes financial concepts more accessible while supporting awareness and inclusion through clear, engaging storytelling.</p>',
				'service_type' => 'digital-content-creation',
				'client_slug'  => 'crisil',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 140,
				'image'        => 'studio-crisil-literacy.webp',
				'alt'          => 'NICE crew filming a financial literacy scene on a village set',
			),
			array(
				'slug'         => 'jayanti',
				'title'        => 'Jayanti',
				'description'  => 'NICE\'s film-production involvement as Executive Producers of the Marathi film Jayanti.',
				'content'      => '<p>NICE entered film production as Executive Producers of the Marathi film <em>Jayanti</em>. Alongside production, the team led marketing, promotions and the film\'s theatrical rollout.</p>',
				'service_type' => 'films-entertainment',
				'client_name'  => '',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 150,
				'image'        => 'studio-jayanti.webp',
				'alt'          => 'Jayanti cast and production team gathered outdoors',
			),
		),
	);
}

/**
 * Return the approved Events section Page manifest.
 *
 * @return array<int, array{slug: string, title: string, template: string}>
 */
function nice_get_events_page_manifest() {
	return array(
		array( 'slug' => 'services', 'title' => 'Events Services', 'template' => 'page-events-services' ),
		array( 'slug' => 'case-studies', 'title' => 'Events Case Studies', 'template' => 'page-events-case-studies' ),
		array( 'slug' => 'clients', 'title' => 'Events Clients', 'template' => 'page-events-clients' ),
		array( 'slug' => 'team', 'title' => 'Events Team', 'template' => 'page-events-team' ),
		array( 'slug' => 'contact', 'title' => 'Events Contact', 'template' => 'page-events-contact' ),
	);
}

/**
 * Provision only the approved structural Events Pages.
 *
 * Existing page content and titles are preserved.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_provision_events_pages() {
	$summary = array( 'created' => 0, 'skipped' => 0, 'errors' => array() );
	$events  = get_page_by_path( 'events', OBJECT, 'page' );

	if ( ! $events instanceof WP_Post ) {
		$events_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => 'events',
				'post_title'  => 'Events',
			),
			true
		);

		if ( is_wp_error( $events_id ) ) {
			$summary['errors'][] = $events_id->get_error_message();
			return $summary;
		}

		$events = get_post( $events_id );
	}

	foreach ( nice_get_events_page_manifest() as $record ) {
		$page = get_page_by_path( 'events/' . $record['slug'], OBJECT, 'page' );

		if ( $page instanceof WP_Post ) {
			++$summary['skipped'];
		} else {
			$page_id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_parent' => $events->ID,
					'post_name'   => $record['slug'],
					'post_title'  => $record['title'],
				),
				true
			);

			if ( is_wp_error( $page_id ) ) {
				$summary['errors'][] = $page_id->get_error_message();
				continue;
			}

			$page = get_post( $page_id );
			++$summary['created'];
		}

		update_post_meta( $page->ID, '_wp_page_template', $record['template'] );
	}

	return $summary;
}

/**
 * Provision the approved Studio Home Page without adding Phase 8 routes.
 *
 * Existing page content and titles are preserved.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_provision_studio_page() {
	$summary = array( 'created' => 0, 'skipped' => 0, 'errors' => array() );
	$page    = get_page_by_path( 'studio', OBJECT, 'page' );

	if ( $page instanceof WP_Post ) {
		++$summary['skipped'];
		return $summary;
	}

	$page_id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_name'   => 'studio',
			'post_title'  => 'Studio',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		$summary['errors'][] = $page_id->get_error_message();
		return $summary;
	}

	++$summary['created'];

	return $summary;
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
			'post_content' => wp_kses_post( $record['content'] ?? $record['description'] ?? '' ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	return array( 'status' => 'created', 'post_id' => $post_id );
}

/**
 * Enrich untouched Phase 5 records with source-approved Phase 6 editor content.
 *
 * Editorial changes are never overwritten.
 *
 * @param string               $post_type Post type.
 * @param array<string, mixed> $record    Migration record.
 * @return int Number of updated fields.
 */
function nice_enrich_migrated_content( $post_type, $record ) {
	$post = nice_find_migrated_post( $record['slug'], $post_type );

	if ( ! $post instanceof WP_Post ) {
		return 0;
	}

	$updates        = 0;
	$legacy_content = trim( (string) ( $record['description'] ?? '' ) );
	$new_content    = trim( (string) ( $record['content'] ?? '' ) );

	if ( $new_content && trim( $post->post_content ) === $legacy_content ) {
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => wp_kses_post( $new_content ),
			)
		);
		++$updates;
	}

	if ( 'nice_case_study' === $post_type ) {
		foreach ( array( '_nice_location' => 'location', '_nice_proof_value' => 'proof_value', '_nice_proof_label' => 'proof_label' ) as $meta_key => $record_key ) {
			if ( ! get_post_meta( $post->ID, $meta_key, true ) && ! empty( $record[ $record_key ] ) ) {
				update_post_meta( $post->ID, $meta_key, sanitize_text_field( $record[ $record_key ] ) );
				++$updates;
			}
		}
	}

	return $updates;
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
		'pages'        => nice_provision_events_pages(),
		'studio_page'  => nice_provision_studio_page(),
		'clients'      => array( 'created' => 0, 'skipped' => 0 ),
		'services'     => array( 'created' => 0, 'skipped' => 0 ),
		'case_studies' => array( 'created' => 0, 'skipped' => 0 ),
		'media'        => array( 'linked' => 0, 'errors' => array() ),
		'enriched'     => 0,
	);

	if ( ! empty( $summary['terms']['errors'] ) ) {
		return new WP_Error( 'nice_term_migration_failed', implode( ' ', $summary['terms']['errors'] ) );
	}
	if ( ! empty( $summary['pages']['errors'] ) ) {
		return new WP_Error( 'nice_page_migration_failed', implode( ' ', $summary['pages']['errors'] ) );
	}
	if ( ! empty( $summary['studio_page']['errors'] ) ) {
		return new WP_Error( 'nice_studio_page_migration_failed', implode( ' ', $summary['studio_page']['errors'] ) );
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

		$summary['enriched'] += nice_enrich_migrated_content( 'nice_service', $record );
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
			update_post_meta( $post_id, '_nice_proof_value', sanitize_text_field( $record['proof_value'] ?? '' ) );
			update_post_meta( $post_id, '_nice_proof_label', sanitize_text_field( $record['proof_label'] ?? '' ) );
			$attachment_id = nice_migrate_media_attachment( $record['image'], $record['alt'] );
			if ( is_wp_error( $attachment_id ) ) {
				$summary['media']['errors'][] = $attachment_id->get_error_message();
			} else {
				set_post_thumbnail( $post_id, $attachment_id );
				++$summary['media']['linked'];
			}
		}

		$summary['enriched'] += nice_enrich_migrated_content( 'nice_case_study', $record );
	}

	update_option( 'nice_core_content_migration_version', NICE_CORE_VERSION, false );

	if ( $summary['pages']['created'] || $summary['studio_page']['created'] ) {
		flush_rewrite_rules( false );
	}

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
	WP_CLI::log( sprintf( 'Events pages: %d created, %d existing', $result['pages']['created'], $result['pages']['skipped'] ) );
	WP_CLI::log( sprintf( 'Studio Home: %d created, %d existing', $result['studio_page']['created'], $result['studio_page']['skipped'] ) );
	WP_CLI::log( sprintf( 'Source-backed fields enriched: %d', $result['enriched'] ) );
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
