<?php
/**
 * Title: NICE Landing Hero
 * Slug: nice/landing-hero
 * Categories: featured
 * Description: Source-led landing hero with the NICE logo and two journeys.
 */

$nice_logo_url   = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
$nice_events_url = esc_url( home_url( '/events/' ) );
$nice_studio_url = esc_url( home_url( '/studio/' ) );
?>
<!-- wp:html -->
<section class="nice-landing-hero" id="top" aria-labelledby="nice-landing-title">
	<div class="nice-wide nice-landing-hero__inner">
		<div class="nice-landing-hero__brand" data-nice-reveal>
			<img class="nice-logo nice-logo--hero" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE - Nucleus Integrated Communication and Entertainment">
			<p>Nucleus Integrated Communication &amp; Entertainment Pvt. Ltd.</p>
		</div>
		<div class="nice-landing-hero__statement" data-nice-editorial-reveal>
			<h1 id="nice-landing-title"><span>Brief.</span> <span>Idea.</span> <span>Solution.</span></h1>
		</div>
		<div class="nice-landing-hero__routes" aria-label="<?php esc_attr_e( 'Choose a NICE division', 'nice' ); ?>" data-nice-reveal>
			<a href="<?php echo $nice_events_url; ?>"><span>Events</span><span aria-hidden="true">&rarr;</span></a>
			<a href="<?php echo $nice_studio_url; ?>"><span>Studio</span><span aria-hidden="true">&rarr;</span></a>
		</div>
	</div>
</section>
<!-- /wp:html -->
