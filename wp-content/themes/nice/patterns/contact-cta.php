<?php
/**
 * Title: NICE Direct Contact CTA
 * Slug: nice/direct-contact
 * Categories: call-to-action
 * Description: Form-free contact band with WhatsApp and email actions.
 */

/*
 * Shared band: one channel set on a division page, one per division on the main
 * pages. With nothing published it keeps the single pending pair so the approval
 * notice and its aria-describedby target still render.
 */
$nice_channel_sets   = function_exists( 'nice_theme_get_contact_channel_sets' ) ? nice_theme_get_contact_channel_sets() : array();
$nice_label_channels = count( $nice_channel_sets ) > 1;

if ( ! $nice_channel_sets ) {
	$nice_whatsapp_action = nice_get_contact_action( 'whatsapp' );
	$nice_email_action    = nice_get_contact_action( 'email' );
	$nice_channel_sets    = array(
		array(
			'division'      => '',
			'label'         => '',
			'whatsapp_url'  => $nice_whatsapp_action['url'],
			'email_address' => '',
			'email_url'     => $nice_email_action['url'],
			'placeholder'   => true,
		),
	);
}

$nice_has_contact_placeholder = ! empty( $nice_channel_sets[0]['placeholder'] );
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
		<div class="wp-block-buttons nice-contact-band__actions">
		<?php
		foreach ( $nice_channel_sets as $nice_set ) :
			$nice_is_pending    = ! empty( $nice_set['placeholder'] );
			$nice_prefix        = $nice_label_channels && $nice_set['label'] ? $nice_set['label'] . ' ' : '';
			$nice_division_attr = ! empty( $nice_set['division'] ) ? ' data-nice-contact-division="' . esc_attr( $nice_set['division'] ) . '"' : '';
			$nice_pending_attrs = $nice_is_pending ? ' aria-describedby="contact-details-pending"' : '';
			$nice_email_href    = isset( $nice_set['email_url'] ) ? $nice_set['email_url'] : 'mailto:' . $nice_set['email_address'];
			?>
			<?php if ( $nice_set['whatsapp_url'] ) : ?>
		<!-- wp:button {"className":"is-style-nice-primary"} -->
		<div class="wp-block-button is-style-nice-primary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $nice_set['whatsapp_url'] ); ?>" data-nice-contact-channel="whatsapp"<?php echo $nice_division_attr; ?> data-nice-contact-placeholder="<?php echo $nice_is_pending ? 'true' : 'false'; ?>"<?php echo $nice_pending_attrs; ?>><?php echo esc_html( $nice_prefix . __( 'WhatsApp', 'nice' ) ); ?></a></div>
		<!-- /wp:button -->
			<?php endif; ?>
			<?php if ( $nice_email_href && ( $nice_is_pending || $nice_set['email_address'] ) ) : ?>
		<!-- wp:button {"className":"is-style-nice-secondary"} -->
		<div class="wp-block-button is-style-nice-secondary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $nice_email_href ); ?>" data-nice-contact-channel="email"<?php echo $nice_division_attr; ?> data-nice-contact-placeholder="<?php echo $nice_is_pending ? 'true' : 'false'; ?>"<?php echo $nice_pending_attrs; ?>><?php echo esc_html( $nice_prefix . __( 'Email', 'nice' ) ); ?></a></div>
		<!-- /wp:button -->
			<?php endif; ?>
		<?php endforeach; ?>
		</div>
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
