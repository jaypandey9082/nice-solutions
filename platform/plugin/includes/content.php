<?php
/** Site identity and local data contracts. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nice_platform_role() {
	$role = defined( 'NICE_SITE_ROLE' ) ? NICE_SITE_ROLE : 'main';
	return in_array( $role, array( 'main', 'events', 'studio' ), true ) ? $role : 'main';
}

/** Accept only absolute HTTP(S) URLs without credentials. */
function nice_platform_absolute_url( $value ) {
	if ( ! is_string( $value ) || preg_match( '/[\x00-\x20\\\\]/', $value ) ) {
		return '';
	}
	$parts = wp_parse_url( $value );
	if ( ! is_array( $parts ) || empty( $parts['host'] ) || ! in_array( strtolower( $parts['scheme'] ?? '' ), array( 'http', 'https' ), true ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
		return '';
	}
	return filter_var( $value, FILTER_VALIDATE_URL ) ? esc_url_raw( $value, array( 'http', 'https' ) ) : '';
}

function nice_platform_destination_path( $value ) {
	if ( ! is_string( $value ) || '' === $value || '/' !== $value[0] || str_starts_with( $value, '//' ) || preg_match( '/[\x00-\x20\\\\]/', $value ) ) {
		return '';
	}
	$parts = wp_parse_url( $value );
	if ( ! is_array( $parts ) || isset( $parts['host'] ) || isset( $parts['scheme'] ) ) {
		return '';
	}
	// Reject encoded separators and traversal before joining a configured site base.
	$decoded = rawurldecode( $parts['path'] ?? '' );
	if ( str_contains( $decoded, '\\' ) || str_starts_with( $decoded, '//' ) || preg_match( '~(?:^|/)\.\.?(?:/|$)~', $decoded ) || preg_match( '/[\x00-\x1f]/', $decoded ) ) {
		return '';
	}
	return $value;
}

function nice_platform_site_url( $role, $path = '' ) {
	$defaults = array( 'main' => 'https://nicesolutions.in', 'events' => 'https://events.nicesolutions.in', 'studio' => 'https://studios.nicesolutions.in' );
	if ( ! is_string( $role ) || ! isset( $defaults[ $role ] ) ) {
		return '';
	}
	$key  = 'NICE_' . strtoupper( $role ) . '_URL';
	$base = defined( $key ) ? nice_platform_absolute_url( constant( $key ) ) : '';
	$base = $base ?: $defaults[ $role ];
	if ( wp_parse_url( $base, PHP_URL_QUERY ) || wp_parse_url( $base, PHP_URL_FRAGMENT ) ) {
		$base = $defaults[ $role ];
	}
	if ( '' === $path ) {
		return trailingslashit( $base );
	}
	if ( ! is_string( $path ) || str_contains( $path, '://' ) || str_starts_with( $path, '//' ) ) {
		return '';
	}
	$path = nice_platform_destination_path( '/' . ltrim( $path, '/' ) );
	return $path ? untrailingslashit( $base ) . $path : '';
}

function nice_platform_home_defaults() {
	return array(
		'hero_title'         => 'NICE Solutions',
		'hero_description'   => 'Events. Films. Stories brought to life.',
		'events_description' => 'Corporate events, exhibitions, conferences, activations and promotions.',
		'studio_description' => 'Corporate videos, digital content, films and entertainment.',
		'about_heading'      => 'One NICE idea. Many possibilities.',
		'about_body'         => 'NICE brings together events and creative production through NICE Events and NICE Studio.',
		'brief_body'         => 'Understand the brief, the audience and the purpose.',
		'idea_body'          => 'Shape a creative response around the brief.',
		'solution_body'      => 'Bring the idea into production and delivery.',
	);
}

function nice_platform_page_id( $page_id = 0 ) {
	$id = absint( $page_id ?: get_option( 'page_on_front', 0 ) );
	return 'page' === get_post_type( $id ) ? $id : 0;
}

function nice_platform_home_content( $page_id = 0 ) {
	$id = nice_platform_page_id( $page_id );
	$content = nice_platform_home_defaults();
	foreach ( $content as $key => $default ) {
		$meta = '_nice_home_' . $key;
		if ( $id && metadata_exists( 'post', $id, $meta ) ) {
			$content[ $key ] = sanitize_textarea_field( get_post_meta( $id, $meta, true ) );
		}
	}
	return $content;
}

function nice_platform_image_id( $id ) {
	$id = is_scalar( $id ) ? absint( $id ) : 0;
	return $id && 'attachment' === get_post_type( $id ) && wp_attachment_is_image( $id ) ? $id : 0;
}

function nice_platform_focal( $value ) {
	return is_numeric( $value ) ? max( 0, min( 100, (float) $value ) ) : 50;
}

function nice_platform_boolean( $value ) {
	return in_array( $value, array( true, 1, '1', 'true' ), true );
}

function nice_platform_media_slot( $slot, $page_id = 0 ) {
	$result = array( 'id' => 0, 'mobile_id' => 0, 'x' => 50, 'y' => 50, 'reference' => false );
	if ( ! in_array( $slot, array( 'hero', 'events', 'studio' ), true ) ) {
		return $result;
	}
	$id = nice_platform_page_id( $page_id );
	foreach ( $result as $field => $default ) {
		$key = '_nice_media_' . $slot . '_' . $field;
		if ( $id && metadata_exists( 'post', $id, $key ) ) {
			$value = get_post_meta( $id, $key, true );
			$result[ $field ] = in_array( $field, array( 'id', 'mobile_id' ), true ) ? nice_platform_image_id( $value ) : ( 'reference' === $field ? nice_platform_boolean( $value ) : nice_platform_focal( $value ) );
		}
	}
	return $result;
}

function nice_platform_division( $value ) {
	return in_array( $value, array( 'events', 'studio' ), true ) ? $value : '';
}

function nice_platform_valid_destination( $url, $division ) {
	$url = nice_platform_absolute_url( $url );
	if ( ! $url || ! nice_platform_division( $division ) ) {
		return '';
	}
	$target = wp_parse_url( $url );
	$path = ( $target['path'] ?? '/' ) . ( isset( $target['query'] ) ? '?' . $target['query'] : '' ) . ( isset( $target['fragment'] ) ? '#' . $target['fragment'] : '' );
	if ( ! nice_platform_destination_path( $path ) ) {
		return '';
	}
	$base   = wp_parse_url( nice_platform_site_url( $division ) );
	$port   = static function ( $parts ) { return $parts['port'] ?? ( 'https' === strtolower( $parts['scheme'] ) ? 443 : 80 ); };
	if ( strtolower( $target['host'] ) === strtolower( $base['host'] ) && strtolower( $target['scheme'] ) === strtolower( $base['scheme'] ) && $port( $target ) === $port( $base ) ) {
		return $url;
	}
	$production_host = 'events' === $division ? 'events.nicesolutions.in' : 'studios.nicesolutions.in';
	if ( strtolower( $target['host'] ) === $production_host && 'https' === strtolower( $target['scheme'] ) && 443 === $port( $target ) ) {
		return nice_platform_site_url( $division, $path );
	}
	return '';
}

function nice_platform_preview_url( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'nice_work_preview' !== $post->post_type ) {
		return '';
	}
	$division = nice_platform_division( get_post_meta( $post->ID, '_nice_division', true ) );
	if ( ! $division ) {
		return '';
	}
	$url = nice_platform_valid_destination( get_post_meta( $post->ID, '_nice_destination_url', true ), $division );
	$path = nice_platform_destination_path( get_post_meta( $post->ID, '_nice_destination_path', true ) );
	return $url ?: ( $path ? nice_platform_site_url( $division, $path ) : '' );
}

