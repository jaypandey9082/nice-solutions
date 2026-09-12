<?php
/**
 * Runtime checks for installation identity, content ownership and approval rules.
 *
 * The three production installations run the same code and differ only by the
 * identity declared in wp-config.php, so the identity is the thing most worth
 * testing: a mistake here publishes one division's work from another division's
 * hostname, and no page-level check would notice.
 *
 * Every assertion is read-only. Identities are simulated through the
 * nice_site_division filter rather than by editing wp-config.php, and the
 * sibling URLs through nice_division_site_urls, so this is safe to run against
 * the combined development site.
 *
 * Run with: wp eval-file scripts/wp-identity-check.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/*
 * Deliberately globals: wp eval-file includes this file inside a function, so a
 * plain top-level variable would not be the one nice_identity_assert() writes to.
 */
global $nice_failures, $nice_checks;

$nice_failures = array();
$nice_checks   = 0;

/**
 * Record one assertion.
 *
 * @param bool   $condition Expected condition.
 * @param string $message   What the condition means.
 */
function nice_identity_assert( $condition, $message ) {
	global $nice_failures, $nice_checks;

	++$nice_checks;

	if ( ! $condition ) {
		$nice_failures[] = $message;
	}
}

/**
 * Run a callback as though this installation declared a given identity.
 *
 * @param string   $identity Identity slug, or an empty string for the combined site.
 * @param callable $callback Assertions to run.
 */
function nice_identity_as( $identity, callable $callback ) {
	$identity_filter = static function () use ( $identity ) {
		return $identity;
	};
	$url_filter      = static function () {
		return array(
			'main'   => 'https://nicesolutions.in',
			'events' => 'https://events.nicesolutions.in',
			'studio' => 'https://studios.nicesolutions.in',
		);
	};

	add_filter( 'nice_site_division', $identity_filter, 99 );
	add_filter( 'nice_division_site_urls', $url_filter, 99 );

	try {
		$callback();
	} finally {
		remove_filter( 'nice_site_division', $identity_filter, 99 );
		remove_filter( 'nice_division_site_urls', $url_filter, 99 );
	}
}

/**
 * Count manifest records by the division that owns them.
 *
 * @param array<int, array<string, mixed>> $records Manifest records.
 * @return array<string, int>
 */
function nice_identity_count_by_division( $records ) {
	$counts = array( 'events' => 0, 'studio' => 0, '' => 0 );

	foreach ( $records as $record ) {
		$division = nice_get_manifest_record_division( $record );
		$counts[ $division ] = ( $counts[ $division ] ?? 0 ) + 1;
	}

	return $counts;
}

$nice_manifest = nice_get_content_migration_manifest();
$nice_services = nice_identity_count_by_division( $nice_manifest['services'] );
$nice_cases    = nice_identity_count_by_division( $nice_manifest['case_studies'] );

nice_identity_assert( 0 === $nice_services[''], 'Every manifest service resolves to a division.' );
nice_identity_assert( 0 === $nice_cases[''], 'Every manifest case study resolves to a division.' );
nice_identity_assert( $nice_services['events'] > 0 && $nice_services['studio'] > 0, 'Both divisions own services.' );

/* ── The combined development site ───────────────────────────────────────── */

nice_identity_as(
	'',
	static function () {
		nice_identity_assert( '' === nice_get_site_division(), 'Combined: no single division.' );
		nice_identity_assert( ! nice_is_gateway_site(), 'Combined: not the gateway.' );
		nice_identity_assert( nice_division_is_local( 'events' ) && nice_division_is_local( 'studio' ), 'Combined: serves both divisions.' );
		nice_identity_assert( 'events' === nice_get_division_prefix( 'events' ), 'Combined: Events keeps its path prefix.' );
		nice_identity_assert(
			home_url( '/events/case-studies/x/' ) === nice_get_division_url( 'events', 'case-studies/x' ),
			'Combined: division URLs stay on this host.'
		);
		nice_identity_assert( nice_site_owns_gateway_projects(), 'Combined: gateway previews are editable here.' );
		nice_identity_assert( array( 'events', 'studio' ) === nice_get_local_division_slugs(), 'Combined: owns both divisions.' );
	}
);

/* ── The main gateway ────────────────────────────────────────────────────── */

