<?php
/**
 * Title: NICE Site Header
 * Slug: nice/site-header
 * Categories: header
 * Inserter: no
 */

$nice_logo_url        = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
$nice_home_url        = esc_url( home_url( '/' ) );
$nice_work_url        = esc_url( home_url( '/#work' ) );
$nice_services_url    = esc_url( home_url( '/#capabilities' ) );
$nice_events_url      = esc_url( home_url( '/events/' ) );
$nice_studio_url      = esc_url( home_url( '/studio/' ) );
$nice_clients_url     = esc_url( home_url( '/#clients' ) );
$nice_contact_url     = esc_url( home_url( '/#contact' ) );
$nice_whatsapp_action = nice_get_contact_action( 'whatsapp' );
$nice_email_action    = nice_get_contact_action( 'email' );
?>
<!-- wp:html -->
<a class="nice-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'nice' ); ?></a>
<header class="nice-site-header" data-nice-header>
	<nav class="nice-nav-shell" aria-label="<?php esc_attr_e( 'Primary navigation', 'nice' ); ?>">
		<a class="nice-brand-link" href="<?php echo $nice_home_url; ?>" aria-label="<?php esc_attr_e( 'NICE home', 'nice' ); ?>">
			<img class="nice-logo nice-logo--nav" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE" fetchpriority="auto">
		</a>
		<div class="nice-desktop-nav">
			<a href="<?php echo $nice_home_url; ?>">NICE</a>
			<a href="<?php echo $nice_work_url; ?>">Work</a>
			<a href="<?php echo $nice_services_url; ?>">Services</a>
			<a href="<?php echo $nice_events_url; ?>">Events</a>
			<a href="<?php echo $nice_studio_url; ?>">Studio</a>
			<a href="<?php echo $nice_clients_url; ?>">Clients</a>
		</div>
		<a class="nice-button nice-button--primary nice-nav-cta" href="<?php echo $nice_contact_url; ?>"><?php esc_html_e( 'Contact', 'nice' ); ?></a>
		<button class="nice-menu-toggle" type="button" aria-expanded="false" aria-controls="nice-mobile-menu" aria-label="<?php esc_attr_e( 'Open menu', 'nice' ); ?>" data-nice-menu-open>
			<span class="nice-menu-icon" aria-hidden="true"></span>
		</button>
	</nav>
	<div class="nice-mobile-menu" id="nice-mobile-menu" data-state="closed" data-nice-mobile-menu aria-hidden="true" inert>
		<div class="nice-mobile-menu__top">
			<a class="nice-brand-link" href="<?php echo $nice_home_url; ?>" aria-label="<?php esc_attr_e( 'NICE home', 'nice' ); ?>">
				<img class="nice-logo nice-logo--menu" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE">
			</a>
			<button class="nice-menu-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'nice' ); ?>" data-nice-menu-close></button>
		</div>
		<div class="nice-mobile-menu__links">
			<a href="<?php echo $nice_work_url; ?>">Work</a>
			<a href="<?php echo $nice_services_url; ?>">Services</a>
			<a href="<?php echo $nice_events_url; ?>">Events</a>
			<a href="<?php echo $nice_studio_url; ?>">Studio</a>
			<a href="<?php echo $nice_clients_url; ?>">Clients</a>
			<a href="<?php echo $nice_contact_url; ?>">Contact</a>
		</div>
		<div class="nice-mobile-menu__actions" aria-label="<?php esc_attr_e( 'Contact options', 'nice' ); ?>">
			<a class="nice-button nice-button--primary" href="<?php echo esc_url( $nice_whatsapp_action['url'] ); ?>" data-nice-contact-channel="whatsapp" data-nice-contact-placeholder="<?php echo $nice_whatsapp_action['placeholder'] ? 'true' : 'false'; ?>">WhatsApp</a>
			<a class="nice-button nice-button--secondary" href="<?php echo esc_url( $nice_email_action['url'] ); ?>" data-nice-contact-channel="email" data-nice-contact-placeholder="<?php echo $nice_email_action['placeholder'] ? 'true' : 'false'; ?>">Email</a>
		</div>
	</div>
</header>
<!-- /wp:html -->
