<?php
/**
 * Title: NICE Landing Clients
 * Slug: nice/landing-clients
 * Categories: text
 * Description: Quiet text-only credibility preview using verified client names.
 */

$nice_clients = nice_get_landing_client_previews();
?>
<!-- wp:html -->
<section class="nice-landing-clients nice-section" id="clients" aria-labelledby="nice-clients-title">
	<div class="nice-wide">
		<header class="nice-landing-clients__heading" data-nice-reveal>
			<p class="nice-eyebrow">Our clients</p>
			<h2 id="nice-clients-title">Selected clients.</h2>
		</header>
		<ul class="nice-client-names" aria-label="<?php esc_attr_e( 'Selected NICE clients', 'nice' ); ?>" data-nice-reveal>
			<?php foreach ( $nice_clients as $nice_client ) : ?>
				<li><?php echo esc_html( $nice_client ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<!-- /wp:html -->
