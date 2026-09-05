<?php
/**
 * Title: NICE Landing Pathways
 * Slug: nice/landing-pathways
 * Categories: featured
 * Description: Prominent Events and Studio destination selector.
 */

$nice_events_image        = esc_url( get_theme_file_uri( '/assets/images/voltas-crowd.webp' ) );
$nice_events_image_mobile = esc_url( get_theme_file_uri( '/assets/images/voltas-crowd-480.webp' ) );
$nice_studio_image        = esc_url( get_theme_file_uri( '/assets/images/strata-production.webp' ) );
$nice_studio_image_mobile = esc_url( get_theme_file_uri( '/assets/images/strata-production-480.webp' ) );
?>
<!-- wp:html -->
<section class="nice-landing-pathways nice-section" id="choose-path" aria-labelledby="nice-pathways-title">
	<div class="nice-wide">
		<header class="nice-section-heading nice-landing-pathways__heading" data-nice-reveal>
			<p class="nice-eyebrow">Events / Studio</p>
			<h2 id="nice-pathways-title">What are you looking for?</h2>
		</header>
		<div class="nice-pathways-grid">
			<a class="nice-pathway" href="/events/" data-nice-reveal>
				<img src="<?php echo $nice_events_image_mobile; ?>" srcset="<?php echo $nice_events_image_mobile; ?> 480w, <?php echo $nice_events_image; ?> 700w" sizes="(max-width: 767px) 100vw, 50vw" width="700" height="377" alt="Guests participating in the Voltas Fam-Tastic Fiesta" loading="eager" decoding="async">
				<span class="nice-pathway__shade" aria-hidden="true"></span>
				<span class="nice-pathway__content">
					<span class="nice-pathway__label">Events</span>
					<span class="nice-pathway__detail">Corporate Events / Exhibitions &amp; Conferences / Activations &amp; Promotions</span>
					<span class="nice-pathway__arrow" aria-hidden="true">&rarr;</span>
				</span>
			</a>
			<a class="nice-pathway" href="/studio/" data-nice-reveal>
				<img src="<?php echo $nice_studio_image_mobile; ?>" srcset="<?php echo $nice_studio_image_mobile; ?> 480w, <?php echo $nice_studio_image; ?> 577w" sizes="(max-width: 767px) 100vw, 50vw" width="577" height="325" alt="NICE production crew filming at the Strata Geosystems factory" loading="eager" decoding="async">
				<span class="nice-pathway__shade" aria-hidden="true"></span>
				<span class="nice-pathway__content">
					<span class="nice-pathway__label">Studio</span>
					<span class="nice-pathway__detail">Corporate Videos / Digital Content Creation / Films &amp; Entertainment</span>
					<span class="nice-pathway__arrow" aria-hidden="true">&rarr;</span>
				</span>
			</a>
		</div>
	</div>
</section>
<!-- /wp:html -->
