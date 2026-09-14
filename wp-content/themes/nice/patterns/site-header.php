<?php
/**
 * Title: NICE Site Header
 * Slug: nice/site-header
 * Categories: header
 * Inserter: no
 */

$nice_logo_url        = esc_url( get_theme_file_uri( '/assets/images/nice-logo.png' ) );
$nice_home_url        = esc_url( nice_theme_main_url() );
$nice_events_url      = esc_url( nice_theme_division_url( 'events' ) );
$nice_studio_url      = esc_url( nice_theme_division_url( 'studio' ) );
$nice_clients_url     = esc_url( home_url( '/#clients' ) );
/*
 * The header is shared across divisions, so the drawer lists every channel set
 * that applies: one on a division page, one per division on the main pages.
 */
$nice_channel_sets   = function_exists( 'nice_theme_get_contact_channel_sets' ) ? nice_theme_get_contact_channel_sets() : array();
$nice_label_channels = count( $nice_channel_sets ) > 1;

$nice_is_events = function_exists( 'nice_theme_is_events_context' ) && nice_theme_is_events_context();
$nice_is_studio = function_exists( 'nice_theme_is_studio_context' ) && nice_theme_is_studio_context();
$nice_is_studio_home = function_exists( 'nice_theme_is_studio_home' ) && nice_theme_is_studio_home();
$nice_is_services = is_singular( 'nice_service' ) || is_page( 'services' );
$nice_is_work     = is_singular( 'nice_case_study' ) || is_page( 'case-studies' );
$nice_is_clients  = is_page( 'clients' );
$nice_is_contact  = is_page( 'contact' );
$nice_has_team    = false;

