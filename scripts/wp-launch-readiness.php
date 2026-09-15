<?php
/**
 * Report what this installation still needs before it can launch.
 *
 * The acceptance criteria for the three-site launch are mostly content
 * decisions, not code: approved imagery, verified contact details, real
 * profiles, cited sources. Those live in the database, so the only honest way
 * to say whether they are done is to look.
 *
 * Read-only. Run on any installation, at any point:
 *
 *     wp eval-file scripts/wp-launch-readiness.php
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

global $nice_rows, $nice_pending;

$nice_rows    = array();
$nice_pending = 0;

/**
 * Record one readiness row.
 *
 * @param string $area   What is being checked.
 * @param bool   $ready  Whether it is done.
 * @param string $detail What was found, or what is still needed.
 */
function nice_readiness_row( $area, $ready, $detail ) {
	global $nice_rows, $nice_pending;

	$nice_rows[] = array( $ready ? 'READY  ' : 'PENDING', $area, $detail );

	if ( ! $ready ) {
		++$nice_pending;
	}
}

/**
 * Count published posts of a type in a division.
 *
 * @param string $post_type Post type.
 * @param string $division  Division slug, or an empty string for any.
 * @return int
 */
function nice_readiness_count( $post_type, $division = '' ) {
	$args = array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);

	if ( $division ) {
		$args['tax_query'] = array(
			array( 'taxonomy' => 'nice_division', 'field' => 'slug', 'terms' => $division ),
		);
	}

	return count( get_posts( $args ) );
}

/* ── Configuration ───────────────────────────────────────────────────────── */

$nice_identity = nice_get_site_identity();
$nice_problems = nice_get_site_identity_problems();

nice_readiness_row(
	'Installation identity',
	! $nice_problems,
	$nice_problems ? implode( ' ', $nice_problems ) : sprintf( '%s (%s)', nice_get_site_identity_label(), $nice_identity ?: 'combined' )
);

$nice_warnings = nice_get_site_identity_warnings();
nice_readiness_row(
	nice_is_combined_site() ? 'Configuration' : 'Sibling site URLs',
	! $nice_warnings,
	$nice_warnings
		? implode( ' ', $nice_warnings )
		: ( nice_is_combined_site() ? 'Nothing outstanding. A combined site has no siblings to point at.' : 'All configured.' )
);

if ( nice_is_division_site() ) {
	$nice_front = (int) get_option( 'page_on_front' );
	$nice_home  = get_page_by_path( nice_get_site_division(), OBJECT, 'page' );
	nice_readiness_row(
		'Front page',
		$nice_home instanceof WP_Post && $nice_front === $nice_home->ID && 'page' === get_option( 'show_on_front' ),
		$nice_home instanceof WP_Post
			? sprintf( 'Settings -> Reading should point at "%s" (ID %d); it points at %s.', $nice_home->post_title, $nice_home->ID, $nice_front ?: 'the posts index' )
			: 'The division home page has not been provisioned. Run setup.'
	);
}

/* Before launch the pre-launch state is the passing one, so an indexed site is pending. */
nice_readiness_row(
	'Search engine visibility',
	! get_option( 'blog_public' ),
	get_option( 'blog_public' )
		? 'Indexing is ON. Turn it off until the launch review, then back on and submit the sitemap.'
		: 'Indexing is off, as it should be before launch.'
);

/* ── Contact and social ──────────────────────────────────────────────────── */

foreach ( nice_get_contact_division_labels() as $nice_slug => $nice_label ) {
	if ( ! nice_division_is_local( $nice_slug ) ) {
		continue;
	}

	$nice_channels = nice_get_contact_channels( $nice_slug );
	$nice_missing  = array();

	foreach ( array( 'phone_url' => 'phone', 'whatsapp_url' => 'WhatsApp', 'email_address' => 'email' ) as $nice_key => $nice_name ) {
		if ( empty( $nice_channels[ $nice_key ] ) ) {
			$nice_missing[] = $nice_name;
		}
	}

	nice_readiness_row(
		sprintf( '%s contact details', $nice_label ),
		! $nice_missing,
		$nice_missing ? 'Missing: ' . implode( ', ', $nice_missing ) : $nice_channels['email_address']
	);
}

$nice_social = nice_get_social_profiles();
nice_readiness_row(
	'Social profiles',
	count( $nice_social ) > 0,
	$nice_social ? implode( ', ', wp_list_pluck( $nice_social, 'label' ) ) : 'None published under Settings -> NICE Contact.'
);

/* ── Division content ────────────────────────────────────────────────────── */

