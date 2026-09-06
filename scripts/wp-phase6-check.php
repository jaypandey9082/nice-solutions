<?php
/**
 * LocalWP runtime checks for the Events inner-page release.
 *
 * Run with: wp eval-file scripts/wp-phase6-check.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/**
 * Fail the runtime check with a useful message.
 *
 * @param bool   $condition Expected condition.
 * @param string $message   Failure message.
 */
function nice_phase6_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$temporary_id = 0;

try {
	nice_phase6_assert( defined( 'NICE_CORE_VERSION' ) && version_compare( NICE_CORE_VERSION, '1.1.0', '>=' ), 'NICE Core predates Phase 6.' );
	nice_phase6_assert( version_compare( wp_get_theme()->get( 'Version' ), '0.5.0', '>=' ), 'NICE theme predates Phase 6.' );

	foreach ( array( 'nice_service', 'nice_case_study' ) as $post_type ) {
		$object = get_post_type_object( $post_type );
		nice_phase6_assert( $object && false === $object->has_archive && false === $object->rewrite, "{$post_type} exposed a native archive or rewrite." );
	}

	$events = get_page_by_path( 'events', OBJECT, 'page' );
	nice_phase6_assert( $events instanceof WP_Post, 'The Events parent Page is missing.' );

	foreach ( nice_get_events_page_manifest() as $record ) {
		$page = get_page_by_path( 'events/' . $record['slug'], OBJECT, 'page' );
		nice_phase6_assert( $page instanceof WP_Post && 'publish' === $page->post_status, "Missing Events Page: {$record['slug']}" );
		nice_phase6_assert( $events->ID === $page->post_parent, "Incorrect Events parent for {$record['slug']}" );
		nice_phase6_assert( $record['template'] === get_page_template_slug( $page ), "Incorrect template for {$record['slug']}" );
	}

	$services = nice_get_events_services();
	nice_phase6_assert( 3 === count( $services ), 'Events must contain exactly three Services.' );
	nice_phase6_assert(
		array( 'Corporate Events', 'Exhibitions & Conferences', 'Activations & Promotions' ) === wp_list_pluck( $services, 'post_title' ),
		'Events Services are missing or incorrectly ordered.'
	);

	foreach ( $services as $service ) {
		$url = nice_get_events_content_url( $service );
		nice_phase6_assert( str_contains( $url, '/events/services/' . $service->post_name . '/' ), "Incorrect Service URL: {$service->post_name}" );
		nice_phase6_assert( $url === get_permalink( $service ), "Service canonical mismatch: {$service->post_name}" );
		nice_phase6_assert( has_term( 'events', 'nice_division', $service ), "Studio Service leaked into Events: {$service->post_name}" );
		nice_phase6_assert( str_contains( $service->post_content, 'What NICE delivers' ), "Service editor content is incomplete: {$service->post_name}" );
	}

	$case_studies = nice_get_case_studies( array( 'division' => 'events' ) );
	nice_phase6_assert( 5 === count( $case_studies ), 'Unexpected Events Case Study count.' );

	foreach ( $case_studies as $case_study ) {
		$url = nice_get_events_content_url( $case_study );
		nice_phase6_assert( str_contains( $url, '/events/case-studies/' . $case_study->post_name . '/' ), "Incorrect Case Study URL: {$case_study->post_name}" );
		nice_phase6_assert( $url === get_permalink( $case_study ), "Case Study canonical mismatch: {$case_study->post_name}" );
		nice_phase6_assert( has_post_thumbnail( $case_study ), "Case Study image is missing: {$case_study->post_name}" );
		nice_phase6_assert( '' !== trim( $case_study->post_content ), "Case Study narrative is missing: {$case_study->post_name}" );
	}

	$voltas = nice_get_case_study_by_slug( 'voltas-fam-tastic-fiesta' );
	$run    = nice_get_case_study_by_slug( 'run-for-equity' );
	nice_phase6_assert( '2,000+' === get_post_meta( $voltas->ID, '_nice_proof_value', true ), 'Voltas proof is missing.' );
	nice_phase6_assert( '5,000+' === get_post_meta( $run->ID, '_nice_proof_value', true ), 'RunForEquity proof is missing.' );

	foreach ( array( 'corporate-events', 'exhibitions-conferences', 'activations-promotions' ) as $service_type ) {
		$group = nice_get_case_studies_by_service( $service_type, array( 'division' => 'events', 'posts_per_page' => 3 ) );
		nice_phase6_assert( count( $group ) <= 3, "Case Study group exceeds three records: {$service_type}" );
		foreach ( $group as $item ) {
			nice_phase6_assert( has_term( $service_type, 'nice_service_type', $item ), "Case Study grouping mismatch: {$item->post_name}" );
		}
	}

	nice_phase6_assert( 10 === count( nice_get_clients() ), 'The shared Client dataset is incomplete.' );
	nice_phase6_assert( 0 === count( nice_get_team_members_by_division( 'events' ) ), 'Unexpected published Events Team records.' );
	$contact = nice_get_contact_settings();
	nice_phase6_assert( empty( $contact['whatsapp_url'] ) && empty( $contact['email_address'] ) && empty( $contact['phone_url'] ), 'Contact settings are no longer publication-pending.' );

	$registry = WP_Block_Type_Registry::get_instance();
	foreach ( array( 'nice/events-services-index', 'nice/events-service-detail', 'nice/events-case-studies-index', 'nice/events-case-study-detail', 'nice/events-clients-index', 'nice/events-team-index', 'nice/events-contact-page' ) as $block ) {
		nice_phase6_assert( $registry->is_registered( $block ), "Missing server-rendered block: {$block}" );
	}

	$temporary_id = wp_insert_post(
		array(
			'post_type'    => 'nice_case_study',
			'post_status'  => 'publish',
			'post_name'    => 'phase-6-render-check',
			'post_title'   => 'Phase 6 Render Check',
			'post_excerpt' => 'Temporary browser-facing CMS verification record.',
			'post_content' => '<p>Temporary browser-facing CMS verification record.</p>',
		),
		true
	);
	nice_phase6_assert( ! is_wp_error( $temporary_id ), 'Could not create temporary CMS verification record.' );
	wp_set_object_terms( $temporary_id, 'activations-promotions', 'nice_service_type', false );
	update_post_meta( $temporary_id, '_nice_display_order', 999 );

	$index_markup = do_blocks( '<!-- wp:nice/events-case-studies-index /-->' );
	nice_phase6_assert( str_contains( $index_markup, 'Phase 6 Render Check' ), 'A new published CMS record did not reach the frontend renderer.' );

	wp_update_post( array( 'ID' => $temporary_id, 'post_title' => 'Phase 6 Updated Render Check' ) );
	$index_markup = do_blocks( '<!-- wp:nice/events-case-studies-index /-->' );
	nice_phase6_assert( str_contains( $index_markup, 'Phase 6 Updated Render Check' ), 'An edited CMS record did not reach the frontend renderer.' );

	WP_CLI::success( 'NICE Phase 6 runtime checks passed.' );
} finally {
	if ( $temporary_id ) {
		wp_delete_post( $temporary_id, true );
	}
}
