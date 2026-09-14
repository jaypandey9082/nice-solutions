<?php
/**
 * Title: NICE Landing Gateway Previews
 * Slug: nice/landing-gateway-projects
 * Categories: featured
 * Description: Curated project previews that hand the reader to the division that owns the work.
 */

$nice_gateway_projects = function_exists( 'nice_theme_get_gateway_projects' ) ? nice_theme_get_gateway_projects() : array();

/*
 * Nothing published means no section at all. An editor unpublishing one preview
 * should leave the other two reading normally, and unpublishing the last should
 * leave no empty heading behind.
 */
if ( $nice_gateway_projects ) :
	?>
<!-- wp:html -->
<section class="nice-gateway-projects nice-section" id="selected-work" aria-labelledby="nice-gateway-projects-title">
	<div class="nice-wide">
		<header class="nice-section-heading" data-nice-reveal>
			<p class="nice-eyebrow">Selected work</p>
			<h2 id="nice-gateway-projects-title">Recent NICE projects.</h2>
		</header>
		<div class="nice-gateway-projects__grid" data-nice-count="<?php echo esc_attr( (string) count( $nice_gateway_projects ) ); ?>">
			<?php foreach ( $nice_gateway_projects as $nice_project ) : ?>
				<article class="nice-gateway-project" data-nice-reveal>
					<h3 class="nice-gateway-project__title"><?php echo esc_html( $nice_project['title'] ); ?></h3>
					<?php if ( $nice_project['attachment_id'] && $nice_project['media_approved'] ) : ?>
						<div class="nice-gateway-project__media">
							<?php
							echo wp_get_attachment_image(
								$nice_project['attachment_id'],
								'large',
								false,
								array(
									'loading'  => 'lazy',
									'decoding' => 'async',
									'sizes'    => '(min-width: 64rem) 28rem, calc(100vw - 2rem)',
								)
							);
							?>
						</div>
					<?php endif; ?>
					<?php if ( $nice_project['summary'] ) : ?>
						<p class="nice-gateway-project__summary"><?php echo esc_html( $nice_project['summary'] ); ?></p>
					<?php endif; ?>
					<?php if ( $nice_project['url'] ) : ?>
						<a class="nice-link nice-gateway-project__link" href="<?php echo esc_url( $nice_project['url'] ); ?>">
							<?php
							/* translators: %s: project title. */
							echo esc_html( sprintf( __( 'View %s', 'nice' ), $nice_project['title'] ) );
							?>
							<span aria-hidden="true">&rarr;</span>
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<!-- /wp:html -->
	<?php
endif;
