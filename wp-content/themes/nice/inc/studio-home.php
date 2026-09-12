<?php
/**
 * Server-rendered Studio Home presentation.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return only approved, currently available contact actions.
 *
 * @return array<int,array{channel:string,label:string,url:string}>
 */
function nice_get_studio_contact_actions() {
	$labels  = array( 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'phone' => 'Phone' );
	$actions = array();

	foreach ( $labels as $channel => $label ) {
		$action = nice_get_contact_action( $channel, '#studio-contact-pending' );

		if ( ! $action['placeholder'] ) {
			$actions[] = array( 'channel' => $channel, 'label' => $label, 'url' => $action['url'] );
		}
	}

	return $actions;
}

/**
 * Return a bounded focal-point value.
 *
 * @param mixed $value Stored value.
 * @return int
 */
function nice_get_studio_hero_focal_value( $value ) {
	return max( 0, min( 100, is_numeric( $value ) ? (int) $value : 50 ) );
}

/**
 * Render the editable Studio hero image.
 *
 * @param int $page_id Studio Page ID.
 * @return string
 */
function nice_get_studio_hero_media( $page_id ) {
	$desktop_id = absint( get_post_meta( $page_id, '_nice_studio_hero_image_id', true ) );
	$mobile_id  = absint( get_post_meta( $page_id, '_nice_studio_hero_mobile_image_id', true ) );
	$focal_x    = nice_get_studio_hero_focal_value( get_post_meta( $page_id, '_nice_studio_hero_focal_x', true ) );
	$focal_y    = nice_get_studio_hero_focal_value( get_post_meta( $page_id, '_nice_studio_hero_focal_y', true ) );

	if ( ! $desktop_id ) {
		return '';
	}

	$alt        = (string) get_post_meta( $desktop_id, '_wp_attachment_image_alt', true );
	$attributes = array(
		'alt'           => $alt,
		'class'         => 'nice-studio-hero__image',
		'decoding'      => 'async',
		'fetchpriority' => 'high',
		'loading'       => 'eager',
		'sizes'         => '100vw',
		'style'         => sprintf( 'object-position:%d%% %d%%', $focal_x, $focal_y ),
	);
	$image      = wp_get_attachment_image( $desktop_id, 'full', false, $attributes );

	if ( ! $image ) {
		return '';
	}

	if ( ! $mobile_id ) {
		return '<picture class="nice-studio-hero__media">' . $image . '</picture>';
	}

	$mobile_src    = wp_get_attachment_image_url( $mobile_id, 'full' );
	$mobile_srcset = wp_get_attachment_image_srcset( $mobile_id, 'full' );
	$source        = $mobile_src ? sprintf(
		'<source media="(max-width:47.99rem)" srcset="%s">',
		esc_attr( $mobile_srcset ?: $mobile_src )
	) : '';

	return '<picture class="nice-studio-hero__media">' . $source . $image . '</picture>';
}

/**
 * Render the Studio Home block.
 *
 * @return string
 */
