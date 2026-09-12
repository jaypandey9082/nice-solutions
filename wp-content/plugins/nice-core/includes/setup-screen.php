<?php
/**
 * Tools → NICE Setup.
 *
 * The WP-CLI command remains the tool of choice where there is shell access.
 * Most of the three installations will be set up through a hosting panel with
 * no shell at all, so the same idempotent routine is reachable from wp-admin,
 * behind the same capability and nonce checks as any other destructive-looking
 * button.
 *
 * The screen refuses to run when the installation has not said which site it
 * is, because guessing produces the one outcome the three-site split exists to
 * prevent: both divisions published from a single database.
 *
 * @package NiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the setup screen under Tools.
 */
function nice_register_setup_screen() {
	add_management_page(
		__( 'NICE Setup', 'nice-core' ),
		__( 'NICE Setup', 'nice-core' ),
		'manage_options',
		'nice-setup',
		'nice_render_setup_screen'
	);
}
add_action( 'admin_menu', 'nice_register_setup_screen' );

/**
 * Return the setup screen URL.
 *
 * @return string
 */
function nice_get_setup_screen_url() {
	return admin_url( 'tools.php?page=nice-setup' );
}

/**
 * Run the setup this installation's identity calls for.
 *
 * Every branch is idempotent: an existing slug in any status is left exactly as
 * it is, and nothing that arrives as a draft is ever published here.
 *
 * @param bool $set_front_page Whether to point Settings → Reading at the generated home page.
 * @return array<string, mixed>|WP_Error
 */
function nice_run_installation_setup( $set_front_page = false ) {
	$problems = nice_get_site_identity_problems();

	if ( $problems ) {
		return new WP_Error( 'nice_setup_identity_invalid', implode( ' ', $problems ) );
	}

	$report = array(
		'identity'   => nice_get_site_identity(),
		'label'      => nice_get_site_identity_label(),
		'sections'     => array(),
		'notes'        => array(),
		'front_page'   => '',
		'home_page_id' => 0,
		'media_errors' => array(),
	);

	$terms = nice_ensure_default_terms();
	$report['sections'][] = array(
		'label'   => __( 'Vocabulary', 'nice-core' ),
		'created' => (int) $terms['created'],
		'skipped' => (int) $terms['existing'],
		'blocked' => 0,
	);

	if ( $terms['errors'] ) {
		return new WP_Error( 'nice_setup_terms_failed', implode( ' ', $terms['errors'] ) );
	}

	if ( nice_is_gateway_site() ) {
		/*
		 * The gateway owns no division content, so the content migration refuses
		 * here by design. What it does own is the curated previews.
		 */
		$report['notes'][] = __( 'This installation is the gateway. It owns no Services, Case Studies or Team Members; those belong to the Events and Studio installations.', 'nice-core' );
	} else {
		$migration = nice_run_content_migration();

		if ( is_wp_error( $migration ) ) {
			return $migration;
		}

		$report['sections'][] = array(
			'label'   => __( 'Section pages (Events)', 'nice-core' ),
			'created' => (int) $migration['pages']['created'],
			'skipped' => (int) $migration['pages']['skipped'],
			'blocked' => 0,
		);
		$report['sections'][] = array(
			'label'   => __( 'Section pages (Studio)', 'nice-core' ),
			'created' => (int) $migration['studio_page']['created'],
			'skipped' => (int) $migration['studio_page']['skipped'],
			'blocked' => 0,
		);

		foreach ( array(
			'clients'      => __( 'Clients', 'nice-core' ),
			'services'     => __( 'Services', 'nice-core' ),
			'case_studies' => __( 'Case Studies', 'nice-core' ),
		) as $key => $label ) {
			$report['sections'][] = array(
				'label'   => $label,
				'created' => (int) $migration[ $key ]['created'],
				'skipped' => (int) $migration[ $key ]['skipped'],
				'blocked' => (int) ( $migration[ $key ]['foreign'] ?? 0 ),
			);
		}

		$report['sections'][] = array(
			'label'   => __( 'LinkedIn candidates (drafts)', 'nice-core' ),
			'created' => (int) $migration['source_drafts']['created'],
			'skipped' => (int) $migration['source_drafts']['skipped'],
			'blocked' => 0,
		);
		$report['sections'][] = array(
			'label'   => __( 'Team members (drafts)', 'nice-core' ),
			'created' => (int) $migration['team_drafts']['created'],
			'skipped' => (int) $migration['team_drafts']['skipped'],
			'blocked' => 0,
		);

		$report['media_errors'] = array_unique( (array) $migration['media']['errors'] );
		$report['home_page_id'] = (int) ( $migration['pages']['home_page_id'] ?: $migration['studio_page']['home_page_id'] );

		if ( nice_is_division_site() ) {
			$division = nice_get_site_division();
			$home     = get_page_by_path( $division, OBJECT, 'page' );
			$report['home_page_id'] = $home instanceof WP_Post ? $home->ID : 0;
		}
	}

	if ( nice_site_owns_gateway_projects() ) {
		$gateway = nice_migrate_gateway_project_drafts();

		if ( $gateway['errors'] ) {
			return new WP_Error( 'nice_setup_gateway_failed', implode( ' ', $gateway['errors'] ) );
		}

		$report['sections'][] = array(
			'label'   => __( 'Gateway previews (drafts)', 'nice-core' ),
			'created' => (int) $gateway['created'],
			'skipped' => (int) $gateway['skipped'],
			'blocked' => 0,
		);
	}

	if ( $set_front_page && nice_is_division_site() && ! empty( $report['home_page_id'] ) ) {
		$report['front_page'] = nice_set_generated_front_page( (int) $report['home_page_id'] );
	}

	flush_rewrite_rules( false );

	return $report;
}

