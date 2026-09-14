<?php
/**
 * What a freshly installed Events installation must look like.
 *
 * Run by scripts/verify-release-install.sh against a throwaway WordPress, so
 * these assertions describe a real production shape rather than the combined
 * development site: content at the root, one division only, and no trace of the
 * sibling.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

global $nice_failures;

$nice_failures = array();

/**
 * Record one assertion.
 *
 * @param bool   $condition Expected condition.
 * @param string $message   What the condition means.
 */
function nice_fresh_assert( $condition, $message ) {
	global $nice_failures;

	if ( $condition ) {
		echo "    ok   {$message}\n";
		return;
	}

	$nice_failures[] = $message;
	echo "    FAIL {$message}\n";
}

nice_fresh_assert( defined( 'NICE_SITE_DIVISION' ) && 'events' === NICE_SITE_DIVISION, 'The installation declares itself as Events.' );
nice_fresh_assert( 'events' === nice_get_site_division(), 'NICE Core resolves the Events identity.' );
nice_fresh_assert( array() === nice_get_site_identity_problems(), 'The identity configuration is complete.' );
nice_fresh_assert( array() === nice_get_site_identity_warnings(), 'Every sibling URL is configured.' );

/* Section pages sit at the root, not under /events/. */
foreach ( array( 'services', 'case-studies', 'clients', 'about', 'contact' ) as $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	nice_fresh_assert( $page instanceof WP_Post && 'publish' === $page->post_status, "The /{$slug}/ page exists at the root." );
}

$home = get_page_by_path( 'events', OBJECT, 'page' );
nice_fresh_assert( $home instanceof WP_Post, 'The division home page exists.' );
nice_fresh_assert( $home instanceof WP_Post && (int) get_option( 'page_on_front' ) === $home->ID, 'It is the front page.' );
nice_fresh_assert( 'page' === get_option( 'show_on_front' ), 'The site shows a page rather than the posts index.' );

/* Only this division's content was imported. */
$services = get_posts( array( 'post_type' => 'nice_service', 'post_status' => 'any', 'posts_per_page' => -1 ) );
$cases    = get_posts( array( 'post_type' => 'nice_case_study', 'post_status' => 'any', 'posts_per_page' => -1 ) );
$team     = get_posts( array( 'post_type' => 'nice_team_member', 'post_status' => 'any', 'posts_per_page' => -1 ) );

$foreign_services = array_filter( $services, static fn( $post ) => has_term( 'studio', 'nice_division', $post ) );
$foreign_cases    = array_filter( $cases, static fn( $post ) => has_term( 'studio', 'nice_division', $post ) );
$foreign_team     = array_filter( $team, static fn( $post ) => has_term( 'studio', 'nice_division', $post ) );

nice_fresh_assert( 3 === count( $services ), sprintf( 'Three services imported, not six (%d).', count( $services ) ) );
nice_fresh_assert( array() === $foreign_services, 'No Studio service was imported.' );
nice_fresh_assert( array() === $foreign_cases, 'No Studio case study was imported.' );
nice_fresh_assert( array() === $foreign_team, 'No Studio team placeholder was imported.' );
nice_fresh_assert( count( $cases ) > 0, 'Events case studies were imported.' );
nice_fresh_assert( 3 === count( $team ), sprintf( 'Three team placeholders imported, not six (%d).', count( $team ) ) );

/* Nothing that arrives as a draft may be published by setup. */
$published_team = array_filter( $team, static fn( $post ) => 'publish' === $post->post_status );
nice_fresh_assert( array() === $published_team, 'No team placeholder was published.' );

$linkedin = array_filter(
	$cases,
	static fn( $post ) => 'linkedin' === get_post_meta( $post->ID, '_nice_source_origin', true )
);
nice_fresh_assert( 5 === count( $linkedin ), sprintf( 'Five LinkedIn candidates seeded (%d).', count( $linkedin ) ) );
nice_fresh_assert(
	array() === array_filter( $linkedin, static fn( $post ) => 'publish' === $post->post_status ),
	'No LinkedIn candidate was published.'
);
nice_fresh_assert(
	array() === array_filter( $linkedin, static fn( $post ) => nice_case_study_source_is_approvable( $post->ID ) ),
	'No LinkedIn candidate can be approved as seeded.'
);

/* The gateway's content type belongs to the gateway. */
nice_fresh_assert( ! nice_site_owns_gateway_projects(), 'Gateway previews are not owned here.' );
nice_fresh_assert( ! post_type_exists( 'nice_gateway_project' ), 'The Gateway Project type is not registered here.' );

/* URLs resolve to the right hostname in both directions. */
nice_fresh_assert(
	home_url( '/case-studies/gca-2025/' ) === nice_get_division_url( 'events', 'case-studies/gca-2025' ),
	'Its own content resolves to its own host with no prefix.'
);
nice_fresh_assert(
	'https://studios.nicesolutions.in/case-studies/krish-e/' === nice_get_division_url( 'studio', 'case-studies/krish-e' ),
	'Studio content resolves to the Studio installation.'
);
nice_fresh_assert( 'https://nicesolutions.in/' === nice_get_main_site_url(), 'The gateway link leaves for the gateway.' );

/* Packages ship no unapproved photography, so records carry no featured image. */
$with_thumbnails = array_filter( $cases, static fn( $post ) => (int) get_post_thumbnail_id( $post ) > 0 );
nice_fresh_assert( array() === $with_thumbnails, 'No deck photograph was imported from the package.' );

if ( $nice_failures ) {
	throw new RuntimeException( sprintf( '%d assertion(s) failed on the fresh installation.', count( $nice_failures ) ) );
}

echo "    All fresh-installation assertions passed.\n";
