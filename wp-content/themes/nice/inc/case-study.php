<?php
/**
 * The Case Study detail page, for both divisions.
 *
 * These two pages were written twice and stayed identical for everything except
 * the class prefix, which is how studio-pages.php ended up reading $video_url
 * from itself for a release and a half. The parts that genuinely differ are
 * small and named: Studio leads with a film or a picture where Events leads with
 * the write-up, and Studio strips embedded media out of the body where Events
 * keeps it.
 *
 * The division-specific leaves -- the heroes, the contact calls to action and
 * the two preview cards -- stay in their own files. Their markup differs for
 * reasons a reader can see, and folding them together would trade one honest
 * duplication for a function full of ternaries.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gather everything both detail pages read from a record.
 *
 * @param WP_Post $case_study Case Study record.
 * @param string  $division   Division slug.
 * @return array
 */
function nice_theme_get_case_study_view( $case_study, $division ) {
	$service_type = function_exists( 'nice_theme_get_primary_term' ) ? nice_theme_get_primary_term( $case_study->ID, 'nice_service_type' ) : null;

	$related = $service_type && function_exists( 'nice_get_case_studies_by_service' )
		? nice_get_case_studies_by_service( $service_type->slug, array( 'division' => $division ) )
		: array();
	$related = array_values(
		array_filter(
			$related,
			static function ( $item ) use ( $case_study ) {
				return $item->ID !== $case_study->ID;
			}
		)
	);
	$related = array_slice( $related, 0, 3 );

	$all_cases = function_exists( 'nice_get_case_studies' ) ? nice_get_case_studies( array( 'division' => $division ) ) : array();

	/* Nothing shares this project's service type, so offer the division instead. */
	if ( ! $related ) {
		$related = array_values(
			array_filter(
				$all_cases,
				static function ( $item ) use ( $case_study ) {
					return $item->ID !== $case_study->ID;
				}
			)
		);
		$related = array_slice( $related, 0, 3 );
	}

	$current = array_search( $case_study->ID, wp_list_pluck( $all_cases, 'ID' ), true );

	return array(
		'client'       => function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : '',
		'location'     => get_post_meta( $case_study->ID, '_nice_location', true ),
		'year'         => (int) get_post_meta( $case_study->ID, '_nice_year', true ),
		'quote_text'   => get_post_meta( $case_study->ID, '_nice_quote_text', true ),
		'quote_author' => get_post_meta( $case_study->ID, '_nice_quote_author', true ),
		'service_type' => $service_type,
		'division'     => function_exists( 'nice_theme_get_primary_term' ) ? nice_theme_get_primary_term( $case_study->ID, 'nice_division' ) : null,
		'related'      => $related,
		'previous'     => false !== $current && $current > 0 ? $all_cases[ $current - 1 ] : null,
		'next'         => false !== $current && $current < count( $all_cases ) - 1 ? $all_cases[ $current + 1 ] : null,
	);
}

/**
 * Render the Client / Location / Year / Service Type / Division strip.
 *
 * @param array  $view     Prepared view, from nice_theme_get_case_study_view().
 * @param string $division Division slug.
 */
function nice_render_case_study_infobar( $view, $division ) {
	?>
	<section class="nice-<?php echo esc_attr( $division ); ?>-case-infobar" aria-label="<?php esc_attr_e( 'Project overview', 'nice' ); ?>">
		<div class="nice-wide">
			<dl class="nice-<?php echo esc_attr( $division ); ?>-case-meta-bar" data-nice-reveal>
				<?php if ( $view['client'] ) : ?><div><dt><?php esc_html_e( 'Client', 'nice' ); ?></dt><dd><?php echo esc_html( $view['client'] ); ?></dd></div><?php endif; ?>
				<?php if ( $view['location'] ) : ?><div><dt><?php esc_html_e( 'Location', 'nice' ); ?></dt><dd><?php echo esc_html( $view['location'] ); ?></dd></div><?php endif; ?>
				<?php if ( $view['year'] ) : ?><div><dt><?php esc_html_e( 'Year', 'nice' ); ?></dt><dd><?php echo esc_html( $view['year'] ); ?></dd></div><?php endif; ?>
				<?php if ( $view['service_type'] ) : ?><div><dt><?php esc_html_e( 'Service Type', 'nice' ); ?></dt><dd><?php echo esc_html( $view['service_type']->name ); ?></dd></div><?php endif; ?>
				<?php if ( $view['division'] ) : ?><div><dt><?php esc_html_e( 'Division', 'nice' ); ?></dt><dd><?php echo esc_html( $view['division']->name ); ?></dd></div><?php endif; ?>
			</dl>
		</div>
	</section>
	<?php
}

