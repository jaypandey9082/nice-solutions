<?php
/**
 * Title: NICE Landing Capabilities
 * Slug: nice/landing-capabilities
 * Categories: services
 * Description: Compact typography-led capability preview.
 */

$nice_capabilities = array(
	'Events' => array(
		'Corporate Events',
		'Exhibitions & Conferences',
		'Activations & Promotions',
	),
	'Studio' => array(
		'Corporate Videos',
		'Digital Content Creation',
		'Films & Entertainment',
	),
);
?>
<!-- wp:html -->
<section class="nice-landing-capabilities" id="capabilities" aria-labelledby="nice-capabilities-title">
	<div class="nice-wide nice-landing-capabilities__inner">
		<header data-nice-reveal>
			<p class="nice-eyebrow">Capabilities</p>
			<h2 id="nice-capabilities-title">What we do.</h2>
		</header>
		<div class="nice-capability-groups">
			<?php foreach ( $nice_capabilities as $nice_division => $nice_services ) : ?>
				<section class="nice-capability-group" aria-labelledby="nice-<?php echo esc_attr( strtolower( $nice_division ) ); ?>-capabilities" data-nice-reveal>
					<h3 id="nice-<?php echo esc_attr( strtolower( $nice_division ) ); ?>-capabilities"><?php echo esc_html( $nice_division ); ?></h3>
					<ol>
						<?php foreach ( $nice_services as $nice_index => $nice_service ) : ?>
							<li><span><?php echo esc_html( sprintf( '%02d', $nice_index + 1 ) ); ?></span><?php echo esc_html( $nice_service ); ?></li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<!-- /wp:html -->
