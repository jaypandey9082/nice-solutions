<?php
/**
 * Server-rendered Events inner-page presentation.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the current request belongs to the Events section.
 *
 * @return bool
 */
function nice_theme_is_events_context() {
	/*
	 * A dedicated events installation owns its hostname, so every page it serves
	 * belongs to the division and there is no path prefix to look for.
	 */
	if ( function_exists( 'nice_get_site_division' ) && 'events' === nice_get_site_division() ) {
		return true;
	}

	if ( is_singular( array( 'nice_service', 'nice_case_study' ) ) ) {
		return has_term( 'events', 'nice_division', get_queried_object_id() );
	}

	if ( ! is_page() ) {
		return false;
	}

	$prefix = function_exists( 'nice_get_division_prefix' ) ? nice_get_division_prefix( 'events' ) : 'events';

	if ( ! $prefix ) {
		return false;
	}

	$page_path = get_page_uri( get_queried_object_id() );

	return $prefix === $page_path || str_starts_with( $page_path, $prefix . '/' );
}

/**
 * Determine whether the request is an Events inner page.
 *
 * @return bool
 */
function nice_theme_is_events_inner_page() {
	return nice_theme_is_events_context() && ! is_page( 'events' );
}

/**
 * Return a safe Events content URL.
 *
 * @param WP_Post $post Service or Case Study.
 * @return string
 */
function nice_theme_get_events_content_url( $post ) {
	if ( function_exists( 'nice_get_content_url' ) ) {
		return nice_get_content_url( $post );
	}

	if ( function_exists( 'nice_get_events_content_url' ) ) {
		return nice_get_events_content_url( $post );
	}

	return '';
}

/**
 * Return one content item's first taxonomy term.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy name.
 * @return WP_Term|null
 */
function nice_theme_get_primary_term( $post_id, $taxonomy ) {
	$terms = wp_get_object_terms( $post_id, $taxonomy );

	return ! is_wp_error( $terms ) && ! empty( $terms ) ? $terms[0] : null;
}

/**
 * Report whether a record's own media is cleared for publication.
 *
 * Attaching a featured image is not the same as holding the right to publish it.
 * Migrated deck photography sits on these records awaiting clearance, so the
 * hero renders only once an editor ticks "Media cleared for publication".
 *
 * This is deliberately not the source approval status: that clears the wording
 * and its provenance, and approving copy must not publish a picture with it.
 *
 * @param int $post_id Record ID.
 * @return bool
 */
function nice_theme_media_approved( $post_id ) {
	return (bool) get_post_meta( $post_id, '_nice_media_approved', true );
}

/**
 * Return responsive featured-image markup.
 *
 * @param int                  $post_id Post ID.
 * @param string               $sizes   Responsive sizes value.
 * @param array<string, mixed> $attrs   Additional attributes.
 * @return string
 */
function nice_theme_get_featured_image( $post_id, $sizes, $attrs = array() ) {
	$attachment_id = get_post_thumbnail_id( $post_id );

	if ( ! $attachment_id ) {
		return '';
	}

	return wp_get_attachment_image(
		$attachment_id,
		'full',
		false,
		wp_parse_args(
			$attrs,
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => $sizes,
			)
		)
	);
}

/**
 * Render the shared Events section navigation.
 *
 * @return string
 */
function nice_render_events_section_navigation() {
	return '';
}

/**
 * Render a full-bleed Events inner-page hero.
 *
 * @param string $eyebrow Introductory label.
 * @param string $title    Page title.
 * @param string $intro    Introductory copy.
 * @param int    $post_id  Reserved for backwards-compatible block calls.
 */