/**
 * Return a Case Study's card image, or an empty string.
 *
 * Every listing card, related-work card and home-page project card on the site
 * was rendering a grey placeholder whatever the record held: the stylesheets had
 * img rules waiting and the data carried the attachment id, but no template ever
 * emitted the picture. One helper for all of them, behind the same approval tick
 * that governs the gallery and the feature media.
 *
 * Lazy without exception. Every one of these sits in a grid below a page heading,
 * and the largest thing a reader waits for is never a card.
 *
 * @param int    $post_id Case Study ID.
 * @param string $sizes   Responsive sizes attribute for the card's slot.
 * @return string
 */
function nice_theme_get_case_study_card_image( $post_id, $sizes = '(min-width: 48rem) 50vw, 100vw' ) {
	if ( ! function_exists( 'nice_theme_media_approved' ) || ! nice_theme_media_approved( $post_id ) ) {
		return '';
	}

	return nice_theme_get_featured_image(
		$post_id,
		$sizes,
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
}

/**
 * Return the project description, as plain paragraphs separated by blank lines.
 *
 * The write-up is the description. It used to sit in a section of its own below
 * the record's details, under a standing "From objective to execution." heading,
 * while the hero carried the one-line excerpt -- so a reader met a summary, then
 * a row of facts, then the same project described again at greater length. The
 * longer text is the one worth reading, so it moved up into the hero and the
 * section went.
 *
 * Returned as text rather than markup because the hero is a hero: a project that
 * needs a heading, a list or an image is describing itself at a length this
 * design does not have a place for.
 *
 * @param WP_Post $case_study Case Study record.
 * @return string
 */
function nice_theme_get_case_study_description( $case_study ) {
	$content = trim( (string) $case_study->post_content );

	if ( '' === $content ) {
		return (string) $case_study->post_excerpt;
	}

	$html = apply_filters( 'the_content', $content );

	/* Record where each paragraph ended, before the tags that said so are cut. */
	$html = preg_replace( '#</(?:p|h[1-6]|li|blockquote|div)>#i', "\n\n", $html );
	$html = preg_replace( '#<br\s*/?>#i', "\n\n", $html );

	$text       = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );
	$paragraphs = array();

	foreach ( preg_split( '/\n\s*\n/', $text ) as $paragraph ) {
		$paragraph = trim( preg_replace( '/\s+/u', ' ', str_replace( "\xc2\xa0", ' ', $paragraph ) ) );

		if ( '' !== $paragraph ) {
			$paragraphs[] = $paragraph;
		}
	}

	/* A record whose only block was an image still has its excerpt to fall on. */
	return $paragraphs ? implode( "\n\n", $paragraphs ) : (string) $case_study->post_excerpt;
}

/**
 * Render the client quote, if one has been recorded.
 *
 * @param array  $view     Prepared view.
 * @param string $division Division slug.
 */
function nice_render_case_study_quote( $view, $division ) {
	if ( ! $view['quote_text'] ) {
		return;
	}

	$prefix = 'nice-' . $division;
	?>
	<section class="<?php echo esc_attr( $prefix ); ?>-case-quote <?php echo esc_attr( $prefix ); ?>-inner-section" aria-label="<?php esc_attr_e( 'Client quote', 'nice' ); ?>">
		<div class="nice-wide" data-nice-reveal>
			<blockquote class="nice-case-quote">
				<p class="nice-editorial" data-nice-editorial-reveal>&ldquo;<?php echo esc_html( $view['quote_text'] ); ?>&rdquo;</p>
				<?php if ( $view['quote_author'] ) : ?>
					<cite>&mdash; <?php echo esc_html( $view['quote_author'] ); ?></cite>
				<?php endif; ?>
			</blockquote>
		</div>
	</section>
	<?php
}

/**
 * Return the feature media an editor chose for a project, or an empty array.
 *
 * Separate from the renderer for the same reason the gallery getter is: the
 * enqueue has to ask what a record holds without drawing it.
 *
 * @param int $post_id Case Study ID.
 * @return array
 */
