<?php
/**
 * Title: NICE Landing Pathways
 * Slug: nice/landing-pathways
 * Categories: featured
 * Description: Editorial doors for Events and Studio divisions.
 */

/*
 * Approved reference imagery. These are theme assets rather than attachments,
 * so the media approval gate cannot reach them; they must therefore be images
 * already cleared for publication, never migrated deck photography.
 */
$nice_events_image        = esc_url( get_theme_file_uri( '/assets/images/events-reference-hero-700.webp' ) );
$nice_events_image_mobile = esc_url( get_theme_file_uri( '/assets/images/events-reference-hero-480.webp' ) );
$nice_studio_image        = esc_url( get_theme_file_uri( '/assets/images/studio-reference-hero-577.webp' ) );
$nice_studio_image_mobile = esc_url( get_theme_file_uri( '/assets/images/studio-reference-hero-480.webp' ) );
$nice_events_url          = esc_url( nice_theme_division_url( 'events' ) );
$nice_studio_url          = esc_url( nice_theme_division_url( 'studio' ) );
?>
<!-- wp:html -->
<section class="nice-doors-section" id="destinations" aria-label="<?php esc_attr_e( 'NICE Divisions', 'nice' ); ?>">
	<div class="nice-wide nice-doors-grid">
		<!-- Section 02: Events -->
		<article class="nice-door nice-door--events nice-pathway" id="events" data-nice-reveal>
			<h2 class="nice-door__title">Events</h2>
			<div class="nice-door__media">
				<img
					src="<?php echo $nice_events_image_mobile; ?>"
					srcset="<?php echo $nice_events_image_mobile; ?> 480w, <?php echo $nice_events_image; ?> 700w"
					sizes="(min-width: 1024px) 38rem, calc(100vw - 2rem)"
					width="700"
					height="377"
					alt="Reference imagery for the NICE Events division"
					loading="lazy"
					decoding="async"
				>
			</div>
			<div class="nice-door__body">
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
			<h2 class="nice-door__title">Studio</h2>
			<div class="nice-door__media">
				<img
					src="<?php echo $nice_studio_image_mobile; ?>"
					srcset="<?php echo $nice_studio_image_mobile; ?> 480w, <?php echo $nice_studio_image; ?> 577w"
					sizes="(min-width: 1024px) 38rem, calc(100vw - 2rem)"
					width="577"
					height="325"
					alt="Reference imagery for the NICE Studio division"
					loading="lazy"
					decoding="async"
				>
			</div>
			<div class="nice-door__body">
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
