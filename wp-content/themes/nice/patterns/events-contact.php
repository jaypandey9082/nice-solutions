<?php
/**
 * Title: NICE Events Contact
 * Slug: nice/events-contact
 * Categories: call-to-action
 * Description: Form-free Events contact band using centralized contact values.
 */

$nice_events_placeholder_url = home_url( '/events/#events-contact-details-pending' );
$nice_whatsapp_action        = nice_get_contact_action( 'whatsapp', $nice_events_placeholder_url );
$nice_email_action           = nice_get_contact_action( 'email', $nice_events_placeholder_url );
$nice_has_placeholder        = $nice_whatsapp_action['placeholder'] || $nice_email_action['placeholder'];
$nice_events_contact_url     = esc_url( home_url( '/events/contact/' ) );
?>
<!-- wp:html -->
<section class="nice-contact-band nice-events-contact" id="events-contact" aria-labelledby="nice-events-contact-title">
	<div class="nice-wide nice-contact-band__inner">
		<div class="nice-stack" data-nice-reveal>
			<p class="nice-eyebrow">Start a conversation</p>
			<h2 id="nice-events-contact-title">Planning an event?<br>Let's make it NICE.</h2>
			<a class="nice-link nice-events-contact__route" href="<?php echo $nice_events_contact_url; ?>" data-nice-future-route="true">Events contact <span aria-hidden="true">-&gt;</span></a>
		</div>
		<div class="nice-contact-band__action-group" data-nice-reveal>
			<div class="nice-contact-band__actions">
				<a class="nice-button nice-button--primary" href="<?php echo esc_url( $nice_whatsapp_action['url'] ); ?>" data-nice-contact-channel="whatsapp" data-nice-contact-placeholder="<?php echo $nice_whatsapp_action['placeholder'] ? 'true' : 'false'; ?>"<?php echo $nice_whatsapp_action['placeholder'] ? ' aria-describedby="events-contact-details-pending"' : ''; ?>>WhatsApp</a>
				<a class="nice-button nice-button--secondary" href="<?php echo esc_url( $nice_email_action['url'] ); ?>" data-nice-contact-channel="email" data-nice-contact-placeholder="<?php echo $nice_email_action['placeholder'] ? 'true' : 'false'; ?>"<?php echo $nice_email_action['placeholder'] ? ' aria-describedby="events-contact-details-pending"' : ''; ?>>Email</a>
			</div>
			<?php if ( $nice_has_placeholder ) : ?>
				<p class="nice-contact-status" id="events-contact-details-pending">Contact details pending publication approval.</p>
			<?php endif; ?>
		</div>
	</div>
</section>
<!-- /wp:html -->
