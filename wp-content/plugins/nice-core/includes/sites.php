<?php
/**
 * Which division this installation serves, and where the others live.
 *
 * Two shapes, one codebase. Everything that builds or matches a division path
 * reads from here, so they differ by configuration rather than by code.
 *
 * COMBINED -- one installation serves everything, with Events and Studio behind
 * the /events/ and /studio/ path prefixes:
 *
 *     define( 'NICE_SITE_DIVISION', 'combined' );
 *
 * That is all it needs. There are no siblings to point at, because there are no
 * siblings: every link resolves to a path on this host.
 *
 * SPLIT -- three installations, each owning a hostname, each division's content
 * at its own root with no prefix:
 *
 *     define( 'NICE_SITE_DIVISION', 'events' );   // or 'studio', or 'main'
 *
 *     define( 'NICE_MAIN_SITE_URL', 'https://nicesolutions.in' );
 *     define( 'NICE_EVENTS_SITE_URL', 'https://events.nicesolutions.in' );
 *     define( 'NICE_STUDIO_SITE_URL', 'https://studios.nicesolutions.in' );
 *
 * Declaring nothing still yields the combined shape, which is what the local
 * development site relies on. On a production host that silence is refused
 * instead: an installation that has never been told what it is should not be
 * guessed at, and 'combined' exists so the answer can be given explicitly.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divisions this codebase knows about.
 *
 * This is the content vocabulary. It is deliberately separate from the set of
 * site identities below, because 'main' is an installation that owns no
 * division content at all.
 *
 * @return string[]
 */
function nice_get_division_slugs() {
	return array( 'events', 'studio' );
}

/**
 * Identities an installation may declare.
 *
 * 'combined' serves every division from one installation behind path prefixes.
 * 'main' is the gateway: it publishes curated previews and links out, and owns
 * none of the division content itself. The other two each own one division.
 *
 * @return string[]
 */
function nice_get_site_identities() {
	return array( 'combined', 'main', 'events', 'studio' );
}

/**
 * Report whether this installation serves every division from one database.
 *
 * True both for an explicit 'combined' and for an installation that has
 * declared nothing, because the two behave identically -- they differ only in
 * whether a production host will run setup for them.
 *
 * @return bool
 */
function nice_is_combined_site() {
	$identity = nice_get_site_identity();

	return '' === $identity || 'combined' === $identity;
}

/**
 * Return the identity this installation serves.
 *
 * An empty string means the combined development site, which serves every
 * division behind its path prefix. Production installations always declare
 * themselves: 'main' for the gateway, or a division slug.
 *
 * @return string Identity, or an empty string for the combined site.
 */
function nice_get_site_identity() {
	$identity = defined( 'NICE_SITE_DIVISION' ) ? sanitize_key( (string) NICE_SITE_DIVISION ) : '';

	if ( '' !== $identity && ! in_array( $identity, nice_get_site_identities(), true ) ) {
		/*
		 * Falling back silently would hand a typo the combined site, which on a
		 * production host publishes both divisions from one installation.
		 */
		_doing_it_wrong(
			__FUNCTION__,
			sprintf(
				/* translators: 1: configured value, 2: accepted values. */
				esc_html__( 'NICE_SITE_DIVISION is set to "%1$s", which is not recognised. Use one of: %2$s. Falling back to the combined site.', 'nice-core' ),
				esc_html( $identity ),
				esc_html( implode( ', ', nice_get_site_identities() ) )
			),
			'1.2.0'
		);

		$identity = '';
	}

	/**
	 * Filter the identity this installation serves.
	 *
	 * @param string $identity 'main', a division slug, or an empty string for the combined site.
	 */
	return (string) apply_filters( 'nice_site_division', $identity );
}

/**
 * Return the division this installation serves.
 *
 * The gateway serves no division, so it reports an empty string here just as
 * the combined site does. Use nice_is_gateway_site() to tell them apart.
 *
 * @return string Division slug, or an empty string.
 */
function nice_get_site_division() {
	$identity = nice_get_site_identity();

	return in_array( $identity, nice_get_division_slugs(), true ) ? $identity : '';
}

/**
 * Report whether this installation is the gateway.
 *
 * @return bool
 */
function nice_is_gateway_site() {
	return 'main' === nice_get_site_identity();
}

/**
 * Report whether this installation serves only one division.
 *
 * @return bool
 */
function nice_is_division_site() {
	return '' !== nice_get_site_division();
}

/**
 * Report whether a division's content is served by this installation.
 *
 * The combined site serves both. A division site serves only its own. The
 * gateway serves neither, and links to both by absolute URL.
 *
 * This predicate is the single gate for rewrite rules, route enforcement, page
 * provisioning and cross-site URL resolution, so the whole gateway behaviour
 * follows from the one branch below.
 *
 * @param string $division Division slug.
 * @return bool
 */
