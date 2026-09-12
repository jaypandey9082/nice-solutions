<?php
/**
 * What a freshly installed gateway must look like.
 *
 * The gateway is the shape most likely to go wrong quietly: it looks like an
 * ordinary installation, but it must own no division content at all, and every
 * link it publishes has to leave for another hostname.
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
function nice_gateway_assert( $condition, $message ) {
	global $nice_failures;

	if ( $condition ) {
		echo "    ok   {$message}\n";
		return;
	}

	$nice_failures[] = $message;
	echo "    FAIL {$message}\n";
}

nice_gateway_assert( nice_is_gateway_site(), 'The installation identifies as the gateway.' );
nice_gateway_assert( '' === nice_get_site_division(), 'It owns no division.' );
nice_gateway_assert( array() === nice_get_local_division_slugs(), 'It owns neither division\'s content.' );
nice_gateway_assert( array() === nice_get_site_identity_problems(), 'The identity configuration is complete.' );
nice_gateway_assert( array() === nice_get_site_identity_warnings(), 'Nothing is left unconfigured.' );

/* No division content, in any status. */
foreach ( array( 'nice_service', 'nice_case_study', 'nice_team_member' ) as $post_type ) {
	$found = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	nice_gateway_assert( array() === $found, sprintf( 'No %s was imported.', str_replace( 'nice_', '', $post_type ) ) );
}

nice_gateway_assert( is_wp_error( nice_run_content_migration() ), 'The content migration refuses to run here.' );

/* No division pages were provisioned. */
foreach ( array( 'events', 'studio', 'services', 'case-studies', 'clients', 'team', 'contact' ) as $slug ) {
	nice_gateway_assert( null === get_page_by_path( $slug, OBJECT, 'page' ), sprintf( 'No /%s/ page was created.', $slug ) );
}

/* The previews it does own. */
nice_gateway_assert( nice_site_owns_gateway_projects(), 'Gateway previews are owned here.' );
nice_gateway_assert( post_type_exists( 'nice_gateway_project' ), 'The Gateway Project type is registered.' );

$previews = get_posts( array( 'post_type' => 'nice_gateway_project', 'post_status' => 'any', 'posts_per_page' => -1 ) );
nice_gateway_assert( 3 === count( $previews ), sprintf( 'Three previews seeded (%d).', count( $previews ) ) );
nice_gateway_assert(
	array() === array_filter( $previews, static fn( $post ) => 'publish' === $post->post_status ),
	'No preview was published.'
);
nice_gateway_assert( array() === nice_get_gateway_projects(), 'Nothing renders on the front page yet.' );

foreach ( $previews as $preview ) {
	$view = nice_get_gateway_project_view( $preview );
	$host = (string) wp_parse_url( $view['url'], PHP_URL_HOST );

	nice_gateway_assert(
		in_array( $host, array( 'events.nicesolutions.in', 'studios.nicesolutions.in' ), true ),
		sprintf( '"%s" points at a division installation (%s).', $view['title'], $host ?: 'no destination' )
	);
	nice_gateway_assert(
		0 === (int) get_post_thumbnail_id( $preview ),
		sprintf( '"%s" carries no photograph from the package.', $view['title'] )
	);
}

/* Every division link leaves this host. */
nice_gateway_assert(
	'https://events.nicesolutions.in/case-studies/gca-2025/' === nice_get_division_url( 'events', 'case-studies/gca-2025' ),
	'Events links leave for the Events installation.'
);
nice_gateway_assert(
	'https://studios.nicesolutions.in/services/' === nice_get_division_url( 'studio', 'services/' ),
	'Studio links leave for the Studio installation.'
);

$rules = get_option( 'rewrite_rules' );
$division_rules = array_filter(
	array_keys( is_array( $rules ) ? $rules : array() ),
	static fn( $rule ) => str_contains( $rule, 'case-studies' ) || str_contains( $rule, 'nice_service' )
);
nice_gateway_assert( array() === $division_rules, 'No division route was registered.' );

if ( $nice_failures ) {
	throw new RuntimeException( sprintf( '%d assertion(s) failed on the gateway installation.', count( $nice_failures ) ) );
}

echo "    All gateway assertions passed.\n";
