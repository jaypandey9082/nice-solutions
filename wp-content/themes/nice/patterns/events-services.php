<?php
/**
 * Title: NICE Events Services
 * Slug: nice/events-services
 * Categories: services
 * Description: Editorial introduction to the three approved Events services.
 */

$nice_events_services     = nice_get_events_service_previews();
$nice_all_services_url    = esc_url( home_url( '/events/services/' ) );
$nice_service_sizes       = '(min-width: 1320px) 612px, (min-width: 768px) calc(50vw - 40px), calc(100vw - 40px)';
?>
<!-- wp:html -->
<section class="nice-events-services nice-events-section" id="events-services" aria-labelledby="nice-events-services-title">
	<div class="nice-wide">
		<header class="nice-events-section-heading" data-nice-reveal>
			<div>
				<p class="nice-eyebrow">Events services</p>
				<h2 id="nice-events-services-title">Built around the experience.</h2>
			</div>
			<a class="nice-link" href="<?php echo $nice_all_services_url; ?>" data-nice-future-route="true">All services <span aria-hidden="true">-&gt;</span></a>
		</header>
		<div class="nice-events-services__list">
			<?php foreach ( $nice_events_services as $nice_index => $nice_service ) :
				$nice_service_image        = esc_url( get_theme_file_uri( '/assets/images/' . $nice_service['image'] . '.webp' ) );
				$nice_service_image_mobile = esc_url( get_theme_file_uri( '/assets/images/' . $nice_service['image_mobile'] . '.webp' ) );
				$nice_service_url          = esc_url( home_url( '/events/services/' . $nice_service['slug'] . '/' ) );
			?>
				<article class="nice-events-service<?php echo 1 === $nice_index ? ' nice-events-service--reverse' : ''; ?>" data-nice-reveal>
					<div class="nice-events-service__media">
						<?php if ( ! empty( $nice_service['attachment_id'] ) ) : ?>
							<?php echo wp_get_attachment_image( $nice_service['attachment_id'], 'full', false, array( 'alt' => $nice_service['alt'], 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => $nice_service_sizes ) ); ?>
						<?php else : ?>
							<img src="<?php echo $nice_service_image_mobile; ?>" srcset="<?php echo $nice_service_image_mobile; ?> 480w, <?php echo $nice_service_image; ?> <?php echo esc_attr( $nice_service['width'] ); ?>w" sizes="<?php echo esc_attr( $nice_service_sizes ); ?>" width="<?php echo esc_attr( $nice_service['width'] ); ?>" height="<?php echo esc_attr( $nice_service['height'] ); ?>" alt="<?php echo esc_attr( $nice_service['alt'] ); ?>" loading="lazy" decoding="async">
						<?php endif; ?>
					</div>
					<div class="nice-events-service__content">
						<span class="nice-events-service__index"><?php echo esc_html( sprintf( '%02d', $nice_index + 1 ) ); ?></span>
						<h3><?php echo esc_html( $nice_service['name'] ); ?></h3>
						<p><?php echo esc_html( $nice_service['description'] ); ?></p>
						<a class="nice-link" href="<?php echo $nice_service_url; ?>" data-nice-future-route="true">Explore service <span aria-hidden="true">-&gt;</span></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<!-- /wp:html -->
