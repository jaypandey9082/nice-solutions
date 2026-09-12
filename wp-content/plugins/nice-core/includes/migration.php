<?php
/**
 * Idempotent migration of approved theme preview content into NICE Core.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the source-approved Phase 5 migration manifest.
 *
 * @return array<string, array<int, array<string, mixed>>>
 */
function nice_get_content_migration_manifest() {
	return array(
		'clients' => array(
			array( 'slug' => 'voltas-limited', 'title' => 'Voltas Limited', 'order' => 10, 'featured' => true ),
			array( 'slug' => 'zoetis', 'title' => 'Zoetis', 'order' => 20, 'featured' => true ),
			array( 'slug' => 'institute-of-actuaries-of-india', 'title' => 'Institute of Actuaries of India', 'order' => 30, 'featured' => true ),
			array( 'slug' => 'franchise-india', 'title' => 'Franchise India', 'order' => 40, 'featured' => true ),
			array( 'slug' => 'airtel', 'title' => 'Airtel', 'order' => 50, 'featured' => true ),
			array( 'slug' => 'ficci', 'title' => 'FICCI', 'order' => 60, 'featured' => true ),
			array( 'slug' => 'bajaj', 'title' => 'Bajaj', 'order' => 70, 'featured' => true ),
			array( 'slug' => 'mahindra', 'title' => 'Mahindra', 'order' => 80, 'featured' => true ),
			array( 'slug' => 'crisil', 'title' => 'CRISIL', 'order' => 90, 'featured' => true ),
			array( 'slug' => 'ajay-thakur', 'title' => 'Ajay Thakur', 'order' => 100, 'featured' => false ),
		),
		'services' => array(
			array(
				'slug'         => 'corporate-events',
				'title'        => 'Corporate Events',
				'description'  => 'Employee gatherings, family celebrations and launches shaped from concept through on-ground execution.',
				'content'      => '<p>An integral part of brand communication, Events is where our heart is. NICE brings thoughtful planning and execution to corporate and audience experiences.</p><h2>What NICE delivers</h2><ul><li>Corporate Events</li><li>Shows &amp; Concerts</li><li>Technology Integration</li></ul>',
				'service_type' => 'corporate-events',
				'image'        => 'zoetis-engagement.webp',
				'alt'          => 'Attendee posing with costumed characters at an employee event',
			),
			array(
				'slug'         => 'exhibitions-conferences',
				'title'        => 'Exhibitions & Conferences',
				'description'  => 'Conference environments, exhibition stalls and technical setups coordinated across venue, production and delivery.',
				'content'      => '<p>Thoughtful planning and execution shape NICE work across corporate and trade conferences, exhibition stall design and fabrication.</p><h2>What NICE delivers</h2><ul><li>Trade Meets &amp; Conferences</li><li>Venue Construction / Hanger Installations</li><li>Custom Stall Design &amp; Fabrication</li><li>Interactive &amp; Engaging Displays</li><li>Seminar Set-ups in Exhibitions</li><li>End-to-End Project Management</li></ul>',
				'service_type' => 'exhibitions-conferences',
				'image'        => 'exhibition-stall.webp',
				'alt'          => 'Custom exhibition stall installed inside an exhibition hall',
			),
			array(
				'slug'         => 'activations-promotions',
				'title'        => 'Activations & Promotions',
				'description'  => 'Audience-facing programmes and awareness initiatives designed to bring people into the experience.',
				'content'      => '<p>NICE plans promotions and activations designed to bring people into the experience through focused, audience-facing execution.</p><h2>What NICE delivers</h2><ul><li>Promotions &amp; Activations</li></ul>',
				'service_type' => 'activations-promotions',
				'image'        => 'power-champs.webp',
				'alt'          => 'Students presenting during a POWER CHAMPS awareness programme',
			),
			array(
				'slug'         => 'corporate-videos',
				'title'        => 'Corporate Videos',
				'description'  => 'Professionally crafted corporate profiles and product or solution audiovisuals shaped to communicate with clarity and impact.',
				'content'      => '<p>NICE creates audiovisual communication for organisations across manufacturing, technology, finance and services, bringing brand identity, core values and key offerings to the screen.</p><h2>What NICE delivers</h2><ul><li>Corporate profiles</li><li>Product and solution audiovisuals</li><li>Motion graphics</li><li>Stock-video integration</li><li>Factory and location shoots</li></ul>',
				'service_type' => 'corporate-videos',
				'image'        => 'strata-production.webp',
				'alt'          => 'NICE production crew filming inside the Strata Geosystems factory',
			),
			array(
				'slug'         => 'digital-content-creation',
				'title'        => 'Digital Content Creation',
				'description'  => 'Digital videos and platform-aware stories that make products, solutions and ideas clear for audiences across digital channels.',
				'content'      => '<p>NICE develops digital storytelling for apps, YouTube channels and social platforms, translating products, solutions and educational ideas into clear visual narratives.</p><h2>What NICE delivers</h2><ul><li>Digital videos</li><li>Social and digital storytelling</li><li>Platform-specific content</li><li>Product and solution explainers</li><li>YouTube and digital-channel content</li></ul>',
				'service_type' => 'digital-content-creation',
				'image'        => 'studio-krish-e.webp',
				'alt'          => 'Farmer working in a field in a Krish-e digital content frame',
			),
			array(
				'slug'         => 'films-entertainment',
				'title'        => 'Films & Entertainment',
				'description'  => 'Film production and cinematic storytelling carried from creative production through marketing and promotion.',
				'content'      => '<p>NICE works across film and entertainment production with a focus on meaningful cinema, cinematic storytelling and the path from production to screen.</p><h2>What NICE delivers</h2><ul><li>Film production</li><li>Cinematic storytelling</li><li>Entertainment production</li><li>Film marketing and promotions</li></ul>',
				'service_type' => 'films-entertainment',
				'image'        => 'studio-jayanti.webp',
				'alt'          => 'Jayanti cast and production team gathered outdoors',
			),
		),
		'case_studies' => array(
			array(
				'slug'         => 'voltas-fam-tastic-fiesta',
				'title'        => 'Voltas Fam-Tastic Fiesta',
				'description'  => 'An employee family fiesta for Voltas Limited at The Parsi Gymkhana, Mumbai.',
				'content'      => '<p>The Voltas Fam-Tastic Fiesta brought more than 2,000 people together for an employee family celebration at The Parsi Gymkhana in Mumbai. NICE planned and executed the event around shared moments, participation and celebration.</p>',
				'service_type' => 'corporate-events',
				'client_slug'  => 'voltas-limited',
				'location'     => 'The Parsi Gymkhana, Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 10,
				'image'        => 'voltas-fam-tastic.webp',
				'alt'          => 'NICE and Voltas team members standing on the event stage',
				'proof_value'  => '2,000+',
				'proof_label'  => 'attendees at the Voltas Fam-Tastic Fiesta',
			),
			array(
				'slug'         => 'gca-2025',
				'title'        => 'GCA 2025',
				'description'  => 'The three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India.',
				'content'      => '<p>NICE executed the three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India. The programme brought together performances, awards and panel discussions in an event shaped around learning, energy and exchange.</p>',
				'service_type' => 'exhibitions-conferences',
				'client_slug'  => 'institute-of-actuaries-of-india',
				'location'     => 'The Westin Mumbai Powai Lake, Mumbai',
				'year'         => 2025,
				'featured'     => true,
				'order'        => 20,
				'image'        => 'gca-2025.webp',
				'alt'          => 'Audience seated in a conference hall facing the stage',
			),
			array(
				'slug'         => 'zoetis-employee-engagement-day',
				'title'        => 'Zoetis Employee Engagement Day',
				'description'  => 'An employee engagement event for 200 Zoetis team members.',
				'content'      => '<p>A power-packed engagement day for 200 Zoetis team members, built around connection and celebration from start to finish.</p>',
				'service_type' => 'corporate-events',
				'client_slug'  => 'zoetis',
				'location'     => 'Zoetis Campus, Navi Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 30,
				'image'        => 'zoetis-engagement.webp',
				'alt'          => 'Attendee posing with costumed characters at an employee event',
			),
			array(
				'slug'         => 'vision-to-victory',
				'title'        => 'Vision to Victory',
				'description'  => 'A book launch for Ajay Thakur at Hotel Sahara Star, Mumbai.',
				'content'      => '<p>NICE partnered with Ajay Thakur, former Head of SME and Startups at BSE, for the launch of his book <em>Vision to Victory</em>. From concept through celebration, the event reflected his journey in India\'s SME ecosystem.</p>',
				'service_type' => 'corporate-events',
				'client_slug'  => 'ajay-thakur',
				'location'     => 'Hotel Sahara Star, Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 40,
				'image'        => 'vision-to-victory.webp',
				'alt'          => 'Guests lighting a ceremonial lamp at a book launch',
			),
			array(
				'slug'         => 'run-for-equity',
				'title'        => 'RunForEquity',
				'description'  => 'A social marathon conceived as a NICE intellectual event property.',
				'content'      => '<p>RunForEquity was conceived in 2017 as a social run and a tribute to Dr. Babasaheb Ambedkar. Its second edition brought together more than 5,000 runners and was rated among India\'s top runs through participant ratings.</p>',
				'service_type' => 'activations-promotions',
				'client_name'  => 'NICE Intellectual Property',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 50,
				'image'        => 'run-for-equity.webp',
				'alt'          => 'Participants running together during RunForEquity',
				'proof_value'  => '5,000+',
				'proof_label'  => 'runners in the second edition of RunForEquity',
			),
			array(
				'slug'         => 'strata-geosystems-factory-shoot',
				'title'        => 'Strata Geosystems Factory Shoot',
				'description'  => 'A factory shoot with drone and FPV presentation for Strata Geosystems India in Gujarat.',
				'content'      => '<p>NICE produced a factory shoot video for Strata Geosystems, capturing the scale, precision and technology of its Gujarat operations. The finished film presents the company\'s manufacturing capabilities and infrastructure for international audiences.</p>',
				'service_type' => 'corporate-videos',
				'client_name'  => 'Strata Geosystems India',
				'location'     => 'Gujarat',
				'year'         => 0,
				'featured'     => true,
				'order'        => 110,
				'image'        => 'strata-production.webp',
				'alt'          => 'NICE production crew filming inside the Strata Geosystems factory',
			),
			array(
				'slug'         => 'career-agents-academy',
				'title'        => 'Career Agents Academy',
				'description'  => 'A digital campaign video for the Bajaj Group\'s Career Agents Academy.',
				'content'      => '<p>NICE partnered with the Bajaj Group to create a digital campaign video for Career Agents Academy, a programme for insurance advisors. The film presents the programme\'s benefits and professional opportunities for digital outreach and recruitment.</p>',
				'service_type' => 'digital-content-creation',
				'client_slug'  => 'bajaj',
				'location'     => 'Mumbai',
				'year'         => 0,
				'featured'     => true,
				'order'        => 120,
				'image'        => 'studio-career-agents.webp',
				'alt'          => 'Camera monitor framing a Career Agents Academy production scene',
			),
			array(
				'slug'         => 'krish-e',
				'title'        => 'Krish-e',
				'description'  => 'Digital content for the Mahindra Group\'s Krish-e farmer facilitation app and YouTube channel.',
				'content'      => '<p>NICE partnered with the Mahindra Group to produce digital content for the Krish-e farmer facilitation app and YouTube channel. The videos simplify agri-tech solutions and best practices for rural audiences across digital touchpoints.</p>',
				'service_type' => 'digital-content-creation',
				'client_slug'  => 'mahindra',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 130,
				'image'        => 'studio-krish-e.webp',
				'alt'          => 'Farmer working in a field in a Krish-e digital content frame',
			),
			array(
				'slug'         => 'crisil-financial-literacy-content',
				'title'        => 'CRISIL Financial Literacy Content',
				'description'  => 'Drama-based visual content designed to make financial literacy accessible and engaging.',
				'content'      => '<p>NICE produced drama-based content for CRISIL Foundation\'s financial literacy initiative. The visual narrative makes financial concepts more accessible while supporting awareness and inclusion through clear, engaging storytelling.</p>',
				'service_type' => 'digital-content-creation',
				'client_slug'  => 'crisil',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 140,
				'image'        => 'studio-crisil-literacy.webp',
				'alt'          => 'NICE crew filming a financial literacy scene on a village set',
			),
			array(
				'slug'         => 'jayanti',
				'title'        => 'Jayanti',
				'description'  => 'NICE\'s film-production involvement as Executive Producers of the Marathi film Jayanti.',
				'content'      => '<p>NICE entered film production as Executive Producers of the Marathi film <em>Jayanti</em>. Alongside production, the team led marketing, promotions and the film\'s theatrical rollout.</p>',
				'service_type' => 'films-entertainment',
				'client_name'  => '',
				'location'     => '',
				'year'         => 0,
				'featured'     => true,
				'order'        => 150,
				'image'        => 'studio-jayanti.webp',
				'alt'          => 'Jayanti cast and production team gathered outdoors',
			),
		),
	);
}

