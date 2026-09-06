<?php
/**
 * Publication-safe NICE contact settings.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the stored contact option with predictable keys.
 *
 * @return array{whatsapp_url: string, email_address: string, phone: string, phone_url: string, social_urls: string[]}
 */
if ( ! function_exists( 'nice_get_contact_settings' ) ) {
	function nice_get_contact_settings() {
		$defaults = array(
			'whatsapp_url' => '',
			'email_address' => '',
			'phone'         => '',
			'social_urls'   => array(),
		);
		$stored   = get_option( 'nice_contact_settings', array() );
		$settings = is_array( $stored ) ? wp_parse_args( $stored, $defaults ) : $defaults;
		$phone    = nice_sanitize_phone( $settings['phone'] );

		$settings['whatsapp_url'] = nice_sanitize_https_url( $settings['whatsapp_url'] );
		$settings['email_address'] = is_email( $settings['email_address'] ) ? sanitize_email( $settings['email_address'] ) : '';
		$settings['phone']         = $phone;
		$settings['phone_url']     = nice_phone_to_url( $phone );
		$settings['social_urls']   = array_values( array_filter( array_map( 'nice_sanitize_https_url', (array) $settings['social_urls'] ) ) );

		return apply_filters( 'nice_contact_settings', $settings );
	}
}

/**
 * Sanitize a display phone number while preserving international formatting.
 *
 * @param mixed $value Candidate phone number.
 * @return string
 */
function nice_sanitize_phone( $value ) {
	$value = sanitize_text_field( (string) $value );
	$value = preg_replace( '/[^0-9+() .-]/', '', $value );

	return trim( (string) $value );
}

/**
 * Convert a display phone number into a usable tel URL.
 *
 * @param string $phone Sanitized phone number.
 * @return string
 */
function nice_phone_to_url( $phone ) {
	if ( ! $phone ) {
		return '';
	}

	$number = preg_replace( '/[^0-9+]/', '', $phone );

	return $number ? 'tel:' . $number : '';
}

/**
 * Sanitize the complete contact settings payload.
 *
 * Invalid values retain the last approved value rather than becoming public.
 *
 * @param mixed $input Submitted option value.
 * @return array<string, mixed>
 */
function nice_sanitize_contact_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$previous = get_option( 'nice_contact_settings', array() );
	$previous = is_array( $previous ) ? $previous : array();
	$output   = array(
		'whatsapp_url' => '',
		'email_address'=> '',
		'phone'        => '',
		'social_urls'  => array(),
	);

	$whatsapp = trim( (string) ( $input['whatsapp_url'] ?? '' ) );
	if ( $whatsapp ) {
		$output['whatsapp_url'] = nice_sanitize_https_url( $whatsapp );

		if ( ! $output['whatsapp_url'] ) {
			$output['whatsapp_url'] = nice_sanitize_https_url( $previous['whatsapp_url'] ?? '' );
			add_settings_error( 'nice_contact_settings', 'nice_invalid_whatsapp', __( 'WhatsApp must be a valid HTTPS URL.', 'nice-core' ) );
		}
	}

	$email = sanitize_email( $input['email_address'] ?? '' );
	if ( ! empty( $input['email_address'] ) && ! is_email( $email ) ) {
		$output['email_address'] = is_email( $previous['email_address'] ?? '' ) ? sanitize_email( $previous['email_address'] ) : '';
		add_settings_error( 'nice_contact_settings', 'nice_invalid_email', __( 'Enter a valid email address.', 'nice-core' ) );
	} else {
		$output['email_address'] = $email;
	}

	$output['phone'] = nice_sanitize_phone( $input['phone'] ?? '' );

	$social_values = $input['social_urls'] ?? array();
	if ( is_string( $social_values ) ) {
		$social_values = preg_split( '/\r\n|\r|\n/', $social_values );
	}

	$invalid_social = false;
	foreach ( (array) $social_values as $social_url ) {
		$social_url = trim( (string) $social_url );

		if ( ! $social_url ) {
			continue;
		}

		$valid_url = nice_sanitize_https_url( $social_url );

		if ( $valid_url ) {
			$output['social_urls'][] = $valid_url;
		} else {
			$invalid_social = true;
			add_settings_error( 'nice_contact_settings', 'nice_invalid_social', __( 'Social links must be valid HTTPS URLs.', 'nice-core' ) );
		}
	}

	if ( $invalid_social ) {
		$output['social_urls'] = array_values( array_filter( array_map( 'nice_sanitize_https_url', (array) ( $previous['social_urls'] ?? array() ) ) ) );
	} else {
		$output['social_urls'] = array_values( array_unique( $output['social_urls'] ) );
	}

	return $output;
}

