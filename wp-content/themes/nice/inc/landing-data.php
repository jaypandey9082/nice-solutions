<?php
/**
 * Temporary source-approved landing-page preview data.
 *
 * These filters form a narrow bridge to future NICE Core queries. They are not
 * the Case Study or Client content models and should be replaced when those
 * models are implemented.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the curated landing-page project preview.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_landing_project_previews() {
	$projects = array(
		array(
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

	return apply_filters( 'nice_landing_project_previews', $projects );
}

/**
 * Return the curated landing-page client preview.
 *
 * @return string[]
 */
function nice_get_landing_client_previews() {
	$clients = array( 'Voltas', 'Zoetis', 'Airtel', 'Bajaj', 'Mahindra', 'CRISIL' );

	return apply_filters( 'nice_landing_client_previews', $clients );
}
