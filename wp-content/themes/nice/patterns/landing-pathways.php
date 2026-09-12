<?php
/**
 * Title: NICE Landing Pathways
 * Slug: nice/landing-pathways
 * Categories: featured
 * Description: Editorial doors for Events and Studio divisions.
 */

$nice_events_image        = esc_url( get_theme_file_uri( '/assets/images/voltas-crowd.webp' ) );
$nice_events_image_mobile = esc_url( get_theme_file_uri( '/assets/images/voltas-crowd-480.webp' ) );
$nice_studio_image        = esc_url( get_theme_file_uri( '/assets/images/strata-production.webp' ) );
$nice_studio_image_mobile = esc_url( get_theme_file_uri( '/assets/images/strata-production-480.webp' ) );
$nice_events_url          = esc_url( home_url( '/events/' ) );
$nice_studio_url          = esc_url( home_url( '/studio/' ) );
?>
<!-- wp:html -->
<section class="nice-doors-section" id="destinations" aria-label="<?php esc_attr_e( 'NICE Divisions', 'nice' ); ?>">
	<div class="nice-wide nice-doors-grid">
		<!-- Section 02: Events -->
		<article class="nice-door nice-door--events nice-pathway" id="events" data-nice-reveal>
			<div class="nice-door__header">
				<span class="nice-door__division">01 // Division</span>
				<span class="nice-door__pip" aria-hidden="true"></span>
			</div>
			<div class="nice-door__media">
				<img
					src="<?php echo $nice_events_image_mobile; ?>"
					srcset="<?php echo $nice_events_image_mobile; ?> 480w, <?php echo $nice_events_image; ?> 700w"
					sizes="(min-width: 1024px) 38rem, calc(100vw - 2rem)"
					width="700"
					height="377"
					alt="Audience members raising their hands during a live event"
					loading="lazy"
					decoding="async"
				>
			</div>
			<div class="nice-door__body">
				<h2 class="nice-door__title">Events</h2>
				<p class="nice-door__description">
					Turnkey live staging, spatial architecture, and immense scale environments engineered for enduring collective memory.
				</p>
			</div>
			<div class="nice-door__action">
				<a class="nice-door__link" href="<?php echo $nice_events_url; ?>">
					<span>Explore Events</span>
					<span class="nice-door__arrow" aria-hidden="true">&rarr;</span>
				</a>
			</div>
		</article>

		<!-- Section 03: Studio -->
		<article class="nice-door nice-door--studio nice-pathway" id="studio" data-nice-reveal>
			<div class="nice-door__header">
				<span class="nice-door__division">02 // Division</span>
				<span class="nice-door__pip" aria-hidden="true"></span>
			</div>
			<div class="nice-door__media">
				<img
					src="<?php echo $nice_studio_image_mobile; ?>"
					srcset="<?php echo $nice_studio_image_mobile; ?> 480w, <?php echo $nice_studio_image; ?> 577w"
					sizes="(min-width: 1024px) 38rem, calc(100vw - 2rem)"
					width="577"
					height="325"
					alt="Production crew filming inside a factory set"
					loading="lazy"
					decoding="async"
				>
			</div>
			<div class="nice-door__body">
				<h2 class="nice-door__title">Studio</h2>
				<p class="nice-door__description">
					Cinematic films, digital craft, real-time virtual volumes, and moving images crafted with narrative precision.
				</p>
			</div>
			<div class="nice-door__action">
				<a class="nice-door__link" href="<?php echo $nice_studio_url; ?>">
					<span>Explore Studio</span>
					<span class="nice-door__arrow" aria-hidden="true">&rarr;</span>
				</a>
			</div>
		</article>
	</div>
</section>
<!-- /wp:html -->