/** @return WP_Post[] Local published records; rendering belongs to the theme. */
function nice_platform_get_previews() {
	return 'main' === nice_platform_role() ? get_posts( array( 'post_type' => 'nice_work_preview', 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ), 'no_found_rows' => true ) ) : array();
}

function nice_platform_client_names() {
	$id = nice_platform_page_id();
	$value = $id ? get_post_meta( $id, '_nice_client_names', true ) : '';
	return array_values( array_unique( array_filter( array_map( 'sanitize_text_field', preg_split( '/\R/', (string) $value ) ), static function ( $name ) { return '' !== $name; } ) ) );
}

/** Contacts are blank until explicitly configured. Social is a list of absolute URLs. */
function nice_platform_sanitize_contact( $input ) {
	$input = is_array( $input ) ? $input : array();
	$result = array( 'email' => '', 'whatsapp' => '', 'phone' => '', 'social' => array() );
	$email = is_string( $input['email'] ?? null ) ? trim( $input['email'] ) : '';
	$result['email'] = is_email( $email ) ? sanitize_email( $email ) : '';
	$phone = is_string( $input['phone'] ?? null ) ? preg_replace( '/[\s().-]/', '', $input['phone'] ) : '';
	$result['phone'] = preg_match( '/^\+?[1-9][0-9]{6,14}$/', $phone ) ? $phone : '';
	$whatsapp = is_string( $input['whatsapp'] ?? null ) ? trim( $input['whatsapp'] ) : '';
	if ( preg_match( '~^https://wa\.me/([1-9][0-9]{6,14})/?$~', $whatsapp, $match ) ) {
		$whatsapp = $match[1];
	} else {
		$whatsapp = preg_replace( '/[\s()+.-]/', '', $whatsapp );
	}
	$result['whatsapp'] = preg_match( '/^[1-9][0-9]{6,14}$/', $whatsapp ) ? 'https://wa.me/' . $whatsapp : '';
	$social = $input['social'] ?? array();
	$social = is_string( $social ) ? preg_split( '/\R/', $social ) : ( is_array( $social ) ? $social : array() );
	$result['social'] = array_values( array_unique( array_filter( array_map( 'nice_platform_absolute_url', $social ) ) ) );
	return $result;
}

