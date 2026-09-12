<?php
/** Run only on an empty disposable main installation: wp eval-file platform/tests/cms-seed.php */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! function_exists( 'nice_platform_seed_main' ) || 'main' !== nice_platform_role() ) {
	throw new RuntimeException( 'Run using wp eval-file on a disposable main installation with the plugin active.' );
}
if ( get_posts( array( 'post_type' => array( 'page', 'nice_work_preview' ), 'post_status' => array_values( get_post_stati() ), 'posts_per_page' => 1 ) ) ) {
	throw new RuntimeException( 'Refusing to seed-test a database containing Pages or previews.' );
}
$old_front = get_option( 'page_on_front' );
$old_show = get_option( 'show_on_front' );
$checks = 0;
$assert = static function ( $value, $message ) use ( &$checks ) {
	if ( ! $value ) { throw new RuntimeException( $message ); }
	++$checks;
};
try {
	$first = nice_platform_seed_main();
	$assert( ! is_wp_error( $first ), 'First seed succeeds.' );
	$assert( 3 === count( nice_platform_get_previews() ), 'Exactly three published previews.' );
	$assert( array( 'Voltas Fam-Tastic Fiesta', 'Strata Geosystems Factory Shoot', 'GCA 2025' ) === wp_list_pluck( nice_platform_get_previews(), 'post_title' ), 'Preview order and titles match approved selection.' );
	$assert( 6 === count( nice_platform_client_names() ), 'Approved client proof is available.' );
	foreach ( $first['preview_ids'] as $id ) {
		$assert( ! get_post_thumbnail_id( $id ) && nice_platform_preview_url( $id ), 'Preview has a division URL and no image.' );
	}
	update_post_meta( $first['home_id'], '_nice_home_hero_title', '' );
	update_post_meta( $first['home_id'], '_nice_client_names', '' );
	update_post_meta( $first['home_id'], '_nice_media_hero_id', 0 );
	wp_update_post( array( 'ID' => $first['preview_ids'][0], 'post_title' => 'Editor revised title', 'menu_order' => 90, 'post_status' => 'draft' ) );
	$second = nice_platform_seed_main();
	$assert( $first === $second, 'Second seed reuses record IDs.' );
	$assert( '' === nice_platform_home_content()['hero_title'] && array() === nice_platform_client_names(), 'Seed preserves intentional blank copy and clients.' );
	$assert( 0 === nice_platform_media_slot( 'hero' )['id'], 'Seed preserves cleared image.' );
	$assert( 'Editor revised title' === get_the_title( $first['preview_ids'][0] ) && 'draft' === get_post_status( $first['preview_ids'][0] ), 'Seed preserves editorial updates and draft status.' );
	$assert( 2 === count( nice_platform_get_previews() ), 'Drafts do not appear on gateway.' );
	wp_trash_post( $first['preview_ids'][1] );
	$third = nice_platform_seed_main();
	$assert( $first === $third && 'trash' === get_post_status( $first['preview_ids'][1] ), 'Seed does not resurrect or duplicate trashed previews.' );
} finally {
	$fixtures = get_posts( array( 'post_type' => array( 'page', 'nice_work_preview' ), 'post_status' => array_values( get_post_stati() ), 'posts_per_page' => -1 ) );
	foreach ( $fixtures as $fixture ) { wp_delete_post( $fixture->ID, true ); }
	update_option( 'page_on_front', $old_front );
	update_option( 'show_on_front', $old_show );
}
WP_CLI::success( $checks . ' seed checks passed; fixtures removed.' );