/**
 * Return source-backed Events candidates that require editorial approval.
 *
 * These records intentionally omit images, proof metrics, and public approval.
 *
 * @return array<int, array<string, mixed>>
 */
/**
 * Return the LinkedIn company feed URL used to seed source candidates.
 *
 * @return string
 */
function nice_get_linkedin_company_feed_url() {
	return 'https://www.linkedin.com/company/n-i-c-e-solutions/posts/?feedView=all';
}

/**
 * Report whether a host belongs to LinkedIn.
 *
 * @param string $host Host from a parsed URL.
 * @return bool
 */
function nice_host_is_linkedin( $host ) {
	return (bool) preg_match( '#(^|\.)linkedin\.com$#i', (string) $host );
}

/**
 * Return the LinkedIn path shapes that identify one individual post.
 *
 * Anything outside this list is a feed, a profile, a company page or a search
 * result: it says where the account is, not which post a claim came from.
 *
 * @return string[] Regular expressions matched against the URL path.
 */
function nice_get_linkedin_post_path_patterns() {
	return array(
		/* https://www.linkedin.com/posts/n-i-c-e-solutions_slug-activity-123-abcd */
		'#^/posts/[^/]+$#i',
		/* https://www.linkedin.com/feed/update/urn:li:activity:123456789/ */
		'#^/(?:embed/)?feed/update/urn:li:(?:activity|share|ugcPost):[0-9]+$#i',
		/* https://www.linkedin.com/pulse/article-slug */
		'#^/pulse/[^/]+$#i',
		/* https://www.linkedin.com/company/name/posts/slug — rare, but specific. */
		'#^/(?:company|school|showcase)/[^/]+/posts/[^/]+$#i',
	);
}

