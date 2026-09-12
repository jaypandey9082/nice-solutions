<?php
/**
 * Title: NICE Landing Hero
 * Slug: nice/landing-hero
 * Categories: featured
 * Description: Minimal warm editorial hero with display statement and narrative support.
 */

$nice_events_url = esc_url( nice_theme_division_url( 'events' ) );
$nice_studio_url = esc_url( nice_theme_division_url( 'studio' ) );
?>
<!-- wp:html -->
<section class="nice-landing-hero" id="top" aria-labelledby="nice-landing-title">
	<div class="nice-wide nice-landing-hero__inner">
		<div class="nice-landing-hero__overline" data-nice-reveal>
			<span class="nice-pip" aria-hidden="true"></span>
			<span class="nice-overline-text">Nucleus Integrated Communication &amp; Entertainment</span>
		</div>
		<div class="nice-landing-hero__statement" data-nice-editorial-reveal>
			<h1 id="nice-landing-title" class="nice-hero-headline">We Create Monumental Experiences &amp; Moving Stories.</h1>
		</div>
		<p class="nice-hero-support" data-nice-reveal>
			Two specialized divisions engineered for experiential scale and cinematic precision.
		</p>
		<div class="nice-landing-hero__routes nice-sr-only" aria-label="<?php esc_attr_e( 'Choose a NICE division', 'nice' ); ?>">
			<a href="<?php echo $nice_events_url; ?>">Events</a>
			<a href="<?php echo $nice_studio_url; ?>">Studio</a>
		</div>
	</div>
</section>
<!-- /wp:html -->
