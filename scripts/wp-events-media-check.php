<?php
/**
 * Run only on an approved local test database:
 * wp eval-file scripts/wp-events-media-check.php
 *
 * Temporarily writes hero metadata and creates fixtures, restoring original
 * metadata and user/POST state in finally. No migration or asset import runs.
 */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

function nice_events_media_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$events = get_page_by_path( 'events', OBJECT, 'page' );
$studio = get_page_by_path( 'studio', OBJECT, 'page' );
nice_events_media_assert( $events instanceof WP_Post && nice_is_events_home_page( $events->ID ), 'A top-level Events Page is required.' );
nice_events_media_assert( $studio instanceof WP_Post, 'A Studio Page is required for compatibility checks.' );
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
nice_events_media_assert( ! empty( $admins ), 'An existing administrator is required.' );
$fields = array( 'image_id', 'mobile_image_id', 'focal_x', 'focal_y', 'reference', 'media_initialized' );
$original_user = get_current_user_id();
$original_post = $_POST;
$snapshots = array();
$fixtures = array();
$source_filter = null;
$query_filter = null;
foreach ( array( 'events' => $events, 'studio' => $studio ) as $division => $page ) {
	foreach ( $fields as $field ) {
		$key = '_nice_' . $division . '_hero_' . $field;
		$snapshots[ $page->ID ][ $key ] = metadata_exists( 'post', $page->ID, $key ) ? get_post_meta( $page->ID, $key, false ) : array();
	}
}
$clear_events = static function () use ( $events, $fields ) {
	foreach ( $fields as $field ) {
		delete_post_meta( $events->ID, '_nice_events_hero_' . $field );
	}
};
$values = static function () use ( $events, $fields ) {
	$result = array();
	foreach ( $fields as $field ) {
		$key = '_nice_events_hero_' . $field;
		$result[ $key ] = metadata_exists( 'post', $events->ID, $key ) ? get_post_meta( $events->ID, $key, false ) : array();
	}
	return $result;
};

