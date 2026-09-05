<?php
/**
 * Title: NICE Service Row
 * Slug: nice/service-row
 * Categories: featured, text
 * Description: Minimal numbered service presentation.
 */
?>
<!-- wp:group {"className":"nice-service-card","layout":{"type":"default"}} -->
<div class="wp-block-group nice-service-card">
	<!-- wp:paragraph {"className":"nice-service-card__number"} -->
	<p class="nice-service-card__number">01</p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":3,"className":"nice-service-card__title"} -->
	<h3 class="wp-block-heading nice-service-card__title"><?php esc_html_e( 'Service name', 'nice' ); ?></h3>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"nice-service-card__copy"} -->
	<p class="nice-service-card__copy"><?php esc_html_e( 'Add a concise, source-approved service description.', 'nice' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"className":"nice-service-card__action"} -->
	<p class="nice-service-card__action"><a class="nice-link" href="#"><?php esc_html_e( 'Explore', 'nice' ); ?> <span class="nice-link__arrow" aria-hidden="true">-&gt;</span></a></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

