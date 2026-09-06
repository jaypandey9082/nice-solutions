<?php
/**
 * Title: NICE Site Footer
 * Slug: nice/site-footer
 * Categories: footer
 * Inserter: no
 */

$nice_logo_url        = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
$nice_home_url        = esc_url( home_url( '/' ) );
$nice_events_url      = esc_url( home_url( '/events/' ) );
$nice_studio_url      = esc_url( home_url( '/studio/' ) );
$nice_clients_url     = esc_url( home_url( '/#clients' ) );
$nice_whatsapp_action = nice_get_contact_action( 'whatsapp' );
$nice_email_action    = nice_get_contact_action( 'email' );

$nice_is_events = function_exists( 'nice_theme_is_events_context' ) && nice_theme_is_events_context();
$nice_is_studio = function_exists( 'nice_theme_is_studio_home' ) && nice_theme_is_studio_home();

if ( $nice_is_events ) {
	$nice_work_url     = esc_url( home_url( '/events/case-studies/' ) );
	$nice_services_url = esc_url( home_url( '/events/services/' ) );
	$nice_contact_url  = esc_url( home_url( '/events/contact/' ) );
} elseif ( $nice_is_studio ) {
	$nice_work_url     = esc_url( home_url( '/studio/#studio-work' ) );
	$nice_services_url = esc_url( home_url( '/studio/#studio-services' ) );
	$nice_contact_url  = esc_url( home_url( '/studio/#studio-contact' ) );
} else {
	$nice_work_url     = esc_url( home_url( '/#work' ) );
	$nice_services_url = esc_url( home_url( '/#capabilities' ) );
	$nice_contact_url  = esc_url( home_url( '/#contact' ) );
}
?>
<!-- wp:html -->
<footer class="nice-site-footer">
	<div class="nice-wide">
		<div class="nice-footer-grid">
			<div class="nice-stack nice-footer-brand">
				<a class="nice-brand-link" href="<?php echo $nice_home_url; ?>" aria-label="<?php esc_attr_e( 'NICE home', 'nice' ); ?>">
					<img class="nice-logo nice-logo--footer" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE">
				</a>
				<p class="nice-muted">Nucleus Integrated Communication &amp; Entertainment Pvt. Ltd.</p>
			</div>
			<nav class="nice-footer-nav" aria-label="<?php esc_attr_e( 'Explore', 'nice' ); ?>">
				<span class="nice-eyebrow">Explore</span>
				<a href="<?php echo $nice_events_url; ?>">Events</a>
				<a href="<?php echo $nice_studio_url; ?>">Studio</a>
				<a href="<?php echo $nice_work_url; ?>">Work</a>
				<a href="<?php echo $nice_services_url; ?>">Services</a>
			</nav>
			<nav class="nice-footer-nav" aria-label="<?php esc_attr_e( 'Connect', 'nice' ); ?>">
				<span class="nice-eyebrow">Connect</span>
				<a href="<?php echo $nice_clients_url; ?>">Clients</a>
				<a href="<?php echo $nice_contact_url; ?>">Contact</a>
				<a href="<?php echo esc_url( $nice_whatsapp_action['url'] ); ?>" data-nice-contact-channel="whatsapp" data-nice-contact-placeholder="<?php echo $nice_whatsapp_action['placeholder'] ? 'true' : 'false'; ?>">WhatsApp</a>
				<a href="<?php echo esc_url( $nice_email_action['url'] ); ?>" data-nice-contact-channel="email" data-nice-contact-placeholder="<?php echo $nice_email_action['placeholder'] ? 'true' : 'false'; ?>">Email</a>
			</nav>
		</div>
		<div class="nice-footer-meta">
			<span>&copy; NICE Solutions</span>
			<span>Events / Studio</span>
		</div>
	</div>
</footer>
<!-- /wp:html -->
