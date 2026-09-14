<?php
/**
 * Shared About page parts: the team directory and the connect block.
 *
 * Events and Studio render the same two components with the same markup. Rather
 * than keep two copies in step by hand, the structure lives here once and each
 * division styles it through the body class its own stylesheet already keys off
 * -- nice-is-events-inner and nice-is-studio-inner. One component, styled twice.
 *
 * @package Nice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the per-person links a published team member carries.
 *
 * Only links that exist are returned, so the card never draws an icon that
 * leads nowhere.
 *
 * @param int $member_id Team member post ID.
 * @return array<int, array{icon: string, url: string, label: string}>
 */
function nice_get_team_member_links( $member_id ) {
	$links    = array();
	$linkedin = (string) get_post_meta( $member_id, '_nice_linkedin_url', true );
	$instagram = (string) get_post_meta( $member_id, '_nice_instagram_url', true );
	$email    = (string) get_post_meta( $member_id, '_nice_public_email', true );
	$name     = get_the_title( $member_id );

	if ( $linkedin ) {
		$links[] = array(
			'icon'  => 'linkedin',
			'url'   => $linkedin,
			/* translators: %s: team member name. */
			'label' => sprintf( __( '%s on LinkedIn', 'nice' ), $name ),
		);
	}

	if ( $instagram ) {
		$links[] = array(
			'icon'  => 'instagram',
			'url'   => $instagram,
			/* translators: %s: team member name. */
			'label' => sprintf( __( '%s on Instagram', 'nice' ), $name ),
		);
	}

	if ( $email && is_email( $email ) ) {
		$links[] = array(
			'icon'  => 'email',
			'url'   => 'mailto:' . $email,
			/* translators: %s: team member name. */
			'label' => sprintf( __( 'Email %s', 'nice' ), $name ),
		);
	}

	return $links;
}

/**
 * Return the three sample cards shown while a division has no published member.
 *
 * These are a layout preview, not people. The content migration seeds real
 * placeholder records as drafts and deletes them if anything publishes them, so
 * the samples cannot come from the database -- and inventing a published person
 * to fill the space is exactly what that rule exists to prevent.
 *
 * @param string $division Division slug.
 * @return array<int, array{role: string, body: string}>
 */
function nice_get_team_sample_members( $division ) {
	if ( 'studio' === $division ) {
		return array(
			array(
				'role' => 'Direction and story',
				'body' => 'A short paragraph introducing this person, in their own words where possible: what they work on, and what they bring to a production.',
			),
			array(
				'role' => 'Production and post',
				'body' => 'Two or three lines is the right length. Long enough to say something specific, short enough that the row of cards still reads as a row.',
			),
			array(
				'role' => 'Design and motion',
				'body' => 'A portrait, a name, a designation, a short description and whichever links this person is happy to have published.',
			),
		);
	}

	return array(
		array(
			'role' => 'Planning and accounts',
			'body' => 'A short paragraph introducing this person, in their own words where possible: what they work on, and what they bring to a brief.',
		),
		array(
			'role' => 'Production and operations',
			'body' => 'Two or three lines is the right length. Long enough to say something specific, short enough that the row of cards still reads as a row.',
		),
		array(
			'role' => 'Design and experience',
			'body' => 'A portrait, a name, a designation, a short description and whichever links this person is happy to have published.',
		),
	);
}

/**
 * Render the About page team section for one division.
 *
 * @param string $division Division slug.
 * @param string $eyebrow  Section eyebrow.
 * @param string $heading  Section heading.
 */