function nice_render_studio_home() {
	$services       = nice_get_studio_home_services();
	$case_studies   = nice_get_studio_home_case_studies();
	$clients        = nice_get_studio_home_clients();
	$page_id        = get_queried_object_id();
	$hero_image     = nice_get_studio_hero_media( $page_id );
	$hero_reference = (bool) get_post_meta( $page_id, '_nice_studio_hero_reference', true );
	$actions        = nice_get_studio_contact_actions();

	ob_start();
	?>
	<div class="nice-studio-home">
		<section class="nice-studio-hero" id="studio-top" aria-labelledby="nice-studio-title">
			<?php if ( $hero_image ) : ?>
				<?php echo $hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped WordPress attachment markup. ?>
			<?php else : ?>
				<div class="nice-studio-hero__media nice-studio-hero__media--empty" data-nice-studio-hero-empty aria-hidden="true"></div>
			<?php endif; ?>
			<div class="nice-wide nice-studio-hero__inner">
				<div class="nice-studio-hero__topline">
					<p class="nice-eyebrow">NICE / Studio</p>
					<?php if ( $hero_reference ) : ?><span class="nice-studio-hero__reference">Reference imagery</span><?php endif; ?>
				</div>
				<h1 id="nice-studio-title">NICE Studio</h1>
				<div class="nice-studio-hero__statement">
					<p>Stories shaped for every screen.</p>
					<nav class="nice-studio-hero__actions" aria-label="Studio page sections">
						<a href="#studio-services">Services <span aria-hidden="true">&#8595;</span></a>
						<a href="#studio-work">Selected work <span aria-hidden="true">&#8595;</span></a>
					</nav>
				</div>
			</div>
		</section>

		<?php echo nice_render_philosophy_strip(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared static markup. ?>

		<section class="nice-studio-services nice-studio-section" id="studio-services" aria-labelledby="nice-studio-services-title">
			<div class="nice-wide">
				<header class="nice-studio-heading" data-nice-reveal>
					<p class="nice-eyebrow">Studio services</p>
					<h2 id="nice-studio-services-title">Built to move from idea to image.</h2>
				</header>
				<?php if ( $services ) : ?>
					<div class="nice-studio-services__list">
						<?php foreach ( $services as $index => $service ) :
							$service_url = home_url( '/studio/services/' . $service->post_name . '/' );
							?>
							<article class="nice-studio-service" data-nice-studio-service="<?php echo esc_attr( $service->post_name ); ?>" data-nice-reveal>
								<span class="nice-studio-service__index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
								<h3><?php echo esc_html( $service->post_title ); ?></h3>
								<p><?php echo esc_html( $service->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $service->post_content ), 28 ) ); ?></p>
								<a class="nice-studio-service__link" href="<?php echo esc_url( $service_url ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Explore %s', $service->post_title ) ); ?>">Explore <span aria-hidden="true">&#8594;</span></a>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="nice-studio-pending" data-nice-studio-services-pending><p class="nice-eyebrow">Publication pending</p><p>Studio service details are being prepared.</p></div>
				<?php endif; ?>
			</div>
		</section>

		<section class="nice-studio-work nice-studio-section" id="studio-work" aria-labelledby="nice-studio-work-title">
			<div class="nice-wide">
				<header class="nice-studio-heading" data-nice-reveal>
					<p class="nice-eyebrow">Selected Studio work</p>
					<h2 id="nice-studio-work-title">Production with a point of view.</h2>
				</header>
				<?php if ( $case_studies ) : ?>
					<div class="nice-studio-work__grid">
						<?php foreach ( $case_studies as $index => $case_study ) :
							$client       = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : '';
							$service_name = nice_get_studio_case_study_service_name( $case_study->ID );
							$project_url  = home_url( '/studio/case-studies/' . $case_study->post_name . '/' );
						?>
							<article class="nice-studio-project" data-nice-studio-project="<?php echo esc_attr( $case_study->post_name ); ?>" data-nice-reveal>
				<a class="nice-studio-project__media nice-studio-project__media--empty" data-nice-project-media-placeholder href="<?php echo esc_url( $project_url ); ?>" aria-label="<?php echo esc_attr( sprintf( 'View %s case study', $case_study->post_title ) ); ?>">
									<span class="nice-sr-only">Approved project image pending.</span>
								</a>
								<div class="nice-studio-project__meta">
									<span><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
									<?php if ( $service_name ) : ?><span><?php echo esc_html( $service_name ); ?></span><?php endif; ?>
									<?php if ( $client ) : ?><span><?php echo esc_html( $client ); ?></span><?php endif; ?>
								</div>
								<h3><?php echo esc_html( $case_study->post_title ); ?></h3>
								<p><?php echo esc_html( $case_study->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $case_study->post_content ), 30 ) ); ?></p>
								<a class="nice-studio-project__link" href="<?php echo esc_url( $project_url ); ?>">View project <span aria-hidden="true">&#8594;</span></a>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="nice-studio-pending" data-nice-studio-work-pending><p class="nice-eyebrow">Publication pending</p><p>Selected Studio work is being prepared.</p></div>
				<?php endif; ?>
			</div>
		</section>

		<section class="nice-studio-method nice-studio-section" aria-labelledby="nice-studio-method-title">
			<div class="nice-wide nice-studio-method__layout">
				<div class="nice-studio-method__intro" data-nice-reveal>
					<p class="nice-eyebrow">The Studio method</p>
					<h2 id="nice-studio-method-title">From story to screen.</h2>
					<p>NICE brings creative production, design, content strategy and marketing together to shape narratives across film, television, digital channels, stages and social platforms.</p>
					<ol class="nice-studio-method__steps">
						<li><strong>Story</strong></li>
						<li><strong>Production</strong></li>
						<li><strong>Screen</strong></li>
					</ol>
				</div>
				<ul class="nice-studio-capability-list" data-nice-reveal>
					<li><span>01</span>Film Productions</li>
					<li><span>02</span>Corporate AVs</li>
					<li><span>03</span>Product Photo-shoots</li>
					<li><span>04</span>Training Visual Aid / Whiteboard Animations</li>
					<li><span>05</span>Digital Storytelling</li>
				</ul>
			</div>
		</section>

		<section class="nice-studio-clients nice-studio-section" id="studio-clients" aria-labelledby="nice-studio-clients-title">
			<div class="nice-wide">
				<header class="nice-studio-heading" data-nice-reveal>
					<p class="nice-eyebrow">Shared collaborations</p>
					<h2 id="nice-studio-clients-title">One NICE client community.</h2>
				</header>
				<?php if ( $clients ) : ?>
					<ul class="nice-studio-clients__list" data-nice-reveal>
						<?php foreach ( $clients as $client ) :
							$client_url = esc_url( get_post_meta( $client->ID, '_nice_client_url', true ) );
						?>
							<li><?php if ( $client_url ) : ?><a href="<?php echo $client_url; ?>"><?php echo esc_html( $client->post_title ); ?></a><?php else : ?><?php echo esc_html( $client->post_title ); ?><?php endif; ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<div class="nice-studio-pending" data-nice-studio-clients-pending><p>Approved client details are being prepared.</p></div>
				<?php endif; ?>
			</div>
		</section>

		<section class="nice-studio-contact" id="studio-contact" aria-labelledby="nice-studio-contact-title">
			<div class="nice-wide nice-studio-contact__inner">
				<div data-nice-reveal>
					<p class="nice-eyebrow">Start a conversation</p>
					<h2 id="nice-studio-contact-title">Have a story to tell?</h2>
				</div>
				<?php if ( $actions ) : ?>
					<div class="nice-studio-contact__actions" aria-label="Approved contact options" data-nice-reveal>
						<?php foreach ( $actions as $action ) : ?>
							<a class="nice-button nice-button--primary" href="<?php echo esc_url( $action['url'] ); ?>" data-nice-contact-channel="<?php echo esc_attr( $action['channel'] ); ?>"><?php echo esc_html( $action['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="nice-studio-contact__pending" id="studio-contact-pending" data-nice-studio-contact-pending data-nice-reveal>
						<p class="nice-eyebrow">Publication pending</p>
						<p>Approved contact channels will appear here when they are ready.</p>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Register the request-time Studio Home block.
 */
function nice_register_studio_home_block() {
	register_block_type(
		'nice/studio-home',
		array(
			'render_callback' => 'nice_render_studio_home',
		)
	);
}
add_action( 'init', 'nice_register_studio_home_block' );