nice_identity_as(
	'main',
	static function () {
		nice_identity_assert( nice_is_gateway_site(), 'Main: identifies as the gateway.' );
		nice_identity_assert( '' === nice_get_site_division(), 'Main: owns no division.' );
		nice_identity_assert( ! nice_division_is_local( 'events' ) && ! nice_division_is_local( 'studio' ), 'Main: serves neither division.' );
		nice_identity_assert( array() === nice_get_local_division_slugs(), 'Main: owns no division content.' );
		nice_identity_assert(
			'https://events.nicesolutions.in/case-studies/x/' === nice_get_division_url( 'events', 'case-studies/x' ),
			'Main: Events links resolve to the Events installation.'
		);
		nice_identity_assert(
			'https://studios.nicesolutions.in/case-studies/x/' === nice_get_division_url( 'studio', 'case-studies/x' ),
			'Main: Studio links resolve to the Studio installation.'
		);
		nice_identity_assert( is_wp_error( nice_run_content_migration() ), 'Main: the content migration refuses to run.' );
		nice_identity_assert( array( 'created' => 0, 'skipped' => 0, 'errors' => array(), 'home_page_id' => 0 ) === nice_provision_events_pages(), 'Main: provisions no Events pages.' );
		nice_identity_assert( 'foreign' === nice_initialize_events_hero_media()['status'], 'Main: imports no Events hero.' );
		nice_identity_assert( 'foreign' === nice_initialize_studio_hero_media()['status'], 'Main: imports no Studio hero.' );
		nice_identity_assert( nice_site_owns_gateway_projects(), 'Main: owns the gateway previews.' );

		/* Destination URLs are restricted to a division's own case studies. */
		nice_identity_assert( '' === nice_get_gateway_destination_problem( 'https://events.nicesolutions.in/case-studies/gca-2025/', 'events' ), 'Main: an Events case study is an allowed destination.' );
		nice_identity_assert( '' !== nice_get_gateway_destination_problem( 'https://events.nicesolutions.in/case-studies/', 'events' ), 'Main: the case-study index is not a destination.' );
		nice_identity_assert( '' !== nice_get_gateway_destination_problem( 'https://events.nicesolutions.in/services/staging/', 'events' ), 'Main: another section is not a destination.' );
		nice_identity_assert( '' !== nice_get_gateway_destination_problem( 'https://studios.nicesolutions.in/case-studies/x/', 'events' ), 'Main: the sibling host is not an Events destination.' );
		nice_identity_assert( '' !== nice_get_gateway_destination_problem( 'https://example.com/case-studies/x/', 'events' ), 'Main: an unrelated host is never a destination.' );
		nice_identity_assert( '' !== nice_get_gateway_destination_problem( 'https://events.nicesolutions.in/case-studies/x/', '' ), 'Main: a destination without a division is rejected.' );
		nice_identity_assert( '' === nice_sanitize_gateway_destination_url( 'https://example.com/case-studies/x/' ), 'Main: the meta sanitizer drops an unrelated destination.' );
		nice_identity_assert( 'https://studios.nicesolutions.in/case-studies/x/' === nice_sanitize_gateway_destination_url( 'https://studios.nicesolutions.in/case-studies/x/' ), 'Main: the meta sanitizer keeps a valid destination.' );
	}
);

/* ── A division installation ─────────────────────────────────────────────── */

