#!/usr/bin/env bash
#
# Install the built packages into a throwaway WordPress and check they work.
#
# The three production installations receive these two zip files and nothing
# else, so the only test that matches what actually ships is to install the zip
# files somewhere clean. This builds a fresh WordPress against an empty
# database, installs both packages, declares a production identity, runs setup,
# and fails on any PHP notice, warning or deprecation along the way.
#
# It never touches the development site or its database.
#
# Requires a MySQL server and a PHP binary. LocalWP's are found automatically;
# override with environment variables if they live elsewhere:
#
#   PHP_BIN=/usr/bin/php MYSQL_SOCKET=/tmp/mysql.sock ./scripts/verify-release-install.sh
#
# Usage: npm run check:install
set -euo pipefail

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
work_root="${WORK_ROOT:-${TMPDIR:-/tmp}}/nice-release-verify"

find_local_binary() {
	# shellcheck disable=SC2044
	find "$HOME/Library/Application Support/Local/lightning-services" \
		-maxdepth 5 -type f -name "$1" 2>/dev/null | sort | tail -1
}

PHP_BIN="${PHP_BIN:-$(find_local_binary php)}"
MYSQL_BIN="${MYSQL_BIN:-$(find_local_binary mysql)}"
MYSQL_SOCKET="${MYSQL_SOCKET:-$(ls "$HOME/Library/Application Support/Local/run/"*/mysql/mysqld.sock 2>/dev/null | head -1)}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-root}"
DB_NAME="${DB_NAME:-nice_release_verify}"
SITE_URL="${SITE_URL:-http://127.0.0.1:8899}"

if [ ! -x "$PHP_BIN" ]; then
	echo "No PHP binary. Set PHP_BIN." >&2
	exit 1
fi

if [ ! -S "$MYSQL_SOCKET" ]; then
	echo "No MySQL socket at '$MYSQL_SOCKET'. Start LocalWP, or set MYSQL_SOCKET." >&2
	exit 1
fi

wp_cli="${WP_CLI_PHAR:-$work_root/wp-cli.phar}"
wp_dir="$work_root/wordpress"

php_run() { "$PHP_BIN" -d mysqli.default_socket="$MYSQL_SOCKET" -d memory_limit=512M "$@"; }
wp() { php_run "$wp_cli" --path="$wp_dir" --skip-plugins=hello --no-color "$@"; }

