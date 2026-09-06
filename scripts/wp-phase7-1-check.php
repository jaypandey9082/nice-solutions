<?php
/**
 * LocalWP runtime checks for Phase 7.1 Editorial, Motion & Journey Refinements.
 *
 * Run with: wp eval-file scripts/wp-phase7-1-check.php
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
function nice_phase7_1_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

// 1. Version checks.
nice_phase7_1_assert( defined( 'NICE_CORE_VERSION' ) && '1.2.0' === NICE_CORE_VERSION, 'Unexpected NICE Core version.' );
nice_phase7_1_assert( '0.6.0' === wp_get_theme()->get( 'Version' ), 'Unexpected NICE theme version.' );

// 2. Meta registration check.
$registered_meta = get_registered_meta_keys( 'post', 'nice_case_study' );
nice_phase7_1_assert( isset( $registered_meta['_nice_hero_video_url'] ), 'Missing _nice_hero_video_url meta field registration.' );
nice_phase7_1_assert( isset( $registered_meta['_nice_quote_text'] ), 'Missing _nice_quote_text meta field registration.' );
nice_phase7_1_assert( isset( $registered_meta['_nice_quote_author'] ), 'Missing _nice_quote_author meta field registration.' );

// 3. Routing functions check.
nice_phase7_1_assert( function_exists( 'nice_get_content_division' ), 'nice_get_content_division() must exist.' );
nice_phase7_1_assert( function_exists( 'nice_get_content_url' ), 'nice_get_content_url() must exist.' );
nice_phase7_1_assert( function_exists( 'nice_is_events_content' ), 'nice_is_events_content() must exist.' );
nice_phase7_1_assert( function_exists( 'nice_is_studio_content' ), 'nice_is_studio_content() must exist.' );

// 4. Content division and URL tests.
$events_case = get_page_by_path( 'voltas-fam-tastic-fiesta', OBJECT, 'nice_case_study' );
if ( $events_case ) {
	nice_phase7_1_assert( 'events' === nice_get_content_division( $events_case ), 'Voltas must belong to Events division.' );
	nice_phase7_1_assert( nice_is_events_content( $events_case ), 'Voltas must pass nice_is_events_content().' );
	nice_phase7_1_assert( ! nice_is_studio_content( $events_case ), 'Voltas must not pass nice_is_studio_content().' );
	$events_url = nice_get_content_url( $events_case );
	nice_phase7_1_assert( str_contains( $events_url, '/events/case-studies/voltas-fam-tastic-fiesta/' ), "Events URL mismatch: {$events_url}" );
}

$studio_case = get_page_by_path( 'krish-e', OBJECT, 'nice_case_study' );
if ( $studio_case ) {
	nice_phase7_1_assert( 'studio' === nice_get_content_division( $studio_case ), 'Krish-e must belong to Studio division.' );
	nice_phase7_1_assert( nice_is_studio_content( $studio_case ), 'Krish-e must pass nice_is_studio_content().' );
	nice_phase7_1_assert( ! nice_is_events_content( $studio_case ), 'Krish-e must not pass nice_is_events_content().' );
	$studio_url = nice_get_content_url( $studio_case );
	nice_phase7_1_assert( str_contains( $studio_url, '/studio/case-studies/krish-e/' ), "Studio URL mismatch: {$studio_url}" );
}

// 5. Future Studio routes must not exist as published pages.
foreach ( array( 'services', 'case-studies', 'clients', 'team', 'contact' ) as $future_slug ) {
	nice_phase7_1_assert( null === get_page_by_path( 'studio/' . $future_slug, OBJECT, 'page' ), "Unexpected Phase 8 Page exists: studio/{$future_slug}" );
}

echo "Phase 7.1 runtime assertions passed successfully.\n";
