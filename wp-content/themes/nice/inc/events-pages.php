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
	if ( is_singular( array( 'nice_service', 'nice_case_study' ) ) ) {
		return has_term( 'events', 'nice_division', get_queried_object_id() );
	}

	if ( ! is_page() ) {
		return false;
	}

	$page_path = get_page_uri( get_queried_object_id() );

	return 'events' === $page_path || str_starts_with( $page_path, 'events/' );
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
 * Return responsive featured-image markup.
 *
 * @param int                  $post_id Post ID.
 * @param string               $sizes   Responsive sizes value.
 * @param array<string, mixed> $attrs   Additional attributes.
 * @return string
 */
/**
 * Report whether a record's own media is cleared for publication.
 *
 * Attaching a featured image is not the same as clearing it for the public
 * site. Migrated deck photography sits on these records awaiting review, so the
 * hero only renders once an editor moves the source approval to approved.
 *
 * @param int $post_id Record ID.
 * @return bool
 */
function nice_theme_media_approved( $post_id ) {
	return 'approved' === get_post_meta( $post_id, '_nice_source_approval_status', true );
}

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
			<p class="nice-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $intro ) : ?>
				<p><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
	echo nice_render_philosophy_strip(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shared static markup.
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
			<?php nice_render_events_media_placeholder( sprintf( '%s project media pending approval', $case_study->post_title ) ); ?>
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
	$cta_url         = ! empty( $args['cta_url'] ) ? $args['cta_url'] : home_url( '/events/contact/' );
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
		'NICE / Events',
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
								<span class="nice-events-service-row__index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
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
		'NICE / Events',
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
 * @return string
 */
function nice_render_events_case_study_detail() {
	$case_study = get_queried_object();

	if ( ! $case_study instanceof WP_Post || 'nice_case_study' !== $case_study->post_type || ! has_term( 'events', 'nice_division', $case_study ) ) {
		return '';
	}

	$client       = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : '';
	$location     = get_post_meta( $case_study->ID, '_nice_location', true );
	$year         = (int) get_post_meta( $case_study->ID, '_nice_year', true );
	$proof_value  = get_post_meta( $case_study->ID, '_nice_proof_value', true );
	$proof_label  = get_post_meta( $case_study->ID, '_nice_proof_label', true );
	$quote_text   = get_post_meta( $case_study->ID, '_nice_quote_text', true );
	$quote_author = get_post_meta( $case_study->ID, '_nice_quote_author', true );
	$service_type = nice_theme_get_primary_term( $case_study->ID, 'nice_service_type' );
	$division     = nice_theme_get_primary_term( $case_study->ID, 'nice_division' );
	$related      = $service_type && function_exists( 'nice_get_case_studies_by_service' )
		? nice_get_case_studies_by_service( $service_type->slug, array( 'division' => 'events' ) )
		: array();
	$related      = array_values( array_filter( $related, static fn( $item ) => $item->ID !== $case_study->ID ) );
	$related      = array_slice( $related, 0, 3 );
	$all_cases    = function_exists( 'nice_get_case_studies' ) ? nice_get_case_studies( array( 'division' => 'events' ) ) : array();
	if ( ! $related ) {
		$related = array_values( array_filter( $all_cases, static fn( $item ) => $item->ID !== $case_study->ID ) );
		$related = array_slice( $related, 0, 3 );
	}
	$current      = array_search( $case_study->ID, wp_list_pluck( $all_cases, 'ID' ), true );
	$previous     = false !== $current && $current > 0 ? $all_cases[ $current - 1 ] : null;
	$next         = false !== $current && $current < count( $all_cases ) - 1 ? $all_cases[ $current + 1 ] : null;

	ob_start();
	nice_render_events_inner_hero( $client ?: 'Events case study', $case_study->post_title, $case_study->post_excerpt, $case_study->ID );
	?>
	<?php if ( $quote_text ) : ?>
		<section class="nice-events-case-quote nice-events-inner-section" aria-label="<?php esc_attr_e( 'Client quote', 'nice' ); ?>">
			<div class="nice-wide" data-nice-reveal>
				<blockquote class="nice-case-quote">
					<p class="nice-editorial" data-nice-editorial-reveal>&ldquo;<?php echo esc_html( $quote_text ); ?>&rdquo;</p>
					<?php if ( $quote_author ) : ?>
						<cite>&mdash; <?php echo esc_html( $quote_author ); ?></cite>
					<?php endif; ?>
				</blockquote>
			</div>
		</section>
	<?php endif; ?>
	<section class="nice-events-case-infobar" aria-label="<?php esc_attr_e( 'Project overview', 'nice' ); ?>">
		<div class="nice-wide">
			<dl class="nice-events-case-meta-bar" data-nice-reveal>
				<?php if ( $client ) : ?><div><dt>Client</dt><dd><?php echo esc_html( $client ); ?></dd></div><?php endif; ?>
				<?php if ( $location ) : ?><div><dt>Location</dt><dd><?php echo esc_html( $location ); ?></dd></div><?php endif; ?>
				<?php if ( $year ) : ?><div><dt>Year</dt><dd><?php echo esc_html( $year ); ?></dd></div><?php endif; ?>
				<?php if ( $service_type ) : ?><div><dt>Service Type</dt><dd><?php echo esc_html( $service_type->name ); ?></dd></div><?php endif; ?>
				<?php if ( $division ) : ?><div><dt>Division</dt><dd><?php echo esc_html( $division->name ); ?></dd></div><?php endif; ?>
			</dl>
		</div>
	</section>
	<?php
	$case_hero_image = nice_theme_media_approved( $case_study->ID )
		? nice_theme_get_featured_image(
			$case_study->ID,
			'(min-width: 75rem) 1200px, 100vw',
			array(
				'alt'           => get_the_title( $case_study->ID ),
				'loading'       => 'eager',
				'fetchpriority' => 'high',
			)
		)
		: '';
	?>
	<section class="nice-case-hero-media-wrap" aria-label="<?php esc_attr_e( 'Project visual', 'nice' ); ?>">
		<div class="nice-wide">
			<div class="nice-case-hero-media" data-nice-reveal>
				<?php if ( $case_hero_image ) : ?>
					<?php echo $case_hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<?php nice_render_events_media_placeholder( sprintf( '%s project media pending approval', $case_study->post_title ) ); ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php if ( $proof_value && $proof_label ) : ?>
		<section class="nice-events-project-proof" aria-labelledby="nice-project-proof-title">
			<div class="nice-wide nice-events-project-proof__content" data-nice-reveal>
				<p class="nice-events-project-proof__value"><?php echo esc_html( $proof_value ); ?></p>
				<div><p class="nice-eyebrow">Project-specific proof</p><h2 id="nice-project-proof-title"><?php echo esc_html( $proof_label ); ?></h2></div>
			</div>
		</section>
	<?php endif; ?>
		<section class="nice-events-case-intro nice-events-inner-section">
			<div class="nice-wide nice-events-case-intro__grid">
				<header class="nice-events-case-intro__heading" data-nice-reveal>
					<p class="nice-eyebrow">The project</p>
					<h2>From objective to execution.</h2>
				</header>
				<div class="nice-events-editor-content" data-nice-reveal>
				<?php echo wp_kses_post( apply_filters( 'the_content', $case_study->post_content ) ); ?>
			</div>
		</div>
	</section>
	<?php if ( $related ) : ?>
		<section class="nice-events-inner-section nice-events-related-work" aria-labelledby="nice-related-work-title">
			<div class="nice-wide">
				<header class="nice-events-inner-heading" data-nice-reveal><p class="nice-eyebrow">Continue exploring</p><h2 id="nice-related-work-title">Related work</h2></header>
				<div class="nice-events-case-list">
					<?php foreach ( $related as $idx => $item ) :
						$rel_class = 0 === $idx ? 'nice-events-case-preview--featured' : 'nice-events-case-preview--secondary';
						nice_render_case_study_preview( $item, $rel_class );
					endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php if ( $previous || $next ) : ?>
		<nav class="nice-events-project-navigation nice-wide" aria-label="<?php esc_attr_e( 'Case study navigation', 'nice' ); ?>">
		<?php if ( $previous ) :
			$prev_client = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $previous->ID ) : '';
		?>
			<a class="nice-events-nav-card nice-events-nav-card--prev" href="<?php echo esc_url( nice_theme_get_events_content_url( $previous ) ); ?>">
				<div class="nice-events-nav-card__body">
						<small>&larr; Previous project</small>
						<?php if ( $prev_client ) : ?><span class="nice-eyebrow"><?php echo esc_html( $prev_client ); ?></span><?php endif; ?>
						<span class="nice-events-nav-card__title"><?php echo esc_html( $previous->post_title ); ?></span>
					</div>
				</a>
			<?php else : ?>
				<span class="nice-events-nav-card-empty"></span>
			<?php endif; ?>
		<?php if ( $next ) :
			$next_client = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $next->ID ) : '';
		?>
				<a class="nice-events-nav-card nice-events-nav-card--next" href="<?php echo esc_url( nice_theme_get_events_content_url( $next ) ); ?>">
					<div class="nice-events-nav-card__body">
						<small>Next project &rarr;</small>
						<?php if ( $next_client ) : ?><span class="nice-eyebrow"><?php echo esc_html( $next_client ); ?></span><?php endif; ?>
						<span class="nice-events-nav-card__title"><?php echo esc_html( $next->post_title ); ?></span>
					</div>
			</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
	<?php
	nice_render_events_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the shared Events Clients page.
 *
 * @return string
 */
