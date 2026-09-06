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
			'phone_url'     => '',
			'social_urls'   => array(),
		);

		$settings = apply_filters( 'nice_contact_settings', $defaults );

		return is_array( $settings ) ? wp_parse_args( $settings, $defaults ) : $defaults;
	}
}

/**
 * Resolve a rendered contact action, falling back to the approval notice.
 *
 * @param string $channel         Supported channel name.
 * @param string $placeholder_url Optional page-specific approval notice URL.
 * @return array{url: string, placeholder: bool}
 */
function nice_get_contact_action( $channel, $placeholder_url = '' ) {
	$settings = nice_get_contact_settings();
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

	return $action;
}
