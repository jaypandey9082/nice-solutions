<?php
/**
 * Shared NICE Core validation and content helpers.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a valid HTTPS URL or an empty string.
 *
 * @param mixed $value Candidate URL.
 * @return string
 */
function nice_sanitize_https_url( $value ) {
	$url = esc_url_raw( (string) $value, array( 'https' ) );

	return $url && 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) ? $url : '';
}

/**
 * Sanitize an integer meta value.
 *
 * @param mixed $value Candidate value.
 * @return int
 */
function nice_sanitize_integer( $value ) {
	return is_numeric( $value ) ? (int) $value : 0;
}

/**
 * Sanitize a percentage used for media focal positioning.
 *
 * @param mixed $value Candidate percentage.
 * @return int
 */
function nice_sanitize_percentage( $value ) {
	return max( 0, min( 100, nice_sanitize_integer( $value ) ) );
}

/**
 * Return whether a Page is the top-level Studio home.
 *
 * @param int $post_id Candidate Page ID.
 * @return bool
 */
function nice_is_studio_home_page( $post_id ) {
	$post = get_post( $post_id );

	return $post instanceof WP_Post
		&& 'page' === $post->post_type
		&& 0 === (int) $post->post_parent
		&& 'studio' === $post->post_name;
}

/**
 * Return whether a Page is the top-level Events home.
 *
 * @param int $post_id Candidate Page ID.
 * @return bool
 */
function nice_is_events_home_page( $post_id ) {
	$post = get_post( $post_id );

	return $post instanceof WP_Post
		&& 'page' === $post->post_type
		&& 0 === (int) $post->post_parent
		&& 'events' === $post->post_name;
}

/**
 * Accept only existing image attachments, or zero for an empty selection.
 *
 * @param mixed $value Candidate attachment ID.
 * @return int
 */
function nice_sanitize_hero_image_id( $value ) {
	if ( ! is_int( $value ) && ! is_string( $value ) ) {
		return 0;
	}
	$id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );

	return $id && 'attachment' === get_post_type( $id ) && wp_attachment_is_image( $id ) ? $id : 0;
}

/**
 * Accept only existing video attachments, or zero for an empty selection.
 *
 * The playable-format policy -- MP4 or WebM -- belongs in the picker, not here.
 * A .mov that is already stored should keep rendering rather than disappear from
 * a page the day this sanitizer next runs over it.
 *
 * @param mixed $value Candidate attachment ID.
 * @return int
 */
function nice_sanitize_video_attachment_id( $value ) {
	if ( ! is_int( $value ) && ! is_string( $value ) ) {
		return 0;
	}
	$id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );

	return $id && 'attachment' === get_post_type( $id ) && wp_attachment_is( 'video', $id ) ? $id : 0;
}

/**
 * Return the provider and id for a supported video link, or null.
 *
 * A path-shape allowlist, the same reasoning as the LinkedIn source patterns: a
 * channel, a playlist or a search result says where a video lives, not which
 * video it is. Only a shape that identifies one video is accepted, because the
 * page has one frame to put it in.
 *
 * @param mixed $value Candidate URL.
 * @return array{provider:string,id:string,hash:string}|null
 */
function nice_parse_embed_video_url( $value ) {
	$url = nice_sanitize_https_url( $value );

	if ( '' === $url ) {
		return null;
	}

	$parts = wp_parse_url( $url );
	$host  = strtolower( (string) ( isset( $parts['host'] ) ? $parts['host'] : '' ) );
	$path  = untrailingslashit( (string) ( isset( $parts['path'] ) ? $parts['path'] : '' ) );

	if ( preg_match( '#(^|\.)(?:youtube\.com|youtube-nocookie\.com)$#i', $host ) ) {
		if ( preg_match( '#^/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})$#', $path, $matches ) ) {
			return array(
				'provider' => 'youtube',
				'id'       => $matches[1],
				'hash'     => '',
			);
		}

		if ( '/watch' === $path ) {
			$query = array();
			parse_str( (string) ( isset( $parts['query'] ) ? $parts['query'] : '' ), $query );

			if ( isset( $query['v'] ) && preg_match( '#^[A-Za-z0-9_-]{11}$#', (string) $query['v'] ) ) {
				return array(
					'provider' => 'youtube',
					'id'       => (string) $query['v'],
					'hash'     => '',
				);
			}
		}

		return null;
	}

	if ( preg_match( '#(^|\.)youtu\.be$#i', $host )
		&& preg_match( '#^/([A-Za-z0-9_-]{11})$#', $path, $matches ) ) {
		return array(
			'provider' => 'youtube',
			'id'       => $matches[1],
			'hash'     => '',
		);
	}

	/* An unlisted Vimeo video carries a privacy hash as a second segment. */
	if ( preg_match( '#(^|\.)vimeo\.com$#i', $host )
		&& preg_match( '#^/(?:video/)?([0-9]{6,12})(?:/([A-Za-z0-9]{6,20}))?$#', $path, $matches ) ) {
		return array(
			'provider' => 'vimeo',
			'id'       => $matches[1],
			'hash'     => isset( $matches[2] ) ? $matches[2] : '',
		);
	}

	return null;
}

