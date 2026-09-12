<?php
/**
 * Server-rendered Studio inner-page presentation.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the current request belongs to the Studio section.
 *
 * @return bool
 */
function nice_theme_is_studio_context() {
	if ( is_singular( array( 'nice_service', 'nice_case_study' ) ) ) {
		return has_term( 'studio', 'nice_division', get_queried_object_id() );
	}

	if ( ! is_page() ) {
		return false;
	}

	$page_path = get_page_uri( get_queried_object_id() );

	return 'studio' === $page_path || str_starts_with( $page_path, 'studio/' );
}

/**
 * Determine whether the request is a Studio inner page.
 *
 * @return bool
 */
function nice_theme_is_studio_inner_page() {
	return nice_theme_is_studio_context() && ! ( function_exists( 'nice_theme_is_studio_home' ) ? nice_theme_is_studio_home() : is_page( 'studio' ) );
}

/**
 * Return a safe Studio content URL.
 *
 * @param WP_Post $post Service or Case Study.
 * @return string
 */
function nice_theme_get_studio_content_url( $post ) {
	if ( function_exists( 'nice_get_content_url' ) ) {
		return nice_get_content_url( $post );
	}
	return '';
}

/**
 * Prepare text-led project content without loading unapproved embedded media.
 *
 * @param string $content Stored Case Study content.
 * @return string
 */
function nice_theme_get_studio_project_content( $content ) {
	$allowed_html = array(
		'a'          => array(
			'href'   => true,
			'rel'    => true,
			'target' => true,
		),
		'blockquote' => array( 'cite' => true ),
		'br'         => array(),
		'cite'       => array(),
		'div'        => array( 'class' => true ),
		'em'         => array(),
		'h2'         => array( 'class' => true, 'id' => true ),
		'h3'         => array( 'class' => true, 'id' => true ),
		'h4'         => array( 'class' => true, 'id' => true ),
		'li'         => array(),
		'ol'         => array( 'class' => true ),
		'p'          => array( 'class' => true ),
		'span'       => array( 'class' => true ),
		'strong'     => array(),
		'ul'         => array( 'class' => true ),
	);
	$text_content = strip_shortcodes( $content );
	$text_content = apply_filters( 'the_content', $text_content );

	return wp_kses( $text_content, $allowed_html );
}

/**
 * Render a full-bleed Studio inner-page hero.
 *
 * @param string $eyebrow   Introductory label.
 * @param string $title     Page title.
 * @param string $intro     Introductory copy.
 * @param int    $post_id   Retained for renderer compatibility.
 * @param string $video_url Retained for renderer compatibility.
 */
