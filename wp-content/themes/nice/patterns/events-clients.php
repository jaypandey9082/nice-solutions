<?php
/**
 * Title: NICE Events Clients
 * Slug: nice/events-clients
 * Categories: text
 * Description: Restrained preview of source-supported Events clients.
 */

$nice_events_clients     = nice_get_events_clients();
$nice_events_clients_url = esc_url( home_url( '/events/clients/' ) );
?>
<!-- wp:html -->
<section class="nice-events-clients nice-events-section" id="events-clients" aria-labelledby="nice-events-clients-title">
	<div class="nice-wide">
		<header class="nice-events-section-heading" data-nice-reveal>
			<div>
				<p class="nice-eyebrow">Events clients</p>
				<h2 id="nice-events-clients-title">Selected collaborations.</h2>
			</div>
			<a class="nice-link" href="<?php echo $nice_events_clients_url; ?>">Client list <span aria-hidden="true">-&gt;</span></a>
		</header>
		<ul class="nice-events-clients__list" aria-label="Selected NICE Events clients" data-nice-reveal>
			<?php foreach ( $nice_events_clients as $nice_client ) : ?>
				<li><?php echo esc_html( $nice_client ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<!-- /wp:html -->
