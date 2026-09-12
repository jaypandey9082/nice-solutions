<?php
/**
 * Contact-channel presentation adapter.
 *
 * NICE Core can provide approved values through the nice_contact_settings
 * filter without coupling the theme to an options implementation.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the centralized public-contact schema.
 *
 * @return array<string, mixed>
 */
if ( ! function_exists( 'nice_get_contact_settings' ) ) {
	function nice_get_contact_settings() {
		$defaults = array(
			'whatsapp_url' => '',
			'email_address' => '',
			'phone'         => '',
			'phone_url'     => '',
			'social_urls'   => array(),
			'social'        => array(),
			'divisions'     => array(),
		);

		$settings = apply_filters( 'nice_contact_settings', $defaults );

		return is_array( $settings ) ? wp_parse_args( $settings, $defaults ) : $defaults;
	}
}

/**
 * Divisions that publish their own contact channels, in display order.
 *
 * @return array<string, string>
 */
function nice_theme_get_contact_division_labels() {
	if ( function_exists( 'nice_get_contact_division_labels' ) ) {
		return nice_get_contact_division_labels();
	}

	return array(
		'events' => __( 'Events', 'nice' ),
		'studio' => __( 'Studio', 'nice' ),
	);
}

/**
 * Detect the division that owns the current request.
 *
 * @return string Division slug, or an empty string outside a division.
 */
function nice_theme_get_contact_division() {
	if ( function_exists( 'nice_theme_is_events_context' ) && nice_theme_is_events_context() ) {
		return 'events';
	}

	if ( function_exists( 'nice_theme_is_studio_context' ) && nice_theme_is_studio_context() ) {
		return 'studio';
	}

	return '';
}

/**
 * Resolve the channel set that applies to a scope.
 *
 * @param string|null $division Division slug, an empty string for the shared scope, or null to detect.
 * @return array{whatsapp_url: string, email_address: string, phone: string, phone_url: string}
 */
function nice_theme_get_contact_channels( $division = null ) {
	if ( null === $division ) {
		$division = nice_theme_get_contact_division();
	}

	if ( function_exists( 'nice_get_contact_channels' ) ) {
		return nice_get_contact_channels( $division );
	}

	$settings = nice_get_contact_settings();
	$branch   = $division && isset( $settings['divisions'][ $division ] ) ? $settings['divisions'][ $division ] : $settings;

	return array(
		'whatsapp_url'  => (string) ( $branch['whatsapp_url'] ?? '' ),
		'email_address' => (string) ( $branch['email_address'] ?? '' ),
		'phone'         => (string) ( $branch['phone'] ?? '' ),
		'phone_url'     => (string) ( $branch['phone_url'] ?? '' ),
	);
}

/**
 * Return every labelled channel set that applies to a scope.
 *
 * Division pages yield a single unlabelled-in-practice set. The shared scope
 * yields one set per publishing division, so the main pages can show both.
 *
 * @param string|null $division Division slug, an empty string for the shared scope, or null to detect.
 * @return array<int, array<string, string>>
 */
function nice_theme_get_contact_channel_sets( $division = null ) {
	if ( null === $division ) {
		$division = nice_theme_get_contact_division();
	}

	if ( function_exists( 'nice_get_contact_channel_sets' ) ) {
		return nice_get_contact_channel_sets( $division );
	}

	$channels = nice_theme_get_contact_channels( $division );

	if ( ! $channels['whatsapp_url'] && ! $channels['email_address'] && ! $channels['phone_url'] ) {
		return array();
	}

	$labels = nice_theme_get_contact_division_labels();

	return array(
		array_merge(
			array(
				'division' => (string) $division,
				'label'    => $division ? ( $labels[ $division ] ?? '' ) : '',
			),
			$channels
		),
	);
}

/**
 * Return published social profiles with their labels, in display order.
 *
 * @return array<int, array{key: string, label: string, url: string}>
 */
function nice_theme_get_social_profiles() {
	if ( function_exists( 'nice_get_social_profiles' ) ) {
		return nice_get_social_profiles();
	}

	$settings = nice_get_contact_settings();
	$social   = is_array( $settings['social'] ?? null ) ? $settings['social'] : array();
	$labels   = array(
		'linkedin'  => __( 'LinkedIn', 'nice' ),
		'instagram' => __( 'Instagram', 'nice' ),
		'facebook'  => __( 'Facebook', 'nice' ),
	);

	$profiles = array();
	foreach ( $labels as $key => $label ) {
		if ( ! empty( $social[ $key ] ) ) {
			$profiles[] = array(
				'key'   => $key,
				'label' => $label,
				'url'   => (string) $social[ $key ],
			);
		}
	}

	return $profiles;
}

/**
 * Resolve a rendered contact action, falling back to the approval notice.
 *
 * @param string      $channel         Supported channel name.
 * @param string      $placeholder_url Optional page-specific approval notice URL.
 * @param string|null $division        Division slug, an empty string to force the shared scope,
 *                                     or null to detect from the current request.
 * @return array{url: string, placeholder: bool}
 */
function nice_get_contact_action( $channel, $placeholder_url = '', $division = null ) {
	$settings = nice_theme_get_contact_channels( $division );
	$action   = array(
		'url'         => $placeholder_url ? $placeholder_url : home_url( '/#contact-details-pending' ),
		'placeholder' => true,
	);

	if ( 'whatsapp' === $channel && ! empty( $settings['whatsapp_url'] ) ) {
		$url = esc_url_raw( $settings['whatsapp_url'], array( 'https' ) );

		if ( $url ) {
			$action['url']         = $url;
			$action['placeholder'] = false;
		}
	}

	if ( 'email' === $channel && ! empty( $settings['email_address'] ) ) {
		$email = sanitize_email( $settings['email_address'] );

		if ( is_email( $email ) ) {
			$action['url']         = 'mailto:' . $email;
			$action['placeholder'] = false;
		}
	}

	if ( 'phone' === $channel && ! empty( $settings['phone_url'] ) ) {
		$url = esc_url_raw( $settings['phone_url'], array( 'tel' ) );

		if ( $url ) {
			$action['url']         = $url;
			$action['placeholder'] = false;
		}
	}

	return $action;
}
