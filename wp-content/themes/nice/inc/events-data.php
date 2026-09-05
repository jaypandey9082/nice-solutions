<?php
/**
 * Source-approved Events home preview data.
 *
 * These filters are a temporary bridge to future NICE Core Service, Case Study,
 * and Client queries. They do not implement those content models.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the three approved Events services.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_events_services() {
	$services = array(
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

	return apply_filters( 'nice_events_services', $services );
}

/**
 * Return the curated Events project preview.
 *
 * @return array<int, array<string, mixed>>
 */
function nice_get_events_project_previews() {
	$projects = array(
		array(
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

	return apply_filters( 'nice_events_project_previews', $projects );
}

/**
 * Return source-supported Events client names.
 *
 * @return string[]
 */
function nice_get_events_clients() {
	$clients = array(
		'Voltas Limited',
		'Zoetis',
		'Institute of Actuaries of India',
		'Franchise India',
		'Airtel',
		'FICCI',
	);

	return apply_filters( 'nice_events_clients', $clients );
}