function nice_division_is_local( $division ) {
	$division = sanitize_key( $division );

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return false;
	}

	if ( nice_is_gateway_site() ) {
		return false;
	}

	$site_division = nice_get_site_division();

	return '' === $site_division || $site_division === $division;
}

/**
 * Return the absolute site URLs configured for each division and the gateway.
 *
 * @return array<string, string> Keys are division slugs plus 'main'.
 */
function nice_get_division_site_urls() {
	$urls = array(
		'main'   => defined( 'NICE_MAIN_SITE_URL' ) ? (string) NICE_MAIN_SITE_URL : '',
		'events' => defined( 'NICE_EVENTS_SITE_URL' ) ? (string) NICE_EVENTS_SITE_URL : '',
		'studio' => defined( 'NICE_STUDIO_SITE_URL' ) ? (string) NICE_STUDIO_SITE_URL : '',
	);

	$stored = get_option( 'nice_division_site_urls', array() );
	if ( is_array( $stored ) ) {
		foreach ( $stored as $key => $value ) {
			$key = sanitize_key( $key );

			if ( isset( $urls[ $key ] ) && ! $urls[ $key ] ) {
				$urls[ $key ] = (string) $value;
			}
		}
	}

	foreach ( $urls as $key => $value ) {
		$value = $value ? esc_url_raw( untrailingslashit( trim( $value ) ) ) : '';

		$urls[ $key ] = $value;
	}

	/**
	 * Filter the absolute URLs of the sibling installations.
	 *
	 * @param array<string, string> $urls Keys are division slugs plus 'main'.
	 */
	return (array) apply_filters( 'nice_division_site_urls', $urls );
}

/**
 * Return the path prefix a division's content sits behind on this installation.
 *
 * The combined site keeps the division slug as the prefix. A division site owns
 * its hostname, so its own content sits at the root with no prefix.
 *
 * @param string $division Division slug.
 * @return string Prefix without surrounding slashes, or an empty string.
 */
function nice_get_division_prefix( $division ) {
	$division = sanitize_key( $division );

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return '';
	}

	$prefix = nice_get_site_division() === $division ? '' : $division;

	/**
	 * Filter the path prefix used for a division on this installation.
	 *
	 * @param string $prefix   Prefix without surrounding slashes.
	 * @param string $division Division slug.
	 */
	return (string) apply_filters( 'nice_division_prefix', $prefix, $division );
}

/**
 * Build a URL for a path inside a division.
 *
 * Content served here resolves against this site. Content owned by a sibling
 * installation resolves against its configured URL, and falls back to the
 * combined-site shape when no sibling URL has been configured yet.
 *
 * @param string $division Division slug.
 * @param string $path     Path within the division, without a leading slash.
 * @return string
 */
function nice_get_division_url( $division, $path = '' ) {
	$division = sanitize_key( $division );
	$path     = ltrim( (string) $path, '/' );

	if ( ! in_array( $division, nice_get_division_slugs(), true ) ) {
		return '';
	}

	if ( ! nice_division_is_local( $division ) ) {
		$urls = nice_get_division_site_urls();

		if ( ! empty( $urls[ $division ] ) ) {
			/*
			 * trailingslashit() rather than user_trailingslashit(): the sibling is
			 * a separate installation, and its canonical URLs carry a trailing
			 * slash whatever this site's own permalink structure happens to be.
			 * Asking the local setting about a remote host sends every cross-site
			 * link through a redirect, and through none at all on a plain-permalink
			 * gateway.
			 */
			return trailingslashit( $urls[ $division ] . '/' . $path );
		}
	}

	$prefix   = nice_get_division_prefix( $division );
	$relative = ltrim( $prefix . '/' . $path, '/' );

	return $relative ? home_url( user_trailingslashit( $relative ) ) : home_url( '/' );
}

/**
 * Return the URL of the main gateway site.
 *
 * @return string
 */
function nice_get_main_site_url() {
	$urls = nice_get_division_site_urls();

	if ( nice_is_division_site() && ! empty( $urls['main'] ) ) {
		return trailingslashit( $urls['main'] );
	}

	return home_url( '/' );
}

/**
 * Return the request path prefix that identifies a division, with slashes.
 *
 * On a division site every path belongs to that division, so the identifying
 * prefix is simply the site root.
 *
 * @param string $division Division slug.
 * @return string
 */
function nice_get_division_path_prefix( $division ) {
	$prefix = nice_get_division_prefix( $division );

	return $prefix ? '/' . $prefix . '/' : '/';
}

/**
 * Return a human-readable label for an identity.
 *
 * @param string $identity Identity slug, or an empty string for the combined site.
 * @return string
 */
