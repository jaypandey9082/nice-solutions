<?php
/**
 * Title: NICE Landing Hero
 * Slug: nice/landing-hero
 * Categories: featured
 * Description: Source-led landing hero with the NICE logo and two journeys.
 */

$nice_landing_images = array(
	'voltas' => array(
		'src'    => esc_url( get_theme_file_uri( '/assets/images/voltas-crowd.webp' ) ),
		'mobile' => esc_url( get_theme_file_uri( '/assets/images/voltas-crowd-480.webp' ) ),
		'width'  => 700,
		'height' => 377,
	),
	'gca' => array(
		'src'    => esc_url( get_theme_file_uri( '/assets/images/gca-2025.webp' ) ),
		'mobile' => esc_url( get_theme_file_uri( '/assets/images/gca-2025-480.webp' ) ),
		'width'  => 569,
		'height' => 293,
	),
	'zoetis' => array(
		'src'    => esc_url( get_theme_file_uri( '/assets/images/zoetis-engagement.webp' ) ),
		'mobile' => esc_url( get_theme_file_uri( '/assets/images/zoetis-engagement-480.webp' ) ),
		'width'  => 543,
		'height' => 305,
	),
	'strata' => array(
		'src'    => esc_url( get_theme_file_uri( '/assets/images/strata-production.webp' ) ),
		'mobile' => esc_url( get_theme_file_uri( '/assets/images/strata-production-480.webp' ) ),
		'width'  => 577,
		'height' => 325,
	),
);
$nice_logo_url    = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
$nice_events_url  = esc_url( home_url( '/events/' ) );
$nice_studio_url  = esc_url( home_url( '/studio/' ) );
$nice_image_index = 0;
?>
<!-- wp:html -->
<section class="nice-landing-hero" id="top" aria-labelledby="nice-landing-title">
	<div class="nice-landing-hero__mosaic" aria-hidden="true">
		<?php foreach ( $nice_landing_images as $nice_image ) : ?>
			<img src="<?php echo $nice_image['mobile']; ?>" srcset="<?php echo $nice_image['mobile']; ?> 480w, <?php echo $nice_image['src']; ?> <?php echo esc_attr( $nice_image['width'] ); ?>w" sizes="(min-width: 768px) 25vw, 50vw" width="<?php echo esc_attr( $nice_image['width'] ); ?>" height="<?php echo esc_attr( $nice_image['height'] ); ?>" alt="" decoding="async" fetchpriority="<?php echo 0 === $nice_image_index ? 'high' : 'low'; ?>">
			<?php $nice_image_index++; ?>
		<?php endforeach; ?>
	</div>
	<div class="nice-landing-hero__veil" aria-hidden="true"></div>
	<div class="nice-wide nice-landing-hero__inner">
		<div class="nice-landing-hero__brand" data-nice-reveal>
			<img class="nice-logo nice-logo--hero" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE - Nucleus Integrated Communication and Entertainment">
			<p>Nucleus Integrated Communication &amp; Entertainment Pvt. Ltd.</p>
		</div>
		<div class="nice-landing-hero__statement" data-nice-reveal>
			<h1 id="nice-landing-title"><span>Brief.</span><span>Idea.</span><span>Solution.</span></h1>
		</div>
		<div class="nice-landing-hero__routes" aria-label="<?php esc_attr_e( 'Choose a NICE division', 'nice' ); ?>" data-nice-reveal>
			<a href="<?php echo $nice_events_url; ?>"><span>Events</span><span aria-hidden="true">&rarr;</span></a>
			<a href="<?php echo $nice_studio_url; ?>"><span>Studio</span><span aria-hidden="true">&rarr;</span></a>
		</div>
	</div>
</section>
<!-- /wp:html -->