nice_identity_as(
	'events',
	static function () {
		nice_identity_assert( 'events' === nice_get_site_division(), 'Events: serves Events.' );
		nice_identity_assert( ! nice_is_gateway_site(), 'Events: not the gateway.' );
		nice_identity_assert( nice_division_is_local( 'events' ), 'Events: owns its own content.' );
		nice_identity_assert( ! nice_division_is_local( 'studio' ), 'Events: does not own Studio content.' );
		nice_identity_assert( '' === nice_get_division_prefix( 'events' ), 'Events: its own content sits at the root.' );
		nice_identity_assert( '/' === nice_get_division_path_prefix( 'events' ), 'Events: every path belongs to Events.' );
		nice_identity_assert( home_url( '/case-studies/x/' ) === nice_get_division_url( 'events', 'case-studies/x' ), 'Events: own URLs carry no prefix.' );
		nice_identity_assert(
			'https://studios.nicesolutions.in/case-studies/x/' === nice_get_division_url( 'studio', 'case-studies/x' ),
			'Events: Studio links leave for the Studio installation.'
		);
		nice_identity_assert( 'https://nicesolutions.in/' === nice_get_main_site_url(), 'Events: the gateway link leaves for the gateway.' );
		nice_identity_assert( ! nice_site_owns_gateway_projects(), 'Events: owns no gateway previews.' );
		nice_identity_assert( 'foreign' === nice_initialize_studio_hero_media()['status'], 'Events: imports no Studio hero.' );
		nice_identity_assert( array( 'events' ) === nice_get_local_division_slugs(), 'Events: owns exactly one division.' );

		/*
		 * A sibling record that reached this database another way must not show
		 * up in a public listing. Queried directly rather than through the theme
		 * helpers, because those already ask for one division by name.
		 */
		$visible = get_posts(
			array(
				'post_type'      => 'nice_case_study',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$studio_visible = array_filter(
			$visible,
			static function ( $post_id ) {
				return has_term( 'studio', 'nice_division', $post_id );
			}
		);
		nice_identity_assert( array() === $studio_visible, 'Events: no Studio record appears in a public listing.' );
		nice_identity_assert( count( $visible ) > 0, 'Events: its own records still appear.' );

		$team = array_filter(
			nice_get_team_member_draft_manifest(),
			static function ( $record ) {
				return nice_division_is_local( $record['division'] );
			}
		);
		nice_identity_assert( 3 === count( $team ), 'Events: seeds only its own team placeholders.' );
	}
);

nice_identity_as(
	'studio',
	static function () {
		nice_identity_assert( 'studio' === nice_get_site_division(), 'Studio: serves Studio.' );
		nice_identity_assert( ! nice_division_is_local( 'events' ), 'Studio: does not own Events content.' );
		nice_identity_assert( '' === nice_get_division_prefix( 'studio' ), 'Studio: its own content sits at the root.' );
		nice_identity_assert(
			'https://events.nicesolutions.in/case-studies/x/' === nice_get_division_url( 'events', 'case-studies/x' ),
			'Studio: Events links leave for the Events installation.'
		);
		nice_identity_assert( 'foreign' === nice_initialize_events_hero_media()['status'], 'Studio: imports no Events hero.' );
		$drafts = nice_migrate_linkedin_case_study_drafts();
		nice_identity_assert( 0 === $drafts['created'] && 0 === $drafts['skipped'], 'Studio: seeds no Events LinkedIn candidates.' );
	}
);

/* ── Source provenance ───────────────────────────────────────────────────── */

$nice_source_cases = array(
	array( 'https://www.linkedin.com/posts/n-i-c-e-solutions_yarn-expo-activity-7280000000000000000-abcd', '', true, 'an individual LinkedIn post' ),
	array( 'https://www.linkedin.com/feed/update/urn:li:activity:7280000000000000000/', '', true, 'an activity permalink' ),
	array( 'https://in.linkedin.com/pulse/some-article-slug', '', true, 'a LinkedIn article' ),
	array( nice_get_linkedin_company_feed_url(), 'linkedin', false, 'the company feed' ),
	array( 'https://www.linkedin.com/company/n-i-c-e-solutions/', 'linkedin', false, 'a company page' ),
	array( 'https://www.linkedin.com/company/n-i-c-e-solutions/posts/', 'linkedin', false, 'a company posts tab' ),
	array( 'https://www.linkedin.com/feed/', 'linkedin', false, 'the LinkedIn feed' ),
	array( 'https://www.linkedin.com/in/someone/', 'linkedin', false, 'a personal profile' ),
	array( 'http://www.linkedin.com/posts/n-i-c-e-solutions_activity-1234-abcd', '', false, 'a post over plain http' ),
	array( '', '', false, 'an empty source' ),
	array( 'not a url', '', false, 'a malformed source' ),
	array( 'https://example.com', '', false, 'a bare domain' ),
	array( 'https://example.com/press/nice-wins-award', '', true, 'a specific page elsewhere' ),
	array( 'https://example.com/press/nice-wins-award', 'linkedin', false, 'an unrelated site on a LinkedIn-derived record' ),
);

foreach ( $nice_source_cases as $nice_case ) {
	list( $nice_url, $nice_origin, $nice_expected, $nice_label ) = $nice_case;

	nice_identity_assert(
		$nice_expected === nice_source_url_is_specific( $nice_url, $nice_origin ),
		sprintf( 'Source approval %s %s.', $nice_expected ? 'accepts' : 'rejects', $nice_label )
	);
}

/* Every seeded candidate is held until an editor pastes the post it came from. */
foreach ( nice_get_linkedin_case_study_draft_manifest() as $nice_record ) {
	nice_identity_assert(
		! nice_source_url_is_specific( $nice_record['source_url'], 'linkedin' ),
		sprintf( 'Seeded candidate "%s" cannot be approved as seeded.', $nice_record['title'] )
	);
}

/* ── Result ──────────────────────────────────────────────────────────────── */

if ( $nice_failures ) {
	foreach ( $nice_failures as $nice_failure ) {
		echo "FAILED: {$nice_failure}\n";
	}

	throw new RuntimeException( sprintf( '%d of %d identity assertions failed.', count( $nice_failures ), $nice_checks ) );
}

echo "Identity, ownership and provenance checks passed ({$nice_checks} assertions).\n";
