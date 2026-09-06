<?php
/**
 * LocalWP runtime checks for Studio Home.
 *
 * Run with: wp eval-file scripts/wp-phase7-check.php
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
function nice_phase7_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

nice_phase7_assert( defined( 'NICE_CORE_VERSION' ) && '1.2.0' === NICE_CORE_VERSION, 'Unexpected NICE Core version.' );
nice_phase7_assert( '0.6.0' === wp_get_theme()->get( 'Version' ), 'Unexpected NICE theme version.' );

$studio = get_page_by_path( 'studio', OBJECT, 'page' );
nice_phase7_assert( $studio instanceof WP_Post && 'publish' === $studio->post_status, 'Studio Home Page is missing.' );

foreach ( array( 'services', 'case-studies', 'clients', 'team', 'contact' ) as $future_slug ) {
	nice_phase7_assert( null === get_page_by_path( 'studio/' . $future_slug, OBJECT, 'page' ), "Unexpected Phase 8 Page exists: {$future_slug}" );
}

$service_slugs = array( 'corporate-videos', 'digital-content-creation', 'films-entertainment' );
$service_names = array( 'Corporate Videos', 'Digital Content Creation', 'Films & Entertainment' );
$services      = nice_get_studio_services();

nice_phase7_assert( $service_slugs === wp_list_pluck( $services, 'post_name' ), 'Studio Services are missing or incorrectly ordered.' );
nice_phase7_assert( $service_names === wp_list_pluck( $services, 'post_title' ), 'Studio Service names do not match the sitemap.' );

foreach ( $services as $index => $service ) {
	nice_phase7_assert( has_term( 'studio', 'nice_division', $service ), "Studio division missing: {$service->post_name}" );
	nice_phase7_assert( has_term( $service_slugs[ $index ], 'nice_service_type', $service ), "Studio Service Type mismatch: {$service->post_name}" );
	nice_phase7_assert( has_post_thumbnail( $service ), "Studio Service image missing: {$service->post_name}" );
	nice_phase7_assert( str_contains( $service->post_content, 'What NICE delivers' ), "Studio Service content incomplete: {$service->post_name}" );
}

$expected_cases = array(
	'strata-geosystems-factory-shoot',
	'career-agents-academy',
	'krish-e',
	'crisil-financial-literacy-content',
	'jayanti',
);
$case_studies = nice_get_case_studies( array( 'division' => 'studio' ) );

nice_phase7_assert( $expected_cases === wp_list_pluck( $case_studies, 'post_name' ), 'Studio Case Studies are missing or incorrectly ordered.' );

foreach ( $case_studies as $case_study ) {
	$types = wp_get_object_terms( $case_study->ID, 'nice_service_type', array( 'fields' => 'slugs' ) );
	nice_phase7_assert( has_term( 'studio', 'nice_division', $case_study ), "Events content leaked into Studio: {$case_study->post_name}" );
	nice_phase7_assert( 1 === count( $types ) && in_array( $types[0], $service_slugs, true ), "Studio Case Study classification invalid: {$case_study->post_name}" );
	nice_phase7_assert( has_post_thumbnail( $case_study ), "Studio Case Study image missing: {$case_study->post_name}" );
	nice_phase7_assert( '' !== trim( $case_study->post_content ), "Studio Case Study narrative missing: {$case_study->post_name}" );
}

nice_phase7_assert( 'Bajaj' === nice_get_case_study_client_name( nice_get_case_study_by_slug( 'career-agents-academy' )->ID ), 'Career Agents Academy Client relationship is incorrect.' );
nice_phase7_assert( 'Mahindra' === nice_get_case_study_client_name( nice_get_case_study_by_slug( 'krish-e' )->ID ), 'Krish-e Client relationship is incorrect.' );
nice_phase7_assert( 'CRISIL' === nice_get_case_study_client_name( nice_get_case_study_by_slug( 'crisil-financial-literacy-content' )->ID ), 'CRISIL Client relationship is incorrect.' );

nice_phase7_assert( 10 === count( nice_get_clients() ), 'The shared Client dataset changed unexpectedly.' );
nice_phase7_assert( 3 === count( nice_get_events_services() ), 'Events Services changed unexpectedly.' );
nice_phase7_assert( 5 === count( nice_get_case_studies( array( 'division' => 'events' ) ) ), 'Events Case Studies changed unexpectedly.' );

$settings = nice_get_contact_settings();
nice_phase7_assert( empty( $settings['whatsapp_url'] ) && empty( $settings['email_address'] ) && empty( $settings['phone_url'] ), 'Studio contact is no longer publication-pending.' );
nice_phase7_assert( empty( nice_get_social_links() ), 'Studio social links are no longer publication-pending.' );

$registry = WP_Block_Type_Registry::get_instance();
nice_phase7_assert( $registry->is_registered( 'nice/studio-home' ), 'Studio Home block is not registered.' );

$rendered = do_blocks( '<!-- wp:nice/studio-home /-->' );
foreach ( array_merge( $service_names, wp_list_pluck( $case_studies, 'post_title' ) ) as $title ) {
	nice_phase7_assert( str_contains( $rendered, esc_html( $title ) ), "CMS content missing from rendered Studio Home: {$title}" );
}
nice_phase7_assert( ! preg_match( '#href=["\'][^"\']*/studio/(services|case-studies|clients|team|contact)/#', $rendered ), 'Studio Home exposes an unimplemented Phase 8 route.' );
nice_phase7_assert( ! str_contains( $rendered, '<form' ), 'Studio Home must not render a form.' );
nice_phase7_assert( str_contains( $rendered, 'data-nice-studio-contact-pending' ), 'Studio contact pending state is missing.' );
nice_phase7_assert( ! str_contains( $rendered, 'nice-studio-social' ), 'Empty social settings must omit the social section.' );

$rerun = nice_run_content_migration();
nice_phase7_assert( ! is_wp_error( $rerun ), 'Migration rerun failed.' );
nice_phase7_assert( 0 === $rerun['services']['created'] && 0 === $rerun['case_studies']['created'], 'Migration rerun created duplicate Studio content.' );
nice_phase7_assert( 0 === $rerun['studio_page']['created'] && 1 === $rerun['studio_page']['skipped'], 'Migration rerun duplicated Studio Home.' );
nice_phase7_assert( 0 === $rerun['media']['linked'] && 0 === $rerun['enriched'], 'Migration rerun changed existing editorial content or media.' );

WP_CLI::success( 'NICE Phase 7 runtime checks passed.' );