/**
 * Register the contact option and its native Settings API fields.
 */
function nice_register_contact_settings() {
	register_setting(
		'nice_contact',
		'nice_contact_settings',
		array(
			'type'              => 'object',
			'default'           => array(),
			'sanitize_callback' => 'nice_sanitize_contact_settings',
			'show_in_rest'      => false,
		)
	);

	add_settings_section(
		'nice_contact_channels',
		__( 'Approved contact channels', 'nice-core' ),
		'nice_render_contact_settings_description',
		'nice-contact'
	);

	$fields = array(
		'whatsapp_url' => __( 'WhatsApp URL', 'nice-core' ),
		'email_address'=> __( 'Email', 'nice-core' ),
		'phone'        => __( 'Phone', 'nice-core' ),
		'social_urls'  => __( 'Social URLs', 'nice-core' ),
	);

	foreach ( $fields as $key => $label ) {
		add_settings_field(
			'nice_contact_' . $key,
			$label,
			'nice_render_contact_setting_field',
			'nice-contact',
			'nice_contact_channels',
			array( 'key' => $key )
		);
	}
}
add_action( 'admin_init', 'nice_register_contact_settings' );

/**
 * Add the Contact Settings page under Settings.
 */
function nice_add_contact_settings_page() {
	add_options_page(
		__( 'NICE Contact Settings', 'nice-core' ),
		__( 'NICE Contact', 'nice-core' ),
		'manage_options',
		'nice-contact',
		'nice_render_contact_settings_page'
	);
}
add_action( 'admin_menu', 'nice_add_contact_settings_page' );

/**
 * Explain the publication boundary in wp-admin.
 */
function nice_render_contact_settings_description() {
	echo '<p>' . esc_html__( 'Enter only contact details approved for public website use. Empty fields remain unpublished.', 'nice-core' ) . '</p>';
}

/**
 * Render one contact field.
 *
 * @param array{key: string} $args Field arguments.
 */
function nice_render_contact_setting_field( $args ) {
	$settings = nice_get_contact_settings();
	$key      = $args['key'];
	$value    = $settings[ $key ] ?? '';
	$name     = 'nice_contact_settings[' . $key . ']';

	if ( 'social_urls' === $key ) {
		printf(
			'<textarea class="large-text code" rows="5" id="nice-contact-%1$s" name="%2$s" placeholder="https://">%3$s</textarea><p class="description">%4$s</p>',
			esc_attr( $key ),
			esc_attr( $name ),
			esc_textarea( implode( "\n", (array) $value ) ),
			esc_html__( 'One approved HTTPS URL per line.', 'nice-core' )
		);
		return;
	}

	$type = 'email_address' === $key ? 'email' : 'text';
	printf(
		'<input class="regular-text" type="%1$s" id="nice-contact-%2$s" name="%3$s" value="%4$s"%5$s>',
		esc_attr( $type ),
		esc_attr( $key ),
		esc_attr( $name ),
		esc_attr( $value ),
		'whatsapp_url' === $key ? ' placeholder="https://"' : ''
	);
}

/**
 * Render the native contact settings screen.
 */
function nice_render_contact_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'NICE Contact Settings', 'nice-core' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'nice_contact' );
			do_settings_sections( 'nice-contact' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Return the approved WhatsApp URL.
 *
 * @return string
 */
function nice_get_contact_whatsapp_url() {
	return nice_get_contact_settings()['whatsapp_url'];
}

/**
 * Return the approved email address.
 *
 * @return string
 */
function nice_get_contact_email() {
	return nice_get_contact_settings()['email_address'];
}

/**
 * Return the approved tel URL.
 *
 * @return string
 */
function nice_get_contact_phone_url() {
	return nice_get_contact_settings()['phone_url'];
}

/**
 * Return approved social URLs.
 *
 * @return string[]
 */
function nice_get_social_links() {
	return nice_get_contact_settings()['social_urls'];
}
