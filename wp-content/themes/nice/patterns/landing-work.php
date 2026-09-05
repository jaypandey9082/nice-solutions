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
			<?php foreach ( $nice_projects as $nice_project ) :
				$nice_image_url        = esc_url( get_theme_file_uri( '/assets/images/' . $nice_project['image'] . '.webp' ) );
				$nice_image_mobile_url = esc_url( get_theme_file_uri( '/assets/images/' . $nice_project['image'] . '-480.webp' ) );
			?>
				<article class="nice-landing-project <?php echo esc_attr( $nice_project['class'] ); ?>" data-nice-reveal>
					<div class="nice-landing-project__media">
						<img src="<?php echo $nice_image_mobile_url; ?>" srcset="<?php echo $nice_image_mobile_url; ?> 480w, <?php echo $nice_image_url; ?> <?php echo esc_attr( $nice_project['width'] ); ?>w" sizes="(max-width: 767px) 100vw, 70vw" width="<?php echo esc_attr( $nice_project['width'] ); ?>" height="<?php echo esc_attr( $nice_project['height'] ); ?>" alt="<?php echo esc_attr( $nice_project['alt'] ); ?>" loading="lazy" decoding="async">
					</div>
					<div class="nice-landing-project__meta">
						<p><span>Client</span><?php echo esc_html( $nice_project['client'] ); ?></p>
						<p><span>Division</span><?php echo esc_html( $nice_project['division'] ); ?></p>
					</div>
					<h3><?php echo esc_html( $nice_project['title'] ); ?></h3>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<!-- /wp:html -->
