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
 * Divisions that own their own contact channels, in display order.
 *
 * @return array<string, string> Division slug to public label.
 */
function nice_get_contact_division_labels() {
	return array(
		'events' => __( 'Events', 'nice-core' ),
		'studio' => __( 'Studio', 'nice-core' ),
	);
}

/**
 * Social networks NICE publishes, in display order.
 *
 * @return array<string, string> Network key to public label.
 */
function nice_get_social_network_labels() {
	return array(
		'linkedin'  => __( 'LinkedIn', 'nice-core' ),
		'instagram' => __( 'Instagram', 'nice-core' ),
		'facebook'  => __( 'Facebook', 'nice-core' ),
	);
}

/**
 * Shape of one contact scope before any derivation.
 *
 * @return array{whatsapp_url: string, email_address: string, phone: string}
 */
function nice_get_contact_channel_defaults() {
	return array(
		'whatsapp_url'  => '',
		'email_address' => '',
		'phone'         => '',
	);
}

/**
 * Return the raw stored option in its full shape, without derivation.
 *
 * The admin field renderer must use this rather than nice_get_contact_settings().
 * Rendering derived values into the form would save them back as stored overrides,
 * so a derived wa.me link would permanently replace the derivation that produced it.
 *
 * @return array<string, mixed>
 */
function nice_get_stored_contact_settings() {
	$stored   = get_option( 'nice_contact_settings', array() );
	$stored   = is_array( $stored ) ? $stored : array();
	$defaults = nice_get_contact_channel_defaults();

	$settings = wp_parse_args(
		$stored,
		array_merge(
			$defaults,
			array(
				'social_urls' => array(),
				'social'      => array(),
				'divisions'   => array(),
			)
		)
	);

	/* wp_parse_args() is shallow, so every nested branch is merged on its own. */
	$social = is_array( $settings['social'] ) ? $settings['social'] : array();
	foreach ( array_keys( nice_get_social_network_labels() ) as $network ) {
		$settings['social'][ $network ] = (string) ( $social[ $network ] ?? '' );
	}

	$divisions = is_array( $settings['divisions'] ) ? $settings['divisions'] : array();
	foreach ( array_keys( nice_get_contact_division_labels() ) as $division ) {
		$branch = isset( $divisions[ $division ] ) && is_array( $divisions[ $division ] ) ? $divisions[ $division ] : array();

		$settings['divisions'][ $division ] = wp_parse_args( $branch, $defaults );
	}

	$settings['social_urls'] = (array) $settings['social_urls'];

	return $settings;
}

/**
 * Resolve and derive one contact scope.
 *
 * @param array<string, mixed> $scope    Stored values for the scope.
 * @param array<string, mixed> $fallback Values inherited when the scope leaves a field blank.
 * @return array{whatsapp_url: string, email_address: string, phone: string, phone_url: string}
 */
function nice_resolve_contact_channels( $scope, $fallback = array() ) {
	$phone = nice_sanitize_phone( $scope['phone'] ?? '' );
	if ( ! $phone ) {
		$phone = nice_sanitize_phone( $fallback['phone'] ?? '' );
	}

	$email = sanitize_email( (string) ( $scope['email_address'] ?? '' ) );
	if ( ! is_email( $email ) ) {
		$email = sanitize_email( (string) ( $fallback['email_address'] ?? '' ) );
		$email = is_email( $email ) ? $email : '';
	}

	/* A stored WhatsApp URL is an override. Blank derives from the phone of this scope. */
	$whatsapp = nice_sanitize_https_url( $scope['whatsapp_url'] ?? '' );
	if ( ! $whatsapp ) {
		$whatsapp = nice_sanitize_https_url( $fallback['whatsapp_url'] ?? '' );
	}
	if ( ! $whatsapp ) {
		$whatsapp = nice_phone_to_whatsapp_url( $phone );
	}

	return array(
		'whatsapp_url'  => $whatsapp,
		'email_address' => $email,
		'phone'         => $phone,
		'phone_url'     => nice_phone_to_url( $phone ),
	);
}

/**
 * Return the stored contact option with predictable keys.
 *
 * Every legacy key keeps its original meaning so existing callers are unaffected.
 * Division branches and named social profiles are added alongside them.
 *
 * @return array{whatsapp_url: string, email_address: string, phone: string, phone_url: string, social_urls: string[], social: array<string, string>, divisions: array<string, array<string, string>>}
 */
