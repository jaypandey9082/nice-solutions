<?php
/**
 * Title: NICE Direct Contact CTA
 * Slug: nice/direct-contact
 * Categories: call-to-action
 * Description: Form-free contact band with WhatsApp and email actions.
 */

$nice_whatsapp_action         = nice_get_contact_action( 'whatsapp' );
$nice_email_action            = nice_get_contact_action( 'email' );
$nice_has_contact_placeholder = $nice_whatsapp_action['placeholder'] || $nice_email_action['placeholder'];
?>
<!-- wp:group {"align":"full","className":"nice-contact-band","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull nice-contact-band" id="contact">
	<!-- wp:group {"className":"nice-wide nice-contact-band__inner","layout":{"type":"default"}} -->
	<div class="wp-block-group nice-wide nice-contact-band__inner">
		<!-- wp:group {"className":"nice-stack","layout":{"type":"default"}} -->
		<div class="wp-block-group nice-stack">
			<!-- wp:paragraph {"className":"nice-eyebrow"} -->
			<p class="nice-eyebrow"><?php esc_html_e( 'Let\'s create something', 'nice' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":2} -->
			<h2 class="wp-block-heading"><?php esc_html_e( 'Have a project in mind?', 'nice' ); ?></h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->
		<!-- wp:group {"className":"nice-contact-band__action-group","layout":{"type":"default"}} -->
		<div class="wp-block-group nice-contact-band__action-group">
		<!-- wp:buttons {"className":"nice-contact-band__actions"} -->
		<div class="wp-block-buttons nice-contact-band__actions"><!-- wp:button {"className":"is-style-nice-primary"} -->
		<div class="wp-block-button is-style-nice-primary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $nice_whatsapp_action['url'] ); ?>" data-nice-contact-channel="whatsapp" data-nice-contact-placeholder="<?php echo $nice_whatsapp_action['placeholder'] ? 'true' : 'false'; ?>"<?php echo $nice_whatsapp_action['placeholder'] ? ' aria-describedby="contact-details-pending"' : ''; ?>><?php esc_html_e( 'WhatsApp', 'nice' ); ?></a></div>
		<!-- /wp:button --><!-- wp:button {"className":"is-style-nice-secondary"} -->
		<div class="wp-block-button is-style-nice-secondary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $nice_email_action['url'] ); ?>" data-nice-contact-channel="email" data-nice-contact-placeholder="<?php echo $nice_email_action['placeholder'] ? 'true' : 'false'; ?>"<?php echo $nice_email_action['placeholder'] ? ' aria-describedby="contact-details-pending"' : ''; ?>><?php esc_html_e( 'Email', 'nice' ); ?></a></div>
		<!-- /wp:button --></div>
		<!-- /wp:buttons -->
		<?php if ( $nice_has_contact_placeholder ) : ?>
		<!-- wp:paragraph {"className":"nice-contact-status"} -->
		<p class="nice-contact-status" id="contact-details-pending"><?php esc_html_e( 'Contact details pending publication approval.', 'nice' ); ?></p>
		<!-- /wp:paragraph -->
		<?php endif; ?>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
