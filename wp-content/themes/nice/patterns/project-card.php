<?php
/**
 * Title: NICE Project Card
 * Slug: nice/project-card
 * Categories: featured
 * Description: Editorial project presentation with media, metadata, and link.
 */
?>
<!-- wp:group {"className":"nice-project-card","layout":{"type":"default"}} -->
<div class="wp-block-group nice-project-card">
	<!-- wp:group {"className":"nice-project-card__media nice-media-placeholder","layout":{"type":"constrained"}} -->
	<div class="wp-block-group nice-project-card__media nice-media-placeholder"></div>
	<!-- /wp:group -->
	<!-- wp:group {"className":"nice-project-card__content","layout":{"type":"default"}} -->
	<div class="wp-block-group nice-project-card__content">
		<!-- wp:group {"className":"nice-project-card__meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group nice-project-card__meta"><!-- wp:paragraph --><p><?php esc_html_e( 'Client', 'nice' ); ?></p><!-- /wp:paragraph --><!-- wp:paragraph --><p><?php esc_html_e( 'Category', 'nice' ); ?></p><!-- /wp:paragraph --><!-- wp:paragraph --><p><?php esc_html_e( 'Year', 'nice' ); ?></p><!-- /wp:paragraph --></div>
		<!-- /wp:group -->
		<!-- wp:heading {"level":3,"className":"nice-project-card__title"} -->
		<h3 class="wp-block-heading nice-project-card__title"><?php esc_html_e( 'Project title', 'nice' ); ?></h3>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"nice-project-card__description"} -->
		<p class="nice-project-card__description"><?php esc_html_e( 'Add a short, factual project descriptor.', 'nice' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

