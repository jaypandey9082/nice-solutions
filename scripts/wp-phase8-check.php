<?php
/**
 * LocalWP runtime checks for Phase 8 Studio Inner Pages.
 *
 * Run with: wp eval-file scripts/wp-phase8-check.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/**
 * Fail with a useful message.
 *
 * @param bool   $condition Expected condition.
 * @param string $message   Failure message.
 */
function nice_phase8_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

// 0. Ensure Studio pages and rewrites are provisioned.
if ( function_exists( 'nice_provision_studio_pages' ) ) {
	nice_provision_studio_pages();
}
if ( function_exists( 'nice_register_content_rewrite_rules' ) ) {
	nice_register_content_rewrite_rules();
}
flush_rewrite_rules();

// 1. Version checks.
nice_phase8_assert( defined( 'NICE_CORE_VERSION' ) && '1.2.0' === NICE_CORE_VERSION, 'Unexpected NICE Core version.' );
nice_phase8_assert( '0.7.0' === wp_get_theme()->get( 'Version' ), 'Unexpected NICE theme version.' );

// 2. Studio parent page check.
$studio = get_page_by_path( 'studio', OBJECT, 'page' );
nice_phase8_assert( $studio instanceof WP_Post && 'publish' === $studio->post_status, 'Studio Home Page is missing.' );

// 3. Studio inner pages checks.
$expected_pages = array(
	'services'     => 'page-studio-services',
	'case-studies' => 'page-studio-case-studies',
	'clients'      => 'page-studio-clients',
	'team'         => 'page-studio-team',
	'contact'      => 'page-studio-contact',
);

foreach ( $expected_pages as $slug => $template ) {
	$page = get_page_by_path( 'studio/' . $slug, OBJECT, 'page' );
	nice_phase8_assert( $page instanceof WP_Post && 'publish' === $page->post_status, "Missing Studio inner page: studio/{$slug}" );
	$assigned_template = get_post_meta( $page->ID, '_wp_page_template', true );
	nice_phase8_assert( $template === $assigned_template, "Template mismatch for studio/{$slug}: expected {$template}, got {$assigned_template}" );
}

// 4. Studio Services checks.
$service_slugs = array( 'corporate-videos', 'digital-content-creation', 'films-entertainment' );
$service_names = array( 'Corporate Videos', 'Digital Content Creation', 'Films & Entertainment' );
$services      = nice_get_studio_services();

nice_phase8_assert( $service_slugs === wp_list_pluck( $services, 'post_name' ), 'Studio Services are missing or incorrectly ordered.' );
nice_phase8_assert( $service_names === wp_list_pluck( $services, 'post_title' ), 'Studio Service names do not match the sitemap.' );

foreach ( $services as $index => $service ) {
	nice_phase8_assert( has_term( 'studio', 'nice_division', $service ), "Studio division missing: {$service->post_name}" );
	nice_phase8_assert( has_term( $service_slugs[ $index ], 'nice_service_type', $service ), "Studio Service Type mismatch: {$service->post_name}" );
	nice_phase8_assert( has_post_thumbnail( $service ), "Studio Service image missing: {$service->post_name}" );
	nice_phase8_assert( str_contains( $service->post_content, 'What NICE delivers' ), "Studio Service content incomplete: {$service->post_name}" );

	$url = nice_get_content_url( $service );
	nice_phase8_assert( str_contains( $url, "/studio/services/{$service->post_name}/" ), "Canonical URL mismatch for service: {$url}" );
}

// 5. Studio Case Studies checks.
$expected_cases = array(
	'strata-geosystems-factory-shoot',
	'career-agents-academy',
	'krish-e',
	'crisil-financial-literacy-content',
	'jayanti',
);
$case_studies = nice_get_case_studies( array( 'division' => 'studio' ) );

nice_phase8_assert( $expected_cases === wp_list_pluck( $case_studies, 'post_name' ), 'Studio Case Studies are missing or incorrectly ordered.' );

foreach ( $case_studies as $case_study ) {
	$types = wp_get_object_terms( $case_study->ID, 'nice_service_type', array( 'fields' => 'slugs' ) );
	nice_phase8_assert( has_term( 'studio', 'nice_division', $case_study ), "Events content leaked into Studio: {$case_study->post_name}" );
	nice_phase8_assert( 1 === count( $types ) && in_array( $types[0], $service_slugs, true ), "Studio Case Study classification invalid: {$case_study->post_name}" );
	nice_phase8_assert( has_post_thumbnail( $case_study ), "Studio Case Study image missing: {$case_study->post_name}" );
	nice_phase8_assert( '' !== trim( $case_study->post_content ), "Studio Case Study narrative missing: {$case_study->post_name}" );

	$url = nice_get_content_url( $case_study );
	nice_phase8_assert( str_contains( $url, "/studio/case-studies/{$case_study->post_name}/" ), "Canonical URL mismatch for case study: {$url}" );
}