function nice_platform_contact() {
	return nice_platform_sanitize_contact( get_option( 'nice_platform_contact', array() ) );
}

function nice_platform_meta_auth( $allowed, $key, $id ) {
	return current_user_can( 'edit_post', $id );
}

function nice_platform_register_content() {
	if ( 'main' === nice_platform_role() ) {
		register_post_type( 'nice_work_preview', array(
			'labels' => array( 'name' => 'Work Previews', 'singular_name' => 'Work Preview', 'add_new_item' => 'Add Work Preview', 'edit_item' => 'Edit Work Preview' ),
			'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => true,
			'exclude_from_search' => true, 'rewrite' => false, 'query_var' => false, 'has_archive' => false,
			'menu_icon' => 'dashicons-format-gallery', 'supports' => array( 'title', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields' ),
			'map_meta_cap' => true, 'capability_type' => 'post',
		) );
		$preview_meta = array( '_nice_division' => array( 'string', 'nice_platform_division' ), '_nice_destination_url' => array( 'string', 'nice_platform_absolute_url' ), '_nice_destination_path' => array( 'string', 'nice_platform_destination_path' ), '_nice_reference' => array( 'boolean', 'nice_platform_boolean' ) );
		foreach ( $preview_meta as $key => $spec ) {
			register_post_meta( 'nice_work_preview', $key, array( 'single' => true, 'type' => $spec[0], 'sanitize_callback' => $spec[1], 'auth_callback' => 'nice_platform_meta_auth', 'show_in_rest' => true ) );
		}
		foreach ( nice_platform_home_defaults() as $key => $default ) {
			register_post_meta( 'page', '_nice_home_' . $key, array( 'single' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'auth_callback' => 'nice_platform_meta_auth', 'show_in_rest' => true ) );
		}
		register_post_meta( 'page', '_nice_client_names', array( 'single' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'auth_callback' => 'nice_platform_meta_auth', 'show_in_rest' => true ) );
	}
	foreach ( array( 'hero', 'events', 'studio' ) as $slot ) {
		foreach ( array( 'id' => array( 'integer', 'nice_platform_image_id' ), 'mobile_id' => array( 'integer', 'nice_platform_image_id' ), 'x' => array( 'number', 'nice_platform_focal' ), 'y' => array( 'number', 'nice_platform_focal' ), 'reference' => array( 'boolean', 'nice_platform_boolean' ) ) as $field => $spec ) {
			register_post_meta( 'page', '_nice_media_' . $slot . '_' . $field, array( 'single' => true, 'type' => $spec[0], 'sanitize_callback' => $spec[1], 'auth_callback' => 'nice_platform_meta_auth', 'show_in_rest' => true ) );
		}
	}
}
add_action( 'init', 'nice_platform_register_content' );

// The REST editor needs theme thumbnail support for the native featured-image panel.
add_action( 'after_setup_theme', function () {
	if ( 'main' !== nice_platform_role() ) {
		return;
	}
	$support = get_theme_support( 'post-thumbnails' );
	if ( ! $support ) {
		add_theme_support( 'post-thumbnails', array( 'nice_work_preview' ) );
	} elseif ( is_array( $support ) && isset( $support[0] ) && is_array( $support[0] ) && ! in_array( 'nice_work_preview', $support[0], true ) ) {
		add_theme_support( 'post-thumbnails', array_merge( $support[0], array( 'nice_work_preview' ) ) );
	}
}, 20 );
