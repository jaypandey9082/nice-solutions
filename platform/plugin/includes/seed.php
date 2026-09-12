<?php
/** Explicit CLI-only provisioning. Never imports another site's database or media. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nice_platform_seed_main() {
	if ( 'main' !== nice_platform_role() ) {
		return new WP_Error( 'nice_wrong_role', 'seed-main is available only on a main installation.' );
	}
	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		return new WP_Error( 'nice_cli_only', 'Run the explicit wp nice-platform seed-main command.' );
	}
	$home_id = nice_platform_page_id();
	if ( $home_id && 'trash' === get_post_status( $home_id ) ) {
		return new WP_Error( 'nice_home_trashed', 'The configured homepage is in Trash. Restore it or choose another homepage before seeding.' );
	}
	if ( ! $home_id ) {
		$existing = get_posts( array( 'post_type' => 'page', 'post_status' => array_values( get_post_stati() ), 'name' => 'home', 'posts_per_page' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
		if ( $existing ) {
			if ( 'trash' === $existing[0]->post_status ) {
				return new WP_Error( 'nice_home_trashed', 'Home is in Trash. Restore it or configure another static homepage before seeding.' );
			}
			$home_id = $existing[0]->ID;
		} else {
			$home_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_name' => 'home' ), true );
			if ( is_wp_error( $home_id ) ) {
				return $home_id;
			}
		}
	}
	foreach ( nice_platform_home_defaults() as $key => $default ) {
		if ( ! metadata_exists( 'post', $home_id, '_nice_home_' . $key ) ) {
			add_post_meta( $home_id, '_nice_home_' . $key, $default, true );
		}
	}
	foreach ( array( 'hero', 'events', 'studio' ) as $slot ) {
		foreach ( array( 'id' => 0, 'mobile_id' => 0, 'x' => 50, 'y' => 50, 'reference' => false ) as $field => $value ) {
			if ( ! metadata_exists( 'post', $home_id, '_nice_media_' . $slot . '_' . $field ) ) {
				add_post_meta( $home_id, '_nice_media_' . $slot . '_' . $field, $value, true );
			}
		}
	}
	if ( ! metadata_exists( 'post', $home_id, '_nice_client_names' ) ) {
		// Names and summaries match the approved reference content; no source photos are imported.
		add_post_meta( $home_id, '_nice_client_names', "Voltas\nZoetis\nAirtel\nBajaj\nMahindra\nCRISIL", true );
	}
	$records = array(
		array( 'voltas-fam-tastic-fiesta', 'Voltas Fam-Tastic Fiesta', 'events', 'An employee family fiesta for Voltas Limited at The Parsi Gymkhana, Mumbai.' ),
		array( 'strata-geosystems-factory-shoot', 'Strata Geosystems Factory Shoot', 'studio', 'A factory shoot with drone and FPV presentation for Strata Geosystems India in Gujarat.' ),
		array( 'gca-2025', 'GCA 2025', 'events', 'The three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India.' ),
	);
	$ids = array();
	foreach ( $records as $index => $record ) {
		list( $slug, $title, $division, $summary ) = $record;
		$existing = get_posts( array( 'post_type' => 'nice_work_preview', 'post_status' => array_values( get_post_stati() ), 'meta_key' => '_nice_seed_key', 'meta_value' => $slug, 'posts_per_page' => 1 ) );
		if ( ! $existing ) {
			$existing = get_posts( array( 'post_type' => 'nice_work_preview', 'post_status' => array_values( get_post_stati() ), 'name' => $slug, 'posts_per_page' => 1 ) );
		}
		if ( $existing ) {
			// Preserve reordered, renamed, drafted, trashed or cleared editorial records.
			$ids[] = $existing[0]->ID;
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'nice_work_preview', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_excerpt' => $summary, 'menu_order' => ( $index + 1 ) * 10, 'meta_input' => array( '_nice_seed_key' => $slug, '_nice_division' => $division, '_nice_destination_url' => '', '_nice_destination_path' => '/case-studies/' . $slug . '/', '_nice_reference' => false ) ), true );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$ids[] = $id;
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
	return array( 'home_id' => $home_id, 'preview_ids' => $ids );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'nice-platform seed-main', function () {
		$result = nice_platform_seed_main();
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		WP_CLI::success( 'Main content ready. Home ID: ' . $result['home_id'] . '; preview IDs: ' . implode( ', ', $result['preview_ids'] ) . '. No images imported.' );
	} );
}
