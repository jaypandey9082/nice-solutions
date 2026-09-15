<?php
/**
 * What a freshly installed combined installation must look like.
 *
 * One WordPress serving everything: the landing page at the root, Events behind
 * /events/ and Studio behind /studio/. This is the shape nicesolutions.in runs,
 * so these assertions describe a production combined site rather than the
 * development one -- the difference is that the identity is declared out loud.
 *
 * Deliberately asserts that no sibling URL is configured. A combined site has
 * no siblings, and a warning about a missing one would be a warning about
 * nothing.
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

nice_fresh_assert( defined( 'NICE_SITE_DIVISION' ) && 'combined' === NICE_SITE_DIVISION, 'The installation declares itself as combined.' );
nice_fresh_assert( 'combined' === nice_get_site_identity(), 'NICE Core resolves the combined identity.' );
nice_fresh_assert( nice_is_combined_site(), 'It reports as a combined site.' );
nice_fresh_assert( ! nice_is_gateway_site(), 'It is not the gateway.' );
nice_fresh_assert( ! nice_is_division_site(), 'It is not a single-division site.' );
nice_fresh_assert( '' === nice_get_site_division(), 'It serves no one division in particular.' );

/*
 * The whole point of the explicit identity: setup has to be willing to run on a
 * production host. An undeclared identity is still refused, which is what this
 * value exists to replace.
 */
nice_fresh_assert( array() === nice_get_site_identity_problems(), 'Setup is not refused on a production host.' );
nice_fresh_assert( array() === nice_get_site_identity_warnings(), 'No sibling URL is wanted, so nothing is missing.' );

/* Both divisions are served from here, each behind its own prefix. */
foreach ( array( 'events', 'studio' ) as $division ) {
	nice_fresh_assert( nice_division_is_local( $division ), "The {$division} division is served here." );
	nice_fresh_assert( $division === nice_get_division_prefix( $division ), "Its content sits behind /{$division}/." );
}

nice_fresh_assert( array( 'events', 'studio' ) === nice_get_local_division_slugs(), 'Both divisions are owned locally.' );

/* Division landing pages, then every inner page behind its prefix. */
foreach ( array( 'events', 'studio' ) as $division ) {
	$home = get_page_by_path( $division, OBJECT, 'page' );
	nice_fresh_assert( $home instanceof WP_Post && 'publish' === $home->post_status, "The /{$division}/ page exists." );

	foreach ( array( 'services', 'case-studies', 'clients', 'about', 'contact' ) as $slug ) {
		$page = get_page_by_path( $division . '/' . $slug, OBJECT, 'page' );
		nice_fresh_assert( $page instanceof WP_Post && 'publish' === $page->post_status, "The /{$division}/{$slug}/ page exists." );
		nice_fresh_assert(
			$page instanceof WP_Post && $home instanceof WP_Post && (int) $page->post_parent === $home->ID,
			"It is a child of /{$division}/, so its path stays under the prefix."
		);
	}
}

/*
 * The landing page is the theme's front-page template, not a Page, so Settings
 * -> Reading is deliberately left alone. Asserting it stays untouched keeps a
 * future change from quietly repointing a live front page.
 */
nice_fresh_assert( 0 === (int) get_option( 'page_on_front' ), 'No Page was forced in front of the landing template.' );

/* Every URL resolves to a path on this host rather than another hostname. */
$host = wp_parse_url( home_url(), PHP_URL_HOST );
foreach ( array( 'events', 'studio' ) as $division ) {
	foreach ( array( '', 'services/', 'case-studies/', 'clients/', 'about/', 'contact/' ) as $path ) {
		$url = nice_get_division_url( $division, $path );
		nice_fresh_assert( wp_parse_url( $url, PHP_URL_HOST ) === $host, "{$division}/{$path} resolves on this host." );
		nice_fresh_assert( str_contains( (string) wp_parse_url( $url, PHP_URL_PATH ), "/{$division}/" ), "{$division}/{$path} keeps its prefix." );
	}
}

/* Both divisions' content is present, and each carries its own division term. */
$services = get_posts( array( 'post_type' => 'nice_service', 'post_status' => 'any', 'posts_per_page' => -1 ) );
$cases    = get_posts( array( 'post_type' => 'nice_case_study', 'post_status' => 'any', 'posts_per_page' => -1 ) );
$team     = get_posts( array( 'post_type' => 'nice_team_member', 'post_status' => 'any', 'posts_per_page' => -1 ) );

foreach ( array( 'events', 'studio' ) as $division ) {
	$division_services = array_filter( $services, static fn( $post ) => has_term( $division, 'nice_division', $post ) );
	$division_team     = array_filter( $team, static fn( $post ) => has_term( $division, 'nice_division', $post ) );

	nice_fresh_assert( count( $division_services ) >= 3, sprintf( 'Three %s services imported (%d).', $division, count( $division_services ) ) );
	nice_fresh_assert( 3 === count( $division_team ), sprintf( 'Three %s team placeholders imported (%d).', $division, count( $division_team ) ) );
}

nice_fresh_assert( 6 === count( $team ), sprintf( 'Six team placeholders in total, three per division (%d).', count( $team ) ) );
nice_fresh_assert( count( $cases ) >= 10, sprintf( 'Both divisions\' case studies imported (%d).', count( $cases ) ) );

/* Nothing unapproved was published by setup. */
$published_team = array_filter( $team, static fn( $post ) => 'publish' === $post->post_status );
nice_fresh_assert( array() === $published_team, 'No team placeholder was published.' );

/* The landing page owns the gateway previews on a combined site. */
nice_fresh_assert( nice_site_owns_gateway_projects(), 'Gateway previews are owned here.' );
nice_fresh_assert( post_type_exists( 'nice_gateway_project' ), 'The Gateway Project type is registered.' );

$previews = get_posts( array( 'post_type' => 'nice_gateway_project', 'post_status' => 'any', 'posts_per_page' => -1 ) );
nice_fresh_assert( 3 === count( $previews ), sprintf( 'Three previews seeded (%d).', count( $previews ) ) );

$published_previews = array_filter( $previews, static fn( $post ) => 'publish' === $post->post_status );
nice_fresh_assert( array() === $published_previews, 'No preview was published.' );

/*
 * A combined site's previews point at its own paths. On the split they carried
 * a sibling hostname, which is the one behaviour that has to differ here.
 */
foreach ( $previews as $preview ) {
	$destination = (string) get_post_meta( $preview->ID, '_nice_gateway_destination_url', true );

	if ( ! $destination ) {
		continue;
	}

	nice_fresh_assert(
		wp_parse_url( $destination, PHP_URL_HOST ) === $host,
		sprintf( '"%s" points at this host rather than a subdomain.', $preview->post_title )
	);
}

if ( $nice_failures ) {
	printf( "\n%d assertion(s) failed.\n", count( $nice_failures ) );
	exit( 1 );
}

echo "    All combined assertions passed.\n";
