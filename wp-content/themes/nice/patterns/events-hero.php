<?php
/**
 * Title: NICE Events Hero
 * Slug: nice/events-hero
 * Categories: featured
 * Description: Full-bleed editorial introduction to the Events division.
 */

$nice_events_hero_image        = esc_url( get_theme_file_uri( '/assets/images/voltas-crowd.webp' ) );
$nice_events_hero_image_mobile = esc_url( get_theme_file_uri( '/assets/images/voltas-crowd-480.webp' ) );
$nice_events_services_url      = esc_url( home_url( '/events/services/' ) );
$nice_events_work_url          = esc_url( home_url( '/events/case-studies/' ) );
$nice_events_clients_url       = esc_url( home_url( '/events/clients/' ) );
$nice_events_team_url          = esc_url( home_url( '/events/team/' ) );
$nice_events_contact_url       = esc_url( home_url( '/events/contact/' ) );
?>
<!-- wp:html -->
<section class="nice-events-hero" id="events-top" aria-labelledby="nice-events-title">
	<img class="nice-events-hero__media" src="<?php echo $nice_events_hero_image_mobile; ?>" srcset="<?php echo $nice_events_hero_image_mobile; ?> 480w, <?php echo $nice_events_hero_image; ?> 700w" sizes="100vw" width="700" height="377" alt="" decoding="async" fetchpriority="high">
	<div class="nice-events-hero__veil" aria-hidden="true"></div>
	<div class="nice-wide nice-events-hero__inner">
		<div class="nice-events-hero__title" data-nice-reveal>
			<p class="nice-eyebrow">NICE / Events</p>
			<h1 id="nice-events-title">Events</h1>
		</div>
		<div class="nice-events-hero__message" data-nice-reveal>
			<p class="nice-events-hero__statement">We create experiences<br>people remember.</p>
			<p class="nice-events-hero__support">Corporate events, exhibitions, conferences and activations planned around the brief and carried through to execution.</p>
			<div class="nice-events-hero__actions">
				<a href="#events-services">Explore services <span aria-hidden="true">&darr;</span></a>
				<a href="#events-work">Selected work <span aria-hidden="true">&darr;</span></a>
			</div>
		</div>
		<nav class="nice-events-hero__nav" aria-label="Events section navigation" data-nice-reveal>
			<a href="<?php echo $nice_events_services_url; ?>">Services</a>
			<a href="<?php echo $nice_events_work_url; ?>">Case Studies</a>
			<a href="<?php echo $nice_events_clients_url; ?>">Clients</a>
			<a href="<?php echo $nice_events_team_url; ?>">Team</a>
			<a href="<?php echo $nice_events_contact_url; ?>">Contact</a>
		</nav>
	</div>
</section>
<!-- /wp:html -->
