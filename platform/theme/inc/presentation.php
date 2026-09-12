<?php
/** Presentation adapters. Every content/media value belongs to this installation. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function nice_platform_theme_role() {
	return function_exists( 'nice_platform_role' ) ? nice_platform_role() : ( defined( 'NICE_SITE_ROLE' ) && in_array( NICE_SITE_ROLE, array( 'main', 'events', 'studio' ), true ) ? NICE_SITE_ROLE : 'main' );
}

function nice_platform_theme_url( $role, $path = '' ) {
	if ( function_exists( 'nice_platform_site_url' ) ) {
		return nice_platform_site_url( $role, $path );
	}
	$origins = array( 'main' => 'https://nicesolutions.in/', 'events' => 'https://events.nicesolutions.in/', 'studio' => 'https://studios.nicesolutions.in/' );
	foreach ( $origins as $site => $origin ) {
		$key = 'NICE_' . strtoupper( $site ) . '_URL';
		if ( defined( $key ) ) { $origins[ $site ] = constant( $key ); }
	}
	return trailingslashit( $origins[ $role ] ?? $origins['main'] ) . ltrim( $path, '/' );
}

function nice_platform_theme_arrow( $diagonal = false ) {
	return '<span class="link-arrow" aria-hidden="true">' . ( $diagonal ? '&#8599;' : '&#8594;' ) . '</span>';
}

function nice_platform_theme_media( $slot_name, $class = '', $priority = false ) {
	$slot = function_exists( 'nice_platform_media_slot' ) ? nice_platform_media_slot( $slot_name ) : array();
	$id = absint( $slot['id'] ?? 0 );
	$mobile_id = absint( $slot['mobile_id'] ?? 0 );
	$x = max( 0, min( 100, (float) ( $slot['x'] ?? 50 ) ) );
	$y = max( 0, min( 100, (float) ( $slot['y'] ?? 50 ) ) );
	$has_image = $id && wp_attachment_is_image( $id );
	ob_start(); ?>
	<div class="platform-media <?php echo esc_attr( $class . ( $has_image ? '' : ' is-empty' ) ); ?>" style="--focal-x:<?php echo esc_attr( $x ); ?>%;--focal-y:<?php echo esc_attr( $y ); ?>%">
		<?php if ( $has_image ) : ?>
			<picture>
				<?php if ( $mobile_id && wp_attachment_is_image( $mobile_id ) ) : ?>
					<source media="(max-width: 767px)" srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $mobile_id, 'large' ) ?: wp_get_attachment_image_url( $mobile_id, 'large' ) ); ?>" sizes="100vw">
				<?php endif; ?>
				<?php echo wp_get_attachment_image( $id, 'full', false, array( 'loading' => $priority ? 'eager' : 'lazy', 'decoding' => 'async', 'fetchpriority' => $priority ? 'high' : 'auto', 'sizes' => 'hero' === $slot_name ? '100vw' : '(min-width: 900px) 50vw, 100vw' ) ); ?>
			</picture>
		<?php endif; ?>
		<?php if ( $has_image && ! empty( $slot['reference'] ) ) : ?><span class="reference-caption">Reference imagery</span><?php endif; ?>
	</div>
	<?php return ob_get_clean();
}

function nice_platform_theme_navigation() {
	$main = nice_platform_theme_url( 'main' );
	return array(
		'Work' => $main . '#work',
		'Events' => nice_platform_theme_url( 'events' ),
		'Studio' => nice_platform_theme_url( 'studio' ),
		'About' => $main . '#about',
		'Contact' => $main . '#contact',
	);
}

function nice_platform_render_header() {
	$role = nice_platform_theme_role();
	$logo = get_template_directory_uri() . '/assets/images/nice-logo.png';
	$navigation = nice_platform_theme_navigation();
	ob_start(); ?>
	<header class="site-header <?php echo ( 'main' === $role && is_front_page() ) ? 'has-hero' : 'is-solid'; ?>" data-site-header>
		<div class="site-header__inner">
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( 'main' === $role ? 'NICE Solutions home' : 'NICE ' . ucfirst( $role ) . ' home' ); ?>">
				<img src="<?php echo esc_url( $logo ); ?>" alt="NICE" width="1080" height="371"><span><?php echo esc_html( 'main' === $role ? 'Solutions' : ucfirst( $role ) ); ?></span>
			</a>
			<nav class="desktop-navigation" aria-label="Primary navigation">
				<?php foreach ( $navigation as $label => $url ) : ?><a href="<?php echo esc_url( $url ); ?>" class="<?php echo 'Contact' === $label ? 'navigation-contact' : ''; ?>"><?php echo esc_html( $label ); ?><?php if ( 'Contact' === $label ) { echo nice_platform_theme_arrow( true ); } ?></a><?php endforeach; ?>
			</nav>
			<button class="menu-toggle" data-menu-toggle aria-controls="nice-menu" aria-expanded="false" aria-label="Open menu"><span class="menu-toggle__lines" aria-hidden="true"></span><span>Menu</span></button>
		</div>
	</header>
	<div id="nice-menu" class="site-menu" role="dialog" aria-modal="true" aria-label="NICE navigation" hidden>
		<div class="site-menu__top"><a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="NICE home"><img src="<?php echo esc_url( $logo ); ?>" alt="NICE" width="1080" height="371"></a><button class="menu-close" data-menu-close aria-label="Close menu"><span aria-hidden="true">&#215;</span></button></div>
		<nav aria-label="Mobile navigation"><?php $index = 1; foreach ( $navigation as $label => $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><span class="menu-index">0<?php echo (int) $index++; ?></span><span><?php echo esc_html( $label ); ?></span><?php echo nice_platform_theme_arrow( true ); ?></a><?php endforeach; ?></nav>
		<p class="site-menu__signature">Nucleus Integrated Communication<br>&amp; Entertainment Pvt. Ltd.</p>
	</div>
	<?php return ob_get_clean();
}

function nice_platform_theme_project( $post, $index ) {
	$division = get_post_meta( $post->ID, '_nice_division', true );
	$division = 'studio' === $division ? 'studio' : 'events';
	$url = function_exists( 'nice_platform_preview_url' ) ? nice_platform_preview_url( $post ) : nice_platform_theme_url( $division );
	$id = get_post_thumbnail_id( $post );
	$reference = get_post_meta( $post->ID, '_nice_reference', true ) || get_post_meta( $id, '_nice_runtime_reference_source', true );
	$has_image = $id && wp_attachment_is_image( $id ) && ! $reference;
	$media_tag = $url ? 'a' : 'div';
	ob_start(); ?>
	<article class="project project--<?php echo 0 === $index ? 'featured' : 'secondary'; ?>" data-reveal>
		<<?php echo $media_tag; ?> class="project__media <?php echo $has_image ? '' : 'is-empty'; ?>" <?php if ( $url ) : ?>href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( 'View ' . get_the_title( $post ) ); ?>"<?php endif; ?>>
			<?php if ( $has_image ) { echo wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'lazy', 'sizes' => '(min-width: 900px) 65vw, 100vw' ) ); } else { ?><span class="project__empty-mark" aria-hidden="true">N<span class="project__empty-line"></span></span><?php } ?>
			<span class="project__number" aria-hidden="true">0<?php echo (int) $index + 1; ?></span>
		</<?php echo $media_tag; ?>>
		<div class="project__details"><p class="eyebrow"><span class="red-tick" aria-hidden="true"></span>NICE <?php echo esc_html( ucfirst( $division ) ); ?></p><h3><?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php endif; ?><?php echo esc_html( get_the_title( $post ) ); ?><?php if ( $url ) : ?></a><?php endif; ?></h3><p class="project__description"><?php echo esc_html( $post->post_excerpt ); ?></p><?php if ( $url ) : ?><a class="text-link" href="<?php echo esc_url( $url ); ?>">View project<?php echo nice_platform_theme_arrow( true ); ?></a><?php endif; ?></div>
	</article>
	<?php return ob_get_clean();
}

function nice_platform_render_gateway() {
	if ( 'main' !== nice_platform_theme_role() ) { return nice_platform_render_page(); }
	$content = function_exists( 'nice_platform_home_content' ) ? nice_platform_home_content() : array();
	$value = function ( $key, $default = '' ) use ( $content ) { return $content[ $key ] ?? $default; };
	$projects = function_exists( 'nice_platform_get_previews' ) ? nice_platform_get_previews() : array();
	$clients = function_exists( 'nice_platform_client_names' ) ? nice_platform_client_names() : array();
	ob_start(); ?>
	<main id="main-content" class="gateway">
		<section class="hero" aria-labelledby="hero-title" data-hero>
			<?php echo nice_platform_theme_media( 'hero', 'hero__media', true ); ?>
			<div class="hero__shade" aria-hidden="true"></div>
			<div class="hero__content page-width">
				<p class="eyebrow hero__eyebrow"><span class="red-tick" aria-hidden="true"></span>Live experiences. Moving stories.</p>
				<h1 id="hero-title"><?php echo esc_html( $value( 'hero_title', 'NICE Solutions' ) ); ?><span class="hero__period" aria-hidden="true">.</span></h1>
				<div class="hero__bottom"><p><?php echo esc_html( $value( 'hero_description', 'Events and creative production. From the first idea to the final moment.' ) ); ?></p><div class="hero__links"><a href="<?php echo esc_url( nice_platform_theme_url( 'events' ) ); ?>">Explore Events<?php echo nice_platform_theme_arrow( true ); ?></a><a href="<?php echo esc_url( nice_platform_theme_url( 'studio' ) ); ?>">Explore Studio<?php echo nice_platform_theme_arrow( true ); ?></a></div></div>
			</div>
		</section>
		<div class="intro-strip page-width"><p>One company. Two creative disciplines.</p><a href="#work" class="eyebrow">Discover NICE <span aria-hidden="true">&#8595;</span></a></div>
		<?php if ( $projects ) : ?>
		<section id="work" class="work-section page-width section-space" aria-labelledby="work-title">
			<div class="section-heading" data-reveal><p class="eyebrow section-label"><span class="red-tick" aria-hidden="true"></span>01 / Selected work</p><h2 id="work-title">Ideas, made real.</h2><p>A selection of experiences and stories<br class="desktop-break"> from across NICE.</p></div>
			<div class="project-grid"><?php foreach ( array_values( $projects ) as $index => $project ) { echo nice_platform_theme_project( $project, $index ); } ?></div>
		</section>
		<?php else : ?><span id="work" class="anchor-target"></span><?php endif; ?>
		<section id="divisions" class="division-section section-space" aria-labelledby="division-title">
			<div class="page-width"><div class="section-heading" data-reveal><p class="eyebrow section-label"><span class="red-tick" aria-hidden="true"></span>02 / Our worlds</p><h2 id="division-title">In the room.<br>On the screen.</h2><p>Different disciplines.<br>One thoughtful approach.</p></div>
			<div class="division-grid">
				<?php foreach ( array( 'events' => 'Events', 'studio' => 'Studio' ) as $role => $label ) : ?>
				<article class="division division--<?php echo esc_attr( $role ); ?>" data-reveal><a class="division__image-link" href="<?php echo esc_url( nice_platform_theme_url( $role ) ); ?>" aria-label="<?php echo esc_attr( 'Explore NICE ' . $label ); ?>"><?php echo nice_platform_theme_media( $role, 'division__media' ); ?></a><div class="division__heading"><h3><?php echo esc_html( $label ); ?></h3><span class="eyebrow"><?php echo 'events' === $role ? 'Live &amp; experiential' : 'Film &amp; digital'; ?></span></div><p><?php echo esc_html( $value( $role . '_description' ) ); ?></p><a class="text-link" href="<?php echo esc_url( nice_platform_theme_url( $role ) ); ?>">Explore <?php echo esc_html( $label ); ?><?php echo nice_platform_theme_arrow( true ); ?></a></article>
				<?php endforeach; ?>
			</div></div>
		</section>
		<section id="about" class="about-section page-width section-space" aria-labelledby="about-title">
			<div class="about-intro" data-reveal><p class="eyebrow section-label"><span class="red-tick" aria-hidden="true"></span>03 / The NICE approach</p><div><h2 id="about-title"><?php echo esc_html( $value( 'about_heading', 'Good work starts with a conversation.' ) ); ?></h2><p class="about-body"><?php echo esc_html( $value( 'about_body' ) ); ?></p></div></div>
			<div class="process-grid"><?php foreach ( array( 'brief' => 'Brief.', 'idea' => 'Idea.', 'solution' => 'Solution.' ) as $key => $title ) : ?><div class="process-step" data-reveal><span class="process-step__line" aria-hidden="true"></span><h3><?php echo esc_html( $title ); ?></h3><p><?php echo esc_html( $value( $key . '_body' ) ); ?></p></div><?php endforeach; ?></div>
		</section>
		<?php if ( $clients ) : ?><section class="clients-section page-width" aria-labelledby="clients-title"><div class="clients-intro"><h2 class="eyebrow" id="clients-title">Some of the names<br>we've worked with.</h2><span aria-hidden="true">&#8594;</span></div><ul class="client-list"><?php foreach ( $clients as $client ) : ?><li><?php echo esc_html( $client ); ?></li><?php endforeach; ?></ul></section><?php endif; ?>
		<section id="contact" class="contact-section" aria-labelledby="contact-title"><div class="page-width"><p class="eyebrow"><span class="red-tick" aria-hidden="true"></span>Start a conversation</p><div class="contact-section__heading" data-reveal><h2 id="contact-title">Have something<br>in mind?</h2><span class="contact-section__arrow" aria-hidden="true">&#8599;</span></div><div class="contact-options"><a href="<?php echo esc_url( nice_platform_theme_url( 'events', 'contact/' ) ); ?>"><span>Let's create an experience.</span><strong>Talk Events</strong><?php echo nice_platform_theme_arrow( true ); ?></a><a href="<?php echo esc_url( nice_platform_theme_url( 'studio', 'contact/' ) ); ?>"><span>Let's tell your story.</span><strong>Talk Studio</strong><?php echo nice_platform_theme_arrow( true ); ?></a></div><?php echo nice_platform_theme_contact_links(); ?></div></section>
	</main>
	<?php return ob_get_clean();
}

function nice_platform_theme_contact_links() {
	$contact = function_exists( 'nice_platform_contact' ) ? nice_platform_contact() : array();
	$links = array();
	$email = $contact['email'] ?? $contact['email_address'] ?? '';
	$whatsapp = $contact['whatsapp'] ?? $contact['whatsapp_url'] ?? '';
	if ( is_email( $email ) ) { $links[ $email ] = 'mailto:' . $email; }
	if ( $whatsapp && wp_http_validate_url( $whatsapp ) ) { $links['WhatsApp'] = $whatsapp; }
	if ( ! empty( $contact['phone'] ) ) { $links[ $contact['phone'] ] = 'tel:' . preg_replace( '/[^+0-9]/', '', $contact['phone'] ); }
	if ( ! $links ) { return ''; }
	ob_start(); ?><div class="direct-contact"><?php foreach ( $links as $label => $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?><?php echo nice_platform_theme_arrow( true ); ?></a><?php endforeach; ?></div><?php return ob_get_clean();
}

function nice_platform_render_footer() {
	$contact = function_exists( 'nice_platform_contact' ) ? nice_platform_contact() : array();
	ob_start(); ?>
	<footer class="site-footer"><div class="page-width">
		<div class="footer-top">
			<a class="footer-brand" href="<?php echo esc_url( nice_platform_theme_url( 'main' ) ); ?>" aria-label="NICE Solutions home">NICE<span class="red-period">.</span></a>
			<p>Nucleus Integrated Communication<br>&amp; Entertainment Pvt. Ltd.</p>
			<nav aria-label="Footer navigation">
				<a href="<?php echo esc_url( nice_platform_theme_url( 'events' ) ); ?>">Events<?php echo nice_platform_theme_arrow( true ); ?></a>
				<a href="<?php echo esc_url( nice_platform_theme_url( 'studio' ) ); ?>">Studio<?php echo nice_platform_theme_arrow( true ); ?></a>
				<a href="<?php echo esc_url( nice_platform_theme_url( 'main', '#about' ) ); ?>">About NICE</a>
				<?php foreach ( $contact['social'] ?? array() as $social ) : ?>
					<a href="<?php echo esc_url( $social ); ?>" rel="me"><?php echo esc_html( preg_replace( '/^www\./', '', wp_parse_url( $social, PHP_URL_HOST ) ?: 'Social profile' ) ); ?><?php echo nice_platform_theme_arrow( true ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>
		<div class="footer-bottom"><small>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> NICE Solutions</small><span class="eyebrow">Brief. Idea. Solution.</span><a class="back-top" href="#main-content">Back to top <span aria-hidden="true">&#8593;</span></a></div>
	</div></footer>
	<?php return ob_get_clean();
}

function nice_platform_render_page() {
	$role = nice_platform_theme_role();
	ob_start(); ?>
	<main id="main-content" class="standard-page page-width">
		<p class="eyebrow"><span class="red-tick" aria-hidden="true"></span><?php echo esc_html( 'NICE ' . ( 'main' === $role ? 'Solutions' : ucfirst( $role ) ) ); ?></p>
		<?php if ( is_404() ) : ?><h1>Page not found.</h1><p>This page isn't available at this address.</p><a class="text-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to home<?php echo nice_platform_theme_arrow(); ?></a>
		<?php elseif ( 'main' !== $role && is_front_page() ) : ?><h1>NICE <?php echo esc_html( ucfirst( $role ) ); ?>.</h1><p><?php echo 'events' === $role ? 'Live experiences, from brief to delivery.' : 'Creative production, from idea to screen.'; ?></p><a class="text-link" href="<?php echo esc_url( nice_platform_theme_url( 'main' ) ); ?>">Explore NICE Solutions<?php echo nice_platform_theme_arrow( true ); ?></a>
		<?php else : ?><h1><?php echo esc_html( get_the_title() ); ?></h1><div class="page-content"><?php echo apply_filters( 'the_content', get_post_field( 'post_content', get_queried_object_id() ) ); ?></div><?php echo nice_platform_theme_contact_links(); ?><?php endif; ?>
	</main>
	<?php return ob_get_clean();
}
