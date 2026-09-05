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
$nice_events_url          = esc_url( home_url( '/events/' ) );
$nice_studio_url          = esc_url( home_url( '/studio/' ) );
?>
<!-- wp:html -->
<section class="nice-landing-pathways nice-section" id="choose-path" aria-labelledby="nice-pathways-title">
	<div class="nice-wide">
		<header class="nice-section-heading nice-landing-pathways__heading" data-nice-reveal>
			<p class="nice-eyebrow">Events / Studio</p>
			<h2 id="nice-pathways-title">What are you looking for?</h2>
		</header>
		<div class="nice-pathways-grid">
			<a class="nice-pathway" href="<?php echo $nice_events_url; ?>" data-nice-reveal>
				<img src="<?php echo $nice_events_image_mobile; ?>" srcset="<?php echo $nice_events_image_mobile; ?> 480w, <?php echo $nice_events_image; ?> 700w" sizes="(min-width: 1320px) 612px, (min-width: 1200px) calc(50vw - 48px), (min-width: 768px) calc(50vw - 40px), calc(100vw - 40px)" width="700" height="377" alt="Audience members raising their hands during a live event" loading="lazy" decoding="async">
				<span class="nice-pathway__shade" aria-hidden="true"></span>
				<span class="nice-pathway__content">
					<span class="nice-pathway__label">Events</span>
					<span class="nice-pathway__detail">Corporate Events / Exhibitions &amp; Conferences / Activations &amp; Promotions</span>
					<span class="nice-pathway__arrow" aria-hidden="true">&rarr;</span>
				</span>
			</a>
			<a class="nice-pathway" href="<?php echo $nice_studio_url; ?>" data-nice-reveal>
				<img src="<?php echo $nice_studio_image_mobile; ?>" srcset="<?php echo $nice_studio_image_mobile; ?> 480w, <?php echo $nice_studio_image; ?> 577w" sizes="(min-width: 1320px) 612px, (min-width: 1200px) calc(50vw - 48px), (min-width: 768px) calc(50vw - 40px), calc(100vw - 40px)" width="577" height="325" alt="Production crew filming inside a factory" loading="lazy" decoding="async">
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