$nice_division = 'global';
if ( $nice_is_events ) {
	$nice_division     = 'events';
	$nice_work_url     = esc_url( nice_theme_division_url( 'events', 'case-studies/' ) );
	$nice_services_url = esc_url( nice_theme_division_url( 'events', 'services/' ) );
	$nice_contact_url  = esc_url( nice_theme_division_url( 'events', 'contact/' ) );
	$nice_clients_url  = esc_url( nice_theme_division_url( 'events', 'clients/' ) );
	$nice_has_team     = function_exists( 'nice_get_team_members_by_division' ) && ! empty( nice_get_team_members_by_division( 'events' ) );
} elseif ( $nice_is_studio ) {
	$nice_division     = 'studio';
	$nice_work_url     = esc_url( nice_theme_division_url( 'studio', 'case-studies/' ) );
	$nice_services_url = esc_url( nice_theme_division_url( 'studio', 'services/' ) );
	$nice_contact_url  = esc_url( nice_theme_division_url( 'studio', 'contact/' ) );
	$nice_clients_url  = esc_url( nice_theme_division_url( 'studio', 'clients/' ) );
	$nice_has_team     = function_exists( 'nice_get_team_members_by_division' ) && ! empty( nice_get_team_members_by_division( 'studio' ) );
} else {
	$nice_work_url     = esc_url( nice_theme_division_url( 'events', 'case-studies/' ) );
	$nice_services_url = esc_url( nice_theme_division_url( 'events', 'services/' ) );
	$nice_contact_url  = esc_url( nice_theme_division_url( 'events', 'contact/' ) );
	$nice_clients_url  = esc_url( nice_theme_division_url( 'events', 'clients/' ) );
}
?>
<!-- wp:html -->
<a class="nice-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'nice' ); ?></a>
<header class="nice-site-header" data-nice-header data-nice-division="<?php echo esc_attr( $nice_division ); ?>">
	<nav class="nice-nav-shell" aria-label="<?php esc_attr_e( 'Primary navigation', 'nice' ); ?>">
		<a class="nice-brand-link" href="<?php echo $nice_home_url; ?>" aria-label="<?php esc_attr_e( 'NICE home', 'nice' ); ?>">
			<img class="nice-logo nice-logo--nav" src="<?php echo $nice_logo_url; ?>" width="1080" height="369" alt="NICE" fetchpriority="auto">
		</a>
		<?php
		/*
		 * The bar's own navigation, shown from 64rem up where the hamburger is
		 * hidden. Deliberately shorter than the drawer: the logo already links
		 * to the gateway, so a "NICE" link here would be the same destination
		 * twice, and Team is omitted for the same reason it is omitted there.
		 */
		?>
		<div class="nice-desktop-nav">
			<?php if ( $nice_is_events ) : ?>
				<a href="<?php echo $nice_events_url; ?>"<?php echo is_page( 'events' ) ? ' aria-current="page"' : ''; ?>>Events</a>
			<?php elseif ( $nice_is_studio ) : ?>
				<a href="<?php echo $nice_studio_url; ?>"<?php echo $nice_is_studio_home ? ' aria-current="page"' : ''; ?>>Studio</a>
			<?php endif; ?>
			<?php if ( $nice_is_events || $nice_is_studio ) : ?>
				<a href="<?php echo $nice_services_url; ?>"<?php echo $nice_is_services ? ' aria-current="page"' : ''; ?>>Services</a>
				<a href="<?php echo $nice_work_url; ?>"<?php echo $nice_is_work ? ' aria-current="page"' : ''; ?>>Work</a>
				<a href="<?php echo $nice_clients_url; ?>"<?php echo $nice_is_clients ? ' aria-current="page"' : ''; ?>>Clients</a>
			<?php else : ?>
				<a href="<?php echo $nice_events_url; ?>">Events</a>
				<a href="<?php echo $nice_studio_url; ?>">Studio</a>
			<?php endif; ?>
		</div>
		<?php if ( $nice_is_events || $nice_is_studio ) : ?>
			<a class="nice-button nice-button--primary nice-nav-cta" href="<?php echo $nice_contact_url; ?>"<?php echo $nice_is_contact ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Contact', 'nice' ); ?></a>
		<?php else : ?>
			<?php
			/*
			 * The gateway owns no contact page, so its button asks which
			 * division first rather than guessing. A disclosure rather than a
			 * link: the choice belongs to the reader, and sending them to Events
			 * by default -- the fallback $nice_contact_url holds here -- would be
			 * picking for them.
			 */
			?>
			<div class="nice-nav-connect" data-nice-connect>
				<button class="nice-button nice-button--primary nice-nav-cta nice-nav-connect__button" type="button" aria-expanded="false" aria-controls="nice-connect-menu" data-nice-connect-toggle>
					<?php esc_html_e( 'Contact', 'nice' ); ?>
					<span class="nice-nav-connect__chevron" aria-hidden="true"></span>
				</button>
				<div class="nice-nav-connect__menu" id="nice-connect-menu" data-nice-connect-menu hidden>
					<a href="<?php echo esc_url( nice_theme_division_url( 'events', 'contact/' ) ); ?>"><?php esc_html_e( 'Connect to Events', 'nice' ); ?></a>
					<a href="<?php echo esc_url( nice_theme_division_url( 'studio', 'contact/' ) ); ?>"><?php esc_html_e( 'Connect to Studio', 'nice' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php
		/*
		 * One control for both directions: the three rules become a cross while
		 * the panel is open, so the button a thumb just pressed is the button
		 * that closes it. The panel therefore carries no close button of its own.
		 */
		?>
		<button class="nice-menu-toggle" type="button" aria-expanded="false" aria-controls="nice-mobile-menu" aria-label="<?php esc_attr_e( 'Open menu', 'nice' ); ?>" data-label-open="<?php esc_attr_e( 'Open menu', 'nice' ); ?>" data-label-close="<?php esc_attr_e( 'Close menu', 'nice' ); ?>" data-nice-menu-open>
			<span class="nice-menu-icon" aria-hidden="true"></span>
		</button>
	</nav>
	<?php
	/*
	 * A disclosure, not a modal. Its control is the bar's toggle, which sits
	 * outside the panel: aria-modal would tell a screen reader to ignore
	 * everything outside, including the only way to close this.
	 */
	?>
	<div class="nice-mobile-menu" id="nice-mobile-menu" data-state="closed" data-nice-mobile-menu aria-label="<?php esc_attr_e( 'Site navigation', 'nice' ); ?>" aria-hidden="true" inert>
		<div class="nice-mobile-menu__links">
			<?php if ( $nice_is_events ) : ?>
				<a href="<?php echo $nice_home_url; ?>">NICE</a>
				<a href="<?php echo $nice_events_url; ?>">Events</a>
				<a href="<?php echo $nice_services_url; ?>">Services</a>
				<a href="<?php echo $nice_work_url; ?>">Work</a>
				<a href="<?php echo $nice_clients_url; ?>">Clients</a>
				<a href="<?php echo $nice_contact_url; ?>">Contact</a>
			<?php elseif ( $nice_is_studio ) : ?>
				<a href="<?php echo $nice_home_url; ?>">NICE</a>
				<a href="<?php echo $nice_studio_url; ?>">Studio</a>
				<a href="<?php echo $nice_services_url; ?>">Services</a>
				<a href="<?php echo $nice_work_url; ?>">Work</a>
				<a href="<?php echo $nice_clients_url; ?>">Clients</a>
				<a href="<?php echo $nice_contact_url; ?>">Contact</a>
			<?php else : ?>
				<a href="<?php echo $nice_events_url; ?>">Events</a>
				<a href="<?php echo $nice_studio_url; ?>">Studio</a>
				<a href="<?php echo $nice_contact_url; ?>">Contact</a>
			<?php endif; ?>
		</div>
		<?php if ( $nice_label_channels ) : ?>
			<?php
			/*
			 * Only on the gateway, where a bare "Contact" link cannot say whose
			 * contact it is. A division's menu already names its own Contact page,
			 * and its WhatsApp and email now live there and on the floating action
			 * rather than being repeated in the drawer.
			 */
			?>
			<div class="nice-mobile-menu__actions" aria-label="<?php esc_attr_e( 'Contact options', 'nice' ); ?>">
				<?php foreach ( $nice_channel_sets as $nice_set ) : ?>
					<a class="nice-button nice-button--secondary" href="<?php echo esc_url( $nice_set['contact_url'] ); ?>" data-nice-contact-division="<?php echo esc_attr( $nice_set['division'] ); ?>"><?php
						/* translators: %s: division label. */
						/* translators: %s: division label. */
						echo esc_html( sprintf( __( 'Connect to %s', 'nice' ), $nice_set['label'] ) );
					?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</header>
<!-- /wp:html -->