/**
 * Point Settings → Reading at the generated home page.
 *
 * @param int $page_id Page ID.
 * @return string Human-readable outcome.
 */
function nice_set_generated_front_page( $page_id ) {
	$page = get_post( $page_id );

	if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) {
		return __( 'The generated home page could not be found, so the front page was left alone.', 'nice-core' );
	}

	if ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) === $page->ID ) {
		return sprintf(
			/* translators: %s: page title. */
			__( 'The front page was already set to %s.', 'nice-core' ),
			$page->post_title
		);
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page->ID );

	return sprintf(
		/* translators: %s: page title. */
		__( 'The front page is now %s.', 'nice-core' ),
		$page->post_title
	);
}

/**
 * Handle the setup submission and render the screen.
 */
function nice_render_setup_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to set up this installation.', 'nice-core' ) );
	}

	$result = null;

	if ( isset( $_POST['nice_run_setup'] ) ) {
		check_admin_referer( 'nice_run_setup' );
		$result = nice_run_installation_setup( ! empty( $_POST['nice_set_front_page'] ) );
	}

	$identity  = nice_get_site_identity();
	$problems  = nice_get_site_identity_problems();
	$warnings  = nice_get_site_identity_warnings();
	$urls      = nice_get_division_site_urls();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'NICE Setup', 'nice-core' ); ?></h1>

		<h2><?php esc_html_e( 'This installation', 'nice-core' ); ?></h2>
		<table class="widefat striped" style="max-width:48rem">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Identity', 'nice-core' ); ?></th>
					<td>
						<strong><?php echo esc_html( nice_get_site_identity_label() ); ?></strong>
						<code><?php echo esc_html( $identity ? $identity : 'not set' ); ?></code>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Owns the content of', 'nice-core' ); ?></th>
					<td><?php
						$local = nice_get_local_division_slugs();
						echo esc_html( $local ? implode( ', ', $local ) : __( 'no division', 'nice-core' ) );
					?></td>
				</tr>
				<?php foreach ( array( 'main' => 'NICE_MAIN_SITE_URL', 'events' => 'NICE_EVENTS_SITE_URL', 'studio' => 'NICE_STUDIO_SITE_URL' ) as $key => $constant ) : ?>
					<tr>
						<th scope="row"><code><?php echo esc_html( $constant ); ?></code></th>
						<td><?php echo $urls[ $key ] ? esc_html( $urls[ $key ] ) : '<em>' . esc_html__( 'not defined', 'nice-core' ) . '</em>'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php foreach ( $problems as $problem ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $problem ); ?></p></div>
		<?php endforeach; ?>
		<?php foreach ( $warnings as $warning ) : ?>
			<div class="notice notice-warning"><p><?php echo esc_html( $warning ); ?></p></div>
		<?php endforeach; ?>

		<?php if ( is_wp_error( $result ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
		<?php elseif ( is_array( $result ) ) : ?>
			<h2><?php esc_html_e( 'What setup did', 'nice-core' ); ?></h2>
			<table class="widefat striped" style="max-width:48rem">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Records', 'nice-core' ); ?></th>
						<th><?php esc_html_e( 'Created', 'nice-core' ); ?></th>
						<th><?php esc_html_e( 'Already present', 'nice-core' ); ?></th>
						<th><?php esc_html_e( 'Not owned here', 'nice-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $result['sections'] as $section ) : ?>
						<tr>
							<td><?php echo esc_html( $section['label'] ); ?></td>
							<td><?php echo esc_html( (string) $section['created'] ); ?></td>
							<td><?php echo esc_html( (string) $section['skipped'] ); ?></td>
							<td><?php echo esc_html( (string) $section['blocked'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description"><?php esc_html_e( 'Drafts stay drafts. Nothing on this screen publishes a LinkedIn candidate, a team member or a gateway preview.', 'nice-core' ); ?></p>
			<?php foreach ( $result['notes'] as $note ) : ?>
				<div class="notice notice-info inline"><p><?php echo esc_html( $note ); ?></p></div>
			<?php endforeach; ?>
			<?php if ( ! empty( $result['front_page'] ) ) : ?>
				<div class="notice notice-success inline"><p><?php echo esc_html( $result['front_page'] ); ?></p></div>
			<?php endif; ?>
			<?php if ( ! empty( $result['media_errors'] ) ) : ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e( 'Some preview images were not imported. Production packages ship without unapproved photography, so this is expected there: upload the approved image on each record.', 'nice-core' ); ?></p>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Run setup', 'nice-core' ); ?></h2>
		<p><?php esc_html_e( 'Creates the vocabulary, the pages and the starter records this installation owns. Safe to run again: an existing record is never changed or replaced.', 'nice-core' ); ?></p>
		<form method="post" action="<?php echo esc_url( nice_get_setup_screen_url() ); ?>">
			<?php wp_nonce_field( 'nice_run_setup' ); ?>
			<?php if ( nice_is_division_site() ) : ?>
				<?php
				/*
				 * Offered on a division installation only. The combined site's front
				 * page is the landing page, and the gateway has no division home at
				 * all, so there is nothing to point Settings → Reading at in either.
				 */
				?>
				<p>
					<label>
						<input type="checkbox" name="nice_set_front_page" value="1" checked>
						<?php esc_html_e( 'Also set the generated home page as this site\'s front page', 'nice-core' ); ?>
					</label>
				</p>
			<?php endif; ?>
			<p>
				<button type="submit" name="nice_run_setup" value="1" class="button button-primary" <?php disabled( (bool) $problems ); ?>>
					<?php esc_html_e( 'Run NICE setup', 'nice-core' ); ?>
				</button>
			</p>
		</form>
		<p class="description"><?php esc_html_e( 'With shell access, wp nice migrate-content does the same work.', 'nice-core' ); ?></p>
	</div>
	<?php
}
