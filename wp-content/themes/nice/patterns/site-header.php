<?php
/**
 * Title: NICE Site Header
 * Slug: nice/site-header
 * Categories: header
 * Inserter: no
 */

$nice_logo_url = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
?>
<!-- wp:html -->
<a class="nice-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'nice' ); ?></a>
<header class="nice-site-header" data-nice-header>
	<nav class="nice-nav-shell" aria-label="<?php esc_attr_e( 'Primary navigation', 'nice' ); ?>">
		<a class="nice-brand-link" href="/" aria-label="<?php esc_attr_e( 'NICE home', 'nice' ); ?>">
			<img class="nice-logo nice-logo--nav" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE">
		</a>
		<div class="nice-desktop-nav">
			<a href="/">NICE</a>
			<a href="/#work">Work</a>
			<a href="/#capabilities">Services</a>
			<a href="/events/">Events</a>
			<a href="/studio/">Studio</a>
			<a href="/#clients">Clients</a>
		</div>
		<a class="nice-button nice-button--primary nice-nav-cta" href="/#contact"><?php esc_html_e( 'Contact', 'nice' ); ?></a>
		<button class="nice-menu-toggle" type="button" aria-expanded="false" aria-controls="nice-mobile-menu" aria-label="<?php esc_attr_e( 'Open menu', 'nice' ); ?>" data-nice-menu-open>
			<span class="nice-menu-icon" aria-hidden="true"></span>
		</button>
	</nav>
	<div class="nice-mobile-menu" id="nice-mobile-menu" data-state="closed" data-nice-mobile-menu aria-hidden="true" inert>
		<div class="nice-mobile-menu__top">
			<a class="nice-brand-link" href="/" aria-label="<?php esc_attr_e( 'NICE home', 'nice' ); ?>">
				<img class="nice-logo nice-logo--menu" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE">
			</a>
			<button class="nice-menu-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'nice' ); ?>" data-nice-menu-close></button>
		</div>
		<div class="nice-mobile-menu__links">
			<a href="/events/">Events</a>
			<a href="/studio/">Studio</a>
			<a href="/#work">Work</a>
			<a href="/#capabilities">Services</a>
			<a href="/#clients">Clients</a>
			<a href="/team/">Team</a>
			<a href="/#contact">Contact</a>
		</div>
		<div class="nice-mobile-menu__actions" aria-label="<?php esc_attr_e( 'Contact options', 'nice' ); ?>">
			<a class="nice-button nice-button--primary" href="/#contact">WhatsApp</a>
			<a class="nice-button nice-button--secondary" href="/#contact">Email</a>
		</div>
	</div>
</header>
<!-- /wp:html -->