/**
 * Explain why a source URL cannot support approval, or return an empty string.
 *
 * Approval records that a reviewer checked the wording against its source, so
 * the URL has to lead to that source and nothing else. A company feed reorders
 * as new posts go up and a site root leads nowhere in particular; neither can
 * be checked against, and neither may clear a record for publication.
 *
 * @param string $source_url Stored source URL.
 * @param string $origin     Where the record came from; 'linkedin' for a seeded candidate.
 * @return string Empty when the URL is usable, otherwise the reason it is not.
 */
function nice_get_source_url_problem( $source_url, $origin = '' ) {
	$source_url = trim( (string) $source_url );
	$origin     = sanitize_key( (string) $origin );

	if ( '' === $source_url ) {
		return __( 'A Source URL is required before a record can be approved.', 'nice-core' );
	}

	$parts = wp_parse_url( $source_url );

	if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
		return __( 'The Source URL is not a valid address.', 'nice-core' );
	}

	if ( 'https' !== strtolower( (string) ( $parts['scheme'] ?? '' ) ) ) {
		return __( 'The Source URL must use https.', 'nice-core' );
	}

	$path       = untrailingslashit( (string) ( $parts['path'] ?? '' ) );
	$is_linkedin = nice_host_is_linkedin( $parts['host'] );

	if ( ! $is_linkedin ) {
		if ( 'linkedin' === $origin ) {
			return __( 'This record was derived from LinkedIn, so its Source URL must be the LinkedIn post it came from.', 'nice-core' );
		}

		/* A bare domain is a publisher, not a citation. */
		if ( '' === $path ) {
			return __( 'The Source URL points at a site rather than at a specific page.', 'nice-core' );
		}

		return '';
	}

	foreach ( nice_get_linkedin_post_path_patterns() as $pattern ) {
		if ( preg_match( $pattern, $path ) ) {
			return '';
		}
	}

	return __( 'The Source URL must identify one LinkedIn post, such as https://www.linkedin.com/posts/... or a /feed/update/urn:li:activity: permalink. A company page or feed does not say which post the wording came from.', 'nice-core' );
}

/**
 * Report whether a source URL identifies a specific post rather than a feed.
 *
 * @param string $source_url Stored source URL.
 * @param string $origin     Where the record came from; 'linkedin' for a seeded candidate.
 * @return bool
 */
function nice_source_url_is_specific( $source_url, $origin = '' ) {
	return '' === nice_get_source_url_problem( $source_url, $origin );
}

/**
 * Report whether a Case Study may be moved to approved.
 *
 * @param int $post_id Case Study ID.
 * @return bool
 */
function nice_case_study_source_is_approvable( $post_id ) {
	return nice_source_url_is_specific(
		get_post_meta( $post_id, '_nice_source_url', true ),
		get_post_meta( $post_id, '_nice_source_origin', true )
	);
}

