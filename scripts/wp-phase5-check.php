<?php
/**
 * Runtime acceptance checks for NICE Core. Run with `wp eval-file`.
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

/**
 * Stop the runtime check on a failed assertion.
 *
 * @param bool   $condition Assertion result.
 * @param string $message   Failure detail.
 */
function nice_phase5_assert( $condition, $message ) {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
}

$administrator = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
	)
);
nice_phase5_assert( ! empty( $administrator ), 'An administrator is required for REST edit-context checks.' );
wp_set_current_user( $administrator[0]->ID );

foreach ( array( 'nice_service', 'nice_case_study', 'nice_client', 'nice_team_member' ) as $post_type ) {
	nice_phase5_assert( post_type_exists( $post_type ), "Missing post type: {$post_type}" );
	$object = get_post_type_object( $post_type );
	nice_phase5_assert( $object->show_in_rest, "REST is disabled for {$post_type}." );
}

foreach ( array( 'nice_division', 'nice_service_type' ) as $taxonomy ) {
	nice_phase5_assert( taxonomy_exists( $taxonomy ), "Missing taxonomy: {$taxonomy}" );
	nice_phase5_assert( get_taxonomy( $taxonomy )->show_in_rest, "REST is disabled for {$taxonomy}." );
}

$division_slugs = get_terms( array( 'taxonomy' => 'nice_division', 'hide_empty' => false, 'fields' => 'slugs' ) );
$service_slugs  = get_terms( array( 'taxonomy' => 'nice_service_type', 'hide_empty' => false, 'fields' => 'slugs' ) );
sort( $division_slugs );
sort( $service_slugs );
$expected_divisions = array_keys( nice_get_approved_divisions() );
$expected_services  = array_keys( nice_get_approved_service_types() );
sort( $expected_divisions );
sort( $expected_services );
nice_phase5_assert( $expected_divisions === $division_slugs, 'Division terms differ from the approved vocabulary.' );
nice_phase5_assert( $expected_services === $service_slugs, 'Service Type terms differ from the approved vocabulary.' );
$unsupported_term = wp_insert_term( 'Unsupported Service', 'nice_service_type' );
nice_phase5_assert( is_wp_error( $unsupported_term ) && 'nice_unapproved_term' === $unsupported_term->get_error_code(), 'An unsupported Service Type term was accepted.' );

$case_meta = get_registered_meta_keys( 'post', 'nice_case_study' );
foreach ( array( '_nice_client_id', '_nice_location', '_nice_year', '_nice_featured', '_nice_display_order' ) as $meta_key ) {
	nice_phase5_assert( isset( $case_meta[ $meta_key ] ), "Missing registered Case Study meta: {$meta_key}" );
}

$events_services = nice_get_events_services();
$case_studies    = nice_get_case_studies( array( 'division' => 'events' ) );
$clients         = nice_get_clients();
nice_phase5_assert( 3 === count( $events_services ), 'Expected three migrated Events Services.' );
nice_phase5_assert( 5 === count( $case_studies ), 'Expected five migrated Events Case Studies.' );
nice_phase5_assert( 10 === count( $clients ), 'Expected ten source-approved Client records.' );
nice_phase5_assert( empty( nice_get_studio_services() ), 'Studio Service records must not be invented.' );
nice_phase5_assert( empty( nice_get_team_members() ), 'Team Member records must remain empty without approved people.' );

do_action( 'add_meta_boxes' );
global $wp_meta_boxes;
nice_phase5_assert( isset( $wp_meta_boxes['nice_service']['side']['high']['nice-service-classification'] ), 'Service Classification admin panel is missing.' );
nice_phase5_assert( isset( $wp_meta_boxes['nice_case_study']['normal']['high']['nice-case-project-information'] ), 'Case Study Project Information panel is missing.' );
nice_phase5_assert( isset( $wp_meta_boxes['nice_case_study']['side']['default']['nice-case-portfolio-controls'] ), 'Case Study Portfolio Controls panel is missing.' );
nice_phase5_assert( isset( $wp_meta_boxes['nice_client']['normal']['default']['nice-client-details'] ), 'Client Details admin panel is missing.' );
nice_phase5_assert( isset( $wp_meta_boxes['nice_team_member']['normal']['default']['nice-team-details'] ), 'Team Member Details admin panel is missing.' );

