<?php
/**
 * Title: NICE Section Heading
 * Slug: nice/section-heading
 * Categories: featured, text
 * Description: Label, editorial heading, supporting copy, and optional link.
 */
?>
<!-- wp:group {"className":"nice-section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group nice-section-heading">
	<!-- wp:paragraph {"className":"nice-eyebrow"} -->
	<p class="nice-eyebrow"><?php esc_html_e( 'Section label', 'nice' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:group {"className":"nice-section-heading__row","layout":{"type":"default"}} -->
	<div class="wp-block-group nice-section-heading__row">
		<!-- wp:heading {"level":2} -->
		<h2 class="wp-block-heading"><?php esc_html_e( 'Section heading', 'nice' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"nice-section-heading__copy"} -->
		<p class="nice-section-heading__copy"><?php esc_html_e( 'Add a concise supporting statement.', 'nice' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