function nice_get_linkedin_case_study_draft_manifest() {
	/*
	 * The company feed, not a citation. It records where these candidates came
	 * from, but it does not identify which post any one of them came from, so a
	 * record carrying only this URL cannot be approved. An editor must replace it
	 * with the individual post URL before the record can clear review; see
	 * nice_source_url_is_specific().
	 */
	$source_url  = nice_get_linkedin_company_feed_url();
	$source_note = 'Derived from the NICE Solutions LinkedIn company feed. Replace this URL with the individual post before approving, and verify the scope, client wording, media rights, and final copy.';

	return array(
		array(
			'slug'         => 'yarn-expo-surat-2026',
			'title'        => 'Yarn Expo Surat 2026',
			'description'  => 'An exhibition presence for Texpert India and Textile Trade Buddy at Yarn Expo Surat 2026.',
			'content'      => '<p>NICE supported the Yarn Expo Surat 2026 exhibition presence for Texpert India and Textile Trade Buddy through stall design and fabrication.</p>',
			'service_type' => 'exhibitions-conferences',
			'client_name'  => 'Texpert India and Textile Trade Buddy',
			'location'     => 'Surat',
			'year'         => 2026,
			'source_url'   => $source_url,
			'source_note'  => $source_note,
		),
		array(
			'slug'         => 'netsurf-communications-conference',
			'title'        => 'Netsurf Communications Conference',
			'description'  => 'A corporate conference for Netsurf Communications at ITC Fortune in Vashi.',
			'content'      => '<p>NICE supported a Netsurf Communications conference at ITC Fortune in Vashi with event production and on-ground delivery.</p>',
			'service_type' => 'corporate-events',
			'client_name'  => 'Netsurf Communications',
			'location'     => 'ITC Fortune, Vashi',
			'year'         => 0,
			'source_url'   => $source_url,
			'source_note'  => $source_note,
		),
		array(
			'slug'         => 'livcon-mumbai-2026',
			'title'        => 'LIVCON Mumbai 2026',
			'description'  => 'An event environment delivered with Panther Pharma for LIVCON Mumbai 2026.',
			'content'      => '<p>NICE partnered with Panther Pharma for LIVCON Mumbai 2026, shaping the event environment and coordinating event delivery.</p>',
			'service_type' => 'exhibitions-conferences',
			'client_name'  => 'Panther Pharma',
			'location'     => 'Mumbai',
			'year'         => 2026,
			'source_url'   => $source_url,
			'source_note'  => $source_note,
		),
		array(
			'slug'         => 'mngl-foundation-day',
			'title'        => 'MNGL Foundation Day',
			'description'  => 'A foundation-day event for MNGL combining venue branding, stage design, and event coordination.',
			'content'      => '<p>NICE supported MNGL\'s Foundation Day with venue branding, stage design, awards coordination, and event delivery.</p>',
			'service_type' => 'corporate-events',
			'client_name'  => 'MNGL',
			'location'     => '',
			'year'         => 0,
			'source_url'   => $source_url,
			'source_note'  => $source_note,
		),
		array(
			'slug'         => 'constro-2026',
			'title'        => 'CONSTRO 2026',
			'description'  => 'Exhibition booths for Apollo Carmix and Apollo Zenith at CONSTRO 2026.',
			'content'      => '<p>NICE designed and fabricated exhibition booths for Apollo Carmix and Apollo Zenith at CONSTRO 2026.</p>',
			'service_type' => 'exhibitions-conferences',
			'client_name'  => 'Apollo Carmix and Apollo Zenith',
			'location'     => 'Pune',
			'year'         => 2026,
			'source_url'   => $source_url,
			'source_note'  => $source_note,
		),
	);
}

/**
 * Return the approved Events section Page manifest.
 *
 * @return array<int, array{slug: string, title: string, template: string}>
 */
function nice_get_events_page_manifest() {
	return array(
		array( 'slug' => 'services', 'title' => 'Events Services', 'template' => 'page-events-services' ),
		array( 'slug' => 'case-studies', 'title' => 'Events Case Studies', 'template' => 'page-events-case-studies' ),
		array( 'slug' => 'clients', 'title' => 'Events Clients', 'template' => 'page-events-clients' ),
		array( 'slug' => 'team', 'title' => 'Events Team', 'template' => 'page-events-team' ),
		array( 'slug' => 'contact', 'title' => 'Events Contact', 'template' => 'page-events-contact' ),
	);
}

/**
 * Provision only the approved structural Events Pages.
 *
 * Existing page content and titles are preserved.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
/**
 * Provision one division's section pages at the path this installation uses.
 *
 * The combined site nests them under an /events/ or /studio/ landing page. A
 * dedicated division installation owns its hostname, so the same pages sit at
 * the root with no landing page above them.
 *
 * @param string $division      Division slug.
 * @param string $parent_title  Title for the landing page on the combined site.
 * @param array  $manifest      Section page manifest.
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_provision_division_pages( $division, $parent_title, $manifest ) {
	$summary = array( 'created' => 0, 'skipped' => 0, 'errors' => array(), 'home_page_id' => 0 );

	if ( ! nice_division_is_local( $division ) ) {
		return $summary;
	}

	$prefix = nice_get_division_prefix( $division );

	/*
	 * The landing page exists in both shapes. On the combined site it also acts
	 * as the parent that puts the section pages behind /events/ or /studio/; on a
	 * division installation it is the page an administrator selects as the front
	 * page, and the section pages sit at the root beside it. Its slug stays the
	 * division either way, because the hero metadata and the page-{slug} template
	 * are both keyed to it.
	 */
	$home = get_page_by_path( $division, OBJECT, 'page' );

	if ( ! $home instanceof WP_Post ) {
		$new_home_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => $division,
				'post_title'  => $parent_title,
			),
			true
		);

		if ( is_wp_error( $new_home_id ) ) {
			$summary['errors'][] = $new_home_id->get_error_message();
			return $summary;
		}

		$home = get_post( $new_home_id );
		++$summary['created'];
	} else {
		++$summary['skipped'];
	}

	$summary['home_page_id'] = $home->ID;
	$parent_id               = $prefix ? $home->ID : 0;

	foreach ( $manifest as $record ) {
		$path = $prefix ? $prefix . '/' . $record['slug'] : $record['slug'];
		$page = get_page_by_path( $path, OBJECT, 'page' );

		if ( $page instanceof WP_Post ) {
			++$summary['skipped'];
		} else {
			$page_id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_parent' => $parent_id,
					'post_name'   => $record['slug'],
					'post_title'  => $record['title'],
				),
				true
			);

			if ( is_wp_error( $page_id ) ) {
				$summary['errors'][] = $page_id->get_error_message();
				continue;
			}

			$page = get_post( $page_id );
			++$summary['created'];
		}

		update_post_meta( $page->ID, '_wp_page_template', $record['template'] );
	}

	return $summary;
}

/**
 * Provision the approved Events section pages.
 *
 * Existing page content and titles are preserved.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_provision_events_pages() {
	return nice_provision_division_pages( 'events', 'Events', nice_get_events_page_manifest() );
}

/**
 * Return the approved Studio section Page manifest.
 *
 * @return array<int, array{slug: string, title: string, template: string}>
 */
function nice_get_studio_page_manifest() {
	return array(
		array( 'slug' => 'services', 'title' => 'Studio Services', 'template' => 'page-studio-services' ),
		array( 'slug' => 'case-studies', 'title' => 'Studio Case Studies', 'template' => 'page-studio-case-studies' ),
		array( 'slug' => 'clients', 'title' => 'Studio Clients', 'template' => 'page-studio-clients' ),
		array( 'slug' => 'team', 'title' => 'Studio Team', 'template' => 'page-studio-team' ),
		array( 'slug' => 'contact', 'title' => 'Studio Contact', 'template' => 'page-studio-contact' ),
	);
}

