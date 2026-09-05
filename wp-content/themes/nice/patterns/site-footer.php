<?php
/**
 * Title: NICE Site Footer
 * Slug: nice/site-footer
 * Categories: footer
 * Inserter: no
 */

$nice_logo_url = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
?>
<!-- wp:html -->
<footer class="nice-site-footer">
	<div class="nice-wide">
		<div class="nice-footer-grid">
			<div class="nice-stack nice-footer-brand">
				<a class="nice-brand-link" href="/" aria-label="<?php esc_attr_e( 'NICE home', 'nice' ); ?>">
					<img class="nice-logo nice-logo--footer" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE">
				</a>
				<p class="nice-muted">Nucleus Integrated Communication &amp; Entertainment Pvt. Ltd.</p>
			</div>
			<nav class="nice-footer-nav" aria-label="<?php esc_attr_e( 'Explore', 'nice' ); ?>">
				<span class="nice-eyebrow">Explore</span>
				<a href="/events/">Events</a>
				<a href="/studio/">Studio</a>
				<a href="/#work">Work</a>
				<a href="/#capabilities">Services</a>
			</nav>
			<nav class="nice-footer-nav" aria-label="<?php esc_attr_e( 'Connect', 'nice' ); ?>">
				<span class="nice-eyebrow">Connect</span>
				<a href="/#clients">Clients</a>
				<a href="/team/">Team</a>
				<a href="/#contact">Contact</a>
				<a href="/#contact">WhatsApp</a>
				<a href="/#contact">Email</a>
			</nav>
		</div>
		<div class="nice-footer-meta">
			<span>&copy; NICE Solutions</span>
			<span>Events / Studio</span>
		</div>
	</div>
</footer>
<!-- /wp:html -->
