<?php
/**
 * Title: NICE Site Footer
 * Slug: nice/site-footer
 * Categories: footer
 * Inserter: no
 */

$nice_logo_url        = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
$nice_home_url        = esc_url( nice_theme_main_url() );
$nice_events_url      = esc_url( nice_theme_division_url( 'events' ) );
$nice_studio_url      = esc_url( nice_theme_division_url( 'studio' ) );
$nice_clients_url     = esc_url( home_url( '/#clients' ) );
/*
 * The footer is shared, so it resolves every channel set that applies. A
 * division page yields one set; the main pages yield one per publishing
 * division, and each link is then prefixed with its division label.
 */
$nice_channel_sets     = function_exists( 'nice_theme_get_contact_channel_sets' ) ? nice_theme_get_contact_channel_sets() : array();
$nice_label_channels   = count( $nice_channel_sets ) > 1;
$nice_social_profiles  = function_exists( 'nice_theme_get_social_profiles' ) ? nice_theme_get_social_profiles() : array();

$nice_is_events = function_exists( 'nice_theme_is_events_context' ) && nice_theme_is_events_context();
$nice_is_studio = function_exists( 'nice_theme_is_studio_context' ) && nice_theme_is_studio_context();

if ( $nice_is_events ) {
	$nice_clients_url = esc_url( nice_theme_division_url( 'events', 'clients/' ) );
	$nice_work_url     = esc_url( nice_theme_division_url( 'events', 'case-studies/' ) );
	$nice_services_url = esc_url( nice_theme_division_url( 'events', 'services/' ) );
	$nice_contact_url  = esc_url( nice_theme_division_url( 'events', 'contact/' ) );
} elseif ( $nice_is_studio ) {
	$nice_work_url     = esc_url( nice_theme_division_url( 'studio', 'case-studies/' ) );
	$nice_services_url = esc_url( nice_theme_division_url( 'studio', 'services/' ) );
	$nice_contact_url  = esc_url( nice_theme_division_url( 'studio', 'contact/' ) );
	$nice_clients_url  = esc_url( nice_theme_division_url( 'studio', 'clients/' ) );
} else {
	$nice_work_url     = esc_url( nice_theme_division_url( 'events', 'case-studies/' ) );
	$nice_services_url = esc_url( nice_theme_division_url( 'events', 'services/' ) );
	$nice_contact_url  = esc_url( nice_theme_division_url( 'events', 'contact/' ) );
	$nice_clients_url  = esc_url( nice_theme_division_url( 'events', 'clients/' ) );
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
				<?php if ( $nice_label_channels ) : ?>
					<?php
					/*
					 * Outside a division a single "Contact" link has to pick a side,
					 * and four raw channels leave the reader working out whose is
					 * whose. Name the two destinations instead; each contact page
					 * presents that division's channels in full.
					 */
					foreach ( $nice_channel_sets as $nice_set ) :
						?>
						<a href="<?php echo esc_url( $nice_set['contact_url'] ); ?>" data-nice-contact-division="<?php echo esc_attr( $nice_set['division'] ); ?>"><?php
							/* translators: %s: division label. */
							echo esc_html( sprintf( __( 'Contact %s', 'nice' ), $nice_set['label'] ) );
						?></a>
					<?php endforeach; ?>
				<?php else : ?>
					<a href="<?php echo $nice_contact_url; ?>">Contact</a>
					<?php
					/*
					 * A fresh installation has no approved channels at all, so the
					 * set can be empty. The contact page link above still stands;
					 * only the direct channels are conditional.
					 */
					if ( $nice_channel_sets ) :
						$nice_set           = $nice_channel_sets[0];
						$nice_division_attr = $nice_set['division'] ? ' data-nice-contact-division="' . esc_attr( $nice_set['division'] ) . '"' : '';
						?>
						<?php if ( $nice_set['whatsapp_url'] ) : ?><a href="<?php echo esc_url( $nice_set['whatsapp_url'] ); ?>" data-nice-contact-channel="whatsapp"<?php echo $nice_division_attr; ?>>WhatsApp</a><?php endif; ?>
						<?php if ( $nice_set['email_address'] ) : ?><a href="<?php echo esc_url( 'mailto:' . $nice_set['email_address'] ); ?>" data-nice-contact-channel="email"<?php echo $nice_division_attr; ?>>Email</a><?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</nav>
			<?php if ( $nice_social_profiles ) : ?>
				<nav class="nice-footer-nav nice-footer-social" aria-label="<?php esc_attr_e( 'Social profiles', 'nice' ); ?>">
					<span class="nice-eyebrow">Follow</span>
					<?php foreach ( $nice_social_profiles as $nice_profile ) : ?>
						<a href="<?php echo esc_url( $nice_profile['url'] ); ?>" rel="me noopener" data-nice-social="<?php echo esc_attr( $nice_profile['key'] ); ?>"><?php echo esc_html( $nice_profile['label'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
		<div class="nice-footer-meta">
			<span>&copy; NICE Solutions</span>
			<span>Events / Studio</span>
		</div>
	</div>
</footer>
<!-- /wp:html -->
