<?php
/**
 * Native WordPress admin controls for NICE content.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add concise metaboxes for the four NICE content types.
 */
function nice_add_content_meta_boxes() {
	foreach ( array( 'nice_service', 'nice_case_study', 'nice_client', 'nice_team_member' ) as $post_type ) {
		remove_meta_box( 'postcustom', $post_type, 'normal' );
	}

	add_meta_box(
		'nice-service-classification',
		__( 'Service Classification', 'nice-core' ),
		'nice_render_classification_meta_box',
		'nice_service',
		'side',
		'high'
	);
	add_meta_box(
		'nice-case-project-information',
		__( 'Project Information', 'nice-core' ),
		'nice_render_case_study_information_meta_box',
		'nice_case_study',
		'normal',
		'high'
	);
	add_meta_box(
		'nice-case-portfolio-controls',
		__( 'Portfolio Controls', 'nice-core' ),
		'nice_render_portfolio_controls_meta_box',
		'nice_case_study',
		'side',
		'default'
	);
	add_meta_box(
		'nice-client-details',
		__( 'Client Details', 'nice-core' ),
		'nice_render_client_meta_box',
		'nice_client',
		'normal',
		'default'
	);
	add_meta_box(
		'nice-team-details',
		__( 'Team Member Details', 'nice-core' ),
		'nice_render_team_member_meta_box',
		'nice_team_member',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'nice_add_content_meta_boxes' );

/**
 * Render the shared save nonce.
 */
function nice_render_content_meta_nonce() {
	wp_nonce_field( 'nice_save_content_meta', 'nice_content_meta_nonce' );
}

/**
 * Render Division and Service Type selects.
 *
 * @param WP_Post $post Current post.
 */
function nice_render_classification_fields( $post ) {
	$division_terms = wp_get_object_terms( $post->ID, 'nice_division', array( 'fields' => 'slugs' ) );
	$service_terms  = wp_get_object_terms( $post->ID, 'nice_service_type', array( 'fields' => 'slugs' ) );
	$division       = $division_terms[0] ?? '';
	$service_type   = $service_terms[0] ?? '';
	?>
	<p>
		<label for="nice-division"><strong><?php esc_html_e( 'Division', 'nice-core' ); ?></strong></label><br>
		<select class="widefat" id="nice-division" name="nice_division">
			<option value=""><?php esc_html_e( 'Select a division', 'nice-core' ); ?></option>
			<?php foreach ( nice_get_approved_divisions() as $slug => $name ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $division, $slug ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="nice-service-type"><strong><?php esc_html_e( 'Service Type', 'nice-core' ); ?></strong></label><br>
		<select class="widefat" id="nice-service-type" name="nice_service_type">
			<option value=""><?php esc_html_e( 'Select a service type', 'nice-core' ); ?></option>
			<?php foreach ( nice_get_approved_service_types() as $slug => $definition ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $service_type, $slug ); ?>><?php echo esc_html( $definition['name'] . ' - ' . nice_get_approved_divisions()[ $definition['division'] ] ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Service Type controls the valid Division relationship.', 'nice-core' ); ?></p>
	<?php
}

/**
 * Render Service classification.
 *
 * @param WP_Post $post Current Service.
 */
function nice_render_classification_meta_box( $post ) {
	nice_render_content_meta_nonce();
	nice_render_classification_fields( $post );
}

/**
 * Render grouped Case Study project fields.
 *
 * @param WP_Post $post Current Case Study.
 */
function nice_render_case_study_information_meta_box( $post ) {
	nice_render_content_meta_nonce();
	nice_render_classification_fields( $post );

	$client_id    = absint( get_post_meta( $post->ID, '_nice_client_id', true ) );
	$location     = get_post_meta( $post->ID, '_nice_location', true );
	$year         = get_post_meta( $post->ID, '_nice_year', true );
	$reference_url = get_post_meta( $post->ID, '_nice_reference_url', true );
	$clients      = nice_get_clients();
	?>
	<hr>
	<p>
		<label for="nice-client-id"><strong><?php esc_html_e( 'Client', 'nice-core' ); ?></strong></label><br>
		<select class="widefat" id="nice-client-id" name="nice_client_id">
			<option value="0"><?php esc_html_e( 'No linked Client', 'nice-core' ); ?></option>
			<?php foreach ( $clients as $client ) : ?>
				<option value="<?php echo esc_attr( $client->ID ); ?>" <?php selected( $client_id, $client->ID ); ?>><?php echo esc_html( $client->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="nice-location"><strong><?php esc_html_e( 'Location', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="text" id="nice-location" name="nice_location" value="<?php echo esc_attr( $location ); ?>">
	</p>
	<p>
		<label for="nice-year"><strong><?php esc_html_e( 'Year', 'nice-core' ); ?></strong></label><br>
		<input class="small-text" type="number" min="1000" max="9999" id="nice-year" name="nice_year" value="<?php echo esc_attr( $year ?: '' ); ?>">
	</p>
	<p>
		<label for="nice-reference-url"><strong><?php esc_html_e( 'Approved Reference URL', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="url" id="nice-reference-url" name="nice_reference_url" value="<?php echo esc_attr( $reference_url ); ?>" placeholder="https://">
	</p>
	<?php
}

/**
 * Render Case Study portfolio controls.
 *
 * @param WP_Post $post Current Case Study.
 */
function nice_render_portfolio_controls_meta_box( $post ) {
	nice_render_content_meta_nonce();
	$featured     = rest_sanitize_boolean( get_post_meta( $post->ID, '_nice_featured', true ) );
	$display_order = (int) get_post_meta( $post->ID, '_nice_display_order', true );
	?>
	<p>
		<label><input type="checkbox" name="nice_featured" value="1" <?php checked( $featured ); ?>> <?php esc_html_e( 'Featured', 'nice-core' ); ?></label>
	</p>
	<p>
		<label for="nice-display-order"><strong><?php esc_html_e( 'Display Order', 'nice-core' ); ?></strong></label><br>
		<input class="small-text" type="number" id="nice-display-order" name="nice_display_order" value="<?php echo esc_attr( $display_order ); ?>">
	</p>
	<?php
}

/**
 * Render Client fields.
 *
 * @param WP_Post $post Current Client.
 */
function nice_render_client_meta_box( $post ) {
	nice_render_content_meta_nonce();
	$url          = get_post_meta( $post->ID, '_nice_client_url', true );
	$featured     = rest_sanitize_boolean( get_post_meta( $post->ID, '_nice_featured', true ) );
	$display_order = (int) get_post_meta( $post->ID, '_nice_display_order', true );
	?>
	<p>
		<label for="nice-client-url"><strong><?php esc_html_e( 'Approved Website URL', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="url" id="nice-client-url" name="nice_client_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
	</p>
	<p><label><input type="checkbox" name="nice_featured" value="1" <?php checked( $featured ); ?>> <?php esc_html_e( 'Featured', 'nice-core' ); ?></label></p>
	<p>
		<label for="nice-display-order"><strong><?php esc_html_e( 'Display Order', 'nice-core' ); ?></strong></label><br>
		<input class="small-text" type="number" id="nice-display-order" name="nice_display_order" value="<?php echo esc_attr( $display_order ); ?>">
	</p>
	<?php
}

/**
 * Render Team Member fields.
 *
 * @param WP_Post $post Current Team Member.
 */
function nice_render_team_member_meta_box( $post ) {
	nice_render_content_meta_nonce();
	$role           = get_post_meta( $post->ID, '_nice_role', true );
	$display_order  = (int) get_post_meta( $post->ID, '_nice_display_order', true );
	$division_terms = wp_get_object_terms( $post->ID, 'nice_division', array( 'fields' => 'slugs' ) );
	$division       = $division_terms[0] ?? '';
	?>
	<p>
		<label for="nice-role"><strong><?php esc_html_e( 'Role', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="text" id="nice-role" name="nice_role" value="<?php echo esc_attr( $role ); ?>">
	</p>
	<p>
		<label for="nice-division"><strong><?php esc_html_e( 'Division', 'nice-core' ); ?></strong></label><br>
		<select class="widefat" id="nice-division" name="nice_division">
			<option value=""><?php esc_html_e( 'Select a division', 'nice-core' ); ?></option>
			<?php foreach ( nice_get_approved_divisions() as $slug => $name ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $division, $slug ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="nice-display-order"><strong><?php esc_html_e( 'Display Order', 'nice-core' ); ?></strong></label><br>
		<input class="small-text" type="number" id="nice-display-order" name="nice_display_order" value="<?php echo esc_attr( $display_order ); ?>">
	</p>
	<?php
}

/**
 * Save a value or remove empty optional metadata.
 *
 * @param int    $post_id  Post ID.
 * @param string $meta_key Meta key.
 * @param mixed  $value    Sanitized value.
 */
function nice_save_or_delete_meta( $post_id, $meta_key, $value ) {
	if ( '' === $value || null === $value ) {
		delete_post_meta( $post_id, $meta_key );
		return;
	}

	update_post_meta( $post_id, $meta_key, $value );
}

/**
 * Save NICE admin fields with capability and nonce checks.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Current post.
 */
function nice_save_content_meta( $post_id, $post ) {
	if ( ! in_array( $post->post_type, array( 'nice_service', 'nice_case_study', 'nice_client', 'nice_team_member' ), true ) ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( empty( $_POST['nice_content_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nice_content_meta_nonce'] ) ), 'nice_save_content_meta' ) ) {
		return;
	}

	if ( in_array( $post->post_type, array( 'nice_service', 'nice_case_study' ), true ) ) {
		$service_type = sanitize_title( wp_unslash( $_POST['nice_service_type'] ?? '' ) );
		$definitions  = nice_get_approved_service_types();

		if ( isset( $definitions[ $service_type ] ) ) {
			wp_set_object_terms( $post_id, $service_type, 'nice_service_type', false );
			wp_set_object_terms( $post_id, $definitions[ $service_type ]['division'], 'nice_division', false );
		} else {
			wp_set_object_terms( $post_id, array(), 'nice_service_type', false );
			wp_set_object_terms( $post_id, array(), 'nice_division', false );
		}
	}

	if ( 'nice_case_study' === $post->post_type ) {
		$client_id    = nice_sanitize_client_id( wp_unslash( $_POST['nice_client_id'] ?? 0 ) );
		$location     = sanitize_text_field( wp_unslash( $_POST['nice_location'] ?? '' ) );
		$year         = nice_sanitize_year( wp_unslash( $_POST['nice_year'] ?? 0 ) );
		$reference_url = nice_sanitize_https_url( wp_unslash( $_POST['nice_reference_url'] ?? '' ) );

		nice_save_or_delete_meta( $post_id, '_nice_client_id', $client_id ?: '' );
		nice_save_or_delete_meta( $post_id, '_nice_location', $location );
		nice_save_or_delete_meta( $post_id, '_nice_year', $year ?: '' );
		nice_save_or_delete_meta( $post_id, '_nice_reference_url', $reference_url );
		update_post_meta( $post_id, '_nice_featured', empty( $_POST['nice_featured'] ) ? 0 : 1 );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( wp_unslash( $_POST['nice_display_order'] ?? 0 ) ) );
	}

	if ( 'nice_client' === $post->post_type ) {
		$url = nice_sanitize_https_url( wp_unslash( $_POST['nice_client_url'] ?? '' ) );
		nice_save_or_delete_meta( $post_id, '_nice_client_url', $url );
		update_post_meta( $post_id, '_nice_featured', empty( $_POST['nice_featured'] ) ? 0 : 1 );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( wp_unslash( $_POST['nice_display_order'] ?? 0 ) ) );
	}

	if ( 'nice_team_member' === $post->post_type ) {
		$role      = sanitize_text_field( wp_unslash( $_POST['nice_role'] ?? '' ) );
		$division  = sanitize_title( wp_unslash( $_POST['nice_division'] ?? '' ) );
		$divisions = nice_get_approved_divisions();

		nice_save_or_delete_meta( $post_id, '_nice_role', $role );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( wp_unslash( $_POST['nice_display_order'] ?? 0 ) ) );
		wp_set_object_terms( $post_id, isset( $divisions[ $division ] ) ? $division : array(), 'nice_division', false );
	}
}
add_action( 'save_post', 'nice_save_content_meta', 10, 2 );

/**
 * Keep public NICE records from being published without a title.
 *
 * @param array<string, mixed> $data    Sanitized post data.
 * @param array<string, mixed> $postarr Raw post input.
 * @return array<string, mixed>
 */
function nice_require_content_title( $data, $postarr ) {
	$post_types = array( 'nice_service', 'nice_case_study', 'nice_client', 'nice_team_member' );

	if ( in_array( $data['post_type'] ?? '', $post_types, true ) && in_array( $data['post_status'] ?? '', array( 'publish', 'future', 'private' ), true ) && ! trim( $data['post_title'] ?? '' ) ) {
		$data['post_status'] = 'draft';
		set_transient( 'nice_title_required_' . get_current_user_id(), true, MINUTE_IN_SECONDS );
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'nice_require_content_title', 10, 2 );

/**
 * Explain why a titleless record remained a draft.
 */
function nice_render_title_required_notice() {
	$key = 'nice_title_required_' . get_current_user_id();

	if ( ! get_transient( $key ) ) {
		return;
	}

	delete_transient( $key );
	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'A title is required before this NICE record can be published.', 'nice-core' ) . '</p></div>';
}
add_action( 'admin_notices', 'nice_render_title_required_notice' );