function nice_theme_get_case_study_feature_media( $post_id ) {
	if ( ! function_exists( 'nice_theme_media_approved' ) || ! nice_theme_media_approved( $post_id ) ) {
		return array();
	}

	$type = function_exists( 'nice_sanitize_feature_media_type' )
		? nice_sanitize_feature_media_type( get_post_meta( $post_id, '_nice_feature_media_type', true ) )
		: 'image';

	$poster_id  = (int) get_post_thumbnail_id( $post_id );
	$poster_url = $poster_id ? (string) wp_get_attachment_image_url( $poster_id, 'full' ) : '';

	if ( 'video-file' === $type ) {
		$video_id = function_exists( 'nice_sanitize_video_attachment_id' )
			? nice_sanitize_video_attachment_id( get_post_meta( $post_id, '_nice_feature_video_id', true ) )
			: 0;

		if ( ! $video_id ) {
			$type = 'image';
		} else {
			return array(
				'type'       => 'video-file',
				'src'        => (string) wp_get_attachment_url( $video_id ),
				'mime'       => (string) get_post_mime_type( $video_id ),
				'poster_url' => $poster_url,
			);
		}
	}

	if ( 'video-link' === $type ) {
		$video = function_exists( 'nice_parse_embed_video_url' )
			? nice_parse_embed_video_url( get_post_meta( $post_id, '_nice_feature_video_url', true ) )
			: null;

		if ( ! $video ) {
			$type = 'image';
		} else {
			return array(
				'type'       => 'video-link',
				'embed'      => nice_get_embed_video_src( $video, true ),
				'provider'   => $video['provider'],
				'poster_id'  => $poster_id,
				'poster_url' => $poster_url,
			);
		}
	}

	return $poster_id
		? array(
			'type'      => 'image',
			'poster_id' => $poster_id,
		)
		: array();
}

/**
 * Render a Studio project's feature media.
 *
 * Returns whether it drew something that paints on load, because the gallery
 * below needs to know whether it owns the page's first picture. A lazy video
 * without a poster paints nothing, so in that case the gallery still does.
 *
 * @param int $post_id Case Study ID.
 * @return bool
 */
function nice_render_case_study_feature_media( $post_id ) {
	$media = nice_theme_get_case_study_feature_media( $post_id );

	if ( ! $media ) {
		return false;
	}

	$painted = false;
	?>
	<section class="nice-case-media-wrap" aria-label="<?php esc_attr_e( 'Project visual', 'nice' ); ?>">
		<div class="nice-wide">
			<?php if ( 'video-file' === $media['type'] ) : ?>
				<?php
				/*
				 * No src until it is near the viewport: media.js swaps data-src in
				 * and calls load(). A feature film at the top of a project page is
				 * the heaviest thing on the site, and it should not be downloaded
				 * by a reader who came for the write-up.
				 */
				$painted = '' !== $media['poster_url'];
				?>
				<div class="nice-video nice-video--case" data-nice-reveal>
					<video data-nice-lazy-video muted autoplay playsinline loop preload="none"<?php echo $media['poster_url'] ? ' poster="' . esc_url( $media['poster_url'] ) . '"' : ''; ?>>
						<source data-src="<?php echo esc_url( $media['src'] ); ?>" type="<?php echo esc_attr( $media['mime'] ); ?>">
					</video>
				</div>
			<?php elseif ( 'video-link' === $media['type'] ) : ?>
				<?php $painted = (bool) $media['poster_id']; ?>
				<div class="nice-video nice-video--case" data-nice-reveal>
					<?php
					if ( $media['poster_id'] ) {
						echo nice_theme_get_featured_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_get_attachment_image().
							$post_id,
							'(min-width: 75rem) 1200px, 100vw',
							array(
								'alt'           => get_the_title( $post_id ),
								'loading'       => 'eager',
								'fetchpriority' => 'high',
							)
						);
					}
					?>
					<button type="button" class="nice-video__play"
						data-nice-video-embed="<?php echo esc_url( $media['embed'] ); ?>"
						data-nice-video-title="<?php echo esc_attr( sprintf( /* translators: %s: project title. */ __( '%s, video', 'nice' ), get_the_title( $post_id ) ) ); ?>">
						<span class="nice-sr-only"><?php esc_html_e( 'Play the project video', 'nice' ); ?></span>
						<svg aria-hidden="true" viewBox="0 0 24 24" width="24" height="24" focusable="false"><path fill="currentColor" d="M8 5.14v13.72L19 12z"></path></svg>
					</button>
				</div>
			<?php else : ?>
				<?php $painted = true; ?>
				<div class="nice-case-media" data-nice-reveal>
					<?php
					echo nice_theme_get_featured_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_get_attachment_image().
						$post_id,
						'(min-width: 75rem) 1200px, 100vw',
						array(
							'alt'           => get_the_title( $post_id ),
							'loading'       => 'eager',
							'fetchpriority' => 'high',
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php

	return $painted;
}

/**
 * Render the related work grid.
 *
 * @param array  $view     Prepared view.
 * @param string $division Division slug.
 */
