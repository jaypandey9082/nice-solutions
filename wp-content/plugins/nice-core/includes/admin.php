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
		'nice-case-content-source',
		__( 'Content Source & Approval', 'nice-core' ),
		'nice_render_case_study_source_meta_box',
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

	if ( function_exists( 'nice_site_owns_gateway_projects' ) && nice_site_owns_gateway_projects() ) {
		remove_meta_box( 'postcustom', 'nice_gateway_project', 'normal' );
		add_meta_box(
			'nice-gateway-project-details',
			__( 'Gateway Preview Details', 'nice-core' ),
			'nice_render_gateway_project_meta_box',
			'nice_gateway_project',
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'nice_add_content_meta_boxes' );

/**
 * Add Studio hero controls only to the Studio Home Page editor.
 *
 * @param WP_Post $post Current Page.
 */
function nice_add_studio_hero_meta_box( $post ) {
	if ( ! $post instanceof WP_Post || ! nice_is_studio_home_page( $post->ID ) ) {
		return;
	}

	add_meta_box(
		'nice-studio-hero-media',
		__( 'Studio Hero Media', 'nice-core' ),
		'nice_render_studio_hero_meta_box',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_page', 'nice_add_studio_hero_meta_box' );

/**
 * Add Events hero controls to the top-level Events Page only.
 *
 * @param WP_Post $post Current Page.
 */
function nice_add_events_hero_meta_box( $post ) {
	if ( $post instanceof WP_Post && nice_is_events_home_page( $post->ID ) ) {
		add_meta_box( 'nice-events-hero-media', __( 'Events Hero Media', 'nice-core' ), 'nice_render_events_hero_meta_box', 'page', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes_page', 'nice_add_events_hero_meta_box' );

/**
 * Render Events controls using the shared native hero panel.
 *
 * @param WP_Post $post Events Page.
 */
function nice_render_events_hero_meta_box( $post ) {
	nice_render_studio_hero_meta_box( $post, 'events' );
}

/**
 * Render one Studio hero image selector.
 *
 * @param string $field_name   Form field name.
 * @param int    $attachment_id Selected attachment ID.
 * @param string $label        Field label.
 * @param string $guidance     Crop guidance.
 */
function nice_render_studio_media_control( $field_name, $attachment_id, $label, $guidance ) {
	$image_src = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	$image_alt = $attachment_id ? get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) : '';
	?>
	<div class="nice-studio-media-control" data-nice-media-control data-empty="<?php echo $image_src ? 'false' : 'true'; ?>">
		<h4><?php echo esc_html( $label ); ?></h4>
		<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>" data-nice-media-id>
		<div class="nice-studio-media-preview" data-nice-media-preview>
			<?php if ( $image_src ) : ?>
				<img src="<?php echo esc_url( $image_src ); ?>" alt="">
			<?php else : ?>
				<span><?php esc_html_e( 'No image selected', 'nice-core' ); ?></span>
			<?php endif; ?>
		</div>
		<p>
			<button type="button" class="button button-secondary" data-nice-media-select data-select-label="<?php esc_attr_e( 'Select image', 'nice-core' ); ?>" data-replace-label="<?php esc_attr_e( 'Replace image', 'nice-core' ); ?>">
				<?php echo $image_src ? esc_html__( 'Replace image', 'nice-core' ) : esc_html__( 'Select image', 'nice-core' ); ?>
			</button>
			<button type="button" class="button-link-delete" data-nice-media-remove<?php echo $image_src ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'nice-core' ); ?></button>
		</p>
		<p class="description"><?php echo esc_html( $guidance ); ?></p>
		<p class="description" data-nice-media-alt>
			<?php
			if ( $image_src && $image_alt ) {
				echo esc_html__( 'Attachment alt text is set.', 'nice-core' );
			} elseif ( $image_src ) {
				echo esc_html__( 'Add meaningful alt text in the Media Library before publishing.', 'nice-core' );
			} else {
				echo esc_html__( 'Alt text is managed in the WordPress Media Library.', 'nice-core' );
			}
			?>
		</p>
	</div>
	<?php
}

/**
 * Render the Studio Home hero media panel.
 *
 * @param WP_Post $post Studio Home Page.
 * @param string  $division Hero division; Studio remains the default.
 */
function nice_render_studio_hero_meta_box( $post, $division = 'studio' ) {
	// WordPress passes the metabox array as the second callback argument.
	$division     = 'events' === $division ? 'events' : 'studio';
	$field_prefix = 'nice_' . $division . '_hero_';
	$id_prefix    = 'nice-' . $division . '-hero-focal-';
	$meta_prefix  = '_' . $field_prefix;
	$desktop_id   = absint( get_post_meta( $post->ID, $meta_prefix . 'image_id', true ) );
	$mobile_id    = absint( get_post_meta( $post->ID, $meta_prefix . 'mobile_image_id', true ) );
	$focal_x      = metadata_exists( 'post', $post->ID, $meta_prefix . 'focal_x' ) ? nice_sanitize_percentage( get_post_meta( $post->ID, $meta_prefix . 'focal_x', true ) ) : 50;
	$focal_y      = metadata_exists( 'post', $post->ID, $meta_prefix . 'focal_y' ) ? nice_sanitize_percentage( get_post_meta( $post->ID, $meta_prefix . 'focal_y', true ) ) : 50;
	$is_reference = rest_sanitize_boolean( get_post_meta( $post->ID, $meta_prefix . 'reference', true ) );

	wp_nonce_field( 'nice_save_' . $division . '_hero_media', $field_prefix . 'media_nonce' );
	?>
	<div class="nice-studio-media-grid">
		<?php
		nice_render_studio_media_control(
			$field_prefix . 'image_id',
			$desktop_id,
			__( 'Desktop hero', 'nice-core' ),
			__( 'Use a wide cinematic image. Recommended crop: 16:9 or wider, at least 1920px across.', 'nice-core' )
		);
		nice_render_studio_media_control(
			$field_prefix . 'mobile_image_id',
			$mobile_id,
			__( 'Mobile hero (optional)', 'nice-core' ),
			__( 'Use a portrait-aware crop around 4:5. When empty, the desktop image is used.', 'nice-core' )
		);
		?>
	</div>
	<div class="nice-studio-focal-controls">
		<h4><?php esc_html_e( 'Focal position', 'nice-core' ); ?></h4>
		<p class="description"><?php esc_html_e( 'Keep the important subject visible when the hero image is cropped.', 'nice-core' ); ?></p>
		<label for="<?php echo esc_attr( $id_prefix . 'x' ); ?>">
			<strong><?php esc_html_e( 'Horizontal', 'nice-core' ); ?></strong>
			<input type="range" min="0" max="100" step="1" id="<?php echo esc_attr( $id_prefix . 'x' ); ?>" name="<?php echo esc_attr( $field_prefix . 'focal_x' ); ?>" value="<?php echo esc_attr( $focal_x ); ?>" data-nice-focal>
			<output for="<?php echo esc_attr( $id_prefix . 'x' ); ?>"><?php echo esc_html( $focal_x ); ?>%</output>
		</label>
		<label for="<?php echo esc_attr( $id_prefix . 'y' ); ?>">
			<strong><?php esc_html_e( 'Vertical', 'nice-core' ); ?></strong>
			<input type="range" min="0" max="100" step="1" id="<?php echo esc_attr( $id_prefix . 'y' ); ?>" name="<?php echo esc_attr( $field_prefix . 'focal_y' ); ?>" value="<?php echo esc_attr( $focal_y ); ?>" data-nice-focal>
			<output for="<?php echo esc_attr( $id_prefix . 'y' ); ?>"><?php echo esc_html( $focal_y ); ?>%</output>
		</label>
	</div>
	<p>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $field_prefix . 'reference' ); ?>" value="1" <?php checked( $is_reference ); ?>>
			<?php esc_html_e( 'Mark this as temporary reference imagery', 'nice-core' ); ?>
		</label>
	</p>
	<p class="description"><?php esc_html_e( 'Reference imagery is identified on the public page and must not be presented as evidence of a NICE project.', 'nice-core' ); ?></p>
	<?php
}

/**
 * Load shared native media controls on the Studio and Events home editors.
 *
 * @param string $hook_suffix Current admin screen hook.
 */
function nice_enqueue_studio_hero_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
	if ( ! $post_id || ( ! nice_is_studio_home_page( $post_id ) && ! nice_is_events_home_page( $post_id ) ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style(
		'nice-core-studio-media',
		plugins_url( 'assets/admin-studio-media.css', NICE_CORE_FILE ),
		array(),
		NICE_CORE_VERSION
	);
	wp_enqueue_script(
		'nice-core-studio-media',
		plugins_url( 'assets/admin-studio-media.js', NICE_CORE_FILE ),
		array( 'media-editor' ),
		NICE_CORE_VERSION,
		true
	);
	wp_localize_script(
		'nice-core-studio-media',
		'niceStudioMedia',
		array(
			'emptyLabel'      => __( 'No image selected', 'nice-core' ),
			'frameTitle'      => nice_is_events_home_page( $post_id ) ? __( 'Choose Events hero image', 'nice-core' ) : __( 'Choose Studio hero image', 'nice-core' ),
			'frameButton'     => __( 'Use this image', 'nice-core' ),
			'altManaged'      => __( 'Alt text is managed in the WordPress Media Library.', 'nice-core' ),
			'altPresent'      => __( 'Attachment alt text is set.', 'nice-core' ),
			'altMissing'      => __( 'Add meaningful alt text in the Media Library before publishing.', 'nice-core' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'nice_enqueue_studio_hero_admin_assets' );

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
	$proof_value   = get_post_meta( $post->ID, '_nice_proof_value', true );
	$proof_label   = get_post_meta( $post->ID, '_nice_proof_label', true );
	?>
	<p>
		<label><input type="checkbox" name="nice_featured" value="1" <?php checked( $featured ); ?>> <?php esc_html_e( 'Featured', 'nice-core' ); ?></label>
	</p>
	<p>
		<label for="nice-display-order"><strong><?php esc_html_e( 'Display Order', 'nice-core' ); ?></strong></label><br>
		<input class="small-text" type="number" id="nice-display-order" name="nice_display_order" value="<?php echo esc_attr( $display_order ); ?>">
	</p>
	<hr>
	<p>
		<label for="nice-proof-value"><strong><?php esc_html_e( 'Project Proof Value', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="text" id="nice-proof-value" name="nice_proof_value" value="<?php echo esc_attr( $proof_value ); ?>" placeholder="2000+">
	</p>
	<p>
		<label for="nice-proof-label"><strong><?php esc_html_e( 'Project Proof Context', 'nice-core' ); ?></strong></label><br>
		<textarea class="widefat" rows="3" id="nice-proof-label" name="nice_proof_label"><?php echo esc_textarea( $proof_label ); ?></textarea>
	</p>
	<p class="description"><?php esc_html_e( 'Use only a source-approved metric specific to this project.', 'nice-core' ); ?></p>
	<?php
}

/**
 * Render private source provenance and editorial approval controls.
 *
 * @param WP_Post $post Current Case Study.
 */
function nice_render_case_study_source_meta_box( $post ) {
	nice_render_content_meta_nonce();

	$source_url      = get_post_meta( $post->ID, '_nice_source_url', true );
	$source_note     = get_post_meta( $post->ID, '_nice_source_note', true );
	$approval_status = nice_sanitize_case_study_approval_status( get_post_meta( $post->ID, '_nice_source_approval_status', true ) );
	$media_approved  = rest_sanitize_boolean( get_post_meta( $post->ID, '_nice_media_approved', true ) );
	$source_origin   = (string) get_post_meta( $post->ID, '_nice_source_origin', true );
	$source_problem  = function_exists( 'nice_get_source_url_problem' ) ? nice_get_source_url_problem( $source_url, $source_origin ) : '';
	?>
	<p class="description"><?php esc_html_e( 'Private editorial fields. They are not exposed through the public REST API.', 'nice-core' ); ?></p>
	<p>
		<label for="nice-source-url"><strong><?php esc_html_e( 'Source URL', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="url" id="nice-source-url" name="nice_source_url" value="<?php echo esc_attr( $source_url ); ?>" placeholder="https://">
	</p>
	<p>
		<label for="nice-source-note"><strong><?php esc_html_e( 'Source Label / Note', 'nice-core' ); ?></strong></label><br>
		<textarea class="widefat" rows="4" id="nice-source-note" name="nice_source_note"><?php echo esc_textarea( $source_note ); ?></textarea>
	</p>
	<p>
		<label for="nice-source-approval-status"><strong><?php esc_html_e( 'Approval Status', 'nice-core' ); ?></strong></label><br>
		<select class="widefat" id="nice-source-approval-status" name="nice_source_approval_status">
			<option value="draft" <?php selected( $approval_status, 'draft' ); ?>><?php esc_html_e( 'Draft - source captured', 'nice-core' ); ?></option>
			<option value="review" <?php selected( $approval_status, 'review' ); ?>><?php esc_html_e( 'Review - awaiting approval', 'nice-core' ); ?></option>
			<option value="approved" <?php selected( $approval_status, 'approved' ); ?>><?php esc_html_e( 'Approved - cleared for editorial use', 'nice-core' ); ?></option>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Approval here records editorial clearance of the wording and its source. It does not clear the image.', 'nice-core' ); ?></p>
	<?php if ( $source_problem ) : ?>
		<p class="description"><strong><?php
			/* translators: %s: reason the source URL cannot support approval. */
			echo esc_html( sprintf( __( 'Approval is blocked. %s', 'nice-core' ), $source_problem ) );
		?></strong></p>
	<?php endif; ?>
	<p>
		<label for="nice-media-approved">
			<input type="checkbox" id="nice-media-approved" name="nice_media_approved" value="1" <?php checked( $media_approved ); ?>>
			<strong><?php esc_html_e( 'Media cleared for publication', 'nice-core' ); ?></strong>
		</label>
	</p>
	<p class="description"><?php esc_html_e( 'Separate from the approval above. Tick this only when NICE holds the right to publish the featured image on this record. While it is unticked the project page shows an intentional placeholder instead.', 'nice-core' ); ?></p>
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
	$linkedin       = get_post_meta( $post->ID, '_nice_linkedin_url', true );
	$instagram      = get_post_meta( $post->ID, '_nice_instagram_url', true );
	$public_email   = get_post_meta( $post->ID, '_nice_public_email', true );
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
	<p class="description"><?php esc_html_e( 'Anything entered below is published on the division About page. Leave a field empty to omit that link.', 'nice-core' ); ?></p>
	<p>
		<label for="nice-linkedin-url"><strong><?php esc_html_e( 'LinkedIn Profile URL', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="url" id="nice-linkedin-url" name="nice_linkedin_url" value="<?php echo esc_attr( $linkedin ); ?>" placeholder="https://">
	</p>
	<p>
		<label for="nice-instagram-url"><strong><?php esc_html_e( 'Instagram Profile URL', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="url" id="nice-instagram-url" name="nice_instagram_url" value="<?php echo esc_attr( $instagram ); ?>" placeholder="https://">
	</p>
	<p>
		<label for="nice-public-email"><strong><?php esc_html_e( 'Public Email Address', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="email" id="nice-public-email" name="nice_public_email" value="<?php echo esc_attr( $public_email ); ?>">
	</p>
	<?php
}

/**
 * Render Gateway Project fields.
 *
 * @param WP_Post $post Current Gateway Project.
 */
function nice_render_gateway_project_meta_box( $post ) {
	nice_render_content_meta_nonce();

	$summary       = (string) get_post_meta( $post->ID, '_nice_gateway_summary', true );
	$division      = sanitize_key( (string) get_post_meta( $post->ID, '_nice_gateway_division', true ) );
	$destination   = (string) get_post_meta( $post->ID, '_nice_gateway_destination_url', true );
	$display_order = (int) get_post_meta( $post->ID, '_nice_display_order', true );
	$media_approved = rest_sanitize_boolean( get_post_meta( $post->ID, '_nice_media_approved', true ) );
	$problem       = nice_get_gateway_destination_problem( $destination, $division );
	?>
	<p class="description"><?php esc_html_e( 'This preview appears on the gateway home page and sends the reader to the installation that owns the work. It publishes nothing by itself.', 'nice-core' ); ?></p>
	<p>
		<label for="nice-gateway-summary"><strong><?php esc_html_e( 'Summary', 'nice-core' ); ?></strong></label><br>
		<textarea class="widefat" rows="3" id="nice-gateway-summary" name="nice_gateway_summary"><?php echo esc_textarea( $summary ); ?></textarea>
	</p>
	<p>
		<label for="nice-gateway-division"><strong><?php esc_html_e( 'Division', 'nice-core' ); ?></strong></label><br>
		<select id="nice-gateway-division" name="nice_gateway_division">
			<option value=""><?php esc_html_e( 'Select a division', 'nice-core' ); ?></option>
			<?php foreach ( nice_get_approved_divisions() as $slug => $name ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $division, $slug ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="nice-gateway-destination"><strong><?php esc_html_e( 'Destination URL', 'nice-core' ); ?></strong></label><br>
		<input class="widefat" type="url" id="nice-gateway-destination" name="nice_gateway_destination_url" value="<?php echo esc_attr( $destination ); ?>">
	</p>
	<?php if ( $problem ) : ?>
		<p class="description"><strong><?php echo esc_html( $problem ); ?></strong></p>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'Only a case study on the selected division can be stored here.', 'nice-core' ); ?></p>
	<?php endif; ?>
	<p>
		<label for="nice-display-order"><strong><?php esc_html_e( 'Display Order', 'nice-core' ); ?></strong></label><br>
		<input class="small-text" type="number" id="nice-display-order" name="nice_display_order" value="<?php echo esc_attr( $display_order ); ?>">
	</p>
	<p>
		<label for="nice-gateway-media-approved">
			<input type="checkbox" id="nice-gateway-media-approved" name="nice_media_approved" value="1" <?php checked( $media_approved ); ?>>
			<strong><?php esc_html_e( 'Media cleared for publication', 'nice-core' ); ?></strong>
		</label>
	</p>
	<p class="description"><?php esc_html_e( 'Tick this only when NICE holds the right to publish the featured image. Until then the preview renders without it.', 'nice-core' ); ?></p>
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
	if ( ! in_array( $post->post_type, array( 'nice_service', 'nice_case_study', 'nice_client', 'nice_team_member', 'nice_gateway_project' ), true ) ) {
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
		$proof_value   = sanitize_text_field( wp_unslash( $_POST['nice_proof_value'] ?? '' ) );
		$proof_label   = sanitize_text_field( wp_unslash( $_POST['nice_proof_label'] ?? '' ) );
		$source_url    = nice_sanitize_https_url( wp_unslash( $_POST['nice_source_url'] ?? '' ) );
		$source_note   = sanitize_textarea_field( wp_unslash( $_POST['nice_source_note'] ?? '' ) );
		$approval_status = nice_sanitize_case_study_approval_status( wp_unslash( $_POST['nice_source_approval_status'] ?? 'draft' ) );

		nice_save_or_delete_meta( $post_id, '_nice_client_id', $client_id ?: '' );
		nice_save_or_delete_meta( $post_id, '_nice_location', $location );
		nice_save_or_delete_meta( $post_id, '_nice_year', $year ?: '' );
		nice_save_or_delete_meta( $post_id, '_nice_reference_url', $reference_url );
		nice_save_or_delete_meta( $post_id, '_nice_proof_value', $proof_value );
		nice_save_or_delete_meta( $post_id, '_nice_proof_label', $proof_label );
		nice_save_or_delete_meta( $post_id, '_nice_source_url', $source_url );
		nice_save_or_delete_meta( $post_id, '_nice_source_note', $source_note );
		/*
		 * Seeded candidates carry the LinkedIn company feed, which does not say
		 * which post they came from. Approving on that basis clears wording nobody
		 * can trace, so hold the record at review until a specific post URL is in.
		 */
		if ( 'approved' === $approval_status
			&& function_exists( 'nice_get_source_url_problem' ) ) {
			$source_problem = nice_get_source_url_problem( $source_url, get_post_meta( $post_id, '_nice_source_origin', true ) );

			if ( $source_problem ) {
				$approval_status = 'review';
				set_transient( 'nice_source_approval_blocked_' . $post_id, $source_problem, 60 );
			}
		}

		update_post_meta( $post_id, '_nice_source_approval_status', $approval_status );
		update_post_meta( $post_id, '_nice_media_approved', empty( $_POST['nice_media_approved'] ) ? 0 : 1 );
		update_post_meta( $post_id, '_nice_featured', empty( $_POST['nice_featured'] ) ? 0 : 1 );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( wp_unslash( $_POST['nice_display_order'] ?? 0 ) ) );
	}

	if ( 'nice_client' === $post->post_type ) {
		$url = nice_sanitize_https_url( wp_unslash( $_POST['nice_client_url'] ?? '' ) );
		nice_save_or_delete_meta( $post_id, '_nice_client_url', $url );
		update_post_meta( $post_id, '_nice_featured', empty( $_POST['nice_featured'] ) ? 0 : 1 );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( wp_unslash( $_POST['nice_display_order'] ?? 0 ) ) );
	}

	if ( 'nice_gateway_project' === $post->post_type ) {
		$division  = sanitize_key( wp_unslash( $_POST['nice_gateway_division'] ?? '' ) );
		$divisions = nice_get_approved_divisions();

		nice_save_or_delete_meta( $post_id, '_nice_gateway_summary', sanitize_textarea_field( wp_unslash( $_POST['nice_gateway_summary'] ?? '' ) ) );
		nice_save_or_delete_meta( $post_id, '_nice_gateway_division', isset( $divisions[ $division ] ) ? $division : '' );
		/*
		 * The meta sanitizer drops a destination outside the division's case
		 * studies, so an unusable address is stored as nothing rather than kept
		 * and quietly ignored at render time.
		 */
		nice_save_or_delete_meta( $post_id, '_nice_gateway_destination_url', nice_sanitize_gateway_destination_url( wp_unslash( $_POST['nice_gateway_destination_url'] ?? '' ) ) );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( wp_unslash( $_POST['nice_display_order'] ?? 0 ) ) );
		update_post_meta( $post_id, '_nice_media_approved', empty( $_POST['nice_media_approved'] ) ? 0 : 1 );
	}

	if ( 'nice_team_member' === $post->post_type ) {
		$role      = sanitize_text_field( wp_unslash( $_POST['nice_role'] ?? '' ) );
		$division  = sanitize_title( wp_unslash( $_POST['nice_division'] ?? '' ) );
		$divisions = nice_get_approved_divisions();

		nice_save_or_delete_meta( $post_id, '_nice_role', $role );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( wp_unslash( $_POST['nice_display_order'] ?? 0 ) ) );

		/*
		 * Each contact point is stored or deleted, never stored empty: the
		 * About page decides whether to draw a link by asking whether the meta
		 * exists, so an empty string left behind would render a dead icon.
		 */
		nice_save_or_delete_meta( $post_id, '_nice_linkedin_url', nice_sanitize_https_url( wp_unslash( $_POST['nice_linkedin_url'] ?? '' ) ) );
		nice_save_or_delete_meta( $post_id, '_nice_instagram_url', nice_sanitize_https_url( wp_unslash( $_POST['nice_instagram_url'] ?? '' ) ) );
		nice_save_or_delete_meta( $post_id, '_nice_public_email', sanitize_email( wp_unslash( $_POST['nice_public_email'] ?? '' ) ) );

		wp_set_object_terms( $post_id, isset( $divisions[ $division ] ) ? $division : array(), 'nice_division', false );
	}
}
add_action( 'save_post', 'nice_save_content_meta', 10, 2 );

/**
 * Save Studio Home hero media with explicit capability and nonce checks.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Current Page.
 */
function nice_save_studio_hero_media( $post_id, $post ) {
	if ( ! $post instanceof WP_Post || ! nice_is_studio_home_page( $post_id ) ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( empty( $_POST['nice_studio_hero_media_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nice_studio_hero_media_nonce'] ) ), 'nice_save_studio_hero_media' ) ) {
		return;
	}

	$attachment_fields = array(
		'nice_studio_hero_image_id'        => '_nice_studio_hero_image_id',
		'nice_studio_hero_mobile_image_id' => '_nice_studio_hero_mobile_image_id',
	);

	foreach ( $attachment_fields as $field_name => $meta_key ) {
		$attachment_id = absint( wp_unslash( $_POST[ $field_name ] ?? 0 ) );
		$is_image      = $attachment_id
			&& 'attachment' === get_post_type( $attachment_id )
			&& wp_attachment_is_image( $attachment_id );

		if ( $is_image ) {
			update_post_meta( $post_id, $meta_key, $attachment_id );
		} else {
			delete_post_meta( $post_id, $meta_key );
		}
	}

	update_post_meta( $post_id, '_nice_studio_hero_focal_x', nice_sanitize_percentage( wp_unslash( $_POST['nice_studio_hero_focal_x'] ?? 50 ) ) );
	update_post_meta( $post_id, '_nice_studio_hero_focal_y', nice_sanitize_percentage( wp_unslash( $_POST['nice_studio_hero_focal_y'] ?? 50 ) ) );
	update_post_meta( $post_id, '_nice_studio_hero_reference', empty( $_POST['nice_studio_hero_reference'] ) ? 0 : 1 );
	update_post_meta( $post_id, '_nice_studio_hero_media_initialized', 1 );
}
add_action( 'save_post_page', 'nice_save_studio_hero_media', 10, 2 );

/**
 * Save Events hero controls after validating the target, nonce and capability.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post Current Page.
 */
function nice_save_events_hero_media( $post_id, $post ) {
	if ( ! $post instanceof WP_Post || (int) $post->ID !== (int) $post_id || ! nice_is_events_home_page( $post_id ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$nonce = $_POST['nice_events_hero_media_nonce'] ?? '';
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'nice_save_events_hero_media' ) ) {
		return;
	}

	foreach ( array( 'image_id', 'mobile_image_id' ) as $field ) {
		$id = nice_sanitize_hero_image_id( wp_unslash( $_POST[ 'nice_events_hero_' . $field ] ?? 0 ) );
		if ( $id ) {
			update_post_meta( $post_id, '_nice_events_hero_' . $field, $id );
		} else {
			delete_post_meta( $post_id, '_nice_events_hero_' . $field );
		}
	}
	foreach ( array( 'focal_x', 'focal_y' ) as $field ) {
		update_post_meta( $post_id, '_nice_events_hero_' . $field, nice_sanitize_percentage( wp_unslash( $_POST[ 'nice_events_hero_' . $field ] ?? 50 ) ) );
	}
	update_post_meta( $post_id, '_nice_events_hero_reference', empty( $_POST['nice_events_hero_reference'] ) ? 0 : 1 );
	update_post_meta( $post_id, '_nice_events_hero_media_initialized', 1 );
}
add_action( 'save_post_page', 'nice_save_events_hero_media', 10, 2 );

/**
 * Record explicit REST edits, including no-op zero/null removals of empty media.
 * WordPress REST handles request authentication and registered-meta capabilities.
 *
 * @param WP_Post         $post Saved Page.
 * @param WP_REST_Request $request REST request.
 */
function nice_mark_events_hero_rest_initialized( $post, $request ) {
	if ( ! nice_is_events_home_page( $post->ID ) || ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}
	$meta = $request->get_param( 'meta' );
	if ( ! is_array( $meta ) ) {
		return;
	}
	foreach ( array( 'image_id', 'mobile_image_id', 'focal_x', 'focal_y', 'reference', 'media_initialized' ) as $field ) {
		if ( array_key_exists( '_nice_events_hero_' . $field, $meta ) ) {
			update_post_meta( $post->ID, '_nice_events_hero_media_initialized', 1 );
			return;
		}
	}
}
add_action( 'rest_after_insert_page', 'nice_mark_events_hero_rest_initialized', 10, 2 );

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

/**
 * Explain why a source approval was held back at review.
 */
function nice_render_source_approval_blocked_notice() {
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice.
	$reason  = $post_id ? get_transient( 'nice_source_approval_blocked_' . $post_id ) : '';

	if ( ! $reason ) {
		return;
	}

	delete_transient( 'nice_source_approval_blocked_' . $post_id );
	printf(
		'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
		esc_html( sprintf(
			/* translators: %s: reason the source URL cannot support approval. */
			__( 'This record was held at Review. %s', 'nice-core' ),
			is_string( $reason ) ? $reason : __( 'Its Source URL does not identify the post the wording came from.', 'nice-core' )
		) )
	);
}
add_action( 'admin_notices', 'nice_render_source_approval_blocked_notice' );
