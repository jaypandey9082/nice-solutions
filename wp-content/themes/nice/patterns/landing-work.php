<?php
/**
 * Title: NICE Landing Selected Work
 * Slug: nice/landing-work
 * Categories: featured
 * Description: Asymmetric preview of three projects verified in the NICE profile.
 */

$nice_projects = nice_get_landing_project_previews();
?>
<!-- wp:html -->
<section class="nice-landing-work nice-section" id="work" aria-labelledby="nice-work-title">
	<div class="nice-wide">
		<header class="nice-section-heading nice-landing-work__heading" data-nice-reveal>
			<p class="nice-eyebrow">Selected work</p>
			<h2 id="nice-work-title">Selected NICE projects.</h2>
		</header>
		<div class="nice-landing-work__grid">
			<?php
			foreach ( $nice_projects as $nice_project ) :
				/*
				 * The featured image on a migrated record is deck photography that
				 * has not been cleared, so it renders only once an editor ticks
				 * "Media cleared for publication". The theme-asset fallbacks are the
				 * same retired deck files, so there is no unapproved image to fall
				 * back to: an uncleared project shows the intentional placeholder.
				 */
				$nice_post_id       = (int) ( $nice_project['post_id'] ?? 0 );
				$nice_media_cleared = $nice_post_id
					&& ! empty( $nice_project['attachment_id'] )
					&& function_exists( 'nice_theme_media_approved' )
					&& nice_theme_media_approved( $nice_post_id );

				$nice_project_url = '';
				if ( $nice_post_id && function_exists( 'nice_get_content_url' ) ) {
					$nice_project_url = nice_get_content_url( $nice_post_id );
				}
				if ( ! $nice_project_url && function_exists( 'nice_theme_division_url' ) ) {
					$nice_project_url = nice_theme_division_url(
						strtolower( (string) $nice_project['division'] ),
						'case-studies/' . $nice_project['slug']
					);
				}
				?>
				<article class="nice-landing-project <?php echo esc_attr( $nice_project['class'] ); ?>" data-nice-reveal>
					<?php if ( $nice_project_url ) : ?>
						<a class="nice-landing-project__link" href="<?php echo esc_url( $nice_project_url ); ?>" aria-label="<?php echo esc_attr( sprintf( 'View the %s case study', $nice_project['title'] ) ); ?>">
					<?php endif; ?>
					<div class="nice-landing-project__media">
						<?php if ( $nice_media_cleared ) : ?>
							<?php echo wp_get_attachment_image( $nice_project['attachment_id'], 'full', false, array( 'alt' => $nice_project['alt'], 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 100vw, 70vw' ) ); ?>
						<?php elseif ( function_exists( 'nice_render_events_media_placeholder' ) ) : ?>
							<?php nice_render_events_media_placeholder( sprintf( '%s project media pending approval', $nice_project['title'] ) ); ?>
						<?php endif; ?>
					</div>
					<div class="nice-landing-project__meta">
						<p><span>Client</span><?php echo esc_html( $nice_project['client'] ); ?></p>
						<p><span>Division</span><?php echo esc_html( $nice_project['division'] ); ?></p>
					</div>
					<h3><?php echo esc_html( $nice_project['title'] ); ?></h3>
					<?php if ( $nice_project_url ) : ?>
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<!-- /wp:html -->