foreach ( nice_get_local_division_slugs() as $nice_division ) {
	$nice_label = ucfirst( $nice_division );

	nice_readiness_row(
		sprintf( '%s services', $nice_label ),
		nice_readiness_count( 'nice_service', $nice_division ) >= 3,
		sprintf( '%d published.', nice_readiness_count( 'nice_service', $nice_division ) )
	);

	$nice_cases = get_posts(
		array(
			'post_type'      => 'nice_case_study',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => array( array( 'taxonomy' => 'nice_division', 'field' => 'slug', 'terms' => $nice_division ) ),
		)
	);

	$nice_without_media = array();
	foreach ( $nice_cases as $nice_case ) {
		$nice_thumb = (int) get_post_thumbnail_id( $nice_case );
		$nice_alt   = $nice_thumb ? trim( (string) get_post_meta( $nice_thumb, '_wp_attachment_image_alt', true ) ) : '';

		if ( ! $nice_thumb || ! $nice_alt || ! get_post_meta( $nice_case->ID, '_nice_media_approved', true ) ) {
			$nice_without_media[] = $nice_case->post_title;
		}
	}

	nice_readiness_row(
		sprintf( '%s project imagery', $nice_label ),
		! $nice_without_media,
		$nice_without_media
			? sprintf( '%d of %d still need an approved image with alt text: %s', count( $nice_without_media ), count( $nice_cases ), implode( '; ', array_slice( $nice_without_media, 0, 4 ) ) . ( count( $nice_without_media ) > 4 ? ' ...' : '' ) )
			: sprintf( 'All %d cleared.', count( $nice_cases ) )
	);

	$nice_team = nice_readiness_count( 'nice_team_member', $nice_division );
	nice_readiness_row(
		sprintf( '%s team', $nice_label ),
		true,
		$nice_team
			? sprintf( '%d published on the About page.', $nice_team )
			: 'No published profiles, so the About page shows three sample cards instead. That is the intended state until real people are approved.'
	);

	$nice_hero_page = get_page_by_path( $nice_division, OBJECT, 'page' );
	$nice_hero_id   = $nice_hero_page instanceof WP_Post ? (int) get_post_meta( $nice_hero_page->ID, sprintf( '_nice_%s_hero_image_id', $nice_division ), true ) : 0;
	nice_readiness_row(
		sprintf( '%s hero image', $nice_label ),
		$nice_hero_id > 0,
		$nice_hero_id ? wp_get_attachment_image_url( $nice_hero_id, 'full' ) : 'No hero image selected on the division home page.'
	);
}

/* ── Source provenance ───────────────────────────────────────────────────── */

/*
 * Split by origin. The five LinkedIn candidates need the post each was written
 * from; the records migrated from the NICE profile deck carry no source URL at
 * all, which is a different job with a different answer.
 */
$nice_linkedin_pending = array();
$nice_other_pending    = array();

foreach (
	get_posts(
		array(
			'post_type'      => 'nice_case_study',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		)
	) as $nice_case
) {
	if ( 'approved' === get_post_meta( $nice_case->ID, '_nice_source_approval_status', true ) ) {
		continue;
	}

	if ( nice_case_study_source_is_approvable( $nice_case->ID ) ) {
		continue;
	}

	if ( 'linkedin' === get_post_meta( $nice_case->ID, '_nice_source_origin', true ) ) {
		$nice_linkedin_pending[] = $nice_case->post_title;
	} else {
		$nice_other_pending[] = $nice_case->post_title;
	}
}

nice_readiness_row(
	'LinkedIn candidate sources',
	! $nice_linkedin_pending,
	$nice_linkedin_pending
		? sprintf( '%d draft(s) need the individual LinkedIn post they were written from: %s', count( $nice_linkedin_pending ), implode( '; ', $nice_linkedin_pending ) )
		: 'Every LinkedIn candidate cites its post.'
);

nice_readiness_row(
	'Other record sources',
	! $nice_other_pending,
	$nice_other_pending
		? sprintf( '%d record(s) carry no source URL, so they cannot be moved to approved: %s', count( $nice_other_pending ), implode( '; ', array_slice( $nice_other_pending, 0, 5 ) ) . ( count( $nice_other_pending ) > 5 ? ' ...' : '' ) )
		: 'Every unapproved record carries a usable source.'
);

/* ── Gateway previews ────────────────────────────────────────────────────── */

if ( nice_site_owns_gateway_projects() ) {
	$nice_previews = nice_get_gateway_project_views();
	$nice_broken   = array_filter(
		$nice_previews,
		static function ( $preview ) {
			return ! $preview['url'] || ! $preview['summary'];
		}
	);

	nice_readiness_row(
		'Gateway previews',
		count( $nice_previews ) >= 1 && ! $nice_broken,
		$nice_previews
			? sprintf( '%d published, %d missing a destination or summary.', count( $nice_previews ), count( $nice_broken ) )
			: 'None published. The front page shows no preview section until at least one is.'
	);
}

/* ── Result ──────────────────────────────────────────────────────────────── */

echo "\nNICE launch readiness\n\n";

foreach ( $nice_rows as $nice_row ) {
	printf( "%s  %-28s %s\n", $nice_row[0], $nice_row[1], $nice_row[2] );
}

printf(
	"\n%d of %d areas still need attention.\n",
	$nice_pending,
	count( $nice_rows )
);