// 6. Clients & Team checks.
nice_phase8_assert( 10 === count( nice_get_clients() ), 'Shared Client CPT dataset changed unexpectedly.' );
$studio_team = nice_get_team_members_by_division( 'studio' );
nice_phase8_assert( 0 === count( $studio_team ), 'Studio team members should be 0 until approved profiles exist.' );

// 7. Events regression checks.
nice_phase8_assert( 3 === count( nice_get_events_services() ), 'Events services count changed unexpectedly.' );
nice_phase8_assert( 5 === count( nice_get_case_studies( array( 'division' => 'events' ) ) ), 'Events case studies count changed unexpectedly.' );

// 8. Block registry checks.
$registry = WP_Block_Type_Registry::get_instance();
$studio_blocks = array(
	'nice/studio-section-navigation',
	'nice/studio-services-index',
	'nice/studio-service-detail',
	'nice/studio-case-studies-index',
	'nice/studio-case-study-detail',
	'nice/studio-clients-index',
	'nice/studio-team-index',
	'nice/studio-contact-page',
);

foreach ( $studio_blocks as $block_name ) {
	nice_phase8_assert( $registry->is_registered( $block_name ), "Studio block not registered: {$block_name}" );
}

// 9. CMS Testing (Section 36): Create temporary draft Service & Case Study, verify, then DELETE.
$draft_service_id = wp_insert_post(
	array(
		'post_type'    => 'nice_service',
		'post_status'  => 'draft',
		'post_title'   => 'Temporary Test Service',
		'post_name'    => 'temp-test-service',
		'post_content' => '<p>Temporary test service content.</p>',
	),
	true
);
nice_phase8_assert( ! is_wp_error( $draft_service_id ), 'Failed to create temporary draft Service.' );

wp_set_object_terms( $draft_service_id, 'corporate-videos', 'nice_service_type', false );
wp_set_object_terms( $draft_service_id, 'studio', 'nice_division', false );

$retrieved_service = get_post( $draft_service_id );
nice_phase8_assert( has_term( 'studio', 'nice_division', $retrieved_service ), 'Draft service missing studio division.' );
nice_phase8_assert( has_term( 'corporate-videos', 'nice_service_type', $retrieved_service ), 'Draft service missing service type.' );

// Clean up draft service.
wp_delete_post( $draft_service_id, true );
nice_phase8_assert( null === get_post( $draft_service_id ), 'Temporary draft service was not properly deleted.' );

// Temporary draft Case Study test.
$draft_case_id = wp_insert_post(
	array(
		'post_type'    => 'nice_case_study',
		'post_status'  => 'draft',
		'post_title'   => 'Temporary Test Case Study',
		'post_name'    => 'temp-test-case-study',
		'post_content' => '<p>Temporary test case study story.</p>',
	),
	true
);
nice_phase8_assert( ! is_wp_error( $draft_case_id ), 'Failed to create temporary draft Case Study.' );

wp_set_object_terms( $draft_case_id, 'films-entertainment', 'nice_service_type', false );
wp_set_object_terms( $draft_case_id, 'studio', 'nice_division', false );
update_post_meta( $draft_case_id, '_nice_location', 'Mumbai' );
update_post_meta( $draft_case_id, '_nice_year', 2025 );
update_post_meta( $draft_case_id, '_nice_hero_video_url', 'https://example.com/video.mp4' );
update_post_meta( $draft_case_id, '_nice_quote_text', 'A visionary collaboration.' );
update_post_meta( $draft_case_id, '_nice_quote_author', 'Producer Name' );

$retrieved_case = get_post( $draft_case_id );
nice_phase8_assert( has_term( 'studio', 'nice_division', $retrieved_case ), 'Draft case missing studio division.' );
nice_phase8_assert( has_term( 'films-entertainment', 'nice_service_type', $retrieved_case ), 'Draft case missing service type.' );
nice_phase8_assert( 'Mumbai' === get_post_meta( $draft_case_id, '_nice_location', true ), 'Draft case meta location mismatch.' );
nice_phase8_assert( 2025 === (int) get_post_meta( $draft_case_id, '_nice_year', true ), 'Draft case meta year mismatch.' );
nice_phase8_assert( 'https://example.com/video.mp4' === get_post_meta( $draft_case_id, '_nice_hero_video_url', true ), 'Draft case video URL mismatch.' );
nice_phase8_assert( 'A visionary collaboration.' === get_post_meta( $draft_case_id, '_nice_quote_text', true ), 'Draft case quote text mismatch.' );
nice_phase8_assert( 'Producer Name' === get_post_meta( $draft_case_id, '_nice_quote_author', true ), 'Draft case quote author mismatch.' );

// Clean up draft case study.
wp_delete_post( $draft_case_id, true );
nice_phase8_assert( null === get_post( $draft_case_id ), 'Temporary draft case study was not properly deleted.' );

echo "Phase 8 runtime assertions and CMS tests passed successfully.\n";
