<?php
/**
 * The small inline icon set.
 *
 * Four icons, inline rather than a sprite file or an icon font. Inline costs no
 * extra request, inherits currentColor so the existing hover and per-division
 * colours keep working without a second set of rules, and needs no build step.
 *
 * Every icon is decorative. The accessible name stays on the link text beside
 * it, so each SVG is hidden from assistive technology and removed from the tab
 * order rather than carrying a title of its own.
 *
 * Provenance: the outline icons are from Feather (MIT). The WhatsApp mark is
 * from Simple Icons (CC0), which is the brand glyph rather than an approximation
 * of it -- a stroked lookalike would misrepresent the mark.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the icon set.
 *
 * Each entry carries its own paint mode, because the WhatsApp mark is a filled
 * glyph while the rest are strokes.
 *
 * @return array<string, array{paint: string, body: string}>
 */
function nice_get_icons() {
	return array(
		'whatsapp' => array(
			'paint' => 'fill',
			'body'  => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>',
		),
		'phone'    => array(
			'paint' => 'stroke',
			'body'  => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/>',
		),
		'email'    => array(
			'paint' => 'stroke',
			'body'  => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/>',
		),
		'location' => array(
			'paint' => 'stroke',
			'body'  => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>',
		),
	);
}

/**
 * Return one icon as inline SVG markup.
 *
 * @param string $name  Icon name.
 * @param string $class Extra class names for the svg element.
 * @return string Markup, or an empty string for an unknown icon.
 */
function nice_get_icon( $name, $class = '' ) {
	$icons = nice_get_icons();
	$name  = sanitize_key( $name );

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	$icon  = $icons[ $name ];
	$paint = 'fill' === $icon['paint']
		? 'fill="currentColor"'
		: 'fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';

	return sprintf(
		'<svg class="nice-icon nice-icon--%1$s%2$s" viewBox="0 0 24 24" width="20" height="20" %3$s aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		$class ? ' ' . esc_attr( $class ) : '',
		$paint,
		$icon['body']
	);
}

/**
 * Print one icon.
 *
 * @param string $name  Icon name.
 * @param string $class Extra class names.
 */
function nice_render_icon( $name, $class = '' ) {
	echo nice_get_icon( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed markup from a closed set.
}