function nice_render_studio_inner_hero( $eyebrow, $title, $intro, $post_id = 0, $video_url = '' ) {
	?>
	<header class="nice-studio-inner-hero nice-studio-inner-hero--cinematic">
		<div class="nice-wide nice-studio-inner-hero__content" data-nice-reveal>
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
 * Render a Case Study preview from one CMS record.
 *
 * @param WP_Post $case_study Case Study post.
 * @param string  $class_name Optional layout modifier.
 */
function nice_render_studio_case_preview( $case_study, $class_name = '' ) {
	$url         = nice_theme_get_studio_content_url( $case_study );
	$client      = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : '';
	$location    = get_post_meta( $case_study->ID, '_nice_location', true );
	$year        = (int) get_post_meta( $case_study->ID, '_nice_year', true );
	$description = $case_study->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $case_study->post_content ), 30 );
	?>
	<article class="nice-studio-case-preview <?php echo esc_attr( $class_name ); ?>" data-nice-reveal>
		<?php if ( $url ) : ?>
			<a class="nice-studio-case-preview__media nice-studio-media-placeholder" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( sprintf( 'View %s case study', $case_study->post_title ) ); ?>">
				<span aria-hidden="true">NICE Studio</span>
			</a>
		<?php else : ?>
			<div class="nice-studio-case-preview__media nice-studio-media-placeholder" aria-hidden="true"><span>NICE Studio</span></div>
		<?php endif; ?>
		<div class="nice-studio-case-preview__body">
			<?php if ( $client ) : ?><p class="nice-eyebrow"><?php echo esc_html( $client ); ?></p><?php endif; ?>
			<h3><?php echo esc_html( $case_study->post_title ); ?></h3>
			<?php if ( $location || $year ) : ?>
				<p class="nice-studio-meta-line"><?php echo esc_html( implode( ' / ', array_filter( array( $location, $year ?: '' ) ) ) ); ?></p>
			<?php endif; ?>
			<?php if ( $description ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?>
			<?php if ( $url ) : ?><a class="nice-link" href="<?php echo esc_url( $url ); ?>">View case study <span aria-hidden="true">-&gt;</span></a><?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Render the reusable Studio contact transition.
 *
 * @param array<string, string> $args Optional custom CTA settings.
 */
function nice_render_studio_inner_contact_cta( $args = array() ) {
	$eyebrow         = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : 'Start a conversation';
	$heading         = ! empty( $args['heading'] ) ? $args['heading'] : 'Have a story to tell?';
	$cta_label       = ! empty( $args['cta_label'] ) ? $args['cta_label'] : "Let's make it NICE";
	$cta_url         = ! empty( $args['cta_url'] ) ? $args['cta_url'] : home_url( '/studio/contact/' );
	$whatsapp_action = function_exists( 'nice_get_contact_action' ) ? nice_get_contact_action( 'whatsapp', '', 'studio' ) : null;
	$email_action    = function_exists( 'nice_get_contact_action' ) ? nice_get_contact_action( 'email', '', 'studio' ) : null;
	?>
	<section class="nice-studio-inner-cta" aria-labelledby="nice-studio-inner-cta-title">
		<div class="nice-wide nice-studio-inner-cta__content" data-nice-reveal>
			<div class="nice-studio-inner-cta__lead">
				<p class="nice-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<h2 id="nice-studio-inner-cta-title" class="nice-editorial" data-nice-editorial-reveal><?php echo esc_html( $heading ); ?></h2>
			</div>
			<div class="nice-studio-inner-cta__actions">
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
 * Render the shared Studio section navigation.
 *
 * @return string
 */
function nice_render_studio_section_navigation() {
	return '';
}

/**
 * Render the Studio Services index.
 *
 * @return string
 */
function nice_render_studio_services_index() {
	$services = function_exists( 'nice_get_studio_services' ) ? nice_get_studio_services() : array();
	$hero_id  = $services[0]->ID ?? 0;

	ob_start();
	nice_render_studio_inner_hero(
		'NICE / Studio',
		'Studio services',
		'Screen-based storytelling across multiple formats. From conceptualisation to post-production, we bring stories to life.',
		$hero_id
	);
	?>
	<section class="nice-studio-inner-section nice-studio-services-index" aria-labelledby="nice-studio-services-list-title">
		<div class="nice-wide">
			<header class="nice-studio-inner-heading" data-nice-reveal>
				<p class="nice-eyebrow">Focused practices</p>
				<h2 id="nice-studio-services-list-title">Bringing stories to life.</h2>
			</header>
			<?php if ( 3 === count( $services ) ) : ?>
				<div class="nice-studio-services-index__list">
					<?php foreach ( $services as $index => $service ) :
						$url         = nice_theme_get_studio_content_url( $service );
						$description = $service->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $service->post_content ), 30 );
						?>
						<article class="nice-studio-service-row" data-nice-reveal>
							<div class="nice-studio-service-row__content">
								<span class="nice-studio-service-row__index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
								<h3><?php echo esc_html( $service->post_title ); ?></h3>
								<p><?php echo esc_html( $description ); ?></p>
								<?php if ( $url ) : ?><a class="nice-link" href="<?php echo esc_url( $url ); ?>">Explore <?php echo esc_html( $service->post_title ); ?> <span aria-hidden="true">-&gt;</span></a><?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-studio-empty-state" data-nice-reveal>
					<h2>Services are being prepared.</h2>
					<p>The approved Studio service collection will appear here when all three records are ready.</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	nice_render_studio_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render one Studio Service detail page.
 *
 * @return string
 */
function nice_render_studio_service_detail() {
	$service = get_queried_object();

	if ( ! $service instanceof WP_Post || 'nice_service' !== $service->post_type || ! has_term( 'studio', 'nice_division', $service ) ) {
		return '';
	}

	$service_type = function_exists( 'nice_theme_get_primary_term' ) ? nice_theme_get_primary_term( $service->ID, 'nice_service_type' ) : null;
	$case_studies = $service_type && function_exists( 'nice_get_case_studies_by_service' )
		? nice_get_case_studies_by_service( $service_type->slug, array( 'division' => 'studio', 'posts_per_page' => 3 ) )
		: array();
	$services     = function_exists( 'nice_get_studio_services' ) ? nice_get_studio_services() : array();

	ob_start();
	nice_render_studio_inner_hero( 'Studio service', $service->post_title, $service->post_excerpt, $service->ID );
	?>
	<section class="nice-studio-inner-section nice-studio-service-story">
		<div class="nice-container nice-studio-editor-content" data-nice-reveal>
			<?php echo wp_kses_post( apply_filters( 'the_content', $service->post_content ) ); ?>
		</div>
	</section>
	<section class="nice-studio-inner-section nice-studio-related-work" aria-labelledby="nice-service-work-title">
		<div class="nice-wide">
			<header class="nice-studio-inner-heading" data-nice-reveal>
				<p class="nice-eyebrow">Relevant work</p>
				<h2 id="nice-service-work-title">Projects in <?php echo esc_html( $service->post_title ); ?></h2>
			</header>
			<?php if ( $case_studies ) : ?>
				<div class="nice-studio-case-list">
					<?php foreach ( $case_studies as $case_study ) : nice_render_studio_case_preview( $case_study ); endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-studio-empty-state" data-nice-reveal><p>Approved work for this service is being prepared for publication.</p></div>
			<?php endif; ?>
		</div>
	</section>
	<section class="nice-studio-inner-section nice-studio-related-services" aria-labelledby="nice-related-services-title">
		<div class="nice-wide">
			<header class="nice-studio-inner-heading" data-nice-reveal>
				<p class="nice-eyebrow">Explore Studio</p>
				<h2 id="nice-related-services-title">Related services</h2>
			</header>
			<div class="nice-studio-related-services__list">
				<?php foreach ( $services as $related_service ) :
					if ( $related_service->ID === $service->ID ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( nice_theme_get_studio_content_url( $related_service ) ); ?>"><span><?php echo esc_html( $related_service->post_title ); ?></span><span aria-hidden="true">-&gt;</span></a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	nice_render_studio_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the Studio Case Studies index.
 *
 * @return string
 */
function nice_render_studio_case_studies_index() {
	$all_cases = function_exists( 'nice_get_case_studies' ) ? nice_get_case_studies( array( 'division' => 'studio' ) ) : array();
	$hero_id   = $all_cases[0]->ID ?? 0;
	$groups    = array(
		'corporate-videos'         => 'Corporate Videos',
		'digital-content-creation' => 'Digital Content Creation',
		'films-entertainment'      => 'Films & Entertainment',
	);

	ob_start();
	nice_render_studio_inner_hero(
		'NICE / Studio',
		'Case studies',
		'Published work from across NICE Studio, organised by the service behind each piece of content.',
		$hero_id
	);
	?>
	<div class="nice-studio-case-groups">
		<?php foreach ( $groups as $slug => $label ) :
			$case_studies = function_exists( 'nice_get_case_studies_by_service' )
				? nice_get_case_studies_by_service( $slug, array( 'division' => 'studio', 'posts_per_page' => 3 ) )
				: array();
			?>
			<section class="nice-studio-inner-section nice-studio-case-group" id="<?php echo esc_attr( $slug ); ?>" aria-labelledby="nice-case-group-<?php echo esc_attr( $slug ); ?>">
				<div class="nice-wide">
					<header class="nice-studio-inner-heading" data-nice-reveal>
						<p class="nice-eyebrow">Studio work</p>
						<h2 id="nice-case-group-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></h2>
					</header>
					<?php if ( $case_studies ) : ?>
						<div class="nice-studio-case-list">
							<?php foreach ( $case_studies as $case_study ) : nice_render_studio_case_preview( $case_study ); endforeach; ?>
						</div>
					<?php else : ?>
						<div class="nice-studio-empty-state" data-nice-reveal><p>Approved case studies in this service are being prepared for publication.</p></div>
					<?php endif; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
	<?php
	nice_render_studio_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render one Studio Case Study detail page.
 *
 * @return string
 */
function nice_render_studio_case_study_detail() {
	$case_study = get_queried_object();

	if ( ! $case_study instanceof WP_Post || 'nice_case_study' !== $case_study->post_type || ! has_term( 'studio', 'nice_division', $case_study ) ) {
		return '';
	}

	$client       = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : '';
	$location     = get_post_meta( $case_study->ID, '_nice_location', true );
	$year         = (int) get_post_meta( $case_study->ID, '_nice_year', true );
	$proof_value  = get_post_meta( $case_study->ID, '_nice_proof_value', true );
	$proof_label  = get_post_meta( $case_study->ID, '_nice_proof_label', true );
	$quote_text   = get_post_meta( $case_study->ID, '_nice_quote_text', true );
	$quote_author = get_post_meta( $case_study->ID, '_nice_quote_author', true );
	$service_type = function_exists( 'nice_theme_get_primary_term' ) ? nice_theme_get_primary_term( $case_study->ID, 'nice_service_type' ) : null;
	$division     = function_exists( 'nice_theme_get_primary_term' ) ? nice_theme_get_primary_term( $case_study->ID, 'nice_division' ) : null;
	$related      = $service_type && function_exists( 'nice_get_case_studies_by_service' )
		? nice_get_case_studies_by_service( $service_type->slug, array( 'division' => 'studio' ) )
		: array();
	$related      = array_values( array_filter( $related, static fn( $item ) => $item->ID !== $case_study->ID ) );
	$related      = array_slice( $related, 0, 3 );
	$all_cases    = function_exists( 'nice_get_case_studies' ) ? nice_get_case_studies( array( 'division' => 'studio' ) ) : array();
	if ( ! $related ) {
		$related = array_values( array_filter( $all_cases, static fn( $item ) => $item->ID !== $case_study->ID ) );
		$related = array_slice( $related, 0, 3 );
	}
	$current      = array_search( $case_study->ID, wp_list_pluck( $all_cases, 'ID' ), true );
	$previous     = false !== $current && $current > 0 ? $all_cases[ $current - 1 ] : null;
	$next         = false !== $current && $current < count( $all_cases ) - 1 ? $all_cases[ $current + 1 ] : null;

	ob_start();
	nice_render_studio_inner_hero( $client ?: 'Studio case study', $case_study->post_title, $case_study->post_excerpt, $case_study->ID );
	?>
	<?php if ( $quote_text ) : ?>
		<section class="nice-studio-case-quote nice-studio-inner-section" aria-label="<?php esc_attr_e( 'Client quote', 'nice' ); ?>">
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
	<section class="nice-studio-case-infobar" aria-label="<?php esc_attr_e( 'Project overview', 'nice' ); ?>">
		<div class="nice-wide">
			<dl class="nice-studio-case-meta-bar" data-nice-reveal>
				<?php if ( $client ) : ?><div><dt>Client</dt><dd><?php echo esc_html( $client ); ?></dd></div><?php endif; ?>
				<?php if ( $location ) : ?><div><dt>Location</dt><dd><?php echo esc_html( $location ); ?></dd></div><?php endif; ?>
				<?php if ( $year ) : ?><div><dt>Year</dt><dd><?php echo esc_html( $year ); ?></dd></div><?php endif; ?>
				<?php if ( $service_type ) : ?><div><dt>Service Type</dt><dd><?php echo esc_html( $service_type->name ); ?></dd></div><?php endif; ?>
				<?php if ( $division ) : ?><div><dt>Division</dt><dd><?php echo esc_html( $division->name ); ?></dd></div><?php endif; ?>
			</dl>
		</div>
	</section>
	<?php
	/* Project media stays unpublished until its source approval is cleared. */
	$case_media_approved = nice_theme_media_approved( $case_study->ID );
	$case_hero_image     = $case_media_approved
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
	$video_url = $case_media_approved ? $video_url : '';
	?>
	<section class="nice-case-hero-media-wrap" aria-label="<?php esc_attr_e( 'Project visual', 'nice' ); ?>">
		<div class="nice-wide">
			<?php if ( $video_url || $case_hero_image ) : ?>
				<div class="nice-case-hero-media" data-nice-reveal>
					<?php if ( $video_url ) : ?>
						<video src="<?php echo esc_url( $video_url ); ?>" muted autoplay playsinline loop poster="<?php echo esc_url( wp_get_attachment_image_url( get_post_thumbnail_id( $case_study->ID ), 'full' ) ); ?>"></video>
					<?php else : ?>
						<?php echo $case_hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="nice-case-hero-media nice-studio-media-placeholder" data-nice-reveal role="img" aria-label="<?php esc_attr_e( 'Project media pending approval', 'nice' ); ?>">
					<span aria-hidden="true">NICE Studio</span>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php if ( $proof_value && $proof_label ) : ?>
		<section class="nice-studio-project-proof" aria-labelledby="nice-project-proof-title">
			<div class="nice-wide nice-studio-project-proof__content" data-nice-reveal>
				<p class="nice-studio-project-proof__value"><?php echo esc_html( $proof_value ); ?></p>
				<div><p class="nice-eyebrow">Project-specific proof</p><h2 id="nice-project-proof-title"><?php echo esc_html( $proof_label ); ?></h2></div>
			</div>
		</section>
	<?php endif; ?>
	<section class="nice-studio-case-intro nice-studio-inner-section">
		<div class="nice-wide nice-studio-case-intro__grid">
			<div class="nice-studio-editor-content" data-nice-reveal>
				<?php echo nice_theme_get_studio_project_content( $case_study->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by helper. ?>
			</div>
		</div>
	</section>
	<?php if ( $related ) : ?>
		<section class="nice-studio-inner-section nice-studio-related-work" aria-labelledby="nice-related-work-title">
			<div class="nice-wide">
				<header class="nice-studio-inner-heading" data-nice-reveal><p class="nice-eyebrow">Continue exploring</p><h2 id="nice-related-work-title">Related work</h2></header>
				<div class="nice-studio-case-list">
					<?php foreach ( $related as $item ) : nice_render_studio_case_preview( $item ); endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php if ( $previous || $next ) : ?>
		<nav class="nice-studio-project-navigation nice-wide" aria-label="<?php esc_attr_e( 'Case study navigation', 'nice' ); ?>">
		<?php if ( $previous ) :
			$prev_client = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $previous->ID ) : '';
		?>
			<a class="nice-studio-nav-card nice-studio-nav-card--prev" href="<?php echo esc_url( nice_theme_get_studio_content_url( $previous ) ); ?>">
				<div class="nice-studio-nav-card__body">
						<small>&larr; Previous project</small>
						<?php if ( $prev_client ) : ?><span class="nice-eyebrow"><?php echo esc_html( $prev_client ); ?></span><?php endif; ?>
						<span class="nice-studio-nav-card__title"><?php echo esc_html( $previous->post_title ); ?></span>
					</div>
				</a>
			<?php else : ?>
				<span class="nice-studio-nav-card-empty"></span>
			<?php endif; ?>
		<?php if ( $next ) :
			$next_client = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $next->ID ) : '';
		?>
				<a class="nice-studio-nav-card nice-studio-nav-card--next" href="<?php echo esc_url( nice_theme_get_studio_content_url( $next ) ); ?>">
					<div class="nice-studio-nav-card__body">
						<small>Next project &rarr;</small>
						<?php if ( $next_client ) : ?><span class="nice-eyebrow"><?php echo esc_html( $next_client ); ?></span><?php endif; ?>
						<span class="nice-studio-nav-card__title"><?php echo esc_html( $next->post_title ); ?></span>
					</div>
			</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
	<?php
	nice_render_studio_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the shared Studio Clients page.
 *
 * @return string
 */
function nice_render_studio_clients_index() {
	$clients = function_exists( 'nice_get_clients' ) ? nice_get_clients() : array();

	ob_start();
	nice_render_studio_inner_hero( 'NICE / Studio', 'Clients', 'A shared NICE client list, presented across the Events and Studio divisions without duplicate records.' );
	?>
	<section class="nice-studio-inner-section nice-studio-client-directory" aria-labelledby="nice-client-directory-title">
		<div class="nice-wide">
			<header class="nice-studio-inner-heading" data-nice-reveal><p class="nice-eyebrow">Selected collaborations</p><h2 id="nice-client-directory-title">Built through the work.</h2></header>
			<?php if ( $clients ) : ?>
				<div class="nice-studio-client-directory__list">
					<?php foreach ( $clients as $client ) :
						$image = nice_theme_get_featured_image( $client->ID, '(min-width: 48rem) 280px, 44vw', array( 'alt' => get_the_title( $client ) ) );
						$url   = get_post_meta( $client->ID, '_nice_client_url', true );
						?>
						<article class="nice-studio-client" data-nice-reveal>
							<?php if ( $image ) : ?><div class="nice-studio-client__logo"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
							<h3><?php echo esc_html( $client->post_title ); ?></h3>
							<?php if ( $url ) : ?><a class="nice-link" href="<?php echo esc_url( $url ); ?>">Visit approved website <span aria-hidden="true">-&gt;</span></a><?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-studio-empty-state"><p>The approved client list is being prepared for publication.</p></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	nice_render_studio_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the Studio Team page and its safe empty state.
 *
 * @return string
 */
function nice_render_studio_team_index() {
	$team = function_exists( 'nice_get_team_members_by_division' ) ? nice_get_team_members_by_division( 'studio' ) : array();

	ob_start();
	nice_render_studio_inner_hero( 'NICE / Studio', 'Studio team', 'The creative and production rosters behind each piece of content.' );
	?>
	<section class="nice-studio-inner-section nice-studio-team-directory" aria-labelledby="nice-team-directory-title">
		<div class="nice-wide">
			<?php if ( $team ) : ?>
				<header class="nice-studio-inner-heading" data-nice-reveal><p class="nice-eyebrow">Studio team</p><h2 id="nice-team-directory-title">Meet the team.</h2></header>
				<div class="nice-studio-team-directory__list">
					<?php foreach ( $team as $member ) :
						$image = nice_theme_get_featured_image( $member->ID, '(min-width: 48rem) 360px, calc(100vw - 40px)' );
						$role  = get_post_meta( $member->ID, '_nice_role', true );
						?>
						<article class="nice-studio-team-member" data-nice-reveal>
							<?php if ( $image ) : ?><div class="nice-studio-team-member__portrait"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
							<?php if ( $role ) : ?><p class="nice-eyebrow"><?php echo esc_html( $role ); ?></p><?php endif; ?>
							<h3><?php echo esc_html( $member->post_title ); ?></h3>
							<div><?php echo wp_kses_post( apply_filters( 'the_content', $member->post_content ) ); ?></div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-studio-empty-state nice-studio-empty-state--feature" data-nice-reveal>
					<p class="nice-eyebrow">Publication pending</p>
					<h2 id="nice-team-directory-title">Our creative and production rosters are currently being updated for publication.</h2>
					<p>Approved Studio team profiles will appear here when they are ready for publication.</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	nice_render_studio_inner_contact_cta();

	return (string) ob_get_clean();
}

/**
 * Render the form-free Studio Contact page.
 *
 * @return string
 */
function nice_render_studio_contact_page() {
	$settings = function_exists( 'nice_theme_get_contact_channels' ) ? nice_theme_get_contact_channels( 'studio' ) : array();
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
	nice_render_studio_inner_hero( 'NICE / Studio', "Let's create something NICE.", 'A direct place to begin a Studio conversation. Approved contact channels appear here when they are ready for publication.' );
	?>
	<section class="nice-studio-inner-section nice-studio-contact-page" aria-labelledby="nice-studio-contact-options-title">
		<div class="nice-wide">
			<?php if ( $actions ) : ?>
				<header class="nice-studio-inner-heading" data-nice-reveal><p class="nice-eyebrow">Contact NICE Studio</p><h2 id="nice-studio-contact-options-title">Choose a channel.</h2></header>
				<div class="nice-studio-contact-page__actions">
					<?php foreach ( $actions as $action ) : ?>
						<a href="<?php echo esc_url( $action['url'] ); ?>" data-nice-contact-channel="<?php echo esc_attr( strtolower( $action['label'] ) ); ?>"><small><?php echo esc_html( $action['label'] ); ?></small><span><?php echo esc_html( $action['value'] ); ?></span><span aria-hidden="true">-&gt;</span></a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="nice-studio-empty-state nice-studio-empty-state--feature" data-nice-reveal>
					<p class="nice-eyebrow">Publication pending</p>
					<h2 id="nice-studio-contact-options-title">Contact details are being prepared.</h2>
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
function nice_register_studio_page_blocks() {
	$blocks = array(
		'nice/studio-section-navigation' => 'nice_render_studio_section_navigation',
		'nice/studio-services-index'     => 'nice_render_studio_services_index',
		'nice/studio-service-detail'     => 'nice_render_studio_service_detail',
		'nice/studio-case-studies-index' => 'nice_render_studio_case_studies_index',
		'nice/studio-case-study-detail'  => 'nice_render_studio_case_study_detail',
		'nice/studio-clients-index'      => 'nice_render_studio_clients_index',
		'nice/studio-team-index'         => 'nice_render_studio_team_index',
		'nice/studio-contact-page'       => 'nice_render_studio_contact_page',
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
add_action( 'init', 'nice_register_studio_page_blocks' );