function nice_render_events_clients_index() {
	$clients = function_exists( 'nice_get_clients' ) ? nice_get_clients() : array();

	ob_start();
	nice_render_events_inner_hero( 'NICE / Events', 'Clients', 'A shared NICE client list, presented across the Events and Studio divisions without duplicate records.' );
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
 * Render the Events Team page and its safe empty state.
 *
 * @return string
 */
function nice_render_events_team_index() {
	$team = function_exists( 'nice_get_team_members_by_division' ) ? nice_get_team_members_by_division( 'events' ) : array();

	ob_start();
	nice_render_events_inner_hero( 'NICE / Events', 'Events team', 'The people behind each brief bring planning, production and execution together.' );
	?>
	<section class="nice-events-inner-section nice-events-team-directory" aria-labelledby="nice-team-directory-title">
		<div class="nice-wide">
			<?php if ( $team ) : ?>
				<header class="nice-events-inner-heading" data-nice-reveal><p class="nice-eyebrow">Events team</p><h2 id="nice-team-directory-title">Meet the team.</h2></header>
				<div class="nice-events-team-directory__list">
					<?php foreach ( $team as $member ) :
						$image = nice_theme_get_featured_image( $member->ID, '(min-width: 48rem) 360px, calc(100vw - 40px)' );
						$role  = get_post_meta( $member->ID, '_nice_role', true );
						?>
						<article class="nice-events-team-member" data-nice-reveal>
							<?php if ( $image ) : ?><div class="nice-events-team-member__portrait"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_get_attachment_image(). ?></div><?php endif; ?>
							<?php if ( $role ) : ?><p class="nice-eyebrow"><?php echo esc_html( $role ); ?></p><?php endif; ?>
							<h3><?php echo esc_html( $member->post_title ); ?></h3>
							<div><?php echo wp_kses_post( apply_filters( 'the_content', $member->post_content ) ); ?></div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-events-empty-state nice-events-empty-state--feature" data-nice-reveal>
					<p class="nice-eyebrow">Publication pending</p>
					<h2 id="nice-team-directory-title">Team details are being prepared.</h2>
					<p>Approved Events team profiles will appear here when they are ready for publication.</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
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
		$actions[] = array( 'label' => 'WhatsApp', 'value' => 'Start a WhatsApp conversation', 'url' => $settings['whatsapp_url'] );
	}
	if ( ! empty( $settings['email_address'] ) ) {
		$actions[] = array( 'label' => 'Email', 'value' => $settings['email_address'], 'url' => 'mailto:' . $settings['email_address'] );
	}
	if ( ! empty( $settings['phone_url'] ) && ! empty( $settings['phone'] ) ) {
		$actions[] = array( 'label' => 'Phone', 'value' => $settings['phone'], 'url' => $settings['phone_url'] );
	}

	ob_start();
	nice_render_events_inner_hero( 'NICE / Events', "Let's make something NICE.", 'A direct place to begin an Events conversation. Approved contact channels appear here when they are ready for publication.' );
	?>
	<section class="nice-events-inner-section nice-events-contact-page" aria-labelledby="nice-events-contact-options-title">
		<div class="nice-wide">
			<?php if ( $actions ) : ?>
				<header class="nice-events-inner-heading" data-nice-reveal><p class="nice-eyebrow">Contact NICE Events</p><h2 id="nice-events-contact-options-title">Choose a channel.</h2></header>
				<div class="nice-events-contact-page__actions">
					<?php foreach ( $actions as $action ) : ?>
						<a href="<?php echo esc_url( $action['url'] ); ?>" data-nice-contact-channel="<?php echo esc_attr( strtolower( $action['label'] ) ); ?>"><small><?php echo esc_html( $action['label'] ); ?></small><span><?php echo esc_html( $action['value'] ); ?></span><span aria-hidden="true">-&gt;</span></a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-events-empty-state nice-events-empty-state--feature" data-nice-reveal>
					<p class="nice-eyebrow">Publication pending</p>
					<h2 id="nice-events-contact-options-title">Contact details are being prepared.</h2>
					<p>Approved WhatsApp, email and phone details will appear here when they are ready for publication.</p>
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
		'nice/events-team-index'         => 'nice_render_events_team_index',
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