/**
 * Provision the approved Studio Home Page and inner pages.
 *
 * Existing page content and titles are preserved.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_provision_studio_pages() {
	return nice_provision_division_pages( 'studio', 'Studio', nice_get_studio_page_manifest() );
}

/**
 * Backward-compatible wrapper for Studio page provisioning.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_provision_studio_page() {
	return nice_provision_studio_pages();
}

/**
 * Find a record regardless of status.
 *
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return WP_Post|null
 */
function nice_find_migrated_post( $slug, $post_type ) {
	global $wpdb;

	/*
	 * Deliberately a direct lookup rather than get_posts(). A query carrying a
	 * post name is treated as a single-post request, and WP_Query discards
	 * results in a non-public status when no user is logged in. Under WP-CLI
	 * nobody is logged in, so every draft slug looked invisible here and reruns
	 * created duplicate records instead of stopping.
	 */
	$post_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s ORDER BY ID ASC LIMIT 1",
			sanitize_title( $slug ),
			$post_type
		)
	);

	return $post_id ? get_post( $post_id ) : null;
}

/**
 * Create source-backed Events candidates as private editorial drafts.
 *
 * An existing slug in any status is a hard stop. This protects published
 * records and every editor change from migration reruns.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_migrate_linkedin_case_study_drafts() {
	$summary = array(
		'created' => 0,
		'skipped' => 0,
		'errors'  => array(),
	);

	/* Every candidate is an Events record, so an installation that does not own Events seeds none. */
	if ( ! nice_division_is_local( 'events' ) ) {
		return $summary;
	}

	$service_types = nice_get_approved_service_types();

	foreach ( nice_get_linkedin_case_study_draft_manifest() as $record ) {
		if ( nice_find_migrated_post( $record['slug'], 'nice_case_study' ) ) {
			++$summary['skipped'];
			continue;
		}

		$service_type = sanitize_title( $record['service_type'] ?? '' );
		if ( empty( $service_types[ $service_type ] ) || 'events' !== $service_types[ $service_type ]['division'] ) {
			$summary['errors'][] = sprintf( 'Invalid Events service type for source draft: %s', sanitize_text_field( $record['title'] ?? '' ) );
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'nice_case_study',
				'post_status'  => 'draft',
				'post_name'    => sanitize_title( $record['slug'] ),
				'post_title'   => sanitize_text_field( $record['title'] ),
				'post_excerpt' => sanitize_textarea_field( $record['description'] ?? '' ),
				'post_content' => wp_kses_post( $record['content'] ?? '' ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$summary['errors'][] = $post_id->get_error_message();
			continue;
		}

		// Keep source intake private even if another hook changes the insert status.
		if ( 'draft' !== get_post_status( $post_id ) ) {
			$status_result = wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'draft',
				),
				true
			);

			if ( is_wp_error( $status_result ) || 'draft' !== get_post_status( $post_id ) ) {
				wp_delete_post( $post_id, true );
				$summary['errors'][] = sprintf( 'Could not keep source candidate as a draft: %s', sanitize_text_field( $record['title'] ) );
				continue;
			}
		}

		wp_set_object_terms( $post_id, $service_type, 'nice_service_type', false );
		update_post_meta( $post_id, '_nice_client_name', sanitize_text_field( $record['client_name'] ?? '' ) );
		update_post_meta( $post_id, '_nice_location', sanitize_text_field( $record['location'] ?? '' ) );
		update_post_meta( $post_id, '_nice_year', nice_sanitize_year( $record['year'] ?? 0 ) );
		update_post_meta( $post_id, '_nice_featured', 0 );
		update_post_meta( $post_id, '_nice_display_order', 0 );
		update_post_meta( $post_id, '_nice_source_url', nice_sanitize_https_url( $record['source_url'] ?? '' ) );
		update_post_meta( $post_id, '_nice_source_note', sanitize_textarea_field( $record['source_note'] ?? '' ) );
		update_post_meta( $post_id, '_nice_source_approval_status', 'draft' );
		/* Records where the wording came from, so approval can insist on a post from there. */
		update_post_meta( $post_id, '_nice_source_origin', 'linkedin' );
		++$summary['created'];
	}

	return $summary;
}

/**
 * Return placeholder team records that establish the structure to fill in.
 *
 * These are deliberately unmistakable placeholders rather than invented people.
 * They carry no portrait, because there is no approved likeness to attach and a
 * production photograph standing in for a face would misrepresent the team.
 *
 * Display order follows the migration convention of increments of ten so later
 * additions can be interleaved without renumbering the existing rows.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_team_member_draft_manifest() {
	$manifest = array();

	foreach ( array( 'events', 'studio' ) as $division ) {
		$label = ucfirst( $division );

		foreach ( array( 'One', 'Two', 'Three' ) as $index => $position ) {
			$manifest[] = array(
				'slug'     => sprintf( '%s-team-member-%s', $division, strtolower( $position ) ),
				'title'    => sprintf( '%s Team Member %s', $label, $position ),
				'role'     => sprintf( '%s role to be confirmed', $label ),
				'division' => $division,
				'order'    => ( $index + 1 ) * 10,
			);
		}
	}

	return $manifest;
}

/**
 * Create placeholder team members as private editorial drafts.
 *
 * An existing slug in any status is a hard stop, so reruns never duplicate a
 * record or overwrite an editor's changes.
 *
 * @return array{created: int, skipped: int, errors: string[]}
 */