function nice_render_about_team_section( $division, $eyebrow, $heading ) {
	$team = function_exists( 'nice_get_team_members_by_division' ) ? nice_get_team_members_by_division( $division ) : array();
	?>
	<section id="team" class="nice-about-section nice-team-directory" aria-labelledby="nice-team-directory-title">
		<div class="nice-wide">
			<header class="nice-about-heading" data-nice-reveal>
				<p class="nice-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<h2 id="nice-team-directory-title"><?php echo esc_html( $heading ); ?></h2>
			</header>

			<?php if ( $team ) : ?>
				<div class="nice-team-directory__list">
					<?php
					foreach ( $team as $member ) :
						$image = function_exists( 'nice_theme_get_featured_image' ) ? nice_theme_get_featured_image( $member->ID, '(min-width: 64rem) 320px, (min-width: 48rem) 44vw, calc(100vw - 40px)' ) : '';
						$role  = get_post_meta( $member->ID, '_nice_role', true );
						$links = nice_get_team_member_links( $member->ID );
						?>
						<article class="nice-team-member" data-nice-reveal>
							<div class="nice-team-member__portrait<?php echo $image ? '' : ' nice-team-member__portrait--empty'; ?>">
								<?php
								if ( $image ) {
									echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_get_attachment_image().
								}
								?>
							</div>
							<div class="nice-team-member__body">
								<h3><?php echo esc_html( $member->post_title ); ?></h3>
								<?php if ( $role ) : ?><p class="nice-team-member__role"><?php echo esc_html( $role ); ?></p><?php endif; ?>
								<div class="nice-team-member__bio"><?php echo wp_kses_post( apply_filters( 'the_content', $member->post_content ) ); ?></div>
								<?php if ( $links ) : ?>
									<ul class="nice-team-member__links">
										<?php foreach ( $links as $link ) : ?>
											<li>
												<a href="<?php echo esc_url( $link['url'] ); ?>" rel="noopener">
													<?php nice_render_icon( $link['icon'] ); ?>
													<span class="nice-sr-only"><?php echo esc_html( $link['label'] ); ?></span>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="nice-team-directory__notice" data-nice-reveal>
					<?php esc_html_e( 'Approved profiles are being prepared. The three cards below show the layout each one will use.', 'nice' ); ?>
				</p>
				<div class="nice-team-directory__list nice-team-directory__list--sample">
					<?php foreach ( nice_get_team_sample_members( $division ) as $sample ) : ?>
						<article class="nice-team-member nice-team-member--sample" data-nice-reveal>
							<div class="nice-team-member__portrait nice-team-member__portrait--empty">
								<span class="nice-team-member__portrait-note"><?php esc_html_e( 'Portrait', 'nice' ); ?></span>
							</div>
							<div class="nice-team-member__body">
								<h3><?php esc_html_e( 'Name Surname', 'nice' ); ?></h3>
								<p class="nice-team-member__role"><?php echo esc_html( $sample['role'] ); ?></p>
								<div class="nice-team-member__bio"><p><?php echo esc_html( $sample['body'] ); ?></p></div>
								<?php
								/*
								 * The sample icon row is chrome, not navigation: there is no
								 * profile to link to yet, and a link to nowhere is worse than
								 * no link. It is drawn for the layout and hidden from
								 * assistive technology, which the notice above explains.
								 */
								?>
								<ul class="nice-team-member__links nice-team-member__links--sample" aria-hidden="true">
									<li><span><?php nice_render_icon( 'linkedin' ); ?></span></li>
									<li><span><?php nice_render_icon( 'instagram' ); ?></span></li>
									<li><span><?php nice_render_icon( 'email' ); ?></span></li>
								</ul>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Render the About page connect block: social profiles and the office address.
 *
 * Both come from settings that already exist -- the company social map and the
 * office details -- so nothing here is retyped and an edit in wp-admin reaches
 * both division About pages at once.
 *
 * @param string $division Division slug.
 */
function nice_render_about_connect_section( $division ) {
	$profiles = function_exists( 'nice_theme_get_social_profiles' ) ? nice_theme_get_social_profiles() : array();
	$office   = function_exists( 'nice_get_office_details' ) ? nice_get_office_details() : array( 'address_lines' => array(), 'map_url' => '' );
	$lines    = (array) ( $office['address_lines'] ?? array() );
	$map_url  = (string) ( $office['map_url'] ?? '' );

	if ( ! $profiles && ! $lines ) {
		return;
	}
	?>
	<section class="nice-about-section nice-about-connect" aria-labelledby="nice-about-connect-title">
		<div class="nice-wide">
			<header class="nice-about-heading" data-nice-reveal>
				<p class="nice-eyebrow"><?php esc_html_e( 'Find us', 'nice' ); ?></p>
				<h2 id="nice-about-connect-title"><?php esc_html_e( 'Where we are, and where we post.', 'nice' ); ?></h2>
			</header>
			<div class="nice-about-connect__grid">
				<?php if ( $lines ) : ?>
					<?php /* The contact pages' office block, reused rather than restyled. */ ?>
					<div class="nice-office" data-nice-reveal>
						<h3 class="nice-office__heading"><?php nice_render_icon( 'location' ); ?><?php esc_html_e( 'Find the office', 'nice' ); ?></h3>
						<address class="nice-office__address">
							<?php foreach ( $lines as $line ) : ?>
								<span><?php echo esc_html( $line ); ?></span>
							<?php endforeach; ?>
						</address>
						<?php if ( $map_url ) : ?>
							<a class="nice-link nice-office__directions" href="<?php echo esc_url( $map_url ); ?>" rel="noopener" target="_blank">
								<?php esc_html_e( 'Get directions', 'nice' ); ?>
								<span class="nice-sr-only"><?php esc_html_e( '(opens Google Maps in a new tab)', 'nice' ); ?></span>
								<span aria-hidden="true">&#8599;</span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $profiles ) : ?>
					<div class="nice-about-connect__social" data-nice-reveal>
						<h3><span><?php esc_html_e( 'Follow NICE', 'nice' ); ?></span></h3>
						<ul class="nice-about-connect__links">
							<?php foreach ( $profiles as $profile ) : ?>
								<li>
									<a href="<?php echo esc_url( $profile['url'] ); ?>" rel="me noopener" data-nice-social="<?php echo esc_attr( $profile['key'] ); ?>">
										<?php nice_render_icon( $profile['key'] ); ?>
										<span><?php echo esc_html( $profile['label'] ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
