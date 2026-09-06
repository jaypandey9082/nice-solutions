<?php
/**
 * Landing-page presentation adapters with safe source-approved fallbacks.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the curated landing project fallback.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_landing_project_previews_fallback() {
	return array(
		array(
			'slug'     => 'voltas-fam-tastic-fiesta',
			'class'    => 'nice-landing-project--feature',
			'image'    => 'voltas-fam-tastic',
			'width'    => 650,
			'height'   => 378,
			'alt'      => 'NICE and Voltas team members standing on the event stage',
			'title'    => 'Voltas Fam-Tastic Fiesta',
			'client'   => 'Voltas Limited',
			'division' => 'Events',
		),
		array(
			'slug'     => 'gca-2025',
			'class'    => 'nice-landing-project--secondary',
			'image'    => 'gca-2025',
			'width'    => 569,
			'height'   => 293,
			'alt'      => 'Audience seated in a conference hall facing the stage',
			'title'    => 'GCA 2025',
			'client'   => 'Institute of Actuaries of India',
			'division' => 'Events',
		),
		array(
			'slug'     => 'zoetis-employee-engagement-day',
			'class'    => 'nice-landing-project--compact',
			'image'    => 'zoetis-engagement',
			'width'    => 543,
			'height'   => 305,
			'alt'      => 'Attendee posing with costumed characters at an employee event',
			'title'    => 'Zoetis Employee Engagement Day',
			'client'   => 'Zoetis',
			'division' => 'Events',
		),
	);
}

/**
 * Return landing projects from NICE Core or the complete fallback set.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_landing_project_previews() {
	$fallback = nice_get_landing_project_previews_fallback();

	if ( function_exists( 'nice_get_case_study_by_slug' ) ) {
		$previews = array();

		foreach ( $fallback as $item ) {
			$case_study = nice_get_case_study_by_slug( $item['slug'] );

			if ( ! $case_study ) {
				return apply_filters( 'nice_landing_project_previews', $fallback );
			}

			$item['title']         = $case_study->post_title;
			$item['client']        = function_exists( 'nice_get_case_study_client_name' ) ? nice_get_case_study_client_name( $case_study->ID ) : $item['client'];
			$item['attachment_id'] = get_post_thumbnail_id( $case_study );
			$previews[]            = $item;
		}

		return apply_filters( 'nice_landing_project_previews', $previews );
	}

	return apply_filters( 'nice_landing_project_previews', $fallback );
}

/**
 * Return the landing client display list after verifying CMS records exist.
 *
 * @return string[]
 */
function nice_get_landing_client_previews() {
	$fallback = array(
		'voltas-limited' => 'Voltas',
		'zoetis'         => 'Zoetis',
		'airtel'         => 'Airtel',
		'bajaj'          => 'Bajaj',
		'mahindra'       => 'Mahindra',
		'crisil'         => 'CRISIL',
	);

	if ( function_exists( 'nice_get_client_by_slug' ) ) {
		foreach ( $fallback as $slug => $display_name ) {
			if ( ! nice_get_client_by_slug( $slug ) ) {
				return apply_filters( 'nice_landing_client_previews', array_values( $fallback ) );
			}
		}
	}

	return apply_filters( 'nice_landing_client_previews', array_values( $fallback ) );
}