function nice_render_events_inner_hero( $eyebrow, $title, $intro, $post_id = 0 ) {
	?>
	<header class="nice-events-inner-hero">
		<div class="nice-wide nice-events-inner-hero__content" data-nice-reveal>
			<?php if ( $eyebrow ) : ?>
				<p class="nice-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $intro ) : ?>
			<?php
			/*
			 * One <p> per paragraph. Every other caller passes a single line, so
			 * this changes nothing for them -- but a Case Study now leads with its
			 * whole write-up, and two paragraphs run into one without this.
			 */
			foreach ( preg_split( '/\n\s*\n/', trim( (string) $intro ) ) as $nice_intro_paragraph ) :
				if ( '' === trim( $nice_intro_paragraph ) ) {
					continue;
				}
				?>
				<p><?php echo esc_html( $nice_intro_paragraph ); ?></p>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</header>
	<?php
	echo nice_render_philosophy_strip(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared static markup.
}

/**
 * Report whether a theme image is actually present.
 *
 * Production packages ship without the deck photography that has not been
 * cleared for publication, so a pattern that falls back to a theme file has to
 * check rather than assume. Where the file is absent the placeholder stands in,
 * which is what an uncleared attachment already produces.
 *
 * @param string $filename Image filename inside /assets/images/.
 * @return bool
 */
function nice_theme_image_exists( $filename ) {
	static $cache = array();

	$filename = ltrim( (string) $filename, '/' );

	if ( ! isset( $cache[ $filename ] ) ) {
		$cache[ $filename ] = '' !== $filename && file_exists( get_theme_file_path( '/assets/images/' . $filename ) );
	}

	return $cache[ $filename ];
}

/**
 * Render an intentional media field while project imagery awaits approval.
 *
 * @param string $label Accessible description for the empty media field.
 */
function nice_render_events_media_placeholder( $label = 'Project media pending approval' ) {
	?>
	<div class="nice-events-media-placeholder" role="img" aria-label="<?php echo esc_attr( $label ); ?>">
		<span aria-hidden="true">Media pending approval</span>
	</div>
	<?php
}

/**
 * Render a Case Study preview from one CMS record.
 *
 * @param WP_Post $case_study Case Study post.
 * @param string  $class_name Optional layout modifier.
 */
function nice_render_case_study_preview( $case_study, $class_name = '' ) {
	$url         = nice_theme_get_events_content_url( $case_study );
	$client      = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : '';
	$location    = get_post_meta( $case_study->ID, '_nice_location', true );
	$year        = (int) get_post_meta( $case_study->ID, '_nice_year', true );
	$description = $case_study->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $case_study->post_content ), 30 );
	?>
	<article class="nice-events-case-preview <?php echo esc_attr( $class_name ); ?>" data-nice-reveal>
		<div class="nice-events-case-preview__media">
			<?php
			$preview_image = function_exists( 'nice_theme_get_case_study_card_image' )
				? nice_theme_get_case_study_card_image( $case_study->ID, '(min-width: 48rem) 45vw, 100vw' )
				: '';

			if ( $preview_image ) {
				echo $preview_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_get_attachment_image().
			} else {
				nice_render_events_media_placeholder( sprintf( '%s project media pending approval', $case_study->post_title ) );
			}
			?>
		</div>
		<div class="nice-events-case-preview__body">
			<?php if ( $client ) : ?><p class="nice-eyebrow"><?php echo esc_html( $client ); ?></p><?php endif; ?>
			<h3><?php echo esc_html( $case_study->post_title ); ?></h3>
			<?php if ( $location || $year ) : ?>
				<p class="nice-events-meta-line"><?php echo esc_html( implode( ' / ', array_filter( array( $location, $year ?: '' ) ) ) ); ?></p>
			<?php endif; ?>
			<?php if ( $description ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?>
			<?php if ( $url ) : ?><a class="nice-link" href="<?php echo esc_url( $url ); ?>">View case study <span aria-hidden="true">-&gt;</span></a><?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Render the reusable Events contact transition.
 *
 * @param array<string, string> $args Optional custom CTA settings.
 */
function nice_render_events_inner_contact_cta( $args = array() ) {
	$eyebrow         = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : 'Start a conversation';
	$heading         = ! empty( $args['heading'] ) ? $args['heading'] : 'Have an Events brief?';
	$cta_label       = ! empty( $args['cta_label'] ) ? $args['cta_label'] : "Let's make it NICE";
	$cta_url         = ! empty( $args['cta_url'] ) ? $args['cta_url'] : nice_theme_division_url( 'events', 'contact/' );
	$whatsapp_action = function_exists( 'nice_get_contact_action' ) ? nice_get_contact_action( 'whatsapp', '', 'events' ) : null;
	$email_action    = function_exists( 'nice_get_contact_action' ) ? nice_get_contact_action( 'email', '', 'events' ) : null;
	?>
	<section class="nice-events-inner-cta" aria-labelledby="nice-events-inner-cta-title">
		<div class="nice-wide nice-events-inner-cta__content" data-nice-reveal>
			<div class="nice-events-inner-cta__lead">
				<p class="nice-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<h2 id="nice-events-inner-cta-title" class="nice-editorial" data-nice-editorial-reveal><?php echo esc_html( $heading ); ?></h2>
			</div>
			<div class="nice-events-inner-cta__actions">
				<a class="nice-button nice-button--primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?> <span aria-hidden="true">-&gt;</span></a>
				<?php if ( $whatsapp_action && ! $whatsapp_action['placeholder'] ) : ?>
					<a class="nice-button nice-button--secondary" href="<?php echo esc_url( $whatsapp_action['url'] ); ?>" data-nice-contact-channel="whatsapp">WhatsApp</a>
				<?php endif; ?>
				<?php if ( $email_action && ! $email_action['placeholder'] ) : ?>
					<a class="nice-button nice-button--secondary" href="<?php echo esc_url( $email_action['url'] ); ?>" data-nice-contact-channel="email">Email</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render the Events Services index.
 *
 * @return string
 */
function nice_render_events_services_index() {
	$services = function_exists( 'nice_get_events_services' ) ? nice_get_events_services() : array();

	ob_start();
	nice_render_events_inner_hero(
		'',
		'Events services',
		'An integral part of brand communication, Events is where our heart is. Thoughtful planning and execution carry each brief forward.',
		0
	);
	?>
	<section class="nice-events-inner-section nice-events-services-index" aria-labelledby="nice-events-services-list-title">
		<div class="nice-wide">
			<header class="nice-events-inner-heading" data-nice-reveal>
				<p class="nice-eyebrow">Three focused practices</p>
				<h2 id="nice-events-services-list-title">From the brief to the experience.</h2>
			</header>
			<?php if ( 3 === count( $services ) ) : ?>
				<div class="nice-events-services-index__list">
					<?php foreach ( $services as $index => $service ) :
						$url         = nice_theme_get_events_content_url( $service );
						$description = $service->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $service->post_content ), 30 );
						?>
						<article class="nice-events-service-row" data-nice-reveal>
							<div class="nice-events-service-row__content">
								<span class="nice-events-service-row__index nice-index-dot" aria-hidden="true"></span>
								<h3><?php echo esc_html( $service->post_title ); ?></h3>
								<p><?php echo esc_html( $description ); ?></p>
								<?php if ( $url ) : ?><a class="nice-link" href="<?php echo esc_url( $url ); ?>">Explore <?php echo esc_html( $service->post_title ); ?> <span aria-hidden="true">-&gt;</span></a><?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-events-empty-state" data-nice-reveal>
					<h2>Services are being prepared.</h2>
					<p>The approved Events service collection will appear here when all three records are ready.</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	nice_render_events_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render one Events Service detail page.
 *
 * @return string
 */
function nice_render_events_service_detail() {
	$service = get_queried_object();

	if ( ! $service instanceof WP_Post || 'nice_service' !== $service->post_type || ! has_term( 'events', 'nice_division', $service ) ) {
		return '';
	}

	$service_type = nice_theme_get_primary_term( $service->ID, 'nice_service_type' );
	$case_studies = $service_type && function_exists( 'nice_get_case_studies_by_service' )
		? nice_get_case_studies_by_service( $service_type->slug, array( 'division' => 'events', 'posts_per_page' => 3 ) )
		: array();
	$services     = function_exists( 'nice_get_events_services' ) ? nice_get_events_services() : array();

	ob_start();
	nice_render_events_inner_hero( 'Events service', $service->post_title, $service->post_excerpt, $service->ID );
	?>
	<section class="nice-events-inner-section nice-events-service-story">
		<div class="nice-container nice-events-editor-content" data-nice-reveal>
			<?php echo wp_kses_post( apply_filters( 'the_content', $service->post_content ) ); ?>
		</div>
	</section>
	<section class="nice-events-inner-section nice-events-related-work" aria-labelledby="nice-service-work-title">
		<div class="nice-wide">
			<header class="nice-events-inner-heading" data-nice-reveal>
				<p class="nice-eyebrow">Relevant work</p>
				<h2 id="nice-service-work-title">Projects in <?php echo esc_html( $service->post_title ); ?></h2>
			</header>
			<?php if ( $case_studies ) : ?>
				<div class="nice-events-case-list">
					<?php foreach ( $case_studies as $case_study ) : nice_render_case_study_preview( $case_study ); endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-events-empty-state" data-nice-reveal><p>Approved work for this service is being prepared for publication.</p></div>
			<?php endif; ?>
		</div>
	</section>
	<section class="nice-events-inner-section nice-events-related-services" aria-labelledby="nice-related-services-title">
		<div class="nice-wide">
			<header class="nice-events-inner-heading" data-nice-reveal>
				<p class="nice-eyebrow">Explore Events</p>
				<h2 id="nice-related-services-title">Related services</h2>
			</header>
			<div class="nice-events-related-services__list">
				<?php foreach ( $services as $related_service ) :
					if ( $related_service->ID === $service->ID ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( nice_theme_get_events_content_url( $related_service ) ); ?>"><span><?php echo esc_html( $related_service->post_title ); ?></span><span aria-hidden="true">-&gt;</span></a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	nice_render_events_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the Events Case Studies index.
 *
 * @return string
 */
function nice_render_events_case_studies_index() {
	$all_cases = function_exists( 'nice_get_case_studies' ) ? nice_get_case_studies( array( 'division' => 'events' ) ) : array();
	$groups    = array(
		'corporate-events'         => 'Corporate Events',
		'exhibitions-conferences'  => 'Exhibitions & Conferences',
		'activations-promotions'   => 'Activations & Promotions',
	);

	ob_start();
	nice_render_events_inner_hero(
		'',
		'Case studies',
		'Published work from across NICE Events, organised by the service behind each experience.',
		0
	);
	?>
	<nav class="nice-events-work-filter nice-wide" aria-label="<?php esc_attr_e( 'Work categories', 'nice' ); ?>" data-nice-reveal>
		<?php foreach ( $groups as $slug => $label ) : ?>
			<a href="#<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>
	<div class="nice-events-case-groups">
		<?php foreach ( $groups as $slug => $label ) :
			$case_studies = function_exists( 'nice_get_case_studies_by_service' )
				? nice_get_case_studies_by_service( $slug, array( 'division' => 'events', 'posts_per_page' => 3 ) )
				: array();
			?>
			<section class="nice-events-inner-section nice-events-case-group" id="<?php echo esc_attr( $slug ); ?>" aria-labelledby="nice-case-group-<?php echo esc_attr( $slug ); ?>">
				<div class="nice-wide">
					<header class="nice-events-inner-heading" data-nice-reveal>
						<p class="nice-eyebrow">Events work</p>
						<h2 id="nice-case-group-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></h2>
					</header>
					<?php if ( $case_studies ) : ?>
						<div class="nice-events-case-list">
							<?php
							$hierarchy_classes = array(
								0 => 'nice-events-case-preview--featured',
								1 => 'nice-events-case-preview--large',
								2 => 'nice-events-case-preview--secondary',
							);
							foreach ( $case_studies as $idx => $case_study ) :
								$variant_class = $hierarchy_classes[ $idx ] ?? 'nice-events-case-preview--secondary';
								nice_render_case_study_preview( $case_study, $variant_class );
							endforeach;
							?>
						</div>
					<?php else : ?>
						<div class="nice-events-empty-state" data-nice-reveal><p>Approved case studies in this service are being prepared for publication.</p></div>
					<?php endif; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
	<?php
	nice_render_events_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render one Events Case Study detail page.
 *
 * The page itself lives in inc/case-study.php, shared with Studio. This stays
 * here because check-theme.mjs asserts the block name appears in this file.
 *
 * @return string
 */
function nice_render_events_case_study_detail() {
	return nice_render_case_study_detail( 'events' );
}

/**
 * Render the shared Events Clients page.
 *
 * @return string
 */
function nice_render_events_clients_index() {
	$clients = function_exists( 'nice_get_clients' ) ? nice_get_clients() : array();

	ob_start();
	nice_render_events_inner_hero( '', 'Clients', 'A shared NICE client list, presented across the Events and Studio divisions without duplicate records.' );
	?>
	<section class="nice-events-inner-section nice-events-client-directory" aria-labelledby="nice-client-directory-title">
		<div class="nice-wide">
			<header class="nice-events-inner-heading" data-nice-reveal><p class="nice-eyebrow">Selected collaborations</p><h2 id="nice-client-directory-title">Built through the work.</h2></header>
			<?php if ( $clients ) : ?>
				<div class="nice-events-client-directory__list">
					<?php foreach ( $clients as $client ) :
						$url = get_post_meta( $client->ID, '_nice_client_url', true );
						?>
						<article class="nice-events-client" data-nice-reveal>
							<h3><?php echo esc_html( $client->post_title ); ?></h3>
							<?php if ( $url ) : ?><a class="nice-link" href="<?php echo esc_url( $url ); ?>">Visit approved website <span aria-hidden="true">-&gt;</span></a><?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-events-empty-state"><p>The approved client list is being prepared for publication.</p></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	nice_render_events_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the Events About page: the philosophy, then the team.
 *
 * About replaced the Team page rather than sitting beside it, so this is where
 * the Events roster now lives -- under the philosophy that explains how the
 * division works before it introduces who does the work.
 *
 * @return string
 */
function nice_render_events_about_page() {
	ob_start();
	nice_render_events_inner_hero(
		'About us',
		'Events is where our heart is',
		'World is a stage, life is an event. NICE plans, designs and runs corporate events, conferences and exhibitions across India, and owns two event properties of its own.'
	);

	echo nice_render_philosophy_manifesto( 'events' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared static markup.
	?>
	<section class="nice-about-section nice-about-story" aria-labelledby="nice-about-story-title">
		<div class="nice-wide">
			<header class="nice-about-heading" data-nice-reveal>
				<p class="nice-eyebrow">What we run</p>
				<h2 id="nice-about-story-title">Thoughtful planning, flawless execution.</h2>
			</header>
			<div class="nice-about-story__grid">
				<p data-nice-reveal>Corporate events, trade meets and conferences, shows and concerts, promotions and activations, and end-to-end exhibition stall design and fabrication. The same team carries a brief from the first conversation to the last crate leaving the venue.</p>
				<p data-nice-reveal>Two of those properties are our own. <strong>RunForEquity</strong>, a social run first held in 2017, drew more than five thousand runners by its second edition. <strong>Samashtee</strong>, run with the Samashtee Foundation, is an art and literature festival built as a platform for artists and performers who do not get a mainstream stage.</p>
			</div>
		</div>
	</section>
	<?php
	nice_render_about_team_section( 'events', 'Events team', 'Meet the team.' );
	nice_render_about_connect_section( 'events' );
	nice_render_events_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the form-free Events Contact page.
 *
 * @return string
 */
function nice_render_events_contact_page() {
	$settings = function_exists( 'nice_theme_get_contact_channels' ) ? nice_theme_get_contact_channels( 'events' ) : array();
	$actions  = array();

	if ( ! empty( $settings['whatsapp_url'] ) ) {
		$actions[] = array( 'label' => 'WhatsApp', 'icon' => 'whatsapp', 'value' => 'Start a WhatsApp conversation', 'url' => $settings['whatsapp_url'] );
	}
	if ( ! empty( $settings['email_address'] ) ) {
		$actions[] = array( 'label' => 'Email', 'icon' => 'email', 'value' => $settings['email_address'], 'url' => 'mailto:' . $settings['email_address'] );
	}
	if ( ! empty( $settings['phone_url'] ) && ! empty( $settings['phone'] ) ) {
		$actions[] = array( 'label' => 'Phone', 'icon' => 'phone', 'value' => $settings['phone'], 'url' => $settings['phone_url'] );
	}

	ob_start();
	nice_render_events_inner_hero( '', "Let's make something NICE.", 'A direct place to begin an Events conversation. Approved contact channels appear here when they are ready for publication.' );
	?>
	<section class="nice-events-inner-section nice-events-contact-page" aria-labelledby="nice-events-contact-options-title">
		<div class="nice-wide">
			<?php if ( $actions ) : ?>
				<header class="nice-events-inner-heading" data-nice-reveal><p class="nice-eyebrow">Contact NICE Events</p><h2 id="nice-events-contact-options-title">Choose a channel.</h2></header>
				<div class="nice-events-contact-page__actions">
					<?php foreach ( $actions as $action ) : ?>
						<a href="<?php echo esc_url( $action['url'] ); ?>" data-nice-contact-channel="<?php echo esc_attr( strtolower( $action['label'] ) ); ?>"><?php nice_render_icon( $action['icon'] ?? '' ); ?><small><?php echo esc_html( $action['label'] ); ?></small><span class="nice-contact-row__value"><?php echo esc_html( $action['value'] ); ?></span></a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-events-empty-state nice-events-empty-state--feature" data-nice-reveal>
					<p class="nice-eyebrow">Publication pending</p>
					<h2 id="nice-events-contact-options-title">Contact details are being prepared.</h2>
					<p>Approved WhatsApp, email and phone details will appear here when they are ready for publication.</p>
				</div>
			<?php endif; ?>
			<?php
			$nice_office = function_exists( 'nice_get_office_details' ) ? nice_get_office_details() : array( 'address_lines' => array(), 'map_url' => '' );
			if ( ! empty( $nice_office['address_lines'] ) ) :
				?>
				<div class="nice-office" data-nice-reveal>
					<h3 class="nice-office__heading"><?php nice_render_icon( 'location' ); ?>Visit the studio</h3>
					<address class="nice-office__address">
						<?php foreach ( $nice_office['address_lines'] as $nice_line ) : ?>
							<span><?php echo esc_html( $nice_line ); ?></span>
						<?php endforeach; ?>
					</address>
					<?php if ( $nice_office['map_url'] ) : ?>
						<a class="nice-link nice-office__directions" href="<?php echo esc_url( $nice_office['map_url'] ); ?>" rel="noopener" target="_blank">
							Get directions
							<span class="nice-sr-only">(opens Google Maps in a new tab)</span>
							<span aria-hidden="true">&#8599;</span>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Register small server-rendered blocks used by the block templates.
 */
function nice_register_events_page_blocks() {
	$blocks = array(
		'nice/events-section-navigation' => 'nice_render_events_section_navigation',
		'nice/events-services-index'     => 'nice_render_events_services_index',
		'nice/events-service-detail'     => 'nice_render_events_service_detail',
		'nice/events-case-studies-index' => 'nice_render_events_case_studies_index',
		'nice/events-case-study-detail'  => 'nice_render_events_case_study_detail',
		'nice/events-clients-index'      => 'nice_render_events_clients_index',
		'nice/events-about-page'         => 'nice_render_events_about_page',
		'nice/events-contact-page'       => 'nice_render_events_contact_page',
	);

	foreach ( $blocks as $name => $callback ) {
		register_block_type(
			$name,
			array(
				'api_version'     => 3,
				'render_callback' => $callback,
			)
		);
	}
}
add_action( 'init', 'nice_register_events_page_blocks' );
