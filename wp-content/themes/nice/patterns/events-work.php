<?php
/**
 * Title: NICE Events Selected Work
 * Slug: nice/events-work
 * Categories: featured
 * Description: Source-approved project preview prepared for future Case Studies.
 */

$nice_events_projects = nice_get_events_project_previews();
$nice_events_work_url = esc_url( home_url( '/events/case-studies/' ) );
?>
<!-- wp:html -->
<section class="nice-events-work nice-events-section" id="events-work" aria-labelledby="nice-events-work-title">
	<div class="nice-wide">
		<header class="nice-events-section-heading" data-nice-reveal>
			<div>
				<p class="nice-eyebrow">Selected Events work</p>
				<h2 id="nice-events-work-title">Made for the moment.</h2>
			</div>
			<a class="nice-link" href="<?php echo $nice_events_work_url; ?>" data-nice-future-route="true">All case studies <span aria-hidden="true">-&gt;</span></a>
		</header>
		<div class="nice-events-work__grid">
			<?php foreach ( $nice_events_projects as $nice_project ) :
				$nice_project_image        = esc_url( get_theme_file_uri( '/assets/images/' . $nice_project['image'] . '.webp' ) );
				$nice_project_image_mobile = esc_url( get_theme_file_uri( '/assets/images/' . $nice_project['image_mobile'] . '.webp' ) );
			?>
				<article class="nice-events-project <?php echo esc_attr( $nice_project['class'] ); ?>" data-nice-reveal>
					<div class="nice-events-project__media">
						<img src="<?php echo $nice_project_image_mobile; ?>" srcset="<?php echo $nice_project_image_mobile; ?> <?php echo esc_attr( str_contains( $nice_project['image_mobile'], '-360' ) ? '360w' : '480w' ); ?>, <?php echo $nice_project_image; ?> <?php echo esc_attr( $nice_project['width'] ); ?>w" sizes="(min-width: 1320px) 760px, (min-width: 768px) 58vw, calc(100vw - 40px)" width="<?php echo esc_attr( $nice_project['width'] ); ?>" height="<?php echo esc_attr( $nice_project['height'] ); ?>" alt="<?php echo esc_attr( $nice_project['alt'] ); ?>" loading="lazy" decoding="async">
					</div>
					<p class="nice-events-project__client"><?php echo esc_html( $nice_project['client'] ); ?></p>
					<h3><?php echo esc_html( $nice_project['title'] ); ?></h3>
					<p><?php echo esc_html( $nice_project['description'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<!-- /wp:html -->