foreach ( array_merge( $events_services, $case_studies ) as $record ) {
	nice_phase5_assert( has_post_thumbnail( $record ), "Featured image missing for {$record->post_title}." );
}

$gca = nice_get_case_study_by_slug( 'gca-2025' );
nice_phase5_assert( $gca instanceof WP_Post, 'GCA 2025 was not migrated.' );
nice_phase5_assert( 2025 === (int) get_post_meta( $gca->ID, '_nice_year', true ), 'GCA year metadata is incorrect.' );
nice_phase5_assert( 'Institute of Actuaries of India' === nice_get_case_study_client_name( $gca->ID ), 'GCA Client relationship is incorrect.' );
nice_phase5_assert( has_term( 'events', 'nice_division', $gca ), 'GCA Division assignment is missing.' );
nice_phase5_assert( has_term( 'exhibitions-conferences', 'nice_service_type', $gca ), 'GCA Service Type assignment is missing.' );

$routes = rest_get_server()->get_routes();
foreach ( array( '/wp/v2/nice-services', '/wp/v2/nice-case-studies', '/wp/v2/nice-clients', '/wp/v2/nice-team-members', '/wp/v2/nice-divisions', '/wp/v2/nice-service-types' ) as $route ) {
	nice_phase5_assert( isset( $routes[ $route ] ), "Missing REST route: {$route}" );
}

$rest_response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/nice-case-studies/' . $gca->ID ) );
nice_phase5_assert( 200 === $rest_response->get_status(), 'Case Study REST request failed.' );
$rest_data = $rest_response->get_data();
nice_phase5_assert( isset( $rest_data['meta']['_nice_year'] ) && 2025 === $rest_data['meta']['_nice_year'], 'Registered Case Study meta is unavailable in REST.' );

wp_set_current_user( 0 );
$public_rest_response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/nice-case-studies/' . $gca->ID ) );
$public_rest_data     = $public_rest_response->get_data();
nice_phase5_assert( 200 === $public_rest_response->get_status(), 'Public Case Study REST request failed.' );
nice_phase5_assert( isset( $public_rest_data['meta']['_nice_year'] ) && 2025 === $public_rest_data['meta']['_nice_year'], 'Approved Case Study meta is unavailable publicly in REST.' );
wp_set_current_user( $administrator[0]->ID );

$original_contact_exists = false;
$original_contact        = get_option( 'nice_contact_settings', null );
if ( null !== $original_contact ) {
	$original_contact_exists = true;
}

$valid_contact = nice_sanitize_contact_settings(
	array(
		'whatsapp_url' => 'https://example.com/whatsapp-test',
		'email_address'=> 'qa@example.com',
		'phone'        => '+91 (000) 000-0000',
		'social_urls'  => "https://example.com/social-test\nhttps://example.com/social-test",
	)
);
nice_phase5_assert( 'https://example.com/whatsapp-test' === $valid_contact['whatsapp_url'], 'Valid WhatsApp URL was rejected.' );
nice_phase5_assert( 'qa@example.com' === $valid_contact['email_address'], 'Valid email was rejected.' );
nice_phase5_assert( 1 === count( $valid_contact['social_urls'] ), 'Social URL validation or de-duplication failed.' );
update_option( 'nice_contact_settings', $valid_contact, false );
nice_phase5_assert( 'tel:+910000000000' === nice_get_contact_phone_url(), 'Phone URL normalization failed.' );

$invalid_contact = nice_sanitize_contact_settings(
	array(
		'whatsapp_url' => 'http://unsafe.example.com',
		'email_address'=> 'not-an-email',
		'social_urls'  => 'javascript:alert(1)',
	)
);
nice_phase5_assert( 'http://unsafe.example.com' !== $invalid_contact['whatsapp_url'], 'Unsafe WhatsApp URL was accepted.' );
nice_phase5_assert( 'not-an-email' !== $invalid_contact['email_address'], 'Invalid email was accepted.' );
nice_phase5_assert( ! in_array( 'javascript:alert(1)', $invalid_contact['social_urls'], true ), 'Unsafe social URL was accepted.' );

