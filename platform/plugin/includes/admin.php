<?php
/** Native WordPress editing controls, restricted to authorized editors. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nice-platform-page', 'NICE Page Content and Media', 'nice_platform_page_box', 'page', 'normal', 'high' );
	if ( 'main' === nice_platform_role() ) {
		add_meta_box( 'nice-platform-preview', 'NICE Project Destination', 'nice_platform_preview_box', 'nice_work_preview', 'normal', 'high' );
	}
} );

function nice_platform_page_box( $post ) {
	wp_nonce_field( 'nice_platform_save', 'nice_platform_nonce' );
	if ( 'main' === nice_platform_role() ) {
		echo '<p>Gateway copy is read from the static homepage. Blank fields stay blank. All copy fields accept plain text.</p>';
		foreach ( nice_platform_home_content( $post->ID ) as $key => $value ) {
			$label = ucwords( str_replace( '_', ' ', $key ) );
			echo '<p><label for="nice-' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br><textarea class="widefat" rows="3" id="nice-' . esc_attr( $key ) . '" name="nice_home[' . esc_attr( $key ) . ']">' . esc_textarea( $value ) . '</textarea></p>';
		}
		echo '<p><label for="nice-clients"><strong>Approved client names</strong></label><br><textarea id="nice-clients" class="widefat" rows="5" name="nice_clients">' . esc_textarea( get_post_meta( $post->ID, '_nice_client_names', true ) ) . '</textarea><span class="description">One approved client name per line. Leave empty to omit the list.</span></p>';
	}
	$slots = 'main' === nice_platform_role() ? array( 'hero', 'events', 'studio' ) : array( 'hero' );
	foreach ( $slots as $slot ) {
		$media = nice_platform_media_slot( $slot, $post->ID );
		echo '<fieldset class="nice-platform-slot"><legend><strong>' . esc_html( ucfirst( $slot ) ) . ' image</strong></legend><p class="description">Landscape desktop image: at least 2000px wide, ideally 16:9. Optional mobile image: portrait 4:5. Keep the subject near the focal point; exact cropping depends on the screen.</p>';
		foreach ( array( 'id' => 'Desktop', 'mobile_id' => 'Mobile (optional)' ) as $field => $label ) {
			$id = $media[ $field ];
			$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
			echo '<div class="nice-platform-picker" data-nice-picker><p><strong>' . esc_html( $label ) . '</strong></p><input type="hidden" data-nice-media-id name="nice_media[' . esc_attr( $slot ) . '][' . esc_attr( $field ) . ']" value="' . esc_attr( $id ) . '">';
			echo '<img data-nice-media-preview alt="' . esc_attr( $label . ' image preview' ) . '"' . ( $src ? ' src="' . esc_url( $src ) . '"' : ' hidden' ) . '><p data-nice-media-empty' . ( $src ? ' hidden' : '' ) . '>No image selected.</p>';
			echo '<p><button type="button" class="button" data-nice-media-select>' . ( $id ? 'Replace' : 'Select' ) . '</button> <button type="button" class="button" data-nice-media-remove' . ( $id ? '' : ' hidden' ) . '>Remove</button></p><span class="screen-reader-text" aria-live="polite" data-nice-media-status></span></div>';
		}
		foreach ( array( 'x' => 'Horizontal focal point', 'y' => 'Vertical focal point' ) as $field => $label ) {
			echo '<p><label>' . esc_html( $label ) . ' <input type="number" min="0" max="100" step="1" name="nice_media[' . esc_attr( $slot ) . '][' . esc_attr( $field ) . ']" value="' . esc_attr( $media[ $field ] ) . '"> %</label></p>';
		}
		echo '<p><label><input type="checkbox" name="nice_media[' . esc_attr( $slot ) . '][reference]" value="1" ' . checked( $media['reference'], true, false ) . '> Temporary reference imagery</label></p></fieldset>';
	}
}

function nice_platform_preview_box( $post ) {
	wp_nonce_field( 'nice_platform_save', 'nice_platform_nonce' );
	$division = get_post_meta( $post->ID, '_nice_division', true );
	echo '<p>Use Title, Excerpt and Featured Image for this locally curated preview. The first three published records appear on the gateway, sorted by Order in Page Attributes. Full project stories live on their division site.</p><p><label for="nice-division">Division</label> <select id="nice-division" name="nice_preview[division]"><option value="">Choose a division</option>';
	foreach ( array( 'events' => 'Events', 'studio' => 'Studio' ) as $value => $label ) {
		echo '<option value="' . esc_attr( $value ) . '" ' . selected( $division, $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></p>';
	foreach ( array( 'destination_url' => 'Absolute project URL (optional override)', 'destination_path' => 'Project path (environment-aware fallback)' ) as $key => $label ) {
		echo '<p><label for="nice-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label><input class="widefat" type="' . ( 'destination_url' === $key ? 'url' : 'text' ) . '" id="nice-' . esc_attr( $key ) . '" name="nice_preview[' . esc_attr( $key ) . ']" value="' . esc_attr( get_post_meta( $post->ID, '_nice_' . $key, true ) ) . '"></p>';
	}
	echo '<p class="description">Use a path such as /case-studies/project-name/. Absolute URLs must use the selected division\'s configured host, protocol and port. Invalid overrides are cleared; a valid path still works.</p><p><label><input type="checkbox" name="nice_preview[reference]" value="1" ' . checked( nice_platform_boolean( get_post_meta( $post->ID, '_nice_reference', true ) ), true, false ) . '> Featured Image is temporary reference imagery</label></p>';
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $screen || 'page' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'nice-platform-admin', plugins_url( 'assets/admin.js', dirname( __DIR__ ) . '/nice-platform-core.php' ), array( 'media-views' ), NICE_PLATFORM_CORE_VERSION, true );
	wp_enqueue_style( 'nice-platform-admin', plugins_url( 'assets/admin.css', dirname( __DIR__ ) . '/nice-platform-core.php' ), array(), NICE_PLATFORM_CORE_VERSION );
} );

function nice_platform_save_post( $id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) || ! current_user_can( 'edit_post', $id ) || ! isset( $_POST['nice_platform_nonce'] ) || ! is_string( $_POST['nice_platform_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nice_platform_nonce'] ) ), 'nice_platform_save' ) ) {
		return;
	}
	$type = get_post_type( $id );
	if ( 'page' === $type ) {
		if ( 'main' === nice_platform_role() && isset( $_POST['nice_home'] ) && is_array( $_POST['nice_home'] ) ) {
			$copy = wp_unslash( $_POST['nice_home'] );
			foreach ( nice_platform_home_defaults() as $key => $default ) {
				if ( isset( $copy[ $key ] ) && is_string( $copy[ $key ] ) ) {
					update_post_meta( $id, '_nice_home_' . $key, sanitize_textarea_field( $copy[ $key ] ) );
				}
			}
		}
		if ( 'main' === nice_platform_role() && isset( $_POST['nice_clients'] ) && is_string( $_POST['nice_clients'] ) ) {
			update_post_meta( $id, '_nice_client_names', sanitize_textarea_field( wp_unslash( $_POST['nice_clients'] ) ) );
		}
		$slots = 'main' === nice_platform_role() ? array( 'hero', 'events', 'studio' ) : array( 'hero' );
		$input = isset( $_POST['nice_media'] ) && is_array( $_POST['nice_media'] ) ? wp_unslash( $_POST['nice_media'] ) : array();
		foreach ( $slots as $slot ) {
			if ( ! isset( $input[ $slot ] ) || ! is_array( $input[ $slot ] ) ) {
				continue;
			}
			$media = $input[ $slot ];
			foreach ( array( 'id', 'mobile_id', 'x', 'y', 'reference' ) as $field ) {
				if ( 'reference' !== $field && ! array_key_exists( $field, $media ) ) {
					continue;
				}
				$value = $media[ $field ] ?? false;
				$value = in_array( $field, array( 'id', 'mobile_id' ), true ) ? nice_platform_image_id( $value ) : ( 'reference' === $field ? nice_platform_boolean( $value ) : nice_platform_focal( $value ) );
				update_post_meta( $id, '_nice_media_' . $slot . '_' . $field, $value );
			}
		}
	}
	if ( 'main' === nice_platform_role() && 'nice_work_preview' === $type && isset( $_POST['nice_preview'] ) && is_array( $_POST['nice_preview'] ) ) {
		$input = wp_unslash( $_POST['nice_preview'] );
		$division = nice_platform_division( $input['division'] ?? '' );
		$url = nice_platform_valid_destination( $input['destination_url'] ?? '', $division );
		update_post_meta( $id, '_nice_division', $division );
		update_post_meta( $id, '_nice_destination_url', $url );
		update_post_meta( $id, '_nice_destination_path', nice_platform_destination_path( $input['destination_path'] ?? '' ) );
		update_post_meta( $id, '_nice_reference', nice_platform_boolean( $input['reference'] ?? false ) );
		if ( ! empty( $input['destination_url'] ) && ! $url ) {
			add_filter( 'redirect_post_location', static function ( $location ) { return add_query_arg( 'nice_invalid_destination', '1', $location ); } );
		}
	}
}
add_action( 'save_post', 'nice_platform_save_post' );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['nice_invalid_destination'] ) && '1' === $_GET['nice_invalid_destination'] ) {
		echo '<div class="notice notice-warning"><p>The project URL did not match the selected division and was cleared. Check the division and destination before publishing.</p></div>';
	}
} );

add_filter( 'rest_pre_insert_nice_work_preview', function ( $prepared, $request ) {
	$meta = $request->get_param( 'meta' );
	$meta = is_array( $meta ) ? $meta : array();
	$id = absint( $request->get_param( 'id' ) );
	$division = $meta['_nice_division'] ?? get_post_meta( $id, '_nice_division', true );
	$url = $meta['_nice_destination_url'] ?? get_post_meta( $id, '_nice_destination_url', true );
	if ( '' !== $url && ! nice_platform_valid_destination( $url, $division ) ) {
		return new WP_Error( 'nice_invalid_destination', 'Project URL must match the selected division host, protocol and port.', array( 'status' => 400 ) );
	}
	return $prepared;
}, 10, 2 );

add_action( 'admin_init', function () {
	register_setting( 'nice_platform', 'nice_platform_contact', array( 'type' => 'object', 'sanitize_callback' => 'nice_platform_sanitize_contact', 'show_in_rest' => false ) );
} );
add_action( 'admin_menu', function () {
	add_options_page( 'NICE Platform', 'NICE Platform', 'manage_options', 'nice-platform', 'nice_platform_settings_page' );
} );

function nice_platform_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$contact = nice_platform_contact();
	echo '<div class="wrap"><h1>NICE Platform</h1><p>Site role: <strong>' . esc_html( nice_platform_role() ) . '</strong>. Role and site URLs are configured in this installation\'s wp-config.php.</p><ul>';
	foreach ( array( 'main', 'events', 'studio' ) as $role ) {
		echo '<li>' . esc_html( ucfirst( $role ) . ': ' . nice_platform_site_url( $role ) ) . '</li>';
	}
	echo '</ul><h2>Approved contact channels</h2><p>Leave unapproved channels empty. Phone and WhatsApp numbers require a country code; WhatsApp also accepts an https://wa.me/number URL.</p><form method="post" action="options.php">';
	settings_fields( 'nice_platform' );
	foreach ( array( 'email', 'whatsapp', 'phone' ) as $key ) {
		echo '<p><label for="nice-contact-' . esc_attr( $key ) . '">' . esc_html( ucfirst( $key ) ) . '</label><br><input class="regular-text" id="nice-contact-' . esc_attr( $key ) . '" type="' . ( 'email' === $key ? 'email' : 'text' ) . '" name="nice_platform_contact[' . esc_attr( $key ) . ']" value="' . esc_attr( $contact[ $key ] ) . '"></p>';
	}
	echo '<p><label for="nice-social">Social profile URLs (one per line)</label><br><textarea id="nice-social" class="large-text" rows="4" name="nice_platform_contact[social]">' . esc_textarea( implode( "\n", $contact['social'] ) ) . '</textarea></p>';
	submit_button();
	echo '</form></div>';
}
