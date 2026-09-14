<?php
/**
 * Shared, server-rendered NICE philosophy: the compact strip and the full manifesto.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the shared slogan without request-level state or duplicate IDs.
 *
 * @return string
 */
function nice_render_philosophy_strip() {
	/*
	 * The separators are drawn, not typed, and hidden from assistive technology:
	 * a screen reader announced the old hyphens, so the strip read as "Emagine
	 * hyphen Explore hyphen Execute". The words keep their real capitalisation
	 * in the markup and are uppercased by CSS, so what is copied and what is
	 * announced stays "Emagine", not "EMAGINE".
	 */
	return '<div class="nice-philosophy-strip"><p class="nice-philosophy-strip__text">'
		. '<span class="nice-philosophy-strip__item">Emagine</span>'
		. '<span class="nice-philosophy-strip__separator" aria-hidden="true"></span>'
		. '<span class="nice-philosophy-strip__item">Explore</span>'
		. '<span class="nice-philosophy-strip__separator" aria-hidden="true"></span>'
		. '<span class="nice-philosophy-strip__item">Execute</span>'
		. '</p></div>';
}

/**
 * Return the three philosophy steps, each with the company's own words.
 *
 * The copy is transcribed from the "The Way We Work!" spread of the company
 * profile deck and is deliberately not paraphrased -- it is NICE's voice, down
 * to the coined spelling of "Emagine".
 *
 * @return array<int, array{word: string, body: string}>
 */
function nice_get_philosophy_steps() {
	return array(
		array(
			'word' => 'Emagine',
			'body' => 'Imagination is the beginning of a creation. Imagination takes our vision far beyond the obvious to creative landscapes of new ideas and dreams. We EMAGINE to EXCEL.',
		),
		array(
			'word' => 'Explore',
			'body' => 'Sometimes, you need to step out, get some air, observe, learn, learn more and get back to work. For US its EVERYTIME.',
		),
		array(
			'word' => 'Execute',
			'body' => 'If you cannot put it to work, what is the point to Emagine and Explore. Once we are up for it, it will always be smooth.',
		),
	);
}

/**
 * Render the full philosophy section for a division About page.
 *
 * Deliberately not the strip. nice_render_*_inner_hero() already emits one
 * .nice-philosophy-strip on every inner page, and the inner-page check asserts
 * there is exactly one per page, so this section carries its own class and its
 * own markup rather than reusing that one at a larger size.
 *
 * Capitalisation follows the same rule the strip established: the words are
 * written in sentence case and uppercased by CSS, so what a screen reader
 * announces and what a visitor copies stays "Emagine", not "EMAGINE".
 *
 * @param string $division Division slug, used only to pick a class modifier.
 * @return string
 */
function nice_render_philosophy_manifesto( $division = '' ) {
	$division = sanitize_key( $division );
	$modifier = $division ? ' nice-philosophy-manifesto--' . $division : '';

	ob_start();
	?>
	<section class="nice-philosophy-manifesto<?php echo esc_attr( $modifier ); ?>" aria-labelledby="nice-philosophy-manifesto-title">
		<div class="nice-wide">
			<header class="nice-philosophy-manifesto__intro" data-nice-reveal>
				<p class="nice-eyebrow">The way we work</p>
				<h2 id="nice-philosophy-manifesto-title">Emagine. Explore. Execute.</h2>
				<p>As clich&eacute;d as it may sound, a job well planned is a job half done. With a well planned communication module, we invest a good time understanding and analysing your brand and the requirements, to map the route and the propulsion required to take it to the desired level.</p>
				<p>Propelled into action, the reach is limited by what you Emagine, the relevance by what you desire to Explore, and the results by your readiness to Execute.</p>
			</header>
			<ol class="nice-philosophy-manifesto__steps">
				<?php foreach ( nice_get_philosophy_steps() as $index => $step ) : ?>
					<li class="nice-philosophy-manifesto__step" data-nice-reveal>
						<span class="nice-philosophy-manifesto__count" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
						<h3><?php echo esc_html( $step['word'] ); ?></h3>
						<p><?php echo esc_html( $step['body'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<blockquote class="nice-philosophy-manifesto__belief" data-nice-reveal>
				<p>Our belief in our capabilities to put <strong>method</strong> to the <strong>madness</strong> is exactly what helps us structure the random and put <strong>meaning</strong> to the chaos.</p>
			</blockquote>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Register the dynamic block used by templates and patterns.
 */
function nice_register_philosophy_strip_block() {
	register_block_type(
		'nice/philosophy-strip',
		array(
			'api_version'     => 3,
			'render_callback' => 'nice_render_philosophy_strip',
		)
	);
}
add_action( 'init', 'nice_register_philosophy_strip_block' );