function nice_render_case_study_related( $view, $division ) {
	if ( ! $view['related'] ) {
		return;
	}

	$prefix = 'nice-' . $division;
	?>
	<section class="<?php echo esc_attr( $prefix ); ?>-inner-section <?php echo esc_attr( $prefix ); ?>-related-work" aria-labelledby="nice-related-work-title">
		<div class="nice-wide">
			<header class="<?php echo esc_attr( $prefix ); ?>-inner-heading" data-nice-reveal>
				<p class="nice-eyebrow"><?php esc_html_e( 'Continue exploring', 'nice' ); ?></p>
				<h2 id="nice-related-work-title"><?php esc_html_e( 'Related work', 'nice' ); ?></h2>
			</header>
			<div class="<?php echo esc_attr( $prefix ); ?>-case-list">
				<?php
				foreach ( $view['related'] as $index => $item ) {
					if ( 'studio' === $division ) {
						nice_render_studio_case_preview( $item );
						continue;
					}

					nice_render_case_study_preview(
						$item,
						0 === $index ? 'nice-events-case-preview--featured' : 'nice-events-case-preview--secondary'
					);
				}
				?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render the previous and next project cards.
 *
 * @param array  $view     Prepared view.
 * @param string $division Division slug.
 */
function nice_render_case_study_navigation( $view, $division ) {
	if ( ! $view['previous'] && ! $view['next'] ) {
		return;
	}

	$prefix = 'nice-' . $division;
	$cards  = array(
		'prev' => array(
			'post'  => $view['previous'],
			'label' => '&larr; ' . esc_html__( 'Previous project', 'nice' ),
		),
		'next' => array(
			'post'  => $view['next'],
			'label' => esc_html__( 'Next project', 'nice' ) . ' &rarr;',
		),
	);
	?>
	<nav class="<?php echo esc_attr( $prefix ); ?>-project-navigation nice-wide" aria-label="<?php esc_attr_e( 'Case study navigation', 'nice' ); ?>">
		<?php
		foreach ( $cards as $key => $card ) :
			if ( ! $card['post'] ) :
				?>
				<span class="<?php echo esc_attr( $prefix ); ?>-nav-card-empty"></span>
				<?php
				continue;
			endif;

			$card_client = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $card['post']->ID ) : '';
			$card_url    = 'studio' === $division
				? nice_theme_get_studio_content_url( $card['post'] )
				: nice_theme_get_events_content_url( $card['post'] );
			?>
			<a class="<?php echo esc_attr( $prefix ); ?>-nav-card <?php echo esc_attr( $prefix ); ?>-nav-card--<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $card_url ); ?>">
				<div class="<?php echo esc_attr( $prefix ); ?>-nav-card__body">
					<small><?php echo $card['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- An escaped string plus an arrow entity. ?></small>
					<?php if ( $card_client ) : ?><span class="nice-eyebrow"><?php echo esc_html( $card_client ); ?></span><?php endif; ?>
					<span class="<?php echo esc_attr( $prefix ); ?>-nav-card__title"><?php echo esc_html( $card['post']->post_title ); ?></span>
				</div>
			</a>
			<?php
		endforeach;
		?>
	</nav>
	<?php
}

/**
 * Render one Case Study detail page.
 *
 * @param string $division Division slug, 'events' or 'studio'.
 * @return string
 */
function nice_render_case_study_detail( $division ) {
	$case_study = get_queried_object();

	if ( ! $case_study instanceof WP_Post
		|| 'nice_case_study' !== $case_study->post_type
		|| ! has_term( $division, 'nice_division', $case_study ) ) {
		return '';
	}

	$view        = nice_theme_get_case_study_view( $case_study, $division );
	$is_studio   = 'studio' === $division;
	$description = nice_theme_get_case_study_description( $case_study );

	ob_start();

	if ( $is_studio ) {
		nice_render_studio_inner_hero( $view['client'] ?: __( 'Studio case study', 'nice' ), $case_study->post_title, $description, $case_study->ID );
	} else {
		nice_render_events_inner_hero( $view['client'] ?: __( 'Events case study', 'nice' ), $case_study->post_title, $description, $case_study->ID );
	}

	nice_render_case_study_infobar( $view, $division );
	nice_render_case_study_quote( $view, $division );

	/*
	 * Studio leads with the work; Events leads with the words and lets the
	 * gallery be the picture. So the gallery only inherits the page's first
	 * paint when nothing above it claimed one.
	 */
	$lead_painted = $is_studio ? nice_render_case_study_feature_media( $case_study->ID ) : false;

	nice_render_case_study_gallery( $case_study->ID, $division, ! $lead_painted );
	nice_render_case_study_related( $view, $division );
	nice_render_case_study_navigation( $view, $division );

	if ( $is_studio ) {
		nice_render_studio_inner_contact_cta();
	} else {
		nice_render_events_inner_contact_cta();
	}

	return (string) ob_get_clean();
}