try {
	wp_set_current_user( (int) $admins[0] );
	$_POST = array();
	$images = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => 2,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		)
	);
	nice_events_media_assert( ! empty( $images ), 'At least one real image attachment is required.' );
	$desktop = (int) $images[0]->ID;
	$mobile  = isset( $images[1] ) ? (int) $images[1]->ID : $desktop;
	$pdf     = wp_insert_post( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_title' => 'Events hero PDF fixture', 'post_mime_type' => 'application/pdf' ), true );
	nice_events_media_assert( ! is_wp_error( $pdf ) && $pdf > 0, 'Could not create the non-image attachment fixture.' );
	$fixtures[] = $pdf;
	$child = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_name' => 'events', 'post_title' => 'Events hero child fixture', 'post_parent' => $events->ID ), true );
	nice_events_media_assert( ! is_wp_error( $child ) && $child > 0, 'Could not create child Page fixture.' );
	$fixtures[] = $child;
	nice_events_media_assert( ! nice_is_events_home_page( $child ), 'Nested Events slugs must not qualify.' );
	$registered = get_registered_meta_keys( 'post', 'page' );
	foreach ( $fields as $field ) {
		$key = '_nice_events_hero_' . $field;
		nice_events_media_assert( isset( $registered[ $key ]['show_in_rest']['schema'] ) && 'nice_authorize_events_hero_meta' === $registered[ $key ]['auth_callback'], 'Missing REST registration/authorization: ' . $key );
	}
	foreach ( array( -1, 0, $pdf, $events->ID, 999999999, 'invalid', array( $desktop ), 1.5 ) as $invalid ) {
		nice_events_media_assert( 0 === nice_sanitize_hero_image_id( $invalid ), 'Invalid image ID accepted.' );
	}
	nice_events_media_assert( $desktop === nice_sanitize_hero_image_id( (string) $desktop ), 'Valid image ID rejected.' );
	nice_events_media_assert( is_wp_error( rest_validate_value_from_schema( array(), $registered['_nice_events_hero_image_id']['show_in_rest']['schema'] ) ), 'REST must reject malformed image values.' );
	$clear_events();
	nice_events_media_assert( 50 === get_post_meta( $events->ID, '_nice_events_hero_focal_x', true ) && 50 === get_post_meta( $events->ID, '_nice_events_hero_focal_y', true ), 'Focal defaults must be 50.' );
	foreach ( array( '', 'invalid', array( 'invalid' ) ) as $nonce ) {
		$_POST = array( 'nice_events_hero_image_id' => $desktop, 'nice_events_hero_media_nonce' => $nonce );
		nice_save_events_hero_media( $events->ID, $events );
		nice_events_media_assert( ! metadata_exists( 'post', $events->ID, '_nice_events_hero_media_initialized' ), 'Invalid nonce allowed a save.' );
	}
	$_POST = array( 'nice_events_hero_media_nonce' => wp_create_nonce( 'nice_save_events_hero_media' ), 'nice_events_hero_image_id' => $desktop, 'nice_events_hero_mobile_image_id' => $mobile, 'nice_events_hero_focal_x' => -9, 'nice_events_hero_focal_y' => 109, 'nice_events_hero_reference' => 1 );
	wp_set_current_user( 0 );
	nice_save_events_hero_media( $events->ID, $events );
	nice_events_media_assert( ! nice_authorize_events_hero_meta( true, '_nice_events_hero_image_id', $events->ID ) && ! metadata_exists( 'post', $events->ID, '_nice_events_hero_image_id' ), 'Unauthorized save allowed.' );
	wp_set_current_user( (int) $admins[0] );
	nice_save_events_hero_media( $child, get_post( $child ) );
	nice_events_media_assert( ! metadata_exists( 'post', $child, '_nice_events_hero_image_id' ), 'Child Page received hero metadata.' );
	nice_save_events_hero_media( $events->ID, $events );
	nice_events_media_assert( $desktop === (int) get_post_meta( $events->ID, '_nice_events_hero_image_id', true ) && $mobile === (int) get_post_meta( $events->ID, '_nice_events_hero_mobile_image_id', true ), 'Native attachment save failed.' );
	nice_events_media_assert( 0 === (int) get_post_meta( $events->ID, '_nice_events_hero_focal_x', true ) && 100 === (int) get_post_meta( $events->ID, '_nice_events_hero_focal_y', true ), 'Focal clamping failed.' );
	$before = $values();
	nice_events_media_assert( 'skipped' === nice_initialize_events_hero_media()['status'] && $before === $values(), 'Initializer overwrote editor selections.' );
	$_POST['nice_events_hero_image_id'] = '';
	$_POST['nice_events_hero_mobile_image_id'] = $pdf;
	unset( $_POST['nice_events_hero_reference'], $_POST['nice_events_hero_focal_x'], $_POST['nice_events_hero_focal_y'] );
	nice_save_events_hero_media( $events->ID, $events );
	nice_events_media_assert( ! metadata_exists( 'post', $events->ID, '_nice_events_hero_image_id' ) && ! metadata_exists( 'post', $events->ID, '_nice_events_hero_mobile_image_id' ), 'Removal or non-image rejection failed.' );
	nice_events_media_assert( ! get_post_meta( $events->ID, '_nice_events_hero_reference', true ) && 50 === (int) get_post_meta( $events->ID, '_nice_events_hero_focal_x', true ), 'Reference clearing or focal default failed.' );
	$before = $values();
	nice_events_media_assert( 'skipped' === nice_initialize_events_hero_media()['status'] && $before === $values(), 'Rerun restored removed media.' );

	// Use the REST metadata writer without changing Page content or timestamps.
	$rest_meta = new WP_REST_Post_Meta_Fields( 'page' );
	$rest_result = $rest_meta->update_value( array( '_nice_events_hero_image_id' => $desktop, '_nice_events_hero_mobile_image_id' => $pdf ), $events->ID );
	nice_events_media_assert( ! is_wp_error( $rest_result ), 'REST save failed: ' . ( is_wp_error( $rest_result ) ? $rest_result->get_error_message() : wp_json_encode( $rest_result ) ) );
	nice_events_media_assert( $desktop === (int) get_post_meta( $events->ID, '_nice_events_hero_image_id', true ) && 0 === (int) get_post_meta( $events->ID, '_nice_events_hero_mobile_image_id', true ), 'REST image validation failed.' );
	nice_events_media_assert( is_wp_error( $rest_meta->update_value( array( '_nice_events_hero_image_id' => $desktop ), $studio->ID ) ), 'REST Events metadata allowed on Studio.' );
	wp_set_current_user( 0 );
	nice_events_media_assert( is_wp_error( $rest_meta->update_value( array( '_nice_events_hero_image_id' => $mobile ), $events->ID ) ), 'REST unauthorized write allowed.' );
	wp_set_current_user( (int) $admins[0] );
	foreach ( array( 0, null ) as $empty ) {
		$clear_events();
		$payload = array( '_nice_events_hero_image_id' => $empty );
		nice_events_media_assert( ! is_wp_error( $rest_meta->update_value( $payload, $events->ID ) ), 'REST empty save failed.' );
		$request = new WP_REST_Request( 'POST', '/wp/v2/pages/' . $events->ID );
		$request->set_param( 'meta', $payload );
		do_action( 'rest_after_insert_page', $events, $request, false );
		nice_events_media_assert( (bool) get_post_meta( $events->ID, '_nice_events_hero_media_initialized', true ) && 'skipped' === nice_initialize_events_hero_media()['status'], 'REST empty save was not preserved.' );
	}
	foreach ( array( 'media_initialized' => false, 'image_id' => 0 ) as $field => $value ) {
		$clear_events();
		update_post_meta( $events->ID, '_nice_events_hero_' . $field, $value );
		nice_events_media_assert( 'skipped' === nice_initialize_events_hero_media()['status'], 'Existing empty/false metadata ignored.' );
	}

	// Intercept only this initializer's source lookup; no files are imported.
	$clear_events();
	$source_filter = static function ( $path, $file ) {
		return 'assets/images/events-reference-hero.webp' === ltrim( $file, '/' ) ? ABSPATH . 'wp-includes/nice-events-test-missing.webp' : $path;
	};
	add_filter( 'theme_file_path', $source_filter, 10, 2 );
	nice_events_media_assert( 'unavailable' === nice_initialize_events_hero_media()['status'] && ! metadata_exists( 'post', $events->ID, '_nice_events_hero_media_initialized' ), 'Missing asset must remain retryable.' );
	remove_filter( 'theme_file_path', $source_filter, 10 );
	$source_filter = static function ( $path, $file ) {
		return 'assets/images/events-reference-hero.webp' === ltrim( $file, '/' ) ? ABSPATH . 'wp-includes/version.php' : $path;
	};
	$query_filter = static function ( $posts, $query ) use ( $desktop ) {
		return 'attachment' === $query->get( 'post_type' ) && '_nice_source_asset' === $query->get( 'meta_key' ) && 'events-reference-hero.webp' === $query->get( 'meta_value' ) ? array( $desktop ) : $posts;
	};
	add_filter( 'theme_file_path', $source_filter, 10, 2 );
	add_filter( 'posts_pre_query', $query_filter, 10, 2 );
	$result = nice_initialize_events_hero_media();
	nice_events_media_assert( 'initialized' === $result['status'] && $desktop === $result['attachment_id'], 'Reference initialization failed.' );
	nice_events_media_assert( 50 === (int) get_post_meta( $events->ID, '_nice_events_hero_focal_x', true ) && 50 === (int) get_post_meta( $events->ID, '_nice_events_hero_focal_y', true ) && get_post_meta( $events->ID, '_nice_events_hero_reference', true ), 'Reference defaults failed.' );
	nice_events_media_assert( ! metadata_exists( 'post', $events->ID, '_nice_events_hero_mobile_image_id' ), 'Initializer must leave mobile empty.' );
	$before = $values();
	nice_events_media_assert( 'skipped' === nice_initialize_events_hero_media()['status'] && $before === $values(), 'Second initialization changed metadata.' );
	ob_start();
	nice_render_studio_hero_meta_box( $studio, array( 'id' => 'nice-studio-hero-media' ) );
	$markup = ob_get_clean();
	nice_events_media_assert( str_contains( $markup, 'name="nice_studio_hero_image_id"' ) && ! str_contains( $markup, 'nice_events_hero_image_id' ), 'Studio callback compatibility failed.' );
	ob_start();
	nice_render_events_hero_meta_box( $events );
	$markup = ob_get_clean();
	nice_events_media_assert( str_contains( $markup, 'name="nice_events_hero_image_id"' ) && str_contains( $markup, 'nice_events_hero_media_nonce' ), 'Events panel fields missing.' );
	$_POST = array( 'nice_studio_hero_media_nonce' => wp_create_nonce( 'nice_save_studio_hero_media' ), 'nice_studio_hero_image_id' => $desktop );
	nice_save_studio_hero_media( $studio->ID, $studio );
	nice_events_media_assert( $desktop === (int) get_post_meta( $studio->ID, '_nice_studio_hero_image_id', true ), 'Studio save regressed.' );
} finally {
	if ( $source_filter ) {
		remove_filter( 'theme_file_path', $source_filter, 10 );
	}
	if ( $query_filter ) {
		remove_filter( 'posts_pre_query', $query_filter, 10 );
	}
	$_POST = array();
	$registered = get_registered_meta_keys( 'post', 'page' );
	foreach ( $snapshots as $post_id => $metadata ) {
		foreach ( $metadata as $key => $old_values ) {
			// Preserve even legacy values that the current sanitizer would normalize.
			$filter   = 'sanitize_post_meta_' . $key . '_for_page';
			$sanitize = $registered[ $key ]['sanitize_callback'];
			remove_filter( $filter, $sanitize, 10 );
			delete_post_meta( $post_id, $key );
			foreach ( $old_values as $value ) {
				add_post_meta( $post_id, $key, wp_slash( $value ) );
			}
			add_filter( $filter, $sanitize, 10, 4 );
		}
	}
	foreach ( array_reverse( $fixtures ) as $id ) {
		if ( 'attachment' === get_post_type( $id ) ) {
			wp_delete_attachment( $id, true );
		} else {
			wp_delete_post( $id, true );
		}
	}
	wp_set_current_user( $original_user );
	$_POST = $original_post;
	foreach ( $snapshots as $post_id => $metadata ) {
		foreach ( $metadata as $key => $old_values ) {
			$restored = metadata_exists( 'post', $post_id, $key ) ? get_post_meta( $post_id, $key, false ) : array();
			nice_events_media_assert( $old_values === $restored, 'Original metadata restoration failed: ' . $key );
		}
	}
}

echo "Events hero assertions passed; original metadata restored.\n";