function nice_migrate_team_member_drafts() {
	$summary   = array(
		'created' => 0,
		'skipped' => 0,
		'errors'  => array(),
	);
	$divisions = nice_get_approved_divisions();

	foreach ( nice_get_team_member_draft_manifest() as $record ) {
		/* A division installation seeds its own roster only; the gateway seeds none. */
		if ( ! nice_division_is_local( $record['division'] ?? '' ) ) {
			continue;
		}

		if ( nice_find_migrated_post( $record['slug'], 'nice_team_member' ) ) {
			++$summary['skipped'];
			continue;
		}

		$division = sanitize_title( $record['division'] ?? '' );
		if ( empty( $divisions[ $division ] ) ) {
			$summary['errors'][] = sprintf( 'Invalid division for team member: %s', sanitize_text_field( $record['title'] ?? '' ) );
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'nice_team_member',
				'post_status' => 'draft',
				'post_name'   => sanitize_title( $record['slug'] ),
				'post_title'  => sanitize_text_field( $record['title'] ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$summary['errors'][] = $post_id->get_error_message();
			continue;
		}

		// Keep placeholder people unpublished even if another hook changes the insert status.
		if ( 'draft' !== get_post_status( $post_id ) ) {
			$status_result = wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'draft',
				),
				true
			);

			if ( is_wp_error( $status_result ) || 'draft' !== get_post_status( $post_id ) ) {
				wp_delete_post( $post_id, true );
				$summary['errors'][] = sprintf( 'Could not keep the placeholder team member as a draft: %s', sanitize_text_field( $record['title'] ) );
				continue;
			}
		}

		wp_set_object_terms( $post_id, $division, 'nice_division', false );
		update_post_meta( $post_id, '_nice_role', sanitize_text_field( $record['role'] ?? '' ) );
		update_post_meta( $post_id, '_nice_display_order', nice_sanitize_integer( $record['order'] ?? 0 ) );
		++$summary['created'];
	}

	return $summary;
}

/**
 * Import one existing theme image into the media library once.
 *
 * @param string $filename Theme image filename.
 * @param string $alt      Approved alt text.
 * @return int|WP_Error Attachment ID or error.
 */
