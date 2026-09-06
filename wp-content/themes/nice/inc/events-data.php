<?php
/**
 * Events presentation adapters with a safe pre-migration fallback.
 *
 * NICE Core owns migrated business content. This file retains source-approved
 * presentation metadata and fallback records so Events remains usable when the
 * plugin is unavailable or only partially populated.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the source-approved Events service fallback.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_events_services_fallback() {
	return array(
		array(
			'slug'         => 'corporate-events',
			'name'         => 'Corporate Events',
			'description'  => 'Employee gatherings, family celebrations and launches shaped from concept through on-ground execution.',
			'image'        => 'zoetis-engagement',
			'image_mobile' => 'zoetis-engagement-480',
			'width'        => 543,
			'height'       => 305,
			'alt'          => 'Attendee posing with costumed characters at an employee event',
		),
		array(
			'slug'         => 'exhibitions-conferences',
			'name'         => 'Exhibitions & Conferences',
			'description'  => 'Conference environments, exhibition stalls and technical setups coordinated across venue, production and delivery.',
			'image'        => 'exhibition-stall',
			'image_mobile' => 'exhibition-stall-480',
			'width'        => 556,
			'height'       => 316,
			'alt'          => 'Custom exhibition stall installed inside an exhibition hall',
		),
		array(
			'slug'         => 'activations-promotions',
			'name'         => 'Activations & Promotions',
			'description'  => 'Audience-facing programmes and awareness initiatives designed to bring people into the experience.',
			'image'        => 'power-champs',
			'image_mobile' => 'power-champs-480',
			'width'        => 739,
			'height'       => 325,
			'alt'          => 'Students presenting during a POWER CHAMPS awareness programme',
		),
	);
}

/**
 * Return the three Events service previews from NICE Core or fallback data.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_events_service_previews() {
	$fallback = nice_get_events_services_fallback();

	if ( function_exists( 'nice_get_events_services' ) ) {
		$services = nice_get_events_services();
		$by_slug  = array();

		foreach ( $services as $service ) {
			if ( $service instanceof WP_Post ) {
				$by_slug[ $service->post_name ] = $service;
			}
		}

		if ( count( $by_slug ) >= count( $fallback ) ) {
			$previews = array();

			foreach ( $fallback as $item ) {
				if ( ! isset( $by_slug[ $item['slug'] ] ) ) {
					return apply_filters( 'nice_events_service_previews', $fallback );
				}

				$service              = $by_slug[ $item['slug'] ];
				$item['name']          = $service->post_title;
				$item['description']   = $service->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $service->post_content ), 28 );
				$item['attachment_id'] = get_post_thumbnail_id( $service );
				$previews[]            = $item;
			}

			return apply_filters( 'nice_events_service_previews', $previews );
		}
	}

	return apply_filters( 'nice_events_service_previews', $fallback );
}

/**
 * Return the source-approved Events project fallback.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_events_project_previews_fallback() {
	return array(
		array(
			'slug'         => 'voltas-fam-tastic-fiesta',
			'class'        => 'nice-events-project--feature',
			'image'        => 'voltas-fam-tastic',
			'image_mobile' => 'voltas-fam-tastic-480',
			'width'        => 650,
			'height'       => 378,
			'alt'          => 'NICE and Voltas team members standing on the event stage',
			'title'        => 'Voltas Fam-Tastic Fiesta',
			'client'       => 'Voltas Limited',
			'description'  => 'An employee family fiesta for Voltas Limited at The Parsi Gymkhana, Mumbai.',
		),
		array(
			'slug'         => 'gca-2025',
			'class'        => 'nice-events-project--secondary',
			'image'        => 'gca-2025',
			'image_mobile' => 'gca-2025-480',
			'width'        => 569,
			'height'       => 293,
			'alt'          => 'Audience seated in a conference hall facing the stage',
			'title'        => 'GCA 2025',
			'client'       => 'Institute of Actuaries of India',
			'description'  => 'The three-day 24th Global Conference of Actuaries for the Institute of Actuaries of India.',
		),
		array(
			'slug'         => 'vision-to-victory',
			'class'        => 'nice-events-project--compact',
			'image'        => 'vision-to-victory',
			'image_mobile' => 'vision-to-victory-360',
			'width'        => 468,
			'height'       => 263,
			'alt'          => 'Guests lighting a ceremonial lamp at a book launch',
			'title'        => 'Vision to Victory',
			'client'       => 'Ajay Thakur',
			'description'  => 'A book launch for Ajay Thakur at Hotel Sahara Star, Mumbai.',
		),
		array(
			'slug'         => 'run-for-equity',
			'class'        => 'nice-events-project--final',
			'image'        => 'run-for-equity',
			'image_mobile' => 'run-for-equity-360',
			'width'        => 453,
			'height'       => 331,
			'alt'          => 'Participants running together during RunForEquity',
			'title'        => 'RunForEquity',
			'client'       => 'NICE Intellectual Property',
			'description'  => 'A social marathon conceived as a NICE intellectual event property.',
		),
	);
}

/**
 * Return Events project previews from NICE Core or fallback data.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_events_project_previews() {
	$fallback = nice_get_events_project_previews_fallback();

	if ( function_exists( 'nice_get_featured_case_studies' ) ) {
		$case_studies = nice_get_featured_case_studies(
			array(
				'division'       => 'events',
				'posts_per_page' => 4,
			)
		);

		if ( ! $case_studies ) {
			return apply_filters( 'nice_events_project_previews', $fallback );
		}

		$fallback_by_slug = array_column( $fallback, null, 'slug' );
		$classes          = array(
			'nice-events-project--feature',
			'nice-events-project--secondary',
			'nice-events-project--compact',
			'nice-events-project--final',
		);
		$previews = array();

		foreach ( $case_studies as $index => $case_study ) {
			$item = wp_parse_args(
				$fallback_by_slug[ $case_study->post_name ] ?? array(),
				array(
					'slug'         => $case_study->post_name,
					'image'        => '',
					'image_mobile' => '',
					'width'        => 1,
					'height'       => 1,
					'alt'          => '',
				)
			);

			$item['class']         = $classes[ $index ] ?? 'nice-events-project--secondary';
			$item['title']         = $case_study->post_title;
			$item['description']   = $case_study->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $case_study->post_content ), 30 );
			$item['client']        = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : '';
			$item['attachment_id'] = get_post_thumbnail_id( $case_study );
			if ( $item['attachment_id'] && ! $item['alt'] ) {
				$item['alt'] = get_post_meta( $item['attachment_id'], '_wp_attachment_image_alt', true );
			}
			$item['url']           = function_exists( 'nice_get_events_content_url' ) ? nice_get_events_content_url( $case_study ) : '';
			$previews[]            = $item;
		}

		return apply_filters( 'nice_events_project_previews', $previews );
	}

	return apply_filters( 'nice_events_project_previews', $fallback );
}

/**
 * Return source-supported Events client names from NICE Core or fallback data.
 *
 * @return string[]
 */
function nice_get_events_clients() {
	$fallback = array(
		'voltas-limited'                  => 'Voltas Limited',
		'zoetis'                          => 'Zoetis',
		'institute-of-actuaries-of-india' => 'Institute of Actuaries of India',
		'franchise-india'                 => 'Franchise India',
		'airtel'                          => 'Airtel',
		'ficci'                           => 'FICCI',
	);

	if ( function_exists( 'nice_get_featured_clients' ) ) {
		$clients = nice_get_featured_clients( array( 'posts_per_page' => 6 ) );

		if ( $clients ) {
			return apply_filters( 'nice_events_clients', wp_list_pluck( $clients, 'post_title' ) );
		}
	}

	return apply_filters( 'nice_events_clients', array_values( $fallback ) );
}