if ( ! function_exists( 'nice_get_contact_settings' ) ) {
	function nice_get_contact_settings() {
		$stored   = nice_get_stored_contact_settings();
		$settings = nice_resolve_contact_channels( $stored );

		$social = array();
		foreach ( array_keys( nice_get_social_network_labels() ) as $network ) {
			$social[ $network ] = nice_sanitize_https_url( $stored['social'][ $network ] ?? '' );
		}
		$settings['social'] = $social;

		/* The flat list stays the published contract: named profiles first, then any extras. */
		$settings['social_urls'] = array_values(
			array_unique(
				array_filter(
					array_merge(
						array_values( $social ),
						array_map( 'nice_sanitize_https_url', (array) $stored['social_urls'] )
					)
				)
			)
		);

		$settings['divisions'] = array();
		foreach ( array_keys( nice_get_contact_division_labels() ) as $division ) {
			$settings['divisions'][ $division ] = nice_resolve_contact_channels( $stored['divisions'][ $division ], $stored );
		}

		return apply_filters( 'nice_contact_settings', $settings );
	}
}

/**
 * Return the resolved channels for one scope.
 *
 * @param string $division Division slug, or an empty string for the shared scope.
 * @return array{whatsapp_url: string, email_address: string, phone: string, phone_url: string}
 */
function nice_get_contact_channels( $division = '' ) {
	$settings = nice_get_contact_settings();
	$division = sanitize_key( $division );

	if ( $division && isset( $settings['divisions'][ $division ] ) ) {
		return $settings['divisions'][ $division ];
	}

	return array(
		'whatsapp_url'  => $settings['whatsapp_url'],
		'email_address' => $settings['email_address'],
		'phone'         => $settings['phone'],
		'phone_url'     => $settings['phone_url'],
	);
}

/**
 * Report whether a resolved scope publishes anything at all.
 *
 * @param array<string, string> $channels Resolved channel set.
 * @return bool
 */
function nice_contact_channels_published( $channels ) {
	return (bool) ( ( $channels['whatsapp_url'] ?? '' ) || ( $channels['email_address'] ?? '' ) || ( $channels['phone_url'] ?? '' ) );
}

/**
 * Return every labelled channel set that applies to a scope.
 *
 * A division shows its own channels. The shared scope shows the company-wide
 * channels when they exist, and otherwise lists each division that publishes,
 * so the main pages point at a real person instead of falling silent.
 *
 * @param string $division Division slug, or an empty string for the shared scope.
 * @return array<int, array{division: string, label: string, whatsapp_url: string, email_address: string, phone: string, phone_url: string}>
 */
function nice_get_contact_channel_sets( $division = '' ) {
	$division = sanitize_key( $division );
	$labels   = nice_get_contact_division_labels();

	if ( $division && isset( $labels[ $division ] ) ) {
		$channels = nice_get_contact_channels( $division );

		return nice_contact_channels_published( $channels )
			? array(
				array_merge(
					array(
						'division'    => $division,
						'label'       => $labels[ $division ],
						'contact_url' => home_url( '/' . $division . '/contact/' ),
					),
					$channels
				),
			)
			: array();
	}

	$shared = nice_get_contact_channels();
	if ( nice_contact_channels_published( $shared ) ) {
		return array( array_merge( array( 'division' => '', 'label' => '', 'contact_url' => '' ), $shared ) );
	}

	$sets = array();
	foreach ( $labels as $slug => $label ) {
		$channels = nice_get_contact_channels( $slug );

		if ( nice_contact_channels_published( $channels ) ) {
			$sets[] = array_merge(
				array(
					'division'    => $slug,
					'label'       => $label,
					'contact_url' => home_url( '/' . $slug . '/contact/' ),
				),
				$channels
			);
		}
	}

	return $sets;
}

/**
 * Return published social profiles with their labels, in display order.
 *
 * @return array<int, array{key: string, label: string, url: string}>
 */