function nice_get_site_identity_label( $identity = null ) {
	$identity = null === $identity ? nice_get_site_identity() : sanitize_key( (string) $identity );

	$labels = array(
		''         => __( 'Combined site (identity not declared)', 'nice-core' ),
		'combined' => __( 'Combined site, Events and Studio under /events/ and /studio/', 'nice-core' ),
		'main'     => __( 'Main gateway', 'nice-core' ),
		'events'   => __( 'NICE Events', 'nice-core' ),
		'studio'   => __( 'NICE Studio', 'nice-core' ),
	);

	return $labels[ $identity ] ?? __( 'Unrecognised', 'nice-core' );
}

/**
 * Report whether this host should be treated as production.
 *
 * The combined site is a legitimate configuration locally and a serious
 * misconfiguration on a live host, so the two have to be told apart before the
 * setup screen decides whether to refuse.
 *
 * @return bool
 */
function nice_is_production_environment() {
	$environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';

	/**
	 * Filter whether this installation counts as production.
	 *
	 * @param bool   $is_production Whether the environment is production.
	 * @param string $environment   Resolved WordPress environment type.
	 */
	return (bool) apply_filters( 'nice_is_production_environment', 'production' === $environment || 'staging' === $environment, $environment );
}

/**
 * Describe anything wrong with this installation's identity configuration.
 *
 * Returns an empty array when the installation is safe to set up. Each entry is
 * a fatal problem: the setup routine must refuse rather than guess.
 *
 * @return string[]
 */
function nice_get_site_identity_problems() {
	$problems = array();
	$declared = defined( 'NICE_SITE_DIVISION' ) ? sanitize_key( (string) NICE_SITE_DIVISION ) : '';

	if ( $declared && ! in_array( $declared, nice_get_site_identities(), true ) ) {
		$problems[] = sprintf(
			/* translators: 1: configured value, 2: accepted values. */
			__( 'NICE_SITE_DIVISION is set to "%1$s", which is not one of: %2$s.', 'nice-core' ),
			$declared,
			implode( ', ', nice_get_site_identities() )
		);

		return $problems;
	}

	/*
	 * An undeclared identity is refused on production, but an explicit
	 * 'combined' is not. The risk was never the combined shape itself -- it is
	 * a supported way to run NICE -- but publishing both divisions from one
	 * database by accident, because nobody had said what the installation was.
	 * Saying so removes the accident.
	 */
	if ( '' === nice_get_site_identity() && nice_is_production_environment() ) {
		$problems[] = __( 'NICE_SITE_DIVISION is not defined, so this installation has not been told what it serves. Define it in wp-config.php: combined for one site serving Events and Studio under /events/ and /studio/, or main, events or studio for a separate installation per hostname.', 'nice-core' );
	}

	return $problems;
}

/**
 * Describe configuration that is missing but not fatal.
 *
 * Cross-site links fall back to the combined-site shape when a sibling URL is
 * absent, which is wrong on production but does not make setup unsafe.
 *
 * @return string[]
 */
function nice_get_site_identity_warnings() {
	$warnings = array();

	/*
	 * A combined site has no siblings, so a missing sibling URL is not a missing
	 * setting. Its permalink check still runs below.
	 */
	if ( nice_is_combined_site() ) {
		return nice_get_permalink_warnings();
	}

	$urls   = nice_get_division_site_urls();
	$labels = array(
		'main'   => 'NICE_MAIN_SITE_URL',
		'events' => 'NICE_EVENTS_SITE_URL',
		'studio' => 'NICE_STUDIO_SITE_URL',
	);

	foreach ( $labels as $key => $constant ) {
		if ( $key === nice_get_site_identity() ) {
			continue;
		}

		if ( empty( $urls[ $key ] ) ) {
			$warnings[] = sprintf(
				/* translators: %s: wp-config constant name. */
				__( '%s is not defined, so links to that installation fall back to a path on this host.', 'nice-core' ),
				$constant
			);
		}
	}

	return array_merge( $warnings, nice_get_permalink_warnings() );
}

/**
 * Warn when permalinks would stop every NICE route from matching.
 *
 * A fresh WordPress uses plain permalinks, under which none of the content
 * routes can ever match. The site would come up looking installed and then 404
 * every service, project and section page.
 *
 * @return string[]
 */
function nice_get_permalink_warnings() {
	if ( get_option( 'permalink_structure' ) ) {
		return array();
	}

	return array( __( 'Permalinks are set to Plain. Every NICE route needs a pretty permalink structure: choose Post name under Settings > Permalinks and save, then run setup again.', 'nice-core' ) );
}

/**
 * Return the divisions whose content this installation owns.
 *
 * The combined site owns both, a division site owns one, the gateway owns none.
 *
 * @return string[]
 */
function nice_get_local_division_slugs() {
	return array_values( array_filter( nice_get_division_slugs(), 'nice_division_is_local' ) );
}