function nice_migrate_media_attachment( $filename, $alt ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => '_nice_source_asset',
			'meta_value'     => sanitize_file_name( $filename ),
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = get_theme_file_path( '/assets/images/' . sanitize_file_name( $filename ) );
	if ( ! is_readable( $source ) ) {
		return new WP_Error( 'nice_missing_source_image', sprintf( 'Source image is unavailable: %s', $filename ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$temp_file = wp_tempnam( $filename );
	if ( ! $temp_file || ! copy( $source, $temp_file ) ) {
		return new WP_Error( 'nice_media_copy_failed', sprintf( 'Could not prepare image: %s', $filename ) );
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => sanitize_file_name( $filename ),
			'tmp_name' => $temp_file,
		),
		0,
		pathinfo( $filename, PATHINFO_FILENAME )
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $temp_file );
		return $attachment_id;
	}

	update_post_meta( $attachment_id, '_nice_source_asset', sanitize_file_name( $filename ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );

	return $attachment_id;
}

/**
 * Assign the Studio reference hero once, without restoring an editor removal.
 *
 * @return array{status: string, attachment_id: int, message: string}
 */
function nice_initialize_studio_hero_media() {
	$summary = array(
		'status'        => 'skipped',
		'attachment_id' => 0,
		'message'       => '',
	);
	if ( ! nice_division_is_local( 'studio' ) ) {
		$summary['status']  = 'foreign';
		$summary['message'] = 'Studio is served by another installation.';
		return $summary;
	}

	$studio = get_page_by_path( 'studio', OBJECT, 'page' );

	if ( ! $studio instanceof WP_Post ) {
		$summary['status']  = 'unavailable';
		$summary['message'] = 'The Studio Home Page is unavailable.';
		return $summary;
	}

	if ( metadata_exists( 'post', $studio->ID, '_nice_studio_hero_media_initialized' ) ) {
		return $summary;
	}

	$filename = 'studio-reference-hero.webp';
	$source   = get_theme_file_path( '/assets/images/' . $filename );
	if ( ! is_readable( $source ) ) {
		$summary['status']  = 'unavailable';
		$summary['message'] = 'The optional Studio reference hero asset is unavailable.';
		return $summary;
	}

	$attachment_id = nice_migrate_media_attachment(
		$filename,
		__( 'Creative production team working on a cinematic studio set', 'nice-core' )
	);

	if ( is_wp_error( $attachment_id ) ) {
		$summary['status']  = 'unavailable';
		$summary['message'] = $attachment_id->get_error_message();
		return $summary;
	}

	update_post_meta( $studio->ID, '_nice_studio_hero_image_id', absint( $attachment_id ) );
	update_post_meta( $studio->ID, '_nice_studio_hero_focal_x', 50 );
	update_post_meta( $studio->ID, '_nice_studio_hero_focal_y', 50 );
	update_post_meta( $studio->ID, '_nice_studio_hero_reference', 1 );
	update_post_meta( $studio->ID, '_nice_studio_hero_media_initialized', 1 );

	$summary['status']        = 'initialized';
	$summary['attachment_id'] = absint( $attachment_id );

	return $summary;
}

/**
 * Initialize Events reference media once, preserving all prior editorial choices.
 * Callable directly with wp eval 'print_r( nice_initialize_events_hero_media() );'.
 *
 * @return array{status: string, attachment_id: int, message: string}
 */
function nice_initialize_events_hero_media() {
	$summary = array( 'status' => 'skipped', 'attachment_id' => 0, 'message' => '' );

	if ( ! nice_division_is_local( 'events' ) ) {
		$summary['status']  = 'foreign';
		$summary['message'] = 'Events is served by another installation.';
		return $summary;
	}

	$events = get_page_by_path( 'events', OBJECT, 'page' );
	if ( ! $events instanceof WP_Post || ! nice_is_events_home_page( $events->ID ) ) {
		$summary['status']  = 'unavailable';
		$summary['message'] = 'The Events Page is unavailable.';
		return $summary;
	}

	// Existence matters: a saved zero, false or empty selection is intentional.
	foreach ( array( 'media_initialized', 'image_id', 'mobile_image_id', 'focal_x', 'focal_y', 'reference' ) as $field ) {
		if ( metadata_exists( 'post', $events->ID, '_nice_events_hero_' . $field ) ) {
			return $summary;
		}
	}

	$filename = 'events-reference-hero.webp';
	if ( ! is_readable( get_theme_file_path( '/assets/images/' . $filename ) ) ) {
		$summary['status']  = 'unavailable';
		$summary['message'] = 'The optional Events reference hero asset is unavailable.';
		return $summary;
	}
	$attachment_id = nice_migrate_media_attachment( $filename, __( 'Temporary Events reference imagery', 'nice-core' ) );
	if ( is_wp_error( $attachment_id ) || ! nice_sanitize_hero_image_id( $attachment_id ) ) {
		$summary['status']  = 'unavailable';
		$summary['message'] = is_wp_error( $attachment_id ) ? $attachment_id->get_error_message() : 'The Events reference asset is not an image attachment.';
		return $summary;
	}

	update_post_meta( $events->ID, '_nice_events_hero_image_id', $attachment_id );
	update_post_meta( $events->ID, '_nice_events_hero_focal_x', 50 );
	update_post_meta( $events->ID, '_nice_events_hero_focal_y', 50 );
	update_post_meta( $events->ID, '_nice_events_hero_reference', 1 );
	update_post_meta( $events->ID, '_nice_events_hero_media_initialized', 1 );
	$summary['status']        = 'initialized';
	$summary['attachment_id'] = $attachment_id;
	return $summary;
}

/**
 * Insert one approved post without overwriting an existing record.
 *
 * @param string               $post_type Post type.
 * @param array<string, mixed> $record    Migration record.
 * @return array{status: string, post_id: int}|WP_Error
 */
function nice_migrate_content_post( $post_type, $record ) {
	$existing = nice_find_migrated_post( $record['slug'], $post_type );

	if ( $existing ) {
		return array( 'status' => 'skipped', 'post_id' => $existing->ID );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			'post_name'    => sanitize_title( $record['slug'] ),
			'post_title'   => sanitize_text_field( $record['title'] ),
			'post_excerpt' => sanitize_textarea_field( $record['description'] ?? '' ),
			'post_content' => wp_kses_post( $record['content'] ?? $record['description'] ?? '' ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	return array( 'status' => 'created', 'post_id' => $post_id );
}

/**
 * Enrich untouched Phase 5 records with source-approved Phase 6 editor content.
 *
 * Editorial changes are never overwritten.
 *
 * @param string               $post_type Post type.
 * @param array<string, mixed> $record    Migration record.
 * @return int Number of updated fields.
 */
function nice_enrich_migrated_content( $post_type, $record ) {
	$post = nice_find_migrated_post( $record['slug'], $post_type );

	if ( ! $post instanceof WP_Post ) {
		return 0;
	}

	$updates        = 0;
	$legacy_content = trim( (string) ( $record['description'] ?? '' ) );
	$new_content    = trim( (string) ( $record['content'] ?? '' ) );

	if ( $new_content && trim( $post->post_content ) === $legacy_content ) {
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => wp_kses_post( $new_content ),
			)
		);
		++$updates;
	}

	if ( 'nice_case_study' === $post_type ) {
		foreach ( array( '_nice_location' => 'location', '_nice_proof_value' => 'proof_value', '_nice_proof_label' => 'proof_label' ) as $meta_key => $record_key ) {
			if ( ! get_post_meta( $post->ID, $meta_key, true ) && ! empty( $record[ $record_key ] ) ) {
				update_post_meta( $post->ID, $meta_key, sanitize_text_field( $record[ $record_key ] ) );
				++$updates;
			}
		}
	}

	return $updates;
}

/**
 * Return the division a manifest record belongs to.
 *
 * Services and Case Studies carry a Service Type, and every approved Service
 * Type belongs to exactly one division, so the record's owner is derivable
 * rather than something the manifest has to repeat.
 *
 * @param array<string, mixed> $record Manifest record.
 * @return string Division slug, or an empty string when the record has no division.
 */
function nice_get_manifest_record_division( $record ) {
	$service_type = sanitize_title( $record['service_type'] ?? '' );
	$definitions  = nice_get_approved_service_types();

	return $definitions[ $service_type ]['division'] ?? '';
}

/**
 * Run the complete approved content migration.
 *
 * @return array<string, mixed>|WP_Error
 */
function nice_run_content_migration() {
	/*
	 * The gateway owns curated previews and links out; the division datasets
	 * belong to the division installations. Running this here would publish both
	 * divisions from one database, which is exactly what the three-site split
	 * exists to prevent, so refuse rather than half-apply it.
	 */
	if ( nice_is_gateway_site() ) {
		return new WP_Error(
			'nice_gateway_migration_refused',
			__( 'This installation is configured as the NICE gateway, which owns no division content. Run the content migration on the Events and Studio installations instead.', 'nice-core' )
		);
	}

	$manifest = nice_get_content_migration_manifest();
	$summary  = array(
		'terms'        => nice_ensure_default_terms(),
		'pages'        => nice_provision_events_pages(),
		'studio_page'  => nice_provision_studio_page(),
		'clients'      => array( 'created' => 0, 'skipped' => 0, 'foreign' => 0 ),
		/* 'foreign' counts records that belong to the sibling installation. */
		'services'     => array( 'created' => 0, 'skipped' => 0, 'foreign' => 0 ),
		'case_studies' => array( 'created' => 0, 'skipped' => 0, 'foreign' => 0 ),
		'source_drafts' => array( 'created' => 0, 'skipped' => 0, 'errors' => array() ),
		'team_drafts'   => array( 'created' => 0, 'skipped' => 0, 'errors' => array() ),
		'media'        => array( 'linked' => 0, 'errors' => array() ),
		'enriched'     => 0,
		'studio_hero'  => array( 'status' => 'skipped', 'attachment_id' => 0, 'message' => '' ),
		'events_hero'  => array( 'status' => 'skipped', 'attachment_id' => 0, 'message' => '' ),
	);

	if ( ! empty( $summary['terms']['errors'] ) ) {
		return new WP_Error( 'nice_term_migration_failed', implode( ' ', $summary['terms']['errors'] ) );
	}
	if ( ! empty( $summary['pages']['errors'] ) ) {
		return new WP_Error( 'nice_page_migration_failed', implode( ' ', $summary['pages']['errors'] ) );
	}
	if ( ! empty( $summary['studio_page']['errors'] ) ) {
		return new WP_Error( 'nice_studio_page_migration_failed', implode( ' ', $summary['studio_page']['errors'] ) );
	}

	$summary['studio_hero'] = nice_initialize_studio_hero_media();
	$summary['events_hero'] = nice_initialize_events_hero_media();
	$summary['source_drafts'] = nice_migrate_linkedin_case_study_drafts();
	if ( ! empty( $summary['source_drafts']['errors'] ) ) {
		return new WP_Error( 'nice_source_draft_migration_failed', implode( ' ', $summary['source_drafts']['errors'] ) );
	}

	$summary['team_drafts'] = nice_migrate_team_member_drafts();
	if ( ! empty( $summary['team_drafts']['errors'] ) ) {
		return new WP_Error( 'nice_team_draft_migration_failed', implode( ' ', $summary['team_drafts']['errors'] ) );
	}

	$client_ids = array();
	foreach ( $manifest['clients'] as $record ) {
		$result = nice_migrate_content_post( 'nice_client', $record );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$client_ids[ $record['slug'] ] = $result['post_id'];
		++$summary['clients'][ $result['status'] ];
		if ( 'created' === $result['status'] ) {
			update_post_meta( $result['post_id'], '_nice_display_order', (int) $record['order'] );
			update_post_meta( $result['post_id'], '_nice_featured', $record['featured'] ? 1 : 0 );
		}
	}

	foreach ( $manifest['services'] as $record ) {
		/*
		 * An installation imports only the divisions it owns. Seeding a sibling's
		 * services here would put the same record on two hostnames, each claiming
		 * to be canonical.
		 */
		if ( ! nice_division_is_local( nice_get_manifest_record_division( $record ) ) ) {
			++$summary['services']['foreign'];
			continue;
		}

		$result = nice_migrate_content_post( 'nice_service', $record );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		++$summary['services'][ $result['status'] ];

		if ( 'created' === $result['status'] ) {
			wp_set_object_terms( $result['post_id'], $record['service_type'], 'nice_service_type', false );
			$attachment_id = nice_migrate_media_attachment( $record['image'], $record['alt'] );
			if ( is_wp_error( $attachment_id ) ) {
				$summary['media']['errors'][] = $attachment_id->get_error_message();
			} else {
				set_post_thumbnail( $result['post_id'], $attachment_id );
				++$summary['media']['linked'];
			}
		}

		$summary['enriched'] += nice_enrich_migrated_content( 'nice_service', $record );
	}

	foreach ( $manifest['case_studies'] as $record ) {
		if ( ! nice_division_is_local( nice_get_manifest_record_division( $record ) ) ) {
			++$summary['case_studies']['foreign'];
			continue;
		}

		$result = nice_migrate_content_post( 'nice_case_study', $record );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$post_id = $result['post_id'];
		++$summary['case_studies'][ $result['status'] ];

		if ( 'created' === $result['status'] ) {
			wp_set_object_terms( $post_id, $record['service_type'], 'nice_service_type', false );
			update_post_meta( $post_id, '_nice_client_id', $client_ids[ $record['client_slug'] ?? '' ] ?? 0 );
			update_post_meta( $post_id, '_nice_client_name', sanitize_text_field( $record['client_name'] ?? '' ) );
			update_post_meta( $post_id, '_nice_location', sanitize_text_field( $record['location'] ) );
			update_post_meta( $post_id, '_nice_year', nice_sanitize_year( $record['year'] ) );
			update_post_meta( $post_id, '_nice_featured', $record['featured'] ? 1 : 0 );
			update_post_meta( $post_id, '_nice_display_order', (int) $record['order'] );
			update_post_meta( $post_id, '_nice_proof_value', sanitize_text_field( $record['proof_value'] ?? '' ) );
			update_post_meta( $post_id, '_nice_proof_label', sanitize_text_field( $record['proof_label'] ?? '' ) );
			$attachment_id = nice_migrate_media_attachment( $record['image'], $record['alt'] );
			if ( is_wp_error( $attachment_id ) ) {
				$summary['media']['errors'][] = $attachment_id->get_error_message();
			} else {
				set_post_thumbnail( $post_id, $attachment_id );
				++$summary['media']['linked'];
			}
		}

		$summary['enriched'] += nice_enrich_migrated_content( 'nice_case_study', $record );
	}

	update_option( 'nice_core_content_migration_version', NICE_CORE_VERSION, false );

	if ( $summary['pages']['created'] || $summary['studio_page']['created'] ) {
		flush_rewrite_rules( false );
	}

	return $summary;
}

/**
 * Run the migration from WP-CLI.
 */
function nice_cli_migrate_content() {
	$result = nice_run_content_migration();

	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}

	foreach ( array( 'clients', 'services', 'case_studies' ) as $content_type ) {
		WP_CLI::log(
			sprintf(
				'%s: %d created, %d skipped, %d owned by another installation',
				ucwords( str_replace( '_', ' ', $content_type ) ),
				$result[ $content_type ]['created'],
				$result[ $content_type ]['skipped'],
				$result[ $content_type ]['foreign']
			)
		);
	}

	WP_CLI::log( sprintf( 'Installation identity: %s', nice_get_site_identity_label() ) );

	WP_CLI::log( sprintf( 'Terms: %d created, %d existing', $result['terms']['created'], $result['terms']['existing'] ) );
	WP_CLI::log( sprintf( 'Events pages: %d created, %d existing', $result['pages']['created'], $result['pages']['skipped'] ) );
	WP_CLI::log( sprintf( 'Studio Home: %d created, %d existing', $result['studio_page']['created'], $result['studio_page']['skipped'] ) );
	WP_CLI::log( sprintf( 'Source-backed fields enriched: %d', $result['enriched'] ) );
	WP_CLI::log( sprintf( 'LinkedIn source drafts: %d created, %d skipped', $result['source_drafts']['created'], $result['source_drafts']['skipped'] ) );
	WP_CLI::log( sprintf( 'Team member drafts: %d created, %d skipped', $result['team_drafts']['created'], $result['team_drafts']['skipped'] ) );
	WP_CLI::log( sprintf( 'Media linked: %d', $result['media']['linked'] ) );
	WP_CLI::log( sprintf( 'Studio reference hero: %s', $result['studio_hero']['status'] ) );
	WP_CLI::log( sprintf( 'Events reference hero: %s', $result['events_hero']['status'] ) );
	if ( $result['events_hero']['message'] ) {
		WP_CLI::warning( $result['events_hero']['message'] );
	}

	if ( $result['studio_hero']['message'] ) {
		WP_CLI::warning( $result['studio_hero']['message'] );
	}

	if ( $result['media']['errors'] ) {
		foreach ( array_unique( $result['media']['errors'] ) as $error ) {
			WP_CLI::warning( $error );
		}
	}

	WP_CLI::success( 'NICE content migration completed.' );
}

/**
 * Register the optional CLI command without affecting web requests.
 */
function nice_register_cli_commands() {
	WP_CLI::add_command( 'nice migrate-content', 'nice_cli_migrate_content' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	add_action( 'cli_init', 'nice_register_cli_commands' );
}