function nice_get_social_profiles() {
	$social   = nice_get_contact_settings()['social'];
	$profiles = array();

	foreach ( nice_get_social_network_labels() as $key => $label ) {
		if ( ! empty( $social[ $key ] ) ) {
			$profiles[] = array(
				'key'   => $key,
				'label' => $label,
				'url'   => $social[ $key ],
			);
		}
	}

	return $profiles;
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
 * Convert a display phone number into a wa.me URL.
 *
 * wa.me needs a full international number. A number without a country code
 * would produce a link that silently fails, so it publishes nothing instead and
 * the channel stays in its pending state.
 *
 * @param string $phone Sanitized phone number.
 * @return string
 */
function nice_phone_to_whatsapp_url( $phone ) {
	$phone = trim( (string) $phone );

	if ( ! $phone || '+' !== substr( $phone, 0, 1 ) ) {
		return '';
	}

	$digits = preg_replace( '/[^0-9]/', '', $phone );
	$length = strlen( (string) $digits );

	if ( $length < 10 || $length > 15 ) {
		return '';
	}

	return nice_sanitize_https_url( 'https://wa.me/' . $digits );
}

/**
 * Sanitize the WhatsApp, email and phone fields of one contact scope.
 *
 * Invalid values retain the last approved value rather than becoming public.
 * The scope suffixes the error code so several scopes can report at once.
 *
 * @param mixed  $input    Submitted values for the scope.
 * @param mixed  $previous Previously approved values for the scope.
 * @param string $scope    Scope identifier used in error codes.
 * @return array{whatsapp_url: string, email_address: string, phone: string}
 */
function nice_sanitize_contact_channel_set( $input, $previous, $scope = '' ) {
	$input    = is_array( $input ) ? $input : array();
	$previous = is_array( $previous ) ? $previous : array();
	$suffix   = $scope ? '_' . $scope : '';
	$output   = nice_get_contact_channel_defaults();

	$whatsapp = trim( (string) ( $input['whatsapp_url'] ?? '' ) );
	if ( $whatsapp ) {
		$output['whatsapp_url'] = nice_sanitize_https_url( $whatsapp );

		if ( ! $output['whatsapp_url'] ) {
			$output['whatsapp_url'] = nice_sanitize_https_url( $previous['whatsapp_url'] ?? '' );
			add_settings_error( 'nice_contact_settings', 'nice_invalid_whatsapp' . $suffix, __( 'WhatsApp must be a valid HTTPS URL.', 'nice-core' ) );
		}
	}

	$email = sanitize_email( $input['email_address'] ?? '' );
	if ( ! empty( $input['email_address'] ) && ! is_email( $email ) ) {
		$output['email_address'] = is_email( $previous['email_address'] ?? '' ) ? sanitize_email( $previous['email_address'] ) : '';
		add_settings_error( 'nice_contact_settings', 'nice_invalid_email' . $suffix, __( 'Enter a valid email address.', 'nice-core' ) );
	} else {
		$output['email_address'] = $email;
	}

	$output['phone'] = nice_sanitize_phone( $input['phone'] ?? '' );

	return $output;
}

/**
 * Sanitize the named social profile map.
 *
 * An invalid URL reverts that one network rather than the whole map.
 *
 * @param mixed $input    Submitted social map.
 * @param mixed $previous Previously approved social map.
 * @return array<string, string>
 */
function nice_sanitize_contact_social_map( $input, $previous ) {
	$input    = is_array( $input ) ? $input : array();
	$previous = is_array( $previous ) ? $previous : array();
	$output   = array();

	foreach ( array_keys( nice_get_social_network_labels() ) as $network ) {
		$candidate = trim( (string) ( $input[ $network ] ?? '' ) );

		if ( ! $candidate ) {
			$output[ $network ] = '';
			continue;
		}

		$valid = nice_sanitize_https_url( $candidate );

		if ( $valid ) {
			$output[ $network ] = $valid;
			continue;
		}

		$output[ $network ] = nice_sanitize_https_url( $previous[ $network ] ?? '' );
		add_settings_error(
			'nice_contact_settings',
			'nice_invalid_social_' . $network,
			/* translators: %s: social network label. */
			sprintf( __( 'The %s URL must be a valid HTTPS URL.', 'nice-core' ), nice_get_social_network_labels()[ $network ] )
		);
	}

	return $output;
}

/**
 * Sanitize the complete contact settings payload.
 *
 * This is the sole writer of the option and rebuilds its output from scratch,
 * so every stored key must be emitted here or it is dropped on the next save.
 *
 * @param mixed $input Submitted option value.
 * @return array<string, mixed>
 */
function nice_sanitize_contact_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$previous = get_option( 'nice_contact_settings', array() );
	$previous = is_array( $previous ) ? $previous : array();

	$output = nice_sanitize_contact_channel_set( $input, $previous );
	$output['social_urls'] = array();
	$output['social']      = nice_sanitize_contact_social_map( $input['social'] ?? array(), $previous['social'] ?? array() );
	$output['divisions']   = array();

	foreach ( array_keys( nice_get_contact_division_labels() ) as $division ) {
		$output['divisions'][ $division ] = nice_sanitize_contact_channel_set(
			$input['divisions'][ $division ] ?? array(),
			$previous['divisions'][ $division ] ?? array(),
			$division
		);
	}

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

	$division_fields = array(
		'phone'        => __( 'Phone', 'nice-core' ),
		'email_address'=> __( 'Email', 'nice-core' ),
		'whatsapp_url' => __( 'WhatsApp URL', 'nice-core' ),
	);

	foreach ( nice_get_contact_division_labels() as $division => $division_label ) {
		$section = 'nice_contact_division_' . $division;

		add_settings_section(
			$section,
			/* translators: %s: division label. */
			sprintf( __( '%s division', 'nice-core' ), $division_label ),
			'nice_render_contact_division_description',
			'nice-contact'
		);

		foreach ( $division_fields as $key => $label ) {
			add_settings_field(
				'nice_contact_' . $division . '_' . $key,
				$label,
				'nice_render_contact_setting_field',
				'nice-contact',
				$section,
				array(
					'key'      => $key,
					'division' => $division,
				)
			);
		}
	}

	add_settings_section(
		'nice_contact_social',
		__( 'Company social profiles', 'nice-core' ),
		'nice_render_contact_social_description',
		'nice-contact'
	);

	foreach ( nice_get_social_network_labels() as $network => $network_label ) {
		add_settings_field(
			'nice_contact_social_' . $network,
			$network_label,
			'nice_render_contact_setting_field',
			'nice-contact',
			'nice_contact_social',
			array(
				'key'   => $network,
				'group' => 'social',
			)
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
	echo '<p>' . esc_html__( 'These company-wide details are used on the main NICE pages where no division applies. Leave them blank to list each division instead.', 'nice-core' ) . '</p>';
}

/**
 * Explain how a division scope inherits from the company-wide values.
 */
function nice_render_contact_division_description() {
	echo '<p>' . esc_html__( 'Used on this division\'s pages. Leave a field blank to inherit the company-wide value above.', 'nice-core' ) . '</p>';
}

/**
 * Explain the social profile fields.
 */
function nice_render_contact_social_description() {
	echo '<p>' . esc_html__( 'Published in the site footer on every page. Each must be a valid HTTPS URL.', 'nice-core' ) . '</p>';
}

/**
 * Render one contact field.
 *
 * Values come from the stored option rather than the resolved one, so inherited
 * and derived values are never written back as explicit overrides.
 *
 * @param array{key: string, division?: string, group?: string} $args Field arguments.
 */
function nice_render_contact_setting_field( $args ) {
	$stored   = nice_get_stored_contact_settings();
	$key      = $args['key'];
	$division = $args['division'] ?? '';
	$group    = $args['group'] ?? '';

	if ( 'social' === $group ) {
		$value = $stored['social'][ $key ] ?? '';
		$name  = 'nice_contact_settings[social][' . $key . ']';
		$id    = 'nice-contact-social-' . $key;
	} elseif ( $division ) {
		$value = $stored['divisions'][ $division ][ $key ] ?? '';
		$name  = 'nice_contact_settings[divisions][' . $division . '][' . $key . ']';
		$id    = 'nice-contact-' . $division . '-' . $key;
	} else {
		$value = $stored[ $key ] ?? '';
		$name  = 'nice_contact_settings[' . $key . ']';
		$id    = 'nice-contact-' . $key;
	}

	if ( 'social_urls' === $key ) {
		printf(
			'<textarea class="large-text code" rows="5" id="%1$s" name="%2$s" placeholder="https://">%3$s</textarea><p class="description">%4$s</p>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( implode( "\n", (array) $value ) ),
			esc_html__( 'Any additional approved HTTPS URLs, one per line.', 'nice-core' )
		);
		return;
	}

	$type = 'email_address' === $key ? 'email' : 'text';
	printf(
		'<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s"%5$s>',
		esc_attr( $type ),
		esc_attr( $id ),
		esc_attr( $name ),
		esc_attr( $value ),
		( 'whatsapp_url' === $key || 'social' === $group ) ? ' placeholder="https://"' : ''
	);

	if ( 'whatsapp_url' === $key ) {
		echo '<p class="description">' . esc_html__( 'Leave blank to derive a wa.me link from the phone number. Deriving needs a country code, for example +91 9930900393.', 'nice-core' ) . '</p>';
	}
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
