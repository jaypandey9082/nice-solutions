<?php
/** Run with wp eval-file on a disposable installation, with NICE Platform Core active. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! function_exists( 'nice_platform_role' ) ) {
	throw new RuntimeException( 'Run using wp eval-file with NICE Platform Core active.' );
}

$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	++$checks;
};
$ids = array();
$old_user = get_current_user_id();
$old_post = $_POST;
try {
	$assert( '' === nice_platform_site_url( 'other' ), 'Reject unknown roles.' );
	$assert( '' === nice_platform_site_url( 'events', '//evil.example/path' ), 'Reject protocol-relative paths.' );
	$assert( '' === nice_platform_destination_path( '/%2e%2e/escape' ), 'Reject encoded traversal.' );
	$assert( '' === nice_platform_absolute_url( 'https://user:pass@events.nicesolutions.in/' ), 'Reject credentials.' );
	$assert( '' === nice_platform_valid_destination( 'https://events.nicesolutions.in.evil.example/work/', 'events' ), 'Reject host suffix tricks.' );
	$assert( '' === nice_platform_valid_destination( 'https://studios.nicesolutions.in/case-studies/a/', 'events' ), 'Reject cross-division URLs.' );
	$assert( '' === nice_platform_valid_destination( 'https://events.nicesolutions.in/%2e%2e/escape', 'events' ), 'Absolute destinations also reject encoded traversal.' );
	$assert( nice_platform_site_url( 'events', '/case-studies/a/' ) === nice_platform_valid_destination( 'https://events.nicesolutions.in/case-studies/a/', 'events' ), 'Map production URLs to the configured environment.' );
	$contact = nice_platform_sanitize_contact( array( 'email' => 'bad@', 'phone' => 'javascript:1', 'whatsapp' => 'https://evil.example/1234567890', 'social' => "javascript:alert(1)\nhttps://example.com/profile" ) );
	$assert( '' === $contact['email'] && '' === $contact['phone'] && '' === $contact['whatsapp'] && array( 'https://example.com/profile' ) === $contact['social'], 'Reject invalid contact channels.' );
	$assert( 'https://wa.me/919876543210' === nice_platform_sanitize_contact( array( 'whatsapp' => '+91 98765 43210' ) )['whatsapp'], 'Normalize valid WhatsApp numbers.' );
	if ( 'main' !== nice_platform_role() ) {
		$assert( ! post_type_exists( 'nice_work_preview' ), 'Division sites do not register main previews.' );
		$assert( array() === nice_platform_get_previews(), 'Division sites do not query main previews.' );
		$assert( is_wp_error( nice_platform_seed_main() ), 'Division sites refuse main seeds.' );
	} else {
		$page = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'CMS temporary contract page' ), true );
		$assert( ! is_wp_error( $page ), 'Create Page fixture.' );
		$ids[] = $page;
		$assert( 'NICE Solutions' === nice_platform_home_content( $page )['hero_title'], 'Missing metadata gets default copy.' );
		update_post_meta( $page, '_nice_home_hero_title', '' );
		update_post_meta( $page, '_nice_media_hero_id', 0 );
		update_post_meta( $page, '_nice_media_hero_x', 0 );
		$assert( '' === nice_platform_home_content( $page )['hero_title'], 'Intentional blank copy persists.' );
		$slot = nice_platform_media_slot( 'hero', $page );
		$assert( 0 === $slot['id'] && 0.0 === (float) $slot['x'] && 50.0 === (float) $slot['y'], 'Blank image and edge focal point persist.' );
		$assert( 100 === nice_platform_focal( 900 ) && 0 === nice_platform_focal( -10 ), 'Clamp focal bounds.' );
		$attachment = wp_insert_attachment( array( 'post_mime_type' => 'image/webp', 'post_title' => 'Temporary media contract fixture', 'post_status' => 'inherit' ), false, $page, true );
		$assert( ! is_wp_error( $attachment ), 'Create attachment metadata fixture without importing media.' );
		$ids[] = $attachment;
		update_post_meta( $attachment, '_wp_attached_file', 'nice-cms-fixture-' . wp_generate_uuid4() . '.webp' );
		update_post_meta( $page, '_nice_media_events_id', $attachment );
		$assert( $attachment === nice_platform_media_slot( 'events', $page )['id'], 'Resolve local image attachment.' );
		update_post_meta( $page, '_nice_media_events_id', 0 );
		$assert( 0 === nice_platform_media_slot( 'events', $page )['id'], 'Clear image without fallback.' );
		$type = get_post_type_object( 'nice_work_preview' );
		$assert( ! $type->publicly_queryable && ! $type->has_archive && $type->show_in_rest, 'Previews are editor-accessible, without public detail routes.' );
		$preview = wp_insert_post( array( 'post_type' => 'nice_work_preview', 'post_status' => 'draft', 'post_title' => 'Temporary preview' ), true );
		$assert( ! is_wp_error( $preview ), 'Create preview fixture.' );
		$ids[] = $preview;
		update_post_meta( $preview, '_nice_division', 'events' );
		update_post_meta( $preview, '_nice_destination_path', '/case-studies/fixture/' );
		update_post_meta( $preview, '_nice_destination_url', 'https://evil.example/' );
		$assert( nice_platform_site_url( 'events', '/case-studies/fixture/' ) === nice_platform_preview_url( $preview ), 'Invalid URL safely falls back to local path.' );
		update_post_meta( $preview, '_nice_destination_path', '' );
		$assert( '' === nice_platform_preview_url( $preview ), 'No destination yields no link.' );
		update_post_meta( $preview, '_nice_destination_url', '' );
		wp_set_current_user( 0 );
		$_POST = array( 'nice_platform_nonce' => wp_create_nonce( 'nice_platform_save' ), 'nice_home' => array( 'hero_title' => 'Unauthorized edit' ) );
		nice_platform_save_post( $page );
		$assert( '' === nice_platform_home_content( $page )['hero_title'], 'Anonymous meta writes are rejected.' );
		$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
		$assert( ! empty( $admins ), 'A fixture administrator is available.' );
		wp_set_current_user( $admins[0]->ID );
		$_POST = array( 'nice_platform_nonce' => 'invalid', 'nice_home' => array( 'hero_title' => 'Bad nonce' ) );
		nice_platform_save_post( $page );
		$assert( '' === nice_platform_home_content( $page )['hero_title'], 'Invalid nonces are rejected.' );
		$_POST = array( 'nice_platform_nonce' => wp_create_nonce( 'nice_platform_save' ), 'nice_home' => array( 'hero_title' => '<b>Clean title</b>' ), 'nice_media' => array( 'hero' => array( 'id' => 0, 'mobile_id' => 0, 'x' => 25, 'y' => 75 ) ) );
		nice_platform_save_post( $page );
		$assert( 'Clean title' === nice_platform_home_content( $page )['hero_title'], 'Authorized saves sanitize plain text.' );
		$assert( false === nice_platform_media_slot( 'hero', $page )['reference'], 'Unchecked reference checkbox saves false.' );
		$_POST = array();
		$request = new WP_REST_Request( 'POST', '/wp/v2/nice_work_preview/' . $preview );
		$request->set_param( 'meta', array( '_nice_destination_url' => 'https://evil.example/project', '_nice_division' => 'events' ) );
		$response = rest_get_server()->dispatch( $request );
		$assert( 400 === $response->get_status(), 'REST rejects off-host project destinations.' );
		$request->set_param( 'meta', array( '_nice_destination_url' => '', '_nice_destination_path' => '/case-studies/rest-fixture/', '_nice_division' => 'studio' ) );
		$response = rest_get_server()->dispatch( $request );
		$assert( 200 === $response->get_status(), 'REST accepts editable local metadata.' );
		$assert( nice_platform_site_url( 'studio', '/case-studies/rest-fixture/' ) === nice_platform_preview_url( $preview ), 'REST update resolves the correct independent site.' );
		wp_set_current_user( 0 );
		$request->set_param( 'meta', array( '_nice_destination_path' => '/case-studies/unauthorized/' ) );
		$assert( 401 === rest_get_server()->dispatch( $request )->get_status(), 'REST blocks anonymous metadata updates.' );
		wp_set_current_user( $admins[0]->ID );
		for ( $index = 0; $index < 4; ++$index ) {
			$id = wp_insert_post( array( 'post_type' => 'nice_work_preview', 'post_status' => 'publish', 'post_title' => 'Temporary ordered preview ' . $index, 'menu_order' => $index - 10000 ), true );
			$assert( ! is_wp_error( $id ), 'Create ordered preview fixture.' );
			$ids[] = $id;
		}
		$published = nice_platform_get_previews();
		$assert( 3 === count( $published ), 'Gateway query is capped at three.' );
		$assert( ! in_array( $preview, wp_list_pluck( $published, 'ID' ), true ), 'Draft preview is excluded.' );
		$assert( $published[0]->menu_order <= $published[1]->menu_order && $published[1]->menu_order <= $published[2]->menu_order, 'Query sorts by menu order ascending.' );
	}
} finally {
	$_POST = $old_post;
	wp_set_current_user( $old_user );
	foreach ( array_reverse( $ids ) as $id ) {
		if ( 'attachment' === get_post_type( $id ) ) {
			wp_delete_attachment( $id, true );
		} else {
			wp_delete_post( $id, true );
		}
	}
}
WP_CLI::success( $checks . ' CMS contract checks passed for role ' . nice_platform_role() . '.' );