if ( $original_contact_exists ) {
	update_option( 'nice_contact_settings', $original_contact, false );
} else {
	delete_option( 'nice_contact_settings' );
}

$test_service_id = wp_insert_post(
	array(
		'post_type'   => 'nice_service',
		'post_status' => 'draft',
		'post_title'  => 'NICE Core relationship check',
	),
	true
);
nice_phase5_assert( ! is_wp_error( $test_service_id ), 'Could not create taxonomy validation record.' );
wp_set_object_terms( $test_service_id, 'corporate-videos', 'nice_service_type', false );
wp_set_object_terms( $test_service_id, 'events', 'nice_division', false );
nice_phase5_assert( has_term( 'studio', 'nice_division', $test_service_id ), 'Invalid Division/Service Type combination was not corrected.' );
wp_delete_post( $test_service_id, true );

$test_client_id = wp_insert_post(
	array(
		'post_type'   => 'nice_client',
		'post_status' => 'publish',
		'post_name'   => 'nice-core-client-check',
		'post_title'  => 'NICE Core client check',
	),
	true
);
nice_phase5_assert( ! is_wp_error( $test_client_id ) && nice_get_client_by_slug( 'nice-core-client-check' ) instanceof WP_Post, 'A Client without display-order meta is missing from helpers.' );
wp_delete_post( $test_client_id, true );

$test_team_id = wp_insert_post(
	array(
		'post_type'   => 'nice_team_member',
		'post_status' => 'draft',
		'post_title'  => 'NICE Core team check',
	),
	true
);
nice_phase5_assert( ! is_wp_error( $test_team_id ), 'Could not create Team Member validation record.' );
wp_set_object_terms( $test_team_id, 'events', 'nice_division', false );
nice_phase5_assert( has_term( 'events', 'nice_division', $test_team_id ), 'Team Member Division assignment failed.' );
wp_delete_post( $test_team_id, true );

$test_case_id = wp_insert_post(
	array(
		'post_type'   => 'nice_case_study',
		'post_status' => 'draft',
		'post_title'  => 'NICE Core field check',
	),
	true
);
nice_phase5_assert( ! is_wp_error( $test_case_id ), 'Could not create metadata validation record.' );
$_POST = array(
	'nice_content_meta_nonce' => wp_create_nonce( 'nice_save_content_meta' ),
	'nice_service_type'       => 'exhibitions-conferences',
	'nice_division'           => 'events',
	'nice_client_id'          => nice_get_client_by_slug( 'voltas-limited' )->ID,
	'nice_location'           => 'Validation location',
	'nice_year'               => '2026',
	'nice_reference_url'      => 'https://example.com/reference-test',
	'nice_featured'           => '1',
	'nice_display_order'      => '77',
);
nice_save_content_meta( $test_case_id, get_post( $test_case_id ) );
clean_post_cache( $test_case_id );
nice_phase5_assert( 2026 === (int) get_post_meta( $test_case_id, '_nice_year', true ), 'Case Study year did not survive save.' );
nice_phase5_assert( 77 === (int) get_post_meta( $test_case_id, '_nice_display_order', true ), 'Case Study display order did not survive save.' );
nice_phase5_assert( has_term( 'events', 'nice_division', $test_case_id ), 'Case Study Division did not survive save.' );
nice_phase5_assert( has_term( 'exhibitions-conferences', 'nice_service_type', $test_case_id ), 'Case Study Service Type did not survive save.' );
nice_phase5_assert( 'Voltas Limited' === nice_get_case_study_client_name( $test_case_id ), 'Case Study Client relationship did not survive save.' );
wp_delete_post( $test_case_id, true );
$_POST = array();

$title_check_id = wp_insert_post(
	array(
		'post_type'   => 'nice_client',
		'post_status' => 'publish',
		'post_title'  => '',
	),
	true
);
nice_phase5_assert( ! is_wp_error( $title_check_id ) && 'draft' === get_post_status( $title_check_id ), 'Titleless NICE content was published.' );
wp_delete_post( $title_check_id, true );
delete_transient( 'nice_title_required_' . get_current_user_id() );

WP_CLI::success( 'NICE Core runtime checks passed.' );
