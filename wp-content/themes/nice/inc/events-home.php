<?php
/**
 * Compact Events homepage, with NICE Core supplying business content.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nice_get_events_hero_media( $page_id ) {
	$desktop = absint( get_post_meta( $page_id, '_nice_events_hero_image_id', true ) );
	$mobile  = absint( get_post_meta( $page_id, '_nice_events_hero_mobile_image_id', true ) );
	$x       = nice_get_studio_hero_focal_value( get_post_meta( $page_id, '_nice_events_hero_focal_x', true ) );
	$y       = nice_get_studio_hero_focal_value( get_post_meta( $page_id, '_nice_events_hero_focal_y', true ) );
	if ( ! $desktop || ! wp_attachment_is_image( $desktop ) ) {
		return '';
	}
	$image = wp_get_attachment_image( $desktop, 'full', false, array(
		'class' => 'nice-events-hero__image', 'sizes' => '100vw',
		'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high',
		'style' => sprintf( 'object-position:%d%% %d%%', $x, $y ),
	) );
	if ( ! $image ) {
		return '';
	}
	$source = '';
	if ( $mobile && wp_attachment_is_image( $mobile ) ) {
		$src = wp_get_attachment_image_srcset( $mobile, 'full' ) ?: wp_get_attachment_image_url( $mobile, 'full' );
		if ( $src ) {
			$source = '<source media="(max-width:47.99rem)" sizes="100vw" srcset="' . esc_attr( $src ) . '">';
		}
	}
	return '<picture class="nice-events-hero__media">' . $source . $image . '</picture>';
}

function nice_render_events_home() {
	$page_id  = get_queried_object_id();
	$hero     = nice_get_events_hero_media( $page_id );
	$services = nice_get_events_service_previews();
	$projects = nice_get_events_project_previews();
	$clients  = nice_get_events_clients();
	$actions  = array();
	foreach ( array( 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'phone' => 'Phone' ) as $channel => $label ) {
		$action = nice_get_contact_action( $channel, '#events-contact-details-pending', 'events' );
		if ( ! $action['placeholder'] ) {
			$actions[] = array( 'label' => $label, 'url' => $action['url'] );
		}
	}
	$proof = array();
	if ( function_exists( 'nice_get_case_studies' ) ) {
		foreach ( nice_get_case_studies( array( 'division' => 'events', 'posts_per_page' => -1 ) ) as $case ) {
			$value = get_post_meta( $case->ID, '_nice_proof_value', true );
			$label = get_post_meta( $case->ID, '_nice_proof_label', true );
			if ( $value && $label ) {
				$proof[] = array( 'value' => $value, 'label' => $label, 'title' => $case->post_title );
			}
		}
	}
	ob_start();
	?>
	<div class="nice-events-home">
		<section class="nice-events-hero" id="events-top" aria-labelledby="nice-events-title">
			<?php if ( $hero ) : ?>
				<?php echo $hero; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped attachment markup. ?>
			<?php else : ?>
				<div class="nice-events-hero__media" data-nice-events-hero-empty aria-hidden="true"></div>
			<?php endif; ?>
			<div class="nice-wide nice-events-hero__inner">
				<div class="nice-events-hero__topline">
					<?php if ( $hero && get_post_meta( $page_id, '_nice_events_hero_reference', true ) ) : ?><span class="nice-events-hero__reference">Reference imagery</span><?php endif; ?>
				</div>
				<h1 id="nice-events-title">NICE <span class="nice-events-hero__title-line">Events</span></h1>
				<div class="nice-events-hero__message">
					<p class="nice-events-hero__statement">We create experiences people remember.</p>
					<p class="nice-events-hero__support">Corporate events, exhibitions, conferences and activations planned around the brief and carried through to execution.</p>
					<nav class="nice-events-hero__actions" aria-label="Events page sections">
						<a href="#events-services">Services <span aria-hidden="true">&#8595;</span></a>
						<a href="#events-work">Selected work <span aria-hidden="true">&#8595;</span></a>
					</nav>
				</div>
			</div>
		</section>
		<?php echo nice_render_philosophy_strip(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared static markup. ?>
		<section class="nice-events-section nice-events-services" id="events-services" aria-labelledby="nice-events-services-title">
			<div class="nice-wide">
				<header class="nice-events-section-heading" data-nice-reveal><p class="nice-eyebrow">Services</p><h2 id="nice-events-services-title">Built around<br>the experience.</h2></header>
				<div class="nice-events-services__list">
					<?php foreach ( $services as $index => $service ) : ?>
						<article class="nice-events-service" data-nice-reveal>
							<span class="nice-events-service__index nice-index-dot" aria-hidden="true"></span>
							<h3><?php echo esc_html( $service['name'] ); ?></h3>
							<p><?php echo esc_html( $service['description'] ); ?></p>
							<a class="nice-link" href="<?php echo esc_url( nice_theme_division_url( 'events', 'services/' . $service['slug'] ) ); ?>" aria-label="<?php echo esc_attr( 'Explore ' . $service['name'] ); ?>">Explore <span aria-hidden="true">&#8599;</span></a>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<section class="nice-events-section nice-events-work" id="events-work" aria-labelledby="nice-events-work-title">
			<div class="nice-wide">
				<header class="nice-events-section-heading" data-nice-reveal><p class="nice-eyebrow">Selected work</p><h2 id="nice-events-work-title">Made for<br>the moment.</h2><a class="nice-link" href="<?php echo esc_url( nice_theme_division_url( 'events', 'case-studies/' ) ); ?>">All case studies <span aria-hidden="true">&#8599;</span></a></header>
				<div class="nice-events-work__grid">
					<?php foreach ( $projects as $project ) : ?>
						<article class="nice-events-project" data-nice-reveal>
							<p class="nice-events-project__client"><?php echo esc_html( $project['client'] ); ?></p>
							<h3><?php echo esc_html( $project['title'] ); ?></h3>
							<div class="nice-events-project__media" aria-hidden="true"></div>
							<p><?php echo esc_html( $project['description'] ); ?></p>
							<?php if ( ! empty( $project['url'] ) ) : ?><a class="nice-link" href="<?php echo esc_url( $project['url'] ); ?>">View case study <span aria-hidden="true">&#8599;</span></a><?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<section class="nice-events-section nice-events-method" aria-labelledby="nice-events-method-title">
			<div class="nice-wide">
				<header class="nice-events-section-heading" data-nice-reveal><p class="nice-eyebrow">Our approach</p><h2 id="nice-events-method-title">From possibility<br>to participation.</h2></header>
				<ol class="nice-events-method__steps">
					<?php foreach ( array( 'Emagine' => 'Understand the brand, the audience and what the event needs to achieve.', 'Explore' => 'Develop the format, communication and experience around those objectives.', 'Execute' => 'Bring the plan to life through coordinated production and on-ground delivery.' ) as $step => $description ) : ?>
						<li data-nice-reveal><h3><?php echo esc_html( $step ); ?></h3><p><?php echo esc_html( $description ); ?></p></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
		<section class="nice-events-section nice-events-clients" id="events-clients" aria-labelledby="nice-events-clients-title">
			<div class="nice-wide">
				<header class="nice-events-section-heading" data-nice-reveal><p class="nice-eyebrow">Collaborations</p><h2 id="nice-events-clients-title">Shared experiences.</h2><a class="nice-link" href="<?php echo esc_url( nice_theme_division_url( 'events', 'clients/' ) ); ?>">Client list <span aria-hidden="true">&#8599;</span></a></header>
				<ul class="nice-events-clients__list" data-nice-reveal><?php foreach ( $clients as $client ) : ?><li><?php echo esc_html( $client ); ?></li><?php endforeach; ?></ul>
				<?php if ( $proof ) : ?><div class="nice-events-proof__grid">
					<?php foreach ( $proof as $item ) : ?><article data-nice-reveal><p class="nice-events-proof__number"><?php echo esc_html( $item['value'] ); ?></p><h3><?php echo esc_html( $item['title'] ); ?></h3><p><?php echo esc_html( $item['label'] ); ?></p></article><?php endforeach; ?>
				</div><?php endif; ?>
			</div>
		</section>
		<section class="nice-events-contact nice-events-section" id="events-contact" aria-labelledby="nice-events-contact-title">
			<div class="nice-wide nice-events-contact__inner">
				<div data-nice-reveal><p class="nice-eyebrow">Start a conversation</p><h2 id="nice-events-contact-title">Planning an event?<br>Let's make it NICE.</h2></div>
				<div class="nice-events-contact__actions" data-nice-reveal>
					<a class="nice-link" href="<?php echo esc_url( nice_theme_division_url( 'events', 'contact/' ) ); ?>">Events contact <span aria-hidden="true">&#8599;</span></a>
					<?php foreach ( $actions as $action ) : ?><a class="nice-link" href="<?php echo esc_url( $action['url'] ); ?>"><?php echo esc_html( $action['label'] ); ?> <span aria-hidden="true">&#8599;</span></a><?php endforeach; ?>
					<?php if ( ! $actions ) : ?><p id="events-contact-details-pending">Contact details pending publication approval.</p><?php endif; ?>
				</div>
			</div>
		</section>
	</div>
	<?php
	return ob_get_clean();
}

add_action( 'init', function () {
	register_block_type( 'nice/events-home', array( 'api_version' => 3, 'render_callback' => 'nice_render_events_home' ) );
} );