/**
 * Explain why a video link cannot be used, or return an empty string.
 *
 * @param mixed $value Candidate URL.
 * @return string
 */
function nice_get_embed_video_problem( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value || nice_parse_embed_video_url( $value ) ) {
		return '';
	}

	return __( 'The link has to identify one YouTube or Vimeo video, such as https://www.youtube.com/watch?v=..., https://youtu.be/... or https://vimeo.com/123456789. A channel, a playlist or a search result says where a video lives, not which one to play.', 'nice-core' );
}

/**
 * Return a supported video link, or an empty string.
 *
 * @param mixed $value Candidate URL.
 * @return string
 */
function nice_sanitize_embed_video_url( $value ) {
	return nice_parse_embed_video_url( $value ) ? nice_sanitize_https_url( $value ) : '';
}

/**
 * Build the player URL for a parsed video.
 *
 * The privacy-preserving hosts in both cases. They matter less than they look:
 * nothing requests either one until a reader presses play, because the page
 * renders a poster frame rather than an embed.
 *
 * @param array $video    Parsed video, from nice_parse_embed_video_url().
 * @param bool  $autoplay Whether the player should start on load.
 * @return string
 */
function nice_get_embed_video_src( $video, $autoplay = false ) {
	if ( empty( $video['provider'] ) || empty( $video['id'] ) ) {
		return '';
	}

	if ( 'youtube' === $video['provider'] ) {
		return add_query_arg(
			array_filter(
				array(
					'rel'            => 0,
					'modestbranding' => 1,
					'playsinline'    => 1,
					'autoplay'       => $autoplay ? 1 : null,
				),
				static function ( $item ) {
					return null !== $item;
				}
			),
			'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video['id'] )
		);
	}

	return add_query_arg(
		array_filter(
			array(
				'h'        => empty( $video['hash'] ) ? null : $video['hash'],
				'dnt'      => 1,
				'autoplay' => $autoplay ? 1 : null,
			),
			static function ( $item ) {
				return null !== $item;
			}
		),
		'https://player.vimeo.com/video/' . rawurlencode( $video['id'] )
	);
}

/**
 * Restrict Events hero REST edits to editors of the top-level Events Page.
 *
 * @param bool   $allowed   Existing decision.
 * @param string $meta_key  Meta key.
 * @param int    $object_id Page ID.
 * @return bool
 */
function nice_authorize_events_hero_meta( $allowed, $meta_key, $object_id ) {
	return nice_is_events_home_page( $object_id ) && current_user_can( 'edit_post', $object_id );
}

/**
 * Sanitize an optional four-digit year.
 *
 * @param mixed $value Candidate year.
 * @return int
 */
function nice_sanitize_year( $value ) {
	$year = nice_sanitize_integer( $value );

	return $year >= 1000 && $year <= 9999 ? $year : 0;
}

/**
 * Sanitize a post relationship to a Client record.
 *
 * @param mixed $value Candidate post ID.
 * @return int
 */
function nice_sanitize_client_id( $value ) {
	$client_id = absint( $value );

	return $client_id && 'nice_client' === get_post_type( $client_id ) ? $client_id : 0;
}

/**
 * Authorize edits to registered NICE post meta.
 *
 * @param bool   $allowed   Existing decision.
 * @param string $meta_key  Meta key.
 * @param int    $object_id Post ID.
 * @return bool
 */
function nice_authorize_post_meta( $allowed, $meta_key, $object_id ) {
	return current_user_can( 'edit_post', $object_id );
}

/**
 * Resolve a case study's related client name.
 *
 * @param int $case_study_id Case Study post ID.
 * @return string
 */
function nice_get_case_study_client_name( $case_study_id ) {
	$client_id = nice_sanitize_client_id( get_post_meta( $case_study_id, '_nice_client_id', true ) );

	if ( $client_id ) {
		return get_the_title( $client_id );
	}

	return sanitize_text_field( (string) get_post_meta( $case_study_id, '_nice_client_name', true ) );
}