verify_identity() {
	local identity="$1"
	local assertions="$2"
	local database="${DB_NAME}_${identity}"

	echo
	echo "════ Verifying a fresh $identity installation ════"
	rm -rf "$wp_dir"
	mkdir -p "$wp_dir"

	"$MYSQL_BIN" --socket="$MYSQL_SOCKET" -u"$DB_USER" -p"$DB_PASSWORD" \
		-e "DROP DATABASE IF EXISTS \`$database\`; CREATE DATABASE \`$database\`;" 2>/dev/null

	wp core download --version=latest --quiet
	wp config create --dbname="$database" --dbuser="$DB_USER" --dbpass="$DB_PASSWORD" --dbhost="localhost:$MYSQL_SOCKET" --quiet

	# Every PHP notice has to be visible; an unclean log fails the run.
	wp config set WP_DEBUG true --raw --quiet
	wp config set WP_DEBUG_LOG true --raw --quiet
	wp config set WP_DEBUG_DISPLAY false --raw --quiet

	wp core install --url="$SITE_URL" --title="NICE release verification" \
		--admin_user=verify --admin_password="$(openssl rand -base64 18)" \
		--admin_email=verify@example.invalid --skip-email --quiet

	echo "==> Installing the built packages"
	wp plugin install "$plugin_zip" --activate --quiet
	wp theme install "$theme_zip" --activate --quiet

	# Deliberately after activation, which is the order an administrator is most
	# likely to use. Activation must not create content before it knows the shape.
	echo "==> Declaring the installation identity"
	wp config set NICE_SITE_DIVISION "$identity" --quiet
	wp config set NICE_MAIN_SITE_URL https://nicesolutions.in --quiet
	wp config set NICE_EVENTS_SITE_URL https://events.nicesolutions.in --quiet
	wp config set NICE_STUDIO_SITE_URL https://studios.nicesolutions.in --quiet

	# The runbook sets this before setup; a plain-permalink site routes nothing.
	wp rewrite structure '/%postname%/' --quiet

	echo "==> Running setup"
	wp eval 'wp_set_current_user( 1 ); $r = nice_run_installation_setup( true ); if ( is_wp_error( $r ) ) { echo "SETUP REFUSED: ", $r->get_error_message(), "\n"; exit( 1 ); } foreach ( $r["sections"] as $s ) { printf( "    %-30s created %d, present %d, elsewhere %d\n", $s["label"], $s["created"], $s["skipped"], $s["blocked"] ); } if ( $r["front_page"] ) { echo "    ", $r["front_page"], "\n"; }'

	echo "==> Asserting the result"
	wp eval-file "$project_root/scripts/$assertions"

	echo "==> Rerunning setup, which must create nothing and change nothing"
	wp eval 'wp_set_current_user( 1 ); $before = get_posts( array( "post_type" => "any", "post_status" => "any", "posts_per_page" => -1, "fields" => "ids" ) ); $r = nice_run_installation_setup(); $created = 0; foreach ( $r["sections"] as $s ) { $created += $s["created"]; } $after = get_posts( array( "post_type" => "any", "post_status" => "any", "posts_per_page" => -1, "fields" => "ids" ) ); if ( $created ) { echo "    FAIL rerun created {$created} records\n"; exit( 1 ); } if ( count( $before ) !== count( $after ) ) { printf( "    FAIL record count moved from %d to %d\n", count( $before ), count( $after ) ); exit( 1 ); } printf( "    ok   rerun created nothing (%d records before and after)\n", count( $after ) );'

	echo "==> Checking that an editor change survives a rerun"
	wp eval 'wp_set_current_user( 1 ); $post = nice_find_migrated_post( "corporate-events", "nice_service" ) ?: nice_find_migrated_post( "corporate-videos", "nice_service" ); if ( ! $post ) { echo "    skipped: no service on this installation\n"; exit( 0 ); } wp_update_post( array( "ID" => $post->ID, "post_title" => "Edited By An Editor" ) ); nice_run_installation_setup(); $after = get_post( $post->ID ); if ( "Edited By An Editor" !== $after->post_title ) { echo "    FAIL an editor title was overwritten\n"; exit( 1 ); } echo "    ok   an editor title survived the rerun\n";'

	local debug_log="$wp_dir/wp-content/debug.log"
	if [ -s "$debug_log" ]; then
		# Core pings wordpress.org for updates on a fresh install. A sandbox with
		# no route there logs a warning that says nothing about these packages, so
		# it is the one thing filtered out; everything else still fails the run.
		local ours
		ours="$(grep -v -E 'wp_update_plugins|wp_update_themes|wp_version_check|could not establish a secure connection to WordPress.org' "$debug_log" || true)"

		if [ -n "$(echo "$ours" | tr -d '[:space:]')" ]; then
			echo "==> PHP notices during installation and setup:"
			echo "$ours"
			exit 1
		fi

		echo "    ok   no PHP notices from these packages (core's update ping could not reach wordpress.org here)"
		return
	fi

	echo "    ok   no PHP notices, warnings or deprecations"
}

echo "==> Preparing $work_root"
mkdir -p "$work_root"

if [ ! -f "$wp_cli" ]; then
	mkdir -p "$(dirname "$wp_cli")"
	curl -sSL -o "$wp_cli" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
fi

theme_zip="$(ls "$project_root"/output/releases/nice-theme-*.zip | tail -1)"
plugin_zip="$(ls "$project_root"/output/releases/nice-core-*.zip | tail -1)"
[ -f "$theme_zip" ] && [ -f "$plugin_zip" ] || { echo "Run npm run build:release first." >&2; exit 1; }

verify_identity events wp-fresh-install-assertions.php
verify_identity main wp-fresh-gateway-assertions.php

rm -rf "$wp_dir"

echo
echo "Verified $(basename "$theme_zip") and $(basename "$plugin_zip") on fresh Events and gateway installations."
